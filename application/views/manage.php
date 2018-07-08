<?php
?>
<html>
    <head>
        <title>Project Settings | AmaderInfo</title>
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
            <div class="col-sm-9 offset-sm-1 text-center" style="height: 100%;">
                <div class="col-sm-12" style="height: 90%; margin-top: 20px;">
                    <table class="table table-striped">
                        <tr>
                            <td style="width: 30%;">Name</td>
                            <td style="width: 15%;">Permission</td>
                            <td style="width: 30%;">Project Name</td>
                            <td style="width: 25%;">Manage</td>
                        </tr>
                        <?php
                            $i=0;
                            foreach ($data->result() as $row){
                        ?>
                        <tr>
                            <td><?php echo $row->fname.' '.$row->lname; ?></td>
                            <td><?php echo $row->permission; ?></td>
                            <td><?php echo $row->pName; ?></td>
                            <td><select id="type<?php echo $i;?>" class="form-control" onchange="change(this.id)">
                                    <option value="">Select Translator Type</option>
                                    <option value="translator">Translator</option>
                                    <option value="expert">Expert Translator</option>
                                </select>
                                <input type="hidden" id="uid<?php echo $i; ?>" value="<?php echo $row->uId; ?>">
                                <input type="hidden" id="pid<?php echo $i; ?>" value="<?php echo $row->projectId; ?>">
                            </td>
                        </tr>
                        <?php $i++; } ?>
                    </table>
                </div>
            </div>
        </div>
        <script>
            function change(id) {
                var row = id.substring(4);
                var type = document.getElementById(id).value;
                var uId = document.getElementById('uid'+row).value;
                var pId = document.getElementById('pid'+row).value;
                $.ajax({
                    type: "post",
                    url: "<?php echo base_url();?>ManageTranslator/update_permission",
                    data: {type:type, uId:uId, pId: pId},
                    success: function (data) {
                        if(data == 'true'){
                            alert('Successfully Updated');
                            location.reload();
                        }else {
                            alert('Sorry! Please try again later');
                            location.reload();
                        }
                    }
                });
            }
        </script>
    </body>
</html>
