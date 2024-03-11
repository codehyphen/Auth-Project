<?php

defined('BASEPATH') or exit('No Direct Script Access Allowed');

class Api extends CI_Model
{
    public function login($data)
    {
        $email_check = $this->db->where('email', (string)$data['emailorUsername'])->from('users')->count_all_results();
        $username_check = $this->db->where('username', (string)$data['emailorUsername'])->from('users')->count_all_results();

        if ($email_check > 0) {

            $query = $this->db->select('password')->from('users')->where('email', (string)$data['emailorUsername'])->get();
            $row = $query->row();
            $password = $row->password;

            // $failed_attempts = $this->db->select('failed_attempts')->where('email', (string)$data['emailorUsername'])->get();

            $blockedStatus = $this->checkBlockedStatus((string)$data['emailorUsername'], null);

            if ($blockedStatus) {
                return 3;
            }


            if (password_verify((string)$data['password'], $password)) {
                $this->db->set('failed_attempts', '0', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');
                return 1;
            } else {
                $this->db->set('failed_attempts', 'failed_attempts+1', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');
                $this->db->set('last_failed_attempt', 'NOW()', FALSE)->where('email', (string)$data['emailorUsername'])->update('users');
                return 2;
            }
        } else if ($username_check > 0) {
            $query = $this->db->select('password')->from('users')->where('username', (string)$data['emailorUsername'])->get();
            $row = $query->row();
            $password = $row->password;

            $blockedStatus = $this->checkBlockedStatus(null, (string)$data['emailorUsername']);

            if ($blockedStatus) {
                return 3;
            }

            if (password_verify((string)$data['password'], $password)) {
                $this->db->set('failed_attempts', '0', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');
                return 1;
            } else {
                $this->db->set('failed_attempts', 'failed_attempts+1', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');
                $this->db->set('last_failed_attempt', 'NOW()', FALSE)->where('username', (string)$data['emailorUsername'])->update('users');
                return 2;
            }
        } else {
            return 2;
        }
    }
    public function logout()
    {
    }
    public function register($data)
    {
        $username_check = $this->db->like('username', (string)$data['username'])->from('users')->count_all_results();
        $email_check = $this->db->like('email', (string)$data['email'])->from('users')->count_all_results();

        if ($username_check == 0 && $email_check == 0) {
            return 1;
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->db->insert('users', $data);
        } else if ($username_check > 0) {
            return 2;
        } else if ($email_check > 0) {
            return 3;
        }
    }

    public function resetpassword($data){
        // if email exist into database then only we can change the password
        $email_check = $this->db->where('email', (string)$data['email'])->from('users')->count_all_results();

        if ($email_check > 0) {
            if ($data['password'] != $data['confirm_password']) {
                return 2;
            } else {
                $hash_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $this->db->set('password', $hash_password)->where('email', (string)$data['email'])->update('users');
                return 1;
            }
        } else {
            return 3;
        }
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

