<?php

class Admin_model extends CI_Model{

    function check_existance($table, $data){
        foreach($data as $k=>$v){
            $this->db->where($k,$v);
        }
        $res = $this->db->get($table);
        //echo $this->db->last_query();exit;
        if($res->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    function preview($data){
        echo '<pre>'; print_r($data);exit;
    }

    // ========== Users ==============

    function login_user($data){
       
        if(strlen($data['username']) > 0){
            $this->db->group_start();
            $this->db->where("username",$data["username"]);
            $this->db->or_where("phone",$data["username"]);
            $this->db->or_where("email",$data["username"]);
            $this->db->group_end();
            $this->db->where("password",$data["password"]);
            $this->db->group_start();
            $this->db->where("role", 0);
            $this->db->or_where("role", 1);
            $this->db->or_where("role", 3);
            $this->db->group_end();
            $res = $this->db->get('users');
            if($res->num_rows() > 0){
                $user_info = $res->row();
                return $user_info;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    function insert_user($data){
        $res = $this->db->insert('users', $data);
        if($res){
            return $this->db->insert_id();
        }else{
            return false;
        }
    }

    function insert_usermeta($meta){
        $res = $this->db->insert('usermeta', $meta);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function get_usermeta($where = false){
        if($where){
            $this->db->where($where);
        }
        $res = $this->db->get('usermeta');
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function update_usermeta($data, $where){
        $res = $this->db->update('usermeta', $data, $where);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function get_users($where = false){
        if($where){
            $this->db->where($where);
        }
        $user_data = $this->db->get_where('users', ['status' => 0]);
        if($user_data->num_rows() > 0){
            return $user_data->result();
        }else{
            return array();
        }
    }


    function get_receptionists($where){
        $res = $this->db->get_where('recadmin_recp', $where);
        if($res->num_rows() > 0){
            foreach($res->result() as $k => $v){
                $receptionist[] = $this->db->get_where('users', ['id' => $v->receptionist_id])->row();
            }
            // $this->preview($receptionist);
            return $receptionist;
        }else{
            return false;
        }
    }


    function get_single_recp_data($id){
        $receptionist = $this->db->get_where('users', ['id' => $id]);
        if($receptionist->num_rows() > 0){
            $receptionist = $receptionist->row();
            $this->db->select('events.*, recp_payment.*');
            $this->db->where(['recep_id' => $id]);
            $this->db->join('events','events.id = recp_payment.event_id', 'left');
            $this->db->order_by('recp_payment.date', 'desc');
            $events = $this->db->get('recp_payment');
            if($events->num_rows() > 0){
                $receptionist->event_details = $events->result();
            }

            $res =  $this->db->query("SELECT 
                        SUM(if(MONTH(date) = 1, amount,0)) as Jan,
                        SUM(if(MONTH(date) = 2, amount,0)) as Feb,
                        SUM(if(MONTH(date) = 3, amount,0)) as Mar,
                        SUM(if(MONTH(date) = 4, amount,0)) as Apr,
                        SUM(if(MONTH(date) = 5, amount,0)) as May,
                        SUM(if(MONTH(date) = 6, amount,0)) as Jun,
                        SUM(if(MONTH(date) = 7, amount,0)) as Jul,
                        SUM(if(MONTH(date) = 8, amount,0)) as Aug,
                        SUM(if(MONTH(date) = 9, amount,0)) as Sep,
                        SUM(if(MONTH(date) = 10, amount,0)) as Oct,
                        SUM(if(MONTH(date) = 11, amount,0)) as Nov,
                        SUM(if(MONTH(date) = 12, amount,0)) as `Dec`
                    FROM recp_payment 
                    WHERE YEAR(date) = ".date('Y')." AND recep_id = {$id}");
            $earnings = $res->row_array();
            $receptionist->earnings = $earnings;


            return $receptionist;
        }else{
            return false;
        }

    
    }

    function get_single_designer_data($id){
        $designer = $this->db->get_where('users', ['id' => $id]);
        if($designer->num_rows() > 0){
            $designer  = $designer->row();
            $this->db->select('events.*, designer_payment.*');
            $this->db->where(['designer_payment.designer_id' => $id]);
            $this->db->join('events', 'events.id = designer_payment.event_id', 'left');
            $this->db->order_by('date','desc');
            $events = $this->db->get('designer_payment');
            if($events->num_rows() > 0){
                $designer->event_details = $events->result();
            }

            $res =  $this->db->query("SELECT 
                                SUM(if(MONTH(date) = 1, amount,0)) as Jan,
                                SUM(if(MONTH(date) = 2, amount,0)) as Feb,
                                SUM(if(MONTH(date) = 3, amount,0)) as Mar,
                                SUM(if(MONTH(date) = 4, amount,0)) as Apr,
                                SUM(if(MONTH(date) = 5, amount,0)) as May,
                                SUM(if(MONTH(date) = 6, amount,0)) as Jun,
                                SUM(if(MONTH(date) = 7, amount,0)) as Jul,
                                SUM(if(MONTH(date) = 8, amount,0)) as Aug,
                                SUM(if(MONTH(date) = 9, amount,0)) as Sep,
                                SUM(if(MONTH(date) = 10, amount,0)) as Oct,
                                SUM(if(MONTH(date) = 11, amount,0)) as Nov,
                                SUM(if(MONTH(date) = 12, amount,0)) as `Dec`
                            FROM designer_payment 
                            WHERE YEAR(date) = ".date('Y')." AND designer_id = {$id}");
            $earnings = $res->row_array();
            $designer->earnings = $earnings;
            return $designer;
        }else{
            return false;
        }
    }


    
    function update_user($data,$where){
        //echo '<pre>'; print_r($data); print_r($user_id);exit;
        if(!empty($data)){
            $res = $this->db->update("users",$data,$where);
            //exit;
            if($res){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    function delete_user($id){
        $get_details = $this->db->get_where("users","id = $id");
        if($get_details->num_rows() > 0){
            $res = $this->db->update('users', ['status' => 1], ['id' => $id]);
        }
        return ($res? true : false);
    }

    function insert_recp_relation($data){
        $res = $this->db->insert('recadmin_recp', $data);
        if($res){
            return true;
        }else{
            return false;
        }
    }


     // ========== Events ==============
    function get_events($where = false){
        $this->db->select("events.*, users.first_name, users.last_name, packages.package_name");
        if($where){
            $this->db->where($where);   
        }
        $this->db->join('users', 'events.user_id = users.id', 'left');
        $this->db->join('packages', 'events.package_id = packages.id', 'left');
        $event_data = $this->db->get('events');
        if($event_data->num_rows() > 0){
            return $event_data->result();
        }else{
            return array();
        }
    }

    function add_event($data){
        $res = $this->db->insert('events', $data);

        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    function update_event($data, $id){
        $res = $this->db->update('events', $data, ['id' => $id]);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function delete_event($id){
       $event = $this->db->get_where('events', ['id' => $id]);

       if($event->num_rows() > 0){
            $this->db->delete('event_designer',['event_id' => $id]);
            $this->db->delete('event_category',['event_id' => $id]);
            $this->db->delete('event_receptionists',['event_id' => $id]);
            $this->db->delete('card_designs',['event_id' => $id]);
            $this->db->delete('participants',['event_id' => $id]);
           $res = $this->db->delete('events', ['id' => $id]);

           if($res){
               return true;
           }else{
               return false;
           }
       }else{
           return false;
       }
    }

    function get_payment_details($where = false){
        if($where){
            $this->db->where($where);
        }
        $res = $this->db->get('payments');
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

     // ========== Packages ==============

    function get_packages($where = false){
        $this->db->select("packages.*,users.first_name,users.last_name");
        if($where){
            $this->db->where($where);
        }
        $this->db->join("users","packages.user_id = users.username",'left');
        $package_data = $this->db->get('packages');
        if($package_data->num_rows() > 0){
            return $package_data->result();
        }else{
            return array();
        }
    }

    function add_package($data){
        $res = $this->db->insert('packages', $data);
        return ($res ? true : false );
    }

    function delete_package($id){
        $get_details = $this->db->get_where('packages', ['id' => $id]);
        if($get_details->num_rows() > 0){
           $res = $this->db->delete('packages', ['id' => $id]);
        }

        return ($res ? true : false);
    }

    function update_package($data, $id){
        $res = $this->db->update('packages', $data, ['id' => $id]);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function get_event_receptionists($id){
        $this->db->select('event_receptionists.event_id, users.*');
        $this->db->where(['event_receptionists.event_id' => $id]);  
        $this->db->join('users', "event_receptionists.receptionist_id = users.id", 'left');
        $this->db->order_by('id', 'desc');
        $res = $this->db->get("event_receptionists");
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function get_event_designer($id){
        $this->db->select('event_designer.event_id, users.*');
        $this->db->where(['event_designer.event_id' => $id]);  
        $this->db->join('users', "event_designer.designer_id = users.id", 'left');
        $this->db->order_by('id', 'desc');
        $res = $this->db->get("event_designer");
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function get_participants_events($event_id){ 
        
        $res = $this->db->query('SELECT * FROM participants WHERE category_id IN (SELECT category_id FROM `event_category` where event_id='.$event_id.') ORDER BY `category_id` DESC');
        if($res->num_rows() > 0){
                return $res->result();
        }else{
            return array();
        }
    }

    function get_event_cards($where){
        $cards = $this->db->get_where('card_designs', $where);
        if($cards->num_rows() > 0){
            return $cards->result();
        }else{
            return false;
        }
    }

     // ========== Promocodes ==============

    function get_promocodes($where = false){
        $this->db->select('promo_codes.*, packages.package_name');
        if($where){
            $this->db->where($where);
        }
        $this->db->join('packages', 'promo_codes.package_id = packages.id', 'left');
        $promocodes_data = $this->db->get('promo_codes');
        if($promocodes_data->num_rows() > 0){
            return $promocodes_data->result();
        }else{
            return array();
        }
    }

    function add_promocode($data){
        $res = $this->db->insert('promo_codes', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    function delete_promocode($id){
        $res = $this->db->delete('promo_codes', ['id' => $id]);

        if($res){
            return true;
        }else{
            return false;
        }
    }

     // ========== Categories ==============

    function create_category($data){
        $res = $this->db->insert('categories', $data);

        if($res){
            return true;
        }else{
            return false;
        }
    }

    function get_category($where=false){
        if($where){
            $this->db->where($where);
        }

        $res = $this->db->get('categories');
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function get_category_listing(){
        $this->db->select('categories.*, users.first_name, users.last_name, users.role');
        $this->db->join('users', 'users.id = categories.user_id', 'left');
        $data = $this->db->get('categories');

        if($data->num_rows() > 0){
            return $data->result();
        }else{
            return array();
        }
    }

    function update_category($data, $id){ 
        $res = $this->db->update('categories', $data, ['id' => $id]);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function delete_category($id){
        $res = $this->db->delete('categories', ['id' => $id]);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    // ========== Settings ==============
    
    function save_settings($key,$value){
        $check = $this->check_existance("settings",array("setting_name"=>$key));
        if($check){
            $this->db->update("settings",array("setting_value"=>$value),array("setting_name"=>$key));
        }else{
            $this->db->insert("settings",array("setting_value"=>$value,"setting_name"=>$key));
        }
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    function get_settings(){
        $res = $this->db->get('settings');
        if($res->num_rows() > 0){
            return $res->result_array();
        }else{
            return false;
        }
    }

    // ========== Participants ==============

    function get_participants($where=false){ 
        if($where){
            $this->db->where($where);
        }

        $res = $this->db->get('participants');
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return array();
        }
    }

    function update_participants($where=false, $data){
        if($where){
            $this->db->where($where);
        }

        $res = $this->db->update('participants', $data);

        if($res){
            return true;
        }else{
            return false;
        }
    }

    // ========== Messages ==============

    function send_message($msg_id, $participant_id){
       $res = $this->db->update('participants', ['message_id' => $msg_id], ['id' => $participant_id]);

       if($res){
           return true;
       }else{
           return false;
       }
    }

    // ========== Statistics ==============

    function get_total_receptionist(){
        $res = $this->db->get_where('users', ['role' => 4, 'status' => 0]);
        return $res->num_rows();
    }

    function get_total_designers(){
        $res = $this->db->get_where('users', ['role' => 5, 'status' => 0]);
        return $res->num_rows();
    }

    function get_total_events(){
        $res = $this->db->get('events');
        return $res->num_rows();
    }

    function get_current_month_events(){
        $res = $this->db->query('SELECT MONTH(event_date) as month, count(id) as events  FROM events WHERE YEAR(event_date) = '.date('Y').' AND MONTH(event_date) = '.date('m').' GROUP BY MONTH(event_date)  ORDER BY month DESC');
        return $res->row();
    }

    function get_total_revenue(){
        $res = $this->db->query('SELECT sum(amount) as amount FROM payments WHERE transaction_status = 3');
        return $res->row();
    }

    function get_current_month_revenue(){
        $res = $this->db->query('SELECT MONTH(transaction_date) as month, sum(amount) as amount FROM payments WHERE YEAR(transaction_date) = '.date('Y').' AND MONTH(transaction_date) = '.date('m').' GROUP BY month ORDER BY month DESC');
        return $res->row();
    }

    function get_total_active_hosts(){
        $this->db->select('count(id) as hosts');
        $res = $this->db->get_where('users', ['role' => 2, 'status' => 0]);
        if($res->num_rows() > 0){
            $res = $res->row();
            return $res->hosts;
        }else{
            return 0;
        }
    }

    function get_yearly_earnings(){
        $res =  $this->db->query("SELECT 
                                SUM(if(MONTH(transaction_date) = 1, amount,0)) as Jan,
                                SUM(if(MONTH(transaction_date) = 2, amount,0)) as Feb,
                                SUM(if(MONTH(transaction_date) = 3, amount,0)) as Mar,
                                SUM(if(MONTH(transaction_date) = 4, amount,0)) as Apr,
                                SUM(if(MONTH(transaction_date) = 5, amount,0)) as May,
                                SUM(if(MONTH(transaction_date) = 6, amount,0)) as Jun,
                                SUM(if(MONTH(transaction_date) = 7, amount,0)) as Jul,
                                SUM(if(MONTH(transaction_date) = 8, amount,0)) as Aug,
                                SUM(if(MONTH(transaction_date) = 9, amount,0)) as Sep,
                                SUM(if(MONTH(transaction_date) = 10, amount,0)) as Oct,
                                SUM(if(MONTH(transaction_date) = 11, amount,0)) as Nov,
                                SUM(if(MONTH(transaction_date) = 12, amount,0)) as `Dec`
                            FROM payments 
                            WHERE YEAR(transaction_date) = ".date('Y')."");
        return $res->row_array();
       // $this->preview($res->row_array());
    
    }

    function get_admin_recep($where){
        $this->db->select('count(receptionist_id) as no_of_receptionists');
        if($where){
            $this->db->where($where);
        }
        $rec_ids = $this->db->get('recadmin_recp');
        return $rec_ids->row()->no_of_receptionists;
        $this->preview($rec_ids->row()->no_of_receptionists);
    }

    function get_recp_monthly_revenue($id){
        $res =  $this->db->query("SELECT 
                                SUM(if(MONTH(date) = 1, amount,0)) as Jan,
                                SUM(if(MONTH(date) = 2, amount,0)) as Feb,
                                SUM(if(MONTH(date) = 3, amount,0)) as Mar,
                                SUM(if(MONTH(date) = 4, amount,0)) as Apr,
                                SUM(if(MONTH(date) = 5, amount,0)) as May,
                                SUM(if(MONTH(date) = 6, amount,0)) as Jun,
                                SUM(if(MONTH(date) = 7, amount,0)) as Jul,
                                SUM(if(MONTH(date) = 8, amount,0)) as Aug,
                                SUM(if(MONTH(date) = 9, amount,0)) as Sep,
                                SUM(if(MONTH(date) = 10, amount,0)) as Oct,
                                SUM(if(MONTH(date) = 11, amount,0)) as Nov,
                                SUM(if(MONTH(date) = 12, amount,0)) as `Dec`
                            FROM recp_payment 
                            WHERE YEAR(date) = ".date('Y')." AND recep_id IN (SELECT receptionist_id FROM recadmin_recp WHERE recp_admin_id = ".$id.")");
        return $res->row_array();
    }

    function get_recp_total_revenue($id){
        $rev = $this->db->query("SELECT sum(amount) as amount FROM recp_payment WHERE recep_id IN (SELECT receptionist_id FROM recadmin_recp WHERE recp_admin_id = ".$id.")")->row()->amount;
        return $rev;
    }

    function get_recp_total_events($id){
        $events = $this->db->query("SELECT count(event_id) as events FROM recp_payment WHERE recep_id IN  (SELECT receptionist_id FROM recadmin_recp WHERE recp_admin_id = ".$id.")")->row()->events;
        return $events;
    }

    function designer_total_earnings($id){
        $res = $this->db->query('SELECT sum(amount) as amount FROM designer_payment WHERE designer_id = '.$id)->row();
        if($res->amount){
            return $res->amount;
        }else{
            return false;
        }
    }

    function get_recp_all_events($id){
        
    }

    

}