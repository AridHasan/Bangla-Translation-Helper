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
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <?php if(!array_key_exists('confirmKey',$_GET)){ ?>
                    <div class="col-sm-4 offset-sm-4" style="margin-top: 20%;">
                        <div class="card text-center" style="padding: 10px;">
                            <h4>Send Code</h4>
                            <p style="color: green"><?php echo $this->session->flashdata('mess') ;?></p>
                            <form class="form-group" method="post" action="<?php echo base_url();?>ChangePassword/send_code">
                                <div class="form-group">
                                    <input type="text" name="email" id="email" placeholder="Username or Email" class="form-control" required>
                                </div>
                                <div class="form-group text-center">
                                    <button type="submit" name="confKey" id="confKey" class="btn btn-primary">Send Code</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } else if(array_key_exists('confirmKey',$_GET)){ ?>
                    <div class="col-sm-4 offset-sm-4" style="margin-top: 20%;">
                        <div class="card text-center" style="padding: 10px;">
                            <h4>Change Password</h4>
                            <p style="color: green"><?php echo $this->session->flashdata('mess') ;?></p>
                            <form class="form-group" method="post" action="<?php echo base_url();?>ChangePassword/change_password">
                                <div class="form-group">
                                    <input type="password" name="password" id="password" placeholder="Password" class="form-control">
                                    <input type="hidden" name="key" value="<?php echo $_GET['confirmKey'];?>">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="conf_pass" id="conf_pass" placeholder="Confirm Password" class="form-control">
                                </div>
                                <div class="form-group text-center">
                                    <button type="submit" name="changePass" id="changePass" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php }else{
                    redirect(base_url().'login');
                } ?>
            </div>
        </div>
    </body>
</html>

