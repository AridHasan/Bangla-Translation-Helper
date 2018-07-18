<?php

class Verify extends CI_Controller
{
    /*
     * Translation verification by expert user
     */
    public function index()
    {
        $this->load->model('Auth');
        if ($this->Auth->islogged() == true) {//checking the user is logged in or not
            $uId = $_SESSION['uId'];// get the user id from session data
            if (array_key_exists('project', $_GET)) {
                $pId = $_GET['project'];
                $this->load->model('VerifyM');
                $user = $this->VerifyM->get_permission($uId, $pId);//checking user permission either user could verify or not
                if($user==true) {
                    /*
                     * Getting the all the log of a specific user and specific project
                     * 1. all the sentences of selected project
                     * 2. all the translated sentences of the selected project
                     * 3. all the skipped sentences by the user of this selected project
                     * 4. all the verified sentences of the selected project
                     */
                    $response = $this->VerifyM->get_data($uId, $pId);
                    $sentences = $response['sentence'];
                    $skips = $response['skip'];
                    $translated = $response['translated'];
                    $verified = $response['verified'];
                    $skip = [];
                    if (!empty($skips)) {
                        foreach ($skips->result() as $row) {
                            array_push($skip, $row->sId);
                        }
                    }
                    $verify = [];
                    if (!empty($verified)) {
                        foreach ($verified->result() as $row) {
                            array_push($verify, $row->sId);
                        }
                    }
                    $translate = [];
                    $t_sentences = [];
                    $t_id = [];
                    if (!empty($translated)) {
                        foreach ($translated->result() as $row) {
                            if (!in_array($row->sId, $verify)) {
                                array_push($translate, $row->sId);
                                array_push($t_sentences, $row->targetText);
                                array_push($t_id, $row->tId);
                            }
                        }
                    }
                    $data = [];
                    $i = 0;
                    /*
                     * prepare a single translated sentence for showing to the user
                     */
                    foreach ($sentences->result() as $row) {
                        if (!in_array($row->sId, $skip) and in_array($row->sId, $translate)) {
                            $data['sId'] = $row->sId;
                            $data['pId'] = $row->projectId;
                            $data['uId'] = $uId;
                            $data['source'] = $row->sourceSentence;
                            $data['target'] = $t_sentences[$key = array_search($row->sId, $translate)];
                            $data['tId'] = $t_id[$key = array_search($row->sId, $translate)];
                            break;
                        }
                        $i++;
                    }
                    $this->load->view('verify_v', $data);
                }else{
                    redirect(base_url().'Dashboard');
                }
            }else{
                redirect(base_url().'Dashboard');
            }
        }else{
            redirect(base_url().'login');
        }
    }

    //Verify translated Sentences

    public function verifying(){
        /*
         * When any kind of Action/Click event occurs then this function will work
         */
        $pId = $this->input->post('project');//getting project id
        $sId = $this->input->post('sentence');//getting source sentence
        $uId = $this->input->post('user');//getting user id
        $tId = $this->input->post('tId');// getting translated id
        $targetText = $this->input->post('targetText');// getting target text
        $time = date("Y-m-d H:i:s", time());// submitted time
        $data = array(
            'pId' => $pId,
            'sId' => $sId,
            'uId' => $uId,
            'tId' => $tId,
            'target' => $targetText,
            'time' => $time
        );
        $this->load->model('VerifyM');
        $response = $this->VerifyM->is_previous($data);
        if($this->input->post('skip')){
            //if skip button is clicked
            $this->VerifyM->skip_verification($data);
            redirect(base_url().'Verify?project='.$pId);
        }else if($this->input->post('valid')){
            //if valid button is clicked
            if($response == false){
                $data['status'] = 'error';
                $this->VerifyM->verify_sentence($data);
                $tId = $this->VerifyM->insert_new_translation($data);
                $data['tId']=$tId;
            }
            $data['status'] = 'valid';
            $this->VerifyM->verify_sentence($data);
            redirect(base_url().'Verify?project='.$pId);
        }else if($this->input->post('invalid')){
            //if invalid button is clicked
            $data['status'] = 'error';
            $this->VerifyM->verify_sentence($data);
            redirect(base_url().'Verify?project='.$pId);
        }
    }
}