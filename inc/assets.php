<?php

function sd_exclude_custom_logo_class_from_lazy_load($classes)
{
	$classes[] = 'not-lazy';
	return $classes;
}

add_action('wp_enqueue_scripts', function () {
	if (is_checkout()) {
		$css_ver = @filemtime(SD_CO_PATH . '/assets/css/checkout.css');
		$js_ver = @filemtime(SD_CO_PATH . '/assets/js/checkout.js');
		wp_enqueue_script('wc-cart');
		wp_enqueue_style('sd-checkout', SD_CO_URL . '/assets/css/checkout.css', [], $css_ver);
		wp_enqueue_script('sd-checkout', SD_CO_URL . '/assets/js/checkout.js', ['jquery'], $js_ver, true);

		$stores = sd_get_data_stores();
		wp_localize_script('sd-checkout', 'SD_Checkout', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'secondary_products' => sd_get_products_secondary(),
			'stores' => $stores,
			'provinces' => sd_get_data_provinces(),
			'quan_huyen' => sd_get_data_quan_huyen(),
			'phuong_xa' => sd_get_data_phuong_xa(),
		]);
	}
}, 999);
