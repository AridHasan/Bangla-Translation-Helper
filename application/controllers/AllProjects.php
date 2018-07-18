<?php

/**
 * Created by PhpStorm.
 * User: Himel
 * Date: 6/24/2018
 * Time: 9:01 PM
 */
class AllProjects extends CI_Controller
{
    /*
     * In this class we will get all the project information from database.
     */
    public function index(){
        $this->load->model('Auth');
        //Checking either the requested user is logged in or not
        if($this->Auth->islogged() == true){
            $uId = $_SESSION['uId'];//getting user id from session data
            $this->load->model('Projects');
            // Getting project & user information from Model->Project Class
            $response = $this->Projects->get_data($uId);
            $user = $response['user_info'];//getting user information from database
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
            //loading user interface with the necessary data
            $this->load->view('all_projects', $user_data);
        }else {
            //if user isn't logged in then the user will redirect to the login to proceed the further step
            redirect(base_url().'login');
        }
    }
    /*
     * Searching user desired project which he/she will collaborate by project_name
     */
    public function search_project(){
        $key = $this->input->post('key');//get search text from html form data
        $this->load->model('Projects');
        $response = $this->Projects->search_project($key);
        $table = '';
        foreach ($response->result() as $row){
            //Making table structure for display the matching project list
            $table .= '<tr><td style="width: 70%;">'.$row->pName.'</td><td><a href="'.base_url().'Editor?project='.$row->projectId.'" class="btn btn-primary">Translate</a></td></tr>';
        }
        echo $table;
    }
    /*
     * re-arrange project list after clearing the search bar
     * or if the search text is null
     */
    public function restore_project(){
        $this->load->model('Projects');
        $response = $this->Projects->restore_project();
        $table = '';
        foreach ($response->result() as $row){
            //Making table structure for display the re-arrange list
            $table .= '<tr><td style="width: 70%;">'.$row->pName.'</td><td><a href="'.base_url().'Editor?project='.$row->projectId.'" class="btn btn-primary">Translate</a></td></tr>';
        }
        echo $table;
    }
}