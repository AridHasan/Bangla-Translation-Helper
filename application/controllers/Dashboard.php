<?php

class Dashboard extends CI_Controller
{
    public function index(){
        $this->load->model('Auth');
        if($this->Auth->islogged() == true){
            $uId = $_SESSION['uId'];
            $this->load->model('Projects');
            $response = $this->Projects->get_data($uId);
            $user = $response['user_info'];
            foreach ($user->result() as $row) {
                $user_data = array(
                    'name' => $row->fname . ' ' . $row->lname,
                    'username' => $row->username
                );
            }
            $my_project = $response['my_projects'];
            $my_data = array();
            while ($row = mysqli_fetch_assoc($my_project)){
                $arr = array(
                    'pName' => $row['pName'],
                    'projectId' => $row['projectId']
                );
                array_push($my_data, $arr);
            }
            $all_projects = $response['all_projects'];
            $all_data = array();
            foreach ($all_projects->result() as $row){
                $arr = array(
                    'pName' => $row->pName,
                    'projectId' => $row->projectId
                );
                array_push($all_data, $arr);
            }
            $user_data['my_data'] = $my_data;
            $user_data['all_data'] = $all_data;
            $this->load->view('dashboard_v', $user_data);
        }else {
            redirect(base_url().'login');
        }
    }
    public function validate_projectId(){
        $pId = $this->input->post('pId');
        $this->load->model('Projects');
        $response = $this->Projects->check_projectId($pId);
        echo $response;
    }
    public function create_project(){
        $pname = $this->input->post('projectName');
        $pId = $this->input->post('projectId');
        $description = $this->input->post('description');
        $status = $this->input->post('status');
        if(!empty($pname) and !empty($pId)){
            $data = array(
                'uId' => $_SESSION['uId'],
                'pName' => $pname,
                'projectId' => $pId,
                'description' => $description,
                'pCreation' => date('Y-m-d H:i:s', time()),
                'status' => $status
            );
            $this->load->model('Projects');
            $response = $this->Projects->create_project($data);
            if($response){
                $this->session->set_flashdata('p_err','Successfully Created');
                $this->index();
            }else{
                $this->session->set_flashdata('p_err','Something went wrong. Try again later');
                $this->index();
            }
        }else{
            $this->session->set_flashdata('p_err','Please Fill All the Fields');
            $this->index();
        }
    }
}