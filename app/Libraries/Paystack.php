<?php

namespace App\Libraries;

/**
 * A modern and robust Paystack PHP client.
 */
class Paystack
{
    /**
     * Base URL for the Paystack API.
     */
    private const API_BASE_URL = 'https://api.paystack.co';

    /**
     * The Paystack secret key.
     */
    private readonly string $secretKey;

    /**
     * Holds the configuration array passed to the constructor.
     * This allows for future options like custom timeouts.
     */
    private object $config;

    /**
     * Constructor.
     *
     * @param object $config Configuration object for the client. Must contain 'secret_key'.
     * @throws \InvalidArgumentException If the secret key is missing or empty in the config.
     */
    public function __construct(object $config)
    {
        // Extract the secret key from the configuration array.
        $secretKey = $config->secret_key ?? null;

        // Validate that the secret_key was provided and is a non-empty string.
        if (empty($secretKey) || !is_string($secretKey)) {
            throw new \InvalidArgumentException('A "secret_key" must be provided in the configuration array and cannot be empty.');
        }

        $this->secretKey = $secretKey;
        $this->config = $config; // Store the entire config for other potential uses.
    }

    /**
     * Verifies a transaction using its reference.
     *
     * @param string $reference The transaction reference to verify.
     * @return \stdClass The transaction data object on success.
     * @throws PaystackApiException If the API call fails or the transaction is not successful.
     * @throws \InvalidArgumentException If the reference is empty.
     */
    public function verifyTransaction(string $reference): \stdClass
    {
        if (empty(trim($reference))) {
            throw new \InvalidArgumentException('Transaction reference cannot be empty.');
        }

        $endpoint = '/transaction/verify/' . rawurlencode($reference);

        try {
            $response = $this->sendRequest($endpoint);
        } catch (\Exception $e) {
            throw new PaystackApiException('Failed to verify transaction: ' . $e->getMessage(), 0, $e);
        }

        if (!isset($response->status) || $response->status !== true) {
            $errorMessage = $response->message ?? 'Unknown API error';
            throw new PaystackApiException('Paystack API Error: ' . $errorMessage);
        }

        if (!isset($response->data->status) || $response->data->status !== 'success') {
            $transactionStatus = $response->data->status ?? 'unknown';
            throw new PaystackApiException("Transaction was not successful. Status: {$transactionStatus}");
        }

        return $response->data;
    }

    /**
     * Sends a request to the Paystack API using cURL.
     *
     * @param string $endpoint The API endpoint to call.
     * @param string $method The HTTP method.
     * @return \stdClass The decoded JSON response object.
     * @throws PaystackApiException On cURL errors or non-200 HTTP status codes.
     */
    private function sendRequest(string $endpoint, string $method = 'GET'): \stdClass
    {
        $curl = curl_init();

        $timeout = 30;

        curl_setopt_array($curl, [
            CURLOPT_URL            => self::API_BASE_URL . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer " . $this->secretKey,
                "Content-Type: application/json",
                "Cache-Control: no-cache"
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($error) {
            throw new PaystackApiException('cURL Error: ' . $error);
        }

        if ($httpCode >= 400) {
            throw new PaystackApiException("Paystack API returned an error. HTTP Status: {$httpCode}. Response: {$response}");
        }

        $decodedResponse = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PaystackApiException('Failed to decode JSON response from Paystack API.');
        }

        return $decodedResponse;
    }
}