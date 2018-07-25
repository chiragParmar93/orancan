<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authe Class
 *
 * An authentication controller class
 */
class Authe extends MY_Controller {	

	/**
	 * Show register form
	 */
          public function __construct() {
            parent::__construct();        
                $this->load->model('Authe_m');         
              }
	public function register()
	{
		// trick for using set_value()
		$_POST = $this->session->flashdata('form_fields');
		$this->template
				->set_partial('alerts', 'alerts/alerts', array('alerts_array' => $this->session->flashdata('alerts_array')))
				->build('authe/form_register');
	}

	/**
	 * Process post request at register
	 */
	public function register_post()
	{
		// Try to insert user
		$user_id = $this->authe_m->insert($_POST);

		if ( ! $user_id) 
		{
			$this->_retry('authe/register');
		}
		else
		{
			// Add activation
			$user = $this->authe_m->add_activation_for($user_id);

			// Add authorization
			$this->autho_group_m->add_autho_group_for($user_id, 'user');

			// Send email
			$this->email->from($this->config->item('site_email'), $this->config->item('site_name'));
			$this->email->to($user->email);

			$this->email->subject('Account Activation');

			$link = site_url('authe/activate') . '/' . $user->activation_code;
			$message = $this->load->view('authe/email_activation', array('link'=>$link), TRUE);
			$this->email->message($message);

			if ($this->email->send())
			{
				echo 'Send Successful';
			}
			else
			{
				echo $this->email->print_debugger();
			}
		}
	}

	/**
	 * Process AJAX post request at register
	 * TODO: email NOT send condition
	 */
	public function register_ajax_post()
	{
		// Try to insert user
		$user_id = $this->authe_m->insert($_POST);

		if ( ! $user_id)
		{
			$this->_retry_ajax();
		}
		else
		{
			// Add activation
			$user = $this->authe_m->add_activation_for($user_id);

			// Add authorization
			$this->users_autho_group_m->add_autho_group_for($user_id, 'user');

			// Send email
			$this->email->from($this->config->item('site_email'), $this->config->item('site_name'));
			$this->email->to($user->email);

			$this->email->subject('ElbutStan.com Account Activation');

			$link = site_url('authe/activate') . '/' . $user->activation_code;
			$message = $this->load->view('authe/email_activation', array('link'=>$link), TRUE);
			$this->email->message($message);

			$this->email->send();
			
			$data['status'] = 'success';
			$data['alerts'] = $this->load->view('alerts/success', 
												array('alert' => 'Please check your email for activating your account'), TRUE);
			echo json_encode($data);
		}
	}


	/**
	 * Process activation of account
	 */
	public function activate($code)
	{

		$status = $this->authe_m->activate($code);

		if ( ! $status) 
		{
			$this->authe_m->set_alert('error', 'Activation code is not authentic');
			$this->_retry('page/alerts');
		}
		else
		{
			$msg = '<h3>Your Account successfully activated</h3>
					<p>Please Login with your credentials to continue from Guest dropdown!</p>
					';
			$this->authe_m->set_alert('success', $msg);
			$this->_retry('page/alerts');
		}
	}

	/**
	 * Display Login form
	 */
	public function login()
	{
            
		// trick for using set_value()
		$_POST = $this->session->flashdata('form_fields');
		$this->template
				->set_partial('alerts', 'alerts/alerts', array('alerts_array' => $this->session->flashdata('alerts_array')))
				->build('authe/form_login');
	}

	/**
	 * Process post request at login
	 */
	public function login_post()
	{
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');	

		if ( ! $this->form_validation->run())
		{
			$this->_retry('authe/login');
		}

		if ( ! $this->authe_m->login( $this->input->post('email'), $this->input->post('password')) )
		{
			$this->_retry('authe/login');
		}

		// You finally reached here means user is authentic and his authe session is set
		redirect('user/dashboard','refresh');
	}

	/**
	 * Process post request at login
	 */
	public function login_ajax_post()
	{   
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');	
                $this->load->model('Authe_m');
		if ( ! $this->form_validation->run() || ! $this->Authe_m->login( $_POST['email'], $_POST['password']) )
		{
			$this->_retry_ajax();
		}
		else
		{
			// You finally reached here means user is authentic and his authe session is set
			$data['status'] = 'success';
			echo json_encode($data);
		}
	}


	/**
	 * Process login via social account
	 */
	public function login_social($provider = NULL)
	{
		if ( ! in_array($provider, array('facebook, google, twitter'))) 
		{
			//show_404();
		}

		try
		{
			// include HybridAuth library
			$this->config->load('hybridauth');
		$this->load->library('HybridAuthLib');
				// initialize Hybrid_Auth class with the config file
		 	// try to authenticate with the selected provider
			
         	if ($this->hybridauthlib->providerEnabled($provider))
			{
		
$adapter = $this->hybridauthlib->authenticate($provider);

			// then grab the user profile
		$user_profile = $adapter->getUserProfile();
	//print_r($user_profile);
			// login via social media and set his session
				$this->authe_m->login_social($provider, $user_profile);

			redirect('user/dashboard','refresh');
			}
			else // This service is not enabled.
			{
				log_message('error', 'controllers.HAuth.login: This provider is not enabled ('.$provider.')');
				show_404($_SERVER['REQUEST_URI']);
			}
	
		}
		catch( Exception $e )
		{
			// something went wrong?
			if (ENVIRONMENT == 'development')
			{
				// $this->authe_m->set_alert('error', var_dump($e));
				// $this->_retry('authe/login');
			}
			else
			{
				 $this->authe_m->set_alert('error', 'Something went wrong with social login! Please try again.');
				 redirect(site_url(), 'refresh');
			}
			
		}

		redirect(site_url(), 'refresh');
		
	}

	/**
	 * used by hybrid auth
	 */
	public function login_social_base()
	{
       	
		include( APPPATH . '../vendor/hybridauth/hybridauth/hybridauth/index.php' );
	}

	/**
	 * Logout the user
	 */
	public function logout()
	{
		session_destroy();
		redirect(site_url(), 'refresh');
	}

	/**
	 * Display forgot password form
	 */
	public function forgot_password()
	{
		$this->template->append_js('/assets/js/someting.js');
		// trick for using set_value()
		$_POST = $this->session->flashdata('form_fields');
		$this->template
				->append_metadata('<script src="/js/jquery.flot.js"></script>')
				->set_partial('alerts', 'alerts/alerts', array('alerts_array' => $this->session->flashdata('alerts_array')))
				->build('authe/form_forgot_password');
	}

	/**
	 * Process post request at forgot_password
	 */
	public function forgot_password_post()
	{
		$email = $this->input->post('email');

		$user = $this->authe_m->get_by('email', $email);

		if ( ! count($user))
		{
			// Set error
			$this->authe_m->set_alert('error', 'Not yet registered!');

			$this->_retry('authe/forgot-password');
		}

		// Add forgot password
		$user = $this->authe_m->add_forgot_password_code_for($user->id);

		// Send email
		$this->email->from($this->config->item('site_email'), $this->config->item('site_name'));
		$this->email->to($user->email);

		$this->email->subject('Forgot Password Code');

		$link = site_url('authe/reset-password-verify') . '/' . $user->forgot_password_code;
		$message = $this->load->view('authe/email_forgot_password', array('link'=>$link), TRUE);
		$this->email->message($message);

		if ($this->email->send())
		{
			$this->authe_m->set_alert('success', 'Please check your inbox for resetting password');
			
			$this->_redirect('authe/forgot-password');
		}
		else
		{
			// debugging purpose
			if (ENVIRONMENT == 'development')
			{
				$this->authe_m->set_alert('error', $this->email->print_debugger());
			}
			$this->authe_m->set_alert('error', 'There is some problem in sending email. Please try again!');
			$this->_redirect('authe/forgot-password','refresh');
		}
	}

	/**
	 * Process AJAX post request at forgot_password
	 */
	public function forgot_password_ajax_post()
	{
		$email = $this->input->post('email');

		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');

		if ( ! $this->form_validation->run() ) {
			$this->_retry_ajax();
			return;
		}


		$user = $this->authe_m->get_by('email', $email);

		if ( ! count($user))
		{
			// Set error
			$this->authe_m->set_alert('error', 'Not yet registered!');

			$this->_retry_ajax();
			return;
		}

		// Add forgot password
		$user = $this->authe_m->add_forgot_password_code_for($user->id);

		// Send email
		$this->email->from($this->config->item('site_email'), $this->config->item('site_name'));
		$this->email->to($user->email);

		$this->email->subject('Forgot Password Code');

		$link = site_url('authe/reset-password-verify') . '/' . $user->forgot_password_code;
		$message = $this->load->view('authe/email_forgot_password', array('link'=>$link), TRUE);
		$this->email->message($message);

		$this->email->send();
				
		$data = [];
		$data['status'] = 'success';
		$data['alerts'] = $this->load->view('alerts/success', array('alert' => 'Please check your inbox for resetting password'), true) ;

		echo json_encode($data);
	}

	/**
	 * Process reset password
	 */
	public function reset_password_verify($code = NULL)
	{
		// Display 404
		if ( ! $code) show_404();

		$user = $this->authe_m->user_from_forgot_password($code);

		if ( ! $user)
		{
			$this->_retry('authe/forgot-password');
		}

		// Set rp_user_id in session and
		// this will be deleted as soon as 
		// user has changed his password
		$_SESSION['rp_user_id'] = $user->id;

		// redirect to reset password form
		redirect('authe/reset-password','refresh');
	}

	/**
	 * Display reset password form
	 */
	public function reset_password()
	{
		$this->template
				->set_partial('alerts', 'alerts/alerts', array('alerts_array' => $this->session->flashdata('alerts_array')))
				->build('authe/form_reset_password');
	}

	/**
	 * Process post request to reset_password
	 */
	public function reset_password_post()
	{
		$this->form_validation->set_rules('password', 'Password', 'required');
		$this->form_validation->set_rules('confirm-password', 'Confirm Password', 'required|matches[password]');

		if ( ! $this->form_validation->run())
		{
			$this->_retry('authe/reset-password');
		}

		$this->authe_m->reset_password();

		// no more use
		unset($_SESSION['rp_user_id']);
		
		$this->_redirect('authe/reset-password');
	}


	/**
	 * Process post request to change-password
	 * used by user controller
	 *
	 * TODO: make it robust for redirect
	 */
	public function change_password_post()
	{
		$this->form_validation->set_rules('prev_password', 'Previous Password', 'required');
		$this->form_validation->set_rules('new_password', 'Password', 'required');
		$this->form_validation->set_rules('conf_password', 'Confirm Password', 'required|matches[new_password]');

		if ( ! $this->form_validation->run() OR ! $this->authe_m->change_password())
		{
			$this->_retry('user/settings/change-password');
		}

		$this->authe_m->set_alert('success', 'Your password successfully changed!');
		$this->_redirect('user/settings/change-password');
	}

}