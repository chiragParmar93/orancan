<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A model for Authe
 */
class Authe_m extends MY_Model {

    /**
     * For now setting it to `users` 
     */
    protected $_table = 'users';

    /**
     * activating observers
     */
    public $before_create = array( 'created_at', 'updated_at', 'password_hash_store' );
    public $before_update = array( 'updated_at', 'password_hash_store' );

	/**
	 * validations for `users`
	 * @var array
	 */
	public $validate = array(
        array( 'field'  => 'email', 
               'label'  => 'email',
               'rules'  => 'required|valid_email|is_unique[users.email]',
               'errors' =>  array( 'is_unique' => 'Provided email has been already registered!' ) ),

        array( 'field'  => 'password',
               'label'  => 'password',
               'rules'  => 'required' ),

        array( 'field'  => 'first_name',
               'label'  => 'First name',
               'rules'  => 'required' ),

        array( 'field'  => 'last_name',
               'label'  => 'Last name',
               'rules'  => 'required' )
    );

    /* --------------------------------------------------------------
     * Account Activation
     * ------------------------------------------------------------ */

    /**
     * add activation code to user
     */
    public function add_activation_for($user_id)
    {
        $code = $this->_rand_string_with_id($user_id);

        $data['activation_code'] = $code;
        $data['activation_status'] = 0;

        $this->update($user_id, $data, TRUE);

        return $this->get($user_id);
    }

    /**
     * IF valid activation code THEN activate the account
     */
    public function activate($code)
    {
        // extract $user_id
        $user_id = $this->_extract_id_from_code($code);

        $user = $this->get_by(array(
                'id' => $user_id,
                'activation_code' => $code,
                ));

        if (count($user))
        {
            $data['activation_code'] = NULL;
            $data['activation_status'] = 1;
            $this->update($user_id, $data, TRUE);

            // Send email to admin
            $this->email->from($this->config->item('site_email'), $this->config->item('site_name'));
            $this->email->to($this->config->item('site_email'));

            $this->email->subject('ElbutStan.com User registered');

            $message = "
                <h1>New user registered</h1>
                <p>Name: {$user->first_name} {$user->last_name}</p>
                <p>Email: {$user->email} </p>
                <p>Via: Email</p>
            ";
            $this->email->message($message);

            $this->email->send();

            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /* --------------------------------------------------------------
     * Resetting Password
     * ------------------------------------------------------------ */

    /**
     * add forgot password code to user
     */
    public function add_forgot_password_code_for($user_id)
    {
        $code = $this->_rand_string_with_id($user_id);

        $data['forgot_password_code'] = $code;
        $data['forgot_password_time'] = time() + (60 * 60 * 24);

        $this->update($user_id, $data, TRUE);

        return $this->get($user_id);
    }

    /**
     * check forgot password
     */
    public function user_from_forgot_password($code)
    {
        $user_id = $this->_extract_id_from_code($code);

        $user = $this->get_by(array(
                'id' => $user_id,
                'forgot_password_code' => $code
                ));

        if ( ! $user)
        {
            $this->set_alert('error', 'Reset Code is not valid!');
            return FALSE;
        }

        if ( $user->forgot_password_time < time() )
        {
            $this->authe_m->set_alert('error', 'Reset Password code expired!');
            return FALSE;
        }

        return $user;
    }

    /**
     * reset the forgotten password
     */
    public function reset_password()
    {
        // get the user_id from session
        $user_id = $_SESSION['rp_user_id'];

        $data['password'] = $this->input->post('password');
        $data['forgot_password_code'] = NULL;
        $data['forgot_password_time'] = NULL;

        $this->set_alert('success', 'Successfully changed. Please Login to continue');

        return $this->update($user_id, $data, TRUE);
    }

    /* --------------------------------------------------------------
     * CHANGING PASSWORD
     * ------------------------------------------------------------ */

    /**
     * this action can be preformed ONLY by logeedin user
     * change password
     */
    public function change_password()
    {
        $prev_pass = $this->input->post('prev_password');
        $new_pass = $this->input->post('new_password');

        $user_id = $_SESSION['es_user']['id'];

        $user = $this->get($user_id);

        if ( ! $this->password_verify($prev_pass, $user->password))
        {
            $this->set_alert('error', 'Your previous password doesnot match');
            return FALSE;
        }

        $data['password'] = $new_pass;

        return $this->update($user_id, $data, TRUE);
    }

    /* --------------------------------------------------------------
     * LOGIN
     * ------------------------------------------------------------ */

    /**
     * Try to login
     */
    public function login($email, $password)
    {
        $user = $this->authe_m->get_by(array(
                'email' => $email
                ));

        if ( ! count($user))
        {
            $this->set_alert('error', 'Not yet registered!');
            return FALSE;
        }

        if ( ! $user->activation_status)
        {
            $this->set_alert('error', 'Not yet activated!');
            return FALSE;
        }

        if ( ! $this->password_verify($password, $user->password))
        {
            $this->set_alert('error', 'Email/Password mismatch!');
            return FALSE;
        }        

        // setting user session
        $this->_set_user_session($user);

        // setting success alert
        $this->set_alert('success', 'Successfully logged in!');
        
        return TRUE;
    }

    /**
     * create a new user if not already present
     * then set it's session
     */
    public function login_social($provider, $user_profile)
    {
        $user = $this->get_by(array(
                'hybridauth_provider_name' => $provider,
                'hybridauth_provider_uid' => $user_profile->identifier
                ));

        if ( ! count($user))
        {
            // lets insert a new user
            $data['email'] = $user_profile->email;

            // Exception for fb
            if ( ! $user_profile->firstName)
            {
                $arr = explode(' ', $user_profile->displayName);

                $data['first_name'] = array_shift($arr);
                $data['last_name'] = array_pop($arr);
            }
            else
            {
                $data['first_name'] = $user_profile->firstName;
                $data['last_name'] = $user_profile->lastName;
            }
            
            $data['profile_pic'] = $user_profile->photoURL;
            $data['hybridauth_provider_name'] = $provider;
            $data['hybridauth_provider_uid'] = $user_profile->identifier;
            $data['activation_status'] = 1;

            $user_id = $this->insert($data, TRUE);

            $user = $this->get($user_id);

            // let's add authorization too
            $this->autho_m->add_autho_group_for($user->id, 'user');

            // Send email to admin
            $this->email->from($this->config->item('site_email'), $this->config->item('site_name'));
            $this->email->to($this->config->item('site_email'));

            $this->email->subject('ElbutStan.com User registered');

            $message = "
                <h1>New user registered</h1>
                <p>Name: {$user->first_name} {$user->last_name}</p>
                <p>Email: {$user->email} </p>
                <p>Via: {$provider}</p>
            ";
            $this->email->message($message);

            $this->email->send();

        }

        // setting user session
        $this->_set_user_session($user);
    }

    /**
     * create a new user if not already present
     * then set it's session
     */
    public function login_guest()
    {
        // Retrieve the user ip
        $ip = $this->input->ip_address();

        $user = $this->get_by('ip_address', $ip);

        if ( ! count($user))
        {
            // Lets insert a new user
            $data['ip_address'] = $ip;
            $data['first_name'] = 'Guest';

            $user_id = $this->insert($data, TRUE);

            $user = $this->get($user_id);

            // Let's add authorization too
            $this->autho_m->add_autho_group_for($user->id, 'guest');
        }

        // setting user session
        $this->_set_user_session($user);
    }

    /**
     * Set the user session
     */
    private function _set_user_session($user)
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

        $user = array (
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'profile_pic' => $profile_pic,
            'autho_groups' => $this->autho_group_m->autho_for_user($user->id)
        );
        $session_data = array(
            'es_user' => $user
        );

        $this->session->set_userdata($session_data);
    }

    /**
     * update user session
     */
    public function update_session()
    {
        $user = $this->get($_SESSION['es_user']['id']);
        $this->_set_user_session($user);
    }


    /* --------------------------------------------------------------
     * OBSERVERS
     * ------------------------------------------------------------ */

    /**
     * Hash the password before storing it to database
     */
    public function password_hash_store($row)
    {
        if (isset($row['password']))
        {
            // Called at time of storing
            $row['password'] = $this->password_hash($row['password']);
        }

        return $row;
    }

    /* --------------------------------------------------------------
     * Helper methods
     * ------------------------------------------------------------ */

    public function password_hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function password_verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * generate 60 characters random string with id
     * used for activation and forgot password
     */
    private function _rand_string_with_id($id)
    {
        $rand_len = 60 - strlen($id) - 1;
        $rand = random_string('alnum', $rand_len);

        // prepend random string to $id with '-'
        return $rand . '-' . $id;
    }

    private function _extract_id_from_code($code)
    {
        return substr($code, strpos($code, '-') + 1);
    }

}

/* End of file Authe_m.php */
/* Location: ./application/models/Authe_m.php */