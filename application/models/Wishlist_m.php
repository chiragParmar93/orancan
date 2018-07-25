<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wishlist_m extends MY_Model {

    protected $_table = 'wishlist';

    /**
     * Add items to wishlist
     */
    public function add()
    {
    	$get_by = array( 'user_id' => $_SESSION['es_user']['id'],
                         'product_id' => $_POST['product_id'] );

    	return $this->sync($get_by);
    }


    /**
     * Get the data formatted for display products in user wishlist
     */
    public function user_wishlist()
    {
        $wishlist = $this->get_many_by('user_id', $_SESSION['es_user']['id']);
        
        $products = [];
        foreach ($wishlist as $wish)
        {
            $product = $this->product_m->get($wish->product_id);
            
            $product->user = $this->user_m->get($product->user_id)->first_name;

            $product->design = $this->product_design_m->get_by(array('product_id'=>$product->id));

            $product->design->url = site_url("uploads/designs/{$product->id}/{$product->design->design_view_id}/display.png");

            $product->product_style = $this->product_style_m->get_by(array('product_id'=>$product->id));            

            $product->t_mockup = $this->transparent_mockup_m->get_by(array('style_id' => $product->product_style->style_id,
                                                                'design_view_id' => $product->design->design_view_id));

            $product->default_color = $this->style_color_m
                                                ->get_default_color($product->id, $product->product_style->style_id);

            $products[] = $product;
        }

        return $products;        
    }
}