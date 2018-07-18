<?php

/*
 * URL = http://something.com/accountActivation?activationKey=$key
 */

class AccountActivation extends CI_Controller
{
    /*
     * This the Account Activation Class where user will confirm his/her registration by confirmation code.
     * Confirmation code sent in mail while submitting the registration form.
     * Confirmation code url will be like:
     *      http://127.0.0.1/TranslationHelper/AccountActivation?activationKey={md5_hash_of_6_digit_code}
     */
    public function index(){
        if(array_key_exists('activationKey', $_GET)){
            $act_key = $_GET['activationKey'];//get teh activation key
            $this->load->model('Auth');
            $response = $this->Auth->check_activation_key($act_key);//checking the activation key is exists in database or not
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