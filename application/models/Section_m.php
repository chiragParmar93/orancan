<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Section_m extends MY_Model {

    protected $_table = 'api_99prints_sections';

    /* --------------------------------------------------------------
     * UTILITY METHODS
     * ------------------------------------------------------------ */

    /**
     * retun only active sections
     */
    public function only_active()
    {
    	$this->db->where('active', 1);
    	return $this;
    }

}
