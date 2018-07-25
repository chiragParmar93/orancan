
$(function() {

	var $form = $('#form-bank-neft');

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

			'neft_name': $form.find('[name="neft_name"]').val(),

			'neft_account': $form.find('[name="neft_account"]').val(),

			'neft_bank': $form.find('[name="neft_bank"]').val(),

			'neft_ifsc': $form.find('[name="neft_ifsc"]').val()

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