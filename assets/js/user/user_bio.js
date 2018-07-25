
$(function(){

	var $form = $('#form-user-bio');

	if ($form.length == 0) return;

	function init() {
		$form.on('submit', form_submit);
	}

	function form_submit(event) {
		event.preventDefault();

		var form_data = {};

		$form.find('[name]').each(function(){
			var $input = $(this);

			var field = $input.attr('name');
			var value = $input.val();

			form_data[field] = value;
		});

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
		
	}


	// Let's go
	init();
});