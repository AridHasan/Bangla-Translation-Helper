<?php
?>
<html>
    <head>
        <title>Account Activation | AmaderInfo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <script src="<?php echo base_url('resources/jquery.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo base_url('resources/bootstrap.min.css')?>" type="text/css" rel="stylesheet">
        <script src="<?php echo base_url('resources/bootstrap.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo base_url('resources/bootstrap.bundle.min.js'); ?>" type="text/javascript"></script>
    </head>
    <body>
        <div class="container" style="margin-top: 10%;">
            <div class="row">
                <div class="col-sm-4 offset-sm-4 text-center">
                    <h5 class="card-title"><?php echo $this->session->flashdata('activation_error'); ?></h5>
                </div>
            </div>
        </div>
    </body>
</html>
