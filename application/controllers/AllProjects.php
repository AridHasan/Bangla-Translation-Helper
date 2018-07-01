<?php

/**
 * Created by PhpStorm.
 * User: Himel
 * Date: 6/24/2018
 * Time: 9:01 PM
 */
class AllProjects extends CI_Controller
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
            $all_projects = $response['all_projects'];
            $all_data = array();
            foreach ($all_projects->result() as $row){
                $arr = array(
                    'pName' => $row->pName,
                    'projectId' => $row->projectId
                );
                array_push($all_data, $arr);
            }
            $user_data['all_data'] = $all_data;
            $this->load->view('all_projects', $user_data);
        }else {
            redirect(base_url().'login');
        }
    }
    public function search_project(){
        $key = $this->input->post('key');
        $this->load->model('Projects');
        $response = $this->Projects->search_project($key);
        $table = '';
        foreach ($response->result() as $row){
            $table .= '<tr><td style="width: 70%;">'.$row->pName.'</td><td><a href="'.base_url().'Editor?project='.$row->projectId.'" class="btn btn-primary">Translate</a></td></tr>';
        }
        echo $table;
    }
    public function restore_project(){
        $this->load->model('Projects');
        $response = $this->Projects->restore_project();
        $table = '';
        foreach ($response->result() as $row){
            $table .= '<tr><td style="width: 70%;">'.$row->pName.'</td><td><a href="'.base_url().'Editor?project='.$row->projectId.'" class="btn btn-primary">Translate</a></td></tr>';
        }
        echo $table;
    }
}