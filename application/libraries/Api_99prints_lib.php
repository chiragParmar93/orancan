<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_99prints_lib
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
		$this->load->config('api_99prints');
	}

	/**
	 * Enables the use of CI super-global without having to define an extra variable.
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	/* --------------------------------------------------------------
     * RETRIEVAL METHODS
     * ------------------------------------------------------------ */

	/**
	 * try to sync products from api
	 */
	public function sync_products()
	{
		$url = $this->config->item('product_url');

		$post_data = array (
			"api_key" => $this->config->item('retrieval_key')
		);

		// fetch all products/sections
		$sections = $this->curl($url, $post_data)['section'];

		foreach ($sections as $section)
		{
			// sync sections
			$section_id = $this->section_m->sync(array('section_name' => $section['section_name']));

			// sync styles
			// NOTE: exception in baby case
			if ( isset( $section['style'][0] ) )
			{
				$this->style_m->sync_multiple($section['style'], array('section_id'=>$section_id));
			}
			else
			{
				$this->style_m->sync($section['style'], array('section_id'=>$section_id));
			}
		}
	}

	/**
	 * try to sync the stock from api
	 */
	public function sync_stock()
	{
		$url = $this->config->item('stock_url');

		$post_data = array (
			"api_key" => $this->config->item('retrieval_key')
		);
		
		// fetch all styles
		$styles = $this->curl($url, $post_data)['style'];

		foreach ($styles as $style) 
		{
			$style_code = $style['style_id'];

			$style_id = $this->style_m->get_by(array('style_code'=>$style_code))->id;

			if ( ! $style_id)
			{
				// the style has not been added yet
				// TODO: auto update product catalog
				show_error('style has not been added yet');
				return FALSE;
			}

			$style_colors = $style['style_color'];

			foreach ($style_colors as $style_color)
			{
				$style_color_name = $style_color['style_color_name'];

				$style_color_id = $this->style_color_m->sync(array('style_color_name' => $style_color_name));
				
				$style_sizes = $this->as_multi($style_color['style_size']);

				foreach ($style_sizes as $style_size)
				{
					$style_size_id = $this->style_size_m->sync(array('style_size_name'=>$style_size['size_name']));

					// for stock
					$get_by = array(
						'style_id' => $style_id,
						'style_color_id' => $style_color_id,
						'style_size_id' => $style_size_id
						);

					// NOTE: error in baby light pink
					if ( ! $style_size['size_stock'])
					{
						$style_size['size_stock'] = 0;
					}

					$this->stock_m->sync($get_by, array_merge($get_by, array('stock'=>$style_size['size_stock'])));
				}

			}
		}
	}

	/**
	 * try to sync countries from api
	 */
	public function sync_countries()
	{
		$url = $this->config->item('ship_countries_url');

		// fetch all countries
		$countries = $this->curl($url, array())['option'];

		$this->country_m->sync_multiple_for('name', $countries);
	}

	/**
	 * try to sync countries from excel
	 */
	public function sync_countries_excel()
	{
		$countries = array();

		$inputFileName = APPPATH . '../../countries.xlsx';

		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		
		$objPHPExcel = $objReader->load($inputFileName);

		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) 
		{
			foreach ($worksheet->getRowIterator() as $row) 
			{
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
				
				$i = 0;
				foreach ($cellIterator as $cell) 
				{
					if ( ! is_null($cell))
					{
						$i++;
						if ($i == 1)
						{
							$country = $cell->getCalculatedValue();
							$countries[$country] = 0;
						}
						if ($i == 2)
						{
							$countries[$country] = $cell->getCalculatedValue();

							$i = 0;
						}
					}
				}
			}
		}

		foreach ($countries as $country => $zone) 
		{
			$db_country = $this->country_m->get_by(array('name'=>$country));

			if (count($db_country))
			{
				$this->country_m->update($db_country->id, array('zone' => $zone));
			}
		}
	}

	/**
	 * try to sync shipping charges from excel
	 */
	public function sync_shipping_charges()
	{
		$charges = array();
		
		$inputFileName = APPPATH . '../../shipping_charges.xlsx';

		$reader = new SpreadsheetReader($inputFileName);

	    foreach ($reader as $key => $row)
	    {
	        if ($key != 0)
	        {
	        	$weight = (string) $row[0];
	        	$cost = array_slice($row, 1);

	        	$charges[$weight] = $cost;
	        }
	    }

	    foreach ($charges as $weight => $zone_charge)
	    {
	    	// $zone_charge with 0 index BUT actual ZONE 1
	    	// means actual zone will be index + 1
	    	foreach ($zone_charge as $zone => $charge)
	    	{
	    		$act_zone = $zone + 1;

	    		$get_by = array('weight' => $weight, 'zone' => $act_zone);

	    		$data = array('weight' => $weight, 'zone' => $act_zone, 'charge' => $charge);

	    		$this->shipping_charge_m->sync($get_by, $data);
	    	}
	    }

	}

	/**
	 * try to sync titles from api
	 */
	public function sync_titles()
	{
		$url = $this->config->item('title_url');

		// fetch all titles
		$titles = $this->curl($url, array())['option'];

		$this->name_title_m->sync_multiple_for('title', $titles);
	}

	/**
	 * try to sync paymodes from api
	 */
	public function sync_paymodes()
	{
		$url = $this->config->item('paymode_url');

		// fetch all paymodes
		$titles = $this->curl($url, array())['option'];

		$this->paymode_m->sync_multiple_for('paymode', $titles);
	}

	/**
	 * Is shipping available
	 */
	public function is_shipped($country, $pincode, $paymode)
	{
		$url = $this->config->item('shipping_url');

		$post_data = array (
			"api_key" => $this->config->item('retrieval_key'),
			"country_name" => $country,
			"pincode" => $pincode,
			"paymode" => $paymode
			);
		
		// fetch shipping details
		$shipping = $this->curl($url, $post_data);		

		if ($shipping['request_status'] == 200)
		{
			return ($shipping['service_available'] == 'Yes') ? TRUE : FALSE;
		}

		return FALSE;
	}

	/* --------------------------------------------------------------
     * UPDATION METHODS
     * ------------------------------------------------------------ */

	/**
	 * add design via api
	 */
	public function add_design($xml)
	{
		$url = $this->config->item('design_url');

		$post_data = array(	'api_key' => $this->config->item('updation_key'), 
							'design_data' => $xml );

		// try to add design
		return $this->curl_xml($url, $post_data);
	}

	/**
	 * place order for the product
	 */
	public function place_order($xml)
	{
		$url = $this->config->item('order_url');

		$post_data = array(	'api_key' => $this->config->item('updation_key'), 
							'order_data' => $xml );

		// try to add design
		return $this->curl_xml($url, $post_data);
	}

	/**
	 * get the order status
	 */
	public function order_status($order_id)
	{
		$url = $this->config->item('order_status_url');
		
		$post_data = array(	'api_key' => $this->config->item('updation_key'), 
							'order_code' => $order_id . '-ELB' );

		// return the response
		return $this->curl_https($url, $post_data);

	}

	/* --------------------------------------------------------------
     * HELPER METHODS
     * ------------------------------------------------------------ */
	
	/**
	 * curl to url with post data. Returns an array of output
	 */
	public function curl($url, $post_data, $to_arr = TRUE)
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

		if ($to_arr)
		{
			return $this->xml_to_arr($output);
		}
		else
		{
			return $output;
		}
	}

	/**
	 * curl https to url
	 */
	public function curl_https($url, $post_data, $to_arr = TRUE)
	{
		$ch = curl_init();
	    
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        $params = http_build_query($post_data);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $params );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_HEADER, 0);
        
        $output = curl_exec($ch);

	    curl_close($ch);		

	    if ($to_arr)
		{
			return $this->xml_to_arr($output);
		}
		else
		{
			return $output;
		}
	}

	/**
	 * curl for xml request
	 */
	public function curl_xml($url, $post_data)
	{
		$params = http_build_query($post_data);

		$headers = array(
			"Accept: text/xml",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"SOAPAction: \"run\""
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);

		// send xml request to a server
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		
		curl_close($ch);

        //convert the XML result into array
        if($response === false)
        {
			$error = curl_error($ch);
			echo $error; 
			die('error occured');
        }
        else
        {
        	return $response;
        }
	}

	/**
	 * convert xml to array then return it
	 */
	protected function xml_to_arr($xml_string)
	{
		$xml = simplexml_load_string($xml_string);
		$json = json_encode($xml);
		$arr = json_decode($json, TRUE);

		return $arr;
	}

	/**
	 * IF single thing array THEN convert it into multi thing array
	 * 
	 * there are instances where 99prints return single thing or multi thing
	 * for same property
	 */
	protected function as_multi($arr)
	{
		if ( ! isset($arr[0]))
		{
			return array($arr);
		}
		else
		{
			return $arr;
		}
	}	

}