<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_product_m extends MY_Model {

	public $belongs_to = array(
		'color' => array(	'primary_key' => 'color_id',
							'model' => 'style_color_m'	),
		'size' => array(	'primary_key' => 'size_id',
							'model' => 'style_size_m'	),
	);

	/**
	 * get the sales for user
	 * and format data accordingly
	 */
	public function get_sales($user_id)
	{
		// Get user products that have been sold
		$this->db->select('order_products.id as order_product_id');
		$this->db->select('order_products.*, product_styles.product_id, product_styles.style_id, products.*, api_99prints_styles.*, currencies.* ');
		$this->db->join('product_styles', 'product_styles.id = order_products.product_style_id');
		$this->db->join('products', 'products.id = product_styles.product_id');
		$this->db->join('api_99prints_styles', 'api_99prints_styles.id = product_styles.style_id');
		$this->db->join('orders', 'orders.id = order_products.order_id');
		$this->db->join('currencies', 'currencies.id = orders.currency_id');

		$this->db->where('products.user_id', $user_id);

		$this->db->order_by('order_products.id', 'desc');

		$sales = $this->get_all();

		$total_sales = 0;
		$total_earning = 0;

		foreach ($sales as &$sale) 
		{
			$amount_inr = $sale->product_base_price * ($sale->product_margin/100) * $sale->quantity;

			$amount = $this->currency_m->convert($amount_inr, 'INR', $_SESSION['es_currency']['code']);
			
			// Attaching earning
			$sale->earning = $amount;

			// Attaching designer payment
			$designer_payment = $this->designer_payment_m->get_by('order_product_id', $sale->order_product_id);
			if ($designer_payment->status == 1) {
				$designer_payment->status = 'Paid';
				if ($designer_payment->cheque != '') {
					$designer_payment->paid_via = "Cheque [$designer_payment->cheque]";
				} else {
					$designer_payment->paid_via = "NEFT [$designer_payment->neft]";
				}	
			} else {
				$designer_payment->status = 'Not Paid';
				$designer_payment->paid_via = "";
			}
			$sale->designer_payment = $designer_payment;

			// Calculating total
			$total_earning += $sale->earning;
			$total_sales += $sale->quantity;
		}

		return (object) [
			'sales' => $sales,
			'total_sales' => $total_sales,
			'total_earning' => $total_earning
		];
	}

}
