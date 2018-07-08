<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//require_once base_url('application/models/Auths/Users.php');
class Auth extends CI_Model
{
    public function send_code($user){
        $utilities = new Utilities();
        $response = $utilities->filter($user);
        $message = "Please follow the link below to reset the password for your account.\r\n\r\n
        If you haven't explicitly requested a password reset, you can ignore this request.";
        if($response == 'email'){
            $result = $this->get_userId($user);
            $code = $this->generate_activation_code($result);
            $message .= base_url().'ChangePassword?changeKey?'.$code;
            if($result != ''){
                $this->send_email($user,'AmaderInfo Change Password', $message);
                return true;
            }else{
                return false;
            }
        }else{
            $result = $this->get_email_by_username($user);
            if($result != ''){
                $code = $this->generate_activation_code($result['uId']);
                $message .= base_url().'ChangePassword?changeKey?'.$code;
                $this->send_email($result['email'],'AmaderInfo Change Password', $message);
                return true;
            }else{
                return false;
            }
        }
    }
    public function validate_key($key){
        $result = $this->db->query("SELECT * FROM activation_key WHERE activationKey='$key'");
        if($result->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
    public function get_userId($email){
        $result = $this->db->query("SELECT * FROM users WHERE email='$email'");
        if($result->num_rows() > 0){
            foreach ($result->result() as $row){
                return $row->uId;
            }
        }else{
            return '';
        }
    }
    public function get_email_by_username($username){
        $result = $this->db->query("SELECT * FROM users WHERE username='$username'");
        if($result->num_rows() > 0){
            foreach ($result->result() as $row){
                $arr = array(
                    'email' => $row->email,
                    'uId' => $row->uId
                );
                return $arr;
            }
        }else{
            return '';
        }
    }
    public function login($data){
        $utilities = new Utilities();
        $response = $utilities->filter($data['email']);
        if($response == 'email'){
            $result = $this->login_by_email($data['email']);
           if($result->num_rows() > 0){
               foreach ($result->result() as $row){
                   if($row->password == $data['password']){
                       return $result;
                   }else{
                       return 'in_pa';
                   }
               }
           }else{
               return 'in_em';
           }
        }else{
            $result = $this->login_by_username($data['email']);
            if($result->num_rows() >0){
                foreach ($result->result() as $row){
                    if($row->password == $data['password']){
                        return $result;
                    }else{
                        return 'in_pa';
                    }
                }
            }else{
                return 'in_un';
            }
        }
    }
    public function update_password($password, $key){
        $query = $this->db->query("SELECT * FROM activation_key WHERE activationKey='$key'");
        foreach ($query->result() as $row){
            $this->db->query("UPDATE users SET password='$password' WHERE uId='$row->uId'");
            $this->db->query("DELETE FROM activation_key WHERE uId='$row->uId'");
        }
        return true;
    }
    public function logout(){
        setcookie('amaderinfo[_id]','',(time()-3600),'/');
        setcookie('amaderinfo[_auth]','',(time()-3600),'/');
        redirect(base_url().'login');
    }
    public function islogged(){
        if(array_key_exists('amaderinfo', $_COOKIE)){
            $cookie = $_COOKIE['amaderinfo'];
            if(array_key_exists('_id', $cookie) and array_key_exists('_auth', $cookie)) {
                $uId = $cookie['_id'];
                $auth = $cookie['_auth'];
                $query = $this->db->query("SELECT * FROM users WHERE uId='$uId'");
                foreach ($query->result() as $row) {
                    if ($auth == md5($row->password)) {
                        $_SESSION['uId'] = $uId;
                        $_SESSION['userType'] = $row->userType;
                        return true;
                    } else {
                        return false;
                    }
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    public function send_email($to, $subject, $message){
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'amaderinfo7@gmail.com',
            'smtp_pass' => 'amaderinfo123',
            'mailtype'  => 'html',
            'charset'   => 'iso-8859-1'
        );
        $this->load->library('email', $config);
        $this->email->set_newline("\r\n");
        $this->email->from('no_reply@amaderinfo.com','no_reply@amaderinfo.com');
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
    public function set_cookies($data){
        setcookie('amaderinfo[_id]',$data['id'],(time()+604800),'/','','',true);
        setcookie('amaderinfo[_auth]',$data['auth'],(time()+604800),'/','','',true);
    }
    public function valid_email($email){
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $this->db->query($sql);
        if($result->num_rows() == 0){
            return false;
        }else {
            return true;
        }
    }
    public function valid_username($username){
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $this->db->query($sql);
        if($result->num_rows() == 0){
            return false;
        }else {
            return true;
        }
    }
    public function check_invited($email,$uId){
        $res = $this->db->query("SELECT * FROM inviteduser WHERE email='$email'");
        if($res->num_rows() > 0){
            foreach ($res->result() as $row){
                $this->db->query("INSERT INTO projectcollaboration VALUES ('','$row->pId','$uId','translator')");
                $this->db->query("DELETE FROM inviteduser WHERE email='$email'");
            }
        }
    }
    public function is_invited($email){
        $res = $this->db->query("SELECT * FROM inviteduser WHERE email='$email'");
        if($res->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
    public function registration($data){
        $user = new Users($data);
        $response = $user->save_user();
        $this->check_invited($data['email'],$response);
        $response = $this->generate_activation_code($response);
        if ($response == ''){
            return false;
        } else{
            $subject = 'AmaderInfo Account Confirmation';
            $message = "Dear Arid Hasan,\r\n\r\nPlease click on the following link to activate your account:\r\n";
            $message .= base_url().'AccountActivation?activationKey?'.$response."\r\n";
            $this->send_email($data['email'], $subject, $message);
            return true;
        }
    }
    public function login_by_username($username){
        $sql = "SELECT * FROM users WHERE username='$username'";
        return $this->db->query($sql);
    }
    public function login_by_email($email){
        $sql = "SELECT * FROM users WHERE email='$email'";
        return $this->db->query($sql);
    }
    function generate_activation_code($uId){
        $a='';
        for ($i = 0; $i<6; $i++)
        {
            $a .= mt_rand(0,9);
        }
        $a = md5($a);
        $sql = "INSERT INTO activation_key VALUES ('$uId','$a')";
        if($this->db->query($sql)){
            return $a;
        }else{
            return '';
        }
    }
    public function check_activation_key($key){
        $sql = "SELECT * FROM activation_key WHERE activationKey='$key'";
        $query = $this->db->query($sql);
        if($query->num_rows() == 1){
            foreach ($query->result() as $row){
                //$uId = $row->uId;
                $result = $this->db->query("UPDATE users SET status='active' WHERE uId='$row->uId'");
                if($result){
                    $this->db->query("DELETE FROM activation_key WHERE uId='$row->uId'");
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }
}
class Utilities{
    function __construct()
    {
    }
    function filter($data){
        if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }else{
            return 'username';
        }
    }
}

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
        /*if(! $this->db->query($sql)){
            return $this->db->error();
        }*/
        $link = new mysqli('localhost', 'root', '', 'amader');
        $response = $link->query($sql);
        if(! $response){
            return mysqli_error($link);
        }
        return $link->insert_id;
    }
}