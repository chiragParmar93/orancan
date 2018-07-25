<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** 
 * A model for athorization
 */
class Autho_m extends Alert_Model {

    /* --------------------------------------------------------------
     * GENERIC METHODS
     * ------------------------------------------------------------ */

    /**
     * Initialise the model and load the required stuff
     */
    public function __construct()
    {
    	parent::__construct();    	
    }
	
	/**
	 * add autho_group to the user
	 */
	public function add_autho_group_for($user_id, $autho_group)
	{
		// get autho_group id
		$data['autho_group_id'] = $this->autho_group_m->get_by(array('name'=>$autho_group))->id;

		$data['user_id'] = $user_id;

		return $this->users_autho_group_m->insert($data);
	}

	/**
	 * add autho_groups to the user
	 */
	public function add_autho_groups_for($user_id, $autho_groups)
	{
		$ids = array();

		foreach ($autho_groups as $autho_group)
		{
			$ids[] = $this->add_autho_group_for($user_id, $autho_group);
		}

		return $ids;
	}

	/**
	 * upgrade the SESSION user to designer
	 */
	public function upgrade_to($autho_group)
	{
		$_SESSION['es_user']['autho_groups'][] = $autho_group;
		$this->add_autho_group_for($_SESSION['es_user']['id'], $autho_group);
	}
}