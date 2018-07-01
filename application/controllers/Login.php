<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends CI_Controller
{
    public function index(){
        $this->load->model('Auth');
        if($this->Auth->islogged() == true){
            redirect(base_url().'dashboard');
        }else {
            $this->load->view('login');
        }
    }
    public function login_valid(){
        //
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        if(!empty($email) && !empty($password)){
            $int_pass = hash_hmac('sha512', $password, 'aridh');
            $md5_pass = md5($int_pass);
            $this->load->model('Auth');
            $data = array(
                'email' => $email,
                'password' => $md5_pass
            );
            $response = $this->Auth->login($data);
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
                foreach ($response->result() as $row){
                    if($row->status == 'active'){
                        $data = array(
                            'id' => $row->uId,
                            'auth' => md5($row->password)
                        );
                        $this->Auth->set_cookies($data);
                        $_SESSION['uId'] = $row->uId;
                        $_SESSION['userType'] = $row->userType;
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
    public function logout(){
        $this->load->model('Auth');
        $this->Auth->logout();
    }
}