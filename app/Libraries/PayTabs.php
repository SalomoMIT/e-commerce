<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use Config\Services;
use Exception;
use InvalidArgumentException;

/**
 * PayTabs Integration Library for CodeIgniter 4 (MVC Compliant)
 *
 * This class provides methods to interact with the PayTabs API.
 * It's designed to be called from a controller, which then passes
 * data to the view.
 */
class PayTabs
{
    private const API_BASE_URL = 'https://secure-global.paytabs.com/';
    private int $profileId;
    private string $serverKey;
    private $httpClient;
    private RequestInterface $request;

    /**
     * Constructor.
     *
     * @param object $config An object containing 'profile_id' and 'server_key'.
     */
    public function __construct(object $config)
    {
        if (empty($config->public_key) || empty($config->secret_key)) {
            throw new InvalidArgumentException('PayTabs Profile ID and Server Key are required.');
        }

        $this->profileId = (int)$config->public_key;
        $this->serverKey = $config->secret_key;
        $this->httpClient = Services::curlrequest(['timeout' => 20]);
        $this->request = Services::request();
    }

    /**
     * Creates a payment page and returns only the redirect URL.
     *
     * @param object $checkout The checkout object.
     * @param object $customer The customer data object.
     * @param string $appName  The application name for the description.
     * @return string The redirect URL to the PayTabs payment page.
     * @throws Exception If the API call fails or does not return a redirect URL.
     */
    public function createPayPage(object $checkout, object $customer, string $appName): string
    {
        $url = self::API_BASE_URL . 'payment/request';

        $params = [
            'profile_id'        => $this->profileId,
            'tran_type'         => 'sale',
            'tran_class'        => 'ecom',
            'cart_id'           => $checkout->checkout_token,
            'cart_description'  => "Order from " . $appName,
            'cart_currency'     => $checkout->currency_code,
            'cart_amount'       => numToDecimal($checkout->grand_total),
            'return'            => base_url('mds-cl-paytabs-payment-redirect'), // Your success/failure redirect page
            'callback'          => base_url('payment/webhook/paytabs'),
            'customer_details'  => [
                "name"  => $customer->first_name . ' ' . $customer->last_name,
                "email" => $customer->email,
                "phone" => $customer->phone_number,
                "street1" => $customer->address,
                "city" => $customer->city ?? '',
                "state" => $customer->state ?? '',
                "country" => $customer->country_iso ?? '',
                "zip" => $customer->zip_code ?? ''
            ],
            'hide_shipping'     => true,
        ];

        $response = $this->sendRequest($url, $params);

        if (!empty($response->redirect_url)) {
            return $response->redirect_url;
        }

        $errorMessage = $response->message ?? 'PayTabs API did not return a redirect URL.';
        throw new Exception($errorMessage);
    }

    /**
     * Verifies a payment by its Transaction Reference.
     *
     * @param string $transactionRef The 'tran_ref' from PayTabs.
     * @return object The full transaction details object.
     * @throws Exception
     */
    public function verifyPayment(string $transactionRef): object
    {
        $url = self::API_BASE_URL . 'payment/query';
        $postData = ['profile_id' => $this->profileId, 'tran_ref' => $transactionRef];
        return $this->sendRequest($url, $postData);
    }

    /**
     * Handles all webhook logic internally.
     * Gets request data, validates signature, checks payment status, and returns
     * the transaction reference string only on complete success.
     *
     * @return string|null The transaction reference ('tran_ref') on success, or null on any failure.
     */
    public function handleWebhook(): ?string
    {
        // Step 1: Get data directly from the injected request service.
        $rawPayload = $this->request->getBody();
        $signature = $this->request->getHeaderLine('Signature');

        if (empty($rawPayload) || empty($signature)) {
            log_message('error', '[PayTabsLib] Webhook received with empty payload or signature.');
            return null;
        }

        // Step 2: Verify the signature to ensure the request is authentic.
        if (!$this->verifySignature($rawPayload, $signature)) {
            log_message('critical', '[PayTabsLib] Webhook with INVALID SIGNATURE received.');
            return null;
        }

        $data = json_decode($rawPayload);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', '[PayTabsLib] Failed to decode webhook JSON.');
            return null;
        }

        // Step 3: Verify that the payment was successful ('A' for Approved).
        $isPaymentSuccessful = isset($data->payment_result->response_status) && $data->payment_result->response_status === 'A';
        if (!$isPaymentSuccessful) {
            $tran_ref = $data->tran_ref ?? 'N/A';
            $status = $data->payment_result->response_status ?? 'N/A';
            log_message('info', "[PayTabsLib] Webhook for tran_ref {$tran_ref} was authentic, but payment status was not 'Approved' (Status: {$status}). No action taken.");
            return null;
        }

        // Step 4: Extract and return the transaction reference.
        if (empty($data->tran_ref)) {
            log_message('error', '[PayTabsLib] Verified successful payment webhook is missing a transaction reference.');
            return null;
        }

        // If all checks pass, return the transaction reference string.
        return $data->tran_ref;
    }

    /**
     * Verifies the webhook signature.
     *
     * @param string $payload The raw JSON payload from the request body.
     * @param string $receivedSignature The signature from the 'Signature' header.
     * @return bool True if the signature is valid, false otherwise.
     */
    private function verifySignature(string $payload, string $receivedSignature): bool
    {
        // Calculate the signature as a hexadecimal string (the default output of hash_hmac).
        // The third parameter of hash_hmac should be false or omitted.
        $calculatedSignature = hash_hmac('sha256', $payload, $this->serverKey);
        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * Sends a request to the PayTabs API.
     *
     * @param string $url The API endpoint URL.
     * @param array $data The data to be sent.
     * @return object The decoded JSON response.
     * @throws Exception
     */
    private function sendRequest(string $url, array $data): object
    {
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => $this->serverKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $data
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('PayTabs API returned a non-200 status code: ' . $response->getStatusCode());
            }

            $body = json_decode($response->getBody());

            if (empty($body)) {
                throw new Exception('PayTabs API returned an empty response.');
            }

            return $body;

        } catch (Exception $e) {
            log_message('error', '[PayTabsLib] API request failed: ' . $e->getMessage());
            throw $e;
        }
    }
}