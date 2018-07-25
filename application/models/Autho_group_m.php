<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autho_group_m extends MY_Model {

	/**
	 * Returns array of authorization groups
	 * @return [type] [description]
	 */
	public function autho_for_user($user_id)
	{
		$autho = array();

		$user_autho_groups = $this->users_autho_group_m->get_many_by('user_id', $user_id);
		foreach ($user_autho_groups as $group) 
		{
			$autho[] = $this->get($group->autho_group_id)->name;
		}

		return $autho;
	}

}
