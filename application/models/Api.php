<?php

defined('BASEPATH') or exit('No Direct Script Access Allowed');

class Api extends CI_Model
{
    public function login($data)
    {
        $userinfo = $this->db
            ->where('email', (string)$data['emailorUsername'])
            ->or_where('username', (string)$data['emailorUsername'])
            ->from('users')
            ->get()->row();

        if (!$userinfo?->email || !$userinfo?->username) {
            return "Invalid Credentials";
        }

        $blockedStatus = $this->checkBlockedStatus($userinfo);

        if ($blockedStatus) {
            return "Login After 30 min";
        }

        if (!password_verify((string)$data['password'], $userinfo->password)) {

            $this->db
                ->set('failed_attempts', 'failed_attempts+1', FALSE)
                ->where('email', (string)$data['emailorUsername'])
                ->or_where('username', (string)$data['emailorUsername'])
                ->update('users');
            $this->db
                ->set('last_failed_attempt', 'NOW()', FALSE)
                ->where('email', (string)$data['emailorUsername'])
                ->or_where('username', (string)$data['emailorUsername'])
                ->update('users');

            $data = array(
                'user_id' => $userinfo->userid,
                'event_id' => 3,
                'status' => 'FAILED',
                'message' => 'Invalid Credentials'
            );

            $this->db->insert('event_logs', $data);
            return "Invalid Credentials";
        }
        $roleid_from_roles = $this->db
            ->select('role_id')
            ->from('roles')
            ->where('title', 'HR')
            ->get()->row()->role_id;

        $roleid_from_roleusermap = $this->db
            ->select('role_id')
            ->from('role_user_map')
            ->where('user_id', $userinfo->userid)
            ->get()->row()->role_id;

        $last_password_update_date = strtotime($userinfo->password_update_date);

        date_default_timezone_set('Asia/Kolkata');
        $current_date = time();

        $time_difference = $current_date - $last_password_update_date;

        if ($roleid_from_roles !== $roleid_from_roleusermap) {
            return "You dont have access to the page";
        }
        if ($time_difference > (90 * 24 * 60 * 60)) {
            return "You need to reset you password for security purposes";
        }

        $this->db
            ->set('failed_attempts', '0', FALSE)
            ->where('email', (string)$data['emailorUsername'])
            ->update('users');

        $data = array(
            'user_id' => $userinfo->userid,
            'event_id' => 1,
            'status' => 'SUCCESS',
            'message' => 'Logged In'
        );

        $this->db->insert('event_logs', $data);
    }
    public function logout($emailorUsername)
    {
        $userid = '';
        $email_check = $this->db->where('email', (string)$emailorUsername)->from('users')->count_all_results();
        $username_check = $this->db->where('username', (string)$emailorUsername)->from('users')->count_all_results();

        if ($email_check > 0) {
            $userid = $this->db->select('userid')->where('email', $emailorUsername)->from('users')->get()->row()->userid;
        } else if ($username_check > 0) {
            $userid = $this->db->select('userid')->where('username', $emailorUsername)->from('users')->get()->row()->userid;
        }

        $data = array(
            'user_id' => $userid,
            'event_id' => 2,
            'status' => 'SUCCESS',
            'message' => 'LoggedOut'
        );
        $this->db->set('message', 'Logged Out')->where('user_id', $userid)->update('event_logs');
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

    public function resetpassword($data)
    {
        $userinfo = $this->db
            ->where('email', (string)$data['email'])
            ->or_where('username', (string)$data['email'])
            ->from('users')
            ->get()->row();

        if (!$userinfo?->email || !$userinfo?->username) {
            return "User Not Exist";
        }

        if ($data['password'] != $data['confirm_password']) {
            return "The Entered Password and Confirm Password are not Same";
        }

        if (password_verify($data['password'], $userinfo?->password) || password_verify($data['password'], $userinfo?->old_password)) {
            return "Old Password and New Password Must Not same";
        }

        $hash_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->db
            ->set('old_password', $userinfo?->password)
            ->set('password', $hash_password)
            ->set('password_update_date', 'NOW()', FALSE)
            ->where('email', (string)$data['email'])
            ->or_where('username', (string)$data['email'])
            ->update('users');
    }

    private function checkBlockedStatus($userinfo)
    {
        date_default_timezone_set('Asia/Kolkata');
        $lastFailedAttempt = strtotime($userinfo->last_failed_attempt);
        $currentTimestamp = time();

        if ($userinfo->failed_attempts >= 3 && $currentTimestamp - $lastFailedAttempt < 30 * 60) {
            return true;
        }
        return false;
    }
}
