<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends CI_Controller
{
    /*
     * This class is responsible for user login
     */
    public function index(){
        $this->load->model('Auth');
        //checking either the user is logged in or not
        if($this->Auth->islogged() == true){
            redirect(base_url().'dashboard');
        }else {
            $this->load->view('login');
        }
    }
    /*
     * if login button clicked and email and password is given then the function will work
     */
    public function login_valid(){
        //getting the form data which is given by user
        $email = $this->input->post('email'); //getting html form data username or email
        $password = $this->input->post('password'); //getting html form data password
        if(!empty($email) && !empty($password)){ // checking both fields are empty or not
            //password encryption for matching with database password
            $int_pass = hash_hmac('sha512', $password, 'aridh'); //encrypt password using sha-512 algorithm
            $md5_pass = md5($int_pass); // again encrypt password using non reversible hash md5 algorithm
            $this->load->model('Auth');
            $data = array(
                'email' => $email,
                'password' => $md5_pass
            );
            $response = $this->Auth->login($data); // checking the provided information is correct or not
            if($response == 'in_em'){
                $this->session->set_flashdata('reg_succ','Invalid Email Address');
                $this->index();
            }else if ($response == 'in_un'){
                $this->session->set_flashdata('reg_succ','Invalid Username');
                $this->index();
            }else if($response == 'in_pa'){
                $this->session->set_flashdata('reg_succ','Invalid Password');
                $this->index();
            }else{
                //print_r($response->result());
                //Set session data after successfully login of a user
                foreach ($response->result() as $row){
                    if($row->status == 'active'){
                        $data = array(
                            'id' => $row->uId,
                            'auth' => md5($row->password)
                        );
                        $this->Auth->set_cookies($data); // set cookies in browser client side
                        $_SESSION['uId'] = $row->uId; //set session user id
                        $_SESSION['userType'] = $row->userType; //set session user type
                        redirect(base_url().'dashboard');
                    }else if($row->status == 'inactive'){
                        $this->session->set_flashdata('reg_succ','Please verify email address');
                        $this->index();
                    }else if($row->status == 'deleted'){
                        $this->session->set_flashdata('reg_succ','Invalid Account');
                        $this->index();
                    }
                }

            }
        }else{
            $this->session->set_flashdata('reg_succ','Please fill all the fields');
            $this->index();
        }
    }
    //if logout button click then this function will work
    public function logout(){
        $this->load->model('Auth');
        $this->Auth->logout();
    }
}