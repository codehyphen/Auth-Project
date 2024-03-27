<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Jobs extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        // $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('Jobs_model', 'jobs');
        // $this->load->helper('url');
    }

    public function get()
    {
        // $result = $this->jobs->get();
        // $status_code = 200;
        // $this->output->set_status_header($status_code);
        // $response_data = array(
        //     'status' => 'success',
        //     'result' => $result
        // );
        // return $this->output
        //     ->set_content_type('application/json')
        //     ->set_output(json_encode($response_data));return $this->output
        //     ->set_content_type('application/json')
        //     ->set_output(json_encode($response_data));
    }

    public function add_job()
    {
    }

    public function update_job()
    {
    }

    public function delete_job()
    {
    }
}
