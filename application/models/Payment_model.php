<?php

class Payment_model extends CI_Model{
    function get_event_details($event_id){
        $res = $this->db->get_where('events', ['id' => $event_id]);
        if($res->num_rows() > 0){
            $event = $res->row();
            $package = $this->db->get_where('packages', ['id' => $event->package_id])->row();
            $event->package_detail = $package;
            $user = $this->db->get_where('users', ['id' => $event->user_id])->row();
            $event->user_detail = $user;
            return $event;
        }else{
            return false;
        }
    }

    function get_credentials(){
        $this->db->select('setting_name, setting_value');
        $res = $this->db->get('settings');
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function insert_transaction($data){
        $res = $this->db->insert('payments', $data);
        if($res){
            $this->db->update('events', ["payment_status" => $data['transaction_status']], ['id' => $data['order_id']]);
            return true;
        }else{
            return false;
        }

    }

    function adjust_payments($id){
        $recep_id = $this->db->get_where('event_receptionists', ['event_id' => $id]);
        $designer = $this->db->get_where('event_designer',['event_id' => $id]);
        if($recep_id->num_rows() > 0){
            foreach($recep_id->result() as $k => $v){
                $price = $this->db->get_where('usermeta', ['meta_key' => 'user_price', 'user_id' => $v->receptionist_id])->row()->meta_value;
                // $receptionists[] = (object) [
                //     'receptionist_id' => $v->receptionist_id,
                //     'event_id' => $id,
                //     'price' => $price,
                // ];

                $recep_data = [
                        'recep_id' => $v->receptionist_id,
                        'event_id' => $id,
                        'amount' => $price,
                    ];
                $this->db->insert('recp_payment', $recep_data);
                // echo "<pre>";print_r($receptionists);
            }

            #$this->preview($receptionists);
        }

        if($designer->num_rows() > 0){
            $designer = $designer->result();
            foreach($designer as $k => $v){
                $price = $this->db->get_where('usermeta', ['meta_key' => 'user_price', 'user_id' => $v->designer_id])->row()->meta_value;
                // $designers[] = (object) [
                //     'receptionist_id' => $v->designer_id,
                //     'event_id' => $id,
                //     'price' => $price,
                // ];

                $designer_data = [
                    'designer_id' => $v->designer_id,
                    'event_id' => $id,
                    'amount' => $price,
                ];

                $this->db->insert('designer_payment', $designer_data);
            }
            
        }
        
    }

    function message_ack($id, $status){
        if($status == 'delivered'){
            $status = 2;
        }else if($status == 'viewed'){
            $status = 3;
        }else if($status == 'sent'){
            $status = 1;
        }else{
            $status = 5;
        }
        $res = $this->db->update('participants', ['status' => $status], ['message_id' => $id]);

        if($res){
            return true;
        }else{
            return false;
        }
    }


}