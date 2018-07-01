<?php
?>

<html>
    <head>
        <title>Glossary | AmaderInfo</title>
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
                $('#search').keyup(function () {
                    var key = document.getElementById('search').value;
                    var low = document.getElementById('low').value;
                    var high = document.getElementById('high').value;
                    if(key==''){
                        $.ajax({
                            type: "post",
                            url: "<?php echo base_url();?>Glossary/restore_glossary",
                            data: {low: low, high: high},
                            success: function (data) {
                                document.getElementById('gloss').innerHTML = data;
                            }
                        });
                    }else {
                        $.ajax({
                            type: "post",
                            url: "<?php echo base_url();?>Glossary/search_glossary",
                            data: {key: key, low: low, high: high},
                            success: function (data) {
                                document.getElementById('gloss').innerHTML = data;
                            }
                        });
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
            <div class="col-sm-9 offset-sm-1" style="height: 100%;">
                <div style="margin-top: 20px;">
                    <form class="form-group" method="get">
                    <div class="form-inline">
                        <input type="text" class="fa fa-search form-control offset-sm-2 col-sm-8" id="search" PLACEHOLDER="&#xf002; Search Terms">
                    </div>
                    </form>
                    <?php $table_data='<tr><th style="width: 30%;">Source Term</th><th style="width: 30%;">Translation</th>';
                    $table_data .= '<th style="width: 25%;">Part of Speech</th><th style="width: 15%;"><i class="fa fa-info-circle" style="color: deepskyblue;"></i></th></tr>';
                    foreach ($glossary->result() as $row) {
                        $table_data .= "<tr><td>$row->enUS</td><td>$row->bnBD</td><td>$row->pos</td>";
                        $table_data .= '<td><a href="#" id="infoChange"><i class="fa fa-edit" style="color: deepskyblue; font-size: 22px;"></i></a></td></tr>';
                    }?>
                    <div class="text-right" style="color: skyblue; margin-right: 10%;">
                        <p>
                            <a href="<?php
                            $page = ($cpage-1);
                            echo base_url()."Glossary?page=$page"; ?>" style="font-size: 20px;"><  </a>
                            <input type="hidden" id="low" value="<?php echo ($cpage-1); ?>">
                            <a href="<?php
                            $page = ($cpage+1);
                            echo base_url()."Glossary?page=$page"; ?>" style="font-size: 20px;">  ></a>
                            <?php echo "$cpage of $tpage"; ?>
                            <input type="hidden" id="high" value="<?php echo ($cpage+1); ?>">
                        </p>
                    </div>
                    <div class="" style="margin-top: 20px; height: 82%; overflow-y: scroll;">
                        <table class="table table-striped" id="gloss">
                            <?php echo $table_data; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
