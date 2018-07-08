<?php
?>

<html>
    <head>
        <title>Dashboard | AmaderInfo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <script src="<?php echo base_url('resources/jquery.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo base_url('resources/bootstrap.min.css')?>" type="text/css" rel="stylesheet">
        <link href="<?php echo base_url('resources/fontA/css/font-awesome.css')?>" type="text/css" rel="stylesheet">
        <script src="<?php echo base_url('resources/bootstrap.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo base_url('resources/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
        <style>
            .row, .col-sm-1, .col-sm-4{
                margin: 0px;
                padding: 0px;
            }
            .side_nav{
                margin: 0;
                padding: 0;
                list-style: none;
            }
            .col-sm-4, .col-sm-8{
                float: left;
            }
            .col-sm-8{
                padding-right: 0;
            }
        </style>
        <script>
            $(document).ready(function () {
                /*$("#plus_btn").click(function () {
                    var data = '<form class="form-group" method="post" action=""> <div class="form-group">'+
                        '<input type="text" id="projectName" name="projectName" class="form-control" placeholder="Project Name">'+
                        '<p id="pname_err" class="text-danger"></p></div>'+
                        '<div class="form-group">'+
                        '<input type="text" id="projectId" name="projectId" class="form-control" placeholder="Project ID">'+
                        '<p id="pId_err" class="text-danger"></p></div>'+
                        '<div class="form-group">'+
                        '<textarea class="form-control" name="description" rows="3" placeholder="Project Description"></textarea></div>'+
                        '<div class="form-group">'+
                        '<button type="submit" id="cProject" name="createP" class="btn btn-primary">Create Project</button> </div> </form>';
                    document.getElementById("create").innerHTML=data;
                });*/
                $("#projectName").focusout(function () {
                    var name = document.getElementById("projectName").value;
                    if(name.length > 0){
                        document.getElementById("pname_err").innerText = '';
                        $('#cProject').attr('disabled', false);
                    }else{
                        document.getElementById("pname_err").innerText = 'Project Name Required';
                        $('#cProject').attr('disabled', true);
                    }
                });
                $("#projectName").keyup(function () {
                    var name = document.getElementById("projectName").value;
                    if(name.length > 0){
                        document.getElementById("pname_err").innerText = '';
                        $('#cProject').attr('disabled', false);
                    }
                });
                $("#projectId").focusout(function () {
                    var name = document.getElementById("projectId").value;
                    if(name.length > 0){
                        document.getElementById("pId_err").innerText = '';
                        $('#cProject').attr('disabled', false);
                    }else{
                        document.getElementById("pId_err").innerText = 'Project ID Required';
                        $('#cProject').attr('disabled', true);
                    }
                });
                $("#projectId").keyup(function () {
                    var pId = document.getElementById("projectId").value;
                    var reg = /\s/g;
                    if(reg.test(String(pId))){
                        document.getElementById("pId_err").innerText = 'Project ID contains no whitespaces';
                        $('#cProject').attr('disabled', true);
                    }else{
                        $.ajax({
                            type: "post",
                            url: "<?php echo base_url();?>dashboard/validate_projectId",
                            data: {pId:pId},
                            success: function (data) {
                                if(data == true){
                                    document.getElementById("pId_err").innerText = 'Project ID already exists';
                                    $('#reg').attr('disabled', true);
                                }else {
                                    document.getElementById("pId_err").innerText = '';
                                    $('#reg').attr('disabled', false);
                                }
                            }
                        });
                    }
                });
            });
        </script>
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
            <div class="col-sm-11" style="height: 100%">
                <div class="col-sm-12 text-left" style="margin-top: 20px;">
                    <div class="">
                        <h3><?php echo $name; ?></h3>
                        <p><i class="fa fa-user"></i> <?php echo $username; ?></p>
                        <hr/>
                    </div>
                </div>
                <div class="col-sm-12 text-center" style="height: 80%;">
                    <?php if($_SESSION['userType'] == 'admin'){ ?>
                    <div class="col-sm-4">
                        <div class="text-center" style="border: 1px solid lightgray; height: auto;">
                            <h5 class="card-title text-center">Create Project</h5>
                            <button type="submit" id="plus_btn" class="btn btn-primary"><i class="fa fa-plus"></i> New Project</button>
                            <br/><br/>
                            <p class="text-warning"><?php echo $this->session->flashdata('p_err'); ?></p>
                            <div style="margin-left: 5px; margin-right: 5px;" id="create">
                                <form class="form-group" method="post" action="<?php echo base_url()."dashboard/create_project";?>">
                                    <div class="form-group">
                                        <input type="text" id="projectName" name="projectName" class="form-control" placeholder="Project Name">
                                        <p id="pname_err" class="text-danger"></p></div>
                                    <div class="form-group">
                                        <input type="text" id="projectId" name="projectId" class="form-control" placeholder="Project ID">
                                        <p id="pId_err" class="text-danger"></p></div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="description" rows="3" placeholder="Project Description(Optional)"></textarea>
                                    </div>
                                    <div class="form-inline form-group">
                                        <div class="form-inline">
                                        <div class="col-sm-6">
                                            <input type="radio" name="status" id="status" value="public" class="form-control" required> Public(Open for All)
                                        </div>
                                        <div class="col-sm-6">
                                            <input type="radio" name="status" id="status" value="private" class="form-control" required> Private(Only Invited user)
                                        </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" id="cProject" name="cProject" class="btn btn-primary">Create Project</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <div <?php if($_SESSION['userType'] == 'user'){ echo 'class="col-sm-12" style=" margin: 0px; padding: 0;"'; }else{ echo 'class="col-sm-8" style="padding-right: 0;"';}?>>
                        <div style="height: 95%; border: 1px solid lightgray;">
                            <h4>My Projects</h4>
                            <div class="card" style="height: 100%; overflow-y: scroll;">
                                <table class="table table-striped">
                                    <?php
                                    for($i=0; $i< count($my_data); $i++){
                                            if($_SESSION['userType'] != 'user'){
                                    ?>
                                    <tr>
                                        <td style="width: 63%;"><?php echo $my_data[$i]['pName']; ?></td>
                                        <td><a href="<?php echo base_url()."ProjectSettings?project=".$my_data[$i]['projectId']; ?>" class="btn btn-primary">Translate</a>
                                            <a href="<?php echo base_url()."verify?project=".$my_data[$i]['projectId']; ?>" class="btn btn-secondary">Verify Translations</a>
                                        </td>
                                    </tr>
                                            <?php }else if($_SESSION['userType'] == 'user'){ ?>
                                            <tr>
                                                <td style="width: 70%;"><?php echo $my_data[$i]['pName']; ?></td>
                                                <td><a href="<?php echo base_url()."Editor?project=".$my_data[$i]['projectId']; ?>" class="btn btn-primary">Translate</a>
                                                <?php if($my_data[$i]['permission'] =='expert'){ ?><a href="<?php echo base_url()."verify?project=".$my_data[$i]['projectId']; ?>" class="btn btn-secondary">Verify Translations</a> <?php } ?></td>
                                            </tr>
                                    <?php } }?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
