
$(function() {

	var $btn = $('.btn-start-designing');

	if ($btn.length == 0) return;

	$btn.on('click', function(event) {
		
		if ( es.is_user != 1 ) {
			event.preventDefault();
			$('[data-target="#modal-form-login"]').click();
		}
	});

});