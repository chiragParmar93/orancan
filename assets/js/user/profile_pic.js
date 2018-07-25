
$(function(){


	var $form = $('#form-profile-pic');

	if ($form.length == 0) return;

	$('#modal-profile-pic .btn-submit').on('click', function(){
		$form.submit();
		
	});




});