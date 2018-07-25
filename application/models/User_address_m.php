<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_address_m extends MY_Model {

	/**
	 * Add the address at checkout page.
	 * Returns the ids of both address
	 */
	public function add_for_checkout($user_id)
	{
		// hold various types of address
		$data_shipping = array();
		$data_billing = array();

		// Fill the above array
		// TODO: replace it with the session id
		$data_shipping['user_id'] 		= $user_id;
		$data_shipping['name_title_id'] = $_POST['shipping_name_title_id'];
		$data_shipping['first_name'] 	= $_POST['shipping_first_name'];
		$data_shipping['last_name'] 	= $_POST['shipping_last_name'];
		$data_shipping['address_line1'] = $_POST['shipping_address1'];
		$data_shipping['address_line2'] = $_POST['shipping_address2'];
		$data_shipping['country_id'] 	= $_POST['shipping_country_id'];
		$data_shipping['city']	 		= $_POST['shipping_city'];
		$data_shipping['state'] 		= $_POST['shipping_state'];
		$data_shipping['pincode'] 		= $_POST['shipping_pincode'];
		$data_shipping['mobile'] 		= $_POST['shipping_mobile'];

		$data_billing['user_id'] 		= $user_id;
		$data_billing['name_title_id'] = $_POST['billing_name_title_id'];
		$data_billing['first_name'] 	= $_POST['billing_first_name'];
		$data_billing['last_name'] 	= $_POST['billing_last_name'];
		$data_billing['address_line1'] = $_POST['billing_address1'];
		$data_billing['address_line2'] = $_POST['billing_address2'];
		$data_billing['country_id'] 	= $_POST['billing_country_id'];
		$data_billing['city']	 		= $_POST['billing_city'];
		$data_billing['state'] 		= $_POST['billing_state'];
		$data_billing['pincode'] 		= $_POST['billing_pincode'];
		$data_billing['mobile'] 		= $_POST['billing_mobile'];		

		if ($data_shipping == $data_billing)
		{
			$shipping_id = $this->sync($data_shipping);
			$billing_id = $shipping_id;
		}
		else
		{
			$shipping_id = $this->sync($data_shipping);
			$billing_id = $this->sync($data_billing);
		}

		return array(
			'shipping_id' => $shipping_id,
			'billing_id' => $billing_id,
		);
	}

	/**
	 * get the address for user
	 * if not found return address object
	 * with all fields set ''
	 * used for initially filling form
	 * @return [type] [description]
	 */
	public function get_address($user_id) 
	{
		$address = $this->get_by('user_id', $user_id);
		
		if ( ! $address)
		{
			$address = $this->get(1);
			foreach ($address as $key => &$value)
			{
				$value = '';
			}
		}

		return $address;
	}

}
