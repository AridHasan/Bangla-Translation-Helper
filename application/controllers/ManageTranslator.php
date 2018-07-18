<?php

class ManageTranslator extends CI_Controller
{
    public $projectId;
    public $userId;
    /*
     * This class is responsible for manage translators.
     * a project admin can change translator type either 'translator' or 'expert translator'
     */
    public function index(){
        $this->load->model('Auth');
        //checking the user is logged in or not
        if($this->Auth->islogged() == true){
            //checking the user is admin or not. Only Admin can access this page
            if($_SESSION['userType'] == 'admin' and array_key_exists('project', $_GET)) {
                $this->userId = $_SESSION['uId'];
                $this->projectId = $_GET['project'];
                $this->load->model('Projects');
                //check the project is created by the user or not.
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
    /*
     * This update_permission function is responsible for updating translator role of a project
     * each translator role vary project to project.
     */
    public function update_permission(){
        $pId = $this->input->post('pId'); //getting html form data project id
        $uId = $this->input->post('uId'); //getting html form data user id
        $type = $this->input->post('type'); //getting html form data permission type
        $this->load->model('Projects');
        $res = $this->Projects->update_permission($pId,$uId,$type);
        if ($res != ''){
            echo 'true';
        }else{
            echo 'false';
        }
    }
}