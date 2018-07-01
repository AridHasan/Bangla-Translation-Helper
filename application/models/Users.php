<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Users
{
    /*
     * FirstName(fname), Email, Gender, Password, acCreation, Status, UserType Must be Needed
     */
    private $uId, $fname, $lname, $email, $gender, $username, $birthdate, $phone, $bio, $password, $address, $lastLogin, $acCreation, $status, $userType;
    function __construct($data)
    {
        if(array_key_exists('uId', $data)){
            $this->uId = $data['uId'];
        }else{
            $this->uId = '';
        }
        if(array_key_exists('fname',$data)){
            $this->fname = $data['fname'];
        }else{
            $this->fname = '';
        }
        if(array_key_exists('lname',$data)){
            $this->lname = $data['lname'];
        }else{
            $this->lname = '';
        }
        if(array_key_exists('email',$data)){
            $this->email = $data['email'];
        }else{
            $this->email = '';
        }
        if(array_key_exists('gender',$data)){
            $this->gender = $data['gender'];
        }else{
            $this->gender = '';
        }
        if(array_key_exists('username',$data)){
            $this->username = $data['username'];
        }else{
            $this->username = '';
        }
        if(array_key_exists('birthdate',$data)){
            $this->birthdate = $data['birthdate'];
        }else{
            $this->birthdate = '';
        }
        if(array_key_exists('phone',$data)){
            $this->phone = $data['phone'];
        }else{
            $this->phone = '';
        }
        if(array_key_exists('bio',$data)){
            $this->bio = $data['bio'];
        }else{
            $this->bio = '';
        }
        if(array_key_exists('password',$data)) {
            $this->password = $data['password'];
        }else{
            $this->password = '';
        }
        if(array_key_exists('address',$data)){
            $this->address = $data['address'];
        }else{
            $this->address = '';
        }
        if(array_key_exists('lastLogin',$data)){
            $this->lastLogin = $data['lastLogin'];
        }else{
            $this->lastLogin = '';
        }
        if(array_key_exists('acCreation', $data)) {
            $this->acCreation = $data['acCreation'];
        }else{
            $this->acCreation = date('Y-m-d H:i:s', time());
        }
        if(array_key_exists('status', $data)) {
            $this->status = $data['status'];
        }else{
            $this->status = 'inactive';
        }
        if(array_key_exists('userType', $data)) {
            $this->userType = $data['userType'];
        }else{
            $this->userType = 'user';
        }
    }

    function save_user(){
        $sql = "INSERT INTO users VALUES ('','$this->fname','$this->lname','$this->email','$this->gender','$this->username','$this->birthdate','".
            "$this->phone','$this->bio','$this->password','$this->address','$this->lastLogin','$this->acCreation','$this->status','$this->userType')";
        if(! $this->db->query($sql)){
            return $this->db->error();
        }
        return true;
    }
}