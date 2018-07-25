<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_style_m extends MY_Model {
		
	public $belongs_to =  array( 
		
		'product' => array( 'primary_key' => 'product_id',
								'model' => 'product_m' ),
		
		'style' => array( 'primary_key' => 'style_id',
								'model' => 'style_m' )
	);

    // BUG: not working
	public $has_many = array(
		'designs' => array( 'primary_key' => 'product_id',
								'model' => 'product_design_m' )
	);

    public $after_get = array( 'attach_price' );


    /* --------------------------------------------------------------
     * GENERAL METHODS
     * ------------------------------------------------------------ */  

    /**
     * Get the base price for product style in currency
     * TODO: both or single should be seprate method
     */
    public function get_base_price($p_style, $currency_id)
    {
        $product = $this->product_m->get($p_style->product_id);
        $designs = $this->product_design_m->get_many_by('product_id', $p_style->product_id);
        $base_price = $this->product_base_price_m->get_by('style_id', $p_style->style_id);
        
        $price = 0;
        if (count($designs) == 1 AND $currency_id == 1)
        {
            $price = $base_price->india_one_sided;
        }
        else if (count($designs) == 2 AND $currency_id == 1)
        {
            $price = $base_price->india_both_sided;
        }
        else if (count($designs) == 1 AND $currency_id == 2)
        {
            $price = $base_price->inter_one_sided;
        }
        else if (count($designs) == 2 AND $currency_id == 2)
        {
            $price = $base_price->inter_both_sided;
        }
        return $price;
    }


    /* --------------------------------------------------------------
     * CRUD METHODS
     * ------------------------------------------------------------ */  

	/**
	 * will save the product style calculating it's price
	 * NOTE: this have been done to improve the performance of website
     * BUT: this will not get updated with change in product base prices
	 */
	public function save_with_price($product_id, $style_id, $margin, $sides)
	{
		$base_price = $this->product_base_price_m->get_by(array('style_id'=>$style_id));

		$price_india = 0;
		$price_inter = 0;

		if ($sides == 1)
		{
			$price_india = $base_price->india_one_sided + $base_price->india_one_sided * $margin / 100 ;
			$price_inter = $base_price->inter_one_sided + $base_price->inter_one_sided * $margin / 100 ;
		}
		else if ($sides == 2)
		{
			$price_india = $base_price->india_both_sided + $base_price->india_both_sided * $margin / 100 ;
			$price_inter = $base_price->inter_both_sided + $base_price->inter_both_sided * $margin / 100 ;
		}

		$price_india = round_amount($price_india);
		$price_inter = round_amount($price_inter);

        // TODO: make it robust
        $ps = $this->get_by(array('product_id' => $product_id, 'style_id' => $style_id));
        if ( ! count($ps))
        {
            $this->insert(array(
                'product_id' => $product_id,
                'style_id' => $style_id,
                'margin' => $margin,
                'price_india' => $price_india,
                'price_inter' => $price_inter
            ));    
        }
        else
        {
            $this->update($ps->id, array(
                'margin' => $margin,
                'price_india' => $price_india,
                'price_inter' => $price_inter
            ));
        }		
	}

    /* --------------------------------------------------------------
     * API METHODS
     * ------------------------------------------------------------ */  

    /**
     * Fetch product data in xml format suitable for api design addition
     */
    public function as_xml($product_style_id)
    {
        $product_style = $this->with('product')
        						->with('style')
        						// ->with('designs')
        						->get($product_style_id);

        // Bcoz of bug have to add seprately
        $product_style->designs = $this->product_design_m->get_many_by('product_id', $product_style->product_id);

        $data['product'] = $product_style->product;
        $data['style'] = $product_style->style;
        $data['designs'] = $product_style->designs;

        foreach ($data['designs'] as &$design)
        {
        	$design->view_name = $this->design_view_m->get($design->design_view_id)->name;
        	$design->actual_url = $this->product_design_m->actual_design_url($data['product']->id, $design->design_view_id);
        	$design->mockup_url = $this->product_design_m->mockup_design_url($data['product']->id, $design->design_view_id);
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= $this->load->view('api_99prints/design_addition', $data, TRUE);
        
        return $xml;
    }

    /* --------------------------------------------------------------
     * COSTING METHODS
     * ------------------------------------------------------------ */  

    /**
     * get the product cost in currency
     */
    public function get_price($product_style, $currency_id)
    {
    	if ($currency_id == 1)
    	{
    		// INR
    		return $product_style->price_india;
    	}
    	else if ($currency_id == 2)
    	{
    		// USD
    		return $this->get($product_style_id)->price_inter;
    	}

    	return FALSE;
    }

    /* --------------------------------------------------------------
     * OBSERVER METHODS
     * ------------------------------------------------------------ */  

    /**
     * Attach price using the session variable
     */
    public function attach_price($row)
    {
        if ( ! isset($row->price_india)) return $row;

        $row->price         = ($_SESSION['es_currency']['id'] == 1) ? $row->price_india : $row->price_inter;
        $row->price_disp    = $_SESSION['es_currency']['code'] . ' ' . $row->price;
        return $row;
    }

    /**
     * return in sequence
     */
    public function in_sequence()
    {
        $this->db->select('product_styles.*');
        $this->db->join('api_99prints_styles', 'api_99prints_styles.id = product_styles.style_id');
        $this->db->where('api_99prints_styles.active', 1);
        $this->db->order_by('api_99prints_styles.sequence', 'asc');
    }
}
