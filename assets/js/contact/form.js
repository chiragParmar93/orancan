
$(function() {

	var $form = $('#form-contact');

	if ($form.length == 0) return;

	function init() {
		$.validate({
			modules : 'security',
			form : '#' + $form.attr('id'),
			onSuccess : submit_form
		});

	}

	function submit_form() {

		var form_data = {};

		$form.find('[name]').each(function(){
			var $input = $(this);

			var field = $input.attr('name');
			var value = $input.val();

			form_data[field] = value;
		});

		$.ajax({
			type : "POST",
			url : $form.attr('action'),
			data : form_data,
			beforeSend : function() {
				$form.find('[type="submit"]').button('loading');
			},
			success : function(data) {
				response = JSON.parse(data);
				$form.prepend(response.alerts);
				$form.find('[type="submit"]').button('reset');
				if (response.status == 'success') {
					//es.reload();
				}
			}
		});

		// Prevent form submission
		return false;
	}

	// Let's go
	init();
});