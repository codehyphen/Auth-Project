<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('form');
    }

    public function index()
    {
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
            $username_check = $this->db->like('username', (string)$data['username'])->from('users')->count_all_results();
            $email_check = $this->db->like('email', (string)$data['email'])->from('users')->count_all_results();

            if ($username_check == 0 && $email_check == 0) {
                echo "Thank you for Creating Account, you can Login Now";
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $this->db->insert('users', $data);
            } else if ($username_check > 0) {
                echo "Username Already exist";
                $this->load->view('register_page', $data);
            } else if ($email_check > 0) {
                echo "Email Already Registered";
                $this->load->view('register_page', $data);
            }
        } else {
            $this->load->view('register_page', $data);
        }
    }

    public function login()
    {
        $data['emailorUsername'] = $this->input->post('emailorUsername');
        $data['password'] = $this->input->post('password');

        $is_userLogin_blocked = false;


        $this->form_validation->set_rules('emailorUsername', 'Email or Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run()) {
            $email_check = $this->db->where('email', (string)$data['emailorUsername'])->from('users')->count_all_results();
            $username_check = $this->db->where('username', (string)$data['emailorUsername'])->from('users')->count_all_results();



            // $this->db->select('password');
            // $this->db->from('users'); 
            // $this->db->where('email', (string)$data['email']);
            // $query = $this->db->get();

            // if ($query->num_rows() > 0) {
            //     $row = $query->row();
            //     $password = $row->password;

            //     if(password_verify((string)$data['password'], $password)){
            //         echo 'Login Successfull';
            //     }else{
            //         echo 'Incorrect Email or Password';
            //     }
            // }else{
            //     echo "Email id not Registered";
            //     $this->load->view('login_page', $data);
            // }

            if ($email_check > 0) {

                $query = $this->db->select('password')->from('users')->where('email', (string)$data['emailorUsername'])->get();
                $row = $query->row();
                $password = $row->password;

                // $failed_attempts = $this->db->select('failed_attempts')->where('email', (string)$data['emailorUsername'])->get();

                $blockedStatus = $this->checkBlockedStatus((string)$data['emailorUsername'], null);

                if ($blockedStatus) {
                    $data['error'] = 'Login After 30 min';
                    $this->load->view('login_page', $data);
                    return;
                }


                if (password_verify((string)$data['password'], $password)) {
                    // check if the failed attemps are greator than 3 and last_failed_attempt is more than 30 min.

                    // After Successful login Reset failed attemps to 0
                    $this->db->set('failed_attempts', '0', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');
                    echo 'Login Successfull via email';
                } else {
                    $data['error'] = 'Invalid Credentials';
                    $this->db->set('failed_attempts', 'failed_attempts+1', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');
                    $this->db->set('last_failed_attempt', 'NOW()', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');
                    $this->load->view('login_page', $data);
                }
            } else if ($username_check > 0) {
                $query = $this->db->select('password')->from('users')->where('username', (string)$data['emailorUsername'])->get();
                $row = $query->row();
                $password = $row->password;

                $blockedStatus = $this->checkBlockedStatus(null, (string)$data['emailorUsername']);

                if ($blockedStatus) {
                    $data['error'] = 'Login After 30 min';
                    $this->load->view('login_page', $data);
                    return;
                }

                if (password_verify((string)$data['password'], $password)) {
                    echo 'Login Successfull via username';
                } else {
                    $data['error'] = 'Invalid Credentials';
                    $this->db->set('failed_attempts', 'failed_attempts+1', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');
                    $this->db->set('last_failed_attempt', 'NOW()', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');
                    $this->load->view('login_page', $data);
                }
            } else {
                $data['error'] = 'Invalid Credentials';
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
            // if email exist into database then only we can change the password
            $email_check = $this->db->where('email', (string)$data['email'])->from('users')->count_all_results();

            if ($email_check > 0) {
                if ($data['password'] != $data['confirm_password']) {
                    $data['error'] = 'The Entered Password and Confirm Password are not Same';
                    $this->load->view('forget_password', $data);
                } else {
                    // hash the password
                    $hash_password = password_hash($data['password'], PASSWORD_DEFAULT);


                    // Now change the password into the database

                    $this->db->set('password', $hash_password)->where('email', (string)$data['email'])->update('users');

                    echo 'Password changed Successfully';
                }
            } else {
                $data['error'] = 'Email Id Not Exist';
                $this->load->view('forget_password', $data);
            }
        } else {
            $this->load->view('forget_password', $data);
        }
    }

    private function checkBlockedStatus($email, $username)
    {
        $query1 = $this->db->select('last_failed_attempt')->from('users')->where(($email!=null ? 'email' : 'username'), ($email!=null ? $email : $username))->get();

        $failed_attempts = $this->db->select('failed_attempts')->from('users')->where($email!=null ? 'email' : 'username', $email!=null ? $email : $username)->get()->row()->failed_attempts;

        date_default_timezone_set('Asia/Kolkata');

        $row = $query1->row();
        $lastFailedAttempt = strtotime($row->last_failed_attempt);
        $currentTimestamp = time();

        if ($failed_attempts >= 3 && $currentTimestamp - $lastFailedAttempt < 30 * 60) {
            return true;
        }
        return false;
    }
}
