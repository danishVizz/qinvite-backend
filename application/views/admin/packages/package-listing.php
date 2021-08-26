<?php 
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>



<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800">Packages</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Package details</h6>
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
                        <th>Name</th>
                        <th>Logo</th>
                        <th>No. of People</th>
                        <th>Price</th>
                        <th>type</th>
                        <th>User</th>
                        <th>Promo code</th>
                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Sr #</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Logo</th>
                        <th>No. of People</th>
                        <th>Price</th>
                        <th>type</th>
                        <th>User</th>
                        <th>Promo code</th>
                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    if(count($package_data) > 0){
                        $n=0;
                        foreach($package_data as $k=>$v){
                            $n++;
                        ?>
                        <tr>
                            <td><?=$n?></td>
                            <td><?=$v->id?></td>
                            <td><?=$v->package_name?></td>
                            <td><?=$v->package_logo?></td>
                            <td><?=$v->package_people?></td>
                            <td><?=$v->package_price?></td>
                            <td><?=($v->package_type == 0?  'Default': 'Custom')?></td>
                            <td>
                                <?=($v->first_name == "" ? "--" : $v->first_name)?>
                                <?php if($v->first_name): ?>
                                <button class="get_details btn ml-1 text-primary" data-toggle="modal" data-target="#detailsModal" id="<?=$v->user_id?>" path="get_userDetails"><i class="fas fa-info"></i></button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($v->promo_code): ?>
                                <?=$v->promo_code?>
                                <button class="get_details btn ml-1 text-primary" data-toggle="modal" data-target="#detailsModal" id="<?=$v->promo_code?>" path="get_promocodeDetails"><i class="fas fa-info"></i></button>
                                <?php endif; ?>
                            </td>
                            <td><a class="btn btn-primary btn-sm" href="<?=base_url().'admin/add_package/'.$v->id?>"><i class="fa fa-edit"></i></a><a class="ml-1 btn btn-danger btn-sm" onclick="return deletion()" href="<?=base_url().'admin/delete_package/'.$v->id?>"><i class="fa fa-trash"></i></a></td>
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