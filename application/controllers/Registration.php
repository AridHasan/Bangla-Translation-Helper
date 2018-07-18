<?php

/**
 * Created by PhpStorm.
 * User: Himel
 * Date: 6/5/2018
 * Time: 10:35 PM
 */
class Registration extends CI_Controller
{
    /*
     * User Registration
     */
    public function index(){
        //checking the user is invited or not
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
        /*
         * the given email is valid or not like the email is exists in our system or not
         * if exists in our system then user will get an error message
         */
        $email = $this->input->post('email');
        $this->load->model('Auth');
        $result = $this->Auth->valid_email($email);
        echo $result;
    }
    public function validate_username(){
        /*
         * the given username is valid or not like the username is exists in our system or not
         * if exists in our system then user will get an error message
         */
        $username = $this->input->post('username');
        $this->load->model('Auth');
        $result = $this->Auth->valid_username($username);
        echo $result;
    }
    public function register(){
        $fname = $lname = $email = $username = $gender = $password = $conf_pass = '';
        $fname = $this->input->post('fname');//get html form data first name
        $lname = $this->input->post('lname');//get html form data last name
        $email = $this->input->post('email');//get html form data email address
        $username = $this->input->post('username');//get html form data username
        $gender = $this->input->post('gender');// get html form data gender
        $password = $this->input->post('password');// get html form data password
        $conf_pass = $this->input->post('conf_pass');//get html form data confirm password
        if($password == $conf_pass){
            $int_pass = hash_hmac('sha512', $password, 'aridh'); // hash the provided password using sha-512 algorithm
            $md5_pass = md5($int_pass);// hash the sha-512 hashed password using md5 algorithm
            $data = array(
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'username' => $username,
                'gender' => $gender,
                'password' => $md5_pass
            );
            $this->load->model('Auth');
            $response = $this->Auth->registration($data);//sending data to the model Auth class for inserting into database
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