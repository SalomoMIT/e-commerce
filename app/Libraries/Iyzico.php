<?php

namespace App\Libraries;

use Exception;
use InvalidArgumentException;

require_once APPPATH . 'ThirdParty/iyzipay/vendor/autoload.php';
require_once APPPATH . 'ThirdParty/iyzipay/vendor/iyzico/iyzipay-php/IyzipayBootstrap.php';

/**
 * Iyzico Integration Library for CodeIgniter 4
 *
 * This class provides a structured way to interact with the Iyzico API,
 * handling the creation of the checkout form and the verification of payment results.
 */
class Iyzico
{
    /**
     * The Iyzico Options object.
     * @var \Iyzipay\Options
     */
    private $options;

    /**
     * Constructor.
     *
     * Initializes the Iyzico SDK and sets up the configuration.
     *
     * @param object $config An object containing 'public_key', 'secret_key', and 'environment'.
     * @throws InvalidArgumentException If required keys are missing.
     */
    public function __construct(object $config)
    {
        if (empty($config->public_key) || empty($config->secret_key) || empty($config->environment)) {
            throw new InvalidArgumentException('Iyzico Public Key, Secret Key, and Environment are required.');
        }

        // Initialize the Iyzico SDK. This only needs to be done once.
        \IyzipayBootstrap::init();

        // Create and configure options
        $this->options = new \Iyzipay\Options();
        $this->options->setApiKey($config->public_key);
        $this->options->setSecretKey($config->secret_key);

        if ($config->environment == 'sandbox') {
            $this->options->setBaseUrl('https://sandbox-api.iyzipay.com');
        } else {
            $this->options->setBaseUrl('https://api.iyzipay.com');
        }
    }

    /**
     * Creates the Iyzico Checkout Form by building all necessary objects internally.
     *
     * @param object $checkout Checkout object class
     * @return \Iyzipay\Model\CheckoutFormInitialize The response object from Iyzico.
     * @throws Exception If the API call fails.
     */
    public function createCheckoutForm(object $checkout): \Iyzipay\Model\CheckoutFormInitialize
    {
        try {
            // A unique key for the transaction
            $conversationId = generateToken();
            // Get cart customer
            $customer = getCartCustomerData($checkout);
            // Price and currency
            $price = numToDecimal($checkout->grand_total);
            $currencyCode = $checkout->currency_code;
            // Set checkout title and category
            $basketItemName = getCheckoutPaymentTitle($checkout);
            $category = $checkout->checkout_type == 'service' ? trans('payment') : trans('sale');
            // Set callback url
            $callbackUrl = base_url() . 'mds-cl-iyzico-payment-redirect?conversation_id=' . $conversationId . '&checkout_token=' . $checkout->checkout_token . '&lang=' . selectedLangId();

            // Prepare Buyer object
            $buyer = new \Iyzipay\Model\Buyer();
            $buyer->setId($customer->id ?? 'guest_' . uniqid());
            $buyer->setName($customer->first_name ?? 'Guest');
            $buyer->setSurname($customer->last_name ?? 'User');
            $buyer->setGsmNumber($customer->phone_number ?? '0000000000');
            $buyer->setEmail($customer->email ?? 'guest@example.com');
            $buyer->setIdentityNumber('11111111111');
            $buyer->setRegistrationAddress('not_set');
            $buyer->setIp(getIPAddress() ?: '85.34.78.112');
            $buyer->setCity('not_set');
            $buyer->setCountry('not_set');
            $buyer->setZipCode('not_set');

            // Prepare Address object
            $address = new \Iyzipay\Model\Address();
            $address->setContactName(($customer->first_name ?? 'Guest') . ' ' . ($customer->last_name ?? 'User'));
            $address->setCity('not_set');
            $address->setCountry('not_set');
            $address->setAddress('not_set');
            $address->setZipCode('not_set');

            // Prepare Basket item
            $basketItem = new \Iyzipay\Model\BasketItem();
            $basketItem->setId('item_01');
            $basketItem->setName($basketItemName);
            $basketItem->setCategory1($category);
            $basketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $basketItem->setPrice($price);

            // Build the final request object
            $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId($conversationId);
            $request->setPrice($price);
            $request->setPaidPrice($price);
            $request->setCurrency($currencyCode);
            $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
            $request->setCallbackUrl($callbackUrl);
            $request->setEnabledInstallments([2, 3, 6, 9]);
            $request->setBuyer($buyer);
            $request->setShippingAddress($address);
            $request->setBillingAddress($address);
            $request->setBasketItems([$basketItem]);

            // Make the API call
            $checkoutForm = \Iyzipay\Model\CheckoutFormInitialize::create($request, $this->options);

            // If Iyzico returns a 'failure' status, throw an exception with the error message.
            if ($checkoutForm->getStatus() == 'failure') {
                throw new \Exception($checkoutForm->getErrorMessage());
            }

            return $checkoutForm;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieves the result of a checkout form payment from Iyzico.
     * This is used in the callback to verify the payment status.
     *
     * @param string $token The token received from the Iyzico callback.
     * @param string $conversationId The conversation ID for the transaction.
     * @return \Iyzipay\Model\CheckoutForm The verified payment result object.
     * @throws Exception If the verification call fails.
     */
    public function retrieveCheckoutResult(string $token, string $conversationId): \Iyzipay\Model\CheckoutForm
    {
        $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($conversationId);
        $request->setToken($token);

        try {
            $checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, $this->options);
            return $checkoutForm;
        } catch (Exception $e) {
            log_message('error', '[IyzicoLib] Checkout result retrieval failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
