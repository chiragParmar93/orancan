<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipping_charge_m extends MY_Model {

	protected $_table = 'api_99prints_shipping_charges';

	/**
	 * calculate charge for shipping product_style to country 
	 * calcuation will be using INR
	 */
	public function charge_for($style_id, $country_id)
	{
		$zone = $this->country_m->get($country_id)->zone;

		if ( ! $zone)
		{
			// Zone may be NULL or 0(INDIA)
			return 0;
		}

		$weight = $this->style_m->get($style_id)->weight;

		$charge = $this->get_by(array(
									'weight' => $weight,
									'zone' => $zone
								))->charge;

		$discount = $this->shipping_discount_m
							->get_by(array('style_id'=>$style_id))
							->discount;

		$result = $charge - $discount;

		$result = ($result > 0) ? $result : 0;

		return format_amount($result);
	}

	/**
	 * will calculate the charge for shipping product_style to country in
	 * given currency
	 */
	public function charge($style_id, $country_id, $currency_code)
	{
		$charge = $this->charge_for($style_id, $country_id);

		return $this->currency_m->convert($charge, 'INR', $currency_code);
	}

}