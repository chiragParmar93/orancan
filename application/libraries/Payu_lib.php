<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payu_lib
{
	/* --------------------------------------------------------------
     * GENERAL METHODS
     * ------------------------------------------------------------ */

	/**
	 * Init the lib and load the config
	 */
	public function __construct()
	{		
		// Load the config
		$this->load->config('payu');
	}

	/**
	 * Enables the use of CI super-global without having to define an extra variable.
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	/* --------------------------------------------------------------
     * SEND METHODS
     * ------------------------------------------------------------ */

	/**
	 * send the post request to payu with all fields
	 */
	public function send($post_data)
	{
		$url = $this->config->item('payu_url');

		$this->curl($url, $post_data);
	}

	/* --------------------------------------------------------------
     * HELPER METHODS
     * ------------------------------------------------------------ */
	
	/**
	 * curl to url with post data. Returns an array of output
	 */
	protected function curl($url, $post_data)
	{

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// we are doing a POST request
		curl_setopt($ch, CURLOPT_POST, 1);
		// adding the post variables to the request
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		$output = curl_exec($ch);

		curl_close($ch);

		return $output;
	}

}

/* End of file Payumoney_lib.php */
/* Location: ./application/libraries/Payumoney_lib.php */
