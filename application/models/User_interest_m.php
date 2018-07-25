<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_interest_m extends MY_Model {

	public function user_interests($user_id)
	{
		$this->db->join('interests', 'interests.id = user_interests.interest_id');
    	$db_interests = $this->get_many_by('user_id', $user_id);
    	$interests = [];
    	if (count($db_interests))
    	{		
	    	foreach ($db_interests as $interest) 
	    	{
	    		$interests[] = $interest->interest;
	    	}
    	}
    	return $interests;
	}

	public function save($user_id)
	{
		// add interest
        $_POST['interests'] = explode(',', $_POST['interests']);
        $interest_ids = $this->interest_m->sync_multiple_for('interest', $_POST['interests']);
    
    	// Remove all the prev interest
    	$this->user_interest_m->delete_by('user_id', $user_id);

        // tag user with interest_ids
        foreach ($interest_ids as $interest_id)
        {
        	// Add new interest
            $this->user_interest_m->insert(array( 'user_id' => $user_id, 
                                                  'interest_id' => $interest_id ));
        }

        return TRUE;
	}

}
