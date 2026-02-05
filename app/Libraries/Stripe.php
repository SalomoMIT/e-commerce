<?php

namespace App\Libraries;

require_once APPPATH . "ThirdParty/stripe/vendor/autoload.php";

use Stripe\Stripe as StripeSDK;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Webhook;

/**
 * Stripe Library for CodeIgniter 4
 * This class handles all Stripe API interactions, abstracting the logic
 * away from the controllers.
 */
class Stripe
{
    /**
     * The Stripe API secret key.
     * @var string
     */
    private $secretKey;

    /**
     * The Stripe Webhook Signing Secret.
     * @var string
     */
    private $webhookSecret;
    private $appName;

    /**
     * Constructor.
     * Initializes the Stripe SDK with the secret key and webhook secret
     * from the provided config object.
     * @param object $config The payment gateway configuration object.
     */
    public function __construct(object $config, string $appName)
    {
        $this->secretKey = $config->secret_key ?? null;
        // Get the webhook secret from the config object.
        // Assumes the field name in the database is 'webhook_secret'.
        $this->webhookSecret = $config->webhook_secret ?? null;

        $this->appName = $appName;

        StripeSDK::setApiKey($this->secretKey);
    }

    /**
     * Creates a complete Checkout Session from a checkout object and its items.
     * This is the main high-level method to be called from a controller. It acts as a router.
     *
     * @param object $checkout The main checkout object containing all order details.
     * @param array $items The list of items from the cart.
     * @return string|null     The checkout URL on success, or null on failure.
     */
    public function createCheckoutSessionFromOrder(object $checkout, array $items): ?string
    {
        $lineItems = [];
        if ($checkout->checkout_type == 'service') {
            $lineItems = $this->createCheckoutSessionServicePayment($checkout);
        } else {
            $lineItems = $this->createCheckoutSessionProductSale($checkout, $items);
        }

        // If line items could not be created, exit.
        if (empty($lineItems)) {
            return null;
        }

        // Prepare metadata to attach to the Stripe session for later verification.
        $metadata['checkout_token'] = $checkout->checkout_token;

        // Call the internal method to create the session with the prepared line items.
        $session = $this->createCheckoutSession($lineItems, $metadata);

        return $session->url ?? null;
    }

    /**
     * Verifies a payment by retrieving the Checkout Session from Stripe.
     * This is typically used for the user-return flow (success_url).
     *
     * @param string $sessionId The ID of the Stripe Checkout Session.
     * @return Session|null    The session object if the payment was successful ('paid'), or null otherwise.
     */
    public function verifyPayment(string $sessionId): ?Session
    {
        try {
            $session = Session::retrieve($sessionId);
            // The most reliable way to confirm a payment is to check the 'payment_status'.
            if ($session && $session->payment_status === 'paid') {
                return $session;
            }
            return null;
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe API Error while verifying payment: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Builds the line_items array for a product sale.
     *
     * @param object $checkout The main checkout object containing cart totals.
     * @param array $items The list of items from the cart.
     * @return array|null      An array of line items or null if cart is empty.
     */
    private function createCheckoutSessionProductSale(object $checkout, array $items): ?array
    {
        if (empty($checkout->grand_total) || $checkout->grand_total <= 0) {
            log_message('error', 'Stripe Error: grand_total is missing or zero for product sale checkout.');
            return null;
        }

        $checkoutId = $checkout->id ?? $checkout->checkout_token;
        $itemName = $this->appName . ' ' . trans("cart_payment");

        $lineItems = [
            [
                'price_data' => [
                    'currency' => $checkout->currency_code,
                    'product_data' => [
                        'name' => $itemName,
                        'description' => trans("reference") . ' #' . $checkoutId
                    ],
                    'unit_amount' => (int)round($checkout->grand_total * 100)
                ],
                'quantity' => 1,
            ]
        ];

        return $lineItems;
    }

    /**
     * Builds the line_items array for a service payment.
     *
     * @param object $checkout The main checkout object containing service details.
     * @return array|null       An array of line items or null on failure.
     */
    private function createCheckoutSessionServicePayment(object $checkout): ?array
    {
        // Ensure there is a final total to charge.
        if (empty($checkout->grand_total) || $checkout->grand_total <= 0) {
            log_message('error', 'Stripe Error: grand_total is missing or zero for service checkout.');
            return null;
        }

        // Map service types to their translation keys for a clean name.
        $serviceNameMap = [
            'add_funds' => trans("add_funds"),
            'promote' => trans("product_promoting_payment"),
            'membership' => trans("membership_plan_payment"),
        ];
        $serviceName = $serviceNameMap[$checkout->service_type] ?? trans("service_payment");

        $checkoutId = $checkout->id ?? $checkout->checkout_token;
        $itemName = $this->appName . ' ' . $serviceName;
        $itemDescription = trans("reference") . ' #' . $checkoutId;

        $lineItems = [
            [
                'price_data' => [
                    'currency'     => $checkout->currency_code,
                    'product_data' => [
                        'name' => $itemName,
                        'description' => $itemDescription,
                    ],
                    'unit_amount'  => (int)round($checkout->grand_total * 100),
                ],
                'quantity' => 1,
            ]
        ];

        return $lineItems;
    }

    /**
     * A private helper function to add a fee (like tax, shipping) as a line item.
     * This prevents code duplication and keeps the main methods clean.
     *
     * @param array  &$lineItems The array of line items to add to (passed by reference).
     * @param string $name The name of the fee to be displayed.
     * @param float $amount The amount of the fee.
     * @param string $currencyCode The currency code.
     */
    private function addFeeToLineItems(array &$lineItems, string $name, float $amount, string $currencyCode): void
    {
        if (!empty($amount) && $amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currencyCode,
                    'product_data' => ['name' => $name],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ];
        }
    }

    /**
     * Internal method to perform the actual API call to create a Stripe Checkout Session.
     *
     * @param array $lineItems An array of items formatted for the Stripe API.
     * @param array $metadata An array of key-value data to attach to the session.
     * @return Session|null    The created session object or null on failure.
     */
    private function createCheckoutSession(array $lineItems, array $metadata): ?Session
    {
        try {
            return Session::create([
                'line_items' => $lineItems,
                'metadata' => $metadata,
                'mode' => 'payment',
                'success_url' => base_url('checkout/complete-stripe-payment') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => generateUrl('cart', 'payment'),
            ]);
        } catch (ApiErrorException $e) {
            log_message('error', 'Stripe API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handles an incoming webhook notification from Stripe.
     * It verifies the signature and returns the relevant object for successful events.
     *
     * @return object|null The event's data object on success (e.g., a Session object), or null on failure.
     */
    public function handleWebhook()
    {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

        // Use the webhook secret stored in the class property.
        $webhook_secret = $this->webhookSecret;

        if (!$payload || !$sig_header || !$webhook_secret) {
            log_message('error', '[StripeLib] Webhook missing payload, signature, or secret.');
            return null;
        }

        try {
            // Verify the webhook signature and construct the event object.
            $event = Webhook::constructEvent($payload, $sig_header, $webhook_secret);
        } catch (\Exception $e) {
            log_message('error', '[StripeLib] Webhook signature verification failed: ' . $e->getMessage());
            return null;
        }

        // We are only interested in successful checkout sessions.
        if ($event->type === 'checkout.session.completed') {
            // Return the full Session object contained within the event data.
            // This is the most efficient way, as it saves an extra API call.
            return $event->data->object;
        }

        // For other event types, we are not returning anything for now.
        return null;
    }
}