<?php

/*
 * Plugin Name: ShopDunk Checkout
 * Plugin URI: https://rudrastyh.com/woocommerce/payment-gateway-plugin.html
 * Description: Take credit card payments on your store.
 * Author: Misha Rudrastyh
 * Author URI: http://rudrastyh.com
 * Version: 1.0.1
 */

use function MyListing\set_cookie;

define('SD_CO_PATH',  dirname(__FILE__));
define('SD_CO_URL', plugin_dir_url(__FILE__));

define('SD_API_ORDERS', 'http://shopdunk-integration.reach.com.vn/api/v1/orders');


add_action('plugins_loaded', function () {
	if (!function_exists('WC')) {
		return;
	}
	
	require dirname(__FILE__) . '/inc/functions.php';

	require dirname(__FILE__) . '/gateways/gateway-contact.php';
	require dirname(__FILE__) . '/gateways/gateway-transfer.php';
	require dirname(__FILE__) . '/admin/admin-settings.php';

	$load = false;
	$test_on_live =  get_option('sd_checkout_test_on_live');
	if (wc_string_to_bool( $test_on_live )) {
		if (isset($_COOKIE['sd_co_live_test']) && $_COOKIE['sd_co_live_test'] == 'on') {
			$load = true;
		}
		if (isset($_GET['sd_co_live_test'])) {
			switch ($_GET['sd_co_live_test']) {
				case 'on':
					setcookie('sd_co_live_test', 'on', time() + 60 * DAY_IN_SECONDS);
					$load = true;
					break;
				case 'off':
					unset($_COOKIE['sd_co_live_test']);
					setcookie('sd_co_live_test', null, -1, '/');
					$load = false;
					break;
			}
		}
	} else {
		$load = true;
	}

	if ($load) {
		// Frontend.
		require dirname(__FILE__) . '/inc/form-field-render.php';
		require dirname(__FILE__) . '/inc/assets.php';
		require dirname(__FILE__) . '/inc/checkout-fields.php';
		require dirname(__FILE__) . '/inc/checkout-hooks.php';
	}
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
