<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Interest_m extends MY_Model {

	/**
	 * try to sync but first covert to lower case
	 */
    public function sync($get_by, $data = NULL)
	{
		if ( ! $data)
		{
			$data = $get_by;
		}

		foreach ($data as $key => $value)
		{
			$data[$key] = strtolower($value);
		}

		return parent::sync($get_by, $data);
	}

}
