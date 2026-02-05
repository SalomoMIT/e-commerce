<?php
$feedItems = [];
$pageLanguage = $activeLang->short_form;
if (!empty($products) && is_array($products)) {
    foreach ($products as $product) {

        if (!is_object($product) || !isset($product->id)) {
            continue;
        }

        $productDetails = getProductDetails($product->id, selectedLangId(), true);
        $productLink = generateProductUrl($product);

        $imageUrl = null;
        $imageSize = '2500';
        $imageMime = 'image/jpeg';

        if (!empty($product->image_cache)) {
            $images = json_decode($product->image_cache, true);
            if (isset($images[0]['storage']) && isset($images[0]['image'])) {
                $storage = $images[0]['storage'];
                $filename = $images[0]['image'];

                $path = 'uploads/images/' . $filename;
                $imageUrl = getStorageFileUrl($path, $storage);

                if ($storage == 'local') {
                    $fullLocalPath = FCPATH . $path;
                    if (file_exists($fullLocalPath)) {
                        $imageSize = filesize($fullLocalPath);
                    }
                }

            }
        }

        $descriptionContent = '<div class="price"><p>✔ ' . trans("price") . ': ' . priceFormatted($product->price, $product->currency) . '</p></div>';
        $descriptionContent .= '<div class="description">' . (!empty($productDetails) ? $productDetails->description : '') . '</div>';

        $feedItems[] = [
            'title' => $product->title,
            'link' => $productLink,
            'guid' => $productLink,
            'description' => $descriptionContent,
            'pubDate' => date('r', strtotime($product->created_at)),
            'creator' => $product->user_username,
            'imageUrl' => $imageUrl,
            'imageSize' => $imageSize,
            'imageMime' => $imageMime,
        ];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
xmlns:admin="http://webns.net/mvcb/"
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
<title><?php echo htmlspecialchars($feedName, ENT_XML1, 'UTF-8'); ?></title>
<link><?php echo htmlspecialchars($feedUrl, ENT_XML1, 'UTF-8'); ?></link>
<description><?php echo htmlspecialchars($pageDescription, ENT_XML1, 'UTF-8'); ?></description>
<dc:language><?php echo htmlspecialchars($pageLanguage, ENT_XML1, 'UTF-8'); ?></dc:language>
<?php if (!empty($baseSettings->copyright)): ?>
<dc:rights><?php echo htmlspecialchars($baseSettings->copyright, ENT_XML1, 'UTF-8'); ?></dc:rights>
<?php endif; ?>

<?php foreach ($feedItems as $item): ?>
<item>
    <title><?php echo htmlspecialchars($item['title'], ENT_XML1, 'UTF-8'); ?></title>
    <link><?php echo htmlspecialchars($item['link'], ENT_XML1, 'UTF-8'); ?></link>
    <guid isPermaLink="true"><?php echo htmlspecialchars($item['guid'], ENT_XML1, 'UTF-8'); ?></guid>
    <description><![CDATA[<?php echo $item['description']; ?>]]></description>
<?php if (!empty($item['imageUrl'])): ?>
    <enclosure url="<?php echo htmlspecialchars($item['imageUrl'], ENT_XML1, 'UTF-8'); ?>"<?php if ($item['imageSize'] !== null): ?> length="<?php echo $item['imageSize']; ?>"<?php endif; ?> type="<?php echo htmlspecialchars($item['imageMime'], ENT_XML1, 'UTF-8'); ?>"/>
<?php endif; ?>
    <pubDate><?php echo htmlspecialchars($item['pubDate'], ENT_XML1, 'UTF-8'); ?></pubDate>
    <dc:creator><?php echo htmlspecialchars($item['creator'], ENT_XML1, 'UTF-8'); ?></dc:creator>
</item>
<?php endforeach; ?>

</channel>
</rss>