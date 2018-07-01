<?php
?>

<html>
    <head>
        <title>Registration | AmaderInfo</title>
        <script src="<?php echo base_url('resources/jquery.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo base_url('resources/bootstrap.min.css')?>" type="text/css" rel="stylesheet">
        <script src="<?php echo base_url('resources/bootstrap.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo base_url('resources/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                $("#conf_pass").focusout(function () {
                    var pass = document.getElementById("password").value;
                    var conf_pass = document.getElementById("conf_pass").value;
                    if(conf_pass != pass){
                        document.getElementById("conf_err").innerText = 'Password Doesn\'t match';
                        $('#reg').attr('disabled', true);
                    }else{
                        document.getElementById("conf_err").innerText = '';
                        $('#reg').attr('disabled', false);
                    }
                });
                $("#conf_pass").keyup(function () {
                    var pass = document.getElementById("password").value;
                    var conf_pass = document.getElementById("conf_pass").value;
                    if(conf_pass != pass.substring(0, conf_pass.length)){
                        document.getElementById("conf_err").innerText = 'Password Doesn\'t match';
                        $('#reg').attr('disabled', true);
                    }else{
                        document.getElementById("conf_err").innerText = '';
                        $('#reg').attr('disabled', false);
                    }
                });
                $("#email").focusout(function () {
                    var email = document.getElementById('email').value;
                    var re = /^\S+@\S+$/;
                    if(re.test(String(email).toLowerCase())){
                        //AJAX WILL PERFORM HERE
                        $.ajax({
                            type: "post",
                            url: "<?php echo base_url();?>registration/validate_email",
                            data: {email:email},
                            success: function (data) {
                                if(data == true){
                                    document.getElementById("email_err").innerText = 'Email address already exists';
                                    $('#reg').attr('disabled', true);
                                }else {
                                    document.getElementById("email_err").innerText = '';
                                    $('#reg').attr('disabled', false);
                                }
                            }
                        });
                        document.getElementById("email_err").innerText = '';
                        $('#reg').attr('disabled', false);
                    }else{
                        document.getElementById("email_err").innerText = 'Email Address isn\'t valid';
                        $('#reg').attr('disabled', true);
                    }
                });
                $("#username").focusout(function () {
                    //AJAX WILL PERFORM HERE
                    var username = document.getElementById('username').value;
                    if(username.length >=4){
                        $.ajax({
                            type: "post",
                            url: "<?php echo base_url();?>registration/validate_username",
                            data: {username:username},
                            success: function (data) {
                                if(data == true){
                                    document.getElementById("usr_err").innerText = 'Username already exists';
                                    $('#reg').attr('disabled', true);
                                }else {
                                    document.getElementById("usr_err").innerText = '';
                                    $('#reg').attr('disabled', false);
                                }
                            }
                        });
                    }else if (username.length != 0){
                        document.getElementById("usr_err").innerText = 'Username must be minimum 4 character';
                        $('#reg').attr('disabled', true);
                    }else{
                        document.getElementById("usr_err").innerText = '';
                        $('#reg').attr('disabled', false);
                    }
                });
                $("#password").keyup(function () {
                    var pass = document.getElementById("password").value;
                    if(pass.length >= 6){
                        document.getElementById("pass_err").innerText = '';
                        $('#reg').attr('disabled', false);
                    }else{
                        document.getElementById("pass_err").innerText = 'Password must be minimum 6 characters';
                        $('#reg').attr('disabled', true);
                    }
                });
                $("#password").focusout(function () {
                    var pass = document.getElementById("password").value;
                    if(pass.length >= 6){
                        document.getElementById("pass_err").innerText = '';
                        $('#reg').attr('disabled', false);
                    }else{
                        document.getElementById("pass_err").innerText = 'Password must be minimum 6 characters';
                        $('#reg').attr('disabled', true);
                    }
                });
            });
        </script>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <div class="container" style="margin-top: 125px;">
            <div class="row">
                <div class="col-sm-6 offset-sm-3">
                    <div class="card text-center">
                        <div class="card text-center">
                            <div class="text-primary text-center">
                                <h4>Sign Up</h4>
                                <p class="text-danger"><?php echo $this->session->flashdata('reg_pass'); ?></p>
                            </div>
                            <form class="form-group" method="post" action="<?php echo base_url();?>registration/register">
                                <div style="margin-left: 35px;">
                                    <div class="form-inline form-group">
                                        <input type="text" name="fname" id="fname" placeholder="First Name ex: Arid" class="form-control col-sm-5" required>
                                        <input type="text" name="lname" id="lname" placeholder="Last Name ex: Hasan" class="form-control col-sm-5 offset-sm-1">
                                    </div>
                                    <div class="form-group" style=" margin-right: 40px;">
                                        <input type="email" name="email" id="email" value="<?php echo $email; ?>" placeholder="Email ex: example@example.com" class="form-control" required>
                                        <p class="text-danger" id="email_err"></p>
                                    </div>
                                    <div class="form-group" style=" margin-right: 40px;">
                                        <input type="text" title="Username must be minimum 4 character" name="username" id="username" placeholder="Username ex: abc123" class="form-control">
                                        <p class="text-danger" id="usr_err"></p>
                                    </div>
                                    <div class="form-inline text-left form-group" style=" margin-right: 40px;">
                                        <div class="col-sm-4">
                                            <input type="radio" name="gender" value="female" class="form-control" required> Female
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="radio" name="gender" value="male" class="form-control" required> Male
                                        </div>
                                    </div>
                                    <div class="form-group" style=" margin-right: 40px;">
                                        <input type="password" name="password" id="password" placeholder="Password" class="form-control" required>
                                        <p class="text-danger" id="pass_err"></p>
                                    </div>
                                    <div class="form-group" style=" margin-right: 40px;">
                                        <input type="password" name="conf_pass" id="conf_pass" placeholder="Confirm Password" class="form-control" required>
                                        <p class="text-danger" id="conf_err"></p>
                                    </div>
                                    <div class="text-center" style=" margin-right: 40px;">
                                        <button type="submit" name="reg" id="reg" class="btn btn-primary">Sign Up</button>
                                        <p>Already have an account? <a href="<?php echo str_replace('/index.php','',site_url('login'));?>" class=""> Login</a></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
