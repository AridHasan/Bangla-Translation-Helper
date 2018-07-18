<?php
ini_set('max_execution_time', 360);
class ProjectSettings extends CI_Controller {
    /*
     * this class manages all the settings which is related to a project like: upload sentences file, upload glossary files etc.
     */
    public $projectId; //store project id
    public $userId;
    public function index(){
        $this->load->model('Auth');//load Auth model for checking the user is logged in or not
        if($this->Auth->islogged() == true){//checking the user is logged in or not
            //this page is only visible to the admin and project admin
            if($_SESSION['userType'] == 'admin' and array_key_exists('project', $_GET)) {//check the user either project admin or not
                $this->userId = $_SESSION['uId'];
                $this->projectId = $_GET['project'];
                $this->load->model('Projects');//loading model projects for checking the project is created by the user
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
        /*
         * Uploading Source sentences
         * ***text/plain file need which means file extension must be .txt***
         * A newline must be needed after each segment
         */
        $config['upload_path'] = './resources/files/'; //uploaded file path
        $config['allowed_types'] = 'txt'; //acceptable file type
        $this->load->library('upload', $config);//initialize upload library for uploading file
        $pId= $this->input->post('projectId');//get the project id
        if($this->upload->do_upload('sentences')){//if upload sentences is clicked
            $url=base_url('resources/files/').$_FILES['sentences']['name'];//getting file location/url
            $fh = fopen($url,'r');//openstream file
            while ($line = fgets($fh)) {//read file line by line
                $data = array(
                    'uId' => $this->input->post('userId'),
                    'projectId' => $pId,
                    'sentence' => $line,
                    'sCreation' => date('Y-m-d H:i:s', time())
                ); //prepare array for inserting into database
                $this->load->model('Projects');
                //print_r($data);
                $this->Projects->upload_sentences($data);//calling uploading_sentences function to insert into database
            }
            fclose($fh);//close file stream
            unlink('./resources/files/'.$_FILES['sentences']['name']);//deleting file
            $this->session->set_flashdata('file_succ','File Uploads Successfully');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }else{
            $this->session->set_flashdata('file_succ','Error in file uploading');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }
    }
    public function export_files(){
        /*
         * If Admin wants to export translated sentences
         * ***Export file format Comma Separated File(CSV) .csv***
         */
        $pId= $this->input->post('pId');
        $format = $this->input->post('format');
        //$config['upload_path'] = './resources/files/downloads/';
        //$config['allowed_types'] = $format;
        //$config['max_size'] = '200';
        $this->load->helper('file');//using file helper only export option works
        $this->load->model('Projects');
        $sentences = $this->Projects->export_sentences($pId);//getting both translated sentences and source sentences from database
        $source = $sentences['source'];
        $target = $sentences['target'];
        $url='';
        if($format=='csv'){
            $url='./resources/files/downloads/'.$pId.'.'.$format;//file location/url
            $fh = fopen("$url","w");//open file to write
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));//adding unicode character for Unicode text
            fputcsv($fh,array('Source Text', 'Translated Text'));
            foreach ($source->result() as $sentence){//adding data to *.csv file
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
            fclose($fh);//close stream
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
        unlink($url); //delete file from server
        exit();
    }
    public function upload_glossary(){
        /*
         * uploading glossary into glossary table
         */
        $config['upload_path'] = './resources/files/'; //uploaded file path
        $config['allowed_types'] = 'csv'; // file type which is allowed
        $config['max_size'] = '200'; // file max size 200KB
        $this->load->library('upload', $config);// Load upload library for uploading file
        $pId= $this->input->post('projectId');
        if($this->upload->do_upload('glossary')){
            $uId= $this->input->post('userId');
            $url=base_url('resources/files/').$_FILES['glossary']['name'];// file url for stream file
            $fh = fopen($url,'r');// stream file to read the data
            $i=0;
            while(! feof($fh)) {
                $line = fgetcsv($fh);//getting each row
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
                    ); //prepare data for inserting into database
                    $this->load->model('Projects');
                    $this->Projects->upload_glossary($data);
                }
                $i++;
            }
            fclose($fh);
            unlink('./resources/files/'.$_FILES['glossary']['name']);//delete file from server
            $this->session->set_flashdata('glo_succ','File Uploads Successfully');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }else{
            $this->session->set_flashdata('glo_succ','File is too big to upload');
            redirect(base_url().'ProjectSettings?project='.$pId);
        }
    }
    public function invite_user(){
        /*
         * invite user to collaborate into my project
         */
        $email = $this->input->post('email'); // get email address of the invited user
        $pId = $this->input->post('pId'); // user is invited for this project id
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