<?php

/**
 * Plugin Name: BiggiDroid Payment Gateway
 * Plugin URI:  https://biggidroid.com
 * Author:      Adeleye Ayodeji
 * Author URI:  https://adeleyeayodeji.com
 * Description: This plugin allows you to accept payment on your website using BiggiDroid Payment Gateway.
 * Version:     0.1.0
 * License:     GPL-2.0+
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: biggidroid-payment
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit('You must not access this file directly');
}

//define the plugin constants
define('BIGGI_DROID_PAYMENT_VERSION', time());
define('BIGGI_DROID_PAYMENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BIGGI_DROID_PAYMENT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BIGGIDROID_TEXT_DOMAIN', 'biggidroid-payment');
//biggidroid file
define('BIGGI_DROID_PAYMENT_FILE', __FILE__);

//check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    //add notice
    add_action('admin_notices', 'biggidroid_payment_woocommerce_notice');
} else {
    //add action plugins loaded
    add_action('plugins_loaded', 'biggidroid_payment_init');
    //add settings url
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'biggidroid_payment_settings_link');
    //register woocommerce payment gateway
    add_filter('woocommerce_payment_gateways', 'biggidroid_payment_gateway');
    //woocommerce_blocks_loaded
    add_action('woocommerce_blocks_loaded', 'biggidroid_payment_block_support');
}

/**
 * biggidroid_payment_block_support
 * 
 */
function biggidroid_payment_block_support()
{
    //check for AbstractPaymentMethodType class
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        //include the BiggiDroidBlockPaymentMethod class
        include_once BIGGI_DROID_PAYMENT_PLUGIN_PATH . '/includes/class-biggidroid-block-payment-method.php';

        // registering our block support class
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                $payment_method_registry->register(new WC_BiggiDroid_Payment_Gateway_Block_Support);
            }
        );
    }
}


//initialize the plugin
function biggidroid_payment_init()
{
    //check if the class exists Biggi_Droid_Payment_Gateway
    if (!class_exists('Biggi_Droid_Payment_Gateway')) {
        include_once BIGGI_DROID_PAYMENT_PLUGIN_PATH . '/includes/main-file.php';
    }
}

//biggidroid_payment_woocommerce_notice
function biggidroid_payment_woocommerce_notice()
{
    ob_start();
    //require the admin notice template
    require_once BIGGI_DROID_PAYMENT_PLUGIN_PATH . '/templates/admin_notice.php';
    $html = ob_get_clean();
    echo $html;
}

//biggidroid_payment_settings_link
function biggidroid_payment_settings_link($links)
{
    $settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=biggidroid_payment">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

//biggidroid_payment_gateway
function biggidroid_payment_gateway($gateways)
{
    $gateways[] = 'Biggi_Droid_Payment_Gateway';
    return $gateways;
}
