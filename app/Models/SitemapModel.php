<?php namespace App\Models;

use XMLWriter;

class SitemapModel extends BaseModel
{
    /**
     * The maximum number of URLs per sitemap file. Google's limit is 50,000.
     * @var int
     */
    const MAX_URLS_PER_SITEMAP = 49999;

    /**
     * The number of records to fetch from the database in a single query.
     * @var int
     */
    const DB_CHUNK_SIZE = 1000;

    /**
     * The change frequency for product categories.
     * Recommended values: 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'.
     * @var string
     */
    const CATEGORY_CHANGE_FREQ = 'weekly';

    /**
     * The change frequency for blog categories.
     * @var string
     */
    const BLOG_CATEGORY_CHANGE_FREQ = 'monthly';


    /**
     * The XMLWriter instance used to write XML files without consuming much memory.
     * @var XMLWriter
     */
    private $xmlWriter;

    /**
     * An array to hold the URLs of all generated sitemap files (e.g., sitemap-products-1.xml).
     * @var array
     */
    private $sitemapIndexFiles = [];

    /**
     * Holds language information to generate correct base URLs.
     * @var array
     */
    protected $langArray = [];

    public function __construct()
    {
        parent::__construct();
        $this->xmlWriter = new XMLWriter();

        // Populate the language array from context
        $languages = getContextValue('languages');
        if (!empty($languages)) {
            foreach ($languages as $lang) {
                if (isset($lang->id) && isset($lang->short_form)) {
                    $this->langArray[$lang->id] = $lang->short_form;
                }
            }
        }
    }

    /**
     * Start the entire sitemap generation process.
     */
    public function generate()
    {
        // Clean up any old sitemap files from the public directory.
        $this->deleteOldSitemaps();

        // Process each content type sequentially
        $this->processContentType('static');
        $this->processContentType('products');
        $this->processContentType('categories');
        $this->processContentType('blog_posts');
        $this->processContentType('blog_categories');

        // Generate the main sitemap index file.
        $this->generateSitemapIndex();
    }

    /**
     * Processes a specific content type (e.g., 'products', 'categories')
     *
     * @param string $contentType A key to identify the content type.
     */
    private function processContentType(string $contentType)
    {
        $model = null;
        $paginatedMethod = '';
        $priority = '0.5';
        $changeFreq = 'monthly';
        $urlGenerator = function ($item) {
            return '';
        };

        // Configure the process based on the content type
        switch ($contentType) {
            case 'static':
                $this->addHomePageWithHreflang();
                return;

            case 'products':
                $model = new ProductModel();
                $paginatedMethod = 'getSitemapProductsPaginated';
                $priority = '0.8';
                $changeFreq = 'auto';
                $urlGenerator = function ($item) {
                    return generateProductUrl($item);
                };
                break;

            case 'categories':
                $model = new CategoryModel();
                $paginatedMethod = 'getSitemapCategoriesPaginated';
                $priority = '0.6';
                $changeFreq = self::CATEGORY_CHANGE_FREQ;
                $urlGenerator = function ($item) {
                    return generateCategoryUrl($item);
                };
                break;

            case 'blog_posts':
                $model = new BlogModel();
                $paginatedMethod = 'getSitemapPostsPaginated';
                $priority = '0.5';
                $changeFreq = 'auto';
                $urlGenerator = function ($item) {
                    $baseURL = $this->getBaseURL($item->lang_id);
                    return $baseURL . getRoute('blog') . '/' . $item->category_slug . '/' . $item->slug;
                };
                break;

            case 'blog_categories':
                $model = new BlogModel();
                $paginatedMethod = 'getSitemapBlogCategoriesPaginated';
                $priority = '0.4';
                $changeFreq = self::BLOG_CATEGORY_CHANGE_FREQ;
                $urlGenerator = function ($item) {
                    $baseURL = $this->getBaseURL($item->lang_id);
                    return $baseURL . getRoute('blog') . '/' . $item->slug;
                };
                break;
        }

        if (!$model || !method_exists($model, $paginatedMethod)) {
            return;
        }

        $offset = 0;
        $urlCounter = 0;
        $fileCounter = 1;
        $hasRecords = false;

        do {
            // Fetch a chunk of records from the database
            $records = $model->$paginatedMethod(self::DB_CHUNK_SIZE, $offset);

            if (!empty($records)) {
                $hasRecords = true;

                foreach ($records as $record) {
                    // If it's the first URL for this content type, start the first file.
                    if ($urlCounter == 0) {
                        $filename = "sitemap_{$contentType}-{$fileCounter}.xml";
                        $this->startNewSitemapFile($filename);
                    }

                    $location = $urlGenerator($record);

                    $lastMod = $record->updated_at ?? $record->created_at ?? date('Y-m-d H:i:s');
                    $finalChangeFreq = '';

                    // Check if frequency should be calculated automatically or use the predefined value
                    if ($changeFreq == 'auto') {
                        $finalChangeFreq = $this->calculateChangeFrequency($lastMod);
                    } else {
                        $finalChangeFreq = $changeFreq;
                    }

                    $this->writeUrlToSitemap($location, $priority, $lastMod, $finalChangeFreq);
                    $urlCounter++;

                    // If the current sitemap file is full, finish it and start a new one.
                    if ($urlCounter >= self::MAX_URLS_PER_SITEMAP) {
                        $this->finishCurrentSitemapFile();
                        $fileCounter++;
                        $urlCounter = 0; // Reset counter for the new file
                    }
                }
            }
            $offset += self::DB_CHUNK_SIZE;
        } while (!empty($records)); // Continue until the database returns no more records

        // If any URLs were written for this content type, make sure the last file is closed.
        if ($hasRecords) {
            $this->finishCurrentSitemapFile();
        }
    }

    /**
     * Add the homepage with all its language alternatives (hreflang) to a sitemap.
     */
    private function addHomePageWithHreflang()
    {
        $this->startNewSitemapFile('sitemap_static.xml');

        $this->xmlWriter->startElement('url');
        $this->xmlWriter->writeElement('loc', base_url());

        // Add hreflang links for all available languages
        if (!empty($this->langArray)) {
            foreach ($this->langArray as $id => $short_form) {
                $this->xmlWriter->startElement('xhtml:link');
                $this->xmlWriter->writeAttribute('rel', 'alternate');
                $this->xmlWriter->writeAttribute('hreflang', $short_form);
                $this->xmlWriter->writeAttribute('href', $this->getBaseURL($id));
                $this->xmlWriter->endElement(); // </xhtml:link>
            }
        }

        // Add the x-default hreflang tag for search engines to identify the default page
        $this->xmlWriter->startElement('xhtml:link');
        $this->xmlWriter->writeAttribute('rel', 'alternate');
        $this->xmlWriter->writeAttribute('hreflang', 'x-default');
        $this->xmlWriter->writeAttribute('href', base_url());
        $this->xmlWriter->endElement(); // </xhtml:link>

        $this->xmlWriter->writeElement('lastmod', date('Y-m-d\TH:i:sP'));
        $this->xmlWriter->writeElement('changefreq', 'daily');
        $this->xmlWriter->writeElement('priority', '1.0');
        $this->xmlWriter->endElement(); // </url>

        $this->finishCurrentSitemapFile();
    }

    /**
     * Calculate the change frequency based on the last modification date.
     *
     * @param string|null $lastModDate The date string of the last modification.
     * @return string The calculated frequency ('daily', 'weekly', 'monthly', 'yearly').
     */
    private function calculateChangeFrequency(?string $lastModDate): string
    {
        if (empty($lastModDate)) {
            return 'monthly';
        }

        $lastModTimestamp = strtotime($lastModDate);
        $now = time();
        $ageInSeconds = $now - $lastModTimestamp;

        $oneDay = 86400;
        $oneWeek = 7 * $oneDay;
        $oneMonth = 30 * $oneDay;

        if ($ageInSeconds <= $oneDay) {
            return 'daily';
        } elseif ($ageInSeconds <= $oneWeek) {
            return 'weekly';
        } elseif ($ageInSeconds <= $oneMonth) {
            return 'monthly';
        } else {
            return 'yearly';
        }
    }

    /**
     * Writes a single <url> node to the currently open sitemap file.
     *
     * @param string $location The URL of the page.
     * @param string $priority The priority of this URL relative to other URLs on your site.
     * @param string $lastMod The date of last modification of the file.
     * @param string $changeFreq The calculated change frequency.
     */
    private function writeUrlToSitemap(string $location, string $priority, string $lastMod, string $changeFreq)
    {
        $this->xmlWriter->startElement('url');
        $this->xmlWriter->writeElement('loc', htmlspecialchars(strtolower($location)));

        if ($this->productSettings->sitemap_last_modification == 'auto'){
            $this->xmlWriter->writeElement('lastmod', date('Y-m-d\TH:i:sP', strtotime($lastMod)));
        }

        if ($this->productSettings->sitemap_frequency == 'auto' && !empty($changeFreq) && $changeFreq != 'none') {
            $this->xmlWriter->writeElement('changefreq', $changeFreq);
        }

        if ($this->productSettings->sitemap_priority == 'auto'){
            $this->xmlWriter->writeElement('priority', $priority);
        }

        $this->xmlWriter->endElement(); // </url>
    }

    /**
     * Creates and opens a new sitemap file for writing.
     *
     * @param string $filename The name of the file to create (e.g., "sitemap-products-1.xml").
     */
    private function startNewSitemapFile(string $filename)
    {
        $this->xmlWriter = new XMLWriter();

        $fullPath = FCPATH . $filename;
        $this->sitemapIndexFiles[] = base_url($filename); // Add to the list for the index file

        $this->xmlWriter->openURI($fullPath);
        $this->xmlWriter->setIndent(true); // Makes the XML readable
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('urlset');
        $this->xmlWriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->xmlWriter->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
    }

    /**
     * Closes the </urlset> tag and finalizes the currently open sitemap file.
     */
    private function finishCurrentSitemapFile()
    {
        if ($this->xmlWriter !== null) {
            $this->xmlWriter->endElement(); // </urlset>
            $this->xmlWriter->endDocument();
            $this->xmlWriter->flush();

            $this->xmlWriter = null;
        }

    }

    /**
     * Generates the main sitemap index file (sitemap.xml) that links to all other sitemaps.
     */
    private function generateSitemapIndex()
    {
        $this->xmlWriter = new XMLWriter();

        $fullPath = FCPATH . 'sitemap.xml';

        $this->xmlWriter->openURI($fullPath);
        $this->xmlWriter->setIndent(true);
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('sitemapindex');
        $this->xmlWriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        if (!empty($this->sitemapIndexFiles)) {
            foreach ($this->sitemapIndexFiles as $sitemapUrl) {
                $this->xmlWriter->startElement('sitemap');
                $this->xmlWriter->writeElement('loc', $sitemapUrl);
                $this->xmlWriter->writeElement('lastmod', date('Y-m-d\TH:i:sP'));
                $this->xmlWriter->endElement(); // </sitemap>
            }
        }

        $this->xmlWriter->endElement(); // </sitemapindex>
        $this->xmlWriter->endDocument();
        $this->xmlWriter->flush();
    }

    /**
     * Deletes all sitemap*.xml files from the root directory to ensure a clean start.
     */
    private function deleteOldSitemaps()
    {
        $files = glob(FCPATH . 'sitemap*.xml');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    // Get base URL
    public function getBaseURL($langId)
    {
        if (empty($langId) || $langId == $this->generalSettings->site_lang || empty($this->langArray[$langId])) {
            return base_url();
        }
        return base_url($this->langArray[$langId]) . '/';
    }

    // Update sitemap settings
    public function updateSitemapSettings()
    {
        $data = [
            'sitemap_frequency' => inputPost('frequency') == 'auto' ? 'auto' : 'none',
            'sitemap_last_modification' => inputPost('last_modification') == 'auto' ? 'auto' : 'none',
            'sitemap_priority' => inputPost('priority') == 'auto' ? 'auto' : 'none'
        ];
        $this->db->table('product_settings')->where('id', 1)->update($data);
        $this->productSettings = $this->db->table('product_settings')->get()->getRow();
    }
}
