<?php
$imageCount = countItems($productImages);
$hasVideo = !empty($video);
$hasAudio = !empty($audio);

$showSlider = $imageCount > 1 || ($imageCount === 1 && !$hasVideo && !$hasAudio);
$showVideo = !$showSlider && $hasVideo;
$showAudio = !$showSlider && !$hasVideo && $hasAudio; ?>

<?php if ($showSlider): ?>
    <div class="product-slider-wrapper">
        <div class="thumb-slider-wrapper">
            <div id="product-thumb-slider" class="swiper thumb-slider">
                <div class="swiper-wrapper">

                    <?php $i = 1;
                    if (!empty($productSliderImages)):
                        foreach ($productSliderImages as $image): ?>
                            <div class="swiper-slide">
                                <img src="<?= !empty($image['url_thumb']) ? esc($image['url_thumb']) : ''; ?>" alt="<?= esc($title) . ' ' . $i; ?>">
                            </div>
                            <?php $i++;
                        endforeach;
                    endif; ?>

                    <?php if ($hasVideo): ?>
                        <div class="swiper-slide no-thumb-sync">
                            <button type="button" data-toggle="modal" data-target="#productVideoModal" aria-label="button video modal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-play-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                    <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445"/>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasAudio): ?>
                        <div class="swiper-slide no-thumb-sync">
                            <button type="button" data-toggle="modal" data-target="#productAudioModal" aria-label="button audio modal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-music-note" viewBox="0 0 16 16">
                                    <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                                    <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                                    <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="slider-for-container">
            <div id="product-slider" class="swiper product-slider">
                <div class="swiper-wrapper">
                    <?php if (!empty($productSliderImages)):
                        foreach ($productSliderImages as $image):?>
                            <div class="swiper-slide">
                                <a href="<?= !empty($image['url_full']) ? esc($image['url_full']) : ''; ?>" class="glightbox-product" data-gallery="product-gallery">
                                    <img src="<?= !empty($image['url_main']) ? esc($image['url_main']) : ''; ?>" alt="<?= esc($title); ?>">
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

    <div class="product-slider-mobile-media-buttons">
        <?php if ($hasVideo): ?>
            <div class="media-item">
                <button type="button" data-toggle="modal" data-target="#productVideoModal" aria-label="button video modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-play-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445"/>
                    </svg>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($hasAudio): ?>
            <div class="media-item">
                <button type="button" data-toggle="modal" data-target="#productAudioModal" aria-label="button audio modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-music-note" viewBox="0 0 16 16">
                        <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2"/>
                        <path fill-rule="evenodd" d="M9 3v10H8V3z"/>
                        <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4L8 5z"/>
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </div>


<?php elseif ($hasVideo): ?>
    <div class="product-video-preview">
        <video id="player" playsinline controls>
            <source src="<?= getProductVideoUrl($video); ?>" type="video/mp4">
        </video>
    </div>
<?php elseif ($hasAudio):
    echo view('product/details/_audio_player');
endif; ?>


<?php if ($hasVideo && $showSlider): ?>
    <div class="modal fade" id="productVideoModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-product-video" role="document">
            <div class="modal-content">
                <button type="button" class="close" data-dismiss="modal" aria-label="close-video-modal"><i class="icon-close"></i></button>
                <div class="product-video-preview m-0">
                    <video id="player" playsinline controls>
                        <source src="<?= getProductVideoUrl($video); ?>" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
    </div>
<?php endif;
if ($hasAudio && $showSlider): ?>
    <div class="modal fade" id="productAudioModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-product-video" role="document">
            <div class="modal-content">
                <div class="row-custom" style="width: auto !important;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close-audio-modal"><i class="icon-close"></i></button>
                    <?= view('product/details/_audio_player'); ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

