

$(function() {

    var $page = $('.page-shop-product');

    if ($page.length == 0) return;

	function init() {

	    $('.style-sizes').on('click', 'li', function(event) {
	    	event.preventDefault();

	    	var $li 			= $(this),
	    		$ul 			= $li.parent('.style-sizes'),
	    		style_id 		= $ul.data('style-id');

    		$ul.find('li').removeClass('active');
    		$li.addClass('active');
	    });

	}

	window.es.change_active_sizes_to = function(style_id) {
        $('.style-sizes').removeClass('active');
        $('.style-sizes[data-style-id="'+style_id+'"]').addClass('active');

        // change size chart
        $('[data-target^="#modal-size-chart"]').attr('data-target', '#modal-size-chart-'+style_id);
    };

    // Let's go
    init();
    
});