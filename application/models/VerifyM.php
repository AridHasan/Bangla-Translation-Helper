<?php

class VerifyM extends CI_Model
{
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
    public function get_permission($uId, $pId){
        $query = $this->db->query("SELECT * FROM projectcollaboration WHERE uId=$uId AND pId='$pId'");
        return $query;
    }
    public function skip_verification($data){
        $sql = "INSERT INTO verification_skip VALUES ('','".$data['sId']."','".$data['uId']."','".$data['tId']."','".$data['pId']."')";
        $this->db->query($sql);
    }
    public function verify_sentence($data){
        $sql = "INSERT INTO verified_sentences VALUES ('','".$data['uId']."','".$data['sId']."','".$data['tId']."','".$data['pId']."','".$data['time']."','".$data['status']."')";
        $this->db->query($sql);
    }
    public function is_previous($d){
        $sql = "SELECT * FROM translated WHERE projectId='".$d['pId']."' AND sId='".$d['sId']."' AND targetText like '".$d['target']."'";
        $result = $this->db->query($sql);
        if($result->num_rows() > 0){
            return true;
        }
        return false;
    }
    public function insert_new_translation($data){
        $sql = "INSERT INTO translated VALUES ('','".$data['sId']."','".$data['uId']."','".$data['pId']."','".$data['target']."','".$data['time']."')";
        $this->db->query($sql);
        return $this->db->insert_id();
    }
}