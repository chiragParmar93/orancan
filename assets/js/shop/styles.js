
$(function() {

    var $page = $('.page-shop-product');

    if ($page.length == 0) return;

    /**
     * init all the necessary stuff
     */
    function init() {

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
    
        $('.slider-for').on('beforeChange', function(event, slick, currentSlide, nextSlide) {

            // Check for artwork
            var $artwork = $('.slider-nav .slick-slide').eq(nextSlide).find('.container-artwork-xs');
            if ($artwork.length != 0) {
                return;
            }

            // Changing the currect style and their relative values
            var curr_style_id = $('.slider-nav .widget-product').eq(nextSlide).data('style-id');

            change_active_style_to(curr_style_id);
            es.change_active_sizes_to(curr_style_id);
            es.change_active_prices_to(curr_style_id);
            es.change_active_color_to(curr_style_id);        
        });

        // Making product active
        if (typeof es.filters != undefined) {
            var find = '';
            if (es.filters.section != 'all') {
                find += '[data-section-name="'+ es.filters.section +'"]';
            }
            if (es.filters.style != 'all') {
                find += '[data-style-name^="'+ es.filters.style +'"]';
            }

            var $active = $('.slider-nav').find(find);

            console.log($active);
            $active.click();
        }

    }

    function change_active_style_to(style_id) {
        $('.slider-nav .widget-product').removeClass('active');

        var $product = $('.slider-nav .widget-product[data-style-id="'+style_id+'"]');

        $product.addClass('active');        

        // changing name
        $('#style-name').html($product.data('style-name'));
        $('#section-name').html($product.data('section-name'));
    }

    // Let's go
    init();
});
