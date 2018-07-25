<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Currency_m extends MY_Model {

	/**
	 * Scan the IP address and set the currency accordingly
	 * TODO: scan the IP address
	 */
	public function init_currency()	
	{
		$_SESSION['es_currency'] = $this->as_array()->get_by('code', 'INR');
	}

	/**
	 * change the session for current currenty
	 */
	public function change($currency_code)
	{
		$_SESSION['es_currency'] = $this->as_array()->get_by('code', $currency_code);
	}

	/**
	 * calculate the price conversion
	 * 
	 * @param  float 	$amount The amount to convert
	 * @param  integer 	$from   the currency code
	 * @param  integer 	$to     the currency code
	 * @return float          	converted currency
	 */
	public function convert($amount, $from = 'INR', $to = 'USD')
	{
		// TODO: make it dynamic
		if ($from == 'INR' && $to == 'USD')
		{
			$amount = format_amount($amount / 63);
		}
		return $amount;
	}

}
