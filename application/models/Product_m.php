<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A model for Product
 */
class Product_m extends MY_Model {

    /* --------------------------------------------------------------
     * VARIABLES
     * ------------------------------------------------------------ */  

    protected $primary_key = 'products.id';

    /**
     * Relationships
     */
    public $belongs_to = array( 'user' => array(   'model' => 'user_m',
                                                    'primary_key' => 'user_id' ) );
    public $has_many = array( 'designs' => array(   'model' => 'product_design_m',
                                                    'primary_key' => 'product_id' ) );

    /**
     * OBSERVERS
     */
    public $before_get = array('fetch_successfully_uploaded');
    public $before_delete = array('delete_related');


    /* --------------------------------------------------------------
     * INSERT METHODS
     * ------------------------------------------------------------ */	

    /**
     * Let's try to save product correctly :)
     */
    public function save($product_id)
    {
        ini_set('memory_limit', '-1');
        
        $data_product = array(  'user_id'       => $_SESSION['es_user']['id'],
                                'title'         => $_POST['product_title'],
                                'slug'          => url_title(strtolower($_POST['product_title'])),
                                'description'   => $_POST['product_description'],
                                'design_status' => $_POST['design_status'],
                                'upload_status' => 0  ); // initially setting it to 0
        
        if ($product_id == '') // ADD
        {   
            $product_id = $this->insert($data_product); 

            $this->process_posted_fabrics_for($product_id);

            $this->add_related($product_id); 
        }
        else // UPDATE
        {
            // Delete related
            $this->products_category_m->delete_by(array('product_id' => $product_id));
            $this->products_interest_m->delete_by(array('product_id' => $product_id));
            $this->product_style_color_m->delete_by(array('product_id' => $product_id));
            $this->product_default_color_m->delete_by(array('product_id' => $product_id));
            $this->product_design_m->delete_by(array('product_id' => $product_id));
            
            // Remove the dir
            rrmdir(APPPATH . '../uploads/designs/' . $product_id);

            $this->update($product_id, $data_product);
            $this->process_posted_fabrics_for($product_id);

            $this->add_related($product_id); 
        }  

        // updating upload_status
        $this->update($product_id, array('upload_status' => 1));

    	return TRUE;
    }

    /**
     * add related product things
     */
    public function add_related($product_id)
    {
        // add interest
        $_POST['interests'] = explode(',', $_POST['interests']);
        $interest_ids = $this->interest_m->sync_multiple_for('interest', $_POST['interests']);
    
        // tag product with interest_ids
        foreach ($interest_ids as $interest_id)
        {
            $this->products_interest_m->insert(array( 'product_id' => $product_id, 
                                                      'interest_id' => $interest_id ));
        }

        // tag product with categories
        foreach ($this->input->post('product_categories') as $category_id)
        {
            $this->products_category_m->insert(array( 'product_id' => $product_id,
                                                      'product_category_id' => $category_id ));
        }

        // process styles
        foreach ($this->input->post('product_styles') as $style)
        {
            // tag product with style, add margin and price
            $sides = 1;
            if ($_POST['fabric_front'] != '' AND $_POST['fabric_back'] != '') $sides = 2;

            $this->product_style_m->save_with_price($product_id, $style['id'], $style['margin'], $sides);
            unset($sides);

            // tag product with style_colors
            foreach ($style['colors'] as $color_id)
            {
                $this->product_style_color_m->insert(array( 'product_id' => $product_id,
                                                            'style_id' => $style['id'],
                                                            'color_id' => $color_id ));
            }

            // tag default color
            $this->product_default_color_m->insert(array( 'product_id' => $product_id,
                                                            'style_id' => $style['id'],
                                                            'color_id' => $style['default_color'] ));
        }            
        
    }

    /**
     * process the fabrics posted
     */
    public function process_posted_fabrics_for($product_id)
    {
    	if ($_POST['fabric_front'] != '')
    	{
    		$this->process_fabric_for($product_id, 'front', $_POST['fabric_front']);
    	}
    	
    	if ($_POST['fabric_back'] != '')
    	{
    		$this->process_fabric_for($product_id, 'back', $_POST['fabric_back']);
    	}	
    }

    /**
     * process the fabric objects
     */
    public function process_fabric_for($product_id, $design_view, $fabric)
    {   
    	$design_view_id = $this->design_view_m->get_by(array('name'=>$design_view))->id;

        $gen_path = APPPATH . '../uploads/designs/' . $product_id . '/' . $design_view_id . '/';

        cmkdir($gen_path);

        // Save the fabric json file to disk
        $fabric_json_file = 'fabric.json';
        if ( ! write_file($gen_path . $fabric_json_file, $fabric))
        {
            die('Not able to write the file');
        }

        // extracting image object
        $fabric_obj = json_decode($fabric);
        $fabric_img_obj = $fabric_obj->objects[0];

        $this->generate_images_for($product_id, $design_view_id, $fabric_img_obj, $gen_path);

        // calculate the print dimensions
        $print_width = round(($fabric_img_obj->width * $fabric_img_obj->scaleX) / 12, 2);
        $print_height = round(($fabric_img_obj->height * $fabric_img_obj->scaleY) / 12, 2);
        $print_top = $fabric_img_obj->top / 12;

        // Save product design to db
        $this->product_design_m->insert(array(  'product_id' => $product_id,
                                                'design_view_id' => $design_view_id,
                                                'fabric_json_file' => $fabric_json_file,
                                                'print_width' => $print_width,
                                                'print_height' => $print_height,
                                                'print_top' => $print_top   ));
    }

    /**
     * Generate images from fabric image object
     */
    public function generate_images_for($product_id, $design_view_id, $fabric_img_obj, $gen_path)
    {        
        $act_file = $gen_path . 'actual.png';
        $act_185_file = $gen_path . 'actual-185.png';
        $disp_file = $gen_path . 'display.png';
        $mockup_file = $gen_path . 'mockup.png';
        
        //
        // Generating the Actual Image
        // ---------------------------
        $act_img_res = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fabric_img_obj->src));
        file_put_contents($act_file, $act_img_res);
        unset($act_img_res);

        //
        // Generating Display image
        // -------------------------
        $act_img_res = imagecreatefrompng($act_file);
        $disp_img_res = xl\image\trans_img_res(144, 180);

        if ( imagecopyresampled( $disp_img_res, $act_img_res, 
                                 $fabric_img_obj->left, $fabric_img_obj->top, 
                                 0, 0, 
                                 $fabric_img_obj->width * $fabric_img_obj->scaleX, 
                                 $fabric_img_obj->height * $fabric_img_obj->scaleY, 
                                 $fabric_img_obj->width, $fabric_img_obj->height ) )
        {
            imagepng($disp_img_res, $disp_file, 0);
            imagedestroy($disp_img_res);
            imagedestroy($act_img_res);
        }
        else
        {
            // TODO: make it robust
            //echo 'failed';
        }

        //
        // Generating act_185 image
        // --------------------------
        xl\image\resize_image($act_file, $act_185_file, 400, 400);

        //
        // Generating Mockup images
        // -------------------------
        if ($design_view_id == 1) {
            $act_mockup_file = APPPATH . '../uploads/mockups/front.jpg';
        } else {
            $act_mockup_file = APPPATH . '../uploads/mockups/back.jpg';
        }
        $this->gen_mockup_image($mockup_file, $act_mockup_file, $disp_file);
    }

    public function gen_mockup_image($merge_mockup_file, $act_mockup_file, $design_file)
    {
        $dst_file = $merge_mockup_file;
        $dst_size = array(370, 488);
        $dst_res = xl\image\trans_img_res($dst_size[0], $dst_size[1]);

        // Resizing mockup
        $src_file = $act_mockup_file;
        $src_size = getimagesize($src_file);
        if ($src_size['mime'] == 'image/png') {
            $src_res = imagecreatefrompng($src_file);
        } else {
            $src_res = imagecreatefromjpeg($src_file);
        }
        
        imagecopyresampled($dst_res , $src_res , 0 , 0 , 0 , 0 , $dst_size[0], $dst_size[1] , $src_size[0] , $src_size[1]);
        imagedestroy($src_res);
        imagepng($dst_res, $dst_file, 0);

        // Merging resized mockup with design
        $src_file = $design_file;
        $src_res = imagecreatefrompng($src_file);
        $src_size = getimagesize($src_file);

        imagecopyresampled($dst_res , $src_res , 118 , 200 , 0 , 0 , $src_size[0], $src_size[1] , $src_size[0] , $src_size[1]);
        imagedestroy($src_res);
        imagepng($dst_res, $dst_file, 0);
        imagedestroy($dst_res);
    }


    /* --------------------------------------------------------------
     * DISPLAY METHODS
     * ------------------------------------------------------------ */  

    /**
     * Get the data formatted suitable for display single product
     */
    public function get_for_display_single($slug, $product_id)
    {
        $product = $this->product_m->get_by(array(
            'slug' => $slug,
            'id' => $product_id
            ));

        if ( ! $product) return FALSE;

        // Simply getting product_styles
        $this->product_style_m->in_sequence();
        $product->product_styles = $this->product_style_m->get_many_by('product_id', $product->id);

        $product->product_designs = $this->product_design_m->get_many_by('product_id', $product->id);

        $this->db->where('product_id', $product->id);
        $product->category_ids = $this->products_category_m->get_array('product_category_id');

        $product->user = $this->user_m->get($product->user_id);

        foreach ($product->product_styles as &$p_style)
        {
            // Attaching mockup
            $p_style->t_mockups = $this->transparent_mockup_m
                                            ->get_many_by(array( 'style_id' => $p_style->style_id ));

            // Attaching colors
            $p_style->colors = $this->product_style_color_m
                                        ->get_avail_for_product_style($product->id, $p_style->style_id);
                                                    
            // Attaching sizes
            $p_style->sizes = $this->style_size_m->get_avail_for_style($p_style->style_id);

            // Attaching style name
            $style = $this->style_m->get($p_style->style_id);
            $p_style->style_name = $style->display_name;

            // Attaching section name
            $p_style->section_name = $this->section_m->get($style->section_id)->section_name;
        }

        foreach ($product->product_designs as &$p_design)
        {
            $p_design->url = site_url("uploads/designs/{$product->id}/{$p_design->design_view_id}/display.png");
            $p_design->size_185_url = site_url("uploads/designs/{$product->id}/{$p_design->design_view_id}/actual-185.png");
        }

        return $product;
    }

    /**
     * Get the data formatted suitable for dispaly products in `SHOP`
     */
    public function get_for_shop($filters)
    {
        if ( $filters['section'] != 'all' )
        {
            $this->db->where('section_name', $filters['section']);
        }
        
        if ( $filters['style'] != 'all' )
        {
            $style_ids = $this->style_m->style_types[$filters['style']];
            $this->db->where_in('api_99prints_styles.id', $style_ids);
        }

        if ( $filters['category'] != 'all' )
        {
            $this->db->where('product_categories.name', $filters['category']);
        }
        
        if ( $filters['color'] != 'all' )
        {
            $this->db->where('api_99prints_style_colors.style_color_name', $filters['color']);
        }

        if ( $filters['search'] != 'all' )
        {
            $search = $filters['search'];
            
            $this->db->group_start();
            
            $this->db->like('products.title', $search); 
            $this->db->or_like('users.first_name', $search);
            $this->db->or_like('interests.interest', $search);

            $this->db->group_end();
        }

        if ( $filters['sort'] )
        {
            if ($filters['sort'] == 1)
            {
                $this->db->order_by('products.id', 'desc');
            }
            else if ($filters['sort'] == 2)
            {
                $this->db->order_by('products.sales', 'desc');
            }
        }

        if ( $filters['page'] )
        {
            $p_config['base_url'] = preg_replace("/\\d*$/", '', filter_url());
            $p_config['per_page'] = 20;

            $offset = ($filters['page'] - 1) * $p_config['per_page'];            
        }

        // Building pagination
        $this->get_for_shop_query_cache();
        if (isset($_SESSION['es_user']['id']))
        {
            $this->db->group_start()
                        ->where("design_status=1 OR user_id={$_SESSION['es_user']['id']}")
                     ->group_end();
        }
        else
        {
            $this->db->where("design_status=1");
        }
        
        $products = $this->get_all();

        $p_config['total_rows'] = count($products);
        $this->pagination->initialize($p_config);
        $pagination = $this->pagination->create_links();

        // Getting actual Record
        $products = array_slice($products, $offset, $p_config['per_page']);

        foreach ($products as $key => &$product) 
        {
            $product->user = $this->user_m->get($product->user_id);

            $product->design = $this->product_design_m->get_by(array('product_id'=>$product->id));

            $product->design->url = site_url("uploads/designs/{$product->id}/{$product->design->design_view_id}/display.png");

            if ($filters['section'] != 'all' OR $filters['style'] != 'all')
            {
                $product->product_style = $this->product_style_m->get_by(array( 'product_id'=> $product->id,
                                                                                'style_id' => $product->style_id ));
            }
            else
            {
                $product->product_style = $this->product_style_m->get_by(array('product_id'=>$product->id));
            }
            

            $product->t_mockup = $this->transparent_mockup_m->get_by(array('style_id' => $product->product_style->style_id,
                                                                'design_view_id' => $product->design->design_view_id));

            if ($filters['color'] != 'all')
            {
                $product->default_color = $this->style_color_m
                                                ->get_by('style_color_name', $filters['color']);
            }
            else
            {
                $product->default_color = $this->style_color_m
                                                ->get_default_color($product->id, $product->product_style->style_id);
            }
            
        }

        return array(
            'products' => $products,
            'pagination' => $pagination
        );
    }

    public function get_for_shop_query_cache()
    {
        $this->db->select('product_styles.style_id');
        $this->db->join('product_styles', 'product_styles.product_id = products.id');
        $this->db->join('api_99prints_styles', 'api_99prints_styles.id = product_styles.style_id');
        $this->db->join('api_99prints_sections', 'api_99prints_sections.id = api_99prints_styles.section_id');

        $this->db->join('products_categories', 'products_categories.product_id = products.id');
        $this->db->join('product_categories', 'product_categories.id = products_categories.product_category_id');

        $this->db->join('product_style_colors', 'product_style_colors.product_id = products.id');
        $this->db->join('api_99prints_style_colors', 'api_99prints_style_colors.id = product_style_colors.color_id');

        $this->db->join('products_interests', 'products_interests.product_id = products.id');
        $this->db->join('interests', 'interests.id = products_interests.interest_id');

        $this->db->join('users', 'users.id = products.user_id');

        $this->db->select('products.*');
        $this->db->group_by('products.id');        
    }

    /**
     * format data for best selling products
     */
    public function bs_products()
    {
        $this->db->limit(5);
        $this->db->order_by('products.sales', 'desc');
        $this->db->where('design_status', 1);
        
        return $this->style_1();
    }

    /**
     * format data for recently designs
     */
    public function rs_products()
    {
        $this->db->order_by('products.id', 'desc');
        $this->db->limit(5);
        $this->db->where('design_status', 1);
        
        return $this->style_1();
    }

    /**
     * Get the artist products and then format them accordingly
     */
    public function artist_products($user_id)
    {
        $this->db->limit(5);
        $this->db->where('user_id', $user_id);
        $this->db->where('design_status', 1);

        return $this->style_1();
    }

    public function category_products($category_ids)
    {
        $this->db->limit(5);
        $this->db->select('products.*');
        $this->db->join('products_categories', 'products_categories.product_id = products.id');
        $this->db->where_in('product_category_id', $category_ids);
        $this->db->where('design_status', 1);

        return $this->style_1();
    }

    /**
     * format data for styles_1
     */
    public function style_1()
    {
        $products = $this->get_all();

        foreach ($products as $key => &$product) 
        {
            $product->user = $this->user_m->get($product->user_id)->first_name;

            $product->design = $this->product_design_m->get_by(array('product_id'=>$product->id));

            $product->design->url = site_url("uploads/designs/{$product->id}/{$product->design->design_view_id}/display.png");

            $product->design->actual_185_url = site_url("uploads/designs/{$product->id}/{$product->design->design_view_id}/actual-185.png");

            $product->product_style = $this->product_style_m->get_by(array('product_id'=>$product->id));            

            $product->t_mockup = $this->transparent_mockup_m->get_by(array('style_id' => $product->product_style->style_id,
                                                                'design_view_id' => $product->design->design_view_id));

            $product->default_color = $this->style_color_m
                                                ->get_default_color($product->id, $product->product_style->style_id);

        }

        return $products;        
    }

    /**
     * format data for lastest designs
     */
    public function ld_products()
    {
        $this->db->order_by('products.id', 'desc');
        $this->db->limit(4);
        $this->db->where('design_status', 1);

        return $this->style_1();
    }

    /**
     * format for trending artist
     */
    public function ta_products()
    {
        $this->db->limit(6);

        $this->db->join('users', 'users.id = products.user_id');
        $this->db->group_by('users.id');
        $this->db->where('design_status', 1);

        $products = $this->get_all();
        
        foreach ($products as $key => &$product) 
        {
            $product->user = $this->user_m->get($product->user_id);
            $product->user->profile_pic_url = $this->user_m->profile_pic_url($product->user);
        }

        return $products;                
    }

    /**
     * format data for designer/dashboard
     */
    public function get_for_designer($user_id)
    {
        $this->db->order_by('products.id', 'desc');
        if (isset($_SESSION['es_user']['id']))
        {
            $this->db->group_start()
                        ->where("design_status=1 OR user_id={$_SESSION['es_user']['id']}")
                     ->group_end();
        }
        else
        {
            $this->db->where("design_status=1");
        }
        $products = $this->get_many_by('user_id', $user_id);
        foreach ($products as &$product)
        {
            $product->design = $this->product_design_m->get_by(array('product_id'=>$product->id));
            $product->design->url = site_url("uploads/designs/{$product->id}/{$product->design->design_view_id}/actual-185.png");

            $product->product_style = $this->product_style_m->get_by(array('product_id'=>$product->id));
            $product->default_color = $this->style_color_m
                                            ->get_default_color($product->id, $product->product_style->style_id);

        }
        return $products;
    }

    /**
     * format data for product update
     */
    public function get_for_update($product_id)
    {
        $product = $this->get($product_id);

        $product->designs = $this->product_design_m->get_many_by('product_id', $product_id);

        foreach ($product->designs as &$design)
        {
            $design->fabric_json = file_get_contents(APPPATH . "uploads/designs/{$product->id}/{$design->design_view_id}/fabric.json");
        }

        $this->product_style_m->in_sequence();
        $product->product_styles = $this->product_style_m->get_many_by('product_id', $product->id);

        $product->margins = array();
        foreach ($product->product_styles as $p_style)
        {
            $p_style->product_style_colors = $this->product_style_color_m
                                                    ->get_many_by(array( 'product_id' => $p_style->product_id,
                                                                         'style_id' => $p_style->style_id ));

            $product->margins[$p_style->style_id] = $p_style->margin;
        }

        $this->db->join('interests', 'interests.id = products_interests.interest_id');
        $interests = $this->products_interest_m->get_many_by('product_id', $product->id);

        $product->interests = '';
        foreach ($interests as $interest)
        {
            $product->interests .= $interest->interest . ',';
        }
        $product->interests = preg_replace('/,$/', '', $product->interests);

        $categories = $this->products_category_m->get_many_by('product_id', $product->id);

        $product->categories = array();
        foreach ($categories as $category)
        {
            $product->categories[] = $category->product_category_id;
        }

        return $product;
    }

    /* --------------------------------------------------------------
     * DELETE METHODS
     * ------------------------------------------------------------ */  

    /**
     * OBSERVER: called before delete and removes all the related db enteries
     * and files
     */
    public function delete_related($product_id)
    {
        // Delete products categories
        $this->products_category_m->delete_by(array('product_id' => $product_id));

        // Delete products interests
        $this->products_interest_m->delete_by(array('product_id' => $product_id));

        // Delete product style colors
        $this->product_style_color_m->delete_by(array('product_id' => $product_id));

        // Delete product default color
        $this->product_default_color_m->delete_by(array('product_id' => $product_id));

        // NOTE: Can't be deleted as registered with cart and order
        // TODO: make it robust
        // Delete product style margin
        $this->product_style_m->delete_by(array('product_id' => $product_id));

        // Delete product designs
        $this->product_design_m->delete_by(array('product_id' => $product_id));

        // Remove Images
        rrmdir(APPPATH . '../uploads/designs/' . $product_id);
    }

    /**
     * OBSERVER: called before get and fetch only successfully uploaded products
     */
    public function fetch_successfully_uploaded()
    {
        $this->db->where('upload_status', 1);
    }
}
