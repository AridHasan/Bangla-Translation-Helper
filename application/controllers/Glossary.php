<?php

class Glossary extends CI_Controller
{
    /*
     * This class is used for showing glossary to the user.
     * Glossary contains Source word, Translated word and Part of Speech(PoS)
     */
    private $page=0;
    public function index(){
        $this->load->model('Auth');
        //Checking either the user is logged in or not
        if($this->Auth->islogged() == true){
            $this->load->model('Projects');
            //Get the page number
            if(array_key_exists('page',$_GET)){
                $cPage=$_GET['page'];//getting current page no from url
                if($cPage==0){
                    $cPage=1;
                }
            }else{
                $cPage = 1;
            }
            //get glossary data from database
            $response = $this->Projects->get_glossary($cPage);
            $num_row = $this->Projects->count_glossary();//count total no of words presents in database
            //count total number of page and per page showing glossary 1000 words
            $this->page = round(($num_row/1000) + 0.5);
            $data = array('glossary' => $response);
            $data["tpage"]=$this->page;
            $data['cpage'] = $cPage;
            $this->load->view('glossary_v', $data);
        }else {
            redirect(base_url().'login');
        }
    }
    /*
     * Search in the glossary by source word or translated word
     * this function will works like starts with something/search_bar_text
     */
    public function search_glossary(){
        $key = $this->input->post('key');//getting html form data {search key}
        $low = $this->input->post('low');
        $high = $this->input->post('high');
        $this->load->model('Projects');
        $response = $this->Projects->search_glossary($key,$low,$high);
        //prepare table for showing glossary
        $table_data='<tr><th style="width: 30%;">Source Term</th><th style="width: 30%;">Translation</th>';
        $table_data .= '<th style="width: 25%;">Part of Speech</th><th style="width: 15%;"><i class="fa fa-info-circle" style="color: deepskyblue;"></i></th></tr>';
        foreach ($response->result() as $row){
            $table_data .= "<tr><td>$row->enUS</td><td>$row->bnBD</td><td>$row->pos</td>";
            $table_data .= '<td><a href="#" id="infoChange"><i class="fa fa-edit" style="color: deepskyblue; font-size: 22px;"></i></a></td></tr>';
        }
        echo $table_data;
    }
    /*
     * When the search bar will be null then the glossary table need to re-arrange
     * This function will work like reset function.
     */
    public function restore_glossary(){
        $low = $this->input->post('low');
        $high = $this->input->post('high');
        $this->load->model('Projects');
        $response = $this->Projects->get_glossary(($low+$high)/2);
        //prepare table for showing glossary
        $table_data='<tr><th style="width: 30%;">Source Term</th><th style="width: 30%;">Translation</th>';
        $table_data .= '<th style="width: 25%;">Part of Speech</th><th style="width: 15%;"><i class="fa fa-info-circle" style="color: deepskyblue;"></i></th></tr>';
        foreach ($response->result() as $row){
            $table_data .= "<tr><td>$row->enUS</td><td>$row->bnBD</td><td>$row->pos</td>";
            $table_data .= '<td><a href="#" id="infoChange"><i class="fa fa-edit" style="color: deepskyblue; font-size: 22px;"></i></a></td></tr>';
        }
        echo $table_data;
    }
}