<?php

class Profile extends CI_Controller
{
    /*
     * This class is responsible for updating profile information eg: First Name, Last Name, password etc.
     */
    public function index(){
        $this->load->model('Auth');
        //checking the user is logged in or not
        if($this->Auth->islogged() == true){
            $uId = $_SESSION['uId'];//getting user id from session data
            $this->load->model('Projects');
            $response = $this->Projects->get_user($uId);
            $data = array('user' => $response);
            $this->load->view('profile_v', $data);
        }else {
            redirect(base_url().'login');
        }
    }
    public function update_name(){
        /*
         * This function is responsible updating name.
         */
        $data = array(
            'fname' => $this->input->post('fname'),
            'lname' => $this->input->post('lname'),
            'uId' => $this->input->post('uId')
        );
        $this->load->model('Projects');
        //Save name in Database
        $response = $this->Projects->update_name($data);
        if($response){
            echo true;
        }else{
            echo false;
        }
    }
    public function check_password(){
        /*
         * this function is responsible for that the old password is given by the user is matches properly or not.
         */
        $oldPass = $this->input->post('oPass');//html form data
        $uId = $this->input->post('uId');//html form data
        //encrypt password using sha512 and md5 algorithm
        $int_pass = hash_hmac('sha512', $oldPass, 'aridh');//sha-512 algorithm
        $md5_pass = md5($int_pass); //Non-reversible hash
        $this->load->model('Projects');//loading model Projects
        //checking the previous password which is stored in database either matches with the provided old password or not
        $response = $this->Projects->check_password($md5_pass, $uId);
        if($response->num_rows() > 0){
            echo true;
        }else{
            echo false;
        }
    }
    public function update_password(){
        /*
         * Updating password in database which is provided by the user.
         */
        $oldPass = $this->input->post('oldPass');
        $uId = $this->input->post('uId');
        $newPass = $this->input->post('newPass');
        $int_pass = hash_hmac('sha512', $oldPass, 'aridh');//sha-512 encryption algorithm
        $md5_pass = md5($int_pass);//non-reversible hash
        $this->load->model('Projects');
        $response = $this->Projects->check_password($md5_pass, $uId);
        if($response->num_rows() > 0){
            $int_pass = hash_hmac('sha512', $newPass, 'aridh');
            $md5_pass = md5($int_pass);
            $response = $this->Projects->update_password($md5_pass, $uId);
            if($response){
                //updating the cookie after successfully password saved/changed
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