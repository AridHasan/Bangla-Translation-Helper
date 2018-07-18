<?php

class Dashboard extends CI_Controller
{
    /*
     * User Dashboard/Home. Here a 'translator' and 'expert translator' can view his/her connected projects
     */
    public function index(){
        $this->load->model('Auth');
        //checking the user either logged in or not
        if($this->Auth->islogged() == true){
            $uId = $_SESSION['uId'];//getting user id from session data
            $this->load->model('Projects');
            $response = $this->Projects->get_data($uId);
            $user = $response['user_info'];
            //Extract user data from response data which was collected data from get_data function of Projects class
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
                    'projectId' => $row['projectId'],
                );
                if(array_key_exists('permission', $row)){
                    $arr['permission'] = $row['permission'];
                }else{
                    $arr['permission'] = '';
                }
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
    /*
     * When a project admin try to create a project we need to check project id either project id exists or not
     * if the project id is exit user will get an error message otherwise user can set that project id as his/her project id
     */
    public function validate_projectId(){
        $pId = $this->input->post('pId');//getting project id from html form data
        $this->load->model('Projects');
        $response = $this->Projects->check_projectId($pId);
        echo $response;
    }
    /*
     * When a project admin click on 'create project' button then this function will work
     * In this function, the system will save the project information in the 'projects' table of database
     */
    public function create_project(){
        $pname = $this->input->post('projectName');//getting project name from html form data for create project
        $pId = $this->input->post('projectId');//getting project Id **must be unique** from html form data for create project
        $description = $this->input->post('description');//getting project description(optional. goal of this project) from html form data for create project
        $status = $this->input->post('status');//getting project status(public or private) from html form data for create project
        if(!empty($pname) and !empty($pId)){
            //Add project related data into a array
            $data = array(
                'uId' => $_SESSION['uId'],
                'pName' => $pname,
                'projectId' => $pId,
                'description' => $description,
                'pCreation' => date('Y-m-d H:i:s', time()),
                'status' => $status
            );
            //Load model Projects to save data
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