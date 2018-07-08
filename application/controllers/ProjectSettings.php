<?php
ini_set('max_execution_time', 360);
class ProjectSettings extends CI_Controller {
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
    public function export_files(){
        $pId= $this->input->post('pId');
        $format = $this->input->post('format');
        //$config['upload_path'] = './resources/files/downloads/';
        //$config['allowed_types'] = $format;
        //$config['max_size'] = '200';
        $this->load->helper('file');
        $this->load->model('Projects');
        $sentences = $this->Projects->export_sentences($pId);
        $source = $sentences['source'];
        $target = $sentences['target'];
        $url='';
        if($format=='csv'){
            $url='./resources/files/downloads/'.$pId.'.'.$format;
            $fh = fopen("$url","w");
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($fh,array('Source Text', 'Translated Text'));
            foreach ($source->result() as $sentence){
                $flag=0;
                foreach ($target->result() as $trans){
                    if($sentence->sId == $trans->sId){
                        $flag=1;
                        fputcsv($fh,array($sentence->sourceSentence, $trans->targetText));
                    }
                }
                if($flag==0){
                    fputcsv($fh,array($sentence->sourceSentence, ''));
                }
            }
            fclose($fh);
        }elseif ($format=='tsv'){
            $url=$url='./resources/files/downloads/'.$pId.'.'.$format;
            $fh = fopen($url,"w");
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($fh,array("Source Text \t Translated Text"));
            foreach ($source->result() as $sentence){
                $flag=0;
                foreach ($target->result() as $trans){
                    if($sentence->sId == $trans->sId){
                        $flag=1;
                        fputcsv($fh,array($sentence->sourceSentence."\t". $trans->targetText));
                    }
                }
                if($flag==0){
                    fputcsv($fh,array($sentence->sourceSentence."\t". ''));
                }
            }
            fclose($fh);
        }
        // Build the headers to push out the file properly.
        header('Set-Cookie: fileDownload=true; path=/');
        header('Pragma: public');     // required
        header('Expires: 0');         // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($url)).' GMT');
        header('Cache-Control: must-revalidate');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename("$pId.$format").'"');  // Add the file name
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($url)); // provide file size
        header('Connection: close');
        readfile($url); // push it out
        unlink($url);
        exit();
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