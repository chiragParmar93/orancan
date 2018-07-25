

$(function() {

	var $form = $('#form-design-submission');

	if ($form.length == 0) return;


	function init() {

	    $('.slider-for').on('beforeChange', function(event, slick, currentSlide, nextSlide) {
	        
	        var curr_style_id = $('.slider-nav').find('.slick-slide').eq(nextSlide).find('.widget-product').data('style-id');
	        
	        // Change active color
	        es.change_active_colors_to(curr_style_id);
	    });

	    $('.style-colors').on('click', 'li', function(event) {
	    	event.preventDefault();

	    	var $li 			= $(this),
	    		$ul 			= $li.parent('.style-colors'),
	    		style_id 		= $ul.data('style-id'),
	    		$default_color 	= $('.style-default-color[data-style-id="'+style_id+'"] li.active');

	    	if ($li.hasClass('active')) 
	    	{
	    		// first check if it's NOT default
	    		if ($default_color.data('color-id') == $li.data('color-id'))
	    		{
	    			alert('Please change default color first!');
	    			return false;
	    		}

	    		$li.removeClass('active');

	    		// clicking default for changing the background
	    		$default_color.click();
	    	} 
	    	else 
	    	{
	    		$li.addClass('active');
	    		change_background(style_id, $li.data('color'));
	    	}
	    });

	    $('.style-default-color').on('click', 'li', function(event){
	    	event.preventDefault();

	    	var $li 			= $(this);
	    		$ul				= $li.parent('.style-default-color'),
	    		style_id 		= $ul.data('style-id'),
	    		$style_colors 	= $('.style-colors[data-style-id='+style_id+']');

	    	// Make color active
	    	$ul.find('li').removeClass('active');
    		$li.addClass('active');

    		// change background
    		change_background(style_id, $li.data('color'));

    		// make selection to .style-colors too
    		$style_colors.find('li[data-color-id="'+$li.data('color-id')+'"]').addClass('active');
	    });

	    if (es.product_style_colors === undefined)
	    {
	    	$('.style-default-color li').first().click();
	    }
	}

	function change_background(style_id, color) {
		$('.slider-for').find('[data-style-id="'+style_id+'"] .mockup').css('backgroundColor', color);
	}

	window.es.change_active_colors_to = function(style_id) {
        $('.style-colors, .style-default-color').removeClass('active');
        $('.style-colors, .style-default-color').filter('[data-style-id="'+style_id+'"]').addClass('active');
	}

	// Populate style colors
	window.es.populate_style_colors = function() {
		es.product_style_colors.forEach(function(obj) {
			var style_id = obj.style_id,
				color_id = obj.color_id;

			$('.style-colors[data-style-id="'+style_id+'"]').find('li[data-color-id="'+color_id+'"]').addClass('active');
		});

		es.product_default_colors.forEach(function(obj) {
			var style_id = obj.style_id,
				color_id = obj.color_id;
			
			$('.style-default-color[data-style-id="'+style_id+'"]').find('li[data-color-id="'+color_id+'"]').click();
		});
	}

	// let's go
	init();
});