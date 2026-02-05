<div class="blog-item blog-item-small">
    <div class="blog-item-img">
        <a href="<?= generateUrl('blog') . '/' . $item->category_slug . '/' . $item->slug; ?>">
            <img data-src="<?= getBlogImageURL($item, 'image_small'); ?>" alt="<?= esc($item->title); ?>" class="img-fluid lazyload" width="300" height="200">
        </a>
    </div>
    <h3 class="blog-post-title">
        <a href="<?= generateUrl('blog') . '/' . $item->category_slug . '/' . $item->slug; ?>"><?= esc(characterLimiter($item->title, 56, '...')); ?></a>
    </h3>
    <div class="blog-post-meta">
        <span><i class="icon-clock"></i><?= timeAgo($item->created_at); ?></span>
        <a href="<?= generateUrl('blog') . '/' . $item->category_slug; ?>"><i class="icon-folder"></i><?= esc($item->category_name); ?></a>
    </div>
</div>