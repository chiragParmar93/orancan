
$(function() {

	var $form = $('#form-user-num-products');

	if ($form.length == 0) return;

	function init() {
		$form.on('submit', form_submit);
		$form.submit();
	}

	function form_submit(event) {
		event.preventDefault();

		if (es.loggedin == '')
		{
			return false;
		}

		var form_data = {
			// security
			'csrf_test_name': $form.find('[name="csrf_test_name"]').val()
		};

		$.ajax({
			type: "POST",
			url: $form.attr('action'),
			data: form_data,
			success: function(data) {
				var response = JSON.parse(data);
				if (response.status == 'success') {
					var num = response.num > 0 ? response.num : '';
					$('[data-cart-num-products]').html(num);
				}
			}
		});
	}

	es.update_num_product = function() {
		$form.submit();
	}

	// Let's go
	init();
});