<?php

/**
 * This file is part of a CodeIgniter 4 application.
 */

namespace App\Libraries;

use Config\Services;
use Exception;
use InvalidArgumentException;

/**
 * Midtrans Integration Library for CodeIgniter 4
 *
 * This class provides a structured way to interact with the Midtrans API,
 * handling Snap Token creation and server-side payment verification.
 *
 * @see https://docs.midtrans.com/en/snap/advanced-feature
 */
class Midtrans
{
    /**
     * The Midtrans Server Key.
     *
     * @var string
     */
    private string $serverKey;

    /**
     * The API environment ('sandbox' or 'production').
     *
     * @var string
     */
    private string $environment;

    /**
     * An instance of CodeIgniter's HTTP Client.
     *
     * @var \CodeIgniter\HTTP\CURLRequest
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * Initializes the Midtrans library with the necessary credentials.
     *
     * @param object $config An object containing 'secret_key' and 'environment'.
     *
     * @throws InvalidArgumentException If required keys are missing.
     */
    public function __construct(object $config)
    {
        if (empty($config->secret_key) || empty($config->environment)) {
            throw new InvalidArgumentException('Midtrans Server Key and Environment are required.');
        }

        $this->serverKey = $config->secret_key;
        $this->environment = $config->environment;

        $this->httpClient = Services::curlrequest(['timeout' => 15]);

        // Load the Midtrans PHP SDK once when the library is initialized.
        require_once APPPATH . 'ThirdParty/midtrans/vendor/autoload.php';
    }

    /**
     * Creates a Snap Token for the Midtrans Snap.js UI.
     *
     * @param string $orderId         Your unique order/checkout token.
     * @param int    $grossAmount     The transaction amount as an integer.
     * @param object $customerDetails An object containing customer's first_name, last_name, email, and phone.
     * @return string The generated Snap Token.
     * @throws Exception If token generation fails.
     */
    public function createSnapToken(string $orderId, int $grossAmount, object $customerDetails): string
    {
        // Construct the parameters array inside the library method for better encapsulation.
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customerDetails->first_name ?? '',
                'last_name' => $customerDetails->last_name ?? '',
                'email' => $customerDetails->email ?? '',
                'phone' => $customerDetails->phone_number ?? '',
            ],
        ];

        try {
            // Configure the Midtrans SDK with the instance properties.
            \Midtrans\Config::$serverKey = $this->serverKey;
            \Midtrans\Config::$isProduction = ($this->environment === 'production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Generate and return the Snap Token.
            return \Midtrans\Snap::getSnapToken($params);

        } catch (Exception $e) {
            log_message('error', '[MidtransLib] Snap Token generation failed: ' . $e->getMessage());
            // Re-throw the exception so the calling controller can handle it.
            throw $e;
        }
    }

    /**
     * Verifies a payment by its transaction ID using the Midtrans Status API.
     *
     * @param string $transactionId The 'transaction_id' returned by Midtrans Snap.
     *
     * @return object|null The full response object from Midtrans on success, or null on failure.
     * @throws Exception If the API call fails or returns an error status.
     */
    public function verifyPayment(string $transactionId): ?object
    {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty.');
        }

        // Determine the correct API URL based on the environment.
        $baseUrl = ($this->environment === 'production')
            ? 'https://api.midtrans.com/v2/'
            : 'https://api.sandbox.midtrans.com/v2/';

        $url = $baseUrl . $transactionId . '/status';

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':')
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('Midtrans API returned a non-200 status code: ' . $response->getStatusCode());
            }

            $body = json_decode($response->getBody());

            // Check for Midtrans-specific error codes in the response body
            if (isset($body->status_code) && $body->status_code >= 400) {
                throw new Exception('Midtrans API error: ' . ($body->status_message ?? 'Unknown error'));
            }

            return $body;

        } catch (Exception $e) {
            log_message('error', '[MidtransLib] Payment verification failed: ' . $e->getMessage());
            throw $e;
        }
    }
}