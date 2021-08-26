<?php
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add <?=$event?></h1>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">Add <?=$event?></div>
            <div class="card-body">
                <?php 
                    if($this->session->flashdata("errors")){
                        echo '<div class="alert alert-danger text-center">'.$this->session->flashdata("errors").'</div>';
                    }elseif($this->session->flashdata('success')){
                        echo '<div class="alert alert-success text-center">'.$this->session->flashdata("success").'</div>';
                    }
                  
                ?>
                <form method="post" action="" enctype="multipart/form-data">

                <div class="form-row align-items-center">
                        <div class="form-group col-md-2">
                            <label class="small mb-1" for="inputcode">Promo Code</label>
                            <input class="form-control" value="" name="code" id="inputcode" type="text" maxlength=6 required>
                        </div>
                        <div class="form-group col-md-6">
                            <button type="button" class="btn btn-primary mt-4" onclick="generate_code()"><i class="fas fa-spin fa-cog mr-2"></i>Generate Code</button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="small mb-1" for="inputDiscount">Disound (%)</label>
                            <input class="form-control" value="" name="discount" id="inputDiscount" type="number" min=0 max=<?=($this->session->userdata('user_role') == 1 ? "20" : "100") ?> required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="small mb-1" for="inputexpiry_date">Expiration Date</label>
                            <input class="form-control" value="" name="expiry_date" id="inputexpiry_date" type="date" required>
                        </div>
                    </div>
                    <!-- Save changes button-->
                    <button class="btn btn-primary" type="submit">Add <?=$event?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function generate_code(){
        let promoCode;
        promoCode = Math.random().toString(36).substring(2, 8);
        document.querySelector('#inputcode').value = promoCode;     
    }          
</script>
<?php
    $this->load->view('admin/inc/footer');