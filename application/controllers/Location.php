<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Location extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        // $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->model('Location_model', 'location');
        $this->load->helper('url');
    }
    
    public function add(){
        $response = $this->checkUserLoggedIn();
        if($response['status']==='FAILED'){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $data = [
            'name' => $this->input->post('name'),
            'created_by_id' => $this->session->userdata('user_id')
        ];

        $response_data = $this->location->add($data);

        $status_code = 200;
        $this->output->set_status_header($status_code);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response_data));
    }

    public function delete(){
        $response = $this->checkUserLoggedIn();
        if($response['status']==='FAILED'){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $data = [
            'location_id' => $this->input->get('id')
        ];

        $response_data = $this->location->delete($data['location_id']);
        $status_code = 200;
        $this->output->set_status_header($status_code);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response_data));
    }

    public function get_all_locations(){
    }

    public function update(){
        $response = $this->checkUserLoggedIn();
        if($response['status']==='FAILED'){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $id = $this->input->get('id');

        if($id===null){
            $response = [
                'status' => 'FAILED',
                'message' => 'Please Enter ID to update the location',
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $name = $this->input->post('name');
        $data = [
            'location_id' => $id,
            'name' => $name
        ];
        $response_data = $this->location->update($data);
        $status_code = 200;
        $this->output->set_status_header($status_code);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response_data));
        
    }

    private function checkUserLoggedIn(){
        // check if the user is logges in or not
        if (!$this->session->userdata('user_data') || time() > $this->session->userdata('session_expire')) {
            
            $status_code = 404;
            $this->output->set_status_header($status_code);
            $response_data = array(
                'status' => 'FAILED',
                'message' => 'No Logged In User Found',
            );
            return $response_data; // not logged in
        }
        
        $response_data = array(
            'status' => 'SUCCESS',
            'message' => 'User Logged In',
        );

        return $response_data;
    }
}