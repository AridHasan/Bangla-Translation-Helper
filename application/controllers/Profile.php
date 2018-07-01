<?php

class Profile extends CI_Controller
{
    public function index(){
        $this->load->model('Auth');
        if($this->Auth->islogged() == true){
            $uId = $_SESSION['uId'];
            $this->load->model('Projects');
            $response = $this->Projects->get_user($uId);
            $data = array('user' => $response);
            $this->load->view('profile_v', $data);
        }else {
            redirect(base_url().'login');
        }
    }
    public function update_name(){
        $data = array(
            'fname' => $this->input->post('fname'),
            'lname' => $this->input->post('lname'),
            'uId' => $this->input->post('uId')
        );
        $this->load->model('Projects');
        $response = $this->Projects->update_name($data);
        if($response){
            echo true;
        }else{
            echo false;
        }
    }
    public function check_password(){
        $oldPass = $this->input->post('oPass');
        $uId = $this->input->post('uId');
        $int_pass = hash_hmac('sha512', $oldPass, 'aridh');
        $md5_pass = md5($int_pass);
        $this->load->model('Projects');
        $response = $this->Projects->check_password($md5_pass, $uId);
        if($response->num_rows() > 0){
            echo true;
        }else{
            echo false;
        }
    }
    public function update_password(){
        $oldPass = $this->input->post('oldPass');
        $uId = $this->input->post('uId');
        $newPass = $this->input->post('newPass');
        $int_pass = hash_hmac('sha512', $oldPass, 'aridh');
        $md5_pass = md5($int_pass);
        $this->load->model('Projects');
        $response = $this->Projects->check_password($md5_pass, $uId);
        if($response->num_rows() > 0){
            $int_pass = hash_hmac('sha512', $newPass, 'aridh');
            $md5_pass = md5($int_pass);
            $response = $this->Projects->update_password($md5_pass, $uId);
            if($response){
                setcookie('amaderinfo[_auth]',md5($md5_pass),(time()+604800),'/','','',true);
                echo true;
            }else{
                echo false;
            }
        }else{
            echo false;
        }
    }
}