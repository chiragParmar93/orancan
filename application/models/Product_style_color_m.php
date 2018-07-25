<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_style_color_m extends MY_Model {

	public $belongs_to = array( 
		'style_color' => array( 'model' => 'style_color_m',
							'primary_key' => 'color_id' )
		);


	/**
	 * get all colors for product style.
	 * Returns colors with default color set
	 */
	public function get_avail_for_product_style($product_id, $style_id)
	{
		$this->db->select('api_99prints_style_colors.id, code');
		$this->db->join('api_99prints_style_colors', 'api_99prints_style_colors.id = product_style_colors.color_id');

		$colors = $this->get_many_by(array(
			'product_id' => $product_id,
			'style_id' => $style_id
			));

		$default_color_id = $this->product_default_color_m->get_by(array(
			'product_id' => $product_id,
			'style_id' => $style_id
			))->color_id;

		foreach ($colors as &$color)
		{
			if ($color->id == $default_color_id)
			{
				$color->default_color = TRUE;
			}
		}

		return $colors;
	}
}
