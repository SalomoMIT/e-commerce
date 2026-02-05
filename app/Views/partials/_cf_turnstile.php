<?php if (!authCheck() && $generalSettings->turnstile_status): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <div class="form-group<?= !empty($turnstileCenter) ? ' text-center' : ''; ?>">
        <div class="cf-turnstile" data-theme="light" data-sitekey="<?= esc($generalSettings->turnstile_site_key); ?>" data-language="<?= esc($activeLang->short_form); ?>"></div>
        <div id="turnstile-error" class="text-danger font-600" style="visibility: hidden;"><?= trans("msg_verification_required"); ?></div>
    </div>
<?php endif; ?>