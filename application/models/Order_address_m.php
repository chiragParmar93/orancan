<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_address_m extends MY_Model {

	public $belongs_to = array(
		'address' => array(	'primary_key' => 'user_address_id',
							'model' => 'user_address_m' )
	);

}