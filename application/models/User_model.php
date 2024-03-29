<?php

defined('BASEPATH') or exit('No Direct Script Access Allowed');

class User_model extends CI_Model
{
    public function login($data)
    {
        $user_info = $this->getUserInfo($data);

        if ($user_info->num_rows() === 0) {
            return "Invalid Credentials";
        }

        $user_info = $user_info->row();

        $blockedStatus = $this->checkBlockedStatus($user_info);

        if ($blockedStatus) {
            return "Login After 30 min";
        }

        if (!password_verify((string)$data['password'], $user_info->password)) {

            $this->db
                ->set('failed_attempts', 'failed_attempts+1', FALSE)
                ->set('last_failed_attempt', 'NOW()', FALSE)
                ->where('user_id', $user_info->user_id)
                ->update('users');

            $this->db->insert('event_logs', [
                'user_id' => $user_info->user_id,
                'event_id' => 3,
                'status' => 'FAILED',
                'message' => 'Invalid Credentials'
            ]);
            return "Invalid Credentials";
        }
        $roleid_from_roles = $this->db
            ->select('role_id')
            ->from('roles')
            ->where('title', 'HR')
            ->get()->row()->role_id;

        $roleid_from_roleusermap = $this->db
            ->from('role_user_map')
            ->where('user_id', $user_info->user_id)
            ->where('role_id', $roleid_from_roles)
            ->get()->num_rows();

        $last_password_update_date = strtotime($user_info->password_update_date);

        date_default_timezone_set('Asia/Kolkata');
        $current_date = time();

        $time_difference = $current_date - $last_password_update_date;

        if ($roleid_from_roleusermap == 0) {
            return "You dont have access to the page";
        }
        if ($time_difference > (90 * 24 * 60 * 60)) {
            return "You need to reset you password for security purposes";
        }

        $this->db
            ->set('failed_attempts', '0', FALSE)
            ->where('user_id', $user_info->user_id)
            ->update('users');

        $this->db->insert('event_logs', [
            'user_id' => $user_info->user_id,
            'event_id' => 1,
            'status' => 'SUCCESS',
            'message' => 'Logged In'
        ]);
    }
    public function logout($emailorUsername)
    {
        $user_id = $this->getUserInfo(['emailorUsername' => $emailorUsername])->row()->user_id;
        
        $this->db->insert('event_logs', [
            'user_id' => $user_id,
            'event_id' => 2,
            'status' => 'SUCCESS',
            'message' => 'LoggedOut'
        ]);
    }
    public function register($data)
    {
        $username_check = $this->db->like('username', (string)$data['username'])->from('users')->count_all_results();
        $email_check = $this->db->like('email', (string)$data['email'])->from('users')->count_all_results();

        if ($email_check > 0) {
            return "Email Already Registered";
        }

        if ($username_check > 0) {
            return "Username Not available";
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        date_default_timezone_set('Asia/Kolkata');
        $data['password_update_date'] = date("Y-m-d H:i:s");;
        $this->db->insert('users', $data);
    }

    public function reset_password($data)
    {
        $user_info = $this->getUserInfo($data);

        if ($user_info->num_rows() === 0) {
            return "User Not Exist";
        }

        $user_info = $user_info->row();

        if ($data['password'] !== $data['confirm_password']) {
            return "The Entered Password and Confirm Password are not Same";
        }

        if (password_verify($data['password'], $user_info?->password)) {
            return "New Password cannot be same as last password";
        }
        if (password_verify($data['password'], $user_info?->old_password)) {
            return "New Password cannot be same as second last password";
        }

        $hash_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->db
            ->set([
                'old_password' => $user_info?->password,
                'password' => $hash_password,
            ])
            ->set('password_update_date', 'NOW()', FALSE)
            ->where('user_id', $user_info->user_id)
            ->update('users');
    }

    private function checkBlockedStatus($user_info)
    {
        date_default_timezone_set('Asia/Kolkata');
        $lastFailedAttempt = strtotime($user_info->last_failed_attempt);
        $currentTimestamp = time();

        if ($user_info->failed_attempts >= 3 && $currentTimestamp - $lastFailedAttempt < 30 * 60) {
            return true;
        }
        return false;
    }

    private function getUserInfo($data){
        return $this->db
            ->where('email', (string)$data['emailorUsername'])
            ->or_where('username', (string)$data['emailorUsername'])
            ->from('users')
            ->get();
    }
}
