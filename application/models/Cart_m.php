<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_m extends MY_Model {

    protected $_table = 'cart';

    protected $belongs_to = array(
        'product_style' => array( 'model' => 'product_style_m',
                                  'primary_key' => 'product_style_id' )
        );

    /**
     * Add items to cart
     */
    public function add()
    {
    	$_POST['user_id'] = $_SESSION['es_user']['id'];

    	$get_by = array(	'user_id' => $_POST['user_id'],
                            'product_style_id' => $_POST['product_style_id'],
    						'color_id' => $_POST['color_id'],
    						'size_id' => $_POST['size_id']	);
    	return $this->sync($get_by, $_POST);
    }

    /**
     * Update item quantity in the cart
     */
    public function update_quantity($user_id)
    {
        $get_by = array(    'user_id' => $user_id,
                            'product_style_id' => $_POST['product_style_id'],
                            'color_id' => $_POST['color_id'],
                            'size_id' => $_POST['size_id']  );
        
        $db_product = $this->get_by($get_by);

        if (count($db_product))
        {
            $this->update($db_product->id, array('quantity' => $_POST['quantity']));
        }        
    }

    /**
     * Get the data formatted for display products in user cart
     */
    public function user_cart($user_id)
    {
        $products = $this->get_many_by(array('user_id'=>$user_id));

        foreach ($products as $key => &$product)
        {
            $product_style = $this->product_style_m->get($product->product_style_id);
                      $product_id = $product_style->product_id;
			

            $product->design = $this->product_design_m->get_by(array('product_id' => $product_id));

            $product->design->url = site_url("uploads/designs/{$product_id}/{$product->design->design_view_id}/actual-185.png");

            $product->color = $this->style_color_m->get($product->color_id);

            $product->name = $this->style_m->get($product_style->style_id)->style_name;

            $product->size = $this->style_size_m->get($product->size_id)->style_size_name;

            $product->price = $product_style->price;

            $product->price_disp = $product_style->price_disp;

            $product->style_id = $product_style->style_id;
        }

        return $products;
    }
}