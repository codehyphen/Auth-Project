<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('Api');
        $this->load->helper('url');
    }


    public function register()
    {
        $data['username'] = $this->input->post('username');
        $data['email'] = $this->input->post('email');
        $data['password'] = $this->input->post('password');


        $this->form_validation->set_rules('recorded...yet
        Set Goal to find your learning path and track your skill progres', 'Username', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run()) {
            $status = $this->Api->register($data);
            if ($status == 1) {
                echo "Thank you for Creating Account, you can Login Now";
            } else if ($status == 2) {
                $data['error'] = "Username Not available";
                $this->load->view('register_page', $data);
            } else {
                $data['error'] = "Email Already Registered";
                $this->load->view('register_page', $data);
            }
        } else {
            $this->load->view('register_page', $data);
        }
    }

    public function login()
    {

        if ($this->session->userdata('user_data') || time() < $this->session->userdata('session_expire')) {
            header("Location: /AuthProject/Dashboard"); // Redirect to login page if session is expired or user is not logged in
        }

        $data['emailorUsername'] = $this->input->post('emailorUsername');
        $data['password'] = $this->input->post('password');


        $this->form_validation->set_rules('emailorUsername', 'Email or Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run()) {
            $status = $this->Api->login($data);

            if ($status == 1) {
                $user_data = array(
                    'emailorUsername' => $data['emailorUsername'],
                );
                $this->session->set_userdata('user_data', $user_data);
                // Set session expiration time
                $this->session->set_userdata('session_expire', time() + 600);
                header("Location: /AuthProject/Dashboard");
            } else if ($status == 2) {
                $data['error'] = 'Invalid Credentials';
                $this->load->view('login_page', $data);
            } else if($status==3){
                $data['error'] = 'Login After 30 min';
                $this->load->view('login_page', $data);
            } else{
                $data['error'] = 'You dont have access to the page';
                $this->load->view('login_page', $data);
            }
        } else {
            $this->load->view('login_page', $data);
        }
    }

    public function resetpassword()
    {
        $data['email'] = $this->input->post('email');
        $data['password'] = $this->input->post('password');
        $data['confirm_password'] = $this->input->post('confirm_password');

        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim');


        if ($this->form_validation->run()) {
            $status = $this->Api->resetpassword($data);

            if ($status == 1) {
                echo 'Password changed Successfully';
            } else if ($status == 3) {
                $data['error'] = 'Email Id Not Exist';
                $this->load->view('forget_password', $data);
            }elseif($status==2){
                $data['error'] = 'The Entered Password and Confirm Password are not Same';
                $this->load->view('forget_password', $data);
            }
        } else {
            $this->load->view('forget_password', $data);
        } 
    }

    public function logout()
    {
        $this->Api->logout($this->session->userdata('user_data')['emailorUsername']);
        $this->session->sess_destroy();
        header("Location: /AuthProject/Users/Login");
    }
}
