
$(function() {

	var $div = $('#cart-summary');

	if ($div.length == 0) return;

	function init() {

		$('[data-cart-product]').each(function() {
			update_product_total($(this));
		});

		es.update_shipping_charges();
		es.update_subtotal();
	}

	update_product_total = function ($tr) {
		var $total 		= $tr.find('[data-cart-product-total]'),
			qty 		= parseInt($tr.find('[data-quantity]').data('quantity')),
			unit_price 	= parseFloat($tr.find('[data-unit-price]').data('unit-price')),
			total 		= qty * unit_price;

		$total.val( es.format_money(total) );
		$total.attr( 'data-cart-product-total', total );
		return total;
	}

	es.update_subtotal = function () {
		var subtotal = 0;
		$('[data-cart-product-total]').each(function() {
			subtotal += parseFloat($(this).data('cart-product-total'));
		});

		subtotal += parseFloat($('[data-shipping-cost]').attr('data-shipping-cost'));

		$('[data-cart-total]').val(es.format_money(subtotal));
	}

	// Let's go
	var interval = setInterval(function(){
		if (es.update_shipping_charges !== undefined)
		{
			clearInterval(interval);
			init();
		}
	}, 500);
});