<?php

defined('BASEPATH') or exit('No Direct Script Access Allowed');

class Location_model extends CI_Model
{
    public function add($data){
        if($this->db->from('locations')->like('name', (string)$data['name'])->get()->num_rows()){
            return [
                'status' => 'FAILED',
                'message' => 'Location Exist Already'
            ];
        }

        if($this->db->insert('locations', $data)){
            return [
                'status' => 'SUCCESS',
                'message' => 'Location Added Successfully'
            ];
        }else{
            return [
                'status' => 'FAILED',
                'message' => 'Failed to Add Location'
            ];
        }
    }

    public function delete($id){
        if($this->db->from('locations')->like('location_id', $id)->get()->num_rows()){
            $this->db->where('location_id', $id)->delete('locations');
            return [
                'status' => 'SUCCESS',
                'message' => 'Deleted Successfully',
            ];
        }else{
            return [
                'status' => 'FAILED',
                'message' => 'Location does not exist'
            ];
        }

    }

    public function get_all_locations(){

    }

    public function update($data){
        $query = $this->db->like('location_id', $data['location_id'])->from('locations')->get();

        if($query->num_rows() > 0 && $query->row()->deleted_on===null){
            $this->db->set('name', $data['name'])->where('location_id', $data['location_id'])->update('locations');
            return [
                'status' => 'SUCCESS',
                'message' => 'Location Updated Successfully'
            ];
            
        }else{
            return [
                'status' => 'FAILED',
                'message' => 'Location does not exist',
            ];
        }
    }
}