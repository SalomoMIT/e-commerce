<?php
$jsConfig = [
    'baseUrl' => base_url(),
    'csrfTokenName' => csrf_token(),
    'sysLangId' => (int)$activeLang->id,
    'directionality' => $activeLang->text_direction == 'rtl' ? 'rtl' : 'ltr',
    'decimalSeparator' => $baseVars->decimalSeparator,
    'currencySymbol' => $defaultCurrency->symbol,
    'backUrl' => getCurrentUrl(false),
    'commissionRate' => (float)$paymentSettings->commission_rate,
    'imageUploadLimit' => (int)$productSettings->product_image_limit,
    'text' => [
        'ok' => trans("ok"),
        'cancel' => trans("cancel"),
        'none' => trans("none"),
        'processing' => trans("processing"),
        'selectImage' => trans("select_image"),
        'tagInput' => trans("type_tag"),
        'topicEmpty' => trans("msg_topic_empty"),
        'select' => trans("select"),
        'noResultsFound' => trans("no_results_found"),
        'enterTwoCharacters' => trans("enter_two_characters"),
        'searching' => trans("searching"),
        'acceptTerms' => trans("msg_accept_terms"),
        'selectCategory' => trans("select_category"),
        'typeTag' => trans("type_tag"),
        'cost' => trans("cost"),
        'minWeight' => trans("min_weight"),
        'maxWeight' => trans("max_weight"),
        'kg' => trans("kg"),
    ]
]; ?>
<script>window.MdsConfig = <?= json_encode($jsConfig, JSON_UNESCAPED_SLASHES); ?>;</script>