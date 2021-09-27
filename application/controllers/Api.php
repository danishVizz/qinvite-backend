<?php

require APPPATH . 'libraries/REST_Controller.php';


class Api extends REST_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model('api_model');
        $this->load->helper('common_helper');
    }

    function preview($data){
        echo '<pre>'; print_r($data);exit;
    }

    function search_get(){
        $keyword = $this->db->escape_str($this->input->get('keyword'));
        $data = $this->api_model->search($keyword);
        $events = $data['data'];
        foreach($events as $event){
            $receptionists = $this->api_model->get_event_receptionists($event->id);
            if($receptionists){
                $event->receptionists = $receptionists;
            }else{
                $event->receptionists = [];
            }
            
            $package = $this->api_model->get_event_package(['id' => $event->package_id]);
            if($package){
                $event->package_details = $package;
            }else{
                $event->package_details = [];
            }

            $participants = $this->api_model->get_participants(['event_id' => $event->id]);
            if($participants){
                $event->participants = $participants;
            }else{
                $event->participants = [];
            }

            // if($event->payment_status == 3){
            //     $payment = $this->api_model->get_payment_details(['order_id' => $event->id]);
            //     if($payment){
            //         $event->payment_details = $payment;
            //     }else{
            //         $event->payment_details = [];
            //     }
            // }
        }

        $data['data'] = $events;
        $this->set_response($data, REST_Controller::HTTP_OK);
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
        $errors .= ($this->api_model->check_existance('users',['username'=> $cnic, 'status' => 0]) ? 'ID card already exists ': '');
        $errors .= ($this->api_model->check_existance('users',['phone'=> $phone, 'status' => 0]) ? 'Phone Number already exists ': '');
        $errors .= ($this->api_model->check_existance('users',['email'=> $email, 'status' => 0]) ? 'Email already exists ': '');

        if($errors ==""){
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
        }else{
            $err = $this->api_model->response(false, [], $errors);
            $this->set_response($err, REST_Controller::HTTP_OK);
        }

       
    }

    public function get_user_get(){
        $user_id = $this->input->get('user_id');
        $data = ['id' => $this->db->escape_str($user_id)];

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
            if($res['status']){
                if($res['data']->user_image){
                    $res['data']->user_image = base_url().'images/user_img/'.$res['data']->user_image;
                }

                if($res['data']->role == 5){
                    $earnings = $this->api_model->designer_total_earnings($res['data']->id);
                    if($earnings){
                        $res['data']->earnings = $earnings;
                    }else{
                        $res['data']->earnings = 0;
                    }
                }
            }
            $this->set_response($res, REST_Controller::HTTP_OK);
        }else{
            $this->set_response("Invalid Credentials", REST_Controller::HTTP_OK);
        }
    }


    public function forget_password_post(){
        $number = $this->db->escape_str($this->input->post('number'));
        $errors = ($this->api_model->check_existance('users',['phone' => $number]) ? "": "User doesn't exist");
        if($this->num_exists($number)){
            if(!$errors){
                $user = $this->api_model->get_user(['phone' => $number]);
                $user = $user['data'][0];
                $otp = rand(1111,9999);
                $message = 'Your Qinvite verification code is '.$otp;
                $data = [
                    'phone' => $number,
                    'body'  => $message
                ];
                $result = $this->send_otp($data);
                // $this->preview($data);
                if($result->sent == 1){
                    $res = [
                        'status' => true,
                        'data' => [
                            'otp' => $otp,
                            'user_id' => $user->id,
                            'phone' => $user->phone
                        ],
                        'message' => 'Message sent successfully'
                    ];
                }else{
                    $res = [
                        'status' => false,
                        'data' => [],
                        'message' => 'Message sending Failed'
                    ];
                }
            }else{
                $res = [
                    'status' => false,
                    'data' => [],
                    'message' => $errors
                ];
            }
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => "Number doesn't exist on whatsapp, if you want to reset please contact support. Or register with whatsapp number."
            ];

            // 'message' => ["Number doesn't exist on whatsapp","if you want to reset please contact support", "Or register with whatsapp number."]
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function update_password_post(){
        $user_id = $this->db->escape_str($this->input->post('user_id'));
        $password = $this->db->escape_str($this->input->post('password'));

        $data = [
            'password' => md5($password)
        ];

        $res = $this->api_model->update_user(['id' => $user_id], $data);

        if($res['status']){
            $res['message'] = "Password updated successfully";
        }
        

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function update_user_post(){

        $user_id  = $this->db->escape_str($this->input->post('user_id'));
        $cnic = $this->input->post('cnic');
        $first_name = $this->input->post('firstname');
        $last_name = $this->input->post('lastname');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $city = $this->input->post('city');
        $country = $this->input->post('country');
        $img = false;
        if(isset($_FILES["user_image"])){
            $file = $_FILES["user_image"];
            $filename = $file["name"];
            $file_name_split = explode(".",$filename);
            $ext = end($file_name_split);
            $allowed_ext = array("jpg","png","jpeg","gif");
            
            if(in_array($ext,$allowed_ext)){
                $img = $this->upload_img($file,"images/user_img/");
            }
        }
        $errors = "";
        if($email){
            $errors .=((!filter_var($email, FILTER_VALIDATE_EMAIL)) ? "Invalid Email address!<br>" : "" );
        }

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
                'country' => $this->db->escape_str($country),
                'role' => 2
            );
            if($img){
                $data['user_image'] = $img;
            }
             
    
            $res = $this->api_model->update_user(['id' => $user_id], $data);
            if($res['data']->user_image){
                $res['data']->user_image = base_url().'images/user_img/'.$res['data']->user_image;
            }
            $this->set_response($res, REST_Controller::HTTP_OK);
        }
       
    }


    public function delete_user_delete($id){
        $res = $this->api_model->delete_user($id);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    // ================= Designer APIs ===================

    
    public function get_designers_get(){
        // $user_id = $this->db->escape_str($this->input->get('user_id'));
        $event_id = $this->db->escape_str($this->input->get('event_id'));
        $designers = $this->api_model->get_designers();
        if($designers){
            // foreach($designers as $k => $v){
            //     $res = $this->api_model->check_designer_user(['event_id' => $event_id, 'designer_id' => $v->id]);
            //     if($res){
            //         foreach($res as $key => $value){
            //             $v->$key = $value;
            //         }
                    
            //     }
            // }
            $res = [
                'status' => true,
                'data' => $designers,
                'message' => 'Data found'
            ];
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Data not found'
            ];
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    public function get_event_designer_get(){
        $event_id = $this->db->escape_str($this->input->get('event_id'));
        $designers = $this->api_model->get_designers();
        $event_designer = [];
        if($designers){
            // $this->preview($designers);
            foreach($designers as $k => $v){
                $res = $this->api_model->check_designer_user(['event_id' => $event_id, 'designer_id' => $v->id]);
                
                if($res){
                    foreach($res as $key => $value){
                        $v->$key = $value;
                    }

                    $event_designer  = $v;
                }
                
                $res = [
                    'status' => true,
                    'data' => $event_designer,
                    'message' => 'Data found successfully'
                ];
                
            }
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Data not found'
            ];
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function request_designer_post(){
        $designer_id = $this->db->escape_str($this->input->post('designer_id'));
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        $user_id = $this->db->escape_str($this->input->post('user_id'));
        $data = [
            'designer_id' => $designer_id,
            'event_id'  => $event_id,
            'user_id' => $user_id,
            'dated' => date('Y-m-d H:i:s'),
            'design_status'    => 0,
            'request_status' => 1
        ];

        $res = $this->api_model->request_designer($data);

        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    // ================= Packages APIs ===================

    public function get_packages_get(){
        $user_id = $this->input->get('user_id');
        $data = $this->api_model->get_packages(['user_id' => $user_id]);
        $this->set_response($data, REST_Controller::HTTP_OK);
    }

    public function validate_package($pkg_name, $discount_code){
        // $pkg_name = $this->db->escape_str($this->input->post('package_name'));
        // $discount_code = $this->db->escape_str($this->input->post('discount_code'));

        $errors = "";
        if($discount_code){
            $errors .= (!$this->api_model->check_existance('promo_codes', ['code' => $discount_code,'status' => 0])) ? "Invalid promo code" : "";
        }
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
        $package_name = $this->input->post('package_name');
        $discount_code = $this->input->post('discount_code');
        $no_of_people = $this->input->post('no_of_people');
        $user_id = $this->input->post('user_id');
        $price_per_qr = $this->api_model->get_price();
        $pkg_price = ($price_per_qr * $no_of_people);

        $validate_package = $this->validate_package($package_name, $discount_code);
        if($validate_package['status']){
            if($discount_code){
                $discount = $this->api_model->get_promocode($discount_code);
                if($discount){
                    $discount = $pkg_price * ($discount->discount / 100);
                    $pkg_price -= $discount;
                }
            }
            $pkg_array = array(
                'package_name' => $this->db->escape_str($package_name),
                'package_price' => $this->db->escape_str($pkg_price),
                'package_people' => $this->db->escape_str($no_of_people),
                'package_type' => 1,
                'user_id' => $this->db->escape_str($user_id),
                'promo_code' => $this->db->escape_str($discount_code)
            );  
            $package_id = $this->api_model->add_package($pkg_array);
            if($discount_code){
                $res = $this->api_model->update_promocode(['status' => 1, 'package_id' => $package_id], ['code' => $discount_code]);
            }
            if($package_id){
                $res = [
                    'status' => true,
                    'data' => [],
                    'message' => "Package created successfully"
                ];
            }
            $this->set_response($res, REST_Controller::HTTP_OK);
        }else{
            $this->set_response($validate_package, REST_Controller::HTTP_OK);
        }       

    }

    public function upgrade_package_post(){

        $package_id = $this->db->escape_str($this->input->post('package_id'));
        $people = $this->db->escape_str($this->input->post('people'));
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        $user_id = $this->db->escape_str($this->input->post('user_id'));
        $user = $this->api_model->get_user(['id' => $user_id]);
        $user = $user['data'][0];

        $package = $this->api_model->get_event_package(['id' => $package_id]);
        if($package->package_type == 0){
            $package->package_name .= " Upgraded";
            $price = $this->api_model->get_price();
            $new_price = $people * $price;
            $package->package_people += $people;
            $package->package_price += $new_price;
            $package->user_id = $user_id;

            $pkg_array = array(
                'package_name' => $package->package_name,
                'package_price' => $package->package_price,
                'package_people' => $package->package_people,
                'package_type' => 1,
                'user_id' => $user_id,
            );  
            $package_id = $this->api_model->add_package($pkg_array);

            if($package_id){
                $res = $this->api_model->edit_event(['package_id' => $package_id], $event_id);
                if($res){
                    $res =[
                        'status' => true,
                        'data' => [],
                        'message' => 'Package upgraded successfully'
                    ];
                }else{
                    $res =[
                        'status' => false,
                        'data' => [],
                        'message' => 'Something went wrong'
                    ];
                }
            }
        }else{
            $price = $this->api_model->get_price();
            $new_price = $people * $price;
            $package->package_people += $people;
            $package->package_price += $new_price;
            $pkg_array = array(
                'package_price' => $package->package_price,
                'package_people' => $package->package_people,
            ); 

            $res = $this->api_model = $this->api_model->upgrade_package($pkg_array, ['id' => $package->id]);

            if($res){
                $res =[
                    'status' => true,
                    'data' => [],
                    'message' => 'Package upgraded successfully'
                ];
            }else{
                $res =[
                    'status' => false,
                    'data' => [],
                    'message' => 'Something went wrong'
                ];
            }
        }

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function delete_all_packages_get(){
        $user_id = $this->db->escape_str($this->input->get('user_id'));
        $res = $this->api_model->delete_all_packages(['package_type' => 1, 'user_id' => $user_id]);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    // ================= Events APIs ===================

    public function add_event_post(){ 
        
        $user_id = $this->input->post('user_id');
        $event_name = $this->input->post('event_name');
        $event_date = $this->input->post('event_date');
        $event_address = $this->input->post('event_address');
        $package_id = $this->db->escape_str($this->input->post('package_id'));
        $no_of_receptionists = $this->db->escape_str($this->input->post('no_of_receptionists'));
        
        $receptionists = $this->input->post('receptionists');
        
        $errors = "";
        $errors .= (!$this->api_model->check_existance('users', ['id' => $user_id]) ? 'Invalid user' : "");
        
        if(!$errors){
            $data = array(
                'user_id' => $this->db->escape_str($user_id),
                'event_name' => $this->db->escape_str($event_name),
                'package_id' => $package_id,
                'event_date' => $this->db->escape_str($event_date),
                'event_address' => $this->db->escape_str($event_address),
                'no_of_receptionists' => $no_of_receptionists,
                'event_status' => 0
            );
            
    
            $event_id = $this->api_model->add_event($data);
            if($event_id){
                $this->api_model->add_receptionists($event_id, $receptionists);
                                
            }

            $event = $this->api_model->get_events(['id' => $event_id]);
            $event = $event['data'][0];
            $packages = $this->api_model->get_packages(['id' => $event->package_id]);
            $packages = $packages['data'];
            foreach($packages as $k => $v){
                if($v->id == $event->package_id){
                    $event->package_details = $v;
                }
            }


            if(empty($event->package_details)){
                $event->package_details = [];
            }
            

            $res = [
                'status' => true,
                'data'  => $event,
                'message' => "Event created successfully"
            ];
            
        }else{
            $res = [
                'status' => false,
                'data'  => [],
                'message' => $errors
            ];
        }

        $this->set_response($res, REST_Controller::HTTP_OK);
    } 


    function add_event_details_post(){

        //Response return from ongage
		// $post_data = print_r($_REQUEST, true);
		// $data = $post_data;
		// $fp = fopen('upload_inventory.txt', 'w') or exit("Unable to open file!");
		// fwrite($fp, $data);
		// fclose($fp);
		// exit;

        //$this->db->insert('temp', ['content' => $this->input->post('categories_messages')]);exit;
        $event_id = $this->input->post('event_id');
        $categories = $this->input->post('categories');
        $categories_messages = $this->input->post('categories_messages');
        $img_width = $this->input->post('width');
        // echo $categories_messages;
        // $str = json_encode($categories_messages,true);
        
        $categories_messages = json_decode($categories_messages);
        $invites = $this->api_model->get_total_invites($event_id);
        $message_sent = $this->api_model->get_sent_messages($event_id);
        // $this->preview($categories_messages);

        $img = false;
        if(isset($_FILES["event_card"])){
            $file = $_FILES["event_card"];
            $filename = $file["name"];
            $file_name_split = explode(".",$filename);
            $ext = end($file_name_split);
            $allowed_ext = array("jpg","png","jpeg","gif","mp4", "mov","mkv","avi","PNG","JPG","JPEG","GIF","MP4", "MOV","MKV","AVI");
            
            if(in_array($ext,$allowed_ext)){
                $img = $this->upload_img($file,"images/event_card/");
            }
        }

        if($message_sent <= $invites){
            if($img){
                $this->api_model->edit_event(['event_card' => $img], $event_id);
                $card_data = [
                    "event_id" => $event_id,
                    "design_card" => $img,
                ];
                $this->api_model->submit_image($card_data);
            }

            $category_list = $this->api_model->get_categories(['event_id' => $event_id]);
            if($category_list['status']){
                $category_list = $category_list['data'];
                foreach($category_list as $category){
                    $data = [
                                'event_id' => $event_id,
                                'category_id' => $category->id
                            ];
                            // $this->api_model->update_participant(['event_id' => $event_id], ['category_id' => $categories[$i]]);
                            $res = $this->api_model->add_event_category($data);
                }
            }
    
            // for($i =0; $i < count($categories); $i++){
            //     $data = [
            //         'event_id' => $event_id,
            //         'category_id' => $categories[$i]
            //     ];
            //     // $this->api_model->update_participant(['event_id' => $event_id], ['category_id' => $categories[$i]]);
            //     $res = $this->api_model->add_event_category($data);
            // }
            //echo '<pre>';print_r($categories_messages);
            // $categories_messages = json_decode($categories_messages);
            // $this->preview($categories_messages);
            if($categories_messages){
                foreach($categories_messages as $k => $cm){
                    $this->send_invite($event_id, $cm->categoryid, ($cm->message ? $cm->message : ''), ($img ? $img : false), $img_width);
                }
            }else{
                foreach($categories as $k => $category_id){
                    $this->send_invite($event_id, $category_id, '', ($img ? $img : false), $img_width);
                }
            }
        }else{
            $res = [
                'status' => true,
                'data' => [],
                'message' => 'Your invitation limit exceeded'
            ];
            $this->set_response($res, REST_Controller::HTTP_OK);
        }

        //$this->set_response($res, REST_Controller::HTTP_OK);
        
    }

    public function get_events_get(){

        $user_id = $this->input->get('user_id');
        $data = [ 'user_id' => $this->db->escape_str($user_id)];

        $data = $this->api_model->get_events($data);
        
        $events = $data['data'];
        foreach($events as $event){
            $receptionists = $this->api_model->get_event_receptionists($event->id);
            if($receptionists){
                $event->receptionists = $receptionists;
            }
            
            // $card = $this->api_model->get_event_cards(['event_id' => $event->id]);
            // if($card['status']){
            //     $card = $card['data'][0]->design_card;
            //     $event->event_card = base_url().'images/event_card/'.$card;
            // }
            
            if($event->event_card){
                $event->event_card = base_url().'images/event_card/'.$event->event_card;
            }
            $package = $this->api_model->get_event_package(['id' => $event->package_id]);
            if($package){
                $event->package_details = $package;
            }
            
            $participants = $this->api_model->get_participants_events("event_id = {$event->id} AND category_id IN (SELECT id FROM categories WHERE event_id = {$event->id} AND type=0)");
            if($participants){
                $event->participants = $participants;
            }else{
                $event->participants = [];
            }

            $category_ids = $this->api_model->get_category_ids($event->id);
            if($category_ids){
                foreach($category_ids as $k => $v){
                    if($v->pdf){
                        $v->pdf = base_url().'pdf/'.$v->pdf;
                    }else{
                        $v->pdf = '';
                    }
                }
                $event->categories = $category_ids;
            }else{
                $event->categories = [];
            }


            
            // if($event->payment_status == 3){
                //     $payment = $this->api_model->get_payment_details(['order_id' => $event->id]);
                //     if($payment){
                    //         $event->payment_details = $payment;
                    //     }else{
                        //         $event->payment_details = [];
                        //     }
                        // }
        }
                    
                    
            $data['data'] = $events;
            //echo '<pre>';print_r($data['data']);exit;

        
        $this->set_response($data, REST_Controller::HTTP_OK);
    }

    public function edit_event_post(){
        $event_id = $this->input->post('event_id');
        $event_name = $this->input->post('event_name');
        $event_date = $this->input->post('event_date');
        $event_address = $this->input->post('event_address');
        $event_package = $this->input->post('event_package');
        $no_of_receptionists = $this->db->escape_str($this->input->post('no_of_receptionists'));
        $receptionists = $this->input->post('receptionists');
        
        
        $data = [
            'event_name' => $this->db->escape_str($event_name),
            'event_address' => $this->db->escape_str($event_address),
            'event_date' => $event_date,
            'no_of_receptionists' => $no_of_receptionists
        ];

        if($event_package){
            $data['package_name'] = $this->db->escape_str($event_package);
        }

        $res = $this->api_model->edit_event($data, $this->db->escape_str($event_id));
        if($res && $receptionists){
            $res = $this->api_model->delete_receptionists(['event_id' => $event_id]);
            if($res){
                $this->api_model->add_receptionists($event_id, $receptionists);
            }

            $res = [
                'status' => true,
                'data' => [],
                'message' => 'Event updated successfully'
            ];
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Something Went wrong'
            ];
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }
    
    public function delete_event_delete($id){
        $res = $this->api_model->delete_event($this->db->escape_str($id));
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function get_event_cards_get(){
        $event_id = $this->db->escape_str($this->input->get('event_id'));

        $event_cards = $this->api_model->get_event_cards(['event_id' => $event_id, 'designer_id !=' => 0]);
        foreach($event_cards['data'] as $k => $v){
            $v->design_card = base_url() .'images/event_card/' . $v->design_card;
        }
        $this->set_response($event_cards, REST_Controller::HTTP_OK);
    }

    public function delete_all_events_get(){
        $user_id = $this->db->escape_str($this->input->get('user_id'));
        $res = $this->api_model->delete_all_events(['user_id' => $user_id, 'event_status' => 2]);
        if($res['status'] == true){
            $events = $this->api_model->get_events(['user_id' => $user_id]);
            if($events['status' == true]){
                $events = $events['data'];
                foreach ($events as $event) {
                    $receptionists = $this->api_model->get_event_receptionists($event->id);
                    if ($receptionists) {
                        $event->receptionists = $receptionists;
                    }

                    if ($event->event_card) {
                        $event->event_card = base_url().'images/event_card/'.$event->event_card;
                    }
                    $package = $this->api_model->get_event_package(['id' => $event->package_id]);
                    if ($package) {
                        $event->package_details = $package;
                    }
                    
                    $participants = $this->api_model->get_participants_events($event->id);
                    if ($participants) {
                        $event->participants = $participants;
                    } else {
                        $event->participants = [];
                    }

                    $category_ids = $this->api_model->get_category_ids($event->id);
                    if ($category_ids) {
                        foreach ($category_ids as $k => $v) {
                            if ($v->pdf) {
                                $v->pdf = base_url().'pdf/'.$v->pdf;
                            } else {
                                $v->pdf = '';
                            }
                        }
                        $event->categories = $category_ids;
                    } else {
                        $event->categories = [];
                    }
                }
            }

            $res['data'] = $events['data'];
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }
    

    // ================= Category APIs ===================

    public function add_category_post(){
        // $post_data = print_r($_POST, true);
		// $data = $post_data;
		// $fp = fopen('upload_inventory.txt', 'w') or exit("Unable to open file!");
		// fwrite($fp, $data);
		// fclose($fp);
        $category_name = $this->input->post('name');
        $phones = $this->db->escape_str($this->input->post('phones'));
        $phones = ($phones === 'allowed' ? 1: 0);
        $people_per_qr = $this->input->post('people_per_qr');
        $type = $this->input->post('category_type');
        $participants = $this->input->post('participants');
        //$this->preview($participants);
        $user_id = $this->input->post('user_id');
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        $errors = '';
        if($type == 'whatsapp'){
            $type = 0;
        }else if($type == 'pdf'){
            $type = 1;
        }
        // $phones = $this->db->escape_str($phones);
        //$this->preview(stripslashes($participants));
        // $errors = (!$this->api_model->check_existance('categories',['name' => $category_name]) ? '': 'Category name already exists');
        

        if(!$errors){
            $data = [
                'name' => $this->db->escape_str($category_name),
                'people_per_qr' => $this->db->escape_str($people_per_qr),
                'user_id' => $user_id,
                'event_id' => $event_id,
                'type' => $type,
                'phones' => $phones
            ];
            
            $category_id = $this->api_model->add_category($data);
            if($category_id){  
                $participants = json_decode($participants,true);
               // $this->preview($participants);
                foreach($participants as $k => $participant){
                    
                    // $this->preview($participant);
                    $participant['category_id'] = $category_id;
                    $participant['event_id'] = $event_id;
                    $participant['number'] = str_replace([ " ", "(", ")", "-"],"",$participant['number']);
                    $participant_id = $this->api_model->add_participant($participant);
                    $data = $this->api_model->get_participants(['id' => $participant_id]);
                    // $qr_path = $this->qr_code_model($data[0]);
                    // $participant['qr_img'] = $qr_path;
                    $this->api_model->update_participant($participant, ['id' => $participant_id]);
                    //$this->preview($participant);
                }
                // for($x=0;$x<count($participants);$x++){
                //     $participant = json_decode($participants[$x],true);
                //     //$participant['event_id'] = $event_id;
                //     $participant['category_id'] = $category_id;
                //     $participant_id = $this->api_model->add_participant($participant);
                //     $data = $this->api_model->get_participants(['id' => $participant_id]);
                //     $qr_path = $this->qr_code_model($data[0]);
                //     //$this->preview($data);
                //     $participant['qr_img'] = $qr_path;
                //     //$this->preview($participant);
    
                // }
                $res = [
                    'status' => true,
                    'data' => [],
                    'message' => 'Category added successfully'
                ];
            }else{
                $res = [
                    'status' => false,
                    'data' => [],
                    'message' => 'something went wrong'
                ];
            }
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => $errors
            ];
        }
       
        // $this->db->insert('temp', ['content' => json_encode($participants)]);
        // $tempdata = $this->db->get('temp')->result();
        // $this->preview($tempdata);
        $this->set_response($res, REST_Controller::HTTP_OK);

    }


    public function edit_category_post(){
        $post_data = print_r($_POST, true);
		$data = $post_data;
		$fp = fopen('upload_inventory.txt', 'w') or exit("Unable to open file!");
		fwrite($fp, $data);
		fclose($fp);
        $id = $this->db->escape_str($this->input->post('id'));
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        $category_name = $this->db->escape_str($this->input->post('name'));
        $phones = $this->db->escape_str($this->input->post('phones'));
        $people_per_qr = $this->db->escape_str($this->input->post('people_per_qr'));
        $participants = $this->input->post('participants');
        // $participants = stripslashes($participants);
        //$this->preview($participants);
        //echo json_decode($participants);exit;
        //echo json_decode($participants, true); exit;
        // $participants = html_entity_decode($participants);
        $participants = json_decode($participants, true);
        //  ============= Json ERROR CHECK =============
        // foreach ($participants as $string) {
        //     echo 'Decoding: ' . $string;
        //     json_decode($string);
        
        //     switch (json_last_error()) {
        //         case JSON_ERROR_NONE:
        //             $error = ' - No errors';
        //         break;
        //         case JSON_ERROR_DEPTH:
        //             $error = ' - Maximum stack depth exceeded';
        //         break;
        //         case JSON_ERROR_STATE_MISMATCH:
        //             $error = ' - Underflow or the modes mismatch';
        //         break;
        //         case JSON_ERROR_CTRL_CHAR:
        //             $error = ' - Unexpected control character found';
        //         break;
        //         case JSON_ERROR_SYNTAX:
        //             $error = ' - Syntax error, malformed JSON';
        //         break;
        //         case JSON_ERROR_UTF8:
        //             $error = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        //         break;
        //         default:
        //             $error = ' - Unknown error';
        //         break;
        //     }
        //     $this->set_response($error, REST_Controller::HTTP_OK);
           
        // }


        $data = [
            'name' => $category_name,
            'people_per_qr' => $people_per_qr,
            'phones' => ($phones === 'allowed' ? 1: 0),
        ];
         
        $res = $this->api_model->update_category($data, $id);
        if($res){
            $this->api_model->delete_participants($id);
            foreach($participants as $k => $participant){
                if(isset($participant['participant_id'])){
                    unset($participant['participant_id']);
                }
                $participant['category_id'] = $id;
                $participant['event_id'] = $event_id;
                $participant['number'] = str_replace([ " ", "(", ")", "-"],"",$participant['number']);
                $participant_id = $this->api_model->add_participant($participant);
                $data = $this->api_model->get_participants(['id' => $participant_id]);
                // $qr_path = $this->qr_code_model($data[0]);
                // $participant['qr_img'] = $qr_path;
                $this->api_model->update_participant($participant, ['id' => $participant_id]);
            }
            $res = [
                'status' => true,
                'data' => [],
                'message' => 'Category edited successfully'
            ];
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'something went wrong'
            ];
        }
    
        // //$res = $this->api_model->delete_participants($id);
        // $res = $this->api_model->update_category($data, $id);
        // if($res){
        //     $participants = json_decode($participants,true); 
        //     $this->preview($participants);
        //     if(!empty($participants)){
        //         for($x=0;$x<count($participants);$x++){
        //             $participant = json_decode($participants[$x],true);
        //             $this->preview($participant);
        //             //$participant['event_id'] = $event_id;
        //             $participant['category_id'] = $id;      
        //             $path = $this->generate_qr($participant);
        //             $participant['qr_img'] = $path;
        //             if(!$participant['id']){
        //                 $participant_id = $this->api_model->add_participant($participant);
        //             }
        //             $data = $this->api_model->get_participants(['id' => $participant_id]);
        //             //$this->preview($data);
        //         }
        //     }
        // }
        $this->set_response($res, REST_Controller::HTTP_OK);


    }


    public function move_participant_post(){
        $category_id = $this->db->escape_str($this->input->post('category_id'));
        $participant_id = $this->db->escape_str($this->input->post('participant_id'));
        $participant = $this->input->post('participant');

        if($participant_id){
            $data = $this->api_model->get_participants(['id' => $participant_id]);
            $participant = $data[0];
            // $this->preview($participant);
            $participant->category_id = $category_id;
            // $qr_path = $this->qr_code_model($participant);
            // $participant->qr_img = $qr_path;
            $res = $this->api_model->update_participant($participant, ['id' => $participant_id]);
            // $res = $this->api_model->update_participant(['category_id' => $category_id],['id' => $participant_id]);
            if($res){
                $res = [
                    'status' => true,
                    'data' => [],
                    'message' => 'Participant moved successfully'
                ];
            }else{
                $res = [
                    'status' => false,
                    'data' => [],
                    'message' => 'Something went wrong!'
                ];
            }
        }else if($participant){
            $participant = json_decode($participant, true);
            // $participant = $participant[0];
            $participant['category_id'] = $category_id;
            $participant['number'] = str_replace([ " ", "(", ")", "-"],"",$participant['number']);
            // $this->preview($participant);
            $participant_id = $this->api_model->add_participant($participant);
            $data = $this->api_model->get_participants(['id' => $participant_id]);
            // $qr_path = $this->qr_code_model($data[0]);
            // $participant['qr_img'] = $qr_path;
            $this->api_model->update_participant($participant, ['id' => $participant_id]);

            if($participant_id){
                $res = [
                    'status' => true,
                    'data' => [],
                    'message' => 'Participant moved successfully'
                ];
            }else{
                $res = [
                    'status' => false,
                    'data' => [],
                    'message' => 'Something went wrong!'
                ];
            }
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Something went wrong!'
            ];
        }
        
        $this->set_response($res, REST_Controller::HTTP_OK);
        
    }


    public function get_categories_get(){
        $user_id = $this->db->escape_str($this->input->get('user_id'));
        $event_id = $this->db->escape_str($this->input->get('event_id'));
        $type = $this->db->escape_str($this->input->get('category_type'));
        if($type == 'whatsapp'){
            $type = 0;
        }else if($type == 'pdf'){
            $type = 1;
        }
        $res = $this->api_model->get_categories(['event_id' => $event_id, 'type' => $type]);
        $categories = $res['data'];
        $invites = $this->api_model->get_total_invites($event_id);
        $message_sent = $this->api_model->get_sent_messages($event_id);
        $res['data'][] = ["total_invites" => $invites];
        for($i = 0; $i < count($categories); $i++){
            $get_participants = $this->api_model->get_participants(['category_id' => $categories[$i]->id]);
            $categories[$i]->participants = $get_participants;
        }
        $res['data'] = [
            'invites' => $invites,
            'message_sent' => $message_sent,
            'categories' => $categories
        ];
        // $this->preview($res);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    public function get_message_details_get(){
        $event_id = $this->db->escape_str($this->input->get('event_id'));
        $participants = $this->api_model->get_participants("event_id = '".$event_id."' AND (status = 1 OR status = 2 OR status = 3)");
        if($participants){
            foreach($participants as $k => $participant){
                $category = $this->api_model->get_the_category(['id' => $participant->category_id]);
                // $this->preview($category);
                $details[] = [
                    'name' => $participant->name,
                    'phone' => $participant->number,
                    'category' => $category->name,
                    'status' => $participant->status
                ];
            }

            $res = ['status' => true, 'data' => $details, 'message' => 'Data found successfully'];
        }else{
            $res = ['status' => false, 'data' => [], 'message' => 'No participants found'];
        }

        $this->set_response($res, REST_Controller::HTTP_OK);
    }
    
    public function delete_category_delete($id){
        $this->api_model->delete_participants($id);
        $res = $this->api_model->delete_category($id);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    // ================= Participants APIs ===================

    public function get_participants_get(){
        $category_id = $this->db->escape_str($this->input->get('category_id'));
        $event_id = $this->db->escape_str($this->input->get('event_id'));

        $participants = $this->api_model->get_participants(['category_id' => $category_id, 'event_id' => $event_id]);

        $this->set_response($participants, REST_Controller::HTTP_OK);
    }

    public function add_participants_post(){
        $participants = $this->input->post('participants');
        $category_id = $this->db->esacape_str($this->input->post('category_id'));
        // $event_id = $this->db->esacape_str($this->input->post('event_id'));

        if($category_id){  
            $participants = json_decode($participants,true);
            for($i=0;$i<count($participants);$i++){
                $participant = json_decode($participants[$i],true);
                //$participant['event_id'] = $event_id;
                $participant['category_id'] = $category_id;
                $participant['number'] = str_replace([ " ", "(", ")", "-"],'',$participant['number']);
                $res = $this->api_model->add_participant($participant);
            }
        }


        $this->set_response($res, REST_Controller::HTTP_OK);
        
    }


    public function remove_participant_delete($id){
        $res = $this->api_model->remove_participant($id);
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    // ================= Receptionist APIs ===================

    public function receptionists_get(){
        $res = $this->api_model->get_receptionists();
        $this->set_response($res, REST_Controller::HTTP_OK);
    }


    

    // ================= QR code APIs ===================


    function qr_code_model($participant, $img_width){
        $participant->category = $this->api_model->get_the_category(['id' => $participant->category_id]);
        $participant->host_data = $this->api_model->get_user(['id' => $participant->category->user_id]);
        $participant->host_data = $participant->host_data['data'][0];
        unset($participant->host_data->password);
        unset($participant->host_data->username);
        unset($participant->host_data->role);
        unset($participant->host_data->status);
        unset($participant->host_data->user_created);
        unset($participant->host_data->user_updated);
        $qr_path = $this->generate_qr($participant, $img_width);
        return $qr_path;
    }
    
    function generate_qr($data, $img_width){
        if($data){
            $img_size = 1300;
            $qr_size = 150;
            $size = 0;
            if($img_width){
                $qr_size = round(($qr_size/$img_size)*$img_width,0);

                if($qr_size > 41 && $qr_size < 82){
                    $size = 1;
                }else if($qr_size >82 && $qr_size < 123){
                    $size = 2;
                }else if($qr_size > 123 && $qr_size < 164){
                    $size = 3;
                }else if($qr_size > 164 && $qr_size < 205){
                    $size = 4;
                }else if($qr_size > 205 && $qr_size < 246){
                    $size = 5;
                }else if($qr_size > 246 && $qr_size < 287){
                    $size = 6;
                }else if($qr_size > 287 && $qr_size < 328){
                    $size = 7;
                }else if($qr_size > 328 && $qr_size < 369){
                    $size = 8;
                }else if($qr_size > 369 && $qr_size < 410){
                    $size = 9;
                }else if($qr_size > 410){
                    $size = 10;
                }
            }else{
                $size = 1;
            }


            $this->load->library('ciqrcode');
            //header("Content-Type: image/png");
            $params['data'] = json_encode($data);
            $params['level'] = 'L';
            $params['size'] = $size;
            $img_name = time().'-'.$data->id.'.png';
            $path = FCPATH.'images/qr_codes/'.$img_name;
            $params['savename'] = $path;
            $this->ciqrcode->generate($params); 
            return $img_name;
        }else{
            return false;
        }
    }

    

    function merge_qr($dir,$qr_img, $event_card){
        $this->load->library('image_lib');
        $config['image_library'] = 'gd2';
        $config['source_image'] = FCPATH.'images/event_card/'.$event_card;
        $config['wm_overlay_path'] = FCPATH.'images/qr_codes/'.$qr_img; 
        $config['wm_type'] = 'overlay';
        $config['wm_opacity'] = '100';
        $event_card = str_replace('.jpg','',$event_card);
        $qr_img = str_replace('.jpg','',$qr_img);
        $image_name = $event_card.'-'.$qr_img.'.jpg';
        $config['new_image'] =$dir.'/'.$image_name;
        $config['wm_x_transp'] = '0';
        $config['wm_y_transp'] = '0';
        // $config['dynamic_output'] = true;
        // $config['wm_vrt_alignment'] = 'top';
        // $config['wm_hor_alignment'] = 'right';
        // $config['wm_vrt_offset'] = '450';
        // $config['wm_hor_offset'] = '100';
        // $config['wm_padding'] = '20';
        // $this->image_lib->clear();
        $this->image_lib->initialize($config);
        $this->image_lib->watermark();
        //exit('This is it');
        return $image_name;
    }


    function get_dir($event_id){
        $path = FCPATH."images/qr_cards";
        $dir = scandir($path);
        if(in_array($event_id,$dir)){
            return $path.'/'.$event_id;
        }else{
            mkdir($path."/".$event_id);
            return $path.'/'.$event_id;
        }
    }

    function prepare_cards($event_id, $event_card = false, $img_width){
        if(!$event_card){
            $event_card = $this->api_model->get_event_cards(['event_id' => $event_id]);
            //$this->preview($event_card);
            if($event_card['status']){
                $event_card = $event_card['data'][0]->design_card  ;
            //$this->preview($event_card);
            }else{
                $resp = [
                    'status' => false,
                    'data' => [],
                    'message' => "Event card not found"
                ];
                $this->set_response($resp, REST_Controller::HTTP_NOT_FOUND);
            }
        }
        $new_dir = $this->get_dir($event_id);
        $participants = $this->api_model->get_participants(['event_id' => $event_id]);
        for($i = 0; $i<count($participants); $i++){
            $qr_path = $this->qr_code_model($participants[$i], $img_width);
            $this->api_model->update_participant(['qr_img' => $qr_path],['id' => $participants[$i]->id]);
            $img_name = $this->merge_qr($new_dir,$qr_path,$event_card);
            $this->api_model->update_participant(['card_img' => $img_name],['id' => $participants[$i]->id]);

        }
    }


    function resend_message_get($event_id){
        $categories = $this->api_model->get_category_id($event_id);
        $flag = true;
        // $this->preview($categories);
        if($categories){
            //$this->preview($categories);
            foreach($categories as $k => $v){
                $participants = $this->api_model->get_participants(['category_id' => $v->category_id, 'status' => 0]);
                
                // $this->preview($participants);
                if($participants){
                    $flag = true;
                    foreach($participants as $participant){  
                        $card_path = base_url().'images/qr_cards/'.$event_id.'/'.$participant->card_img;
                        $type = pathinfo($card_path, PATHINFO_EXTENSION);
                        $img = file_get_contents($card_path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($img);
                        $data = array("phone"=>$participant->number,"body"=>$base64, 'filename' => 'Qinvite.jpg', 'caption' => $v->event_message);
                        $sms = $this->curl_sms($data);
                        if($sms->sent == true){
                            //$this->preview($sms);
                            $this->api_model->send_message($sms->id,$participant->id);
                        }
                    }
                }else{
                    $flag = false;
                }
                
                
            }

            if($flag){
                $res = [
                    'status' => true,
                    'data' => [],
                    'message' => "Message have been resent successfully"
                ];
            }else{
                $res = [
                    'status' => false,
                    'data' => [],
                    'message' => "No pending messages left"
                ];
            }

            $this->set_response($res, REST_Controller::HTTP_OK);
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => "No categories found"
            ];

            $this->set_response($res, REST_Controller::HTTP_OK);
        }
    }

    function upload_img($img,$path){
		if ($img["name"] != '') {
            $file_name = $img['name'];
            $size = $img['size'];
            $file_path = $path;
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




/**
 * ======================================================
 * APIs for the Recptionist App
 * =====================================================
 */

function get_rp_events_get(){
    $receptionist_id = $this->db->escape_str($this->input->get('receptionist_id'));
    $events = $this->api_model->get_rp_events($receptionist_id);
    $this->set_response($events, REST_Controller::HTTP_OK);
}

function check_in_get($id){
    $participant = $this->api_model->get_participants(['id' => $id]);
    $participant = $participant[0];
    $event = $this->api_model->get_events(['id' => $participant->event_id]);
    $event = $event['data'][0];
    if(date('Y-m-d H:i:s') < date('Y-m-d H:i:s', strtotime($event->event_date))){
        $res = $this->api_model->check_in($id);
    }else{
        $res = ['status' => false, 'data' => [], 'message' => 'Event already closed'];
    }

    
    $this->set_response($res, REST_Controller::HTTP_OK);

}

function check_out_get($id){
    $res = $this->api_model->check_out($id);
    $this->set_response($res, REST_Controller::HTTP_OK);
}

/**
 * ======================================================
 * APIs for the Designer App
 * =====================================================
 */


    function get_event_requests_get(){
        $designer_id = $this->db->escape_str($this->input->get('designer_id'));
        $event_requests = $this->api_model->get_event_requests($designer_id);
        $this->set_response($event_requests, REST_Controller::HTTP_OK);
    }

    
    // public function submit_design_post(){
    //     $event_id = $this->db->escape_str($this->input->post('event_id'));
    //     $designer_id = $this->db->escape_str($this->input->post('designer_id'));
    //     if($_FILES['event_card']['size'] > 10){
                    
    //         $file = $_FILES["event_card"];
    //         $filename = $file["name"];
    //         $file_name_split = explode(".",$filename);
    //         $ext = end($file_name_split);
    //         $allowed_ext = array("jpg","png","jpeg","gif","mp4", "mov","mkv","avi","PNG","JPG","JPEG","GIF","MP4", "MOV","MKV","AVI");
            
    //         if(in_array($ext,$allowed_ext)){
    //             //echo $_FILES['event_card']['name'];exit;
    //             $img = $this->upload_img($file,"images/event_card/");
    //             $card_data = [
    //                 'design_card' => $img,
    //                 'event_id' => $event_id,
    //                 'designer_id' => $designer_id,
    //             ];

    //             //$res = $this->api_model->edit_event($event_data, $event_id);
    //             $res = $this->api_model->submit_image($card_data);
    //             $this->api_model->update_event_request(['design_status' => 3], ['event_id' => $event_id, 'designer_id' => $designer_id]);

    //             if($res){
    //                 $res=[
    //                     'status'=> true,
    //                     'data'=> [],
    //                     'message' => "Image uploaded successfully"

    //                 ];
    //             }

    //             $this->response($res, REST_Controller::HTTP_OK);
    //         }else{
    //             $this->set_response('Invalid file format', REST_Controller::HTTP_NOT_FOUND);
    //         }
    //     }
    // }

    public function submit_design_get(){
        $event_id = $this->db->escape_str($this->input->get('event_id'));
        $designer_id = $this->db->escape_str($this->input->get('designer_id'));
        
        if($_FILES['event_card']['size'] > 10){
                    
            $file = $_FILES["event_card"];
            $filename = $file["name"];
            $file_name_split = explode(".",$filename);
            $ext = end($file_name_split);
            $allowed_ext = array("jpg","png","jpeg","gif","mp4", "mov","mkv","avi","PNG","JPG","JPEG","GIF","MP4", "MOV","MKV","AVI");
            
            if(in_array($ext,$allowed_ext)){
                //echo $_FILES['event_card']['name'];exit;
                $img = $this->upload_img($file,"images/event_card/");
                $card_data = [
                    'design_card' => $img,
                    'event_id' => $event_id,
                    'designer_id' => $designer_id,
                ];

                //$res = $this->api_model->edit_event($event_data, $event_id);
                $res = $this->api_model->submit_image($card_data);
                $this->api_model->update_event_request(['design_status' => 3], ['event_id' => $event_id, 'designer_id' => $designer_id]);

                if($res){
                    $res=[
                        'status'=> true,
                        'data'=> [],
                        'message' => "Image uploaded successfully"

                    ];
                }

                $this->response($res, REST_Controller::HTTP_OK);
            }else{
                $this->set_response('Invalid file format', REST_Controller::HTTP_NOT_FOUND);
            }
        }

        $this->load->view('admin/upload_image');
    }

    public function accept_design_post(){
        $design_status = $this->db->escape_str($this->input->post('design_status'));
        $event_id = $this->db->escape_str($this->input->post('event_id'));
        $deadline = $this->db->escape_str($this->input->post('deadline'));

        if($design_status == 'accept'){
            $design_status = 1;
        }else if($design_status == 'reject'){
            $design_status = 2;
        }

        $data['design_status'] = $design_status;
        if($design_status == 1){
            $data['designer_appointed'] = date('Y-m-d H:i:s');
        }
            $data['design_deadline'] = $deadline;

        // $condition = [
        //     'id' => $event_id,
        //     'designer_id' => $designer_id
        // ];

        $res = $this->api_model->update_event_request($data, ['event_id' => $event_id]);
        if($res){
            $res = [
                'status' => true,
                'data' => [],
                'message' => "Request is accepted by designer"
            ];
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Request is rejected by designer'
            ];
        }
        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    public function toggle_availability_get(){
        $id = $this->input->get('user_id');
        $trigger = $this->input->get('trigger');

        if($trigger == 'yes'){
            $res = $this->api_model->toggle_availability(['availability' => 0], ['id' => $id]);
        }else if($trigger == 'no'){
            $res = $this->api_model->toggle_availability(['availability' => 1], ['id' => $id]);
        }

        if($res){
            $res = [
                'status' => true,
                'data' => [],
                'message' => 'Availability is changed'
            ];
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Something went wrong!'
            ];
        }

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    /**
 * ======================================================
 * SMS send
 * =====================================================
 */

    function send_invite($event_id, $category_id, $message, $event_card, $img_width){
        // $event_id = $this->db->escape_str($this->input->post('event_id'));
        // $category_id = $this->db->escape_str($this->input->post('category_id'));
        // $message = $this->input->post('message');
        $is_video = false;
        $ext = explode('.',$event_card);
        $ext = end($ext);
        if($ext == 'mp4' || $ext == 'mov' || $ext == 'mkv' || $ext == 'avi'){
            $is_video = true;
        }else{
            $is_video = false;
        }
        $res = [];
        if(!$is_video){

            $this->prepare_cards($event_id, ($event_card ? $event_card : false), $img_width);
        }

        if($message){
            $save_message = $this->api_model->save_message(['event_message' => $message], ['event_id' => $event_id, 'category_id' => $category_id]);
        }else{
            $save_message = true;
        }

        if($save_message){
            $participants = $this->api_model->get_participants(['category_id' => $category_id]);
            foreach($participants as $k => $participant){
                if($this->num_exists($participant->number)){
                    $participant->number = str_replace([ " ", "(", ")", "-"],'', $participant->number);
                    $contact=[$participant->name => $participant->number];
                    $participant_id = $participant->id;
                    if($is_video){
                        $card_path = base_url().'images/qr_codes/'.$participant->qr_img;
                        $video_path = base_url().'images/event_card/'.$event_card;
                    }else{
                        $card_path = base_url().'images/qr_cards/'.$event_id.'/'.$participant->card_img;
                    }
                    $this->send_message($contact, $message, $card_path,$participant_id, ($is_video && $video_path ? $video_path : false));
                }else{
                    $participant_id = $participant->id;
                    $this->api_model->update_participant(['status' => 6], ['id' => $participant_id]);
                }
            }
            
            sleep(10);
            $categories = $this->api_model->get_categories(['event_id' => $event_id]);
            if($categories['status']){
                $categories = $categories['data'];
                foreach($categories as $category){
                    $pdf_category = $this->api_model->check_category_type(['id' => $category->id]);
                    if($pdf_category){
                        $participants = $this->api_model->get_participants("event_id = {$event_id} AND category_id = {$category->id}");
                        //echo $this->db->last_query();exit;
                        
                        if(! empty($participants)):
                            $pdf = $this->generate_pdf($message, $participants, $event_id);
                            $pdf_ok = $this->api_model->update_pdf(['pdf' => $pdf], ['event_id' => $event_id, 'category_id' => $category->id]);
                        endif;
                    }

                }
            }   
           

            $res = [
                'status' => true,
                'data' => [],
                'message' => "Messages sent successfully to All contacts"
            ];
            
        }else{

            $res = [
                'status' => false,
                'data' => [],
                'message' => "Messages sending failed!"
            ];

        }

        $this->set_response($res, REST_Controller::HTTP_OK);
    }

    function send_message($contact, $message, $card_path,$participant_id, $video_path){
        //echo $message;
        //$this->preview($contact);


        //https://api.chat-api.com/instance244221/sendMessage?token=taagsj151xx1si9d
        $resp = array();
        //$path = base_url().'/images/event_card/1.jpg';
        $type = pathinfo($card_path, PATHINFO_EXTENSION);
        $img = file_get_contents($card_path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($img);

        if($video_path){
            $type = pathinfo($video_path, PATHINFO_EXTENSION);
            $vid = file_get_contents($video_path);
            $base64_vid = 'data:video/' . $type . ';base64,' . base64_encode($vid);
        }
        //echo $base64;exit;
        $n = 1;
        while($n <= count($contact)){
            if($n % 20 != 0){
                foreach ($contact as $name => $number){
                    $number = str_replace([ " ", "(", ")", "-"], '', $number);
                    if($video_path){
                        $data = array("phone"=>$number,"body"=>$base64_vid, 'filename' => 'Qinvite.mp4');
                        //$this->preview($data);
                        $sms = $this->curl_sms($data);
                    }

                    $data = array("phone"=>$number,"body"=>$base64, 'filename' => 'Qinvite.jpg', 'caption' => $message);
                    //$this->preview($data);
                    $sms = $this->curl_sms($data);
                    
                    if($sms->sent == true){
                        //$this->preview($sms);
                        $this->api_model->send_message($sms->id,$participant_id);
                    }else{
                        $resp = [
                            'status' => false,
                            'data' => [],
                            'message' => "Message Sending Failed!"
                        ];
                        //echo json_encode($resp);
                        $this->set_response($resp, REST_Controller::HTTP_OK);
                        //exit;
                    }

                    
                }  
                    // echo "<pre>";
                    // print_r($resp);
                    // exit;
            }else{
                sleep(60);
            }
            $n++;
        }
        
    }


    function send_otp($data=false){
        $json = json_encode($data); // Encode data to JSON
        // URL for request POST /message
        // $res = $this->api_model->get_chat_api_data();
        // foreach($res as $k => $v){
        //     $api_data[$v->setting_name] = $v->setting_value;
        // }

        $settings = $this->api_model->get_settings();
        foreach($settings as $key => $value){
            if($value["setting_name"] == 'chat_api_instance_id') {
                $api_instance = json_decode($value["setting_value"]);
            }else if($value["setting_name"] == 'chat_api_token'){
                $api_token = json_decode($value["setting_value"]);
            }else if($value["setting_name"] == 'message_count'){
                $message_count = $value["setting_value"];
            }else if($value["setting_name"] == 'current_index'){
                $current_index = $value["setting_value"];
            }else if($value["setting_name"] == 'total_index'){
                $total_index = $value["setting_value"];
            }
        }
        if($message_count >= 5900){
            $current_index = $this->get_random_number($total_index, $current_index);
            $this->api_model->save_settings('current_index', $current_index);
            $message_count = 0;
        }

        
        $token = $api_token[$current_index];
        $instanceId = $api_instance[$current_index];

        if(!apiExpired($instanceId, $token)){

            $data['ackNotificationsOn'] = 1;
            $url = 'https://api.chat-api.com/instance'.$instanceId.'/sendMessage?token='.$token;
            // Make a POST request
            $options = stream_context_create(['http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    //'ackNotificationsOn' => 1,
                    'content' => $json
                ]
            ]);
            // Send a request
    
            try {
                $result = file_get_contents($url, false, $options);
                $result = json_decode($result);
            } catch (\Throwable $th) {
                $result->sent = false;
            }
            
            //$this->preview($result);
            $message_count++;
            $this->api_model->save_settings('message_count', $message_count);
            return $result;
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Something went wrong with Chat API'
            ];
            echo json_encode($res);
            exit;
        }
    }

    function get_random_number($total_index,$except) {
        do {
            $n = mt_rand(0,$total_index - 1);
        } while(in_array($n, array($except)));
    
        return $n;
    }
    


    function curl_sms($data=false){
        $json = json_encode($data); // Encode data to JSON
        // URL for request POST /message
        // $res = $this->api_model->get_chat_api_data();
        // foreach($res as $k => $v){
        //     $api_data[$v->setting_name] = $v->setting_value;
        // }

        $settings = $this->api_model->get_settings();
        foreach($settings as $key => $value){
            if($value["setting_name"] == 'chat_api_instance_id') {
                $api_instance = json_decode($value["setting_value"]);
            }else if($value["setting_name"] == 'chat_api_token'){
                $api_token = json_decode($value["setting_value"]);
            }else if($value["setting_name"] == 'message_count'){
                $message_count = $value["setting_value"];
            }else if($value["setting_name"] == 'current_index'){
                $current_index = $value["setting_value"];
            }else if($value["setting_name"] == 'total_index'){
                $total_index = $value["setting_value"];
            }
        }
        if($message_count >= 5900){
            $current_index = $this->get_random_number($total_index, $current_index);
            $this->api_model->save_settings('current_index', $current_index);
            $message_count = 0;
        }

        
        $token = $api_token[$current_index];
        $instanceId = $api_instance[$current_index];

        if(!apiExpired($instanceId, $token)){
            $data['ackNotificationsOn'] = 1;
            $url = 'https://api.chat-api.com/instance'.$instanceId.'/sendFile?token='.$token;
            // Make a POST request
            $options = stream_context_create(['http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    //'ackNotificationsOn' => 1,
                    'content' => $json
                ]
            ]);
            // Send a request

            try {
                $result = file_get_contents($url, false, $options);
                $result = json_decode($result);
            } catch (\Throwable $th) {
                $result->sent = false;
            }
            
            //$this->preview($result);
            $message_count++;
            $this->api_model->save_settings('message_count', $message_count);
            return $result;
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Something went wrong with Chat API'
            ];
            echo json_encode($res);
            exit;
        }
    }

    function generate_pdf($message, $participants, $event_id){
        //Load the library
        //$this->preview($participants);
        $this->load->library('pdf');
        $html = "<div><h1 style='text-align:center;'>Qinvite Invitation cards</h1></div>";
        foreach ($participants as $k => $participant):
            //Set folder to save PDF to
            
            $img = $_SERVER['DOCUMENT_ROOT']."/images/qr_cards/".$event_id."/".$participant->card_img;
            $pdf_path = $this->pdf->folder($_SERVER['DOCUMENT_ROOT']."/pdf/");

        //Set the filename to save/download as
         $pdf_name = time().'-'.$event_id.'.pdf';
         $this->pdf->filename($pdf_name);

        //Set the paper defaults
        $this->pdf->paper('a4', 'portrait');

        //Load html view
        
            // $html .= "<div style='text-align:center;height:100%;'><h1>Qinvite Invitation Card</h1><img src='".$img."' style='width:100%;height:auto;'><br><br><h4>Dear ".$participant->name.",</h4><br><p>".$message.".</p> </div>";

            $html .= "<img src='".$img."' style='width:100%;height:100%;'>";
        endforeach;
        $this->pdf->html($html);
        $this->pdf->create('save');
        return $pdf_name;
    }

    function num_exists($number){
        $number = str_replace([ " ", "(", ")", "-"],'',$number);
        $settings = $this->api_model->get_settings();
        foreach($settings as $key => $value){
            if($value["setting_name"] == 'chat_api_instance_id') {
                $api_instance = json_decode($value["setting_value"]);
            }else if($value["setting_name"] == 'chat_api_token'){
                $api_token = json_decode($value["setting_value"]);
            }else if($value["setting_name"] == 'message_count'){
                $message_count = $value["setting_value"];
            }else if($value["setting_name"] == 'current_index'){
                $current_index = $value["setting_value"];
            }else if($value["setting_name"] == 'total_index'){
                $total_index = $value["setting_value"];
            }
        }
        $token = $api_token[$current_index];
        $instanceId = $api_instance[$current_index];
        if(!apiExpired($instanceId, $token)){
            $result = file_get_contents('https://api.chat-api.com/instance'.$instanceId.'/checkPhone?token='.$token.'&phone='.$number);
            $result = json_decode($result);
            if($result->result == 'exists'){
                return true;
            }else{
                return false;
            }
        }else{
            $res = [
                'status' => false,
                'data' => [],
                'message' => 'Something went wrong with Chat API'
            ];
            echo json_encode($res);
            exit;
        }
    }

// ===========================================================================================================


    function message_check_get(){
        $resp = array();
        $path = base_url().'images/event_card/1.jpg';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $img = file_get_contents($path);
        $base64_1 = 'data:image/' . $type . ';base64,' . base64_encode($img);

        $resp = array();
        $path2 = base_url().'images/event_card/vid.mp4';
        $type2 = pathinfo($path2, PATHINFO_EXTENSION);
        $vid = file_get_contents($path2);
        $base64_2 = 'data:video/' . $type2 . ';base64,' . base64_encode($vid);

        // echo $base64_2;exit;


                    $data = array("phone"=>"923085032607","body"=> $base64_1, 'filename' => 'Qinvite.jpg');
                    $data2 = array("phone"=>"923085032607","body"=> $base64_2, 'filename' => '123.mp4', 'caption' => 'A test message');
                    //$this->preview($data);
                    $sms = $this->curl_sms($data);
                    $sms = $this->curl_sms($data2);
                
                    
                    $this->preview($sms);
                    if($sms->sent == true){
                    }else{
                        $resp = [
                            'status' => false,
                            'data' => [],
                            'message' => "Message Sending Failed!"
                        ];
                        //echo json_encode($resp);
                        $this->set_response($resp, REST_Controller::HTTP_OK);
                        //exit;
                    }
    }

    function test_get($id){
        $categories = $this->api_model->get_categories(['event_id' => $id]);
        $categories = $categories['data'];
        foreach($categories as $cat){
            $this->preview($cat->id);
        }
    }
 

}