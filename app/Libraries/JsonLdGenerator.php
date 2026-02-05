<?php

namespace App\Libraries;

/**
 * A helper class to generate structured data in JSON-LD format.
 */

class JsonLdGenerator
{
    /**
     * The main public method to generate the JSON-LD script tag.
     *
     * @param array $types An array of schema types to generate (e.g., ['product', 'breadcrumb']).
     * @param array $data  An associative array containing all necessary data objects.
     * @return string The complete <script> tag with the JSON-LD data, or an empty string on failure.
     */
    public function generate(array $types, array $data): string
    {
        $schemas = [];

        // Loop through each requested type and build its corresponding schema array.
        foreach ($types as $type) {
            $schema = null;
            switch ($type) {
                case 'website':
                    $schema = $this->buildWebsiteSchema($data);
                    break;
                case 'organization':
                    $schema = $this->buildOrganizationSchema($data);
                    break;
                case 'product':
                    if (!empty($data['product'])) {
                        $schema = $this->buildProductSchema($data);
                    }
                    break;
                case 'breadcrumb':
                    if (!empty($data['parentCategoriesTree'])) {
                        $schema = $this->buildBreadcrumbSchema($data['parentCategoriesTree']);
                    }
                    break;
            }
            if ($schema !== null) {
                $schemas[] = $schema;
            }
        }

        if (empty($schemas)) {
            return '';
        }

        // If we have multiple schemas, we wrap them in a @graph array.
        $outputSchema = count($schemas) > 1
            ? ['@context' => 'https://schema.org', '@graph' => $schemas]
            : $schemas[0];

        // Removed JSON_PRETTY_PRINT to generate a single-line, minified output for production.
        $json = json_encode($outputSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /**
     * Builds the JSON-LD schema for the main website.
     */
    private function buildWebsiteSchema(array $data): array
    {
        return [
            '@type' => 'WebSite',
            'name' => $data['app_name'] ?? '',
            'url' => base_url(),
            'description' => $data['description'] ?? '',
            'publisher' => [
                '@type' => 'Organization',
                'name' => $data['app_name'] ?? '',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => getLogo(),
                ],
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => generateUrl('products') . '?search={search_term_string}',
                ],
                'query-input' => [
                    '@type' => 'PropertyValueSpecification',
                    'valueName' => 'search_term_string',
                    'valueRequired' => 'http://schema.org/True'
                ]
            ],
        ];
    }

    /**
     * Builds a detailed JSON-LD schema for the organization.
     */
    private function buildOrganizationSchema(array $data): array
    {
        $socialLinks = $data['socialMediaLinks'] ?? [];

        // Extract only the URL values from the returned array.
        $socialUrls = array_map(function ($link) {
            return $link['value'];
        }, $socialLinks);

        // Filter out any empty URLs to ensure the array is clean.
        $filteredSocialUrls = array_filter($socialUrls);

        $schema = [
            '@type' => 'Organization',
            'name' => $data['app_name'] ?? '',
            'url' => base_url(),
            'logo' => getLogo(),
            'sameAs' => array_values($filteredSocialUrls),
        ];
        return $schema;
    }

    /**
     * Builds the JSON-LD schema for a product page.
     */
    private function buildProductSchema(array $data): array
    {
        $product = $data['product'];
        $productDetails = $data['productDetails'];

        $schema = [
            '@type' => 'Product',
            'name' => $productDetails->title,
            'description' => $productDetails->short_description,
            'url' => generateProductUrl($product),
            'sku' => $product->sku ?? null,
            'image' => array_map(function ($img) {
                return getProductImageURL($img, 'image_default');
            }, $data['productImages']),
        ];

        if (!empty($data['productBrandName'])) {
            $schema['brand'] = ['@type' => 'Brand', 'name' => $data['productBrandName']];
        }

        if ($data['reviewsCount'] > 0 && $product->rating > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->rating,
                'reviewCount' => $data['reviewsCount'],
            ];
        }

        if (!empty($data['reviews'])) {
            $schema['review'] = array_map(function ($review) {
                return [
                    '@type' => 'Review',
                    'author' => ['@type' => 'Person', 'name' => $review->user_username],
                    'datePublished' => date('Y-m-d', strtotime($review->created_at)),
                    'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $review->rating],
                    'reviewBody' => $review->review,
                ];
            }, $data['reviews']);
        }

        $schema['offers'] = [
            '@type' => 'Offer',
            'url' => generateProductUrl($product),
            'priceCurrency' => $product->currency,
            'price' => numToDecimal($product->price_discounted),
            'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability' => (getProductStock($product) > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => ['@type' => 'Organization', 'name' => $product->user_username],
        ];

        return $schema;
    }

    /**
     * Builds the JSON-LD schema for breadcrumbs.
     */
    private function buildBreadcrumbSchema(array $tree): array
    {
        $elements = [];
        $elements[] = ['@type' => 'ListItem', 'position' => 1, 'name' => trans("home"), 'item' => base_url()];

        foreach ($tree as $i => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $i + 2,
                'name' => $item->cat_name,
                'item' => generateCategoryUrl($item),
            ];
        }

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $elements];
    }
}
?>
