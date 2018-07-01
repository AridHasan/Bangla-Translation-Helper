<?php

/**
 * Created by PhpStorm.
 * User: Himel
 * Date: 6/5/2018
 * Time: 10:35 PM
 */
class Registration extends CI_Controller
{
    public function index(){
        if(array_key_exists('email',$_GET)){
            $this->load->model('Auth');
            $is_invited = $this->Auth->is_invited($_GET['email']);
            if($is_invited){
                $data['email'] = $_GET['email'];
                $this->load->view('registration',$data);
            }else{
                redirect(base_url().'login');
            }
        }else{
            $data['email'] = '';
            $this->load->view('registration',$data);
        }
    }
    public function validate_email(){
        $email = $this->input->post('email');
        $this->load->model('Auth');
        $result = $this->Auth->valid_email($email);
        echo $result;
    }
    public function validate_username(){
        $username = $this->input->post('username');
        $this->load->model('Auth');
        $result = $this->Auth->valid_username($username);
        echo $result;
    }
    public function register(){
        $fname = $lname = $email = $username = $gender = $password = $conf_pass = '';
        $fname = $this->input->post('fname');
        $lname = $this->input->post('lname');
        $email = $this->input->post('email');
        $username = $this->input->post('username');
        $gender = $this->input->post('gender');
        $password = $this->input->post('password');
        $conf_pass = $this->input->post('conf_pass');
        if($password == $conf_pass){
            $int_pass = hash_hmac('sha512', $password, 'aridh');
            $md5_pass = md5($int_pass);
            $data = array(
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'username' => $username,
                'gender' => $gender,
                'password' => $md5_pass
            );
            $this->load->model('Auth');
            $response = $this->Auth->registration($data);
            if($response == true){
                $this->session->set_flashdata('reg_succ','An Email Has been sent. Please confirm registration');
                redirect(base_url().'login');
            }else{
                $this->session->set_flashdata('reg_pass','Something went wrong! Try again later.');
            }
        }else{
            $this->session->set_flashdata('reg_pass','Password Doesn\'t match');
            $this->index();
        }
    }
}