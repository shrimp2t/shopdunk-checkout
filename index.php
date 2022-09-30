<?php

/*
 * Plugin Name: ShopDunk Checkout
 * Plugin URI: https://rudrastyh.com/woocommerce/payment-gateway-plugin.html
 * Description: Take credit card payments on your store.
 * Author: Misha Rudrastyh
 * Author URI: http://rudrastyh.com
 * Version: 1.0.1
 */


define('SD_CO_PATH',  dirname(__FILE__));
define('SD_CO_URL', plugin_dir_url(__FILE__));

define('SD_API_ORDERS', 'http://shopdunk-integration.reach.com.vn/api/v1/orders');



add_action('plugins_loaded', function () {
	require dirname(__FILE__) . '/inc/functions.php';
	require dirname(__FILE__) . '/inc/form-field.php';
	require dirname(__FILE__) . '/inc/assets.php';
	require dirname(__FILE__) . '/inc/checkout-fields.php';
	require dirname(__FILE__) . '/inc/checkout-hooks.php';
	require dirname(__FILE__) . '/gateways/contact.php';
	require dirname(__FILE__) . '/gateways/gateway.php';
	require dirname(__FILE__) . '/admin/admin-settings.php';
});


add_filter('woocommerce_payment_gateways', 'sd_add_gateway_class');
function sd_add_gateway_class($gateways)
{
	$gateways[] = 'WC_SD_Contact_Payment_Gateway'; // your class name is here
	$gateways[] = 'WC_SD_Bank_Transfer_Payment_Gateway'; // your class name is here

	return $gateways;
}




add_action('wp_loaded', function () {
	

});

// add_filter('woocommerce_order_needs_payment', '__return_false', 999);
