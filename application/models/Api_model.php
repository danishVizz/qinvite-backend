<?php

class Api_model extends CI_Model{


    // ================== Misc functions ====================

    function response($status, $data, $message){
        $res = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        return $res;
    }

    function preview($data){
        echo '<pre>'; print_r($data);exit;
    }

    function check_existance($table, $data){
        $res = $this->db->get_where($table, $data);
        if($res->num_rows() > 0){
            return true;
        }else{
            return false;
        }
    }

    function update_promocode($data, $code){
        $res = $this->db->update('promo_codes', $data, $code);
        if($res){
            return $this->response(true, [], "Data inserted successfully");
        }else{
            return $this->response(false, [], "Something went wrong!");
        }
    }

    function get_promocode($code){
        $res = $this->db->get_where('promo_codes', ['code' => $code]);
        if($res->num_rows() > 0){
            return $res->row();
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


    function get_price(){
        $this->db->select('setting_value');
        $res = $this->db->get_where('settings', ['setting_name' => 'price_per_invite'])->row();
        $price = $res->setting_value;
        if($res){
            return $price;
        }else{
            return false;
        }
    }

    function get_chat_api_data(){
        $this->db->select('setting_name,setting_value');
        $this->db->like('setting_name','chat_api');
        $res = $this->db->get('settings');
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function get_category_ids($event_id){
        $this->db->distinct('category_id');
        $this->db->select('category_id, pdf');
        $res = $this->db->get_where('event_category', ['event_id' => $event_id]);
        foreach($res->result() as $k => $v){
            $this->db->select('name');
            $v->id = $v->category_id;
            unset($v->category_id);
            $category_name = $this->db->get_where('categories', ['id' => $v->id])->row();
            if($category_name){
                $v->name = $category_name->name;
            }
        }
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function get_category_id($event_id){
        $this->db->distinct('category_id');
        $this->db->select('category_id, event_message');
        $res = $this->db->get_where('event_category', ['event_id' => $event_id]);
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }

    function check_category_type($where){
        $category = $this->db->get_where('categories', $where);
        if($category->num_rows() > 0){
            $category = $category->row();
            if($category->type == 1){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function search($keyword){
        $this->db->like('event_name', $keyword);
        $this->db->order_by('id', 'desc');
        $res = $this->db->get('events');
        if($res->num_rows() > 0){
            return $this->response(true, $res->result(), 'Data found');
        }else{
            return $this->response(false, [], 'No data found');
        }
    }

    // ================== User functions ====================

    function add_user($data){
        if(!$this->check_existance('users',['username' => $data['username']]) && (!$this->check_existance('users',['phone' => $data['phone']]) || !$this->check_existance('users',['email' => $data['email']]))){
            $res = $this->db->insert("users", $data);
            if($res){
                $user_info = $this->db->get_where('users', $data)->result();
                return $this->response(true,$user_info,'Sign up successful');
            }else{
                return $this->response(false,[],'Data insertion failed');
            }
        }else{
            return $this->response(false, [], 'User already exists');
        }
    }

    function get_user($data){
        $this->db->order_by('id', 'desc');
        $user_info = $this->db->get_where('users', $data)->result();
        if($user_info){
            return $this->response(true, $user_info, 'User found');
        }else{
            return $this->response(false, [], "No user found");
        }
    }

    
    function login_user($data){
       
        if(strlen($data['username']) > 0){
            $this->db->group_start();
            $this->db->where("username",$data["username"]);
            $this->db->or_where("phone",$data["username"]);
            $this->db->or_where("email",$data["username"]);
            $this->db->group_end();
            $this->db->where("password",$data["password"]);
            $this->db->where("status", 0);
            $res = $this->db->get('users');
            if($res->num_rows() > 0){
                $user_info = $res->row();
                return $this->response(true, $user_info, 'Login successful');
            }else{
                return $this->response(false, [], 'Invalid credentials');
            }
        }else{
            return $this->response(false, [], 'Invalid credentials');
        }

    }

    function update_user($id, $data){

        if($this->check_existance('users', ['id' => $id['id']])){
            $res = $this->db->update('users', $data, $id);
            if($res){
                $new_data = $this->db->get_where('users', $id)->row();
                return $this->response(true, $new_data, 'User updated successfuly');
            }else{
                return $this->response(false, [], 'User update failed');
            }
        }else{
                return $this->response(false, [], "Invalid user");
            }
            
    }


    function delete_user($id){
        if($this->check_existance('users', ['id' => $id])){
            $res = $this->db->update('users', ['status' => 1], ['id' => $id]);
            if($res){
                return $this->response(true, [], 'User deleted successfuly');
            }else{
                return $this->response(false, [], 'User deletion failed');
            }
        }else{
            return $this->response(false, [], "Invalid username");
        }
        
    }



    // ================== Designers functions ====================


    // function get_designers(){
    //     $res = $this->db->get_where('users', ['role' => 5]);
    //     if($res->num_rows() > 0){
    //         return $res->result();
    //     }else{
    //         return array();
    //     }
    // }

    function get_designers(){
        $this->db->select('users.id, users.first_name, users.last_name, users.phone, users.role, users.availability');
        $this->db->order_by('id', 'desc');
        $res = $this->db->get_where('users',['role' => 5]);
        if($res->num_rows() > 0){
            $designers = $res->result();
            foreach($designers as $k => $designer){
                $meta = $this->get_usermeta(['user_id' => $designer->id]);
                if($meta){
                    foreach($meta as $k => $v){
                        $designer->{$v->meta_key} = $v->meta_value;
                    }
                }
            }
            return $designers;
        }else{
            return false;
        }
    }

    function check_designer_user($where){
        $this->db->order_by('id', 'desc');
        $res = $this->db->get_where('event_designer', $where);
        if($res->num_rows() > 0){
            return $res->row();
        }else{
            return false;
        }
    }


    function request_designer($data){
        $res = $this->db->insert('event_designer', $data);
        if($res){
            return $this->response(true, [], "Request submitted successfully");
        }else{
            return $this->response(false, [], "Something went wrong!");
        }
        
    }



    // ================== Event functions ====================

    function add_event($data){
        $res = $this->db->insert('events', $data);
        if($res){
            return $this->db->insert_id();
        }else{
            return false;
        }
    }

    function get_events($data){
        $this->db->order_by('id', 'desc');
        $res = $this->db->get_where('events', $data)->result();
        if($res){
            return $this->response(true, $res, 'Events found successfuly');
        }else{
            return $this->response(false, [], 'Events not found');
        }
    }

    function get_total_invites($event_id){
        $event = $this->db->get_where('events', ['id' => $event_id])->row();
        if($event){
            $package = $this->db->get_where('packages', ['id' => $event->package_id])->row();
            if($package){
                $total_invites = $package->package_people;
                if($total_invites){
                    return $total_invites;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    function get_sent_messages($event_id){
        $res = $this->db->query('SELECT count(status) as num From participants WHERE event_id = "'.$event_id. '" AND status IN (1,2,3)');
        if($res->num_rows() > 0){
            return $res->row()->num;
        }else{
            return false;
        }
    }

    function edit_event($data, $id){
        if($this->check_existance('events', ['id' => $id])){
            $this->db->where(['id' => $id]);
                $res = $this->db->update('events', $data);
                if($res){
                    // $new_data = $this->db->get_where('events', $id)->result();
                    // return $this->response(true, $new_data, "Event updated successfuly");
                    return true;
                }else{
                    // return $this->response(false, [], "Event updation faliure!");
                    return false;
                }
        }else{
            // return $this->response(false, [], "Event does not exist!");
            return false;
        }
        
    }
    

    function delete_event($id){
        if($this->check_existance('events',['id' => $id])){
            $this->db->delete('event_designer',['event_id' => $id]);
            $this->db->delete('event_category',['event_id' => $id]);
            $this->db->delete('event_receptionists',['event_id' => $id]);
            $this->db->delete('card_designs',['event_id' => $id]);
            $res = $this->db->delete('events', ['id' => $id]);
            if($res){
                return $this->response(true, [], 'Event deleted successfuly');
            }else{
                return $this->response(false, [], 'Something went wrong!');
            }
        }else{
            return $this->response(false, [], "Event does not exist");
        }
    }

    function get_event_cards($where){
        $cards = $this->db->get_where('card_designs', $where);
        if($cards->num_rows() > 0){
            return $this->response(true, $cards->result(), "Data found successfully");
        }else{
            return $this->response(false, $cards->result(), "Data no found");
        }
    }

    function delete_all_events($where){
        $events = $this->db->get_where('events',$where);
        if($events->num_rows() > 0){
            $events = $events->result();
            foreach($events as $event){
                // $this->db->delete('event_designer',['event_id' => $event->id]);
                $this->db->delete('event_category',['event_id' => $event->id]);
                // $this->db->delete('event_receptionists',['event_id' => $event->id]);
                // $this->db->delete('card_designs',['event_id' => $event->id]);
                $res = $this->db->delete('events', ['id' => $event->id]);
                if($res){
                    $res = [
                        'status' => true,
                        'data' => [],
                        'message' => "Events deleted successfully"
                    ];
                }else{
                    $res = [
                        'status' => false,
                        'data' => [],
                        'message' => "Something went wrong!"
                    ];
                }
            }

            return $res;
        }else{
            return $this->response(false, [], "No expired events found");
        }
    }

    // ================== Receptionist functions for the host App ====================

    function add_receptionists($event_id, $receptionists){
        
        for($i=0;$i<count($receptionists); $i++){
            if(!$this->check_existance('event_receptionists', ['event_id' => $event_id, 'receptionist_id' => $receptionists[$i]])){

                $res = $this->db->insert('event_receptionists', ['event_id' => $event_id, 'receptionist_id' => $receptionists[$i]]);
            }
        }

        if($res){
            return true;
        }else{
            return false;
        }
    }

    function delete_receptionists($where){
        $res = $this->db->delete('event_receptionists', $where);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function update_receptionist($event_id, $receptionists){
        for($i = 0; $i<count($receptionists); $i++){
            if($this->check_existance('event_receptionists', ['event_id' => $event_id, 'receptionist_id' => $receptionists[$i]])){
                $res = $this->db->update('event_receptionists', ['receptionist_id' => $receptionists[$i]], ['event_id' => $event_id]);
            }
        }

        if($res){
            return $this->response(true, [], "Data updated successfully");
        }else{
            return $this->response(false, [], "Something went wrong");
        }
    }

    function get_event_receptionists($id){
        $this->db->select('event_receptionists.event_id, users.*');
        $this->db->where(['event_receptionists.event_id' => $id]);  
        $this->db->join('users', "event_receptionists.receptionist_id = users.id", 'left');
        $this->db->order_by('id', 'desc');
        $res = $this->db->get("event_receptionists");
        // $res["user_price"] = 34;
        if($res->num_rows() > 0){
            return $res->result();
        }else{
            return false;
        }
    }
    
    function get_receptionists($where = false){
        if($where){
            $this->db->where($where);
        }
        $this->db->order_by('id', 'desc');
        $res = $this->db->get_where('users', ['role' => 4, 'status' => 0]);

        if($res->num_rows() > 0){
            return $this->response(true, $res->result(), "Success");
        }else{
            return $this->response(false, [], "Failure");
        }
    }
    
    // ================== Package functions ====================
    
    function get_packages($where = false){
        if($where){
            $this->db->group_start();
            $this->db->where($where);
            $this->db->where(['package_type' => 1]);
            $this->db->group_end();
        }
        $this->db->or_where(['package_type' => 0]);
        $this->db->order_by('id', 'desc');
        $res = $this->db->get('packages')->result();
        if($res){
            return $this->response(true, $res, 'Packages found successfuly');
        }else{
            return $this->response(false, [], 'Packages not found!');
        }

        // return $data;
    }

    function get_event_package($where){
        $this->db->where($where);
        $res = $this->db->get('packages')->row();
        if($res){
            return $res;
        }else{
            return false;
        }
    }
    
    function add_package($data){
        $res = $this->db->insert('packages', $data);
        if($res){
            $package_id = $this->db->insert_id();
            return $package_id;
        }else{
            return $this->response(false, [], "Something went wrong!");
        }
    }

    function upgrade_package($data, $where){
        $res = $this->db->update('packages', $data, $where);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function delete_package($where){
        // $res = $this->db->delete('packages', $where);
        $res = $this->db->update('packages', ['deleted' => 1], $where);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function delete_all_packages($where){
        if($this->check_existance('packages', $where)){
            // $res = $this->db->delete('packages', $where);
            $res = $this->db->update('packages',['deleted' => 1], $where);
            if($res){
                $packages = $this->db->get_where('packages',['package_type' => 0, 'deleted' => 0])->result();
                return $this->response(true, $packages, 'All packages deleted successfully');
            }else{
                return $this->response(false, [], 'Something went wrong!');
            }
        }else{
            return $this->response(false, [], 'There are no custom packages found');
        }
    }
   
    // ================== Category functions ====================

    function add_category($data){
        // $this->preview($data);
        $res = $this->db->insert('categories', $data);
        
        if($res){
            return $this->db->insert_id();
        }else{
            return false;
        }
    }

    function get_categories($where){
        $this->db->where($where);
        $this->db->order_by('id', 'desc');
        $res = $this->db->get('categories');
        if($res->num_rows() > 0){
            return $this->response(true, $res->result(), "Categories found");
        }else{
            return $this->response(false, [], "No categories found");
        }
    }

    function get_the_category($where=false){
        if($where){
            $this->db->where($where);
        }

        $res = $this->db->get('categories');
        if($res->num_rows() > 0){
            return $res->row();
        }else{
            return false;
        }
    }

    function delete_category($id){
        if($this->check_existance('categories', ['id' => $id])){
            $res = $this->db->delete('categories', ['id' => $id]);

            if($res){
                return $this->response(true, [], "Category deleted successfuly");
            }else{
                return $this->response(false, [], "Something went wrong");
            }
        }else{
            return $this->response(false, [], "Invalid category");
        }
    }

    function update_category($data, $id){
        if($this->check_existance('categories', ['id' => $id])){
            $res = $this->db->update('categories', $data, ['id' => $id]);
            if($res){
                // $new_data = $this->db->get_where('categories', ['id' => $id])->result();
                // return $this->response(true, $new_data, 'Category updated successfuly');
                return true;
            }else{
                // return $this->response(false, [], 'Something went wrong');
                return false;
            }
        }else{
            //return $this->response(false, [], 'Invalid category');
            return false;
        }
    }

    function add_event_category($data){
        $res = $this->db->insert('event_category', $data);
        if($res){
            return $this->response(true, [], "Data inserted successfully");
        }else{
            return $this->response(false, [], "Something went wrong!");
        }
    }

    // ================== Participants functions ====================

    function add_participant($data){
        if(!empty($data)){
            $this->db->insert("participants",$data);
            if($this->db->affected_rows() > 0){
                return $this->db->insert_id();
            }else{
                return $this->response(true, [], 'Something went wrong!');
            }
        }else{
            return $this->response(true, [], 'No participants');
        }
    }

    function get_participants($where=false){ 
        if($where){
            $this->db->where($where);
        }
        $this->db->order_by('id', 'desc');
        $res = $this->db->get('participants');
        if($res->num_rows() > 0){
                return $res->result();
        }else{
            return array();
        }
    }

    function get_participants_events($where){ 
        
        // $res = $this->db->query('SELECT * FROM participants WHERE category_id IN (SELECT category_id FROM `event_category` where event_id='.$event_id.') ORDER BY `category_id` DESC');
        $res = $this->db->get_where('participants', $where);
        if($res->num_rows() > 0){
                return $res->result();
        }else{
            return array();
        }
    }

    function delete_participants($id){
        $res = $this->db->delete('participants', ['category_id' => $id]);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function remove_participant($id){
        $res = $this->db->delete('participants', ['id' => $id]);
        if($res){
            return $this->response(true, [], 'Participant removed successfully');
        }else{
            return $this->response(false, [], 'Something went wrong!');
        }
    }


    function update_participant($data, $where){
        
        $res = $this->db->update('participants', $data, $where);

        if($res){
            return true;
        }else{
            return false;
        }
    }
     
    // ================== Card functions ====================



    // ================== QR code functions ====================
    
    //
    
    // ================== Payment functions ====================

    function get_payment_details($where = false){
        if($where){
            $this->db->where($where);
        }
        $details = $this->db->get('payments');

        if($details->num_rows() > 0){
            return $details->row();
        }else{
            return false;
        }
    }

    /**
     * ===================================================
     *  Functions for Receptionists App
     * ===================================================
    */


    function get_rp_events($id){
        $this->db->select('event_receptionists.id, event_receptionists.receptionist_id, events.id, events.event_name, events.event_date, events.event_address,events.user_id, events.no_of_receptionists, events.event_card, events.event_status');
        $this->db->where(['event_receptionists.receptionist_id' => $id]);  
        $this->db->join('events', "event_receptionists.event_id = events.id", 'left');
        $this->db->order_by('events.id', 'desc');
        $events = $this->db->get("event_receptionists")->result();
        //echo $this->db->last_query();
        $k = 1;
        foreach($events as $event){
            $event->key = $k++;
            $event->host_details = $this->db->get_where('users',['id' => $event->user_id])->row();
            // $this->db->query('SELECT * FROM participants WHERE event_id = '.$event->id.' AND category_id IN (SELECT category_id FROM event_category WHERE event_id = '.$event->id.')');
            $participants = $this->get_participants_events($event->id);
            //echo $this->db->last_query();
            $event->participants = $participants;
        }
        
        if($events){
            return $this->response(true, $events, "Events found successfully");
        }else{
            return $this->response(false, [], "No data found");
        }
    }
    
    function check_in($id){

        if($this->check_existance('participants', ['id' => $id])){
            if($this->check_existance('participants',['check_in' => 0, 'id' => $id])){

                $res = $this->db->update('participants', ['check_in' => 1], ['id' => $id]);

                if($res){
                    return $this->response(true, [], "Participants checked in successfully");
                }else{
                    return $this->response(false, [], "Check in Failed!");
                }

            }else{
                return $this->response(false, [], "Participant is already checked in");
            }
        }else{
            return $this->response(false, [], "Not invited!");
        }


        
    }

    function check_out($id){

        $res = $this->db->update('participants', ['check_in' => 0], ['id' => $id]);
        if($res){
            return $this->response(true, [], "Check out successful");
        }else{
            return $this->response(false, [], "Check out failed");
        }
    }

    /**
     * ===================================================
     *  Functions for Designer App
     * ===================================================
    */

    function get_event_requests($id){
        $this->db->select('event_designer.*, events.event_name, events.event_date, users.first_name, users.last_name, users.phone');
        $this->db->where(['event_designer.designer_id' => $id ]);
        $this->db->join('users', 'users.id = event_designer.user_id', 'left');
        $this->db->join('events', 'events.id = event_designer.event_id', 'left');
        $this->db->order_by('events.id', 'desc');
        $events = $this->db->get('event_designer');
        if($events->num_rows() > 0){
            return $this->response(true, $events->result(), "Requests found");
        }else{
            return $this->response(false, [], "No requests found");
        }

    }

    function update_event_request($data, $where){
        $res = $this->db->update('event_designer', $data, $where);

        if($res){
            return true;
        }else{
            return false;
        }
    }

    function submit_image($card_data){
        $res  = $this->db->insert('card_designs', $card_data);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function designer_total_earnings($id){
        $res = $this->db->query('SELECT sum(amount) as amount FROM designer_payment WHERE designer_id = '.$id)->row();
        if($res->amount){
            return $res->amount;
        }else{
            return false;
        }
    }

    function toggle_availability($data, $where){
        $res = $this->db->update('users', $data, $where);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * ===================================================
     *  Functions for Sending Messages
     * ===================================================
    */

    function send_message($msg_id, $participant_id){
        $res = $this->db->update('participants', ['message_id' => $msg_id, 'status' => 5], ['id' => $participant_id]);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    function save_message($message, $where){
        $res = $this->db->update('event_category', $message, $where);
        
        return ($res ? true: false);
    }

    function update_pdf($data, $where){
        $res = $this->db->update('event_category', $data, $where);
        if($res){
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


}