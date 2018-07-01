<?php

class Glossary extends CI_Controller
{
    private $page=0;
    public function index(){
        $this->load->model('Auth');
        if($this->Auth->islogged() == true){
            $this->load->model('Projects');
            if(array_key_exists('page',$_GET)){
                $cPage=$_GET['page'];
                if($cPage==0){
                    $cPage=1;
                }
            }else{
                $cPage = 1;
            }
            $response = $this->Projects->get_glossary($cPage);
            $num_row = $this->Projects->count_glossary();
            $this->page = round(($num_row/1000) + 0.5);
            $data = array('glossary' => $response);
            $data["tpage"]=$this->page;
            $data['cpage'] = $cPage;
            $this->load->view('glossary_v', $data);
        }else {
            redirect(base_url().'login');
        }
    }
    public function search_glossary(){
        $key = $this->input->post('key');
        $low = $this->input->post('low');
        $high = $this->input->post('high');
        $this->load->model('Projects');
        $response = $this->Projects->search_glossary($key,$low,$high);
        $table_data='<tr><th style="width: 30%;">Source Term</th><th style="width: 30%;">Translation</th>';
        $table_data .= '<th style="width: 25%;">Part of Speech</th><th style="width: 15%;"><i class="fa fa-info-circle" style="color: deepskyblue;"></i></th></tr>';
        foreach ($response->result() as $row){
            $table_data .= "<tr><td>$row->enUS</td><td>$row->bnBD</td><td>$row->pos</td>";
            $table_data .= '<td><a href="#" id="infoChange"><i class="fa fa-edit" style="color: deepskyblue; font-size: 22px;"></i></a></td></tr>';
        }
        echo $table_data;
    }
    public function restore_glossary(){
        $low = $this->input->post('low');
        $high = $this->input->post('high');
        $this->load->model('Projects');
        $response = $this->Projects->get_glossary(($low+$high)/2);
        $table_data='<tr><th style="width: 30%;">Source Term</th><th style="width: 30%;">Translation</th>';
        $table_data .= '<th style="width: 25%;">Part of Speech</th><th style="width: 15%;"><i class="fa fa-info-circle" style="color: deepskyblue;"></i></th></tr>';
        foreach ($response->result() as $row){
            $table_data .= "<tr><td>$row->enUS</td><td>$row->bnBD</td><td>$row->pos</td>";
            $table_data .= '<td><a href="#" id="infoChange"><i class="fa fa-edit" style="color: deepskyblue; font-size: 22px;"></i></a></td></tr>';
        }
        echo $table_data;
    }
}