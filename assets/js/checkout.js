// alert( 'OK' );

jQuery(function ($) {

	$('#shipping_sd_method_field input[name="shipping_sd_method"]').on('change', function () {
		const m = $('#shipping_sd_method_field input[name="shipping_sd_method"]:checked').val();
		
		if (m === 'ship') {
			$('#shipping_store_id_field, #shipping_store_area_field').hide();
			$('#shipping_province_field, #shipping_quan_huyen_field, #shipping_phuong_xa_field, #shipping_address_1_field').show();
			$('#shipping_province').trigger( 'change' );
		} else {
			$('#shipping_province_field, #shipping_quan_huyen_field, #shipping_phuong_xa_field, #shipping_address_1_field').hide();
			$('#shipping_store_id_field, #shipping_store_area_field').show();
			$('#shipping_store_area').trigger( 'change' );
		}
	});

	$('#shipping_sd_method_field input[value="store"]').trigger('change');


	// Change Store area

	$('#shipping_store_area').on('change', function () {
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

			$('#shipping_store_id').html(aOptions.join(' '));
		}


	});



	$('#shipping_province').on('change', function () {
		const v = $(this).val();
		const lv = $(this).data('lv');

		if (lv != v) {
			$(this).data('lv', v);
			const defaultOpt = `<option value="">Chọn quận/huyện</option>`;
			const aOptions = [];
			$.each(SD_Checkout.quan_huyen, (index, el) => {
				if (parseInt( el.province_id ) === parseInt( v )) {
					aOptions.push(`<option value="${el.code}">${el.name}</option>`);
				}
			});

			if (!aOptions.length) {
				aOptions.push(defaultOpt);
			}

			$('#shipping_phuong_xa').data('lv', false);
			$('#shipping_quan_huyen').data('lv', false);

			$('#shipping_quan_huyen').html(aOptions.join(' '));
			$('#shipping_quan_huyen option:first-child').attr( 'selected', true );
			$('#shipping_quan_huyen').trigger('change');
		}
	});

	$('#shipping_quan_huyen').on('change', function () {
		const v = $(this).val();
		const pid =  $('#shipping_province').val();
		const lv = $(this).data('lv');
		const id = `${v}_${pid}`;

		if (lv != id) {
			$(this).data('lv', id);
			const defaultOpt = `<option value="">Chọn xã/phường</option>`;
			const aOptions = [];
			$.each(SD_Checkout.phuong_xa, (index, el) => {
				if (parseInt( el.province_id ) === parseInt( pid ) && parseInt( el.id_px ) === parseInt( v ) ) {
					aOptions.push(`<option value="${el.code}">${el.name}</option>`);
				}
			});

			if (!aOptions.length) {
				aOptions.push(defaultOpt);
			}

			$('#shipping_phuong_xa').html(aOptions.join(' '));
		}
	});




	$('#shipping_store_area').trigger('change');
	$('#shipping_province').trigger('change');


	




});