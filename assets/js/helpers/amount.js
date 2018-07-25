
$(function() {

	var $a = $('[data-currency-id]');

	if ($a.length == 0) return;

	/**
	 * format the money by appending currey code to amount
	 */
	es.format_money = function (amount) {
		return $('[data-currency-id].active').data('code') + ' ' + es.format_amount(amount);
	}

	es.format_amount = function(amount) {
		amount = parseFloat(amount);
		return amount.toFixed(2);
	}

	es.round_amount = function(amount) {
		return es.format_amount( Math.round( parseFloat(amount) ) );
	}

});