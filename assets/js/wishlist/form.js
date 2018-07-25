
$(function() {

	var $form = $('#form-add-to-wishlist');

	if ($form.length == 0) return;

	$form.on('submit', function(event) {

		event.preventDefault();

		if (es.loggedin == '')
		{
			$('[data-target="#modal-form-login"]').click();
			return false;
		}

		var form_data = {

			'csrf_test_name': $('[name="csrf_test_name"]').val(),

			'product_id': $form.find('[name="product_id"]').val()

		};

		$.ajax({
			type: "POST",
			url: $form.attr('action'),
			data: form_data,
			beforeSend: function() {
				$form.find('[type="submit"]').attr('disabled', 'disabled');
			},
			success: function(data) {
				var response = JSON.parse(data);
				if (response.status == 'success') {
					bootbox.alert(response.alerts);
				}
				$form.find('[type="submit"]').removeAttr('disabled');
			}
		});

	});

});