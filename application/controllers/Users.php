<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->library('form_validation'); 
        $this->load->helper('form');
    }

    public function index(){ 
    }
    
    public function register(){
        $data['username'] = $this->input->post('username');
        $data['email'] = $this->input->post('email');
        $data['password'] = $this->input->post('password');

        $hashed_password = 

        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        
        if($this->form_validation->run()){
            $username_check = $this->db->like('username', (string)$data['username'])->from('users')->count_all_results();
            $email_check = $this->db->like('email', (string)$data['email'])->from('users')->count_all_results();
            
            if($username_check==0 && $email_check==0){
                echo "Thank you for Creating Account, you can Login Now";
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $this->db->insert('users', $data);
            }else if($username_check>0){
                echo "Username Already exist";
                $this->load->view('register_page', $data);
            }else if($email_check>0){
                echo "Email Already Registered";
                $this->load->view('register_page', $data);
            }
        }else{
            $this->load->view('register_page', $data);
        }
    }
    
    public function login(){
        $data['email'] = $this->input->post('email');
        $data['password'] = $this->input->post('password');
        
        
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        
        if($this->form_validation->run()){
            $email_check = $this->db->where('email', (string)$data['email'])->from('users')->count_all_results();

            $this->db->select('password');
            $this->db->from('users'); // Replace 'your_table_name' with your table name
            $this->db->where('email', (string)$data['email']);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                // Email exists, get the password
                $row = $query->row();
                $password = $row->password;

                if(password_verify((string)$data['password'], $password)){
                    echo 'Login Successfull';
                }else{
                    echo 'Incorrect Email or Password';
                }
                // Now $password holds the password corresponding to the entered email
            }else{
                echo "Email id not Registered";
                $this->load->view('login_page', $data);
            }

        }else{
            $this->load->view('login_page', $data);
        }
    }
}