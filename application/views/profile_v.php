<?php
?>

<html>
<head>
    <title>Profile | AmaderInfo</title>
    <script src="<?php echo base_url('resources/jquery.js'); ?>" type="text/javascript"></script>
    <link href="<?php echo base_url('resources/bootstrap.min.css')?>" type="text/css" rel="stylesheet">
    <link href="<?php echo base_url('resources/fontA/css/font-awesome.css')?>" type="text/css" rel="stylesheet">
    <script src="<?php echo base_url('resources/bootstrap.min.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo base_url('resources/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
    <style>
        .row, .col-sm-1{
            margin: 0px;
            padding: 0px;
        }
        .side_nav{
            margin: 0;
            padding: 0;
            list-style: none;
        }
    </style>
    <script>
        $(document).ready(function () {
            $("#updateName").click(function () {
                var fname = document.getElementById('fname').value;
                var lname = document.getElementById('lname').value;
                var uId = document.getElementById('uId').value;
                if(lname.length > 0){
                    $.ajax({
                        type: "post",
                        url: "<?php echo base_url();?>Profile/update_name",
                        data: {fname:fname, lname:lname, uId:uId},
                        success: function (data) {
                            if(data==true){
                                document.getElementById("up_succ").innerText = 'Name updated successfully';
                            }else{
                                document.getElementById("up_err").innerText = 'Something went wrong. Try again later.';
                            }
                        }
                    });
                }else{
                    document.getElementById("fn_err").innerText = 'First Name required';
                }
            });
            $("#oldPass").focusout(function () {
                var oldPass = document.getElementById('oldPass').value;
                var uId = document.getElementById('uId').value;
                $.ajax({
                    type: "post",
                    url: "<?php echo base_url();?>Profile/check_password",
                    data: {oPass:oldPass, uId:uId},
                    success: function (data) {
                        if(data==true){
                            document.getElementById("up_succ").innerText = '';
                            document.getElementById("pass_err").innerText = '';
                            $('#changePass').attr('disabled', false);
                        }else{
                            document.getElementById("pass_err").innerText = 'Password doesn\'t match';
                            $('#changePass').attr('disabled', true);
                        }
                    }
                });
            });
            $("#newPass").keyup(function () {
                var pass = document.getElementById("newPass").value;
                if(pass.length >= 6){
                    document.getElementById("pass_err").innerText = '';
                    $('#changePass').attr('disabled', false);
                }else{
                    document.getElementById("pass_err").innerText = 'Password must be minimum 6 characters';
                    $('#changePass').attr('disabled', true);
                }
            });
            $("#newPass").focusout(function () {
                var pass = document.getElementById("newPass").value;
                if(pass.length >= 6){
                    document.getElementById("pass_err").innerText = '';
                    $('#changePass').attr('disabled', false);
                }else{
                    document.getElementById("pass_err").innerText = 'Password must be minimum 6 characters';
                    $('#changePass').attr('disabled', true);
                }
            });
            $("#conNewPass").keyup(function () {
                var pass = document.getElementById("newPass").value;
                var conf_pass = document.getElementById("conNewPass").value;
                if(conf_pass != pass.substring(0, conf_pass.length)){
                    document.getElementById("conf_err").innerText = 'Password Doesn\'t match';
                    $('#changePass').attr('disabled', true);
                }else{
                    document.getElementById("conf_err").innerText = '';
                    $('#changePass').attr('disabled', false);
                }
            });
            $("#conNewPass").focusout(function () {
                var pass = document.getElementById("newPass").value;
                var conf_pass = document.getElementById("conNewPass").value;
                if(conf_pass != pass){
                    document.getElementById("conf_err").innerText = 'Password Doesn\'t match';
                    $('#changePass').attr('disabled', true);
                }else{
                    document.getElementById("conf_err").innerText = '';
                    $('#changePass').attr('disabled', false);
                }
            });
            $("#changePass").click(function () {
                var oldPass = document.getElementById('oldPass').value;
                var newPass = document.getElementById('newPass').value;
                var conPass = document.getElementById('conNewPass').value;
                var uId = document.getElementById('uId').value;
                if(newPass == conPass){
                    $.ajax({
                        type: "post",
                        url: "<?php echo base_url();?>Profile/update_password",
                        data: {oldPass:oldPass, newPass:newPass, uId:uId},
                        success: function (data) {
                            if(data==true){
                                document.getElementById("up_succ").innerText = 'Password updated successfully';
                            }else{
                                document.getElementById("up_err").innerText = 'Something went wrong. Try again later.';
                            }
                        }
                    });
                }else{
                    document.getElementById("conf_err").innerText = 'Password Doesn\'t match';
                }
            });
        });
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
</head>
<body>
    <div class="row">
        <div class="col-sm-1 bg-primary">
                <nav class="navbar navbar-expand-sm navbar-light" style="margin-top: 10%;">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                        <ul class="side_nav mr-auto mt-2 mt-lg-0">
                            <li class="nav-item active">
                                <a class="nav-link" href="<?php echo base_url().'dashboard';?>">
                                    <i class="fa fa-home" style="color: white;font-size: 30px;"></i>
                                    <p style="color: white;">Home</p>
                                    <span class="sr-only">(current)</span></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo base_url().'AllProjects';?>" class="nav-link">
                                    <i class="fa fa-search" style="color: white;font-size: 30px;"></i>
                                    <p style="color: white;">All Projects</p></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo base_url().'profile';?>" class="nav-link">
                                    <i class="fa fa-user" style="color: white;font-size: 30px;"></i>
                                    <p style="color: white;">Profile</p></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo base_url().'glossary';?>" class="nav-link">
                                    <i class="fa fa-newspaper-o" style="color: white;font-size: 30px;"></i>
                                    <p style="color: white;">Glossary</p></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo base_url().'login/logout';?>" class="nav-link">
                                    <svg viewBox="0 0 35 45" width="50px" height="40px" style="fill: white;">
                                        <path d="M14.531 1.406q-2.698.271-5.12 1.49T5.156 6.125q-1.802 1.979-2.802 4.49Q1.333 13.209 1.333 16q0 2.99 1.156 5.708 1.073 2.573 3.135 4.667 2.094 2.063 4.667 3.135 2.719 1.156 5.708 1.156 2.792 0 5.385-1.021 2.51-1 4.49-2.802 2.01-1.833 3.229-4.255t1.49-5.12q.052-.594-.339-1.031t-.995-.438q-.51 0-.891.344t-.432.854q-.219 2.219-1.214 4.198t-2.651 3.479q-1.615 1.49-3.667 2.292-2.125.833-4.406.833-2.448 0-4.667-.938-2.104-.885-3.823-2.573-1.688-1.719-2.573-3.823-.938-2.219-.938-4.667 0-2.281.833-4.406.802-2.052 2.292-3.667 1.5-1.656 3.479-2.651t4.198-1.214q.51-.052.854-.432t.344-.891q0-.396-.151-.677t-.401-.417-.474-.188-.443-.052zm14.802-.073h-8q-.552 0-.943.391t-.391.943.391.943.943.391h4.781L15.051 15.064q-.385.375-.385.938 0 .573.38.953t.953.38q.563 0 .948-.385L27.999 5.887v4.781q0 .552.391.943t.943.391.943-.391.391-.943v-8q0-.552-.391-.943t-.943-.391z">
                                        </path>
                                    </svg>
                                    <p style="color: white; margin-top: -10px;">Logout</p></a>
                            </li>
                        </ul>
                    </div>
                </nav>
        </div>
        <div class="col-sm-11" style="height: 100%;">
            <div class="col-sm-10 offset-sm-1" style="margin-top: 20px;">
                <div class="card" style="width: 100%;">
                    <h4 class="card-title text-center">Profile Settings</h4>
                    <hr style="color: lightgray;"/>
                    <?php foreach ($user->result() as $row){ ?>
                    <div class="form-group">
                        <input type="hidden" name="uId" id="uId" value="<?php echo $row->uId; ?>">
                        <p id="up_succ" class="text-success text-center"></p>
                        <p id="up_err" class="text-danger text-center"></p>
                        <div class="form-inline">
                            <div class="col-sm-6">
                                <p>First Name</p>
                                <input type="text" class="form-control" id="fname" placeholder="First Name" value="<?php echo $row->fname; ?>">
                                <p id="fn_err" class="text-danger"></p>
                            </div>
                            <div class="col-sm-6">
                                <p>Last Name</p>
                                <input type="text" class="form-control" id="lname" placeholder="Last Name" value="<?php echo $row->lname; ?>">
                            </div>
                        </div>
                        <div class="form-group col-sm-12" style="margin-top: 10px;">
                            <button type="submit" id="updateName" class="btn btn-primary">Update Name</button>
                        </div>
                    </div>
                    <div style="margin-left: 15px;">
                        <div class="card-text" style="margin-bottom: 5px;">
                            <p style="font-size: 16px;"><b>Email</b></p>
                            <p><i class="fa fa-envelope"></i> <?php echo $row->email; ?></p>
                        </div>
                        <div class="card-text" style="margin-bottom: 5px;">
                            <p style="font-size: 16px;"><b>Username</b></p>
                            <p><i class="fa fa-user"></i> <?php echo $row->username; ?></p>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="form-group">
                        <h5 style="margin-left: 15px;">Change Password</h5>
                        <div class="form-group">
                            <div class="form-group col-sm-9">
                                <input type="password" class="form-control fa fa-lock" id="oldPass" placeholder="&#xf023; Old Password">
                                <p id="pass_err" class="text-danger text-center"></p>
                            </div>
                            <div class="form-group col-sm-9">
                                <input type="password" class="form-control fa fa-lock" id="newPass" placeholder="&#xf023; New Password">
                            </div>
                            <div class="form-group col-sm-9">
                                <input type="password" class="form-control fa fa-lock" id="conNewPass" placeholder="&#xf023; Confirm New Password">
                                <p id="conf_err" class="text-danger text-center"></p>
                            </div>
                        </div>
                        <div class="form-group col-sm-12" style="margin-top: 10px;">
                            <button type="submit" id="changePass" class="btn btn-primary">Change Password</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
