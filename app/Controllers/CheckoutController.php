<?php

namespace App\Controllers;

use App\Libraries\DlocalGo;
use App\Libraries\Flutterwave;
use App\Libraries\Iyzico;
use App\Libraries\Midtrans;
use App\Libraries\Paypal;
use App\Libraries\PayTabs;
use App\Libraries\Razorpay;
use App\Libraries\Stripe;
use App\Libraries\Paystack;
use App\Libraries\TapPayments;
use App\Libraries\YooMoney;
use App\Libraries\MercadoPago;
use App\Models\CartModel;
use App\Models\CheckoutModel;
use App\Models\EarningsModel;
use App\Models\MembershipModel;
use App\Models\OrderModel;
use App\Models\PromoteModel;

class CheckoutController extends BaseController
{
    protected $checkoutModel;
    protected $cartModel;
    protected $orderModel;
    protected $membershipModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->checkoutModel = new CheckoutModel();
        $this->cartModel = new CartModel();
        $this->orderModel = new OrderModel();
        $this->membershipModel = new MembershipModel();
    }

    /**
     * Verify the PayPal payment and finalize the order.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completePaypalPayment()
    {
        try {
            $paymentId = $this->request->getPost('payment_id');
            $checkoutToken = $this->request->getPost('checkout_token');

            if (empty($paymentId) || empty($checkoutToken)) {
                log_message('error', "Invalid PayPal request data!");
                return $this->paymentErrorResponse(trans("msg_payment_error"), true);
            }

            // Get PayPal config
            $config = getPaymentGateway('paypal');
            if (empty($config) || !$config->status) {
                log_message('error', "Payment method not found!");
                return $this->paymentErrorResponse(trans("msg_payment_error"), true);
            }

            $paypal = new Paypal($config);
            $verifiedOrder = $paypal->getOrderDetails($paymentId);

            if (empty($verifiedOrder)) {
                return $this->paymentErrorResponse(trans("msg_payment_error"), true);
            }

            // Check the order status. After a successful client-side capture, it must be 'COMPLETED'.
            if ($verifiedOrder->status !== 'COMPLETED') {
                return $this->paymentErrorResponse('Payment was not completed. Status: ' . $verifiedOrder->status, true);
            }

            $checkout = $this->checkoutModel->getCheckoutByToken($checkoutToken);
            if (empty($checkout)) {
                return $this->paymentErrorResponse("No payment found for the given checkout token!", true);
            }

            // The captured payment details are inside the 'purchase_units' array.
            $purchaseUnit = $verifiedOrder->purchase_units[0] ?? null;
            $capture = $purchaseUnit->payments->captures[0] ?? null;
            if (empty($capture)) {
                return $this->paymentErrorResponse(trans("msg_payment_error"), true);
            }

            // Compare amounts and currency from PayPal with our database records.
            $paypalAmount = $capture->amount->value;
            $paypalCurrency = $capture->amount->currency_code;

            $expectedAmount = numToDecimal($checkout->grand_total);
            $expectedCurrency = $checkout->currency_code;

            if (bccomp((string)$paypalAmount, (string)$expectedAmount, 2) !== 0 || $paypalCurrency !== $expectedCurrency) {
                $logMessage = "PayPal amount/currency mismatch for Order ID {$paymentId}. Expected {$expectedAmount} {$expectedCurrency}, but PayPal reported {$paypalAmount} {$paypalCurrency}.";
                log_message('critical', $logMessage);
                return $this->paymentErrorResponse(trans("msg_payment_error"), true);
            }

            $transaction = (object)[
                'payment_id' => $capture->id,
                'status_text' => $verifiedOrder->status,
                'status' => 1,
                'payment_method' => 'paypal',
            ];

            $result = $this->handlePayment($checkout, $transaction);
            return $this->handleCheckoutResponse($result, true);

        } catch (\Throwable $e) {
            log_message('error', 'PayPal Client Payment Processing Exception: ' . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"), true);
        }
    }

    /**
     * Verify the Stripe payment and finalize the order.
     *
     * @method GET
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function completeStripePayment()
    {
        $sessionId = $this->request->getGet('session_id');

        if (empty($sessionId)) {
            log_message('error', 'Stripe: A request was made to the completion URL without a session_id.');
            return $this->paymentErrorResponse();
        }

        $config = getPaymentGateway('stripe');
        if (empty($config)) {
            log_message('error', 'Stripe: Payment gateway configuration not found.');
            return $this->paymentErrorResponse();
        }

        try {
            $stripe = new Stripe($config, $this->baseVars->appName);
            // Verify the payment and get the session object.
            $session = $stripe->verifyPayment($sessionId);

            if ($session) {
                // If verification is successful, pass the session object to the shared processing function.
                $result = $this->processStripeOrder($session, false);
                return $this->handleCheckoutResponse($result);
            } else {
                throw new \Exception("Stripe payment verification failed or payment was not completed.");
            }

        } catch (\Exception $e) {
            log_message('error', "Stripe payment processing failed after user return: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }
    }

    /**
     * Processes Stripe order
     *
     * @param object $session The Stripe Checkout Session object.
     * @param bool $isWebhook Indicates if the call comes from a server notification.
     */
    private function processStripeOrder(object $session, bool $isWebhook = false)
    {
        $token = $session->metadata->checkout_token ?? null;
        if (empty($token)) {
            throw new \Exception("Stripe: Empty checkout token.");
        }

        $checkout = $this->checkoutModel->getCheckoutByToken($token);
        if (empty($checkout)) {
            throw new \Exception("Stripe: Checkout not found for token: {$token}");
        }

        if ($checkout->status === 'paid') {
            if ($isWebhook) {
                return true;
            }
            return [
                'status' => 1,
                'redirectUrl' => $this->checkoutModel->createOrderRedirectUrl($checkout)
            ];
        }

        // Verify the amount and currency.
        $paidAmountInSubunit = $session->amount_total; // e.g., 1099 for $10.99
        $paidCurrency = $session->currency;

        $expectedAmount = numToDecimal($checkout->grand_total);
        $expectedAmountInSubunit = (int)round($checkout->grand_total * 100);
        $expectedCurrency = $checkout->currency_code;

        if ($paidAmountInSubunit != $expectedAmountInSubunit || strtolower($paidCurrency) != strtolower($expectedCurrency)) {
            $logMessage = "Stripe: Amount/currency mismatch for token: {$token}. Expected: {$expectedAmountInSubunit} {$expectedCurrency}, Paid: {$paidAmountInSubunit} {$paidCurrency}";
            throw new \Exception($logMessage);
        }

        // If all checks pass, create the final transaction object.
        $transaction = (object)[
            'payment_id' => $session->payment_intent ?? $session->id,
            'status_text' => $session->payment_status, // "paid"
            'status' => 1,
            'payment_method' => 'stripe',
        ];

        return $this->handlePayment($checkout, $transaction, $isWebhook);
    }

    /**
     * Verify the PayStack payment and finalize the order.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completePayStackPayment()
    {
        // Get PayStack config
        $config = getPaymentGateway('paystack');
        if (empty($config) || !$config->status) {
            return $this->paymentErrorResponse("Payment method not found!", true);
        }

        // Get data from POST request
        $token = inputPost('checkout_token');
        $paymentId = inputPost('payment_id');
        if (empty($token) || empty($paymentId)) {
            return $this->paymentErrorResponse("Invalid request!", true);
        }

        // Find checkout
        $checkout = $this->checkoutModel->getCheckoutByToken($token);
        if (empty($checkout)) {
            log_message('error', "PayStack: Checkout not found for token: {$token}");
            return $this->paymentErrorResponse("No payment found for the given token!", true);
        }

        try {
            // Load PayStack and verify the transaction (within a try-catch block)
            $paystack = new Paystack($config);
            $transactionData = $paystack->verifyTransaction($paymentId);

            // Use integer comparison for amounts to avoid float precision issues.
            $paidAmountInSubunit = $transactionData->amount;
            $paidCurrency = $transactionData->currency;

            $expectedAmount = numToDecimal($checkout->grand_total);
            $expectedAmountInSubunit = (int)round($expectedAmount * 100);
            $expectedCurrency = $checkout->currency_code;

            if ($paidAmountInSubunit == $expectedAmountInSubunit && $paidCurrency == $expectedCurrency) {
                $transaction = (object)[
                    'payment_id' => $paymentId,
                    'status_text' => 'success',
                    'status' => 1,
                    'payment_method' => 'paystack',
                ];

                $result = $this->handlePayment($checkout, $transaction);
                return $this->handleCheckoutResponse($result, true);
            } else {
                $logMessage = "PayStack: Amount mismatch for token: {$token}. Expected: {$expectedAmountInSubunit} {$expectedCurrency}, Paid: {$paidAmountInSubunit} {$paidCurrency}";
                log_message('critical', $logMessage);
                return $this->paymentErrorResponse(trans("msg_payment_error"), true);
            }

        } catch (\Exception $e) {
            log_message('error', "PayStack payment verification failed: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"), true);
        }
    }

    /**
     * Verify the Razorpay payment and finalize the order.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeRazorpayPayment()
    {
        try {
            // Get data from the POST request sent by Razorpay's checkout.js
            $postData = [
                'razorpay_payment_id' => $this->request->getPost('razorpay_payment_id'),
                'razorpay_order_id' => $this->request->getPost('razorpay_order_id'),
                'razorpay_signature' => $this->request->getPost('razorpay_signature')
            ];
            $token = $this->request->getPost('checkout_token');

            if (empty($token) || empty($postData['razorpay_payment_id'])) {
                return $this->paymentErrorResponse("Invalid request data.", true);
            }

            // Initialize the library. The library will handle all API interactions.
            $config = getPaymentGateway('razorpay');
            $razorpay = new Razorpay($config);

            $payment = $razorpay->verifyAndCapturePayment($postData);
            if ($payment) {
                $result = $this->processRazorpayOrder($payment, $token, false);
                return $this->handleCheckoutResponse($result, true);
            }

            return $this->paymentErrorResponse(trans("msg_payment_error"), true);

        } catch (\Exception $e) {
            log_message('error', "Razorpay payment processing failed: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"), true);
        }
    }

    /**
     * Processes Razorpay order
     *
     * @param object $payment The Razorpay payment object.
     * @param string $checkoutToken The checkout token
     * @param bool $isWebhook Indicates if the call comes from a server notification.
     */
    private function processRazorpayOrder(object $payment, string $checkoutToken, bool $isWebhook = false)
    {
        if (empty($payment)) {
            throw new \Exception("Razorpay: Invalid payment data.");
        }

        if (empty($checkoutToken)) {
            throw new \Exception("Razorpay: Empty checkout token.");
        }

        $checkout = $this->checkoutModel->getCheckoutByToken($checkoutToken);
        if (empty($checkout)) {
            throw new \Exception("Razorpay: Checkout not found for token: {$checkoutToken}");
        }

        if ($checkout->status === 'paid') {
            if ($isWebhook) {
                return true;
            }
            return [
                'status' => 1,
                'redirectUrl' => $this->checkoutModel->createOrderRedirectUrl($checkout)
            ];
        }

        $paidAmountInSubunits = $payment->amount;
        $paidCurrency = $payment->currency;

        $expectedAmountInSubunits = (int)round($checkout->grand_total * 100);
        $expectedCurrency = $checkout->currency_code;

        if ($paidAmountInSubunits == $expectedAmountInSubunits && $paidCurrency == $expectedCurrency) {
            $transaction = (object)[
                'payment_id' => $payment->id,
                'status_text' => $payment->status,
                'status' => 1,
                'payment_method' => 'razorpay',
            ];

            return $this->handlePayment($checkout, $transaction, $isWebhook);
        } else {
            $logMessage = "Razorpay: Amount mismatch for token: {$checkoutToken}. Expected: {$expectedAmountInSubunits} {$expectedCurrency}, Paid: {$paidAmountInSubunits} {$paidCurrency}";
            log_message('critical', $logMessage);
        }
    }

    /**
     * Verify the Flutterwave payment and finalize the order.
     *
     * @method GET
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeFlutterwavePayment()
    {
        // Get Flutterwave config from the database
        $config = getPaymentGateway('flutterwave');
        if (empty($config) || !$config->status) {
            return $this->paymentErrorResponse("Payment method not found!");
        }

        $status = $this->request->getGet('status');
        $transactionId = $this->request->getGet('transaction_id');
        $txRef = $this->request->getGet('tx_ref'); // checkout_token

        // First, check the status from the redirect. If it's not successful, no need to proceed.
        if ($status !== 'successful') {
            $errorMessage = "Flutterwave payment was not successful. Status: " . esc($status);
            log_message('error', $errorMessage . " for tx_ref: " . esc($txRef));
            return $this->paymentErrorResponse($errorMessage);
        }

        // Validate that all required data is present
        if (empty($txRef) || empty($transactionId)) {
            return $this->paymentErrorResponse("Invalid request! Missing payment data.");
        }

        // Find the pending checkout session using our unique token (tx_ref)
        $checkout = $this->checkoutModel->getCheckoutByToken($txRef);
        if (empty($checkout)) {
            log_message('error', "Flutterwave: Checkout not found for tx_ref: {$txRef}");
            return $this->paymentErrorResponse("No payment session found for the given token!");
        }

        try {
            $flutterwave = new Flutterwave($config);
            // Verify the payment
            $payment = $flutterwave->verifyPayment($transactionId);

            if ($payment && $payment->status === 'successful') {

                $paidAmount = $payment->amount;
                $paidCurrency = $payment->currency;

                $expectedAmount = numToDecimal($checkout->grand_total);
                $expectedCurrency = $checkout->currency_code;

                // Verify that the paid amount and currency match the order in our database
                if (bccomp((string)$paidAmount, (string)$expectedAmount, 2) === 0 && $paidCurrency == $expectedCurrency) {
                    $transaction = (object)[
                        'payment_id' => $payment->id,
                        'status_text' => 'Successful',
                        'status' => 1,
                        'payment_method' => 'flutterwave',
                    ];

                    $result = $this->handlePayment($checkout, $transaction);
                    return $this->handleCheckoutResponse($result);
                } else {
                    $logMessage = "Flutterwave: Amount mismatch for tx_ref: {$txRef}. Expected: {$expectedAmount} {$expectedCurrency}, Paid: {$paidAmount} {$paidCurrency}";
                    log_message('critical', $logMessage);
                    return $this->paymentErrorResponse(trans("msg_payment_error"));
                }
            }

            throw new \Exception('Flutterwave server verification failed. Status: ' . ($payment->status ?? 'Unknown'));

        } catch (\Exception $e) {
            log_message('error', "Flutterwave payment verification failed: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }
    }

    /**
     * Verify the dLocal Go payment and finalize the order.
     *
     * @method GET
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function completeDLocalGoPayment()
    {
        // Get dLocal Go config from the database
        $config = getPaymentGateway('dlocalgo');
        if (empty($config) || !$config->status) {
            return $this->paymentErrorResponse("Payment method not found!");
        }

        $token = $this->request->getGet('token');

        // Find the pending checkout session using our unique token
        $checkout = $this->checkoutModel->getCheckoutByToken($token);
        if (empty($checkout)) {
            log_message('error', "dLocal Go: Checkout not found for token: {$token}");
            return $this->paymentErrorResponse("No payment session found for the given token!");
        }

        try {
            $transaction = (object)[
                'payment_id' => 'N/A',
                'status_text' => 'pending_payment',
                'status' => 0,
                'payment_method' => 'dlocalgo',
            ];

            // Process the transaction
            $result = $this->handlePayment($checkout, $transaction);
            return $this->handleCheckoutResponse($result);

        } catch (\Exception $e) {
            log_message('error', "dLocal Go payment verification failed: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }
    }

    /**
     * Processes the dLocal Go order
     *
     * @param array|null $payload The validated data from the dLocal Go webhook.
     * @param bool $isWebhook Indicates if the call comes from a server notification.
     */
    private function processDlocalGoOrder(?array $payload, bool $isWebhook = false): bool
    {
        if (empty($payload) || empty($payload['order_id'])) {
            throw new \InvalidArgumentException('dLocal Go: Missing or invalid order_id in webhook payload.');
        }

        $token = $payload['order_id'];
        $checkout = $this->checkoutModel->getCheckoutByToken($token);

        if (empty($checkout)) {
            throw new \Exception("dLocal Go: Checkout not found for token: {$token}");
        }

        $amountMatches = abs((float)$checkout->grand_total - (float)$payload['amount']) < 0.01;
        $currencyMatches = strtoupper($checkout->currency_code) === strtoupper($payload['currency']);

        if (!$amountMatches || !$currencyMatches) {
            throw new \Exception(
                "[SECURITY] dLocal Go Webhook: Amount or currency mismatch for Checkout ID {$checkout->id}. " .
                "Expected: {$checkout->grand_total} {$checkout->currency_code}, " .
                "Received: {$payload['amount']} {$payload['currency']}"
            );
        }

        // All checks passed. Construct a standard transaction object.
        $transaction = (object)[
            'payment_id' => $payload['id'] ?? 'N/A',
            'status_text' => 'Paid',
            'status' => 1,
            'payment_method' => 'dlocalgo'
        ];

        $this->handlePayment($checkout, $transaction, $isWebhook);

        $this->checkoutModel->updatePaymentTransactionAfterWebHook($checkout, $transaction);

        return true;
    }

    /**
     * Verify the Midtrans payment and finalize the order.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeMidtransPayment()
    {
        // Get Midtrans config from the database
        $config = getPaymentGateway('midtrans');
        if (empty($config) || !$config->status) {
            return $this->paymentErrorResponse("Payment method not found!", true);
        }

        // Get data from the AJAX POST request
        $transactionId = $this->request->getPost('transaction_id');
        $orderId = $this->request->getPost('order_id'); // checkout_token
        $checkoutToken = $this->request->getPost('checkout_token');

        // Use order_id as the token if checkout_token is not explicitly sent
        $token = !empty($checkoutToken) ? $checkoutToken : $orderId;

        // Validate that all required data is present
        if (empty($token) || empty($transactionId)) {
            return $this->paymentErrorResponse("Invalid request! Missing payment data.", true);
        }

        // Find the pending checkout session using our unique token
        $checkout = $this->checkoutModel->getCheckoutByToken($token);
        if (empty($checkout)) {
            log_message('error', "Midtrans: Checkout not found for token: {$token}");
            return $this->paymentErrorResponse("No payment session found for the given token!", true);
        }

        try {
            $midtrans = new Midtrans($config);
            $payment = $midtrans->verifyPayment($transactionId);

            // Check if the transaction status indicates a successful payment.
            if ($payment && ($payment->transaction_status == 'settlement' || $payment->transaction_status == 'capture')) {

                $paidAmount = intval($payment->gross_amount);
                $paidCurrency = $payment->currency;

                $expectedAmount = intval($checkout->grand_total);
                $expectedCurrency = $checkout->currency_code;

                // Compare the integer amount and the currency code
                if ($paidAmount == $expectedAmount && $paidCurrency == $expectedCurrency) {
                    $transaction = (object)[
                        'payment_id' => $payment->transaction_id,
                        'status_text' => $payment->transaction_status, // 'settlement' or 'capture'
                        'status' => 1,
                        'payment_method' => 'midtrans',
                    ];

                    // Process the successful payment
                    $result = $this->handlePayment($checkout, $transaction);
                    return $this->handleCheckoutResponse($result, true);
                } else {
                    $logMessage = "Midtrans: Amount mismatch for token: {$token}. Expected: {$expectedAmount} {$expectedCurrency}, Paid: {$paidAmount} {$paidCurrency}";
                    log_message('critical', $logMessage);
                    return $this->paymentErrorResponse(trans("msg_payment_error"), true);
                }
            }

            throw new \Exception('Midtrans server verification failed. Status: ' . ($payment->transaction_status ?? 'Unknown'));

        } catch (\Exception $e) {
            log_message('error', "Midtrans payment verification failed: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"), true);
        }
    }

    /**
     * Verify the Iyzico payment and finalize the order.
     *
     * @method GET
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeIyzicoPayment()
    {
        // Get Iyzico config from the database
        $config = getPaymentGateway('iyzico');
        if (empty($config) || !$config->status) {
            return $this->paymentErrorResponse("Payment method not found!");
        }

        // Get data from GET request (redirected from pre_system event)
        $token = $this->request->getGet('token');
        $conversationId = $this->request->getGet('conversation_id');
        $checkoutToken = $this->request->getGet('checkout_token');

        // Validate that all required data is present
        if (empty($token) || empty($conversationId) || empty($checkoutToken)) {
            return $this->paymentErrorResponse("Invalid request! Missing payment data.");
        }

        // Find and validate the checkout session using our unique token
        $checkout = $this->checkoutModel->getCheckoutByToken($checkoutToken);
        if (empty($checkout)) {
            log_message('error', "Iyzico: Checkout not found for token: {$checkoutToken}");
            return $this->paymentErrorResponse("No payment session found for the given token!");
        }

        try {
            $iyzico = new Iyzico($config);
            $payment = $iyzico->retrieveCheckoutResult($token, $conversationId);

            if ($payment->getPaymentStatus() === 'SUCCESS') {
                $paidAmount = $payment->getPaidPrice();
                $paidCurrency = $payment->getCurrency();

                $expectedAmount = numToDecimal($checkout->grand_total);
                $expectedCurrency = $checkout->currency_code;

                // Use bccomp for safe floating-point number comparison
                if (bccomp((string)$paidAmount, (string)$expectedAmount, 2) === 0 && $paidCurrency == $expectedCurrency) {
                    $transaction = (object)[
                        'payment_id' => $payment->getPaymentId(),
                        'status_text' => 'SUCCESS',
                        'status' => 1,
                        'payment_method' => 'iyzico',
                    ];

                    // Process the successful payment
                    $result = $this->handlePayment($checkout, $transaction);
                    return $this->handleCheckoutResponse($result);
                } else {
                    $logMessage = "Iyzico: Amount mismatch for token: {$checkoutToken}. Expected: {$expectedAmount} {$expectedCurrency}, Paid: {$paidAmount} {$paidCurrency}";
                    log_message('critical', $logMessage);
                    return $this->paymentErrorResponse(trans("msg_payment_error"));
                }
            }

            throw new \Exception('Iyzico server verification failed. Status: ' . $payment->getPaymentStatus());

        } catch (\Exception $e) {
            log_message('error', "Iyzico payment verification failed: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }
    }

    /**
     * Create the order with a "pending_payment" status when a PayTabs payment is initiated.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completePayTabsPayment()
    {
        // Get the base64 encoded data from the GET parameter set by your redirect script.
        $postDataEncoded = $this->request->getGet('post_data');
        if (empty($postDataEncoded)) {
            return $this->paymentErrorResponse("Invalid payment data received.");
        }

        // Decode the data safely.
        $postDataJson = base64_decode($postDataEncoded, true);
        if ($postDataJson === false) {
            return $this->paymentErrorResponse("Failed to decode payment data.");
        }

        $postData = json_decode($postDataJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->paymentErrorResponse("Invalid JSON format in payment data.");
        }

        // Get the transaction reference using the correct key 'tran_ref'.
        $transactionRef = $postData['tranRef'] ?? null;
        if (empty($transactionRef)) {
            return $this->paymentErrorResponse("Transaction reference not found in payment data.");
        }

        try {
            // Process the order
            $result = $this->processPayTabsOrder($transactionRef, false);
            return $this->handleCheckoutResponse($result);

        } catch (Exception $e) {
            log_message('error', "[PayTabs] Payment processing failed after user return: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }
    }

    /**
     * Processes PayTabs order
     *
     * @param string|null $transactionRef The transaction reference from PayTabs.
     * @param bool $isWebhook Indicates if the call comes from a server notification.
     */
    private function processPayTabsOrder(?string $transactionRef, bool $isWebhook = false)
    {
        if (empty($transactionRef)) {
            throw new \Exception("Transaction reference is missing.");
        }

        // Get PayTabs config
        $config = getPaymentGateway('paytabs');
        if (empty($config) || !$config->status) {
            throw new \Exception("PayTabs payment gateway is not enabled.");
        }

        // Securely verify the transaction status with the PayTabs API
        $paytabs = new PayTabs($config);
        $response = $paytabs->verifyPayment($transactionRef);
        if ($response->tran_ref !== $transactionRef) {
            throw new \Exception("Transaction reference mismatch during verification.");
        }

        // Find the checkout using the cart_id from the verified response
        $checkout = $this->checkoutModel->getCheckoutByToken($response->cart_id);
        if (empty($checkout)) {
            throw new \Exception("Checkout not found for cart_id: " . $response->cart_id);
        }

        // Check if payment was approved ('A' stands for Approved)
        if ($response->payment_result->response_status === 'A') {
            $paidAmount = $response->cart_amount;
            $paidCurrency = $response->cart_currency;

            $expectedAmount = numToDecimal($checkout->grand_total);
            $expectedCurrency = $checkout->currency_code;

            // Securely compare amounts and currency
            if (bccomp((string)$paidAmount, (string)$expectedAmount, 2) === 0 && $paidCurrency == $expectedCurrency) {
                $transaction = (object)[
                    'payment_id' => $response->tran_ref,
                    'status_text' => $response->payment_result->response_message,
                    'status' => 1,
                    'payment_method' => 'paytabs',
                ];

                return $this->handlePayment($checkout, $transaction, $isWebhook);
            } else {
                throw new \Exception("Amount/currency mismatch. Expected {$expectedAmount} {$expectedCurrency} but paid {$paidAmount} {$paidCurrency}.");
            }
        } else {
            throw new \Exception("Payment not approved by PayTabs. Status: " . $response->payment_result->response_message);
        }
    }

    /**
     * Create the order with a "pending_payment" status when a YooMoney payment is initiated.
     *
     * @method GET
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeYoomoneyPayment()
    {
        // Get YooMoney config from the database
        $config = getPaymentGateway('yoomoney');
        if (empty($config) || !$config->status) {
            return $this->paymentErrorResponse("Payment method not found!");
        }

        $token = $this->request->getGet('token');

        // Find the pending checkout session using our unique token
        $checkout = $this->checkoutModel->getCheckoutByToken($token);
        if (empty($checkout)) {
            log_message('error', "YooMoney: Checkout not found for token: {$token}");
            return $this->paymentErrorResponse("No payment session found for the given token!");
        }

        try {
            $transaction = (object)[
                'payment_id' => 'N/A',
                'status_text' => 'pending_payment',
                'status' => 0,
                'payment_method' => 'yoomoney',
            ];

            // Process the transaction
            $result = $this->handlePayment($checkout, $transaction);
            return $this->handleCheckoutResponse($result);

        } catch (\Exception $e) {
            log_message('error', "YooMoney payment verification failed: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }
    }

    /**
     * Processes YooMoney order
     *
     * @param object $session The YooMoney Checkout Session object.
     * @param bool $isWebhook Indicates if the call comes from a server notification.
     */
    private function processYooMoneyOrder(object $notificationData, bool $isWebhook = false)
    {
        $token = $notificationData->label ?? null;
        if (empty($token)) {
            throw new \Exception("YooMoney: Empty checkout token.");
        }

        $checkout = $this->checkoutModel->getCheckoutByToken($token);
        if (empty($checkout)) {
            throw new \Exception("YooMoney: Checkout not found for token: {$token}");
        }

        // Compare expected amount (gross amount paid)
        if (bccomp((string)$checkout->grand_total, (string)$notificationData->withdrawAmount, 2) === 0) {

            $transaction = (object)[
                'payment_id' => $notificationData->operationId,
                'status_text' => 'Paid',
                'status' => 1,
                'payment_method' => 'yoomoney',
            ];

            $this->handlePayment($checkout, $transaction, $isWebhook);

            $this->checkoutModel->updatePaymentTransactionAfterWebHook($checkout, $transaction);

            return true;

        } else {
            log_message('critical', 'YooMoney: AMOUNT MISMATCH for token ' . $token . '. Expected: '
                . $checkout->grand_total . ', Paid (Gross): ' . $notificationData->withdrawAmount . ', Received (Net): ' . $notificationData->amount);

            return false;
        }
    }

    /**
     * Verify the Mercado Pago payment and finalize the order after the user returns.
     *
     * @method GET
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     */
    public function completeMercadoPagoPayment()
    {
        // Mercado Pago provides payment details in the query string.
        $paymentId = $this->request->getGet('payment_id');

        if (empty($paymentId)) {
            return $this->paymentErrorResponse();
        }

        $config = getPaymentGateway('mercado_pago');
        if (empty($config)) {
            return $this->paymentErrorResponse();
        }

        try {
            $mercadoPago = new MercadoPago($config, $this->baseVars->appName);

            // Verify the payment with the API to get trusted data. DO NOT trust URL parameters.
            $payment = $mercadoPago->verifyPayment($paymentId);
            if ($payment) {
                // If verification is successful, pass the trusted payment object to the shared processing function.
                $result = $this->processMercadoPagoOrder($payment, false);
                return $this->handleCheckoutResponse($result);
            } else {
                // This means the payment status was not 'approved' or verification failed.
                throw new Exception("Mercado Pago payment verification failed or payment was not approved.");
            }

        } catch (Exception $e) {
            log_message('error', "Mercado Pago processing failed after user return: " . $e->getMessage());
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }
    }

    /**
     * Process Mercado Pago order
     *
     * @param object $payment The trusted payment object from the Mercado Pago API.
     * @param bool $isWebhook Indicates if the call comes from a webhook.
     */
    private function processMercadoPagoOrder(object $payment, bool $isWebhook = false)
    {
        $token = $payment->external_reference ?? null;
        if (empty($token)) {
            throw new Exception("Mercado Pago: Payment object is missing 'external_reference' (checkout_token).");
        }

        $checkout = $this->checkoutModel->getCheckoutByToken($token);
        if (empty($checkout)) {
            throw new Exception("Mercado Pago: Checkout not found for token: {$token}");
        }

        // If the order is already marked as paid, no need to process again.
        if ($checkout->status === 'paid') {
            if ($isWebhook) {
                return true;
            }
            // For user returns, it means they refreshed the success page.
            return ['status' => 1, 'redirectUrl' => $this->checkoutModel->createOrderRedirectUrl($checkout)];
        }

        // Verify amount and currency
        $paidAmount = $payment->transaction_amount;
        $paidCurrency = $payment->currency_id;

        $expectedAmount = (float)numToDecimal($checkout->grand_total);
        $expectedCurrency = $checkout->currency_code;

        // Compare amounts. A small tolerance might be needed for floating point inaccuracies.
        if (abs($paidAmount - $expectedAmount) > 0.01 || strtoupper($paidCurrency) != strtoupper($expectedCurrency)) {
            $logMessage = "Mercado Pago: Amount/currency mismatch for token: {$token}. Expected: {$expectedAmount} {$expectedCurrency}, Paid: {$paidAmount} {$paidCurrency}";
            throw new Exception($logMessage);
        }

        // If all checks pass, create the final transaction
        $transaction = (object)[
            'payment_id' => (string)$payment->id,
            'status_text' => $payment->status,
            'status' => 1,
            'payment_method' => 'mercado_pago',
        ];

        return $this->handlePayment($checkout, $transaction, $isWebhook);
    }

    /**
     * Create the order with a "pending_payment" status when a Bank Transfer payment is initiated.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeBankTransferPayment()
    {
        $checkoutToken = inputPost('checkout_token');

        $checkout = $this->checkoutModel->getCheckoutByToken($checkoutToken);
        if (empty($checkout)) {
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }

        $transaction = (object)[
            'payment_id' => $checkout->transaction_number,
            'status_text' => 'pending_payment',
            'status' => 0,
            'payment_method' => 'bank_transfer',
        ];

        // Process the transaction
        $result = $this->handlePayment($checkout, $transaction);
        return $this->handleCheckoutResponse($result);
    }

    /**
     * Accept Cash on Delivery and finalize the order.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeCashOnDeliveryPayment()
    {
        $checkoutToken = inputPost('checkout_token');

        $checkout = $this->checkoutModel->getCheckoutByToken($checkoutToken);
        if (empty($checkout)) {
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }

        $transaction = (object)[
            'payment_id' => '-',
            'status_text' => 'pending_payment',
            'status' => 1,
            'payment_method' => 'cash_on_delivery',
        ];

        // Process the transaction
        $result = $this->handlePayment($checkout, $transaction);

        return $this->handleCheckoutResponse($result);
    }

    /**
     * Verify wallet balance payment and finalize the order.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function completeWalletBalancePayment()
    {
        if (!authCheck() || $this->paymentSettings->wallet_status != 1) {
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }

        $checkoutToken = inputPost('checkout_token');

        $checkout = $this->checkoutModel->getCheckoutByToken($checkoutToken);
        if (empty($checkout)) {
            return $this->paymentErrorResponse(trans("msg_payment_error"));
        }

        $hasEnoughFunds = canPayWithBalance($checkout->grand_total, $checkout->currency_code);
        if (!$hasEnoughFunds) {
            return $this->paymentErrorResponse(trans("msg_insufficient_balance"));
        }

        $transaction = (object)[
            'payment_id' => $checkout->transaction_number,
            'status_text' => 'Succeeded',
            'status' => 1,
            'payment_method' => 'wallet_balance',
        ];

        // Process the transaction
        $result = $this->handlePayment($checkout, $transaction);

        if (!empty($result) && !empty($result['status'])) {
            //add expense
            $earningsModel = new EarningsModel();
            $orderNumber = !empty($result['orderNumber']) ? $result['orderNumber'] : 0;
            $earningsModel->addExpense($checkout, $transaction, $orderNumber);
        }

        return $this->handleCheckoutResponse($result);
    }

    /**
     * Creates a new order or service record based on the checkout type
     *
     * @param object $checkout The checkout session object.
     * @param object $transaction The verified transaction object.
     * @param bool $isWebhook Determines the type of error response.
     * @return mixed The result of the specific checkout handler.
     */
    private function handlePayment($checkout, $transaction, $isWebhook = false)
    {
        if (empty($checkout) || empty($transaction)) {
            log_message('error', "handlePayment: Checkout or transaction object is empty.");
            return $isWebhook ? false : $this->checkoutModel->errorCheckoutResponse();
        }

        return match ($checkout->checkout_type) {
            'product' => $this->checkoutModel->handleProductCheckout($checkout, $transaction),
            'service' => match ($checkout->service_type) {
                'add_funds' => $this->checkoutModel->handleAddFundsServiceCheckout($checkout, $transaction),
                'membership' => $this->checkoutModel->handleMembershipServiceCheckout($checkout, $transaction),
                'promote' => $this->checkoutModel->handleProductPromotionServiceCheckout($checkout, $transaction),
                default => $isWebhook ? false : $this->checkoutModel->errorCheckoutResponse(),
            },
            default => $isWebhook ? false : $this->checkoutModel->errorCheckoutResponse(),
        };
    }

    /**
     * --------------------------------------------------------------------------
     * Payment Gateway Webhooks & Callback Notifications
     * --------------------------------------------------------------------------
     */

    /**
     * Handles incoming webhook notifications from Stripe.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface The HTTP response object.
     */
    public function handleStripeWebhook()
    {
        try {
            $config = getPaymentGateway('stripe');
            if (empty($config) || !$config->status) {
                throw new \Exception('Stripe webhook failed: Configuration missing or gateway disabled.');
            }

            $stripe = new Stripe($config, $this->baseVars->appName);
            $session = $stripe->handleWebhook();

            if (!empty($session)) {
                if (!$this->processStripeOrder($session, true)) {
                    $paymentIntentId = $session->payment_intent ?? ($session->id ?? 'N/A');
                    log_message('critical', "Stripe webhook: Order processing FAILED for a successful payment. Payment Intent/Event ID: {$paymentIntentId}. MANUAL CHECK REQUIRED.");
                }
            }

            return $this->response->setStatusCode(200, 'OK');

        } catch (\Throwable $e) {
            log_message('error', 'Stripe Webhook Controller Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500, 'Internal Server Error');
        }
    }

    /**
     * Handles incoming webhook notifications from Razorpay.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface The HTTP response object.
     */
    public function handleRazorpayWebhook()
    {
        try {
            $config = getPaymentGateway('razorpay');
            if (empty($config) || !$config->status) {
                throw new \Exception('Razorpay webhook failed: Configuration missing or gateway disabled.');
            }

            $razorpay = new Razorpay($config);

            $result = $razorpay->handleWebhook($this->request);

            if (!empty($result) && !empty($result['paymentEntity']) && !empty($result['checkoutToken'])) {
                if (!$this->processRazorpayOrder($result['paymentEntity'], $result['checkoutToken'], true)) {
                    $paymentId = $result['paymentEntity']->id ?? 'N/A';
                    log_message('critical', "Razorpay webhook: Order processing FAILED for a successful payment. Payment ID: {$paymentId}. MANUAL CHECK REQUIRED.");
                }
            }

            return $this->response->setStatusCode(200, 'OK');

        } catch (\Throwable $e) {
            log_message('error', 'Razorpay Webhook Controller Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500, 'Internal Server Error');
        }
    }

    /**
     * Handles incoming webhook notifications from PayTabs.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface The HTTP response object.
     */
    public function handlePayTabsWebhook()
    {
        try {
            $config = getPaymentGateway('paytabs');
            if (empty($config) || !$config->status) {
                throw new \Exception('PayTabs configuration missing.');
            }

            $paytabs = new PayTabs($config);
            $transactionRef = $paytabs->handleWebhook();

            if (!empty($transactionRef)) {
                if (!$this->processPayTabsOrder($transactionRef, true)) {
                    log_message('critical', "PayTabs webhook: Order processing FAILED for a successful payment. tran_ref: {$transactionRef}. MANUAL CHECK REQUIRED.");
                }
            }

            return $this->response->setStatusCode(200, 'OK');

        } catch (\Throwable $e) {
            log_message('error', 'PayTabs Webhook Controller Unhandled Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500, 'Internal Server Error');
        }
    }

    /**
     * Handles incoming webhook notifications from dLocal Go.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface The HTTP response object.
     */
    public function handleDlocalGoWebhook()
    {
        try {
            $config = getPaymentGateway('dlocalgo');
            if (empty($config) || !$config->status) {
                log_message('info', 'dLocal Go webhook received but gateway is disabled. Acknowledged with 200 OK.');
                return $this->response->setStatusCode(200, 'OK');
            }

            $dlocal = new DlocalGo($config);
            $payload = $dlocal->handleWebhook($this->request);

            if (!empty($payload)) {
                if (!$this->processDlocalGoOrder($payload, true)) {
                    $checkoutToken = $payload['order_id'] ?? 'N/A';
                    log_message('critical', "dLocal Go webhook: Order processing FAILED for a successful payment. Checkout Token: {$checkoutToken}. MANUAL CHECK REQUIRED.");
                }
            }

            return $this->response->setStatusCode(200, 'OK');

        } catch (\Throwable $e) {
            log_message('error', 'dLocal Go Webhook Controller Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500, 'Internal Server Error');
        }
    }

    /**
     * Handles incoming webhook notifications from YooMoney.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface The HTTP response object.
     */
    public function handleYooMoneyWebhook()
    {
        try {
            $config = getPaymentGateway('yoomoney');
            if (empty($config) || !$config->status) {
                throw new \Exception('YooMoney webhook failed: Configuration missing or gateway disabled.');
            }

            $yooMoney = new YooMoney($config);
            $notificationData = $yooMoney->handleWebhook($this->request);

            if (!empty($notificationData)) {
                if (!$this->processYooMoneyOrder($notificationData, true)) {
                    $checkoutToken = $notificationData->label ?? 'N/A';
                    log_message('critical', "YooMoney webhook: Order processing FAILED for a successful payment. Checkout Token: {$checkoutToken}. MANUAL CHECK REQUIRED.");
                }
            }

            return $this->response->setStatusCode(200, 'OK');

        } catch (\Throwable $e) {
            log_message('error', 'YooMoney Webhook Controller Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500, 'Internal Server Error');
        }
    }

    /**
     * Handles incoming webhook notifications from Mercado Pago.
     *
     * @method POST
     * @return \CodeIgniter\HTTP\ResponseInterface The HTTP response object.
     */
    public function handleMercadoPagoWebhook()
    {
        try {
            $config = getPaymentGateway('mercado_pago');
            if (empty($config) || !$config->status) {
                throw new Exception('Mercado Pago webhook failed: Configuration missing or gateway disabled.');
            }

            $mercadoPago = new MercadoPago($config, $this->baseVars->appName);
            $payment = $mercadoPago->handleWebhook($this->request);

            if (!empty($payment)) {
                if (isset($payment->status) && $payment->status === 'approved') {
                    if (!$this->processMercadoPagoOrder($payment, true)) {
                        log_message('critical', "Mercado Pago webhook: Order processing FAILED for an approved payment. Payment ID: {$payment->id}. MANUAL CHECK REQUIRED.");
                    }
                } else {
                    log_message('info', "Mercado Pago webhook: Received a notification for a non-approved payment. Status: {$payment->status}. Skipping.");
                }
            }

            return $this->response->setStatusCode(200, 'OK');

        } catch (\Throwable $e) {
            log_message('error', 'Mercado Pago Webhook Controller Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500, 'Internal Server Error');
        }
    }

    /**
     * Order Completed
     */
    public function orderCompleted($orderNumber)
    {
        $data['title'] = trans("msg_order_completed");
        $data['description'] = trans("msg_order_completed") . ' - ' . $this->baseVars->appName;
        $data['keywords'] = trans("msg_order_completed") . ',' . $this->baseVars->appName;
        $data['order'] = $this->orderModel->getOrderByOrderNumber($orderNumber);

        if (empty($data['order'])) {
            return redirect()->to(langBaseUrl());
        }

        if (empty(helperGetSession('mds_show_order_completed_page'))) {
            return redirect()->to(langBaseUrl());
        }

        echo view('partials/_header', $data);
        echo view('cart/order_completed', $data);
        echo view('partials/_footer');
    }

    /**
     * Service Payment Completed
     */
    public function servicePaymentCompleted()
    {
        $data = setPageMeta(trans("msg_payment_completed"));

        $checkoutToken = inputGet('checkout');
        $transactionId = inputGet('tx_id');

        if (empty($checkoutToken) || empty($transactionId)) {
            return redirect()->to(langBaseUrl());
        }

        $checkout = $this->checkoutModel->getCheckoutByToken($checkoutToken);
        if (empty($checkout)) {
            return redirect()->to(langBaseUrl());
        }

        if ($checkout->service_type === 'membership') {
            $data['transaction'] = $this->membershipModel->getMembershipTransaction($transactionId);
            $data['membershipRequestType'] = 'new';
            if (!empty($checkout->service_data)) {
                $serviceData = json_decode($checkout->service_data);
                if (!empty($serviceData) && !empty($serviceData->planRequestType) && $serviceData->planRequestType == 'renew') {
                    $data['membershipRequestType'] = 'renew';
                }
            }
        } elseif ($checkout->service_type === 'promote') {
            $promoteModel = new PromoteModel();
            $data['transaction'] = $promoteModel->getTransaction($transactionId);
        } elseif ($checkout->service_type === 'add_funds') {
            $earningsModel = new EarningsModel();
            $data['transaction'] = $earningsModel->getDepositTransaction($transactionId);
        }
        if (empty($data['transaction'])) {
            return redirect()->to(langBaseUrl());
        }
        $data['checkout'] = $checkout;

        echo view('partials/_header', $data);
        echo view('cart/payment_completed_service', $data);
        echo view('partials/_footer');
    }

    /**
     * Processes the checkout result and either redirects or returns a JSON response.
     *
     * @param mixed $result Array with keys: 'status', 'message', 'redirectUrl'
     * @param bool $isAjax Whether the request is an AJAX request
     * @return \CodeIgniter\HTTP\RedirectResponse|array|null
     */
    private function handleCheckoutResponse(mixed $result, bool $isAjax = false)
    {
        if (!is_array($result)) {
            return null;
        }

        if (!empty($result['message'])) {
            if (!empty($result['status'])) {
                setSuccessMessage($result['message']);
            } else {
                setErrorMessage($result['message']);
            }
        }

        if (!empty($result['redirectUrl'])) {
            if ($isAjax) {
                return jsonResponse($result);
            }

            return redirect()->to($result['redirectUrl']);
        }

        return null;
    }

    /**
     * Returns an error response specific to payment failures.
     *
     * @param string|null $message Optional error message to display.
     * @param bool $isAjax Whether the request is an AJAX call.
     * @return \CodeIgniter\HTTP\RedirectResponse|array
     */
    private function paymentErrorResponse(?string $message = null, bool $isAjax = false)
    {
        $response = $this->checkoutModel->errorCheckoutResponse($message);
        if (empty($message)) {
            $message = $response['message'];
        }

        if (!empty($message)) {
            setErrorMessage($message);
        }

        if ($isAjax) {
            return jsonResponse($response);
        }

        return redirect()->to($response['redirectUrl']);
    }
}
