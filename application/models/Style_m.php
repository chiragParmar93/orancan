<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Style_m extends MY_Model {

    protected $_table = 'api_99prints_styles';

	public $belongs_to = array( 
		'section' => array( 'model' => 'section_m',
							'primary_key' => 'section_id' )
		);

	/**
	 * contain style type with their style_ids
	 * TODO: drop these into their table
	 */
	public $style_types = array (
		'Ringer T-Shirt' => array(1),
		'Round Neck T-Shirt' => array(3, 10),
		'Full Sleeve T-Shirt' => array(6),
		'V-Neck T-Shirt' => array(7),
		// 'Hooded Sweatshirt' => array(4, 11)
	);

	/* --------------------------------------------------------------
     * DATA FORMATTER METHODS
     * ------------------------------------------------------------ */

	/**
	 * get the style type by style_id
	 * @return [type] [description]
	 */
	public function get_style_type($style_id)
	{
		foreach ($this->style_types as $key => $st)
		{
			if (in_array($style_id, $st))
			{
				return $key;
			}
		}
		return '';
	}

	public function get_for_product_add()
	{
		$this->in_sequence();
		$styles = $this->only_active()->get_all();

		foreach ($styles as &$style)
        {
            // Attaching mockup
            $style->t_mockups = $this->transparent_mockup_m
                                            ->get_many_by('style_id', $style->id);
           
            // Attaching colors
            $style->colors = $this->style_color_m->get_avail_for_style($style->id);

            // Attaching base prices
            $style->base_prices = $this->product_base_price_m->get_by('style_id', $style->id);
        }

        return $styles;
	}


	/* --------------------------------------------------------------
     * UTILITY METHODS
     * ------------------------------------------------------------ */
	
	/**
	 * return only active
	 */
	public function only_active()
	{
		$this->db->where($this->_table . '.active', 1);
		return $this;
	}

	/**
	 * Always return in sequence
	 */
	public function in_sequence()
	{
		$this->db->order_by($this->_table . '.sequence', 'asc');
	}
}