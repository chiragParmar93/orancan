<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Style_color_m extends MY_Model {

    protected $_table = 'api_99prints_style_colors';

    /**
     * fetch all colors from stock available for style
     */
    public function get_avail_for_style($style_id)
    {
    	$this->db->select('api_99prints_style_colors.id, api_99prints_style_colors.code');
    	$this->db->where('style_id', $style_id);
    	$this->db->group_by('api_99prints_stock.style_color_id');
    	$this->db->join('api_99prints_stock', 'api_99prints_stock.style_color_id = api_99prints_style_colors.id');

    	return $this->get_all();
    }

    /**
     * get the default color for product and style
     */
    public function get_default_color($product_id, $style_id)
    {
        $color = $this->product_default_color_m->get_by(array( 'product_id' => $product_id,
                                                                 'style_id' => $style_id ));
        if ( ! count($color))
        {
            dump($product_id);
            dump($style_id);
            die();
        }

        return $this->get($color->color_id);
    }
}