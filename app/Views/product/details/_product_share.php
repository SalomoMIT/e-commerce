<?php $productUrl = urlencode(generateProductUrl($product));
$productTitle = urlencode($title); ?>
<div class="row-custom product-share">
    <ul>
        <li>
            <button type="button" class="button-link" onclick='window.open("https://www.facebook.com/sharer/sharer.php?u=<?= $productUrl; ?>", "Share This Post", "width=640,height=450");return false' aria-label="share-facebook">
                <i class="icon-facebook"></i>
            </button>
        </li>
        <li>
            <button type="button" class="button-link" onclick='window.open("https://twitter.com/share?url=<?= $productUrl; ?>&amp;text=<?= $productTitle; ?>", "Share This Post", "width=640,height=450");return false' aria-label="share-twitter">
                <i class="icon-twitter"></i>
            </button>
        </li>
        <li>
            <a href="https://api.whatsapp.com/send?text=<?= $productTitle; ?> - <?= $productUrl; ?>" target="_blank" title="share-whatsapp">
                <i class="icon-whatsapp"></i>
            </a>
        </li>
        <li>
            <button type="button" class="button-link" onclick='window.open("http://pinterest.com/pin/create/button/?url=<?= $productUrl; ?>&amp;media=<?= $ogImage; ?>", "Share This Post", "width=640,height=450");return false' aria-label="share-pinterest">
                <i class="icon-pinterest"></i>
            </button>
        </li>
        <li>
            <button type="button" class="button-link" onclick='window.open("http://www.linkedin.com/shareArticle?mini=true&amp;url=<?= $productUrl; ?>", "Share This Post", "width=640,height=450");return false' aria-label="share-linkedin">
                <i class="icon-linkedin"></i>
            </button>
        </li>
        <li>
            <button type="button" class="button-link" onclick='window.open("https://t.me/share/url?url=<?= $productUrl; ?>&text=<?= $productTitle; ?>", "Share This Post", "width=640,height=450");return false' aria-label="share-telegram">
                <i class="icon-telegram"></i>
            </button>
        </li>
    </ul>
</div>