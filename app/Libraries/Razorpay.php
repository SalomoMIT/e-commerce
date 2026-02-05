<?php

/**
 * Razorpay Integration Library for CodeIgniter 4
 *
 * Provides a structured and secure way to interact with the Razorpay API.
 * It handles order creation, payment verification, and webhook processing.
 *
 * @see https://razorpay.com/docs/payment-gateway/php-integration/
 */

namespace App\Libraries;

require_once APPPATH . 'ThirdParty/razorpay/vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Exception\SignatureVerificationError;
use Exception;
use InvalidArgumentException;
use Throwable;
use CodeIgniter\HTTP\IncomingRequest;

class Razorpay
{
    /**
     * The Razorpay API client instance.
     * @var Api
     */
    private Api $client;

    /**
     * The Razorpay API Key ID (Public Key).
     * @var string
     */
    private string $keyId;

    /**
     * The Razorpay API Secret Key.
     * @var string
     */
    private string $secretKey;

    /**
     * The optional Razorpay Webhook Secret.
     * @var string
     */
    private string $webhookSecret;

    /**
     * Constructor. Initializes the Razorpay API client.
     *
     * @param object $config An object containing 'public_key', 'secret_key', and optional 'webhook_secret'.
     * @throws InvalidArgumentException If API keys are missing.
     */
    public function __construct(object $config)
    {
        if (empty($config->public_key) || empty($config->secret_key)) {
            throw new InvalidArgumentException('Razorpay Public Key and Secret Key are required.');
        }

        $this->keyId = $config->public_key;
        $this->secretKey = $config->secret_key;
        $this->webhookSecret = $config->webhook_secret ?? '';

        $this->client = new Api($this->keyId, $this->secretKey);
    }

    /**
     * Creates a new order on Razorpay.
     *
     * @param array $data An array containing order details like amount and currency.
     * @return string The generated Razorpay Order ID.
     * @throws Exception
     */
    public function createOrder(array $data): string
    {
        if (!isset($data['amount']) || !is_int($data['amount'])) {
            throw new InvalidArgumentException('The "amount" must be provided as an integer in the smallest currency unit.');
        }

        $order = $this->client->order->create($data);

        if (empty($order['id'])) {
            throw new Exception('Failed to create Razorpay order: No Order ID returned.');
        }

        return $order['id'];
    }

    /**
     * Verifies, fetches, and captures a payment from the client-side checkout flow.
     * This is the primary method for handling the payment return from the user's browser.
     *
     * @param array $attributes The POST data from Razorpay's checkout.js.
     * @return object|null The final, captured payment entity on success, or null on any failure.
     */
    public function verifyAndCapturePayment(array $attributes): ?object
    {
        // Verify the signature first to ensure the request is authentic.
        if (!$this->verifyPaymentSignature($attributes)) {
            // The verifyPaymentSignature method already logs the specific error.
            return null;
        }

        try {
            // For added security, fetch the payment details directly from Razorpay's API.
            $payment = $this->client->payment->fetch($attributes['razorpay_payment_id']);

            // If payment is only 'authorized', we must 'capture' it to complete the transaction.
            if ($payment && $payment->status === 'authorized') {
                // The capture call finalizes the transaction and moves the funds.
                $payment = $payment->capture(['amount' => $payment->amount, 'currency' => $payment->currency]);
            }

            // Check if the final payment status is 'captured' (successful).
            if ($payment && $payment->status === 'captured') {
                return $payment; // Return the final payment object on complete success.
            }

            // If status is not captured, it's a failure.
            log_message('warning', 'Razorpay payment status was not "captured". Final Status: ' . ($payment->status ?? 'Unknown'));
            return null;

        } catch (Throwable $e) {
            log_message('error', "Razorpay payment processing in library failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifies the payment signature from the standard client-side checkout flow.
     * This is a security check to prevent tampering.
     *
     * @param array $attributes An array containing 'razorpay_order_id', 'razorpay_payment_id', and 'razorpay_signature'.
     * @return bool True if the signature is valid.
     */
    public function verifyPaymentSignature(array $attributes): bool
    {
        if (isset($attributes['payment_id'])) {
            $attributes['razorpay_payment_id'] = $attributes['payment_id'];
        }

        if (!isset($attributes['razorpay_signature'], $attributes['razorpay_payment_id'], $attributes['razorpay_order_id'])) {
            throw new InvalidArgumentException('Payment signature verification requires razorpay_signature, razorpay_payment_id, and razorpay_order_id.');
        }

        try {
            // The SDK's utility method will throw a SignatureVerificationError on failure.
            $this->client->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (SignatureVerificationError $e) {
            log_message('error', '[RazorpayLib] Signature verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handles and validates an incoming webhook notification from Razorpay.
     * On a successful 'payment.captured' event, it returns a standard object with structured payment data.
     *
     * @param IncomingRequest $request The current request object from the controller.
     * @return object|null The structured result object on success, or null on any failure or irrelevant event.
     */
    public function handleWebhook(IncomingRequest $request): ?array
    {
        // Verify the webhook signature (this code is already correct)
        $rawPayload = $request->getBody();
        $receivedSignature = $request->getHeaderLine('X-Razorpay-Signature');

        if (empty($rawPayload)) {
            log_message('error', '[RazorpayLib] Webhook with empty payload.');
            return null;
        }

        if (!empty($this->webhookSecret)) {
            if (!hash_equals(hash_hmac('sha256', $rawPayload, $this->webhookSecret), $receivedSignature)) {
                log_message('critical', '[RazorpayLib] Webhook with INVALID SIGNATURE received.');
                return null;
            }
        }

        $data = json_decode($rawPayload);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', '[RazorpayLib] Failed to decode JSON payload: ' . json_last_error_msg());
            return null;
        }

        $event = $data->event ?? null;
        $paymentEntity = $data->payload->payment->entity ?? null;

        if (empty($paymentEntity)) {
            log_message('error', '[RazorpayLib] Webhook is missing the payment entity payload for event: ' . $event);
            return null;
        }

        try {
            // Decide action based on the event
            switch ($event) {
                case 'payment.authorized':
                    // Capture the payment immediately.
                    $capturedPayment = $this->client->payment->fetch($paymentEntity->id)->capture([
                        'amount'   => $paymentEntity->amount,
                        'currency' => $paymentEntity->currency
                    ]);

                    return ['status' => 'capture_initiated', 'paymentEntity' => $capturedPayment];

                case 'payment.captured':
                    return [
                        'status' => 'payment_confirmed',
                        'paymentEntity' => $paymentEntity,
                        'checkoutToken' => $paymentEntity->notes->checkout_token ?? null,
                    ];

                default:
                    // We ignore other events like 'order.paid', 'payment.failed', etc. for now.
                    log_message('info', "[RazorpayLib] Ignoring irrelevant webhook event: {$event}");
                    return null;
            }
        } catch (Throwable $e) {
            log_message('error', "[RazorpayLib] Error processing webhook event '{$event}' for Payment ID {$paymentEntity->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Returns the configured Razorpay Key ID.
     * This is useful for passing the key to the frontend JavaScript.
     *
     * @return string
     */
    public function getKeyId(): string
    {
        return $this->keyId;
    }
}