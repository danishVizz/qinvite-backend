<?php
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800"><?=$event?></h1>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header"><?=$event?></div>
            <div class="card-body">
                <?php 
                    if($this->session->flashdata("errors")){
                        echo '<div class="alert alert-danger text-center">'.$this->session->flashdata("errors").'</div>';
                    }elseif($this->session->flashdata('success')){
                        echo '<div class="alert alert-success text-center">'.$this->session->flashdata("success").'</div>';
                    }
                  
                ?>
                <form method="post" action="" enctype="multipart/form-data">

                    <div class="form-group">
                        <label class="small mb-1" for="inputPackageName">Package Name</label>
                        <input class="form-control" value="<?=$package_name?>" name="package_name" id="inputPackageName" type="text" placeholder="Enter package name">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputPeople">No. of People</label>
                            <input class="form-control" value="<?=$package_people?>" name="package_people" id="inputPeople" type="number" min=1> 
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputPrice">Package Price</label>
                            <input class="form-control" name="package_price" id="inputPrice" type="number" value="<?=$package_price?>" min=1>
                        </div>
                    </div>


                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1 btn btn-primary" for="inputPackageLogo">Package Logo</label>
                            <input class="form-control d-none img_file_prv" id="inputPackageLogo" type="file" name="package_logo">
                        </div>
                        <div class="form-group col-md-6">
                            <img width="100" height="100" alt="Image Preview" src="<?=base_url()."images/package_img/".$package_logo?>" class="prv_img">
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
    ?>
    <script>
 function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                    $('.prv_img').attr('src', e.target.result);
                    }
                    
                    reader.readAsDataURL(input.files[0]); // convert to base64 string
                }
            }

            $(".img_file_prv").change(function() {
            readURL(this);
            });
    </script>