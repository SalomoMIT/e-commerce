<?php $newsletterSettings = getSettingsUnserialized('newsletter');
echo view("partials/_modals", ['newsletterSettings' => $newsletterSettings]); ?>

<footer id="footer">
<div class="container">
<div class="row">
<div class="col-12">
<div class="footer-top">
<div class="row">
<div class="col-12 col-lg-4 footer-widget">
<div class="row-custom">
    <div class="footer-logo">
        <a href="<?= langBaseUrl(); ?>"><img src="<?= getLogo(); ?>" alt="logo" width="<?= $baseVars->logoWidth; ?>" height="<?= $baseVars->logoHeight; ?>"></a>
    </div>
</div>
<div class="row-custom">
    <div class="footer-about">
        <?= $baseSettings->about_footer; ?>
    </div>
    <div class="footer-social-links">
        <?php $socialLinks = getSocialLinksArray($baseSettings, false);
        if (!empty($socialLinks)):?>
            <ul>
                <?php foreach ($socialLinks as $socialLink):
                    if (!empty($socialLink['value'])): ?>
                        <li><a href="<?= esc($socialLink['value']); ?>" target="_blank" title="<?= esc(ucfirst($socialLink['name'])); ?>"><i class="icon-<?= esc($socialLink['name']); ?>"></i></a></li>
                    <?php endif;
                endforeach;
                if ($generalSettings->rss_system == 1): ?>
                    <li><a href="<?= generateUrl('rss_feeds'); ?>" class="rss" target="_blank" title="<?= trans("rss_feeds"); ?>"><i class="icon-rss"></i></a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
</div>
<div class="col-12 col-lg-8">
<div class="row">
    <div class="col-12 col-lg-7">
        <div class="row">
            <div class="col-12 col-sm-6 col-lg-6 footer-widget">
                <div class="nav-footer">
                    <div class="row-custom">
                        <h4 class="footer-title"><?= trans("categories"); ?></h4>
                    </div>
                    <div class="row-custom">
                        <?php $i = 0;
                        if (!empty($parentCategories)): ?>
                            <ul>
                                <?php foreach ($parentCategories as $category):
                                    if ($category->show_on_main_menu == 1 && $i < 12): ?>
                                        <li><a href="<?= generateCategoryUrl($category); ?>"><?= esc($category->cat_name); ?></a></li>
                                    <?php endif;
                                    $i++;
                                endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-6 footer-widget">
                <div class="nav-footer">
                    <div class="row-custom">
                        <h4 class="footer-title"><?= trans("footer_quick_links"); ?></h4>
                    </div>
                    <div class="row-custom">
                        <ul>
                            <li><a href="<?= langBaseUrl(); ?>"><?= trans("home"); ?></a></li>
                            <?php if (!empty($menuLinks)):
                                foreach ($menuLinks as $menuLink):
                                    if ($menuLink->location == 'quick_links'):
                                        $itemLink = generateMenuItemUrl($menuLink);
                                        if (!empty($menuLink->page_default_name)):
                                            $itemLink = generateUrl($menuLink->page_default_name);
                                        endif; ?>
                                        <li><a href="<?= $itemLink; ?>"><?= esc($menuLink->title); ?></a></li>
                                    <?php endif;
                                endforeach;
                            endif;
                            if (getSettingsUnserialized('affiliate')->status == 1): ?>
                                <li><a href="<?= generateUrl('affiliate-program'); ?>"><?= trans("affiliate_program"); ?></a></li>
                            <?php endif; ?>
                            <li><a href="<?= generateUrl('help_center'); ?>"><?= trans("help_center"); ?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="nav-footer">
                    <div class="row-custom m-t-15">
                        <h4 class="footer-title"><?= trans("footer_information"); ?></h4>
                    </div>
                    <div class="row-custom">
                        <ul>
                            <?php if (!empty($menuLinks)):
                                foreach ($menuLinks as $menuLink):
                                    if ($menuLink->location == 'information'):
                                        $itemLink = generateMenuItemUrl($menuLink);
                                        if (!empty($menuLink->page_default_name)):
                                            $itemLink = generateUrl($menuLink->page_default_name);
                                        endif; ?>
                                        <li><a href="<?= $itemLink; ?>"><?= esc($menuLink->title); ?></a></li>
                                    <?php endif;
                                endforeach;
                            endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="footer-widget">
            <?php if ($newsletterSettings->status == 1): ?>
                <div class="newsletter-footer">
                    <h4 class="footer-title"><?= trans("newsletter"); ?></h4>
                    <p class="title-desc"><?= trans("newsletter_desc"); ?></p>
                    <form id="form_newsletter_footer" class="form-newsletter-footer">
                        <input type="email" name="email" class="form-input" maxlength="249" placeholder="<?= trans("enter_email"); ?>" required>
                        <button type="submit" name="submit" value="form" class="btn btn-custom"><?= trans("subscribe"); ?></button>
                        <input type="text" name="url">
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php $envPaymentIcons = env('PAYMENT_ICONS');
        if (!empty($envPaymentIcons)):
            $paymentIconsArray = explode(',', $envPaymentIcons ?? '');
            if (!empty($paymentIconsArray) && countItems($paymentIconsArray) > 0):?>
                <div class="footer-payment-icons">
                    <?php foreach ($paymentIconsArray as $icon):
                        if (file_exists(FCPATH . 'assets/img/payment/' . $icon . '.svg')):?>
                            <img data-src="<?= base_url('assets/img/payment/' . $icon . '.svg'); ?>" alt="<?= $icon; ?>" width="30" height="22" class="lazyload">
                        <?php endif;
                    endforeach; ?>
                </div>
            <?php
            endif;
        endif; ?>
    </div>
</div>
</div>
</div>
</div>
<div class="footer-bottom">
<div class="row">
<div class="col-lg-4 col-md-12">
<div class="copyright">
    <?= esc($baseSettings->copyright); ?>
</div>
</div>
<div class="col-lg-8 col-md-12">
<ul class="nav-footer-bottom">
    <?php if (!empty($menuLinks)):
        foreach ($menuLinks as $menuLink):
            if ($menuLink->location == 'footer_bottom'):
                $itemLink = generateMenuItemUrl($menuLink);
                if (!empty($menuLink->page_default_name)):
                    $itemLink = generateUrl($menuLink->page_default_name);
                endif; ?>
                <li><a href="<?= $itemLink; ?>"><?= esc($menuLink->title); ?></a></li>
            <?php endif;
        endforeach;
    endif; ?>
</ul>
</div>
</div>
</div>
</div>
</div>
</div>
</footer>
<?php if (empty(helperGetCookie('cks_warning')) && $baseSettings->cookies_warning): ?>
<div class="cookies-warning">
<button type="button" aria-label="close" class="close" onclick="hideCookiesWarning();"><i class="icon-close"></i></button>
<div class="text">
<?= $baseSettings->cookies_warning_text; ?>
</div>
<button type="button" class="btn btn-md btn-block" aria-label="close" onclick="hideCookiesWarning();"><?= trans("accept_cookies"); ?></button>
</div>
<?php endif; ?>
<button type="button" class="scrollup" aria-label="scroll-up"><i class="icon-arrow-up"></i></button>
<script src="<?= base_url('assets/js/jquery-3.5.1.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/plugins-2.6.js'); ?>"></script>
<script src="<?= base_url('assets/common/js/utils.min.js'); ?>"></script>
<?= view("cart/_payment_js.php"); ?>
<script src="<?= base_url('assets/js/script-2.6.2.js'); ?>"></script>
<script>$('<input>').attr({type: 'hidden', name: 'sysLangId', value: '<?=selectedLangId(); ?>'}).appendTo('form[method="post"]');</script>
<?php if ($generalSettings->pwa_status == 1): ?>
<script>if ('serviceWorker' in navigator) {window.addEventListener('load', function () {navigator.serviceWorker.register('<?= base_url('pwa-sw.js');?>').then(function (registration) {}, function (err) {console.log('ServiceWorker registration failed: ', err);}).catch(function (err) {console.log(err);});});} else {console.log('service worker is not supported');}</script>
<?php endif; ?>
<?php if (!empty($video) || !empty($audio)): ?>
<script src="<?= base_url('assets/vendor/plyr/plyr.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/plyr/plyr.polyfilled.min.js'); ?>"></script>
<script>
$(document).bind('ready ajaxComplete', function () {
const player = new Plyr('#player');
const audio_player = new Plyr('#audio_player');
});
$(document).ready(function () {
setTimeout(function () {
$(".product-video-preview").css("opacity", "1");
}, 300);
setTimeout(function () {
$(".product-audio-preview").css("opacity", "1");
}, 300);
});</script>
<?php endif;
if (!empty($loadSupportEditor)):
echo view('support/_editor');
endif; ?>
<?php if (checkNewsletterModal($newsletterSettings)): ?>
<script>$(window).on('load', function () {
$('#modal_newsletter').modal('show');
});</script>
<?php endif; ?>
<?= $generalSettings->google_analytics; ?>
<?= $generalSettings->custom_footer_codes; ?>
</body>
</html>