<?php

if(!defined('BASEPATH'))
    exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        // Load the rest client spark
        $this->load->spark('restclient/2.0.0');
        // Run some setup
        $this->rest->initialize(array('server' => 'http://api.tribble.local/'));
    }

    public function login($lb = NULL)        
    {                        
        $data['title'] = 'Tribble - Login';
        $data['meta_description'] = 'A design content sharing and discussion tool.';
        $data['meta_keywords'] = 'Tribble';
        $data['form_action'] = current_url();

        $this->form_validation->set_error_delimiters('<p class="help">', '</p>');

        if($this->form_validation->run('login') == false)
        {
            $this->load->view('common/page_top.php', $data);            
            $this->load->view('user/login.php', $data);
            $this->load->view('common/page_end.php', $data);
        } else 
        {

            $email = $this->input->post('email');
            $password = $this->input->post('password');

            if($result = $this->rest->post('auth/login',array('email'=> $email,'password'=>$password)))
            {
                if(!$result->request_status){
                    $data['error'] = $result->message;
                    $this->load->view('common/page_top.php', $data);
                    $this->load->view('user/login.php', $data);
                    $this->load->view('common/page_end.php', $data);
                } else 
                {
                    $user_data = $result->user[0];                
                    @$session_data = array('user_id'=>$user_data->user_id,'user_name'=>$user_data->user_name,'user_email'=>$user_data->user_email,'user_avatar'=>$user_data->user_avatar);           

                    if(!$session = $this->rest->put('auth/session',$session_data))
                        show_error(lang('error_api_connect'));

                    if(!$session->request_status)
                        show_error($session->message);                 
                          
                    $this->session->set_userdata(array('sid'=>$session->id));
                    $redirectUrl = site_url().string_to_uri($lb);
                    redirect($redirectUrl);}
                }

            }
        }
    
    
    public function logout($lb = NULL)
	{    
        $sid = $this->session->userdata('sid');
        $delete = $this->rest->delete('auth/session/',array('id'=>$sid));   
        $this->session->sess_destroy();
        $redirectUrl = site_url().string_to_uri($lb);    
        redirect($redirectUrl);
	}


}

?>