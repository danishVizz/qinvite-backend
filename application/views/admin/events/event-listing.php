<?php 
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>



<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800"><?=$title?>s</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?=$title?> details</h6>
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
                        <th>Event ID</th>
                        <th>Host Name</th>
                        <th>Event Name</th>
                        <th>Package</th>
                        <th>Event Date</th>
                        <th>Event Address</th>
                        <th>Event status</th>
                        <th>Payment Status</th>
                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Sr #</th>
                        <th>Event ID</th>
                        <th>Host Name</th>
                        <th>Event Name</th>
                        <th>Package</th>
                        <th>Event Date</th>
                        <th>Event Address</th>
                        <th>Event status</th>
                        <th>Payment Status</th>
                        <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Action&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    if(count($event_data) > 0){
                        $n=0;
                        foreach($event_data as $k=>$v){
                            $n++;
                            if($v->event_status == 0){
                                $v->event_status = "Pending";
                                $txt_class = 'text-info';
                            }elseif($v->event_status == 1){
                                $v->event_status = "Active";
                                $txt_class = 'text-success';
                            }elseif($v->event_status == 2){
                                $v->event_status = "Finished";
                                $txt_class = 'text-danger';
                            }

                            if($v->payment_status == 0){
                                $v->payment_status = '--';
                                $txt_color = 'text-dark'; 
                            }else if($v->payment_status == 1){
                                $v->payment_status = 'Draft';
                                $txt_color = 'text-primary'; 
                            }else if($v->payment_status == 2){
                                $v->payment_status = 'Unpaid';
                                $txt_color = 'text-danger'; 
                            }else if($v->payment_status == 3){
                                $v->payment_status = 'Paid';
                                $txt_color = 'text-success'; 
                            }else if($v->payment_status == 4){
                                $v->payment_status = 'Overdue';
                                $txt_color = 'text-info'; 
                            }else if($v->payment_status == 5){
                                $v->payment_status = 'Canceled';
                                $txt_color = 'text-secondary'; 
                            }
                        ?>
                        <tr>
                            <td><?=$n?></td>
                            <td><?=$v->id?></td>
                            <td><?=$v->first_name?> <?=$v->last_name?><button class="get_details btn ml-1 text-primary" data-toggle="modal" data-target="#detailsModal" id="<?=$v->user_id?>" path="get_userDetails"><i class="fas fa-info"></i></button></td>
                            <td><?=$v->event_name?></td>
                            <td><?=($v->package_name ? $v->package_name : "--")?>
                                <?php if ($v->package_name): ?>
                                <button class="get_details btn ml-1 text-primary" data-toggle="modal" data-target="#detailsModal" id="<?=$v->package_id?>" path="get_packageDetails"><i class="fas fa-info"></i></button>
                                <?php endif; ?>
                            </td>
                            <td><?=$v->event_date?></td>
                            <td><?=$v->event_address?></td>
                            <td><span class="<?=$txt_class?>"><?=$v->event_status?></span></td>
                            <td><span class="<?=$txt_color;?>"><?=$v->payment_status?></span></td>
                            <td>
                                <!-- <a class="btn btn-primary btn-sm" href="<?=base_url().'admin/add_event/'.$v->id?>"><i class="fa fa-edit"></i></a> -->
                                <a class="ml-1 btn btn-danger btn-sm" onclick="return deletion()" href="<?=base_url().'admin/delete_event/'.$v->id?>"><i class="fa fa-trash"></i></a>
                                <a class="ml-1 btn btn-info btn-sm" href="<?=base_url().'admin/single_event/'.$v->id;?>"><i class="fas fa-info-circle"></i></a>
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