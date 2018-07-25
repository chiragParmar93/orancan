<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_status_m extends MY_Model {

	protected $_table = 'order_statuses';

    /**
     * activating observers
     */
    public $before_create = array( 'created_at' );

    /**
     * return all the order status for order id
     */
    public function statuses($order_id)
    {
    	$this->db->join('order_status_values', 'order_status_values.id = order_statuses.value_id');
        $this->db->order_by('order_statuses.created_at', 'asc');
		return $this->get_many_by('order_id', $order_id);
    }

}