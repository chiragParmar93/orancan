<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Designer_payment_m extends MY_Model {
	
	/**
	 * format the data for the admin
	 */
	public function designer_payments($user_id)
	{
		$this->db->join('order_products', 'order_products.id = designer_payments.order_product_id');
		$this->db->join('orders', 'orders.id = order_products.order_id');
		$this->db->join('currencies', 'currencies.id = orders.currency_id');
		$this->db->join('product_styles', 'product_styles.id = order_products.product_style_id');
		$this->db->join('api_99prints_styles', 'api_99prints_styles.id = product_styles.style_id');
		$this->db->join('products', 'products.id = product_styles.product_id');
		$this->db->join('users', 'users.id = products.user_id');
	
		$this->db->select('products.*');
		$this->db->select('api_99prints_styles.style_name');
		$this->db->select('currencies.code as currency_code');
		$this->db->select('order_products.*'); 
		$this->db->select('designer_payments.*');// NOTE: trick for ID

		$this->db->where('users.id', $user_id);

		$designer_payments = $this->get_all();

		foreach($designer_payments as &$dp)
		{
			$dp->amount = format_amount($dp->product_base_price * ($dp->product_margin/100) * $dp->quantity);
		}

		return $designer_payments;
	}

	/**
	 * get list of all designers which have sales with their payments
	 */
	public function designers()
	{
		$this->db->join('order_products', 'order_products.id = designer_payments.order_product_id');
		$this->db->join('orders', 'orders.id = order_products.order_id');
		$this->db->join('currencies', 'currencies.id = orders.currency_id');
		$this->db->join('product_styles', 'product_styles.id = order_products.product_style_id');
		$this->db->join('products', 'products.id = product_styles.product_id');
		$this->db->join('users', 'users.id = products.user_id');

		$this->db->select('users.id as user_id, users.first_name');
		$this->db->select('currencies.code as currency_code');
		$this->db->select('order_products.product_cost, order_products.product_base_price, order_products.product_margin');
		$this->db->select('designer_payments.status');

		$dps = $this->get_all();
		
		$designers = array();
		foreach ($dps as $dp)
		{
			if ( isset($designers[$dp->user_id]) )
			{
				if ($dp->status == 0)
				{
					$designers[$dp->user_id]->unpaid_amount += $this->calculate_amount($dp->product_base_price, $dp->product_margin, $dp->currency_code);
				}
				else if ($dp->status == 1)
				{
					$designers[$dp->user_id]->paid_amount += $this->calculate_amount($dp->product_base_price, $dp->product_margin, $dp->currency_code);
				}
			}
			else
			{
				// Build new designer object
				$designers[$dp->user_id] = (object) [
					'id' => $dp->user_id,
					'first_name' => $dp->first_name,
					'paid_amount' => ($dp->status == 1) ? $this->calculate_amount($dp->product_base_price, $dp->product_margin, $dp->currency_code) : 0,
					'unpaid_amount' => ($dp->status == 0) ? $this->calculate_amount($dp->product_base_price, $dp->product_margin, $dp->currency_code) : 0
				];
			}
		}

		return $designers;
	}

	public function calculate_amount($base_price, $margin, $currency_code)
	{
		if ($currency_code == 'INR') {
			return $base_price * $margin / 100;
		} else {
			return $base_price * $margin / 100 * 63;
		}
	}

}
