<?php

/**
 * This file is part of a CodeIgniter 4 application.
 */

namespace App\Libraries;

use Config\Services;
use Exception;
use InvalidArgumentException;

/**
 * Flutterwave Integration Library for CodeIgniter 4
 *
 * This class provides a structured way to interact with the Flutterwave API,
 * focusing on the crucial server-side verification of payments.
 *
 * @see https://developer.flutterwave.com/docs/verifying-transactions
 */
class Flutterwave
{
    /**
     * The Flutterwave Secret Key.
     *
     * @var string
     */
    private string $secretKey;

    /**
     * An instance of CodeIgniter's HTTP Client.
     *
     * @var \CodeIgniter\HTTP\CURLRequest
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * Initializes the Flutterwave library with the necessary secret key.
     *
     * @param object $config An object containing the 'secret_key'.
     *
     * @throws InvalidArgumentException If the secret key is missing.
     */
    public function __construct(object $config)
    {
        // Validate that the secret key exists and is not empty.
        if (empty($config->secret_key)) {
            throw new InvalidArgumentException('Flutterwave Secret Key is required.');
        }

        $this->secretKey = $config->secret_key;

        // Get an instance of the HTTP client service from CodeIgniter.
        // The base_uri is removed to prevent resolution issues.
        $this->httpClient = Services::curlrequest([
            'timeout'  => 15, // Set a reasonable timeout for API calls
        ]);
    }

    /**
     * Verifies a payment by its transaction ID.
     *
     *
     * @param string $transactionId The 'transaction_id' returned by Flutterwave.
     *
     * @return object|null The 'data' object from the Flutterwave response on success, or null on failure.
     * @throws Exception If the API call fails or returns an error status.
     */
    public function verifyPayment(string $transactionId): ?object
    {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty.');
        }

        // Construct the full, absolute URL for the API endpoint.
        $url = 'https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify';

        try {
            // Use the full URL directly in the request method to avoid host resolution errors.
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type'  => 'application/json',
                ]
            ]);

            // Check for non-200 status codes
            if ($response->getStatusCode() !== 200) {
                throw new Exception('Flutterwave API returned a non-successful status code: ' . $response->getStatusCode());
            }

            $body = json_decode($response->getBody());

            // Check if the API response itself indicates a failure
            if (isset($body->status) && $body->status === 'error') {
                throw new Exception('Flutterwave API error: ' . ($body->message ?? 'Unknown error'));
            }

            // Return the 'data' object which contains payment details like amount, currency, status, etc.
            return $body->data ?? null;

        } catch (Exception $e) {
            // Log the detailed error for debugging purposes.
            log_message('error', '[FlutterwaveLib] Payment verification failed: ' . $e->getMessage());
            // Re-throw the exception so the controller can handle it.
            throw $e;
        }
    }
}