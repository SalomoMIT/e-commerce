<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class PaymentGateways extends BaseConfig
{
    /**
     * Supported currencies for all payment gateways
     */
    public static array $currencies = [
        'paypal' => [
            'AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY',
            'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK',
            'CHF', 'THB', 'USD'
        ],

        'stripe' => [
            'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM',
            'BBD', 'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BZD',
            'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK',
            'DOP', 'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP', 'GMD',
            'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR',
            'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW', 'KYD', 'KZT', 'LAK',
            'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP',
            'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR',
            'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD',
            'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD',
            'STD', 'SZL', 'THB', 'TJS', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX',
            'USD', 'UYU', 'UZS', 'VES', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF',
            'YER', 'ZAR', 'ZMW'
        ],

        'paystack' => ['NGN', 'GHS', 'ZAR', 'USD'],

        'razorpay' => ['INR', 'USD', 'EUR', 'SGD', 'AED', 'MYR', 'BHD', 'OMR', 'QAR', 'SAR', 'KWD'],

        'flutterwave' => ['NGN', 'GHS', 'USD', 'ZAR', 'TZS', 'KES', 'UGX', 'RWF', 'XAF', 'XOF', 'EUR', 'GBP'],

        'iyzico' => ['TRY', 'EUR', 'USD', 'GBP'],

        'midtrans' => ['IDR'],

        'dlocalgo' => ['ARS', 'BOB', 'BRL', 'CLP', 'COP', 'CRC', 'DOP', 'GTQ', 'MXN', 'PEN', 'PYG', 'UYU', 'USD'],

        'mercado_pago' => ['ARS', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'UYU'],

        'paytabs' => ['AED', 'EGP', 'EUR', 'GBP', 'IQD', 'JOD', 'KWT', 'OMR', 'SAR', 'TRY', 'USD'],

        'yoomoney' => ['RUB', 'USD', 'EUR', 'BYN', 'KZT', 'CNY', 'GBP', 'CHF', 'CZK', 'PLN', 'JPY'],
    ];
}