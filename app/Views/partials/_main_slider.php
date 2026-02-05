<?php if (!empty($sliderItems) && $generalSettings->slider_status == 1): ?>
    <div class="section-main-slider">
        <div class="<?= $generalSettings->slider_type == 'boxed' ? "container" : "container-fluid"; ?>">
            <div class="row">
                <div class="swiper main-slider<?= $generalSettings->slider_type == 'boxed' ? " main-slider-boxed" : ""; ?>" <?= $baseVars->rtl == true ? 'dir="rtl"' : ''; ?>>
                    <div class="swiper-wrapper">

                        <?php if (!empty($sliderItems)):
                            foreach ($sliderItems as $item): ?>
                                <div class="swiper-slide item" <?= $baseVars->rtl == true ? 'dir="rtl"' : ''; ?>>
                                    <a href="<?= esc($item->link); ?>">
                                        <picture>
                                            <source media="(max-width: 769px)" srcset="<?= esc($item->image_mobile); ?>">
                                            <img src="<?= base_url($item->image); ?>" class="swiper-lazy slide-background" alt="<?= esc("$item->title"); ?>">
                                        </picture>

                                        <div class="container">
                                            <div class="caption">
                                                <?php if (!empty($item->title)): ?>
                                                    <h2 class="title" data-animation="animate__<?= $item->animation_title; ?>" data-delay="0.1s" style="color: <?= $item->text_color; ?>"><?= esc($item->title); ?></h2>
                                                <?php endif;
                                                if (!empty($item->description)): ?>
                                                    <p class="description" data-animation="animate__<?= $item->animation_description; ?>" data-delay="0.5s" style="color: <?= $item->text_color; ?>"><?= esc($item->description); ?></p>
                                                <?php endif;
                                                if (!empty($item->button_text)): ?>
                                                    <button class="btn btn-slider" data-animation="animate__<?= $item->animation_button; ?>" data-delay="0.9s" style="background-color: <?= $item->button_color; ?>;border-color: <?= $item->button_color; ?>;color: <?= $item->button_text_color; ?>"><?= esc($item->button_text); ?></button>
                                                <?php endif; ?>
                                            </div>

                                        </div>
                                    </a>
                                </div>
                            <?php endforeach;
                        endif; ?>

                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>