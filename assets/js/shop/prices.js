

$(function() {

    var $page = $('.page-shop-product');

    if ($page.length == 0) return;

	window.es.change_active_prices_to = function(style_id) {
        $('.product-price').removeClass('active');
        $('.product-price').filter('[data-style-id="'+style_id+'"]').addClass('active');
    };

});