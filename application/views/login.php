<?php
?>

<html>
    <head>
        <title>Login | AmaderInfo</title>
        <script src="<?php echo base_url('resources/jquery.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo base_url('resources/bootstrap.min.css')?>" type="text/css" rel="stylesheet">
        <script src="<?php echo base_url('resources/bootstrap.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo base_url('resources/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <div class="container" style="margin-top: 200px;">
            <div class="row">
                <div class="col-sm-4 offset-sm-4">
                    <div class="card text-center">
                        <div class="text-primary text-center">
                            <h4>Login</h4>
                            <p class="text-danger"><?php echo $this->session->flashdata('reg_succ');?></p>
                        </div>
                        <form action="<?php echo base_url();?>login/login_valid" method="post" class="form-group">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <input type="text" name="email" id="email" class="form-control" placeholder="Username or Email">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                                    <a href="<?php echo base_url().'ChangePassword'; ?>" class="text-right"> <p class="text-secondary">Forgot password?</p></a>
                                </div>
                            </div>
                            <div class="col-sm-12 text-center">
                                <button type="submit" name="login" id="login" class="btn btn-primary">Login</button>
                                <p>New member? <a href="<?php echo str_replace('/index.php','',site_url('registration'));?>" class=""> Create a new account</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
