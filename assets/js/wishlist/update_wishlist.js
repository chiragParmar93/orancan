
$(function() {

	var $form = $('#form-update-wishlist');

	if ($form.length == 0) return;

	$('.btn-remove-product').on('click', function() {
		var $btn = $(this);
		
		form_data = {
			'csrf_test_name': $('[name="csrf_test_name"]').val(),

			'product_id': $btn.data('product-id')
		};

		$.ajax({
			type: "POST",
			url: $form.attr('action'),
			data: form_data,
			success: function(data) {
				var response = JSON.parse(data);
				if (response.status == 'success')
				{
					$btn.closest('.widget-product').remove();
					bootbox.alert(response.alerts);
				}
			}
		});
	});
});