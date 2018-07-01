<?php

/*
 * URL = http://something.com/accountActivation?activationKey=$key
 */

class AccountActivation extends CI_Controller
{
    public function index(){
        if(array_key_exists('activationKey', $_GET)){
            $act_key = $_GET['activationKey'];
            $this->load->model('Auth');
            $response = $this->Auth->check_activation_key($act_key);
            if($response == true){
                $this->session->set_flashdata('reg_succ', 'Account successfully confirmed');
                redirect(base_url().'login');
            }else{
                $this->session->set_flashdata('activation_error', 'Invalid Activation Key');
                $this->load->view('accountActivation');
            }
        }else{
            $this->session->set_flashdata('activation_error', 'Invalid URL');
            $this->load->view('accountActivation');
        }
    }
}