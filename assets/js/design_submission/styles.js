
$(function(){

	var $form = $('#form-design-submission');

	if ($form.length == 0) return;


	// SCOPE VARIABLES
	var slick_for = '.slider-for',
		slick_nav = '.slider-nav';

	/**
	 * init variables and add events
	 */
	function init() {

		// init slick
		// ------------
		$('.slider-for').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            asNavFor: '.slider-nav',
            infinite: false
        });

        $('.slider-nav').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            asNavFor: '.slider-for',
            dots: false,
            centerMode: false,
            focusOnSelect: true,
            variableWidth: true,
            infinite: false
        });

		// setting events
		// ---------------
		$('#modal-styles .widget-product').on('click', select_style);

		// reseting view to front
	    $('.slider-for').on('beforeChange', function(event, slick, currentSlide, nextSlide) {
	        window.es.reset_drawing_view();
	    });

	    // while updating product
	    if (es.product_styles !== undefined && es.product_styles.length > 0)
	    {
	    	es.product_styles.forEach(function(style_id) {
	    		$widget = $('#modal-styles .widget-product[data-style-id="'+style_id+'"]');

	    		if ( ! $widget.hasClass('selected'))
    			{
    				$widget.click();
    				es.populate_style_colors();
    			}
	    		
	    	});

	    	var $first_widget = $('#modal-styles .widget-product').first()
	    		first_style_id = $first_widget.data('style-id');

	    	if ( $.inArray(first_style_id, es.product_styles) == -1 )
	    	{
	    		setTimeout(function(){
	    			$first_widget.click();
	    		}, 1000);
	    	}

	    	// Add fixed class so that they can't be removed
	    	setTimeout(function(){
	    		es.product_styles.forEach(function(style_id){
	    			$('.slider-nav .widget-product[data-style-id="'+style_id+'"]').addClass('fixed');
	    			$('#modal-styles .widget-product[data-style-id="'+style_id+'"]').addClass('fixed');
	    		});
	    	}, 1000);

	    	es.populate_style_colors();
	    }
	}

	/**
	 * toggle selected class to widget-product
	 */
	function select_style(event) {

		$widget = $(this);

		// toggle selected class
		if ($widget.hasClass('selected')) 
		{
			if ($('#modal-styles .widget-product.selected').length == 1) {
				alert('Atlease one style should be selected');
				return false;
			}

			if ($widget.hasClass('fixed')) {
				alert("This style can't be removed");
				return false;
			}

			$widget.removeClass('selected');
			slick_remove($widget.data('style-id'));	
		} 
		else 
		{
			$widget.addClass('selected');
			slick_add($widget.data('style-id'));

			// activating default color
			$('.style-default-color[data-style-id="'+$widget.data('style-id')+'"] li').first().click();			
		}

		var curr_style_id = $('.slider-nav .slick-current .widget-product').data('style-id');

		// update active prices
		es.change_active_prices_to(curr_style_id);

		// update active colors
		es.change_active_colors_to(curr_style_id);

		// resetting view
		window.es.reset_drawing_view();
	}

	// add slick
	function slick_add(style_id) {
		var widget_nav = $('.slick-nav-content .widget-product[data-style-id="'+style_id+'"]')[0].outerHTML;
		$(slick_nav).slick('slickAdd', "<div>"+ widget_nav +"</div>");

		var widget_for = $('.slick-for-content .widget-product[data-style-id="'+style_id+'"]').closest('.product-lg-container')[0].outerHTML;
		$(slick_for).slick('slickAdd', "<div>"+ widget_for +"</div>");
	}

	// remove slick by style id
	function slick_remove(style_id) {
		// get the idx of style_id
		var idx = $(slick_nav).find('[data-style-id="'+style_id+'"]').parent('.slick-slide').index();
		
		$(slick_nav).slick('slickRemove', idx);
		$(slick_for).slick('slickRemove', idx);
	}

	// update the design area
	// TODO: make it DB driven
	function update_design_area()
	{
		var drawing_height = '180px'
		$('.slider-nav .widget-product').each(function() {
			$widget = $(this);
			var style_id = $widget.data('style-id');
			if (style_id == 4 || style_id == 11) {
				drawing_height = '132px';
			}
		})
		$('.drawing-area.front').css('height', drawing_height);
	}

	// let's go
	init();
});