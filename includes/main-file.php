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
    }
}
