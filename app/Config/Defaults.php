<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Defaults extends BaseConfig
{

    /**
     * List of cloud storages
     */
    public static array $cloudStorages = [
        'aws_s3',
        'backblaze_b2',
        'cloudflare_r2'
    ];

    /**
     * List of allowed mail services with their labels
     */
    public static array $emailServices = [
        'php-mailer' => 'PHPMailer',
        'codeigniter' => 'CodeIgniter Mail',
        'brevo' => 'Brevo (Sendinblue)',
        'mailgun' => 'Mailgun',
    ];

    //form input keys
    public static array $formInputKeys = [
        'storage' => [
            'storage' => 'text',
            'aws_key' => 'text',
            'aws_secret' => 'text',
            'aws_bucket' => 'text',
            'aws_region' => 'text',
            'b2_key' => 'text',
            'b2_secret' => 'text',
            'b2_bucket' => 'text',
            'b2_endpoint_url' => 'text',
            'b2_public_url' => 'text',
            'r2_key' => 'text',
            'r2_secret' => 'text',
            'r2_bucket' => 'text',
            'r2_endpoint_url' => 'text',
            'r2_public_url' => 'text'
        ],

        'affiliate' => [
            'status' => 'bool',
            'type' => 'text',
            'image' => 'file',
            'storage' => 'file',
            'commission_rate' => 'number',
            'discount_rate' => 'number'
        ],

        'email' => [
            'mail_service' => 'text',
            'mail_protocol' => 'text',
            'mail_encryption' => 'text',
            'mail_host' => 'text',
            'mail_port' => 'text',
            'mail_username' => 'text',
            'mail_password' => 'text',
            'mail_reply_to' => 'text',
            'mail_title' => 'text',
            'mailgun_api_key' => 'text',
            'mailgun_region' => 'text',
            'mailgun_domain' => 'text',
            'mailgun_sender_email' => 'text',
            'brevo_api_key' => 'text'
        ],

        'payout' => [
            'paypal_email' => 'text',
            'btc_address' => 'text',
            'iban_full_name' => 'text',
            'iban_country_id' => 'text',
            'iban_bank_name' => 'text',
            'iban_number' => 'text',
            'swift_full_name' => 'text',
            'swift_address' => 'text',
            'swift_state' => 'text',
            'swift_city' => 'text',
            'swift_postcode' => 'text',
            'swift_country_id' => 'text',
            'swift_bank_account_holder_name' => 'text',
            'swift_iban' => 'text',
            'swift_code' => 'text',
            'swift_bank_name' => 'text',
            'swift_bank_branch_city' => 'text',
            'swift_bank_branch_country_id' => 'text'
        ],

        'watermark' => [
            'w_text' => 'text',
            'w_font_size' => 'text',
            'w_product_images' => 'bool',
            'w_blog_images' => 'bool',
            'w_thumbnail_images' => 'bool',
            'w_vrt_alignment' => 'text',
            'w_hor_alignment' => 'text'
        ],

        'newsletter' => [
            'status' => 'bool',
            'is_popup_active' => 'bool',
            'image' => 'file',
            'storage' => 'file'
        ]
    ];

    /**
     * Default permission structure
     */
    public static array $rolePermissions = [
        '1' => 'admin_panel',
        '2' => 'vendor',
        '3' => 'theme',
        '4' => 'slider',
        '5' => 'homepage_manager',
        '6' => 'orders',
        '7' => 'digital_sales',
        '8' => 'earnings',
        '9' => 'payouts',
        '10' => 'refund_requests',
        '11' => 'products',
        '12' => 'quote_requests',
        '13' => 'categories',
        '14' => 'custom_fields',
        '15' => 'pages',
        '16' => 'blog',
        '17' => 'location',
        '18' => 'membership',
        '19' => 'help_center',
        //'20' => 'storage',
        '21' => 'cache_system',
        '22' => 'seo_tools',
        '23' => 'ad_spaces',
        '24' => 'contact_messages',
        '25' => 'reviews',
        '26' => 'comments',
        '27' => 'abuse_reports',
        '28' => 'newsletter',
        '29' => 'preferences',
        '30' => 'general_settings',
        '31' => 'product_settings',
        '32' => 'payment_settings',
        '33' => 'brands',
        '34' => 'chat_messages',
        '35' => 'payments',
        '36' => 'tags',
        '37' => 'ai_writer'
    ];

    /**
     * Continents
     */
    public static array $continents = [
        'EU' => 'Europe',
        'AS' => 'Asia',
        'AF' => 'Africa',
        'NA' => 'North America',
        'SA' => 'South America',
        'OC' => 'Oceania',
        'AN' => 'Antarctica'
    ];

    //safe extensions for file uploads
    public static array $safeExtensions = [
        // Documents
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt',

        // Images
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico',

        // Archives
        'zip', 'rar', '7z', 'tar', 'gz', 'bz2',

        // Config / Code
        'html', 'css', 'js', 'json', 'xml', 'csv', 'yml', 'md',

        // Media
        'mp3', 'wav', 'mp4', 'mov', 'avi', 'mkv'
    ];

    /**
     * Exception list: Currencies with few digits that should be displayed inline.
     * The default layout for all other currencies is stacked (multi-line).
     */
    public static array $shortCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'CHF'];

    /**
     * Text editor language options
     */
    public static array $editorLanguageOptions = [
        ["short" => "ar", "name" => "Arabic"],
        ["short" => "hy", "name" => "Armenian"],
        ["short" => "az", "name" => "Azerbaijani"],
        ["short" => "eu", "name" => "Basque"],
        ["short" => "be", "name" => "Belarusian"],
        ["short" => "bn_BD", "name" => "Bengali (Bangladesh)"],
        ["short" => "bs", "name" => "Bosnian"],
        ["short" => "bg_BG", "name" => "Bulgarian"],
        ["short" => "ca", "name" => "Catalan"],
        ["short" => "zh_CN", "name" => "Chinese (China)"],
        ["short" => "zh_TW", "name" => "Chinese (Taiwan)"],
        ["short" => "hr", "name" => "Croatian"],
        ["short" => "cs", "name" => "Czech"],
        ["short" => "da", "name" => "Danish"],
        ["short" => "dv", "name" => "Divehi"],
        ["short" => "nl", "name" => "Dutch"],
        ["short" => "en", "name" => "English"],
        ["short" => "et", "name" => "Estonian"],
        ["short" => "fo", "name" => "Faroese"],
        ["short" => "fi", "name" => "Finnish"],
        ["short" => "fr_FR", "name" => "French"],
        ["short" => "gd", "name" => "Gaelic, Scottish"],
        ["short" => "gl", "name" => "Galician"],
        ["short" => "ka_GE", "name" => "Georgian"],
        ["short" => "de", "name" => "German"],
        ["short" => "el", "name" => "Greek"],
        ["short" => "he", "name" => "Hebrew"],
        ["short" => "hi_IN", "name" => "Hindi"],
        ["short" => "hu_HU", "name" => "Hungarian"],
        ["short" => "is_IS", "name" => "Icelandic"],
        ["short" => "id", "name" => "Indonesian"],
        ["short" => "it", "name" => "Italian"],
        ["short" => "ja", "name" => "Japanese"],
        ["short" => "kab", "name" => "Kabyle"],
        ["short" => "kk", "name" => "Kazakh"],
        ["short" => "km_KH", "name" => "Khmer"],
        ["short" => "ko_KR", "name" => "Korean"],
        ["short" => "ku", "name" => "Kurdish"],
        ["short" => "lv", "name" => "Latvian"],
        ["short" => "lt", "name" => "Lithuanian"],
        ["short" => "lb", "name" => "Luxembourgish"],
        ["short" => "ml", "name" => "Malayalam"],
        ["short" => "mn", "name" => "Mongolian"],
        ["short" => "nb_NO", "name" => "Norwegian Bokmål (Norway)"],
        ["short" => "fa", "name" => "Persian"],
        ["short" => "pl", "name" => "Polish"],
        ["short" => "pt_BR", "name" => "Portuguese (Brazil)"],
        ["short" => "pt_PT", "name" => "Portuguese (Portugal)"],
        ["short" => "ro", "name" => "Romanian"],
        ["short" => "ru", "name" => "Russian"],
        ["short" => "sr", "name" => "Serbian"],
        ["short" => "si_LK", "name" => "Sinhala (Sri Lanka)"],
        ["short" => "sk", "name" => "Slovak"],
        ["short" => "sl_SI", "name" => "Slovenian (Slovenia)"],
        ["short" => "es", "name" => "Spanish"],
        ["short" => "es_MX", "name" => "Spanish (Mexico)"],
        ["short" => "sv_SE", "name" => "Swedish (Sweden)"],
        ["short" => "tg", "name" => "Tajik"],
        ["short" => "ta", "name" => "Tamil"],
        ["short" => "tt", "name" => "Tatar"],
        ["short" => "th_TH", "name" => "Thai"],
        ["short" => "tr", "name" => "Turkish"],
        ["short" => "ug", "name" => "Uighur"],
        ["short" => "uk", "name" => "Ukrainian"],
        ["short" => "vi", "name" => "Vietnamese"],
        ["short" => "cy", "name" => "Welsh"]
    ];
}
