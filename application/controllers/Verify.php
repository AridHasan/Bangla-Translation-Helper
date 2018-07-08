<?php

class Verify extends CI_Controller
{
    public function index()
    {
        $this->load->model('Auth');
        if ($this->Auth->islogged() == true) {
            $uId = $_SESSION['uId'];
            if (array_key_exists('project', $_GET)) {
                $pId = $_GET['project'];
                $this->load->model('VerifyM');
                $user = $this->VerifyM->get_permission($uId, $pId);
                if($user==true) {
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
                    foreach ($sentences->result() as $row) {
                        if (!in_array($row->sId, $skip) and in_array($row->sId, $translate)) {
                            $data['sId'] = $row->sId;
                            $data['pId'] = $row->projectId;
                            $data['uId'] = $uId;
                            $data['source'] = $row->sourceSentence;//'মেসির সঙ্গে আমি খেলতে পেরেছি সেটা আমার জন্য বিশাল একটি সম্মান';//
                            //$data['source'] = 'পাঁচ দিন চলেছে অনুষ্ঠান';
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
        $pId = $this->input->post('project');
        $sId = $this->input->post('sentence');
        $uId = $this->input->post('user');
        $tId = $this->input->post('tId');
        $targetText = $this->input->post('targetText');
        $time = date("Y-m-d H:i:s", time());
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
            $this->VerifyM->skip_verification($data);
            redirect(base_url().'Verify?project='.$pId);
        }else if($this->input->post('valid')){
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
            $data['status'] = 'error';
            $this->VerifyM->verify_sentence($data);
            redirect(base_url().'Verify?project='.$pId);
        }
    }

    public function reject_previous(){
        //
    }
}