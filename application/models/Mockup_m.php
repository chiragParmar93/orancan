<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mockup_m extends MY_Model {

	protected $_table = 'api_99prints_mockups';

	public $belongs_to = array( 
		'design_view' => array( 'primary_key' => 'design_view_id',
								'model' => 'design_view_m' )
		);

	/**
	 * insert or update in db
	 * then upload the file
	 */
	public function add()
	{
		// insert or update
		$id = $this->sync($_POST);

		$file_name = $id . '.' . pathinfo($_FILES['mockup_file']['name'], PATHINFO_EXTENSION);

		$file = APPPATH . '../uploads/mockups/' . $file_name;

		// checking if file exsists
		if (file_exists($file)) unlink($file);

		// Place it into your "uploads" folder
		move_uploaded_file($_FILES['mockup_file']['tmp_name'], $file);

		// update the db
		$this->update($id, array('mockup_file_name' => $file_name));
	}

	/**
	 * get only active
	 */
	public function get_active()
	{
		$this->db->select($this->_table . '.*');
		
		$this->db->join('api_99prints_styles', "api_99prints_styles.id = {$this->_table}.style_id");
		$this->db->join('api_99prints_sections', "api_99prints_sections.id = api_99prints_styles.section_id");

		$this->db->order_by('style_id');
		$this->db->order_by('style_color_id');
		$this->db->order_by('design_view_id');

		return $this->get_all();
	}

}
