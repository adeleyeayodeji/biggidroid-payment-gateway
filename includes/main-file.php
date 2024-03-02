<?php
//security check
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
        //process admin options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        //admin script
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
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
}
