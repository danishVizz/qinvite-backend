<?php

    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
    $events = (isset($receptionist->event_details) ? $receptionist->event_details : "");
    // echo '<pre>'; print_r($receptionist); exit;
?>

    <div class="card my-5 shadow">
        <div class="card-header"><h2 class="text-primary">Receptionist Details</h2></div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-12">
                    <div class="card  border-0 shadow text-dark">
                        <div class="card-header bg-primary text-white">
                            <h3>Bio</h3>
                        </div>
                        <div class="card-body">
                            <div class="recp_bio p-4">
                                <p><b>Name:</b>  <?= $receptionist->first_name .' '. $receptionist->last_name?></p>
                                <p><b>ID card:</b>  <?= $receptionist->username ?></p>
                                <p><b>Phone No: </b><?= $receptionist->phone ?></p>
                                <p><b>Email: </b><?= $receptionist->email ?></p>   
                                <p><b>Address:</b> <?=$receptionist->city. ', ' . $receptionist->country?></p>
                                <p><b>Total Earning this month: </b><span class="font-weight-bold text-success"><?= $receptionist->earnings[date('M')] ?></span> QAR</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <?php if($receptionist->user_image){ ?>
                        <img height="300px" width="300px" class="rounded-circle d-block mx-auto" src="<?=base_url()?>images/user_img/<?=$receptionist->user_img?>" alt="Profile Image">
                    <?php }else{ ?>
                        <img height="300px" width="300px" class="rounded-circle d-block mx-auto" src="<?=base_url()?>assets/img/undraw_profile.svg" alt="Profile Image">
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="card shadow my-3">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Earnings Overview</h6>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="myAreaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                        <div style="max-height: 400px; height: 400px;" class="card my-5">
                            <div class="card-header  bg-info text-white"><h3>Payment Details</h3></div>
                            <div style=" overflow: auto;" class="card-body text-dark">
                                        <?php if ($events) { ?>

                                            <table class="table table-striped shadow ">
                                                <thead class="bg-primary text-white">
                                                    <tr>
                                                        <td>Event Name</td>
                                                        <td>Address</td>
                                                        <td>Date</td>
                                                        <td>Status</td>
                                                        <td>Amount (QAR)</td>
                                                        <td>Payment Date</td>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-dark">

                                        <?php
                                            foreach ($events as $k => $event):
                                            
                                            if ($event->event_status == 0) {
                                                $event->event_status = "Pending";
                                                $txt_class = 'text-info';
                                            } elseif ($event->event_status == 1) {
                                                $event->event_status = "Active";
                                                $txt_class = 'text-success';
                                            } elseif ($event->event_status == 2) {
                                                $event->event_status = "Finished";
                                                $txt_class = 'text-danger';
                                            } ?>
                                        <!-- <div class="card border-0 rounded shadow my-3 border-0">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12">
                                                        <p><b>Event Name:</b>  <?= $event->event_name ?></p>
                                                        <p><b>Address:</b>  <?= $event->event_address ?></p>
                                                        <p><b>Event Date: </b><?= date('d M, Y', strtotime($event->event_date)) ?></p>
                                                        <p><b>status: </b><span class="<?=$txt_class?>"><?= $event->event_status?></span></p>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12">
                                                        <p><b>Amount:</b>  <?= $event->amount ?> QAR</p>
                                                        <p><b>Payment Date:</b>  <?= date('d M, Y', strtotime($event->date)) ?></p>
                                                    </div>
                                                </div>  
                                            </div>
                                        </div>  -->


                                        <tr>
                                            <td><?= $event->event_name ?></td>
                                            <td><?= $event->event_address ?></td>
                                            <td><?= date('d M, Y', strtotime($event->event_date)) ?></td>
                                            <td><span class="<?=$txt_class?>"><?= $event->event_status?></span></td>
                                            <td><?= $event->amount ?></td>
                                            <td><?= date('d M, Y', strtotime($event->date)) ?></td>
                                        </tr>
                                        <?php endforeach;
                                        } else { ?>
                                            <span class="text-secondary text-center d-block">There are no events yet.</span>
                                        <?php } ?>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

<?php
    $this->load->view('admin/inc/footer');
    $years = $receptionist->earnings;                                        
?>
<script src="<?=base_url();?>assets/vendor/chart.js/Chart.min.js"></script>
<script>
var ctx = document.getElementById("myAreaChart");
var myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
    labels: [<?php foreach($years as $k => $v){echo '"'.$k.'",';} ?>],
    datasets: [{
        label: "Revenue",
        lineTension: 0.3,
        backgroundColor: "rgba(78, 115, 223, 0.05)",
        borderColor: "rgba(78, 115, 223, 1)",
        pointRadius: 3,
        pointBackgroundColor: "rgba(78, 115, 223, 1)",
        pointBorderColor: "rgba(78, 115, 223, 1)",
        pointHoverRadius: 3,
        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
        pointHitRadius: 10,
        pointBorderWidth: 2,
        data: [<?php foreach($years as $k => $v){echo $v.',';} ?>],
    }],
    },
    options: {
    maintainAspectRatio: false,
    layout: {
        padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
        }
    },
    scales: {
        xAxes: [{
        time: {
            unit: 'date'
        },
        gridLines: {
            display: false,
            drawBorder: false
        },
        ticks: {
            maxTicksLimit: 7
        }
        }],
        yAxes: [{
        ticks: {
            maxTicksLimit: 5,
            padding: 10,
            // Include a dollar sign in the ticks
            callback: function(value, index, values) {
            return value + ' QAR';
            }
        },
        gridLines: {
            color: "rgb(234, 236, 244)",
            zeroLineColor: "rgb(234, 236, 244)",
            drawBorder: false,
            borderDash: [2],
            zeroLineBorderDash: [2]
        }
        }],
    },
    legend: {
        display: false
    },
    tooltips: {
        backgroundColor: "rgb(255,255,255)",
        bodyFontColor: "#858796",
        titleMarginBottom: 10,
        titleFontColor: '#6e707e',
        titleFontSize: 14,
        borderColor: '#dddfeb',
        borderWidth: 1,
        xPadding: 15,
        yPadding: 15,
        displayColors: false,
        intersect: false,
        mode: 'index',
        caretPadding: 10,
        callbacks: {
        label: function(tooltipItem, chart) {
            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
            return datasetLabel + ': ' + tooltipItem.yLabel + ' QAR';
        }
        }
    }
    }
});


</script>