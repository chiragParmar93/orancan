<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transparent_mockup_m extends MY_Model {

    protected $_table = 'api_99prints_transparent_mockups';

	public $belongs_to = array(
      		'design_view' => array( 'primary_key' => 'design_view_id',
      								'model' => 'design_view_m' ),
      		'style' => array( 'primary_key' => 'style_id',
      								'model' => 'style_m' )
      		);

	public $after_get = array( 'build_url' );


	/**
	 * insert or update in db
	 * then upload the file
	 */
	public function add()
	{
		// insert or update
		$id = $this->sync($_POST);

		// file front
		$file_front = $id . '-front.' . $this->ext($_FILES['file_front']['name']);
		$this->add_file($file_front, $_FILES['file_front']['tmp_name']);

		// file back
		$file_back = $id . '-back.' . $this->ext($_FILES['file_back']['name']);
		$this->add_file($file_back, $_FILES['file_back']['tmp_name']);

		// update the db
		$this->update($id, array('file_front' => $file_front, 'file_back' => $file_back));

		return TRUE;
	}

	/**
	 * get file extension
	 */
	public function ext($uploaded_name)
	{
		return pathinfo($uploaded_name, PATHINFO_EXTENSION);
	}

	/**
	 * add the file
	 */
	public function add_file($file_name, $tmp)
	{
		$upload_path = APPPATH . '../uploads/transparent_mockups/';

		$file = $upload_path . $file_name;

		// checking if file exsists
		if (file_exists($file)) unlink($file);

		// Place it into "uploads" folder
		move_uploaded_file($tmp, $file);
	}

	/**
	 * get for product add which require style, design view and colors
	 */
	public function get_for_product_add()
	{
		$selected = $this->with('design_view')->with('style')->get_active();

		// add colors
		foreach ($selected as $key => $mockup) 
		{
			// initialize with empty array
			$selected[$key]->style_colors = array();

			$this->db->group_by('style_color_id');
			$stocks = $this->stock_m->get_many_by(array('style_id'=>$mockup->style_id));

			foreach ($stocks as $stock) 
			{
				$selected[$key]->style_colors[] = $this->style_color_m->get($stock->style_color_id);
			}
		}

		return $selected;
	}

	/**
	 * return active transparent mockup
	 */
	public function get_active()
	{
		$this->db->select("{$this->_table}.*");
		$this->db->join('api_99prints_styles', "api_99prints_styles.id = {$this->_table}.style_id", 'left');
		$this->db->group_by("{$this->_table}.style_id");
		return $this->get_all();
	}

	/**
	 * build_url
	 *
	 * TODO: trigger NOT while syncing
	 */
	public function build_url($row)
	{
		if (isset($row->file_front) AND isset($row->file_back))
		{
			$row->file_front_url = site_url('uploads/transparent_mockups/' . $row->file_front);
			$row->file_back_url = site_url('uploads/transparent_mockups/' . $row->file_back);	
		}
		return $row;
	}

}
