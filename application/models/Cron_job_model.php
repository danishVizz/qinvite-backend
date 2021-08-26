<?php

class Cron_job_model extends CI_Model{
    function get_event_dates(){
        //$this->db->select("id");
        $res = $this->db->get_where('events', '(DATE(event_date) = "'.date('Y-m-d').'" OR DATE(event_date) < "' . date('Y-m-d').'") AND event_status < 2');
        // echo $this->db->last_query();exit;
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function update_event_status($data, $where){
        $res = $this->db->update('events', $data, $where);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function get_promocodes(){
        $res = $this->db->get('promo_codes');
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function update_promocodes($data, $where){
        $res = $this->db->update('promo_codes', $data, $where);
        if($res){
            return true;
        }else{
            return false;
        }
    }
}