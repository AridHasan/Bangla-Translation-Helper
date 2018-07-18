<?php

class ChangePassword extends CI_Controller
{
    //My base Url = http://127.0.0.1/TranslationHelper/
    /*
     * In this class a user can change password using the option 'forget password'
     */
     /*
     * In this class, there is two option
      * 1. confirm that user wants to change password by providing valid email address or username
     * which is for validate user.
      *     URL will be 'http://127.0.0.1/TranslationHelper/ChangePassword?changeKey={md5 hash of 6 digit code}'
      * 2. after confirm by code which was sent into the user email he will able to change password
      *     Url will be like 'http://127.0.0.1/TranslationHelper/ChangePassword'
     */
    public function index(){
        //checking the changeKey exists in the url if changeKey exists then the user will able to change password
        /*otherwise user will able to get the confirmation code by email address or username. In this case, user need to prove that
         * the user is valid for this application
         */
        if(array_key_exists('changeKey',$_GET)){
            $key = $_GET['changeKey'];//getting change key from url
            $this->load->model('Auth');
            $valid = $this->Auth->validate_key($key); //verify the key is exist or not in the database
            if($valid){
                $this->load->view('forgot_pass');
            }else{
                redirect(base_url().'login');
            }
        }else {
            $this->load->view('forgot_pass');
        }
    }
    /*
     * If the user give the email address or username to get confirmation code then this 'send_code' function will work
     * In this function, Initially system will check the given email or username by the user is valid for this system or not.
     * if the user is valid for the system then the user will get a confirmation code to change the password
     * otherwise the user will get an error message that 'Invalid username or email'
     * Email will contain a URL like : http://127.0.0.1/TranslationHelper/ChangePassword?changeKey={md5 hash of 6 digits code}
     */
    public function send_code(){
        $user = $this->input->post('email');//getting email address from html form data for sending code
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
    /*
     * This function will work when the 'change password' button is clicked.
     * First of all the the password given by the user will encrypted by using 'sha512' algorithm then the 'sha512' encrypted password
     *  will be encrypted into non-reversible hash by 'md5' hash algorithm.
     * After encrypt the password system will save the new password into 'users' table.
     */
    public function change_password(){
        $password = $this->input->post('password');
        $conf_password = $this->input->post('conf_pass');
        $key = $this->input->post('key');
        //checking the given password matches or not
        if($password == $conf_password){
            //if the password matches then these line will be executed
            $int_pass = hash_hmac('sha512', $password, 'aridh');
            $md5_pass = md5($int_pass);
            $this->load->model('Auth');
            $this->Auth->update_password($md5_pass, $key);
            $this->session->set_flashdata('reg_succ','Password Successfully Changed');
            redirect(base_url().'login');
        }else{
            //if the password doesn't matches then the user will get an error message
            $this->session->set_flashdata('mess','Password Doesn\'t match');
            redirect(base_url().'ChangePassword?confirmKey='.$key);
        }
    }
}