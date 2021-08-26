<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Reset Password | Qinvite</title>

    <!-- Custom fonts for this template-->

    <link href="<?= base_url();?>assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <link

        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"

        rel="stylesheet">

    <!-- Custom styles for this template-->

    <link href="<?= base_url();?>assets/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">
    <div class="container">

        <!-- Outer Row -->

        <div class="row justify-content-center">

            <div class="col-md-6 col-sm-12">

                <div class="card o-hidden border-0 shadow-lg my-5">

                    <div class="card-body p-0">

                        <!-- Nested Row within Card Body -->

                        <div class="row align-items-center">

                            <div class="col-lg-12">

                                <div class="p-5">

                                    <div class="text-center">

                                        <h1 class="h4 text-gray-900 mb-4">Reset Password</h1>

                                    </div>

                                    <?php

                                        if($this->session->flashdata("errors")){

                                            echo '<div class="alert alert-danger">'.$this->session->flashdata("errors").'</div>';

                                        }

                                    ?>

                                    <form class="user" method="post">

                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" id="exampleInputnew_pass" name="new_pass" placeholder="Enter new password" required>
                                        </div>

                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" id="exampleInputconfirm_pass" name="confirm_pass" placeholder="Enter confirm password" required>
                                        </div>

                                        <input type="submit" class="btn btn-primary btn-user btn-block" value="RESET PASSWORD">

                                        <!-- <hr>

                                        <a href="index.html" class="btn btn-google btn-user btn-block">

                                            <i class="fab fa-google fa-fw"></i> Signup with Google

                                        </a>

                                        <a href="index.html" class="btn btn-facebook btn-user btn-block">

                                            <i class="fab fa-facebook-f fa-fw"></i> Signup with Facebook

                                        </a> -->

                                    </form>


                                </div>

                            </div>

                        </div>

                    </div>

                </div>



            </div>



        </div>



    </div>



    <!-- Bootstrap core JavaScript-->

    <script src="<?= base_url();?>assets/vendor/jquery/jquery.min.js"></script>

    <script src="<?= base_url();?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>



    <!-- Core plugin JavaScript-->

    <script src="<?= base_url();?>assets/vendor/jquery-easing/jquery.easing.min.js"></script>



    <!-- Custom scripts for all pages-->

    <script src="<?= base_url();?>assets/js/sb-admin-2.min.js"></script>



</body>



</html>