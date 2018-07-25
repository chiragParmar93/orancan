<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_autho_group_m extends MY_Model {

	public function add_autho_group_for($user_id, $group_name)
	{
		$group_id = $this->autho_group_m->get_by('name', $group_name)->id;

		$data = array(
			'user_id' => $user_id,
			'autho_group_id' => $group_id
			);

		$this->insert($data);
	}

}