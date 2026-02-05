<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;
use Exception;

try {
    $autoload_path = APPPATH . 'ThirdParty/mercadopago/vendor/autoload.php';
    if (file_exists($autoload_path)) {
        require_once $autoload_path;
    } else {
        throw new Exception("Mercado Pago SDK not found at: " . $autoload_path);
    }
} catch (Exception $e) {
    log_message('error', 'Failed to load Mercado Pago SDK: ' . $e->getMessage());
    die('A critical error occurred with the payment gateway.');
}

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

/**
 * Mercado Pago Library for CodeIgniter 4
 * This class handles all Mercado Pago API interactions, abstracting the logic
 * away from the controllers, similar to the Stripe implementation.
 */
class MercadoPago
{
    private string $secretKey;
    private string $appName;
    protected $logger;

    /**
     * Initializes the Mercado Pago library and SDK.
     *
     * @param object $config Must contain: secret_key
     * @param string $appName The name of the application for payment descriptions.
     */
    public function __construct(object $config, string $appName)
    {
        $this->secretKey = $config->secret_key ?? '';
        $this->appName = $appName;
        $this->logger = Services::logger();

        $this->initializeSDK();
    }

    /**
     * Creates a Checkout Preference from a checkout object.
     *
     * @param object $checkout The main checkout object containing all order details.
     * @return string|null The preference ID on success, or null on failure.
     */
    public function createPreferenceFromCheckout(object $checkout): ?string
    {
        // Basic validation
        if (empty($checkout->grand_total) || empty($checkout->currency_code) || empty($checkout->checkout_token)) {
            $this->logger->error('[Mercado Pago] Missing required checkout data for preference creation.');
            return null;
        }

        try {
            $client = new PreferenceClient();

            $itemName = $this->getPaymentTitle($checkout);
            $itemDescription = trans("reference") . ' #' . ($checkout->id ?? $checkout->checkout_token);

            $preferenceData = [
                'items' => [
                    [
                        'title' => $itemName,
                        'description' => $itemDescription,
                        'quantity' => 1,
                        'currency_id' => $checkout->currency_code,
                        'unit_price' => (float)$checkout->grand_total,
                    ]
                ],
                'back_urls' => [
                    'success' => base_url('checkout/complete-mercado-pago-payment'),
                    'failure' => generateUrl('cart', 'payment'),
                    'pending' => generateUrl('cart', 'payment'),
                ],
                'auto_return' => 'approved',
                'notification_url' => base_url('payment/webhook/mercado-pago'),
                'external_reference' => $checkout->checkout_token,
            ];

            $preference = $client->create($preferenceData);
            return $preference->id;

        } catch (MPApiException $e) {
            $this->logger->error('[Mercado Pago] API Error creating preference: ' . $e->getMessage(), (array)$e->getApiResponse()->getContent());
            return null;
        } catch (Exception $e) {
            $this->logger->error('[Mercado Pago] General Error creating preference: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifies a payment by fetching its details from the Mercado Pago API.
     * This is the trusted way to confirm a payment status.
     *
     * @param string $paymentId The ID of the Mercado Pago payment.
     * @return object|null The payment object if the payment was successful ('approved'), or null otherwise.
     */
    public function verifyPayment(string $paymentId): ?object
    {
        try {
            $payment = $this->getPaymentDetails($paymentId);
            // The most reliable way to confirm is to check the 'status'.
            if ($payment && $payment->status === 'approved') {
                return $payment;
            }
            $this->logger->warning('[Mercado Pago] Payment verification check, but status was not "approved".', ['payment_id' => $paymentId, 'status' => $payment->status ?? 'N/A']);
            return null;
        } catch (Exception $e) {
            $this->logger->error('[Mercado Pago] Error during payment verification: ' . $e->getMessage(), ['payment_id' => $paymentId]);
            return null;
        }
    }

    /**
     * Handles an incoming webhook notification from Mercado Pago.
     * It parses the payload, fetches the trusted payment details, and returns them.
     *
     * @param IncomingRequest $request
     * @return object|null The full, trusted payment object on success, or null on failure.
     */
    public function handleWebhook(IncomingRequest $request): ?object
    {
        $payload = $request->getBody();
        $jsonPayload = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('[Mercado Pago Webhook] Invalid JSON received.', ['payload' => $payload]);
            return null;
        }

        // Check notifications of type 'payment'.
        if (isset($jsonPayload['type']) && $jsonPayload['type'] === 'payment' && !empty($jsonPayload['data']['id'])) {
            $paymentId = $jsonPayload['data']['id'];
            $this->logger->info('[Mercado Pago Webhook] Received payment notification for ID: ' . $paymentId);
            // Fetch the full, trusted payment details directly from the API.
            // Do not trust any other data in the webhook payload.
            return $this->getPaymentDetails($paymentId);
        }

        $this->logger->info('[Mercado Pago Webhook] Received a notification that was not of type "payment" or had no ID. Skipping.', ['payload' => $payload]);
        return null;
    }

    /**
     * Retrieves the full details of a specific payment from the Mercado Pago API.
     *
     * @param string $paymentId The ID of the payment.
     * @return object|null The payment details object on success, or null on failure.
     */
    private function getPaymentDetails(string $paymentId): ?object
    {
        if (empty($paymentId)) {
            return null;
        }
        try {
            $client = new PaymentClient();
            return $client->get($paymentId);
        } catch (MPApiException $e) {
            $this->logger->error('[Mercado Pago] API Error fetching payment details for ID ' . $paymentId . ': ' . $e->getMessage(), (array)$e->getApiResponse()->getContent());
            return null;
        } catch (Exception $e) {
            $this->logger->error('[Mercado Pago] General Error fetching payment details for ID ' . $paymentId . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Initializes the SDK with the provided secret key.
     */
    private function initializeSDK(): void
    {
        if (empty($this->secretKey)) {
            $this->logger->critical('[Mercado Pago] Secret Key is not configured.');
            // This is a fatal error, so we should stop execution.
            throw new Exception("Mercado Pago payment gateway is not configured.");
        }
        MercadoPagoConfig::setAccessToken($this->secretKey);
    }

    /**
     * Generates a descriptive title for the payment based on the checkout type.
     *
     * @param object $checkout
     * @return string
     */
    private function getPaymentTitle(object $checkout): string
    {
        if ($checkout->checkout_type == 'service') {
            $serviceNameMap = [
                'add_funds' => trans("add_funds"),
                'promote' => trans("product_promoting_payment"),
                'membership' => trans("membership_plan_payment"),
            ];
            $serviceName = $serviceNameMap[$checkout->service_type] ?? trans("service_payment");
            return $this->appName . ' ' . $serviceName;
        }
        return $this->appName . ' ' . trans("cart_payment");
    }
}