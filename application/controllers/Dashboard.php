<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        if (!$this->session->userdata('user_data') || time() > $this->session->userdata('session_expire')) {
            header("Location: /AuthProject/Users/Login"); // Redirect to login page if session is expired or user is not logged in
        }
    }
    public function index()
    {
        // Load dashboard view
        $this->load->view('dashboard');
    }
}
