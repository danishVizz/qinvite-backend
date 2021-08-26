<?php
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');

    if($event->event_status == 0){
        $event->event_status = "Pending";
        $txt_class = 'text-info';
    }elseif($event->event_status == 1){
        $event->event_status = "Active";
        $txt_class = 'text-success';
    }elseif($event->event_status == 2){
        $event->event_status = "Finished";
        $txt_class = 'text-danger';
    }

    if($event->payment_status == 0){
        $event->payment_status = '--';
        $bg_color = 'bg-dark'; 
    }else if($event->payment_status == 1){
        $event->payment_status = 'Draft';
        $bg_color = 'bg-primary'; 
    }else if($event->payment_status == 2){
        $event->payment_status = 'Unpaid';
        $bg_color = 'bg-danger'; 
    }else if($event->payment_status == 3){
        $event->payment_status = 'Paid';
        $bg_color = 'bg-success'; 
    }else if($event->payment_status == 4){
        $event->payment_status = 'Overdue';
        $bg_color = 'bg-info'; 
    }else if($event->payment_status == 5){
        $event->payment_status = 'Canceled';
        $bg_color = 'bg-secondary'; 
    }
?>

<div class="card shadow mb-4 p-4 text-dark event_detail">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <h1 class="title d-inline mr-2"><?=$event->event_name?></h1><span class="badge rounded-pill <?= $bg_color ?> text-white"><?= $event->payment_status ?></span>
            <span class="<?=$txt_class?> d-block"><?= $event->event_status?></span>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-md-6 col-sm-12">
            <p><b>Host Name:</b>  <?= $event->first_name .' '. $event->last_name?></p>
            <p><b>No of Receptionist: </b><?= $event->no_of_receptionists?></p>
            <p><b>Package: </b><?= $event->package_name ?></p>   
            <p><b>Address:</b> <?=$event->event_address?></p>
            <p><b>Date:</b> <?=$event->event_date?></p>
        </div>
        <div class="col-md-6 col-sm-12 ">
            <img src="<?= $event->event_card ?>" class="img-fluid w-100 d-block mx-auto" alt="">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Receptionists</h3>
                </div>
                <div class="card-body receptionists_container">
                    
                    <?php 
                       if($event->receptionists) {
                        foreach($event->receptionists as $k => $v) : ?>
                        <div class="receptioinst">
                            <div class="pf_image">
                                <img src="<?= base_url().'images/user_img/'. ($v->user_image ? $v->user_image : '1.jpg')?>" alt="">
                            </div>
                            <div class="detail">
                                <h4 class="name"><?= $v->first_name . ' ' . $v->last_name ?></h4>
                                <p><?= $v->phone ?></p>
                                <p><?= $v->city ?>, <?= $v->country ?></p>
                            </div>
                        </div>
                    <?php endforeach;
                        }else{
                          echo '<p class="text-center">No Receptioinsts</p>';
                        }
                    ?>
                </div>
                
            </div>
        </div>

        <div class="col-md-4 col-sm-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3>Participants</h3>
                </div>
                <div class="card-body participants_container">
                    <?php 
                        if($event->participants) {
                            foreach($event->participants as $k => $v): 
                                if($v->isphoneallow == 0){
                                    $v->isphoneallow = "text-danger";
                                }else{
                                    $v->isphoneallow = "text-success";
                                }
                            
                            ?>
                        <div class="participant">
                            <div class="">
                                <h3 class="name"><?= $v->name ?></h3>
                                <p class="phone"><?= $v->number ?></p>
                            </div>
                            <span class="<?= $v->isphoneallow ?>"><i class="fas fa-mobile-alt"></i></span>
                        </div>
                    <?php endforeach; 
                        }else{
                            echo '<p class="text-center">No Participants</p>';
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Designer</h3>
                </div>
                <div class="card-body receptionists_container">
                    
                    <?php 
                       if($event->designer) {
                        foreach($event->designer as $k => $v) : ?>
                        <div class="receptioinst">
                            <div class="pf_image">
                                <img src="<?= base_url().'images/user_img/'. ($v->user_image ? $v->user_image : '1.jpg')?>" alt="">
                            </div>
                            <div class="detail">
                                <h4 class="name"><?= $v->first_name . ' ' . $v->last_name ?></h4>
                                <p><?= $v->phone ?></p>
                                <p><?= $v->city ?>, <?= $v->country ?></p>
                            </div>
                        </div>
                    <?php endforeach;
                        }else{
                          echo '<p class="text-center">No Desinger</p>';
                        }
                    ?>
                </div>
                
            </div>
        </div>

    </div>

    <?php if(isset($event->payment)): ?>
    <div class="row my-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white"><h3>Payment Details</h3></div>
                <div class="card-body">
                    <?php foreach($event->payment as $k => $v): ?>
                    <div class="my-2">
                        <p><b>Transaction ID:</b>  <?= $v->transaction_no ?></p>
                        <p><b>Amount: </b><?= $v->amount ?></p>
                        <p><b>Date: </b><?= $v->transaction_date ?></p>
                    </div>
                    <?php endforeach; ?>               
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
    $this->load->view('admin/inc/footer');