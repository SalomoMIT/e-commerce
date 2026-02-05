<?php
$font = getFontClient($activeFonts, 'site');
if (!empty($font)) {
    if ($font->font_source == 'local' && !empty($font->font_key)) {

        $fontCssUrl = base_url("assets/fonts/" . $font->font_key . ".css");
        echo '<link rel="stylesheet" href="' . $fontCssUrl . '">';

    } else if ($font->font_source == 'google' && !empty($font->font_url)) {

        echo $font->font_url;

        if (!empty($font->font_family)) {
            echo "<style>:root { " . $font->font_family . " }</style>\n";
        }
    }
} ?>