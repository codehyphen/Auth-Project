<?php

defined('BASEPATH') or exit('No Direct Script Access Allowed');

class Job_roles_model extends CI_Model
{
    private function get_all_job_roles(){
        // $this->db
        // get all the information 

        // join job_role and users table for getting user's first name and last name as combined 

        // limitation for 50 records only

        
        
    }

    public function add($data){
        // we will take only non deleted values with name
        // return true or false

        // $data['created_by_id'] = 
        // $data['created_on] = time();

        // then insert into job role table

        if($this->db->from('job_roles')->like('name', (string)$data['name'])->get()->num_rows()){
            return [
                'status' => 'FAILED',
                'message' => 'Job Role Exist Already'
            ];
        }

        if($this->db->insert('job_roles', $data)){
            return [
                'status' => 'SUCCESS',
                'message' => 'Job Added Successfully'
            ];
        }else{
            return [
                'status' => 'FAILED',
                'message' => 'Failed to Add Job'
            ];
        }
    }

    public function delete($id){
        // before deletion check if it exist or not and throw error based on it.
        

        if($this->db->from('job_roles')->like('job_role_id', $id)->get()->num_rows()){
            $this->db->where('job_role_id', $id)->delete('job_roles');
            return [
                'status' => 'SUCCESS',
                'message' => 'Deleted Successfully',
            ];
        }else{
            return [
                'status' => 'FAILED',
                'message' => 'JOB does not exist'
            ];
        }

    }

    public function update($data){
        // before updation check if it exist or not and non deleted and throw error based on it.

        $query = $this->db->like('job_role_id', $data['job_role_id'])->from('job_roles')->get();

        if($query->num_rows() > 0 && $query->row()->deleted_on===null){
            $this->db->set('name', $data['name'])->where('job_role_id', $data['job_role_id'])->update('job_roles');
            return [
                'status' => 'SUCCESS',
                'message' => 'Job Updated Successfully',
                'rows' => $query->num_rows(),
                'deleted_on' => $query->row()->deleted_on,
                'name' => $data['name']
            ];
            
        }else{
            return [
                'status' => 'FAILED',
                'message' => 'JOB does not exist',
                'rows' => $query->num_rows(),
                'deleted_on' => $query->row()->deleted_on
            ];
        }
        
        // if(num_rows==0){
        //     throw error
        // }

        // if() //check if the job role id is not equal job role id passed in the data

        // if(num_rows>0){
        //     throw error
        // }
    }

}