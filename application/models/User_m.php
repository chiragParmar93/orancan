<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A model for User
 */
class User_m extends MY_Model {

    /* --------------------------------------------------------------
     * PROFILE METHODS
     * ------------------------------------------------------------ */

    /**
     * Get the data formatted correctly for nav_sub_profile
     */
    public function get_nav_sub_profile($user_id)
    {
    	$user = $this->get($user_id);

    	// Formatting Data
    	$user->location = $user->city;
    	$user->country = $this->country_m->get($user->country_id);
    	if ($user->country) {
    		$user->location .= ', ' . $user->country->name;
    	}

    	$user->interests = $this->user_interest_m->user_interests($user_id);
    	
    	return $user;
    }

    /**
     * build the profile pic url for the user
     * @return [type] [description]
     */
    public function profile_pic_url($user)
    {
        // Set Avatar if NOT set profile pic yet
        if ( ! $user->profile_pic)
        {
            $profile_pic = site_url('uploads/profile_pics/avatar.jpg');
        }
        else if ( strpos($user->profile_pic, 'http') === 0 )
        {
            // profile from hybrid auth
            $profile_pic = $user->profile_pic;
        }
        else 
        {
            // have uploaded at ES
            $profile_pic = site_url('uploads/profile_pics/' . $user->profile_pic);
        }

        return $profile_pic;
    }

	/**
	 * try to save the profile pic by uploading first
	 * and then saving it to db
	 */
	public function save_profile_pic()
	{
		$user_id = $_SESSION['es_user']['id'];

		$profile_pic = $this->upload_profile_pic($user_id, 'profile_pic');

		if ( ! $profile_pic)
		{
			return FALSE;
		}
		else
		{
			// Update session
			$_SESSION['es_user']['profile_pic'] = site_url('uploads/profile_pics/' . $profile_pic);
			$this->set_alert('success', 'Successfully changed');
			$this->update($user_id, array('profile_pic' => $profile_pic));
			return TRUE;
		}
	}

	/**
	 * try to upload the profile pic
	 * Returns uploaded profile pic name
	 */
	public function upload_profile_pic($user_id, $field_name)
	{
		$upload_dir = APPPATH . "uploads/profile_pics";
		
		$file_type = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);

		$profile_pic_name = "{$user_id}.{$file_type}";

		$profile_pic = $upload_dir . '/' . $profile_pic_name;

		// Check if image file is a actual image or fake image
	    if( ! getimagesize($_FILES[$field_name]['tmp_name'])) 
	    {
	    	$this->set_alert('error', 'NOT image');
	        return FALSE;
	    }
		
		// Check file size
		if ($_FILES[$field_name]["size"] > 500000) 
		{
			$this->set_alert('error', 'File size too big');
		    return FALSE;
		}
		
		// Allow certain file formats
		if($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg"
		&& $file_type != "gif" ) 
		{
			$this->set_alert('error', "File format : {$file_type} not supported");
		    return FALSE;
		}
		
		// Check if file already exists
		if (file_exists($profile_pic)) 
		{
		    unlink($profile_pic);
		}
		
	    if ( move_uploaded_file($_FILES[$field_name]["tmp_name"], $profile_pic) )
	    {
	    	xl\image\resize_image($profile_pic, $profile_pic, 200, 200);
	    	return $profile_pic_name;
	    }
	    else
	    {
	    	return FALSE;
	    }
	}

}
