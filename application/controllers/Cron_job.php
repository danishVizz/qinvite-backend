<?php

 class Cron_job extends CI_Controller{
     function __construct(){
         parent::__construct();
         $this->load->model('cron_job_model');
     }

     function update_event_status(){
        $event_dates = $this->cron_job_model->get_event_dates();

        if($event_dates){
            foreach($event_dates as $k => $v){
                if(date('Y-m-d',strtotime($v->event_date)) == date('Y-m-d')){
                    //status update to active
                    $this->cron_job_model->update_event_status(['event_status' => 1], ['id' => $v->id]);
                }else if(date('Y-m-d',strtotime($v->event_date)) < date('Y-m-d')){
                    //Status update to guzra hua
                    $this->cron_job_model->update_event_status(['event_status' => 2], ['id' => $v->id]);
                }
            }
        }
     }


     function update_promocode_status(){
         $promocodes = $this->cron_job_model->get_promocodes();
         if($promocodes){
             foreach($promocodes as $k => $v){
                 if(date('Y-m-d', strtotime($v->expiry_date)) < date('Y-m-d')){
                     $this->cron_job_model->update_promocodes(['status' => 2], ['id' => $v->id]);
                 }
             }
         }
     }

 }