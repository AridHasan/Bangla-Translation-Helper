<?php

class ChangePassword extends CI_Controller
{
    public function index(){
        if(array_key_exists('changeKey',$_GET)){
            $key = $_GET['changeKey'];
            $this->load->model('Auth');
            $valid = $this->Auth->validate_key($key);
            if($valid){
                $this->load->view('forgot_pass');
            }else{
                redirect(base_url().'login');
            }
        }else {
            $this->load->view('forgot_pass');
        }
    }
    public function send_code(){
        $user = $this->input->post('email');
        $this->load->model('Auth');
        $response = $this->Auth->send_code($user);
        if($response){
            $this->session->set_flashdata('reg_succ','An Email Has been sent. Please confirm your identity');
            redirect(base_url().'login');
        }else{
            $this->session->set_flashdata('mess','Invalid username or Email.');
            $this->index();
        }
    }
    public function change_password(){
        $password = $this->input->post('password');
        $conf_password = $this->input->post('conf_pass');
        $key = $this->input->post('key');
        if($password == $conf_password){
            $int_pass = hash_hmac('sha512', $password, 'aridh');
            $md5_pass = md5($int_pass);
            $this->load->model('Auth');
            $this->Auth->update_password($md5_pass, $key);
            $this->session->set_flashdata('reg_succ','Password Successfully Changed');
            redirect(base_url().'login');
        }else{
            $this->session->set_flashdata('mess','Password Doesn\'t match');
            redirect(base_url().'ChangePassword?confirmKey='.$key);
        }
    }
}