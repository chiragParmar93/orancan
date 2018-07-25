$(function() {
	
	var $form = $('#form-design-submission');

	if ($form.length == 0) return;

	function init() {
		$form.on('submit', submit_form);
		window.onbeforeunload = function() {
		  return "Data will be lost if you leave the page, are you sure?";
		};
	}

	function submit_form(event) {
		event.preventDefault();

		$('#modal-submission').modal('show');

		form_data = get_form_data();

		if ( ! validate_form_data(form_data)) {
			$('#modal-submission').modal('hide');
			return false;
		}

		$form.find('[type="submit"]').button('loading');

		$.ajax({
			type: "POST",
			url: $form.attr('action'),
			data: form_data,
			success: function(data) {
				console.log(data);
				response = JSON.parse(data);
				bootbox.alert(response.alerts);
				if (response.status == 'success') {
					// Removing the beforeunload
					window.onbeforeunload = null;

					// Redirect the user
					setTimeout(function(){
						window.location.replace(response.redirect_url);
					}, 1000);
				}
				$form.find('[type="submit"]').button('reset');
			}
		});

	}

	function validate_form_data(form_data) {
		var errors = []
		if (form_data.fabric_front == '' && form_data.fabric_back == '') {
			errors.push('Atleast one design is required!');
		}
		if (form_data.product_title == '') {
			errors.push('Product Title is required');
		}
		if (form_data.product_description == '') {
			errors.push('Product description is required');
		}
		if (form_data.product_categories.length == 0) {
			errors.push('Atleast one category is required');
		}
		if (form_data.interests == '') {
			errors.push('Atleast one interest is required');
		}

		if (errors.length == 0) {
			return true;
		} else {
			var alert = '<br><div class="alert alert-danger">';
			errors.forEach(function(error){
				alert += '<p>' + error + '</p>';
			});
			alert += '</div>';

			bootbox.alert(alert);
			return false;
		}
	}

	function get_form_data() {
		return {
			// security
			'csrf_test_name': $form.find('[name="csrf_test_name"]').val(),

			// product id while updating
			'product_id': (function() {
				if (es.product_id !== undefined) return es.product_id;
				else return '';
			})(),

			// designs
			'fabric_front': (function(){
				if (es.front_fabric._objects.length > 0) {
					return JSON.stringify(es.front_fabric);
				} else {
					return '';
				}
			})(),
			'fabric_back': (function(){
				if (es.back_fabric._objects.length > 0) {
					return JSON.stringify(es.back_fabric);
				} else {
					return '';
				}
			})(),

			// product styles
			'product_styles': (function() {
				var styles = [];
				$('.slider-nav .widget-product').each(function() {
					$widget = $(this);

					var style = {};
					
					style.id = $widget.data('style-id');
					
					style.colors = (function(){
						var colors = [];
						$('.style-colors[data-style-id="'+ style.id +'"] li.active').each(function(){
							colors.push($(this).data('color-id'));
						});
						return colors;
					})();

					style.default_color = (function(){
						return $('.style-default-color[data-style-id="'+ style.id +'"] li.active').data('color-id');
					})();

					style.margin = (function(){
						return $('[name="style_'+ style.id +'_margin"]').attr('value');
					})();

					styles.push(style);
				});
				return styles;
			})(),

			// general
			'product_title': (function(){
				return $('[name="product_title"]').val();
			})(),

			'product_description': (function(){
				return $('[name="product_desc"]').val();
			})(),

			'interests': (function(){
				return $('[name="product_interests"]').val();
			})(),

			'product_categories': (function(){
				var cats = [];
				$('[name="product_categories[]"]').filter(':checked').each(function(){
					cats.push($(this).attr('value'));
				});
				return cats;
			})(),

			'design_status': (function(){
				return $('[name="product_design_status"]').filter(':checked').attr('value');
			})()
		};		
	}

	// Let's go
	init();
});