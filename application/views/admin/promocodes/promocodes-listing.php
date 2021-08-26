<?php 
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>



<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800">Promo Codes</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Promo Codes</h6>
    </div>
    <div class="card-body">
    <?php 
        if($this->session->flashdata("errors")){
            echo '<div class="alert alert-danger text-center">'.$this->session->flashdata("errors").'</div>';
        }elseif($this->session->flashdata('success')){
            echo '<div class="alert alert-success text-center">'.$this->session->flashdata("success").'</div>';
        }
        
    ?>
        <div class="table-responsive">
            <table class="table table-striped text-dark" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Sr #</th>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Discount (%)</th>
                        <th>Status</th>
                        <th>Expiration Date</th>
                        <th>Package Name</th>
                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Sr #</th>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Discount (%)</th>
                        <th>Status</th>
                        <th>Expiration Date</th>
                        <th>Package Name</th>
                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    if(count($promocode_data) > 0){
                        $n=0;
                        foreach($promocode_data as $k=>$v){
                            $n++;
                            if($v->status == 0){
                                $v->status = 'Active';
                                $txt_class = "text-success";
                            }else if($v->status == 1){
                                $v->status = 'Used';
                                $txt_class = "text-danger";
                            }else if($v->status == 2){
                                $v->status = 'Expired';
                                $txt_class = "text-info";
                            }
                        ?>
                        <tr>
                            <td><?=$n?></td>
                            <td><?=$v->id?></td>
                            <td><?=$v->code?></td>
                            <td><?=$v->discount?></td>
                            <td><span class='<?= $txt_class ?>'><?= $v->status ?></span></td>
                            <td><?= date('d-M-Y', strtotime($v->expiry_date)) ?></td>
                            <td>
                                <?=(!$v->package_name ? '--': $v->package_name)?>
                                <?php if($v->package_name): ?>
                                <button class="get_details btn ml-1 text-primary" data-toggle="modal" data-target="#detailsModal" id="<?=$v->package_id?>" path="get_packageDetails"><i class="fas fa-info"></i></button>
                                <?php endif; ?>
                            </td>
                            <td><a class="ml-1 btn btn-danger btn-sm" onclick="return deletion()" href="<?=base_url().'admin/delete_promocode/'.$v->id?>"><i class="fa fa-trash"></i> Delete</a></td>
                        </tr>
                        <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
function deletion(){
    var x = confirm('Are you sure ?');
    if(x){
        return true;
    }else{
        return false;
    }
}
</script>

<?php
    $this->load->view('admin/inc/footer');
    ?>
    <script src="<?=base_url();?>assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?=base_url();?>assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="<?=base_url();?>assets/js/demo/datatables-demo.js"></script>