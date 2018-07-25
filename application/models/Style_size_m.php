<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Style_size_m extends MY_Model {

    protected $_table = 'api_99prints_style_sizes';

    /**
     * get all the sizes availabe for style_id
     */
    public function get_avail_for_style($style_id)
    {
    	$this->db->select('style_size_id');
    	$this->db->group_by('style_size_id');
    	$all_stock = $this->stock_m->get_many_by('style_id', $style_id);

    	$ids = array();
    	foreach ($all_stock as $stock)
    	{
    		$ids[] = $stock->style_size_id;
    	}

    	return $this->get_many($ids);
    }

}