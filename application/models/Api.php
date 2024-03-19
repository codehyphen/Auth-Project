<?php

defined('BASEPATH') or exit('No Direct Script Access Allowed');

class Api extends CI_Model
{
    public function login($data)
    {
        $email_check = $this->db->where('email', (string)$data['emailorUsername'])->from('users')->count_all_results();
        $username_check = $this->db->where('username', (string)$data['emailorUsername'])->from('users')->count_all_results();

        if ($email_check > 0) {

            $row = $this->db->from('users')->where('email', (string)$data['emailorUsername'])->get()->row();
            $password = $row->password;
            $userid = $row->userid;

            // $row = $this->db->query("SELECT * FROM users WHERE email = ?", $data['emailorUsername'])->row();
            // $password = $row->password;

            $blockedStatus = $this->checkBlockedStatus((string)$data['emailorUsername'], null);

            if ($blockedStatus) {
                return 3; // user is blocked
            }


            if (password_verify((string)$data['password'], $password)) {
                $roleid_from_roles = $this->db->select('role_id')->from('roles')->where('title', 'HR')->get()->row()->role_id;
                $roleid_from_roleusermap = $this->db->select('role_id')->from('role_user_map')->where('user_id', $userid)->get()->row()->role_id;
                $last_password_update_date = strtotime($this->db->select('password_update_date')->from('users')->where('userid', $userid)->get()->row()->password_update_date);

                date_default_timezone_set('Asia/Kolkata');
                $current_date = time();

                $time_difference = $current_date - $last_password_update_date;

                if ($roleid_from_roles == $roleid_from_roleusermap) {
                    if ($time_difference < (90 * 24 * 60 * 60)) {
                        $this->db->set('failed_attempts', '0', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');

                        $data = array(
                            'user_id' => $userid,
                            'event_id' => 1,
                            'status' => 'SUCCESS',
                            'message' => 'Logged In'
                        );

                        $this->db->insert('event_logs', $data);

                        return 1; //successfull login
                    } else {
                        return 5; // reset the password
                    }
                } else {
                    return 4; // You don't have access 
                }
            } else {
                $this->db->set('failed_attempts', 'failed_attempts+1', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');
                $this->db->set('last_failed_attempt', 'NOW()', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');

                $data = array(
                    'user_id' => $userid,
                    'event_id' => 3,
                    'status' => 'FAILED',
                    'message' => 'Invalid Credentials'
                );

                $this->db->insert('event_logs', $data);
                return 2; // invalid credentials
            }
        } else if ($username_check > 0) {
            $query = $this->db->from('users')->where('username', (string)$data['emailorUsername'])->get();
            $row = $query->row();
            $password = $row->password;
            $userid = $row->userid;

            $blockedStatus = $this->checkBlockedStatus(null, (string)$data['emailorUsername']);

            if ($blockedStatus) {
                return 3;
            }

            if (password_verify((string)$data['password'], $password)) {

                $roleid_from_roles = $this->db->select('role_id')->from('roles')->where('title', 'HR')->get()->row()->role_id;
                $roleid_from_roleusermap = $this->db->select('role_id')->from('role_user_map')->where('user_id', $userid)->get()->row()->role_id;
                $last_password_update_date = strtotime($this->db->select('password_update_date')->from('users')->where('userid', $userid)->get()->row()->password_update_date);

                date_default_timezone_set('Asia/Kolkata');
                $current_date = time();

                $time_difference = $current_date - $last_password_update_date;

                if ($roleid_from_roles == $roleid_from_roleusermap) {

                    if ($time_difference < (90 * 24 * 60 * 60)) {
                        $this->db->set('failed_attempts', '0', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');
                        $data = array(
                            'user_id' => $userid,
                            'event_id' => 1,
                            'status' => 'SUCCESS',
                            'message' => 'Logged In'
                        );

                        $this->db->insert('event_logs', $data);
                        return 1;
                    } else {
                        return 5;
                    }
                } else {
                    return 4;
                }
            } else {
                $this->db->set('failed_attempts', 'failed_attempts+1', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');
                $this->db->set('last_failed_attempt', 'NOW()', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');

                $data = array(
                    'user_id' => $userid,
                    'event_id' => 3,
                    'status' => 'FAILED',
                    'message' => 'Invalid Credentials'
                );

                $this->db->insert('event_logs', $data);
                return 2;
            }
        } else {
            return 2;
        }
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

        if ($username_check == 0 && $email_check == 0) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            date_default_timezone_set('Asia/Kolkata');
            $data['password_update_date'] = date("Y-m-d H:i:s");;
            $this->db->insert('users', $data);
            return 1;
        } else if ($username_check > 0) {
            return 2;
        } else if ($email_check > 0) {
            return 3;
        }
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

        if (password_verify($data['password'], $userinfo?->password) || password_verify($data['password'], $userinfo?->old_password) ) {
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

    private function checkBlockedStatus($email, $username)
    {
        $query1 = $this->db->select('last_failed_attempt')->from('users')->where(($email != null ? 'email' : 'username'), ($email != null ? $email : $username))->get();

        $failed_attempts = $this->db->select('failed_attempts')->from('users')->where($email != null ? 'email' : 'username', $email != null ? $email : $username)->get()->row()->failed_attempts;

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
