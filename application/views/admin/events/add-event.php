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
                    <!-- Form Group (Host's ID card No)-->
                    <div class="form-group col-md-6">
                        <label class="small mb-1" for="inputid">User ID Card</label>
                        <input class="form-control" value="<?=$user_idCard?>" name="user_idCard" id="inputid" type="text" placeholder="Enter user's ID card number">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="small mb-1" for="pacakge_name">Select Package</label>
                        <select class="form-control" name="package_id" id="pacakge_name">
                            <option value="">Select</option>
                            <?php
                                foreach($packages as $k => $v){
                                    echo '<option '.($v->id == $package_id ? 'selected': '').' value="'.$v->id.'">'.$v->package_name.'</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>
                    <!-- Form Row-->
                    <div class="form-row">
                        <!-- Form Group (event name)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputEventName">Event Name</label>
                            <input class="form-control" value="<?=$event_name?>" name="event_name" id="inputEventName" type="text" placeholder="Enter event's name">
                        </div>
                        <!-- Form Group (event date)-->
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputEventDate">Event Date</label>
                            <input class="form-control" name="event_date" id="inputEventDate" type="date" placeholder="Enter event's date" value="<?=date('Y-m-d',strtotime($event_date))?>">
                        </div>
                    </div>
                    <!-- Form Group (event address)-->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputEventAddress">Event address</label>
                            <input class="form-control" id="inputEventAddress" type="text" placeholder="Enter event address" name="event_address" value="<?=$event_address?>">
                        </div>
                        <div class="form-group col-md-6">
                        <label class="small mb-1" for="event-status">Event Status</label>
                            <select class="form-control" name="event_status" id="event-status">
                                <option value=""> -- Select --</option>
                                <option <?=($event_status == 0 ? 'selected' : '')?> value="0">Pending</option>
                                <option <?=($event_status == 1 ? 'selected' : '')?> value="1">Active</option>
                                <option <?=($event_status == 2 ? 'selected' : '')?> value="2">Finished</option>
                            </select>
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="form-row">
                        <!-- Form Group (phone number)-->
                        <div class="form-group col-md-2 col-sm-12">
                            <label class="small mb-1" for="inputNumReceptionist">No. of Receptionists</label>
                            <input class="form-control " id="inputNumReceptionist" name="no_of_receptionists" type="number" min=1 value="<?=$no_of_receptionists?>">
                        </div>
                        <div class="form-group col-md-10">
                            <label class="small mb-1 btn btn-primary mt-4" for="inputEventCard">Event Card</label>
                            <input class="form-control d-none" id="inputEventCard" name="eventcard" type="file">
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