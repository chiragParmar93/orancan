
$(function() {

	var $form = $('#form-change-currency');

	if ($form.length == 0) return;

	function init() {
		
		$('[data-currency-id]').on('click', function(event) {
			
			event.preventDefault();

			var $a = $(this);

			form_data = {
				// security
				'csrf_test_name': $form.find('[name="csrf_test_name"]').val(),

				'currency_code': $a.data('code')
			};

			$.ajax({
				type: "POST",
				url: $form.attr('action'),
				data: form_data,
				success: function(data) {
					console.log(data);
					response = JSON.parse(data);
					if (response.status == 'success') {
						window.location.replace(window.location.href);
					}
				}
			});			
		});

	}

	// change the currency
	es.change_currency = function(currency_code) {
		var $a = $('[data-currency-id]').filter('[data-code="'+ currency_code +'"]');

		// make the change
		$a.click();
	};

	// Let's go
	init();
});