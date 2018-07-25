

$(function() {

	var $form = $('#form-design-submission');

	if ($form.length == 0) return;


	function init() {

	    $('.slider-for').on('beforeChange', function(event, slick, currentSlide, nextSlide) {
	        
	        var curr_style_id = $('.slider-nav').find('.slick-slide').eq(nextSlide).find('.widget-product').data('style-id');
	        
	        // Change active prices
	        es.change_active_prices_to(curr_style_id);
	    });

	    // change margin event
	    $('[name$="_margin"]').on('change', function() {
	    	es.update_prices();
	    });

	    // first time update prices
	    es.update_prices();
	}

    // Attaching update prices to es namespace
    window.es.update_prices = function() {
		var sides = num_sides();

		$('.profit-margin').each(function() {
			var $profit_margin = $(this);

			var prices = JSON.parse($profit_margin.data('prices'));

			var $base_price = $profit_margin.find('[name$="_base_price"]');
			var $margin = $profit_margin.find('[name$="_margin"]');
			var $selling_price = $profit_margin.find('[name$="_selling_price"]');

			$base_price.val(function() {
				if (sides == 0 || sides == 1) 
				{
					if ($('[data-currency-id].active').data('currency-id') == 1)
					{
						return prices.india_one_sided;	
					}
					else
					{
						return prices.inter_one_sided;
					}
					
				} 
				else
				{
					if ($('[data-currency-id].active').data('currency-id') == 1)
					{
						return prices.india_both_sided;	
					}
					else
					{
						return prices.inter_both_sided;
					}
				} 
			});
			
			$selling_price.val(function() {
				var bp = parseFloat($base_price.val());
				var margin = parseFloat($margin.val()) / 100;
				return es.round_amount( bp + bp * margin );
			});
		});
	};

	/**
	 * returns the num of sides where drawing has been done
	 */
	function num_sides() {
		var sides = 0;
		if (es.front_fabric._objects.length > 0) sides += 1;
		if (es.back_fabric._objects.length > 0) sides += 1;
		return sides;
	}

	/**
	 * change active prices to style_id
	 */
	window.es.change_active_prices_to = function(style_id) {
        $('.profit-margin').removeClass('active');
        $('.profit-margin').filter('[data-style-id="'+style_id+'"]').addClass('active');
	};

	// let's go
	init();


});