<?php

namespace App\Services;

use Config\Services;
use CodeIgniter\HTTP\CURLRequest;
use Exception;
use stdClass;

/**
 * Cloudflare Turnstile Verification Service
 *
 * This service handles the server-side validation of Cloudflare Turnstile tokens.
 */
class TurnstileService
{
    /**
     * The Cloudflare Turnstile verification endpoint.
     */
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private readonly string $secretKey;
    private readonly CURLRequest $httpClient;

    /**
     * Constructor.
     * Initializes the HTTP client and loads the configuration from a settings object.
     *
     * @param stdClass|object $settings An object containing application settings from the database.
     * It must contain a 'turnstile_secret_key' property.
     */
    public function __construct(object $settings)
    {
        // Initialize the HTTP client using CodeIgniter's service
        $this->httpClient = Services::curlrequest([
            'baseURI' => self::VERIFY_URL,
            'timeout' => 5, // Request timeout in seconds
        ]);

        // Set the secret key from the settings object passed from the database
        $this->secretKey = $settings->turnstile_secret_key ?? '';
    }

    /**
     * Verifies the Cloudflare Turnstile token from the form submission.
     * If no token is provided, it automatically fetches it from the POST request.
     *
     * @param string|null $token The 'cf-turnstile-response' token. Optional.
     *
     * @return bool True if the token is valid, false otherwise.
     */
    public function verify(?string $token = null): bool
    {
        // If no token is passed as an argument, get it from the current POST request.
        $tokenToVerify = $token ?? Services::request()->getPost('cf-turnstile-response');

        // If no secret key is configured or no token is found, fail immediately for security.
        if (empty($this->secretKey) || empty($tokenToVerify)) {
            return false;
        }

        try {
            $response = $this->httpClient->post('', [
                'form_params' => [
                    'secret'   => $this->secretKey,
                    'response' => $tokenToVerify,
                    // Sending the user's IP is recommended by Cloudflare
                    'remoteip' => Services::request()->getIPAddress(),
                ],
            ]);

            $body = json_decode($response->getBody(), true);

            // Check if the response body is valid and the 'success' key is true.
            if (isset($body['success']) && $body['success'] === true) {
                return true;
            }
            return false;

        } catch (Exception $e) {
            return false;
        }
    }
}