

$(function() {

    var $page = $('.page-shop-product');

    if ($page.length == 0) return;


	function init() {

	    $('.style-colors').on('click', 'li', function(event) {
	    	event.preventDefault();

	    	var $li 			= $(this),
	    		$ul 			= $li.parent('.style-colors'),
	    		style_id 		= $ul.data('style-id');

	    		$ul.find('li').removeClass('active');
	    		$li.addClass('active');
	    		change_background(style_id, $li.data('color'));
	    });

	    es.product_default_colors.forEach(function(obj){
	    	$('.style-colors[data-style-id="'+obj.style_id+'"]')
	    		.find('li[data-color-id="'+obj.color_id+'"]').click();
	    });

	    var artwork_bg_color = $('[data-color-id="'+es.product_default_colors[0].color_id+'"]').data('color');
	    $('.container-artwork-lg, .container-artwork-xs').css('backgroundColor', artwork_bg_color);
	}

	function change_background(style_id, color) {
		$('.slider-for').find('[data-style-id="'+style_id+'"] .mockup').css('backgroundColor', color);
	}

	window.es.change_active_color_to = function(style_id) {
        $('.style-colors').removeClass('active');
        $('.style-colors').filter('[data-style-id="'+style_id+'"]').addClass('active');
	}

	// let's go
	init();
});