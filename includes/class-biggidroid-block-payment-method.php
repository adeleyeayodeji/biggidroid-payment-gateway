<?php
//check for security

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

if (!defined('ABSPATH')) {
    exit('You must not access this file directly');
}

final class WC_BiggiDroid_Payment_Gateway_Block_Support extends AbstractPaymentMethodType
{
    /**
     * Payment method name
     * 
     */
    protected $name = 'biggidroid_payment';

    /**
     * Initialize the payment method type
     * 
     */
    public function initialize()
    {
        $this->settings = get_option('woocommerce_biggidroid_payment_settings', array());

        //add failure message
        add_action('woocommerce_rest_checkout_process_payment_with_context', array($this, 'add_failure_message'), 10, 2);
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active()
    {
        $payment_gateways_class = WC()->payment_gateways();
        $payment_gateways       = $payment_gateways_class->payment_gateways();
        return $payment_gateways['biggidroid_payment']->is_available();
    }

    /**
     * Add failed payment notice to the payment details.
     *
     * @param PaymentContext $context Holds context for the payment.
     * @param PaymentResult  $result  Result object for the payment.
     */
    public function add_failure_message(PaymentContext $context, PaymentResult &$result)
    {
        if ('biggidroid_payment' === $context->payment_method) {
            add_action(
                'wc_gateway_biggidroid_payment_process_payment_error',
                function ($failed_notice) use (&$result) {
                    $payment_details                 = $result->payment_details;
                    $payment_details['errorMessage'] = wp_strip_all_tags($failed_notice);
                    $result->set_payment_details($payment_details);
                }
            );
        }
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data()
    {
        $payment_gateways_class = WC()->payment_gateways();
        $payment_gateways       = $payment_gateways_class->payment_gateways();
        $gateway                = $payment_gateways['biggidroid_payment'];

        return array(
            'title'             => $this->get_setting('title'),
            'description'       => $this->get_setting('description'),
            'supports'          => array_filter($gateway->supports, array($gateway, 'supports')),
            'allow_saved_cards' => $gateway->saved_cards,
            'logo_urls'         => array($payment_gateways['biggidroid_payment']->get_logo_url()),
        );
    }


    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles()
    {
        $asset_path   = plugin_dir_path(BIGGI_DROID_PAYMENT_FILE) . 'assets/js/block/block.asset.php';
        $version      = null;
        $dependencies = array();
        if (file_exists($asset_path)) {
            $asset        = require $asset_path;
            $version      = isset($asset['version']) ? $asset['version'] : $version;
            $dependencies = isset($asset['dependencies']) ? $asset['dependencies'] : $dependencies;
        }

        wp_register_script(
            'wc-biggidroid-payment-blocks-integration',
            plugin_dir_url(BIGGI_DROID_PAYMENT_FILE) . 'assets/js/block/block.js',
            $dependencies,
            $version,
            true
        );

        //logo url
        $logo_url = WC_HTTPS::force_https_url(BIGGI_DROID_PAYMENT_PLUGIN_URL . '/assets/images/biggidroid_payment.png');

        //localize script
        wp_localize_script('wc-biggidroid-payment-blocks-integration', 'biggidroid_payment_data', array(
            'logo_url' => $logo_url
        ));

        return array('wc-biggidroid-payment-blocks-integration');
    }
}
