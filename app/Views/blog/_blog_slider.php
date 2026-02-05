<?php if ($generalSettings->index_blog_slider == 1 && !empty($blogSliderPosts)): ?>
    <div class="col-12 section section-blog">
        <div class="section-header">
            <h3 class="title"><a href="<?= generateUrl('blog'); ?>"><?= trans("latest_blog_posts"); ?></a></h3>
        </div>
        <div class="swiper swiper-carousel swiper-carousel-blog" <?= $baseVars->rtl == true ? 'dir="rtl"' : ''; ?>>
            <div class="swiper-wrapper">
                <?php foreach ($blogSliderPosts as $item): ?>
                    <div class="swiper-slide text-left">
                        <?= view('blog/_blog_item', ['item' => $item]); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
<?php endif; ?>