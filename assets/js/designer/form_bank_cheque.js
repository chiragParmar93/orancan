
$(function() {

	var $form = $('#form-bank-cheque');

	if ($form.length == 0) return;

	function init() {
		$form.submit(function(event){
			event.preventDefault();
			submit_form();
		});		
	}

	function submit_form() {

		var form_data = {

			'csrf_test_name': $form.find('[name="csrf_test_name"]').val(),

			'cheque_name': $form.find('[name="cheque_name"]').val(),

			'cheque_address': $form.find('[name="cheque_address"]').val(),

		};

		$.ajax({
			type: "POST",
			url: $form.attr('action'),
			data: form_data,
			success: function(data) {
				response = JSON.parse(data);
				if (response.status == 'success') {
					bootbox.alert(response.alerts);
				}
			}
		});

		// Prevent form submission
		return false;
	}

	// Let's go
	init();
});