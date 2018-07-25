
$(function() {

	var $form = $('#form-shipping-charges');

	if ($form.length == 0) return;

	function init() {

		$('[name="shipping_country_id"], [name="paymode_id"]').on('change', function(){
			console.log('called');
			es.update_shipping_charges();
		});

		$('[name="billing_country_id"]').on('change', function(){
			setTimeout(function(){
				es.update_shipping_charges();
			}, 1000);
		});	

		$form.on('submit', function(event) {
			event.preventDefault();

			var form_data = {
				// security
				'csrf_test_name': $form.find('[name="csrf_test_name"]').val(),

				'country_id': $('[name="shipping_country_id"]').val(),

				'paymode_id': $('[name="paymode_id"]:checked').val(),

				'product_styles': (function(){
					var product_styles = [];
					$('[data-cart-product]').each(function(){
						product_styles.push({
							'style_id': $(this).data('style-id'),
							'quantity': $(this).find('[data-quantity]').data('quantity')
						});
					});
					return product_styles;
				})()
			};

			console.log(form_data);

			$.ajax({
				type: "POST",
				url: $form.attr('action'),
				data: form_data,
				success: function(data) {
					var response = JSON.parse(data);
					if (response.status == 'success') {
						$('[data-shipping-cost]').val(es.format_money(response.charges));
						$('[data-shipping-cost]').attr('data-shipping-cost', response.charges);
						es.update_subtotal();
					}
				}
			});

		});

	}

	es.update_shipping_charges = function() {
		$form.submit();
	}

	// Let's go
	init();
});