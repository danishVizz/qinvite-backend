<?php 
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>



<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800"><?=$event?></h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?=$event?> details</h6>
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
                        <th>Name</th>
                        <th>ID Card No.</th>
                        <!-- <th>User Image</th> -->
                        <th>Phone</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Role</th>
                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Sr #</th>
                        <th>Name</th>
                        <th>ID Card No.</th>
                        <!-- <th>User Image</th> -->
                        <th>Phone</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    if(count($user_data) > 0){
                        $n=0;
                        foreach($user_data as $k=>$v){
                            $n++;
                            if($v->role==0){
                                $v->role = "Admin";
                            }elseif($v->role==1){
                                $v->role = "Assistant";
                            }elseif($v->role==2){
                                $v->role = "Host";
                            }elseif($v->role==3){
                                $v->role = "Receptionist admin";
                            }elseif($v->role==4){
                                $v->role = "Receptionist";
                            }
                            elseif($v->role==5){
                                $v->role = "Designer";
                            }
                        ?>
                        <tr>
                            <td><?=$n?></td>
                            <td><?=$v->first_name?> <?=$v->last_name?></td>
                            <td><?=$v->username?></td>
                            <!-- <td><img class="img-responsive img-fluid" src="<?=base_url()?>images/user_img/<?=$v->user_image?>" alt=""></td> -->
                            <td><?=$v->phone?></td>
                            <td><?=$v->email?></td>
                            <td><?=$v->city?></td>
                            <td><?=$v->country?></td>
                            <td><?=$v->role?></td>
                            <td>
                                <div class="d-flex">
                                    <a class="btn btn-primary btn-sm" href="<?=base_url()?>admin/add_user/<?=$v->id?>"><i class="fa fa-edit"></i></a>
                                    <a class="ml-1 btn btn-danger btn-sm" onclick="return deletion()" href="<?=base_url().'admin/delete_user/'.$v->id?>"><i class="fa fa-trash"></i></a>
                                    <?php if($v->role == 'Receptionist'): ?>
                                    <a class="ml-1 btn btn-info btn-sm" href="<?=base_url().'admin/receptionist_details/'.$v->id;?>"><i class="fas fa-info-circle"></i></a>
                                    <?php endif; ?>
                                    <?php if($v->role == 'Designer'): ?>
                                    <a class="ml-1 btn btn-info btn-sm" href="<?=base_url().'admin/designer_details/'.$v->id;?>"><i class="fas fa-info-circle"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
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