<?php

class ManageTranslator extends CI_Controller
{
    public $projectId;
    public $userId;
    public function index(){
        $this->load->model('Auth');
        if($this->Auth->islogged() == true){
            if($_SESSION['userType'] == 'admin' and array_key_exists('project', $_GET)) {
                $this->userId = $_SESSION['uId'];
                $this->projectId = $_GET['project'];
                $this->load->model('Projects');
                $response = $this->Projects->check_project($this->userId, $this->projectId);
                if($response!= false) {
                    $result = $this->Projects->get_translators($this->projectId);
                    /*foreach ($response->result() as $row) {
                        $data = array(
                            'pId' => $this->projectId,
                            'uId' => $this->userId,
                            'pName' => $row->pName
                        );
                    }*/
                    $data['data'] = $result;
                    $this->load->view('manage', $data);
                }else{
                    redirect(base_url().'dashboard');
                }
            }else{
                redirect(base_url().'dashboard');
            }
        }else {
            redirect(base_url().'login');
        }
    }
    public function update_permission(){
        $pId = $this->input->post('pId');
        $uId = $this->input->post('uId');
        $type = $this->input->post('type');
        $this->load->model('Projects');
        $res = $this->Projects->update_permission($pId,$uId,$type);
        if ($res != ''){
            echo 'true';
        }else{
            echo 'false';
        }
    }
}