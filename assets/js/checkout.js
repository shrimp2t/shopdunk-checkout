// alert( 'OK' );

jQuery(function ($) {

	// Retry order SD_Checkout
	$('.sd-retry-pay-btn').on('click', function (e) {
		e.preventDefault();
		const btn = $(this);
		btn.attr('disabled', 'disabled');
		btn.addClass('loading');
		const id = $(this).data('id');
		$.ajax({
			url: SD_Checkout.ajax_url,
			type: 'post',
			data: {
				action: 'sd_retry_order',
				id
			},
			success: function (res) {
				console.log('res', res);
				btn.removeAttr('disabled');
				btn.removeClass('loading');
				if (!res.success) {
					$('.sd-retry-pay-msg').append(res.message).removeClass('hide');
				} else {
					// reload page.
					window.location = window.location;
				}
			}
		});
	});

	// Auto change cart qty.
	let updateCartTimeout = false;
	$(document.body).on('change', 'form.woocommerce-cart-form input.qty', function () {
		const $cart = $('form.woocommerce-cart-form');
		if (updateCartTimeout) {
			clearTimeout(updateCartTimeout);
		}
		updateCartTimeout = setTimeout(function () {
			$cart.find('button[name="update_cart"]').click();
			updateCartTimeout = false;
		}, 1000);

	});

	// Change secondary products.
	$('.secondary_ps_name select').on('change', function () {
		const pn = $(this).val();
		const i = $(this).data('i');
		console.log(pn);
		let colorOptions = [];
		let storageOptions = [];
		if (SD_Checkout?.secondary_products[pn]) {
			SD_Checkout?.secondary_products[pn].colors.map(el => {
				colorOptions.push(`<option value="${el}">${el}</option>`)
			});
			SD_Checkout?.secondary_products[pn].storage.map(el => {
				storageOptions.push(`<option value="${el}">${el}</option>`)
			});
		}

		$('#secondary_p' + i + '_color').html(colorOptions.join(' '));
		$('#secondary_p' + i + '_storage').html(storageOptions.join(' '));


	});


	$('#more_shipping_info').on('change', function (e) {
		const v = $(this).is(':checked') ? true : false;
		if (v) {
			$('.more_shipping_field').show();
		} else {
			$('.more_shipping_field').hide();
		}
	});

	$('#vat_check').on('change', function (e) {
		const v = $(this).is(':checked') ? true : false;
		if (v) {
			$('.vat_field').show();
		} else {
			$('.vat_field').hide();
		}
	});

	$('#secondary_check').on('change', function (e) {
		const v = $(this).is(':checked') ? true : false;
		if (v) {
			$('.secondary_ps_field').show();
		} else {
			$('.secondary_ps_field').hide();
		}
	});


	$('#secondary_check, #vat_check, #more_shipping_info, .secondary_ps_name select').trigger('change');


	$('#sd_shipping_method_field input[name="sd_shipping_method"]').on('change', function () {
		const m = $('#sd_shipping_method_field input[name="sd_shipping_method"]:checked').val();
		if (m === 'ship') {
			$('#sd_store_id_field, #sd_store_area_field').hide();
			$('#sd_shipping_province_id_field, #sd_shipping_qh_id_field, #sd_shipping_px_id_field, #shipping_address_1_field').show();
			$('#sd_shipping_province_id').trigger('change');
		} else {
			$('#sd_shipping_province_id_field, #sd_shipping_qh_id_field, #sd_shipping_px_id_field, #shipping_address_1_field').hide();
			$('#sd_store_id_field, #sd_store_area_field').show();
			$('#sd_store_area').trigger('change');
		}
	});

	$('#sd_shipping_method_field input[value="store"]').trigger('change');
	
	// Change Store area.

	$('#sd_store_area').on('change', function () {
		const v = $(this).val();
		const lv = $(this).data('lv');
		if (lv != v) {
			$(this).data('lv', v);
			const defaultOpt = `<option value="">Chọn cửa hàng</option>`;
			const aOptions = [];
			$.each(SD_Checkout.stores, (index, el) => {
				if (el.province === v) {
					aOptions.push(`<option value="${el.code}">${el.address}</option>`);
				}
			});
			if (!aOptions.length) {
				aOptions.push(defaultOpt);
			}
			$('#sd_store_id').html(aOptions.join(' '));
		}
	});



	$('#sd_shipping_province_id').on('change', function () {
		const v = $(this).val();
		const lv = $(this).data('lv');

		if (lv != v) {
			$(this).data('lv', v);
			const defaultOpt = `<option value="">Chọn quận/huyện</option>`;
			const aOptions = [];
			$.each(SD_Checkout.quan_huyen, (index, el) => {
				if (parseInt(el.province_id) === parseInt(v)) {
					aOptions.push(`<option value="${el.code}">${el.name}</option>`);
				}
			});

			if (!aOptions.length) {
				aOptions.push(defaultOpt);
			}

			$('#sd_shipping_px_id').data('lv', false);
			$('#sd_shipping_qh_id').data('lv', false);

			$('#sd_shipping_qh_id').html(aOptions.join(' '));
			$('#sd_shipping_qh_id option:first-child').attr('selected', true);
			$('#sd_shipping_qh_id').trigger('change');
		}
	});

	$('#sd_shipping_qh_id').on('change', function () {
		const v = $(this).val();
		const pid = $('#sd_shipping_province_id').val();
		const lv = $(this).data('lv');
		const id = `${v}_${pid}`;

		if (lv != id) {
			$(this).data('lv', id);
			const defaultOpt = `<option value="">Chọn xã/phường</option>`;
			const aOptions = [];
			$.each(SD_Checkout.phuong_xa, (index, el) => {
				if (parseInt(el.province_id) === parseInt(pid) && parseInt(el.id_px) === parseInt(v)) {
					aOptions.push(`<option value="${el.code}">${el.name}</option>`);
				}
			});

			if (!aOptions.length) {
				aOptions.push(defaultOpt);
			}

			$('#sd_shipping_px_id').html(aOptions.join(' '));
		}
	});




	$('#sd_store_area').trigger('change');
	$('#sd_shipping_province_id').trigger('change');


	// Chọn phương thức thanh toán.
	const $morebtn = $('<a class="other-payment-gateways" href="#">Xem thêm các phương thức thanh toán khác</a>');
	$('.wc_payment_methods li:first-child').append($morebtn)
	$('.wc_payment_methods li').not(':first-child').addClass('hide');
	$(document).on('click', '.other-payment-gateways', function (e) {
		e.preventDefault();
		console.log('clcicked');
		$('.wc_payment_methods li').not(':first-child').removeClass('hide');
		$('.other-payment-gateways').hide();
	})



});