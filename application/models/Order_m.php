<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_m extends MY_Model {

    /* --------------------------------------------------------------
     * VARIABLES
     * ------------------------------------------------------------ */	

	public $belongs_to = array(
		'paymode' => array(	'primary_key' => 'paymode_id',
							'model' => 'paymode_m'	),
	);

    /**
     * OBSERVERS
     */
    public $before_delete = array('delete_related');



    /* --------------------------------------------------------------
     * API METHODS
     * ------------------------------------------------------------ */	

	/**
	 * get xml formed data
	 */
	public function as_xml($order_id)
	{
		$order = $this->get($order_id);
		$order->paymode = $this->paymode_m->get($order->paymode_id)->paymode;
		$order->currency = $this->currency_m->get($order->currency_id)->code;
		
		$products = $this->order_product_m
						 ->with('color')
						 ->with('size')
						 ->get_many_by('order_id', $order_id);

		foreach ($products as &$product)
		{
			$db_product_id = $this->product_style_m->get($product->product_style_id)->product_id;
			$db_product = $this->product_m->get($db_product_id);
			$product->name = $db_product->title;
			$product->design_id = $db_product->id;
		}

		$shipping = $this->order_address_m
							->with('address')
							->get_by(array(
								'order_id' => $order_id,
								'type_id' => 1
							));

		$shipping->address->name_title = $this->name_title_m
												->get($shipping->address->name_title_id)->title;

		$shipping->email = $this->user_m->get($shipping->address->user_id)->email;

		$shipping->address->country = $this->country_m->get($shipping->address->country_id)->name;
		

		$billing = $this->order_address_m
							->with('address')
							->get_by(array(
								'order_id' => $order_id,
								'type_id' => 2
							));

		$billing->address->name_title = $this->name_title_m
												->get($billing->address->name_title_id)->title;

		$billing->address->country = $this->country_m->get($billing->address->country_id)->name;

		$data['order'] = $order;
		$data['products'] = $products;
		$data['shipping'] = $shipping;
		$data['billing'] = $billing;

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= $this->load->view('api_99prints/order_creation', $data, TRUE);
		
        return $xml;
	}

	/**
	 * returns yet to place orders
	 */
	public function yet_to_place()
	{
		$not_placed_id = $this->order_status_value_m
							  ->get_by(array( 'type_id' => 4,
							  				  'status' => 'not_placed'))
							  ->id;

		$placed_id = $this->order_status_value_m
						  ->get_by(array( 'type_id' => 4,
							  			  'status' => 'placed'))
						  ->id;

		$this->db->where('value_id', $not_placed_id);

		$order_ids = $this->order_status_m->get_array('order_id');

		foreach ($order_ids as $key => $order_id)
		{
			$placed = $this->order_status_m
						   ->get_by(array( 'order_id' => $order_id,
										   'value_id' => $placed_id ));
			if (count($placed))
			{
				unset($order_ids[$key]);
			}
		}

		return $order_ids;
	}

    /* --------------------------------------------------------------
     * GENERAL METHODS
     * ------------------------------------------------------------ */	

    /**
     * format the order id for public use
     */
    public function public_order_id($order_id)
    {
    	return $order_id . '-ELB';
    }

    /**
     * extract the db order id from public_order_id
     * TODO: make it robust
     */
    public function db_order_id($public_order_id)
    {
    	return explode('-', $public_order_id)[0];
    }

	/**
	 * Place the order from checkout page.
	 * Set status as checkout processing
	 * Sets the SESSION FOR PREPAID ORDER
	 */
	public function place_order()
	{
		$user_id 		= $_SESSION['es_user']['id'];
		$currency_id 	= $_SESSION['es_currency']['id'];
		$country_id 	= $_POST['shipping_country_id'];

		// add addresses
		$address_ids = $this->user_address_m->add_for_checkout($user_id);

		// add order
		// cost addition remaining
		$data_order = array(
			'user_id' => $user_id,
			'datetime' => date('Y-m-d H:i:s'),
			'currency_id' => $currency_id,
			'paymode_id' => $_POST['paymode_id']
			);
		$order_id = $this->insert($data_order);		
		// Add order status
		$this->checkout_processing($order_id);

		// 
		// add order addresses
		// --------------------
		$data_order_ship = array(
			'order_id' => $order_id,
			'user_address_id' => $address_ids['shipping_id'],
			'type_id' => 1
			);
		$this->order_address_m->insert($data_order_ship);	

		$data_order_bill = array(
			'order_id' => $order_id,
			'user_address_id' => $address_ids['billing_id'],
			'type_id' => 2
			);
		$this->order_address_m->insert($data_order_bill);

		// 
		// add order products
		// -------------------

		// Get everything from cart
		$data_order_products = $this->cart_m->get_many_by(array('user_id' => $user_id));
		
		// Empty the cart
		$this->cart_m->delete_by('user_id', $user_id);

		// will hold the respective cost
		$products_cost = 0;
		$shipping_cost = 0;			

		foreach ($data_order_products as &$product)
		{
			$p_style = $this->product_style_m->get($product->product_style_id);

			// Update the total sales of product
			$sales = $this->product_m->get($p_style->product_id)->sales + $product->quantity;
			$this->product_m->update($p_style->product_id, array('sales' => $sales));

			// Required to be inserted
			$product->order_id = $order_id;

			$product->product_cost = ($_SESSION['es_currency']['id']==1) ? $p_style->price_india : $p_style->price_inter;
			$product->product_cost = format_amount($product->product_cost);
            $product_cost_with_quantity = $product->product_cost * $product->quantity;
			$product->product_margin = $p_style->margin;
			$product->product_base_price = $this->product_style_m->get_base_price($p_style, $_SESSION['es_currency']['id']);

			// Costing
		  $products_cost += $product_cost_with_quantity;
			
			$shipping_charge = $this->shipping_charge_m->charge($p_style->style_id, $country_id, $_SESSION['es_currency']['code']);
			if ($shipping_cost < $shipping_charge)
			{
				$shipping_cost = $shipping_charge;
			}

			// NOT required to be inserted
			unset($product->id);
			unset($product->user_id);
		}
		$order_product_ids = $this->order_product_m->insert_many($data_order_products);

		// Inserting for designer payments
		foreach ($order_product_ids as $id)
		{
			$data = array('order_product_id' => $id);
			$this->designer_payment_m->insert($data);
		}

		// updating order with cost calculated
		if ($_POST['paymode_id'] == 2)
		{
			$shipping_cost += 50;
		}
	    $total_cost = format_amount($products_cost + $shipping_cost);
		$this->update($order_id, array(
			'products_cost' => format_amount($products_cost),
			'shipping_cost' => format_amount($shipping_cost),
			'total_cost' => $total_cost
		));   				

		// Guest email
		if ( ! $_SESSION['es_user']['email'])
		{
			$this->user_m->update($_SESSION['es_user']['id'], array('email' => $_POST['user_email']));
			$this->authe_m->update_session();
		}

		// Set the SESSION 
		$_SESSION['order_session'] = array(
			'order_id' => $order_id,
			'total_cost' => $total_cost,
			'email' => $_SESSION['es_user']['email'],
			'order_info' => 'Products brought from ES'
		);
		$_POST['billing_country'] = $this->country_m->get($_POST['billing_country_id'])->name;
		$_POST['shipping_country'] = $this->country_m->get($_POST['shipping_country_id'])->name;
		$_SESSION['order_session'] = array_merge($_SESSION['order_session'], $_POST);
	}

    /* --------------------------------------------------------------
     * ORDER STATUS METHODS
     * ------------------------------------------------------------ */	

	/**
	 * update order status payment gateway success
	 */
	public function payment_gateway_success()
	{
		$order_id = $_SESSION['order_session']['order_id'];

		$value_id = $this->order_status_value_m
							->get_by(array(
								'type_id' => 2,
								'status' => 'Success'
							))
							->id;

		$data = array(
			'order_id' => $order_id,
			'value_id' => $value_id
		);
		$this->order_status_m->insert($data);
	}

	/**
	 * update order status payment gateway failure
	 */
	public function payment_gateway_failure($status = '')
	{
		$order_id = $_SESSION['order_session']['order_id'];

		// Correct the status via DB
		$this->db->where('type_id', 2);
		$statuses = $this->order_status_value_m->get_array('status');
		if ( ! in_array($status, $statuses)) 
		{
			$status = 'Other';
		}

		$value_id = $this->order_status_value_m
							->get_by(array(
								'type_id' => 2,
								'status' => 'Failure'
							))
							->id;

		$data = array(
			'order_id' => $order_id,
			'value_id' => $value_id
		);
		$this->order_status_m->insert($data);
	}

	/**
	 * update checkout processing
	 */
	public function checkout_processing($order_id)
	{
		$data = array(
			'order_id' => $order_id,
			'value_id' => $this->order_status_value_m
							   ->get_by(array( 'type_id' => 1,
							   				   'status' => 'processing' ))
							   ->id
		);
		$this->order_status_m->insert($data);
	}

	/**
	 * update checkout success
	 * queue the order for cron
	 * and finally unset order_session
	 */
	public function checkout_success()
	{
		$order_id = $_SESSION['order_session']['order_id'];

		$data = array(
			'order_id' => $order_id,
			'value_id' => 2,
			);
		$this->order_status_m->insert($data);

		// queue the order for cron
		$data = array(
			'order_id' => $order_id,
			'value_id' => $this->order_status_value_m
							   ->get_by(array( 'type_id' => 4,
							   				   'status' => 'not_placed' ))
							   ->id
		);
		$this->order_status_m->insert($data);

		unset($_SESSION['order_session']);
	}

	/**
	 * update checkout failure
	 * rollback the cart with products
	 * and finally unset order_session
	 */
	public function checkout_failure()
	{
		$user_id = $_SESSION['es_user']['id'];
		$order_id = $_SESSION['order_session']['order_id'];

		$data = array(
			'order_id' => $order_id,
			'value_id' => 3
		);
		$this->order_status_m->insert($data);		

		$order_products = $this->order_product_m->get_many_by(array('order_id' => $order_id));

		foreach ($order_products as &$product)
		{
			// Removing unwanted
			unset($product->id);
			unset($product->order_id);
			unset($product->product_cost);
			unset($product->product_base_price);
			unset($product->product_margin);

			// Addding wanted
			$product->user_id = $user_id;

			$this->cart_m->insert($product);
		}

		unset($_SESSION['order_session']);
	}

    /* --------------------------------------------------------------
     * RETRIEVAL METHODS
     * ------------------------------------------------------------ */		

    /**
     * get data formatted for user orders
     */
    public function user_orders($user_id)
    {
 		$orders = $this->with('paymode')->get_many_by('user_id', $user_id);

 		foreach ($orders as $key => &$order)
 		{
 			$this->db->join('product_styles', 'product_styles.id = order_products.product_style_id');
 			$this->db->join('products', 'products.id = product_styles.product_id');
 			$this->db->join('api_99prints_styles', 'api_99prints_styles.id = product_styles.style_id');
 			$order->order_products = $this->order_product_m
 										  ->with('color')
 										  ->with('size')
 										  ->get_many_by('order_id', $order->id);

 			$order->amount = $this->currency_m->get($order->currency_id)->code . ' ' . $order->total_cost;

 			$order->statuses = $this->order_status_m->statuses($order->id);

 			// will include only those order that have checkout status
 			if (count($order->statuses) < 2)
 			{
 				unset($orders[$key]);
 			}
 		}

 		return $orders;
    }

    /* --------------------------------------------------------------
     * DELETE METHODS
     * ------------------------------------------------------------ */  

    /**
     * OBSERVER: called before delete and removes all the related db enteries
     * and files
     */
    public function delete_related($order_id)
    {
        // Delete order address
        $this->order_address_m->delete_by(array('order_id' => $order_id));

        // Delete order products
        $this->order_product_m->delete_by(array('order_id' => $order_id));

        // Delete order status
        $this->order_status_m->delete_by(array('order_id' => $order_id));
    }
}