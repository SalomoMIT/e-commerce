document.addEventListener('DOMContentLoaded', function () {

    // Check for PayPal configuration and initialize if it exists
    if (typeof configPaypal !== 'undefined') {
        initializePayPal(configPaypal);
    }

    // Check for Paystack configuration and initialize if it exists
    if (typeof configPaystack !== 'undefined') {
        initializePaystack(configPaystack);
    }

    // Check for Razorpay configuration and initialize if it exists
    if (typeof configRazorpay !== 'undefined') {
        initializeRazorpay(configRazorpay);
    }

    // Check for Flutterwave configuration and initialize if it exists
    if (typeof configFlutterwave !== 'undefined') {
        initializeFlutterwave(configFlutterwave);
    }

    // Check for Midtrans configuration and initialize if it exists
    if (typeof configMidtrans !== 'undefined') {
        initializeMidtrans(configMidtrans);
    }

    // Check for PayTabs configuration and initialize if it exists
    if (typeof configPayTabs !== 'undefined') {
        initializePayTabs(configPayTabs);
    }

    // Check for MercadoPago configuration and initialize if it exists
    if (typeof configMercadoPago !== 'undefined') {
        initializeMercadoPago(configMercadoPago);
    }

});

/**
 * Initializes all PayPal related logic.
 * @param {object} config The configuration object passed from the server.
 */
function initializePayPal(config) {
    const pageLoader = document.getElementById('page-loader');
    const paymentContainer = document.getElementById('payment-container');

    // Check if PayPal SDK was loaded
    if (typeof paypal === 'undefined') {
        showError("PayPal SDK could not be loaded. Please check your connection and try again.");
        pageLoader.classList.add('d-none');
        paymentContainer.classList.remove('d-none');
        document.getElementById('paypal-button-container').innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
        return;
    }

    // Render the PayPal Buttons
    paypal.Buttons({
        createOrder: function (data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: config.totalAmount,
                        currency_code: config.currencyCode
                    }
                }]
            });
        },

        onApprove: function (data, actions) {
            $('.paypal-loader').removeClass('d-none');
            $('#paypal-button-container').addClass('d-none');

            return actions.order.capture().then(function (details) {
                if (details.status !== 'COMPLETED') {
                    showError(`Payment not completed. Status: ${details.status}. Please contact support.`);
                    $('.paypal-loader').addClass('d-none');
                    return;
                }

                var dataArray = {
                    'payment_id': data.orderID,
                    'currency': config.currencyCode,
                    'payment_amount': config.totalAmount,
                    'payment_status': details.status,
                    'checkout_token': config.checkoutToken
                };

                $.ajax({
                    type: 'POST',
                    url: config.paymentPostUrl,
                    data: dataArray,
                    success: function (response) {
                        if (response.status == 1 && response.redirectUrl) {
                            window.location.href = response.redirectUrl;
                        } else {
                            showError(response.message || 'There was an issue processing your order on our server. Please try again.');
                            $('.paypal-loader').addClass('d-none');
                            $('#paypal-button-container').removeClass('d-none');
                        }
                    },
                    error: function (xhr, status, error) {
                        showError('A communication error occurred. Please check your internet connection and try again.');
                        $('.paypal-loader').addClass('d-none');
                        $('#paypal-button-container').removeClass('d-none');
                    }
                });
            });
        },

        onError: function (err) {
            showError('An error occurred during the PayPal transaction. Please try again.');
            $('.paypal-loader').addClass('d-none');
            $('#paypal-button-container').removeClass('d-none');
        }
    }).render('#paypal-button-container').then(() => {
        pageLoader.classList.add('d-none');
        paymentContainer.classList.remove('d-none');
    }).catch((err) => {
        showError("Could not display PayPal buttons. Please refresh the page and try again.");
        pageLoader.classList.add('d-none');
        paymentContainer.classList.remove('d-none');
        document.getElementById('paypal-button-container').innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
    });
}

/**
 * Initializes all Paystack related logic.
 * @param {object} config The configuration object passed from the server.
 */
function initializePaystack(config) {
    const paystackButton = document.getElementById('btn-paystack');

    if (!paystackButton) {
        console.error('Paystack button not found.');
        return;
    }

    paystackButton.addEventListener('click', function () {
        payWithPaystack();
    });

    function payWithPaystack() {
        // Check if PaystackPop is available
        if (typeof PaystackPop === 'undefined') {
            alert('Paystack script could not be loaded. Please check your connection and try again.');
            return;
        }

        const handler = PaystackPop.setup({
            key: config.publicKey,
            email: config.email,
            amount: config.amount,
            currency: config.currency,
            ref: config.ref,
            callback: function (response) {
                const data = {
                    'payment_id': response.reference,
                    'payment_status': response.status,
                    'amount': config.amount,
                    'currency': config.currency,
                    'checkout_token': config.checkoutToken
                };

                // Show a loader or disable the button
                paystackButton.disabled = true;
                paystackButton.textContent = MdsConfig.text.processing;

                $.ajax({
                    type: 'POST',
                    url: config.paymentPostUrl,
                    data: data,
                    success: function (response) {
                        if (response.status == 1 && response.redirectUrl) {
                            window.location.href = response.redirectUrl;
                        } else {
                            location.reload();
                        }
                    },
                    error: function () {
                        showError('A communication error occurred. Please try again.');
                        location.reload();
                    }
                });
            },
            onClose: function () {
                console.log('Paystack popup closed by user.');
            }
        });
        handler.openIframe();
    }
}

/**
 * Initializes all Razorpay related logic.
 * @param {object} config The configuration object passed from the server.
 */
function initializeRazorpay(config) {
    const razorpayButton = document.getElementById('rzp-button1');
    const paymentLoader = document.querySelector('.payment-loader');

    if (!razorpayButton) {
        console.error('Razorpay button not found.');
        return;
    }

    // Check if Razorpay SDK was loaded
    if (typeof Razorpay === 'undefined') {
        showError("Razorpay SDK could not be loaded. Please check your connection and try again.");
        return;
    }

    const options = {
        key: config.key,
        amount: config.amount,
        currency: config.currency,
        name: config.name,
        description: config.description,
        image: config.image,
        order_id: config.orderId,
        handler: function (response) {
            // Show loader and hide the button to prevent multiple clicks
            if (paymentLoader) paymentLoader.classList.remove('d-none');
            razorpayButton.classList.add('d-none');

            const data = {
                'razorpay_payment_id': response.razorpay_payment_id,
                'razorpay_order_id': response.razorpay_order_id,
                'razorpay_signature': response.razorpay_signature,
                'checkout_token': config.checkoutToken
            };

            $.ajax({
                type: 'POST',
                url: config.paymentPostUrl,
                data: data,
                success: function (response) {
                    if (response.status == 1 && response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    } else {
                        // Show error and re-enable the button
                        showError(response.message || 'An unknown error occurred. Please try again.');
                        if (paymentLoader) paymentLoader.classList.add('d-none');
                        razorpayButton.classList.remove('d-none');
                    }
                },
                error: function () {
                    showError('A communication error occurred. Please check your internet connection and try again.');
                    if (paymentLoader) paymentLoader.classList.add('d-none');
                    razorpayButton.classList.remove('d-none');
                }
            });
        },
        theme: {
            color: "#528FF0"
        }
    };

    const rzp = new Razorpay(options);

    razorpayButton.addEventListener('click', function (e) {
        e.preventDefault();
        rzp.open();
    });

    // Optional: Handle payment failure events from Razorpay's side
    rzp.on('payment.failed', function (response) {
        showError(`Payment Failed: ${response.error.description}`);
    });
}

/**
 * Initializes all Flutterwave related logic.
 * @param {object} config The configuration object passed from the server.
 */
function initializeFlutterwave(config) {
    const flutterwaveButton = document.getElementById('btn-flutterwave');

    if (!flutterwaveButton) {
        console.error('Flutterwave button not found.');
        return;
    }

    flutterwaveButton.addEventListener('click', function () {
        // Check if Flutterwave script was loaded
        if (typeof FlutterwaveCheckout === 'undefined') {
            showError("Flutterwave SDK could not be loaded. Please check your connection and try again.");
            return;
        }

        FlutterwaveCheckout({
            public_key: config.publicKey,
            tx_ref: config.tx_ref,
            amount: config.amount,
            currency: config.currency,
            payment_options: "card, mobilemoneyghana",
            redirect_url: config.redirectUrl,
            meta: config.meta,
            customer: config.customer,
            customizations: config.customizations,
            onclose: function () {
                location.reload();
            },
            callback: function (data) {
            }
        });
    });
}

/**
 * Initializes all Midtrans related logic.
 * @param {object} config The configuration object passed from the server.
 */
function initializeMidtrans(config) {
    const midtransButton = document.getElementById('btn-midtrans');
    const paymentLoader = document.querySelector('.payment-loader');

    if (!midtransButton) {
        console.error('Midtrans button not found.');
        return;
    }

    // Check if Midtrans Snap SDK was loaded
    if (typeof snap === 'undefined') {
        showError("Midtrans SDK could not be loaded. Please check your connection and try again.");
        return;
    }

    midtransButton.addEventListener('click', function () {
        snap.pay(config.snapToken, {
            enabledPayments: ['credit_card'],
            onSuccess: function (result) {
                if (paymentLoader) paymentLoader.classList.remove('d-none');
                midtransButton.classList.add('d-none');

                const postData = {
                    'transaction_id': result.transaction_id,
                    'order_id': result.order_id, // checkout_token
                    'payment_status': result.transaction_status,
                    'checkout_token': config.checkoutToken
                };

                $.ajax({
                    type: 'POST',
                    url: config.paymentPostUrl,
                    data: postData,
                    success: function (response) {
                        if (response.status == 1 && response.redirectUrl) {
                            window.location.href = response.redirectUrl;
                        } else {
                            showError(response.message || 'An unknown server error occurred.');
                            if (paymentLoader) paymentLoader.classList.add('d-none');
                            midtransButton.classList.remove('d-none');
                        }
                    },
                    error: function () {
                        showError('A communication error occurred. Please try again.');
                        if (paymentLoader) paymentLoader.classList.add('d-none');
                        midtransButton.classList.remove('d-none');
                    }
                });
            },
            onPending: function (result) {
                // Inform the user that the payment is pending
                showError(`Payment is pending. Status: ${result.status_message}`);
            },
            onError: function (result) {
                // Inform the user about the error
                showError(`Payment failed. Status: ${result.status_message}`);
            },
            onClose: function () {
                location.reload();
            }
        });
    });
}

/**
 * Initializes all PayTabs related logic.
 * @param {object} config The configuration object passed from the server.
 */
function initializePayTabs(config) {
    const paytabsButton = document.getElementById('btn-paytabs');
    const paymentLoader = document.querySelector('.payment-loader');

    if (!paytabsButton) {
        console.error('PayTabs button not found.');
        return;
    }
    if (typeof paytabs === 'undefined') {
        return showError("PayTabs SDK could not be loaded. Please check your connection.");
    }

    paytabsButton.addEventListener('click', function (e) {
        e.preventDefault();

        // Show loader and disable button
        if (paymentLoader) paymentLoader.classList.remove('d-none');
        paytabsButton.disabled = true;
        paytabsButton.classList.add('btn-disabled');

        // AJAX request to your server to create the payment page session
        $.ajax({
            type: 'POST',
            url: config.initiateUrl,
            data: {
                'checkout_token': config.checkoutToken
            },
            success: function (response) {
                // Check if the server responded with an error or missing URL
                if (response.status !== 'success' || !response.redirect_url) {
                    showError(response.message || 'An unknown error occurred while preparing the payment.');
                    if (paymentLoader) paymentLoader.classList.add('d-none');
                    paytabsButton.disabled = false;
                    paytabsButton.classList.remove('btn-disabled');
                    return;
                }

                // Launch PayTabs Framed Mode with the URL from the server
                var paymentPage = new paytabs.PaymentPage();
                paymentPage.show(response.redirect_url);

                // This function is called when the payment popup is closed
                paymentPage.on('close', function (result) {
                    // Always hide loader and re-enable button when popup closes
                    if (paymentLoader) paymentLoader.classList.add('d-none');
                    paytabsButton.disabled = false;
                    paytabsButton.classList.remove('btn-disabled');

                    // The 'result' object contains the transaction reference ('tran_ref').
                    // We must check if 'tran_ref' exists to proceed.
                    if (result && result.tran_ref) {
                        // Redirect to the final verification page, passing the transaction reference.
                        // The server will use this 'tran_ref' to securely verify the payment status.
                        let completionUrl = new URL(config.completionUrl);
                        completionUrl.searchParams.append('tran_ref', result.tran_ref);
                        completionUrl.searchParams.append('checkout_token', config.checkoutToken);
                        window.location.href = completionUrl.toString();
                    } else {
                        // This block runs if the user closes the popup without completing the payment.
                        console.log('PayTabs popup closed without a transaction.');
                    }
                });
            },
            error: function () {
                // Handle AJAX errors (e.g., server is down)
                showError('A network error occurred. Please try again.');
                if (paymentLoader) paymentLoader.classList.add('d-none');
                paytabsButton.disabled = false;
                paytabsButton.classList.remove('btn-disabled');
            }
        });
    });
}

/**
 * Initializes and renders the Mercado Pago Wallet Brick.
 *
 * @param {object} config - Configuration object for the brick.
 */
function initializeMercadoPago(config) {
    // Basic validation to ensure all required parameters are provided.
    if (!config || !config.publicKey || !config.preferenceId || !config.locale || !config.containerId) {
        console.error('Mercado Pago Brick Error: Missing required configuration.');
        const container = document.getElementById(config.containerId);
        if (container) {
            container.innerText = 'Error loading payment button. Configuration is missing.';
        }
        return;
    }

    try {
        // Initialize the Mercado Pago SDK with the public key and locale.
        const mp = new MercadoPago(config.publicKey, {
            locale: config.locale
        });

        // Initialize the Bricks builder.
        const bricksBuilder = mp.bricks();

        // Asynchronously create and render the Wallet Brick.
        const renderWalletBrick = async (builder) => {
            const settings = {
                initialization: {
                    preferenceId: config.preferenceId,
                },
                customization: {
                    texts: {
                        'action': 'pay' // You can change this to 'buy' or other actions.
                    },
                },
            };
            // The 'wallet' brick will be rendered inside the specified container div.
            window.walletBrickContainer = await builder.create('wallet', config.containerId, settings);
        };

        renderWalletBrick(bricksBuilder);

    } catch (error) {
        console.error('Error initializing Mercado Pago SDK:', error);
        // Display a user-friendly message in the container if something goes wrong.
        const container = document.getElementById(config.containerId);
        if (container) {
            container.innerText = 'Could not load payment button. Please refresh the page and try again.';
        }
    }
}

//show error
function showError(message) {
    Swal.fire({text: message, icon: 'error', confirmButtonText: MdsConfig.text.ok});
}
