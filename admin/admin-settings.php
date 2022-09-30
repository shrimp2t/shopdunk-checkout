<?php



/**
 * Create the section beneath the products tab
 **/
add_filter( 'woocommerce_get_sections_advanced', 'woorei_mysettings_add_section' );
function woorei_mysettings_add_section( $sections ) {
	$sections['shopdunk_settings'] = __( 'Shopdunk' );
	return $sections;
}

/**
 * Add settings to the specific section we created before
 */
add_filter( 'woocommerce_get_settings_advanced', 'sd_checkout_settings', 10, 2 );
function sd_checkout_settings( $settings, $current_section ) {

	
	/**
	 * Check the current section is what we want
	 **/
	if ( $current_section == 'shopdunk_settings' ) {
		$settings = array();
		// Add Title to the Settings
		$settings[] = array( 'name' => __( 'Shopdunk Payment Settings', 'text-domain' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure WC Slider', 'text-domain' ), 'id' => 'shopdunk_settings' );
		// Add first checkbox option
		$settings[] = array(
			'name'     => __( 'Bật tính năng', 'text-domain' ),
			'id'       => 'sd_payment_option_enable',
			'type'     => 'checkbox',
			'css'      => 'min-width:300px;',
			'desc'     => __( 'Check vào đây để bật các tính năng.', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'Odoo API URL', 'text-domain' ),
			'id'       => 'sd_odoo_api_url',
			'type'     => 'text',
			'desc'     => __( 'URL đến kết nối đến API Odoo', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'Odoo API Access Token', 'text-domain' ),
			'id'       => 'sd_odoo_api_token',
			'type'     => 'text',
			'desc'     => __( 'API Token', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'Webhook URL(s)', 'text-domain' ),
			'id'       => 'sd_webhook_urls',
			'type'     => 'textarea',
			'custom_attributes' => [
				'rows' => 8
			],
			'css' => 'width: 100%',
			'desc'     => __( 'Địa chỉ webhook khi order được cập nhật sẽ gửi data đến, Có thể dùng nhiều URL mỗi URL trên một dòng.', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'Sản phẩm cho phép cọc', 'text-domain' ),
			'id'       => 'sd_partial_pay_allowed_skus',
			'type'     => 'textarea',
			'custom_attributes' => [
				'rows' => 8,
				'cols' => 100,
				
			],
			
			'desc'     => __( 'SKU(s) sản phẩm có trong đơn hàng. Đơn hàng có sku này mới được phép thanh toán một phần. Mỗi sku trên một dòng.', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'Số tiền cọc', 'text-domain' ),
			'id'       => 'sd_partial_order_amount',
			'type'     => 'number',
			'desc'     => __( 'Số tiền cọc', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'Store mặc định', 'text-domain' ),
			'id'       => 'sd_default_store',
			'type'     => 'text',
			'desc'     => __( 'Id Store mặc định, 3 ký tự', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'TK ngân hàng của store mặc định', 'text-domain' ),
			'id'       => 'sd_default_bank_account',
			'type'     => 'text',
			'desc'     => __( 'Tk ngân hàng của store mặc định.', 'text-domain' ),
		);

		$settings[] = array(
			'name'     => __( 'Tên ngân hàng của store mặc định', 'text-domain' ),
			'id'       => 'sd_default_bank_name',
			'type'     => 'text',
			'desc'     => __( 'Tên ngân hàng của store mặc định.', 'text-domain' ),
		);
		
		$settings[] = array( 'type' => 'sectionend', 'id' => 'shopdunk_settings' );
		return $settings;
	
	/**
	 * If not, return the standard settings
	 **/
	} else {
		return $settings;
	}
}

