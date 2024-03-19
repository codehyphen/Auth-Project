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


        $this->form_validation->set_rules('username', 'Username', 'required|trim');
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

        if (!$this->form_validation->run()) {
            return $this->load->view('login_page', $data);
        }
        
        $status = $this->Api->login($data);

        if ($status) {
            $data['error'] = $status;
            return $this->load->view('login_page', $data);
        }

        $user_data = array(
            'emailorUsername' => $data['emailorUsername'],
        );
        $this->session->set_userdata('user_data', $user_data);
        // Set session expiration time
        $this->session->set_userdata('session_expire', time() + 600);
        header("Location: /AuthProject/Dashboard");
    }

    public function resetpassword()
    {
        $data['email'] = $this->input->post('email');
        $data['password'] = $this->input->post('password');
        $data['confirm_password'] = $this->input->post('confirm_password');

        $this->form_validation->set_rules('email', 'Email', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim');


        if (!$this->form_validation->run()) {
            return $this->load->view('forget_password', $data);
        }

        $status = $this->Api->resetpassword($data);
        if ($status) {
            $data['error'] = $status;
            $this->load->view('forget_password', $data);
        } else {
            echo 'Password changed Successfully';
        }
    }

    public function logout()
    {
        $this->Api->logout($this->session->userdata('user_data')['emailorUsername']);
        $this->session->sess_destroy();
        header("Location: /AuthProject/Users/Login");
    }
}
