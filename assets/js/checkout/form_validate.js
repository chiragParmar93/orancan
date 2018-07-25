$(function() {

	var $form = $('#form-checkout');

	if ($form.length == 0) return;

	function init() {
		$('#collapse-one-btn').on('click', function(event) {
			event.preventDefault();
			if (validate_step1()) {
				$('#collapseOne').collapse('hide');
				$('#collapseTwo').collapse('show');
			}
		});		

		$('#collapse-two-btn').on('click', function(event) {
			event.preventDefault();
			if (validate_step2()) {
				$('#collapseTwo').collapse('hide');
				$('#collapseThree').collapse('show');
			}
		});		

		$form.on('submit', function(event) {
			if ( ! validate_step1() && ! validate_step2() ) {
				bootbox.alert('Please fill all the required fields first!');
				event.preventDefault();
			}
			return true;
		});
	}


	function validate_step1() {
		var valid = true;
		$('[name^="billing_"], [name="user_email"]').not('[name="billing_address2"]').each(function() {
			var $input = $(this);
			if ( ! $input.val() )
			{
				display_error($input);
				valid = false;
			}
			else
			{
				remove_error($input);
			}
		});
		return valid;
	}

	function validate_step2() {
		var valid = true;
		$('[name^="shipping_"]').not('[name="shipping_address2"]').each(function() {
			var $input = $(this);
			if ( ! $input.val() )
			{
				display_error($input);
				valid = false;
			}
			else
			{
				remove_error($input);
			}
		});
		return valid;
	}



	/**
	 * return template for form error
	 */
	function fe_span(msg) {
		return '<span class="help-block form-error">' + msg + '</span>';
	}

	/**
	 * will display error for the form element
	 */
	function display_error($input) {
		$input.parent('.form-group').addClass('has-error');
		$input.after(fe_span('You have not answered all required fields'));
	}

	/**
	 * remove error
	 */
	function remove_error($input) {
		$input.parent('.form-group').removeClass('has-error');
		$input.parent('.form-group').find('.form-error').remove();
	}

	// Let's go
	init();
})