<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//require_once base_url('application/models/Auths/Users.php');
class Auth extends CI_Model
{
    /*
     * All the function or work which is related to the user authentication will be found here
     */
    public function send_code($user){
        /*
         * Send_code function is used for 'forgot password' option
         */
        $utilities = new Utilities();
        $response = $utilities->filter($user);
        $message = "Please follow the link below to reset the password for your account.\r\n\r\n
        If you haven't explicitly requested a password reset, you can ignore this request."; // email body which will be send to user
        if($response == 'email'){
            $result = $this->get_userId($user);// getting user id by email
            $code = $this->generate_activation_code($result); //calling the 6 digit code generation method
            $message .= base_url().'ChangePassword?changeKey?'.$code; // add url to the message body
            if($result != ''){
                $this->send_email($user,'AmaderInfo Change Password', $message); // send email to the requested user
                return true;
            }else{
                return false;
            }
        }else{
            $result = $this->get_email_by_username($user); //getting email by username
            if($result != ''){
                $code = $this->generate_activation_code($result['uId']);//calling the 6 digit code generation method
                $message .= base_url().'ChangePassword?changeKey?'.$code; // add url to the message body
                $this->send_email($result['email'],'AmaderInfo Change Password', $message);// send email to the requested user
                return true;
            }else{
                return false;
            }
        }
    }
    public function validate_key($key){
        /*
         * verify the given key is valid or not for both account activation key and change password confirmation key
         */
        $result = $this->db->query("SELECT * FROM activation_key WHERE activationKey='$key'"); // sql query for select the row if the key is present
        if($result->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
    public function get_userId($email){
        /*
         * all the database table are interconnected by primary key
         * so we need to find users table primary key if email address given
         */
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
        /*
         * all the database table are interconnected by primary key
         * so we need to find users table primary key and email for sending email if username given
         */
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
        /*
         * Matches between databased stored data and html submitted form data
         */
        $utilities = new Utilities();
        $response = $utilities->filter($data['email']); // checking the given data is email address or username
        if($response == 'email'){
            $result = $this->login_by_email($data['email']); // login by email
           if($result->num_rows() > 0){
               foreach ($result->result() as $row){
                   if($row->password == $data['password']){ //password matching
                       return $result;
                   }else{
                       return 'in_pa';
                   }
               }
           }else{
               return 'in_em';
           }
        }else{
            $result = $this->login_by_username($data['email']); // login by username
            if($result->num_rows() >0){
                foreach ($result->result() as $row){
                    if($row->password == $data['password']){ //password matching
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
        /*
         * updating password for 'forgot password option'
         */
        $query = $this->db->query("SELECT * FROM activation_key WHERE activationKey='$key'");// getting user id by activation key
        foreach ($query->result() as $row){
            $this->db->query("UPDATE users SET password='$password' WHERE uId='$row->uId'"); //update database stored password
            $this->db->query("DELETE FROM activation_key WHERE uId='$row->uId'");//delete activation key from database
        }
        return true;
    }
    public function logout(){
        //set cookies time 7days earlier and redirect to login page
        setcookie('amaderinfo[_id]','',(time()-3600),'/');
        setcookie('amaderinfo[_auth]','',(time()-3600),'/');
        redirect(base_url().'login');
    }
    public function islogged(){
        /*
         * checking the user is logged in or not
         * if a user is logged in then the browser will keep trace of cookies and system will matches cookie data for ensure user login
         */
        if(array_key_exists('amaderinfo', $_COOKIE)){ //checking the cookie is exists in client browser side
            $cookie = $_COOKIE['amaderinfo'];
            if(array_key_exists('_id', $cookie) and array_key_exists('_auth', $cookie)) { //checking every cookie key exists or not
                $uId = $cookie['_id'];
                $auth = $cookie['_auth'];
                $query = $this->db->query("SELECT * FROM users WHERE uId='$uId'"); //grab user data from password
                foreach ($query->result() as $row) {
                    if ($auth == md5($row->password)) { //matching auth data for verification. either cookie is set by system or not
                        $_SESSION['uId'] = $uId;// set session data
                        $_SESSION['userType'] = $row->userType;//set session data
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
        /*
         * sending mail for account confirmation, change password etc
         */
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_port' => 465,
            'smtp_user' => 'amaderinfo7@gmail.com',
            'smtp_pass' => 'amaderinfo123',
            'mailtype'  => 'html',
            'charset'   => 'iso-8859-1'
        );
        $this->load->library('email', $config); //load email library for sending email
        $this->email->set_newline("\r\n");
        $this->email->from('no_reply@amaderinfo.com','no_reply@amaderinfo.com');
        $this->email->to($to); //which user will get the email
        $this->email->subject($subject); //email subject
        $this->email->message($message); //email body
        $this->email->send(); //sending message
    }
    public function set_cookies($data){
        /*
         * set cookies in client browser side for 7days.
         */
        setcookie('amaderinfo[_id]',$data['id'],(time()+604800),'/','','',true);
        setcookie('amaderinfo[_auth]',$data['auth'],(time()+604800),'/','','',true);
    }
    public function valid_email($email){
        /*
         * validate email address for new user registration and forgot password
         */
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $this->db->query($sql);
        if($result->num_rows() == 0){
            return false;
        }else {
            return true;
        }
    }
    public function valid_username($username){
        /*
         * validate username for new user registration
         */
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $this->db->query($sql);
        if($result->num_rows() == 0){
            return false;
        }else {
            return true;
        }
    }
    public function check_invited($email,$uId){
        /*
         * insert data into project collaboration and delete row from invite user
         * this function just work after the user registration is clicked
         */
        $res = $this->db->query("SELECT * FROM inviteduser WHERE email='$email'");
        if($res->num_rows() > 0){
            foreach ($res->result() as $row){
                $this->db->query("INSERT INTO projectcollaboration VALUES ('','$row->pId','$uId','translator')");
                $this->db->query("DELETE FROM inviteduser WHERE email='$email'");
            }
        }
    }
    public function is_invited($email){
        /*
         * checking the email address is invited or not
         */
        $res = $this->db->query("SELECT * FROM inviteduser WHERE email='$email'");
        if($res->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
    public function registration($data){
        /*
         * save user data in users table
         */
        $user = new Users($data);//prepare user data according to database table column
        $response = $user->save_user(); //calling the function which will save the user data into database
        $this->check_invited($data['email'],$response);
        $response = $this->generate_activation_code($response); //generate activation code
        if ($response == ''){
            return false;
        } else{
            /*
             * sending mail to newly registered user for his/her email verification
             */
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