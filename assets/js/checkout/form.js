
$(function() {

	var $form = $('#form-checkout');

	if ($form.length == 0) return;

	function init()	{

		$('#ship-to-bill').on('click', function() {
			var $input = $(this);
			if ($input.is(':checked')) {
				es.update_address('copy');
				$('[name^="shipping_"]').attr('disabled', 'disabled');
			} else {
				es.update_address('empty');
				$('[name^="shipping_"]').removeAttr('disabled');
			}
		});

		// if the form is already filled
		// $('[name^="shipping_"]').attr('disabled', 'disabled');
		// es.update_address('copy');

		// On Changing billing Address 
		// COPY address only if ship-to-bill input is checked
		$('[name^="billing_"]').on('change', function(){
			if ($('#ship-to-bill').is(':checked')) {
				es.update_address('copy');
				$('[name^="shipping_"]').attr('disabled', 'disabled');
			}
		});

		// remove disabled on submit
		$form.on('submit', function() {
	        $('[name^="shipping_"]').removeAttr('disabled');
	    });

		// Update with shipping
		es.update_payment_method();
		es.fix_currency();
		$('[name="shipping_country_id"]').on('change', function(){
			es.update_payment_method();			
			es.fix_currency();
		});
	}

	es.update_address = function(action) {
		$('[name^="billing_"]').each(function(){
			var $input = $(this);
			var field = $input.attr('name').replace('billing', 'shipping');

			$('[name="'+field+'"]').each(function(){
				if (action == 'copy')
				{
					$(this).val($input.val());
				}
				else
				{
					if (field == 'shipping_country_id' || 
						field == 'shipping_name_title_id')
					{
						$(this).find('option:eq(0)').attr('selected', true);
					}
					else
					{
						$(this).val('');
					}
					
				}
				
			});
		});

		// Update payment method
		es.update_payment_method();

		// fix currency
		es.fix_currency();
	};

	es.update_payment_method = function() {
		// COD only in India
		if ($('[name="shipping_country_id"]').val() != 94) {
			$('[name="paymode_id"]').filter('[value="1"]').click();
			$('[name="paymode_id"]').filter('[value="2"]').attr('disabled', 'disabled');
		} else {
			$('[name="paymode_id"]').filter('[value="2"]').removeAttr('disabled', 'disabled');
		}
	};

	es.fix_currency = function() {
		var cookie_country_id = docCookies.getItem('cookie_country_id');
		var removed = false;
		if (cookie_country_id != null) {
			// resetting country
			// $('[name="billing_country_id"]').val(String(cookie_country_id));
			$('[name="shipping_country_id"]').val(String(cookie_country_id));

			// deleting cookie
			docCookies.removeItem('cookie_country_id');
			removed = true;
		}

		if ($('[name="shipping_country_id"]').val() != 94) {			
			if ( ! removed) {
				var country_id = $('[name="shipping_country_id"]').val();
				docCookies.setItem('cookie_country_id', country_id);
			}		

			// fixing to usd
			var $a_usd = $('[data-currency-id]').filter('[data-code="USD"]');
			if ( ! $a_usd.hasClass('active')) {
				$a_usd.click();
			}
		}
	};

	// Let's go
	init();
});