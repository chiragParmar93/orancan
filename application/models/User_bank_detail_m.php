<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_bank_detail_m extends MY_Model {

	/**
	 * sync the neft of user.
	 * This will be modified by Loggedin user
	 */
	public function sync_neft()
	{
		$get_by['user_id'] = $_SESSION['es_user']['id'];
		
		$data['user_id'] = $_SESSION['es_user']['id'];
		$data['neft_name'] = $_POST['neft_name'];
		$data['neft_account'] = $_POST['neft_account'];
		$data['neft_bank'] = $_POST['neft_bank'];
		$data['neft_ifsc'] = $_POST['neft_ifsc'];

		return $this->sync($get_by, $data);
	}

	/**
	 * sync the cheque of user.
	 * This will be modified by Loggedin user
	 */
	public function sync_cheque()
	{
		$get_by['user_id'] = $_SESSION['es_user']['id'];
		
		$data['user_id'] = $_SESSION['es_user']['id'];
		$data['cheque_name'] = $_POST['cheque_name'];
		$data['cheque_address'] = $_POST['cheque_address'];

		return $this->sync($get_by, $data);
	}

	/**
	 * Get for edit
	 * properly fill all the fields required
	 * TODO: get all table fields
	 */
	public function get_for_edit($get_by = NULL)
	{	
		if ( ! $get_by )
		{
			$db_data = NULL;
		}
		else
		{		
			$db_data = $this->get_by($get_by);
		}
		
		if ( ! $db_data)
		{
			$db_data = new stdClass();
			$fields = $this->db->list_fields($this->_table);
			foreach ($fields as $field)
			{
				$db_data->$field = '';
			}
		}
		return $db_data;
	}

}