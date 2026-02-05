<?php

namespace App\Libraries;

use Config\Services;
use Throwable;

/**
 * Paypal Class (SDK-less Version)
 *
 * A service class to interact with the PayPal REST API using standard HTTP requests.
 * It handles OAuth2 token management and payment verification manually.
 */
class Paypal
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private ?string $accessToken = null;
    private $httpClient;

    /**
     * Initializes the PayPal client configuration.
     *
     * @param object $config Object with keys: public_key (Client ID), secret_key (Client Secret), environment
     */
    public function __construct(object $config)
    {
        $this->clientId = $config->public_key ?? '';
        $this->clientSecret = $config->secret_key ?? '';
        $environment = strtolower($config->environment ?? 'sandbox');

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new \InvalidArgumentException('PayPal Client ID and Secret are required.');
        }

        $this->baseUrl = ($environment === 'production')
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        // Use CodeIgniter's built-in HTTP Client
        $this->httpClient = Services::curlrequest(['timeout' => 20]);
    }

    /**
     * Retrieves the details of a completed order from PayPal's servers.
     *
     * @param string $orderId The order ID from the client-side onApprove function.
     * @return object|null The verified order object on success, null on failure.
     */
    public function getOrderDetails(string $orderId): ?object
    {
        try {
            // Ensure we have a valid access token.
            $token = $this->getAccessToken();
            if (!$token) {
                // The getAccessToken method already logs the error if an exception occurs.
                return null;
            }

            // Make the API call to verify the order.
            $url = $this->baseUrl . '/v2/checkout/orders/' . $orderId;

            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                // Log for non-200 status removed as per user request.
                return null;
            }

            return json_decode($response->getBody());

        } catch (Throwable $e) {
            log_message('error', '[PayPal-SDKless] GetOrderDetails failed for Order ID ' . $orderId . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets an OAuth2 access token from PayPal.
     *
     * @return string|null The access token on success, null on failure.
     */
    private function getAccessToken(): ?string
    {
        // If we already have a token in this instance, reuse it.
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $url = $this->baseUrl . '/v1/oauth2/token';

        // Use Basic Authentication with Client ID and Secret.
        $auth = base64_encode($this->clientId . ':' . $this->clientSecret);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Basic ' . $auth,
                    'Accept'        => 'application/json',
                ],
                // The body must be 'grant_type=client_credentials'
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                // Log for non-200 status removed as per user request.
                return null;
            }

            $data = json_decode($response->getBody());

            if (empty($data->access_token)) {
                // Log for missing token removed as per user request.
                return null;
            }

            // Cache the token and return it.
            $this->accessToken = $data->access_token;
            return $this->accessToken;

        } catch (Throwable $e) {
            log_message('error', '[PayPal-SDKless] Exception while getting Access Token: ' . $e->getMessage());
            return null;
        }
    }
}