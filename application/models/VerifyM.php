<?php

class VerifyM extends CI_Model
{
    /*
     * getting 1.source sentences list
     * 2. skip list by a user while he/she is verify any specific project
     * 3. list of translated sentences of this specific project
     * 4. list of verified sentences of this specific project
     */
    public function get_data($uId, $pId){
        $sql = "SELECT * FROM sentences WHERE projectId='$pId' ORDER BY sId ASC";
        $sentences = $this->db->query($sql);
        $sql = "SELECT * FROM verification_skip WHERE uId='$uId' AND projectId='$pId' ORDER BY sId ASC";
        $skips = $this->db->query($sql);
        $sql = "SELECT * FROM translated WHERE projectId='$pId' ORDER BY sId ASC";
        $translated = $this->db->query($sql);
        $sql = "SELECT * FROM verified_sentences WHERE pId='$pId' ORDER BY sId ASC";
        $verified = $this->db->query($sql);
        $data = array(
            'sentence' => $sentences,
            'skip' => $skips,
            'translated' => $translated,
            'verified' => $verified
        );
        return $data;
    }
    //get user permission for checking either the user will able to verify sentences or not
    public function get_permission($uId, $pId){
        $query = $this->db->query("SELECT * FROM projectcollaboration WHERE uId=$uId AND pId='$pId'");
        return $query;
    }
    //insert new data to verification_skip when a user clicked on skip button in verification page
    public function skip_verification($data){
        $sql = "INSERT INTO verification_skip VALUES ('','".$data['sId']."','".$data['uId']."','".$data['tId']."','".$data['pId']."')";
        $this->db->query($sql);
    }
    //insert new data to verified_sentences when a user clicked on verify button in verification page
    public function verify_sentence($data){
        $sql = "INSERT INTO verified_sentences VALUES ('','".$data['uId']."','".$data['sId']."','".$data['tId']."','".$data['pId']."','".$data['time']."','".$data['status']."')";
        $this->db->query($sql);
    }
    //checking the submitted translated sentence while verifying is previous sentence or new sentence
    public function is_previous($d){
        $sql = "SELECT * FROM translated WHERE projectId='".$d['pId']."' AND sId='".$d['sId']."' AND targetText like '".$d['target']."'";
        $result = $this->db->query($sql);
        if($result->num_rows() > 0){
            return true;
        }
        return false;
    }
    //if the submitted translated sentence while verifying is new sentence then system will insert the new sentence into database with 100% valid
    public function insert_new_translation($data){
        $sql = "INSERT INTO translated VALUES ('','".$data['sId']."','".$data['uId']."','".$data['pId']."','".$data['target']."','".$data['time']."')";
        $this->db->query($sql);
        return $this->db->insert_id();
    }
}