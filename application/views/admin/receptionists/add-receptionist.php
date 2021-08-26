<?php
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?=$event?> receptionist</h1>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header"><?=$event?> receptionist</div>
            <div class="card-body">
                <?php 
                    if($this->session->flashdata("errors")){
                        echo '<div class="alert alert-danger text-center">'.$this->session->flashdata("errors").'</div>';
                    }elseif($this->session->flashdata('success')){
                        echo '<div class="alert alert-success text-center">'.$this->session->flashdata("success").'</div>';
                    }
                  
                ?>
                <form method="post" action="" enctype="multipart/form-data">
                    <!-- Form Group (username)-->
                    <div class="form-group">
                        <label class="small mb-1" for="inputid">ID Card</label>
                        <input class="form-control" value="" name="user_idCard" id="inputid" type="text" placeholder="Enter your ID card number">
                    </div>
                    <!-- Form Row-->
                    <div class="form-row">
                        <!-- Form Group (first name)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputFirstName">First name</label>
                            <input class="form-control" value="" name="user_fname" id="inputFirstName" type="text" placeholder="Enter your first name">
                        </div>
                        <!-- Form Group (last name)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputLastName">Last name</label>
                            <input class="form-control" name="user_lname" id="inputLastName" type="text" placeholder="Enter your last name" value="">
                        </div>
                    </div>
                    <!-- Form Group (email address)-->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputEmailAddress">Email address</label>
                            <input class="form-control" id="inputEmailAddress" type="email" placeholder="Enter email address" name="user_email" value="">
                        </div>
                        <div class="form-group col-md-6">
                        <label class="small mb-1" for="user-role">User role </label>
                            <select class="form-control" name="user_role" id="user-role">
                                <option value="">-- Select user role --</option>
                                <option <?php echo ($role==4) ? "selected" : "" ?> value="4">Receptionist</option>
                            </select>
                        </div>
                    </div>


                    <div id="price_field" class="form-row <?= (isset($user_price) ? '' : 'd-none') ?>">
                        <!-- Form Group (phone number)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputprice">Price</label>
                            <input class="form-control" id="inputprice" name="user_price" type="number" min=1 value="<?= (isset($user_price) ? $user_price : '' )?>">
                        </div>
                    </div>


                    <!-- Form Row-->
                    <div class="form-row">
                        <!-- Form Group (phone number)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputPhone">Phone number</label>
                            <input class="form-control" id="inputPhone" name="user_phone" type="tel" placeholder="Enter your phone number" value="">
                            </div>
                            <!-- Form Group (birthday)-->
                            <div class="form-group col-md-6">
                                <label class="small mb-1" for="inputimage">Profile Image</label>
                                <input class="form-control" id="inputimage" type="file" name="user_image" >
                                </div>
                                </div>
                    <!-- Form Row-->
                    <div class="form-row">
                        <!-- Form Group (phone number)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputPassword">Password</label>
                            <input class="form-control" id="inputPassword" type="password" placeholder="Enter password" name="user_password">
                        </div>
                        <!-- Form Group (birthday)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputConfirmpass">Confirm Password</label>
                            <input class="form-control" id="inputConfirmpass" type="password" name="user_confirm_password" placeholder="Confirm password">
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="form-row">
                        <!-- Form Group (phone number)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputCity">City</label>
                            <input class="form-control" id="inputCity" type="text" placeholder="City" name="user_city" value="">
                        </div>
                        <!-- Form Group (birthday)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputCountry">Country</label>
                            <select class="form-control" name="user_country" id="user-country">
                                <option value="">Select Country</option>
                                <?php
                                    foreach($countries as $countryy){
                                        ?>
                                            <option <?php echo ($user_country==$countryy) ? "selected" : "" ?> value="<?= $countryy ?>"><?= $countryy ?></option>
                                        <?php
                                    }
                                    ?>
                            </select>
                        </div>
                    </div>
                    <!-- Save changes button-->
                    <button class="btn btn-primary" type="submit"><?=$event?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
    $this->load->view('admin/inc/footer');