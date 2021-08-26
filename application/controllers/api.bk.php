<?php

require APPPATH . 'libraries/REST_Controller.php';


class Api extends REST_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model('api_model');
    }


    // ================= Users APIs ===================

    public function signup_post(){

        
        $cnic = $this->input->post('cnic');
        $password = $this->input->post('password');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        
        $errors = "";
        if($email){
            $errors .= ((!filter_var($email,FILTER_VALIDATE_EMAIL)) ? "Invalid Email" : "");
        }
        $errors .= ((strlen($password) < 6) ? "Password length must not be less than 6" : "");

        if($errors ==""){
            if($this->api_model->check_existance('users',['username'=> $cnic])){
                $res = $this->api_model->response(false, [], 'User already exists!');
                $this->set_response($res, REST_Controller::HTTP_NOT_FOUND);
            }else{
                $data = array(
                    'username' => $this->db->escape_str($cnic),
                    'password' => md5($password),
                    'phone' => $this->db->escape_str($phone),
                    'email' => $this->db->escape_str($email),
                    'role' => 2,
                    'status' => 0
                );

                $res = $this->api_model->add_user($data);
        
                $this->set_response($res, REST_Controller::HTTP_OK);
            }
        }else{
            $err = $this->api_model->response(false, [], $errors);
            $this->set_response($err, REST_Controller::HTTP_OK);
        }

       
    }

    public function get_user_get(){
        $username = $this->input->get('username');
        $data = ['username' => $this->db->escape_str($username)];

        $res = $this->api_model->get_user($data);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    public function login_post(){
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        if($username && $password ){
            $data = array(
                'username' => $this->db->escape_str($username),
                'password' => md5($password)
            );

            $res = $this->api_model->login_user($data);
            $this->set_response($res, REST_Controller::HTTP_OK);
        }else{
            $this->set_response("invalid details", REST_Controller::HTTP_OK);
        }
    }

    public function update_user_post(){

        
        $cnic = $this->input->post('cnic');
        $first_name = $this->input->post('firstname');
        $last_name = $this->input->post('lastname');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $city = $this->input->post('city');
        $country = $this->input->post('country');
        $id = array('username' => $this->db->escape_str($cnic));
        $img = false;
        if(isset($_FILES["user_image"])){
            $file = $_FILES["user_image"];
            $filename = $file["name"];
            $file_name_split = explode(".",$filename);
            $ext = end($file_name_split);
            $allowed_ext = array("jpg","png","jpeg","gif");
            
            if(in_array($ext,$allowed_ext)){
                $img = $this->upload_img($file,"../images/");
            }
        }
        $errors = "";
        $errors .=((!filter_var($email, FILTER_VALIDATE_EMAIL)) ? "Invalid Email address!<br>" : "" );

        if($errors){

            $err = $this->api_model->response(false, [], $errors);
            $this->set_response($err, REST_Controller::HTTP_OK);

        }else{

            $data = array(
                'first_name' => $this->db->escape_str($first_name),
                'last_name' => $this->db->escape_str($last_name),
                'phone' => $this->db->escape_str($phone),
                'email' => $this->db->escape_str($email),
                'city' => $this->db->escape_str($city),
                'country' => $this->db->escape_str($country)
            );
            if($img){
                $data["user_image"] = $img;
            }
    
            $res = $this->api_model->update_user($id, $data);
            $this->set_response($res, REST_Controller::HTTP_OK);
        }
       
    }


    public function delete_user_delete($id){
        $res = $this->api_model->delete_user($id);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    // ================= Designer APIs ===================

    
    public function get_designers_get(){
        $designers = $this->api_model->get_designers();
        $this->set_response($designers, REST_Controller::HTTP_OK);
    }


    public function submit_design_post(){
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        if($_FILES['eventcard']['size'] > 10){
                    
            $file = $_FILES["eventcard"];
            $filename = $file["name"];
            $file_name_split = explode(".",$filename);
            $ext = end($file_name_split);
            $allowed_ext = array("jpg","png","jpeg","gif","PNG","JPG","JPEG","GIF");
            
            if(in_array($ext,$allowed_ext)){
                //echo $_FILES['eventcard']['name'];exit;
                $img = $this->upload_img($file,"../images/event_card/");
                $event_data = [
                    'event_card' => $img,
                    'design_status' => 3
                ];

                $res = $this->api_model->edit_event($event_data, ['id' => $event_id]);
                $this->response($res, REST_Controller::HTTP_OK);
            }else{
                $this->set_response('Invalid file format', REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    function accept_design_post(){
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        $designer_id = $this->db->escape_str($this->input->post('designer_id'));

        $data = [
            'design_status' => 1,
        ];

        $condition = [
            'id' => $event_id,
            'designer_id' => $designer_id
        ];

        $res = $this->api_model->edit_event($data, $condition);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    function reject_design_post(){
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        $designer_id = $this->db->escape_str($this->input->post('designer_id'));

        $data = [
            'design_status' => 2,
        ];

        $condition = [
            'id' => $event_id,
            'designer_id' => $designer_id
        ];

        $res = $this->api_model->edit_event($data, $condition);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    // ================= Packages APIs ===================

    public function get_packages_get(){
        $data = $this->api_model->get_packages();
        $this->set_response($data, REST_Controller::HTTP_OK);
    }

    public function validate_package($pkg_name, $discount_code){
        // $pkg_name = $this->db->escape_str($this->input->post('package_name'));
        // $discount_code = $this->db->escape_str($this->input->post('discount_code'));

        $errors = "";
        $errors .= (!$this->api_model->check_existance('promo_codes', ['code' => $discount_code,'status' => 0])) ? "Invalid promo code" : "";
        $errors .=($this->api_model->check_existance('packages', ['package_name' => $pkg_name])) ? "Package name already taken" : "";

        if(!$errors){
            $res = [
                'status' => true,
                'data' => [],
                'message' => 'Package is valid'
            ];
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => $errors
            ];
            
        }
        return $res;
        //$this->set_response($res, REST_Controller::HTTP_OK);

    }


    public function add_package_post(){
        $price_per_pkg = 2;
        $package_name = $this->input->post('package_name');
        $discount_code = $this->input->post('discount_code');
        $no_of_people = $this->input->post('no_of_people');
        $package_id = $this->input->post('package_id');
        $user_id = $this->input->post('user_id');
        $pkg_price = ($price_per_pkg * $no_of_people);
        
        if(!$package_id){
            $validate_package = $this->validate_package($package_name, $discount_code);
            //echo "<pre>"; print_r($validate_package);exit;
            if($validate_package['status']){
                //echo "<pre>"; print_r($this->input->post());exit;
                $pkg_array = array(
                    'package_name' => $this->db->escape_str($package_name),
                    'package_price' => $this->db->escape_str($pkg_price),
                    'package_people' => $this->db->escape_str($no_of_people),
                    'package_type' => 1,
                    'user_id' => $this->db->escape_str($user_id),
                    'promo_code' => $this->db->escape_str($discount_code)
                );  
                $res = $this->api_model->add_package($pkg_array);
                // if($discount_code){
                //     $set_promocode = $this->api_model->update_promocode(['status' => 1, 'event_id' => $event_id], ['code' => $discount_code]);
                // }
                $this->set_response($res, REST_Controller::HTTP_OK);
            }else{
                $this->set_response($validate_package, REST_Controller::HTTP_OK);
            }

            //$res = $this->api_model->edit_event(["package_id"=>$package_id], ['id' => $event_id]);
        }
        
        

    }


    // ================= Events APIs ===================

    public function add_event_post(){

        $user_id = $this->input->post('user_id');
        $event_name = $this->input->post('event_name');
        $event_date = $this->input->post('event_date');
        $event_address = $this->input->post('event_address');

        // Packages data
        // $price_per_pkg = 2;
        // $package_name = $this->input->post('package_name');
        // $discount_code = $this->input->post('discount_code');
        // $no_of_people = $this->input->post('no_of_people');
        // $package_id = $this->input->post('package_id');
        // $pkg_price = ($price_per_pkg * $no_of_people);
          

        $data = array(
            'user_id' => $this->db->escape_str($user_id),
            'event_name' => $this->db->escape_str($event_name),
            'event_date' => $this->db->escape_str($event_date),
            'event_address' => $this->db->escape_str($event_address),
            'event_status' => 0
        );

        $res = $this->api_model->add_event($data);
        // if($event_id){
        //     if(!$package_id){
        //         $pkg_array = array(
        //             'package_name' => $this->db->escape_str($package_name),
        //             'package_price' => $this->db->escape_str($pkg_price),
        //             'package_people' => $this->db->escape_str($no_of_people),
        //             'package_type' => 1,
        //             'user_id' => $this->db->escape_str($user_id),
        //             'promo_code' => $this->db->escape_str($discount_code)
        //         );  
        //         $package_id = $this->api_model->add_package($pkg_array);
        //         if($discount_code){
        //             $set_promocode = $this->api_model->update_promocode(['status' => 1, 'event_id' => $event_id], ['code' => $discount_code]);
        //         }
        //     }
        // }
            
        // $res = $this->api_model->edit_event(["package_id"=>$package_id], ['id' => $event_id]);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    

    public function get_events_get(){

        $user_id = $this->input->get('user_id');
        $data = [ 'user_id' => $this->db->escape_str($user_id)];

        $data = $this->api_model->get_events($data);
        $this->set_response($data, REST_Controller::HTTP_OK);
    }

    public function edit_event_post(){
        $event_id = $this->input->post('event_id');
        $event_name = $this->input->post('event_name');
        $event_type = $this->input->post('event_type');
        $event_time = $this->input->post('event_time');
        $event_address = $this->input->post('event_address');
        $event_package = $this->input->post('event_package');
        
        $data = [
            'event_name' => $this->db->escape_str($event_name),
            'event_type' => $this->db->escape_str($event_type),
            'event_address' => $this->db->escape_str($event_address),
            'package_name' => $this->db->escape_str($event_package),
            'event_date' => $event_time
        ];

        $res = $this->api_model->edit_event($data, $this->db->escape_str($event_id));
        $this->set_response($res, REST_Controller::HTTP_OK);
    }
    
    public function delete_event_delete($id){
        $res = $this->api_model->delete_event($this->db->escape_str($id));
        $this->set_response($res, REST_Controller::HTTP_OK);
    }
    

    // ================= Category APIs ===================

    public function add_category_post(){
        $category_name = $this->input->post('category_name');
        $phones = $this->input->post('phones');
        $people_per_qr = $this->input->post('no_of_qr');
        $user_id = $this->input->post('user_id');
        $participants = $this->input->post('participants');
        $phones = $this->db->escape_str($phones);
        $user_id = $this->db->escape_str($user_id);

        //echo '<pre>'; print_r($participants);exit;

        $data = [
            'name' => $this->db->escape_str($category_name),
            'people_per_qr' => $this->db->escape_str($people_per_qr),
            'user_id' => $this->db->escape_str($user_id),
            'type' => 1,
            'phones' => ($phones === 'allowed' ? 1: 0),
            'user_id' => $user_id
        ];



        $res = $this->api_model->add_category($data);
        $this->set_response($res, REST_Controller::HTTP_OK);

    }


    public function edit_category_post(){
        $id = $this->input->post('id');
        $id = $this->db->escape_str($id);
        $category_name = $this->input->post('name');
        $phones = $this->input->post('phones');
        $phones = $this->db->escape_str($phones);
        $people_per_qr = $this->input->post('people_per_qr');

        $data = [
            'name' => $this->db->escape_str($category_name),
            'people_per_qr' => $this->db->escape_str($people_per_qr),
            'phones' => ($phones === 'allowed' ? 1: 0),
        ];

        $res = $this->api_model->update_category($data, $id);
        $this->set_response($res, REST_Controller::HTTP_OK);


    }


    public function get_categories_get(){
        $id = $this->input->get('user_id');
        $res = $this->api_model->get_categories($id);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function delete_category_delete($id){
        $res = $this->api_model->delete_category($id);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    // ================= Receptionist APIs ===================

    public function receptionists_get(){
        $res = $this->api_model->get_receptionists();
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    // ================= Cards APIs ===================


    // ================= QR code APIs ===================




    function upload_img($img,$path){
		if ($img["name"] != '') {
            $file_name = $img['name'];
            $size = $img['size'];
            $file_path = "assets/".$path;
            list($txt, $ext) = explode(".", $file_name);
            $actual_image_name = time() . substr(str_replace(" ", "_", $txt), 5) . "." . $ext;
            $tmp = $img['tmp_name'];
            if (move_uploaded_file($tmp, $file_path . $actual_image_name)) {
                return $actual_image_name;
            } else {
                return false;
            }
        }
	}




    // ==============================================
    // Package Data Code (Temporary)

    /*public function add_package($pkg_data){ 

        $pkg_name = $this->input->post('package_name');
        $no_of_people = $this->input->post('no_of_people');
        $no_of_qr = $this->input->post('no_of_qr');
        $user_id = $this->input->post('user_id');
        $discount_code = $this->input->post('discount_code');
        $event_id = $this->input->post('event_id');
        $discount_code = $this->db->escape_str($discount_code);
        $event_id = $this->db->escape_str($event_id);
        $pkg_price = 400;

        $promo_status = $this->api_model->check_promocode($discount_code, $event_id);
        $errors = "";
        $errors .= (!$this->api_model->check_existance('promo_codes', ['code' => $promo_code,'status' => 0])) ? "Invalid promo code<br>" : "";
        $errors .=(!$this->api_model->check_existance('packages', ['package_name' => $pkg_name])) ? "Package name already taken<br>" : "";
        if($errors ==""){
            $data = array(
                'package_name' => $this->db->escape_str($pkg_name),
                'package_price' => $this->db->escape_str($pkg_price),
                'package_people' => $this->db->escape_str($no_of_people),
                'package_type' => 1,
                'user_id' => $this->db->escape_str($user_id),
                'promo_code' => $discount_code
            );  
            $pkg_id = $this->api_model->add_package($data);
            if($discount_code!=""){
                $this->api_model->update_pkg($discount_code,$pkg_id);
            }else{

            }
            $this->set_response($res, REST_Controller::HTTP_OK);

        }else{
            $res = [
                'status' => false,
                'data' =>[],
                'message' => $errors
            ];
            return $this->set_response($res, REST_Controller::HTTP_NOT_FOUND);
        }
        
    }*/
    // ===============================================

}