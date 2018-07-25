<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authentication library
 * Combine authe and autho controllers
 *
 * @author Shavi Pathania <shavi@xorlabs.in>
 */

class Auth
{
    /* --------------------------------------------------------------
     * GENERAL METHODS
     * ------------------------------------------------------------ */

	/**
	 * Enables the use of CI super-global without having to define an extra variable.
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

    /* --------------------------------------------------------------
     * AUTHO METHODS
     * ------------------------------------------------------------ */

    /**
     * check user autho group
     */
    public function is($group)
    {
        if ( ! isset($_SESSION['es_user'])) return FALSE;

        return in_array($group, $_SESSION['es_user']['autho_groups']);
    }
}

/* End of file Auth.php */
/* Location: ./application/libraries/Auth.php */
