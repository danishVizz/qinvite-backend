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

                    <div class="form-row">
                    <div class="form-group col-md-2 col-sm-12">
                        <label class="small mb-1" for="inputCategoryName">Category Name</label>
                        <input class="form-control" value="<?=$category_name?>" name="category_name" id="inputCategoryName" type="text" placeholder="Enter category name">
                    </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="small mb-1" for="inputPeople">No. of People (Per QR)</label>
                            <input class="form-control" min=1 value="<?=($people_per_qr ? $people_per_qr: 1)?>" name="people_per_qr" id="inputPeople" type="number">
                        </div>
                    </div>

                    <div class="form-row">
                    <div class="form-group col-md-2">
                        <label class="small mb-1" for="phone_allowed">Allow Phones</label>
                            <select class="form-control" name="phone_allowed" id="phone_allowed">
                                <option value=""> -- Select --</option>
                                <option <?=($phone_allowed == 1 ? "selected" : "" )?> value="1">Allowed</option>
                                <option <?=($phone_allowed == 0 ? "selected" : "" )?> value="0">Not Allowed</option>
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