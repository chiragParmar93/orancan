

$(function() {

	var $form = $('#form-design-submission');

	if ($form.length == 0) return;


	// SCOPE VARIABLES
	// ---------------
	var SCALE_DOWN = 0.04;
	fabric.Object.NUM_FRACTION_DIGITS = 17;

	// Front
	var $front_upload = $('#design-front-upload'),
		$front_delete = $('#design-front-delete'),
	    $front_file = $('#design-front-file');

	// Back
	var $back_upload = $('#design-back-upload'),
		$back_delete = $('#design-back-delete'),
    	$back_file = $('#design-back-file');



	function init() {

		// drawing
		// --------
		$('.slider-for').on('click', 'a[href^="#front-"]', function(){
			window.es.drawing_front();
		});
		$('.slider-for').on('click', 'a[href^="#back-"]', function(){
			$('.drawing-container').removeClass('active');
			$('.drawing-container.back').addClass('active');
		});

		// 
		// fabric
		// -------		
		
		// front fabric
		es.front_fabric = new fabric.Canvas('design-front-canvas');
		es.front_fabric.observe("object:scaling", max_scale);

		// while updating product
		if (es.front_fabric_json !== undefined && es.front_fabric_json != '')
		{
			es.front_fabric.loadFromJSON(es.front_fabric_json, function() {
				es.front_fabric.renderAll();
			});
		}

		$front_upload.on('click', function() {
			$front_file.click();
		});
		$front_file.on('change', file_change);

		$front_delete.on('click', function(){
			es.front_fabric.clear().renderAll();
			// update prices
		    window.es.update_prices();
		});

		// back faric
		es.back_fabric = new fabric.Canvas('design-back-canvas');
		es.back_fabric.observe("object:scaling", max_scale);

		// while updating product
		if (es.back_fabric_json !== undefined && es.back_fabric_json != '')
		{
			es.back_fabric.loadFromJSON(es.back_fabric_json, function() {
				es.back_fabric.renderAll();
			});
		}

		$back_upload.on('click', function() {
		    $back_file.click();
		});
		$back_file.on('change', file_change);

		$back_delete.on('click', function(){
			es.back_fabric.clear().renderAll();
			// update prices
		    window.es.update_prices();
		});
	}

	/**
	 * file change
	 */
	function file_change() {
	    var $input = $(this),
	        numFiles = $input.get(0).files ? $input.get(0).files.length : 1,
	        label = $input.val().replace(/\\/g, '/').replace(/.*\//, '');

	    var file = $input.get(0).files[0];

	    if (file.size > 5000000) {
	        bootbox.alert('File Too Big! Please compress it');
	        return false;
	    }

	    if (es.file_ext(label) != 'png') {
	    	bootbox.alert('Only PNG files are supported!');
	    	return false;
	    }

	    var reader = new FileReader();
	    reader.onloadend = function () {
	        init_fabric(reader.result, get_which($input));
	    }
	    reader.readAsDataURL(file);
	}

	/**
	 * get which front or back
	 */
	function get_which($obj) {
	    if ($obj.attr('id') == 'design-front-file') {
	        return 'front';
	    } else {
	        return 'back';
	    }
	}

	/**
	 * init our fabric
	 */
	function init_fabric(img_base64, which) {

		fabric.Image.fromURL(img_base64, function(oImg) {
			oImg.scale(SCALE_DOWN);
	        if (which == 'front') {
	        	// first clear everything
	        	es.front_fabric.clear().renderAll();

	        	// then add new
	            es.front_fabric.add(oImg);
	            es.front_fabric.item(0).lockRotation = true;
	        } else {
	        	// first clear everything
	        	es.back_fabric.clear().renderAll();

	        	// then add new
	            es.back_fabric.add(oImg);
	            es.back_fabric.item(0).lockRotation = true;
	        }
	
		    // update prices
		    window.es.update_prices();
		});

	}

	/**
	 * init max scale
	 * @link: http://goo.gl/UKv6yf
	 */
	function max_scale(e) {
	    var shape = e.target;

	    if (shape.scaleX >= SCALE_DOWN || shape.scaleY >= SCALE_DOWN) {
	        shape.set({scaleX: SCALE_DOWN});
	        shape.set({scaleY: SCALE_DOWN});
	    }
	}

	window.es.reset_drawing_view = function() {
		$('.slider-for a[href^="#front-"]').click();
		es.drawing_front();
	};

	window.es.drawing_front = function() {
		$('.drawing-container').removeClass('active');
		$('.drawing-container.front').addClass('active');
	};

	// let's go
	init();
});