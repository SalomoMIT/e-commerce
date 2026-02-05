<div class="search-results mds-scrollbar">
    <ul>
        <?php if (!empty($suggestions['tags'])) :
            foreach ($suggestions['tags'] as $item) : ?>
                <li>
                    <a href="<?= generateUrl('products') . '?search=' . urlencode($item->tag); ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="search-item-left"><i class="icon-search"></i>&nbsp;&nbsp;<?= esc($item->tag); ?></div>
                            <div class="search-item-right"></div>
                        </div>
                    </a>
                </li>
            <?php endforeach;
        endif; ?>

        <?php if (!empty($suggestions['categories'])) :
            foreach ($suggestions['categories'] as $item) : ?>
                <li>
                    <a href="<?= generateCategoryUrl($item); ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="search-item-left"><?= esc($item->name); ?></div>
                            <div class="search-item-right"><span><?= trans('category'); ?></span></div>
                        </div>
                    </a>
                </li>
            <?php endforeach;
        endif; ?>

        <?php if (!empty($suggestions['brands'])) :
            foreach ($suggestions['brands'] as $item) : ?>
                <li>
                    <a href="<?= generateUrl('products') . "?brand=" . $item->id; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="search-item-left"><?= esc($item->name); ?></div>
                            <div class="search-item-right"><span><?= trans('brand'); ?></span></div>
                        </div>
                    </a>
                </li>
            <?php endforeach;
        endif; ?>

        <?php if (!empty($suggestions['shops'])) :
            foreach ($suggestions['shops'] as $item) : ?>
                <li>
                    <a href="<?= generateProfileUrl($item->slug); ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="search-item-left"><?= esc($item->username); ?></div>
                            <div class="search-item-right"><span><?= trans('shop'); ?></span></div>
                        </div>
                    </a>
                </li>
            <?php endforeach;
        endif; ?>


    </ul>
</div>