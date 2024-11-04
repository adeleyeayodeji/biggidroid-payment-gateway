<?php
//security check

use WpOrg\Requests\Requests;

if (!defined('ABSPATH')) {
    exit('You must not access this file directly');
}

/**
 * BiggiDroid Payment Gateway
 * 
 * @author Adeleye Ayodeji
 * @since 1.0.0
 */
class Biggi_Droid_Payment_Gateway extends WC_Payment_Gateway_CC
{
    /**
     * Public key
     * 
     * @var string
     */
    public $public_key;

    /**
     * Secret key
     * 
     * @var string
     */
    public $secret_key;

    /**
     * Test mode
     * 
     */
    public $test_mode;

    /**
     * Title
     * 
     */
    public $title;

    /**
     * Description
     * 
     */
    public $description;

    /**
     * test_public_key
     * 
     */
    public $test_public_key;

    /**
     * test_secret_key
     * 
     */
    public $test_secret_key;

    /**
     * live_public_key
     * 
     */
    public $live_public_key;

    /**
     * live_secret_key
     * 
     */
    public $live_secret_key;

    /**
     * saved_cards
     * 
     */
    public $saved_cards;

    /**
     * remove_cancel_order_button
     * 
     */
    public $remove_cancel_order_button;

    /**
     * Constructor
     * 
     * @since 1.0.0
     */
    public function __construct()
    {
        //id
        $this->id = 'biggidroid_payment';
        //has fields
        $this->has_fields = true;
        //method title
        $this->method_title = __('BiggiDroid Payment Gateway', BIGGIDROID_TEXT_DOMAIN);
        //description
        $this->method_description = __('This plugin allows you to accept payment on your website using BiggiDroid Payment Gateway.', BIGGIDROID_TEXT_DOMAIN);
        //supports
        $this->supports = array(
            'products'
            // 'tokenization',
            // 'subscriptions',
            // 'subscription_cancellation',
            // 'subscription_suspension',
            // 'subscription_reactivation',
            // 'subscription_amount_changes',
            // 'subscription_date_changes',
            // 'subscription_payment_method_change',
            // 'subscription_payment_method_change_customer',
            // 'subscription_payment_method_change_admin',
            // 'multiple_subscriptions',
        );
        //Add form fields
        $this->init_form_fields();

        ///// Form Fields //////////////////
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');

        //saved_cards
        $this->saved_cards = false;

        $this->test_mode = 'yes' === $this->get_option('test_mode') ? true : false;

        $this->test_public_key = $this->get_option('test_public_key');
        $this->test_secret_key = $this->get_option('test_secret_key');

        $this->live_public_key = $this->get_option('live_public_key');
        $this->live_secret_key = $this->get_option('live_secret_key');

        $this->public_key = $this->test_mode ? $this->test_public_key : $this->live_public_key;
        $this->secret_key = $this->test_mode ? $this->test_secret_key : $this->live_secret_key;

        //process admin options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        //register checkout script
        add_action('wp_enqueue_scripts', [$this, 'checkout_scripts']);
        //admin script
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        //woocommerce available payment gateways
        add_action('woocommerce_available_payment_gateways', [$this, 'available_payment_gateways']);
        //register receipt page
        add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
        //register api endpoint
        add_action('woocommerce_api_biggi_droid_payment_gateway', [$this, 'biggidroid_payment_callback']);

        //is valid for use
        if (!$this->is_valid_for_use()) {
            //disable the gateway because the current currency is not supported
            $this->enabled = 'no';
        }
    }

    /**
     * Get Paystack payment icon URL.
     */
    public function get_logo_url()
    {
        $url = WC_HTTPS::force_https_url(BIGGI_DROID_PAYMENT_PLUGIN_URL . '/assets/images/biggidroid_payment.png');
        return apply_filters('woocommerce_biggidroid_payment_icon', $url, $this->id);
    }

    /**
     * available_payment_gateways
     * 
     */
    public function available_payment_gateways($available_gateways)
    {
        if (!$this->is_available()) {
            //unset the gateway
            unset($available_gateways[$this->id]);
        }

        return $available_gateways;
    }

    /**
     * is available
     * 
     */
    public function is_available()
    {
        return $this->enabled === 'yes';
    }

    /**
     * Admin scripts
     * 
     * @since 1.0.0
     */
    public function admin_scripts()
    {
        wp_enqueue_script('biggidroid-admin-script', BIGGI_DROID_PAYMENT_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], BIGGI_DROID_PAYMENT_VERSION, true);
    }

    /**
     * Checkout scripts
     * 
     */
    public function checkout_scripts()
    {
        //check if checkout page
        if (!is_checkout()) {
            return;
        }

        //check if gateway is enabled
        if ($this->enabled === 'no') {
            return;
        }

        //get the order id
        $order_id = absint(get_query_var('order-pay'));

        //if order id is not set
        if (!$order_id) {
            return;
        }

        //get the order
        $order = wc_get_order($order_id);

        $payment_method = method_exists($order, 'get_payment_method') ? $order->get_payment_method() : $order->payment_method;

        //check if payment method is biggidroid payment
        if ($payment_method !== $this->id) {
            return;
        }

        //add paystack script https://js.paystack.co/v2/inline.js
        wp_enqueue_script('paystack-script', 'https://js.paystack.co/v2/inline.js', [], BIGGI_DROID_PAYMENT_VERSION, true);
        //enqueue checkout script
        wp_enqueue_script('biggidroid-checkout-script', BIGGI_DROID_PAYMENT_PLUGIN_URL . 'assets/js/checkout.js', ['jquery'], BIGGI_DROID_PAYMENT_VERSION, true);

        //localize script
        wp_localize_script('biggidroid-checkout-script', 'biggidroid_checkout_params', [
            'public_key' => $this->public_key,
            'order_id' => $order_id,
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
            'amount' => $order->get_total() * 100, //convert to cents
            'redirect_url' => WC()->api_request_url('Biggi_Droid_Payment_Gateway')
        ]);
    }

    /**
     * Initialize form fields
     * 
     * @since 1.0.0
     */
    public function init_form_fields()
    {
        $form_fields = apply_filters('woo_biggidroid_payment', [
            'enabled' => [
                'title' => __('Enable/Disable', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'checkbox',
                'label' => __('Enable BiggiDroid Payment Gateway', BIGGIDROID_TEXT_DOMAIN),
                'default' => 'no'
            ],
            //test mode select
            'test_mode' => [
                'title' => __('Payment Mode', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'select',
                'description' => __('Select the test mode', BIGGIDROID_TEXT_DOMAIN),
                'options' => [
                    'yes' => __('Test', BIGGIDROID_TEXT_DOMAIN),
                    'no' => __('Live', BIGGIDROID_TEXT_DOMAIN)
                ],
                'default' => 'yes'
            ],
            'title' => [
                'title' => __('Title', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', BIGGIDROID_TEXT_DOMAIN),
                'default' => __('BiggiDroid Payment Gateway', BIGGIDROID_TEXT_DOMAIN),
                'desc_tip' => true
            ],
            'description' => [
                'title' => __('Description', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', BIGGIDROID_TEXT_DOMAIN),
                'default' => __('Pay with your credit card via BiggiDroid Payment Gateway.', BIGGIDROID_TEXT_DOMAIN),
                'desc_tip' => true
            ],
            'live_public_key' => [
                'title' => __('Live Public Key', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('This is the live public key provided by BiggiDroid Payment Gateway.', BIGGIDROID_TEXT_DOMAIN),
                'default' => '',
                'desc_tip' => true
            ],
            'live_secret_key' => [
                'title' => __('Live Secret Key', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('This is the live secret key provided by BiggiDroid Payment Gateway.', BIGGIDROID_TEXT_DOMAIN),
                'default' => '',
                'desc_tip' => true
            ],
            //test pk key
            'test_public_key' => [
                'title' => __('Test Public Key', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('This is the test public key provided by BiggiDroid Payment Gateway.', BIGGIDROID_TEXT_DOMAIN),
                'default' => '',
                'desc_tip' => true
            ],
            //test secret key
            'test_secret_key' => [
                'title' => __('Test Secret Key', BIGGIDROID_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('This is the test secret key provided by BiggiDroid Payment Gateway.', BIGGIDROID_TEXT_DOMAIN),
                'default' => '',
                'desc_tip' => true
            ]
        ]);
        //return form fields to woocommerce
        $this->form_fields = $form_fields;
    }

    /**
     * Receipt page
     * 
     * @param int $order_id
     */
    public function receipt_page($order_id)
    {
        //get the order
        $order = wc_get_order($order_id);
        //get cancel url
        $cancel_url = $order->get_cancel_order_url();

        echo '<div id="yes-add">' . __('Thank you for your order, please click the button below to pay with BiggiDroid Payment Gateway.', BIGGIDROID_TEXT_DOMAIN) . '</div>';

        echo '<div id="biggidroid_form"><form id="order_review" method="post" action="' . WC()->api_request_url('Biggi_Droid_Payment_Gateway') . '"></form><button class="button alt" id="wc-biggidroid-payment-gateway-button">' . __('Pay Now', BIGGIDROID_TEXT_DOMAIN) . '</button>';

        // if (!$this->remove_cancel_order_button) {
        //     echo '  <a class="button cancel" id="cancel-btn" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel order &amp; restore cart', BIGGIDROID_TEXT_DOMAIN) . '</a></div>';
        // }
    }

    /**
     * Is valid for use
     * 
     */
    public function is_valid_for_use()
    {
        //check if current currency is within array
        $supported_currencies = ['NGN', 'USD', 'GHS'];
        if (in_array(get_woocommerce_currency(), $supported_currencies)) {
            return true;
        }

        //error message
        $msg = sprintf(__('BiggiDroid Payment Gateway does not support %s currency.', BIGGIDROID_TEXT_DOMAIN), get_woocommerce_currency());
        //add error message to woocommerce
        WC_Admin_Settings::add_error($msg);
        //return false
        return false;
    }

    /**
     * Payment form on checkout page
     */
    public function payment_fields()
    {

        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }

        if (!is_ssl()) {
            return;
        }

        if ($this->supports('tokenization') && is_checkout() && $this->saved_cards && is_user_logged_in()) {
            $this->tokenization_script();
            $this->saved_payment_methods();
            $this->save_payment_method_checkbox();
        }
    }

    /**
     * Process the payment.
     *
     * @param int $order_id
     *
     * @return array|void
     */
    public function process_payment($order_id)
    {

        if (is_user_logged_in() && isset($_POST['wc-' . $this->id . '-new-payment-method']) && true === (bool)
        $_POST['wc-' . $this->id . '-new-payment-method'] && $this->saved_cards) {

            update_post_meta($order_id, '_wc_monnify_save_card', true);
        }

        $order = wc_get_order($order_id);

        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        );
    }

    /**
     * Payment validation callback
     * 
     */
    public function biggidroid_payment_callback()
    {
        try {
            //check if paystack reference is set
            if (!isset($_GET['paystack_reference'])) {
                throw new Exception("Paystack reference is required");
            }

            //check if is not empty
            if (empty($_GET['paystack_reference'])) {
                throw new Exception("Paystack reference is required");
            }

            //sanitize the paystack reference
            $paystack_reference = sanitize_text_field($_GET['paystack_reference']);
            //get the order id
            $order_id = absint($_GET['order_id']);

            //get the order
            $order = wc_get_order($order_id);

            //validate the payment
            $response = Requests::get('https://api.paystack.co/transaction/verify/' . $paystack_reference, [
                'Authorization' => 'Bearer ' . $this->secret_key
            ]);

            //get response body
            $response_body = json_decode($response->body, true);

            //log response body
            error_log("Response Body: " . print_r($response_body, true));

            //check if response is successful
            if ($response_body['status']) {
                //check if order is already completed
                if ($order->get_status() === 'completed') {
                    //redirect to thank you page
                    wp_redirect($order->get_checkout_order_received_url());
                    exit;
                }

                //add message
                wc_add_notice(__('Payment successful using BiggiDroid Payment Gateway, thank you for your order.', BIGGIDROID_TEXT_DOMAIN), 'success');

                //order notice
                $order->add_order_note(__('Payment successful using BiggiDroid Payment Gateway, thank you for your order.', BIGGIDROID_TEXT_DOMAIN));
                //add payment reference
                $order->update_meta_data('_biggidroid_payment_reference', $paystack_reference);

                //update order status
                $order->update_status('completed');

                //complete payment
                $order->payment_complete();

                //redirect to thank you page
                wp_redirect($order->get_checkout_order_received_url());
                exit;
            }

            //update order status to failed
            $order->update_status('failed');

            //order note
            $order->add_order_note(__('Payment failed using BiggiDroid Payment Gateway, please try again.', BIGGIDROID_TEXT_DOMAIN));

            //quit and redirect
            wp_redirect($this->get_return_url($order));
            exit;
        } catch (Exception $e) {
            //log error
            error_log("BiggiDroid Payment Gateway Error: " . $e->getMessage());
            //checkout url
            $checkout_url = wc_get_checkout_url();
            //add error message
            wc_add_notice($e->getMessage(), 'error');
            //redirect to checkout
            wp_redirect($checkout_url);
            exit;
        }
    }
}
