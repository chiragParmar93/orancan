<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_design_m extends MY_Model {

	/* --------------------------------------------------------------
     * BUILD URL METHODS
     * ------------------------------------------------------------ */ 

	/**
	 * Get actual image url
	 */
	public function actual_design_url($product_id, $design_view_id)
	{
		return site_url("uploads/designs/{$product_id}/{$design_view_id}/actual.png");
	}

	/**
	 * Get design url
	 */
	public function design_url($product_id, $design_view_id)
	{
		return site_url("uploads/designs/{$product_id}/{$design_view_id}");;
	}

	/**
	 * Get mockup image url
	 */
	public function mockup_design_url($product_id, $design_view_id)
	{
		return site_url("uploads/designs/{$product_id}/{$design_view_id}/mockup.png");
	}	

}
