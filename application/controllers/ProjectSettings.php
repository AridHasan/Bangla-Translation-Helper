<?php
ini_set('max_execution_time', 360);
class ProjectSettings extends CI_Controller {
    public $projectId;
    public $userId;
    public function index(){
        $this->load->model('Auth');
        if($this->Auth->islogged() == true){
            if($_SESSION['userType'] != 'user' and array_key_exists('project', $_GET)) {
                $this->userId = $_SESSION['uId'];
                $this->projectId = $_GET['project'];
                $this->load->model('Projects');
                $response = $this->Projects->check_project($this->userId, $this->projectId);
                if($response!= false) {
                    foreach ($response->result() as $row) {
                        $data = array(
                            'pId' => $this->projectId,
                            'uId' => $this->userId,
                            'pName' => $row->pName
                        );
                    }
                    $this->load->view('projectSetting', $data);
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
    public function upload_sentences(){
        $config['upload_path'] = './resources/files/';
        $config['allowed_types'] = 'txt';
        $this->load->library('upload', $config);
        $pId= $this->input->post('projectId');
        if($this->upload->do_upload('sentences')){
            $url=base_url('resources/files/').$_FILES['sentences']['name'];
            $fh = fopen($url,'r');
            while ($line = fgets($fh)) {
                $data = array(
                    'uId' => $this->input->post('userId'),
                    'projectId' => $pId,
                    'sentence' => $line,
                    'sCreation' => date('Y-m-d H:i:s', time())
                );
                $this->load->model('Projects');
                //print_r($data);
                $this->Projects->upload_sentences($data);
            }
            fclose($fh);
            unlink('./resources/files/'.$_FILES['sentences']['name']);
            $this->session->set_flashdata('file_succ','File Uploads Successfully');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }else{
            $this->session->set_flashdata('file_succ','Error in file uploading');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }
    }
    public function upload_glossary(){
        $config['upload_path'] = './resources/files/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = '200';
        $this->load->library('upload', $config);
        $pId= $this->input->post('projectId');
        if($this->upload->do_upload('glossary')){
            $uId= $this->input->post('userId');
            $url=base_url('resources/files/').$_FILES['glossary']['name'];
            $fh = fopen($url,'r');
            $i=0;
            while(! feof($fh)) {
                $line = fgetcsv($fh);
                if($i==0){
                    if($line[0]!='en-US' and $line[1]!='bn-BD' and ($line[2]!='pos' or $line[2]!='POS') and $line[3]!='description'){
                        break;
                    }
                }else{
                    $data = array(
                        'uId' => $uId,
                        'enUS' => $line[0],
                        'bnBD' => $line[1],
                        'pos' => $line[2],
                        'description' => $line[3],
                        'gCreation' => date('Y-m-d H:i:s', time())
                    );
                    $this->load->model('Projects');
                    $this->Projects->upload_glossary($data);
                }
                $i++;
            }
            fclose($fh);
            unlink('./resources/files/'.$_FILES['glossary']['name']);
            $this->session->set_flashdata('glo_succ','File Uploads Successfully');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }else{
            $this->session->set_flashdata('glo_succ','File is too big to upload');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }
    }
    public function invite_user(){
        $email = $this->input->post('email');
        $pId = $this->input->post('pId');
        $this->load->model('Projects');
        //echo $email . " ".$pId;
        $result = $this->Projects->invited_users($email,$pId);
        if($result == "u_err"){
            $this->session->set_flashdata('mes','This user already Invited');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }else if($result== "p_err"){
            $this->session->set_flashdata('mes','This user already exists in your project');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }else{
            $this->session->set_flashdata('mes','Successfully Invited');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }
    }
}