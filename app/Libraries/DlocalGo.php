<?php

namespace App\Libraries;

use App\Models\CheckoutModel;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;

/**
 * dLocal Go Payment Gateway Library for CodeIgniter 4.
 * This library handles payment creation and webhook notifications.
 * The webhook logic is based on receiving a payment_id and then fetching
 * the trusted details via a secure API call.
 *
 */
class DlocalGo
{
    protected string $publicKey;
    protected string $secretKey;
    protected string $endpoint;
    protected $logger;

    /**
     * Initializes the dLocal Go library.
     *
     * @param object $config Must contain: public_key, secret_key, environment ('production' or 'sandbox')
     */
    public function __construct(object $config)
    {
        $this->publicKey = $config->public_key ?? '';
        $this->secretKey = $config->secret_key ?? '';
        $this->logger = Services::logger();
        $mode = $config->environment ?? 'sandbox';

        $this->endpoint = $mode === 'production'
            ? 'https://api.dlocalgo.com/v1'
            : 'https://api-sbx.dlocalgo.com/v1';
    }

    /**
     * Generates a dLocal Go payment redirect URL from a checkout object.
     *
     * @param object $checkout The checkout object from your CheckoutModel.
     * @return string|null Redirect URL on success, or null on failure.
     */
    public function generatePaymentRedirectUrl(object $checkout): ?string
    {
        if (empty($checkout->currency_code) || empty($checkout->grand_total) || empty($checkout->id)) {
            $this->logger->error('[dLocal Go] Missing required checkout data for payment URL generation.');
            return null;
        }

        $dlocalGoCountryCodes = [
            'ARS' => 'AR', 'BOB' => 'BO', 'BRL' => 'BR', 'CLP' => 'CL', 'COP' => 'CO',
            'CRC' => 'CR', 'DOP' => 'DO', 'GTQ' => 'GT', 'MXN' => 'MX', 'PEN' => 'PE',
            'PYG' => 'PY', 'UYU' => 'UY', 'USD' => 'US',
        ];

        $currency = strtoupper($checkout->currency_code);
        $country = $dlocalGoCountryCodes[$currency] ?? null;

        if (!$country) {
            $this->logger->error("[dLocal Go] No country mapping found for currency: {$currency}");
            return null;
        }

        $paymentData = [
            'currency' => $currency,
            'amount' => $checkout->grand_total,
            'country' => $country,
            'order_id' => (string)$checkout->checkout_token,
            'description' => getCheckoutPaymentTitle($checkout),
            'success_url' => langBaseUrl('checkout/complete-dlocalgo-payment') . '?token=' . $checkout->checkout_token,
            'back_url' => generateUrl('cart', 'payment'),
            'notification_url' => base_url('payment/webhook/dlocalgo'),
        ];

        $response = $this->createPayment($paymentData);

        if (!empty($response['redirect_url'])) {
            return $response['redirect_url'];
        }

        $errorMessage = $response['message'] ?? json_encode($response);
        $this->logger->error('[dLocal Go] Payment creation failed: ' . $errorMessage, $response);
        return null;
    }

    /**
     * Handles incoming webhook notifications by fetching payment details using the provided payment_id.
     *
     * @param IncomingRequest $request
     * @return array|null The validated and complete payment payload if successful, otherwise null.
     */
    public function handleWebhook(IncomingRequest $request): ?array
    {
        $rawPayload = $request->getBody();
        $jsonPayload = json_decode($rawPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('[dLocal Go Webhook] Invalid JSON in payload.', ['payload' => $rawPayload]);
            return null;
        }

        // The primary logic expects a 'payment_id'.
        if (empty($jsonPayload['payment_id'])) {
            $this->logger->error('[dLocal Go Webhook] Received webhook without a payment_id.', ['payload' => $rawPayload]);
            return null;
        }

        $paymentId = $jsonPayload['payment_id'];

        // Fetch the full, trusted payment details directly from the API.
        $paymentDetails = $this->getPaymentDetails($paymentId);

        if (empty($paymentDetails)) {
            $this->logger->error('[dLocal Go Webhook] Failed to fetch details for payment_id: ' . $paymentId);
            return null;
        }

        // Check the status from the trusted data we just fetched.
        $paymentStatus = isset($paymentDetails['status']) ? strtoupper($paymentDetails['status']) : null;
        if ($paymentStatus === 'PAID' || $paymentStatus === 'COMPLETED') {
            return $paymentDetails; // Return the full details fetched from the API
        }

        $this->logger->warning('[dLocal Go Webhook] Fetched details, but payment status is not final.', [
            'payment_id' => $paymentId,
            'status' => $paymentStatus
        ]);
        return null;
    }

    /**
     * Retrieves the details of a specific payment from dLocal Go API.
     *
     * @param string $paymentId The ID of the payment (e.g., "DP-107171").
     * @return array|null The payment details on success, or null on failure.
     */
    public function getPaymentDetails(string $paymentId): ?array
    {
        if (empty($paymentId)) {
            return null;
        }
        return $this->sendRequest('GET', '/payments/' . $paymentId);
    }

    /**
     * Creates a payment request to dLocal Go.
     *
     * @param array $data
     * @return array|null
     */
    private function createPayment(array $data): ?array
    {
        return $this->sendRequest('POST', '/payments', $data);
    }

    /**
     * Sends a request to the dLocal Go API.
     *
     * @param string $method 'GET' or 'POST'
     * @param string $path The API endpoint path.
     * @param array|null $data The data to send for POST requests.
     * @return array|null
     */
    private function sendRequest(string $method, string $path, ?array $data = null): ?array
    {
        $url = $this->endpoint . $path;
        $authorization = 'Bearer ' . $this->publicKey . ':' . $this->secretKey;
        $uniqueRequestId = uniqid('req_');

        $headers = [
            'Authorization: ' . $authorization,
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Idempotency-Key: ' . $uniqueRequestId,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $this->logger->error("[dLocal Go] cURL error for {$url}: " . curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $decodedResponse = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error("[dLocal Go] Failed to decode JSON response from {$url}. HTTP Status: {$httpCode}", ['body' => $responseBody]);
            return null;
        }

        return $decodedResponse;
    }
}