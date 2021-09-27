<?php

class Admin extends CI_Controller{

    function __construct(){
        parent::__construct();
        $this->load->model('admin_model');
        $this->load->model('api_model');
        // if(!$this->session->userdata("admin_id") && $this->uri->segment(2)!=="login"){
        //     redirect(base_url()."admin/login");
        // }

        
        $this->load->helper("common");
    }

    function index(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }else{
            redirect(base_url('admin/dashboard'));
        }
    }

    function preview($data){
        echo '<pre>'; print_r($data);exit;
    }

    function upload_img($img,$path){
        if ($img["name"] != '') {
            $file_name = $img['name'];
            $size = $img['size'];
            $file_path = "images/".$path;
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
    
    function dashboard(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['total_receptionists'] = $this->admin_model->get_total_receptionist();
        $data['total_events'] = $this->admin_model->get_total_events();
        $data['current_month_events'] = $this->admin_model->get_current_month_events();
        $data['total_revenue'] = $this->admin_model->get_total_revenue();
        $data['current_month_revenue'] = $this->admin_model->get_current_month_revenue();
        $data['total_revenue'] = $data['total_revenue']->amount;
        $data['active_hosts'] = $this->admin_model->get_total_active_hosts();
        $data['total_designers'] = $this->admin_model->get_total_designers();
        $data['yearly_earnings'] = $this->admin_model->get_yearly_earnings();
        $data['receptionist_data']['no_of_receptionists'] = $this->admin_model->get_admin_recep(['recp_admin_id' => $this->session->userdata('admin_id')]);
        $data['receptionist_data']['recp_monthly_revenue'] = $this->admin_model->get_recp_monthly_revenue($this->session->userdata('admin_id'));
        $data['receptionist_data']['total_recp_revenue'] = $this->admin_model->get_recp_total_revenue($this->session->userdata('admin_id'));
        $data['receptionist_data']['total_recp_events'] = $this->admin_model->get_recp_total_events($this->session->userdata('admin_id'));
        //$this->preview($data);
        $this->load->view('admin/dashboard', $data);
    }

    // ============ User =====================

    function login(){
        
        if($this->input->post()){
            $data = array();
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $remember = $this->input->post('qi_remember');

            $data = [
                'username' => $this->db->escape_str($username),
                'password' => md5($password)
            ];
            //print_r($data);exit;
            $user = $this->admin_model->login_user($data);

            if($user){
                if($remember){
                    $this->load->helper('cookie');
                    set_cookie('qi_username' , $username, '36000');
                    set_cookie('qi_password', $password, '36000');
                }
                $this->session->set_userdata('admin_id', $user->id);
                $this->session->set_userdata('user_role', $user->role);
                redirect(base_url()."admin/dashboard");
            }else{
                $this->session->set_flashdata('error', "Invalid credentials");
                redirect(base_url()."admin/login");
            }
        }
        $this->load->view('admin/login');
    }

    function forgot_password(){
        if($this->input->post()){
            $email = $this->db->escape_str($this->input->post('email'));
            $errors = '';
            $errors .= (!$this->admin_model->check_existance('users',['email' => $email]) ? "Invalid email address" : "");

            if(!$errors){
                $email = $this->db->escape_str($this->input->post('email'));

                $code = mt_rand(1000, pow(10,4));
                $this->load->library('email');
                $config['mailtype'] = 'html';
                $this->email->initialize($config);
                $this->email->to($email);
                $this->email->from('zeeshan.ali60767@gmail.com',"Qinvite");
                $this->email->subject('Reset your password');
                $this->email->message("your Verification code is {$code}");
                $res = $this->email->send();
                if($res){
                    $this->load->helper('cookie');
                    set_cookie('otp', md5($code), 36000);
                    set_cookie('email', $email, 36000);
                    $this->session->set_flashdata('success','Verification code sent successfully');
                    redirect(base_url('admin/verification_code'));
                }else{
                    $this->session->set_flashdata('errors','Something went wrong!');
                }
            }else{
                $this->session->set_flashdata('errors',$errors);
            }
        }
        $this->load->view('admin/forgot-password');
    }

    
    function verification_code(){
        if($this->input->post()){
            $this->load->helper('cookie');
            $code = $this->db->escape_str($this->input->post('code'));
            $otp  = get_cookie('otp');
            if(md5($code) == $otp){
                // $this->session->set_flashdata('success','Verification code sent successfully');
                redirect(base_url('admin/reset_password'));
            }else{
                $this->session->set_flashdata('errors','Invalid verification code');
            }
        }
        $this->load->view('admin/code-verification');
    }

    function reset_password(){
        if($this->input->post()){
            $pass = $this->db->escape_str($this->input->post('new_pass'));
            $confirm_pass = $this->db->escape_str($this->input->post('confirm_pass'));

            $errors = '';

            $errors .= ($pass != $confirm_pass ? "Password does not match<br>" : '');
            if(!$errors){
                $this->load->helper('cookie');
                $email = get_cookie('email');
                $res = $this->admin_model->update_user(['password' => md5($pass)], ['email' => $email]);
                if($res){
                    delete_cookie('otp');
                    delete_cookie('email');
                    $this->session->set_flashdata('success','Password successfuly updated');
                    redirect(base_url('admin/login'));
                }else{
                    $this->session->set_flashdata('errors','Invalid verification code');
                }
            }else{
                $this->session->set_flashdata('errors',$errors);
            }

        }
        $this->load->view('admin/reset-password');
    }



    function add_user($user_id=0){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['title'] = "Add User";
        $data = array('user_idCard'=>'','user_fname'=>'', 'user_lname' => '', 'user_email' => '', 'user_phone' => '', 'user_city' => '', 'user_country' => '', 'user_image' => '', 'role' => "");
        $errors = "";
        if($user_id > 0){
            $get_user_data = $this->admin_model->get_users(array("id"=>$user_id));
            $get_user_meta = $this->admin_model->get_usermeta(['user_id' => $user_id]);
            if($get_user_meta){
                foreach($get_user_meta as $k => $v){
                    $data[$v->meta_key] = $v->meta_value;
                }
            }

            $user_data = (array)$get_user_data[0];
            $data["user_idCard"] =  $user_data["username"];
            $data["user_fname"] =  $user_data["first_name"];
            $data['user_lname'] = $user_data['last_name'];
            $data['user_email'] = $user_data['email'];
            $data['user_phone'] = $user_data['phone'];
            $data['user_city'] = $user_data['city'];
            $data['user_country'] = $user_data['country'];
            $data['user_image'] = $user_data['user_image'];
            $data['role'] = $user_data['role'];
        
        }

        if($this->input->post()){
            $id_card = $this->db->escape_str($this->input->post("user_idCard"));
            $first_name = $this->db->escape_str($this->input->post("user_fname"));
            $last_name = $this->db->escape_str($this->input->post("user_lname"));
            $email = $this->db->escape_str($this->input->post("user_email"));
            $role = $this->db->escape_str($this->input->post("user_role"));
            $phone = $this->db->escape_str($this->input->post("user_phone"));
            $city = $this->db->escape_str($this->input->post('user_city'));
            $country = $this->db->escape_str($this->input->post('user_country'));
            $password = $this->db->escape_str($this->input->post('user_password'));
            $confirm_password = $this->db->escape_str($this->input->post('user_confirm_password'));
            $user_price = $this->db->escape_str($this->input->post('user_price'));

            $img = false;

            if($email){
                $errors .=((!filter_var($email, FILTER_VALIDATE_EMAIL)) ? "Invalid Email address!<br>" : "" );
            }

            if($user_id == 0){
                $errors .= ($this->admin_model->check_existance('users', ['username' => $id_card],["id"=>$user_id]) ? "User already exists<br>" : "");
                $errors .= ($password != $confirm_password ? "Password didn't match<br>" : "");
                $errors .= (strlen($password) < 8 ? "Password lenght should be more than 8<br>" : "");
            }
            

            if(!$errors){
                if($_FILES['user_image']['size'] > 10){
                    $file = $_FILES["user_image"];
                    $filename = $file["name"];
                    $file_name_split = explode(".",$filename);
                    $ext = end($file_name_split);
                    $allowed_ext = array("jpg","png","jpeg","gif");
                    
                    if(in_array($ext,$allowed_ext)){
                        $img = $this->upload_img($file,"user_img/");
                    }
                }
                $user_data = [
                    'username' => $id_card,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => $city,
                    'country' => $country,
                    'role' => $role,
                    'status' => 0
                ];
                if($img){
                    $user_data["user_image"] = $img;
                }
                if($user_id > 0){
                    $res = $this->admin_model->update_user($user_data,['id' => $user_id]);
                    if($res){     
                        $this->admin_model->update_usermeta(['meta_value' => $user_price], ['user_id' => $user_id, 'meta_key' => 'user_price']);
                        $this->session->set_flashdata('success', "Data updated successfuly");
                        redirect(base_url().'admin/add_user');
                    }else{
                        $this->session->set_flashdata('errors', "Something went wrong");
                    }
                }else{
                    $user_data['password'] = md5($password);
                    $res = $this->admin_model->insert_user($user_data);
                    if($res){
                        if($user_data['role'] == 5 || $user_data['role'] == 4){
                            $meta = [
                                'user_id' => $res,
                                'meta_key' => 'user_price',
                                'meta_value' => $user_price
                            ];
                            $this->admin_model->insert_usermeta($meta);
                            //echo $user_data['role'];
                            if($user_data['role'] == 4){
                                $rel = [
                                    'recp_admin_id' => $this->session->userdata('admin_id'),
                                    'receptionist_id' => $res,
                                ];

                                $this->admin_model->insert_recp_relation($rel);
                            }
                        }
                            
                        $this->session->set_flashdata('success', "Data inserted successfuly");
                        redirect(base_url().'admin/add_user');
                    }else{
                        $this->session->set_flashdata('errors', "Something went wrong");
                    }
                }
                
            }else{
                $this->session->set_flashdata('errors', $errors);
            }
        }
        $data["event"] = ($user_id > 0 ) ? "Update" : "Add";
        $data['countries'] = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");

        $this->load->view('admin/users/add-user', $data); 
    }



    function users(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['event'] = 'User';
        $data['title'] = "Users";
        $data["user_data"] = $this->admin_model->get_users();
        $this->load->view('admin/users/user-listing',$data);
    }


    function delete_user($id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $res = $this->admin_model->delete_user($id);

        if($res){
            $this->session->set_flashdata('success', "Data deleted successfully");
        }else{
            $this->session->set_flashdata('errors', "Something went wrong!");
        }
        redirect(base_url().'admin/users');
    }

    function get_userDetails(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        if($this->input->post()){
            $id = $this->db->escape_str($this->input->post('id'));
            $res = $this->admin_model->get_users(['id' => $id]);
            if($res){
                echo json_encode($res);
            }else{
                echo json_encode(array());
            }
        }
    }

    //  ================ Receptionists ================

    function receptionists(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['event'] = 'Receptionist';
        $data['title'] = "Receptionists";
        $data["user_data"] = $this->admin_model->get_receptionists(['recp_admin_id' => $this->session->userdata('admin_id')]);
        $this->load->view('admin/receptionists/receptionist-listing',$data);
    }


    function add_receptionist($user_id=0){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['title'] = "Add User";
        $data = array('user_idCard'=>'','user_fname'=>'', 'user_lname' => '', 'user_email' => '', 'user_phone' => '', 'user_city' => '', 'user_country' => '', 'user_image' => '', 'role' => "");
        $errors = "";
        if($user_id > 0){
            $get_user_data = $this->admin_model->get_users(array("id"=>$user_id));
            $get_user_meta = $this->admin_model->get_usermeta(['user_id' => $user_id]);
            if($get_user_meta){
                foreach($get_user_meta as $k => $v){
                    $data[$v->meta_key] = $v->meta_value;
                }
            }
            $get_user_data = $this->admin_model->get_users(array("id"=>$user_id));

            $user_data = (array)$get_user_data[0];
            $data["user_idCard"] =  $user_data["username"];
            $data["user_fname"] =  $user_data["first_name"];
            $data['user_lname'] = $user_data['last_name'];
            $data['user_email'] = $user_data['email'];
            $data['user_phone'] = $user_data['phone'];
            $data['user_city'] = $user_data['city'];
            $data['user_country'] = $user_data['country'];
            $data['user_image'] = $user_data['user_image'];
            $data['role'] = $user_data['role'];
        
        }

        if($this->input->post()){
            $id_card = $this->db->escape_str($this->input->post("user_idCard"));
            $first_name = $this->db->escape_str($this->input->post("user_fname"));
            $last_name = $this->db->escape_str($this->input->post("user_lname"));
            $email = $this->db->escape_str($this->input->post("user_email"));
            $role = $this->db->escape_str($this->input->post("user_role"));
            $phone = $this->db->escape_str($this->input->post("user_phone"));
            $city = $this->db->escape_str($this->input->post('user_city'));
            $country = $this->db->escape_str($this->input->post('user_country'));
            $password = $this->db->escape_str($this->input->post('user_password'));
            $confirm_password = $this->db->escape_str($this->input->post('user_confirm_password'));
            $user_price = $this->db->escape_str($this->input->post('user_price'));

            $img = false;

            if($email){
                $errors .=((!filter_var($email, FILTER_VALIDATE_EMAIL)) ? "Invalid Email address!<br>" : "" );
            }

            if($user_id == 0){
                $errors .= ($this->admin_model->check_existance('users', ['username' => $id_card],["id"=>$user_id]) ? "User already exists<br>" : "");
                $errors .= ($password != $confirm_password ? "Password didn't match<br>" : "");
                $errors .= (strlen($password) < 8 ? "Password lenght should be more than 8<br>" : "");
            }
            

            if(!$errors){
                if($_FILES['user_image']['size'] > 10){
                    $file = $_FILES["user_image"];
                    $filename = $file["name"];
                    $file_name_split = explode(".",$filename);
                    $ext = end($file_name_split);
                    $allowed_ext = array("jpg","png","jpeg","gif");
                    
                    if(in_array($ext,$allowed_ext)){
                        $img = $this->upload_img($file,"user_img/");
                    }
                }
                $user_data = [
                    'username' => $id_card,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => $city,
                    'country' => $country,
                    'role' => $role,
                    'status' => 0
                ];
                if($img){
                    $user_data["user_image"] = $img;
                }
                if($user_id > 0){
                    $res = $this->admin_model->update_user($user_data,['id' => $user_id]);
                    if($res){
                        $this->admin_model->update_usermeta(['meta_value' => $user_price], ['user_id' => $user_id, 'meta_key' => 'user_price']);
                        $this->session->set_flashdata('success', "Data updated successfuly");
                        redirect(base_url().'admin/add_user');
                    }else{
                        $this->session->set_flashdata('errors', "Something went wrong");
                    }
                }else{
                    $user_data['password'] = md5($password);
                    $res = $this->admin_model->insert_user($user_data);
                    if($res){
                        $meta = [
                            'user_id' => $res,
                            'meta_key' => 'user_price',
                            'meta_value' => $user_price
                        ];
                        $this->admin_model->insert_usermeta($meta);

                        $rel = [
                            'recp_admin_id' => $this->session->userdata('admin_id'),
                            'receptionist_id' => $res,
                        ];

                        $this->admin_model->insert_recp_relation($rel);
                        $this->session->set_flashdata('success', "Data inserted successfuly");
                        redirect(base_url().'admin/add_receptionist');
                    }else{
                        $this->session->set_flashdata('errors', "Something went wrong");
                    }
                }
                
            }else{
                $this->session->set_flashdata('errors', $errors);
            }
        }
        $data["event"] = ($user_id > 0 ) ? "Update" : "Add";
        $data['countries'] = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");

        $this->load->view('admin/receptionists/add-receptionist', $data); 

    }


    function receptionist_details($id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['receptionist'] = $this->admin_model->get_single_recp_data($id);
        $this->load->view('admin/receptionists/single-receptionist', $data);
    }

    // ================== Designer =====================

    function designer_details($id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['designer'] = $this->admin_model->get_single_designer_data($id);
        $this->load->view('admin/designers/designer-details',$data);
    }


    // ============ Events =====================

    public function events(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data = array();
        $data['title'] = 'Event';
        $data['event_data'] = $this->admin_model->get_events();
        $this->load->view('admin/events/event-listing', $data);
    }

    public function add_event($id = 0){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data = array('user_idCard' => '', 'packagae_id' => '', 'event_name' => '', 'event_date' => '', 'event_address' => '', 'event_status' => '', 'no_of_receptionists' => '');
        $data['event'] = 'Add Event';
        $errors = "";
        $data['packages'] = $this->admin_model->get_packages(['package_type' => 0]);

        if($id > 0){
            $event_details = $this->admin_model->get_events(['events.id' => $id]);
            //echo '<pre>';print_r($event_details[0]);exit;
            $event_details = (array)$event_details[0];
            $data['user_idCard'] = $event_details['user_id'];
            $data['package_id'] = $event_details['package_id'];
            $data['event_name'] = $event_details['event_name'];
            $data['event_date'] = $event_details['event_date'];
            $data['event_address'] = $event_details['event_address'];
            $data['event_status'] = $event_details['event_status'];
            $data['no_of_receptionists'] = $event_details['no_of_receptionists'];
            $data['event'] = 'Update Event';
            //echo '<pre>';print_r($data);exit;
        }

        if($this->input->post()){
            $user_id = $this->db->escape_str($this->input->post('user_idCard'));
            $package_id = $this->db->escape_str($this->input->post('package_id'));
            $event_name = $this->db->escape_str($this->input->post('event_name'));
            $event_date = $this->db->escape_str($this->input->post('event_date'));
            $event_address = $this->db->escape_str($this->input->post('event_address'));
            $event_status = $this->db->escape_str($this->input->post('event_status'));
            $no_of_receptionists = $this->db->escape_str($this->input->post('no_of_receptionists'));
            $img = false;


            
            $errors .= (!$this->admin_model->check_existance('users', ['users.id' => $user_id]) ? "User doesn't exist!": "");
            
            if(!$errors){
                $user_id = $this->admin_model->get_users(['users.id' => $user_id]);
                $user_id = $user_id[0]->id;
                if($_FILES['eventcard']['size'] > 10){
                    
                    $file = $_FILES["eventcard"];
                    $filename = $file["name"];
                    $file_name_split = explode(".",$filename);
                    $ext = end($file_name_split);
                    $allowed_ext = array("jpg","png","jpeg","gif","PNG","JPG","JPEG","GIF");
                    
                    if(in_array($ext,$allowed_ext)){
                        //echo $_FILES['eventcard']['name'];exit;
                        $img = $this->upload_img($file,"event_card/");
                    }
                }
                
                $event_data = [
                    'event_name' => $event_name,
                    'user_id' => $user_id,
                    'package_id' => $package_id,
                    'event_date' => $event_date,
                    'event_address' => $event_address,
                    'event_status' => $event_status,
                    'no_of_receptionists' => $no_of_receptionists
                ];

            if($img){
                $event_data["event_card"] = $img;
            }
            
            if($id > 0){

                $res = $this->admin_model->update_event($event_data, $id);
            
                if($res){
                    $this->session->set_flashdata('success', "Event updated successfully");
                    redirect(base_url().'admin/add_event');
                }else{
                    $this->session->set_flassdata('errors', "Something went wrong!");
                    redirect(base_url().'admin/add_event');
                }

            }else{
                $res = $this->admin_model->add_event($event_data);
            
                if($res){
                    $this->session->set_flashdata('success', "Event inserted successfully");
                    redirect(base_url().'admin/add_event');
                }else{
                    $this->session->set_flassdata('errors', "Something went wrong!");
                    redirect(base_url().'admin/add_event');
                }
            }
            
            }else{
                $this->session->set_flashdata('errors', $errors);
                redirect(base_url().'admin/add_event');
            }
        }
        $this->load->view('admin/events/add-event', $data);
    }

    public function delete_event($id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $res = $this->admin_model->delete_event($id);

        if($res){
            $this->session->set_flashdata('success', "Event deleted successfully");
        }else{
            $this->session->set_flashdata('errors', "Something went wrong!");
        }
        redirect(base_url().'admin/events');
    }

    public function get_eventDetails(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        if($this->input->post()){
            $id = $this->db->escape_str($this->input->post('id'));
            $res = $this->admin_model->get_events(['events.id' => $id]);
            if($res){
                echo json_encode($res);
            }else{
                echo json_encode(array());
            }
        }
    }

    public function single_event($event_id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['event'] = $this->admin_model->get_events(['events.id' => $event_id]);
        $data['event'] = $data['event'][0];
        $data['event']->event_card = $this->admin_model->get_event_cards(['event_id' => $data['event']->id]);
            if($data['event']->event_card){
                $data['event']->event_card = $data['event']->event_card[0]->design_card;
                $data['event']->event_card = base_url().'images/event_card/'.$data['event']->event_card;
            }
        $data['event']->receptionists = $this->admin_model->get_event_receptionists($data['event']->id);
        $data['event']->participants = $this->admin_model->get_participants_events($data['event']->id);
        $data['event']->designer = $this->admin_model->get_event_designer($data['event']->id);
        $payment_details = $this->admin_model->get_payment_details(['order_id' => $data['event']->id]);
        if($payment_details){

            $data['event']->payment = $payment_details;
        }


        
        //  $this->preview($data);
        $this->load->view('admin/events/single_event', $data);
    }

    // ============ Packages =====================

    public function packages(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data = array();
        $data['package_data'] = $this->admin_model->get_packages();
        $this->load->view('admin/packages/package-listing', $data);
    }

    public function add_package($id=0){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data = ['package_name' => '', 'package_people' => '', 'package_price' => '','package_logo' => ''];
        $data['event'] = "Add package";

        if($id > 0){
            $package_details = $this->admin_model->get_packages(['packages.id' => $id]);
            $package_details = (array)$package_details[0];
            $data['package_name'] = $package_details['package_name'];
            $data['package_people'] = $package_details['package_people'];
            $data['package_price'] = $package_details['package_price'];
            $data['package_logo'] = $package_details['package_logo'];
            $data['event'] = "Update package";
            
        }
        
        if($this->input->post()){
            $data = array();
            $errors = "";
            $img = false;
            $package_name = $this->db->escape_str($this->input->post('package_name'));
            $no_of_people = $this->db->escape_str($this->input->post('package_people'));
            $price = $this->db->escape_str($this->input->post('package_price'));
            

            if($id == 0){
                $errors .= ($this->admin_model->check_existance('packages', ['package_name' => $package_name]) ? "Please choose different package name" : "");
            }
            $errors .= ((!filter_var($no_of_people, FILTER_VALIDATE_INT)) ? "Select Valid No. of people" : "");
            
            if(!$errors){
                if($_FILES['package_logo']['size'] > 10){
                    $file = $_FILES["package_logo"];
                    $filename = $file["name"];
                    $file_name_split = explode(".",$filename);
                    $ext = end($file_name_split);
                    $allowed_ext = array("jpg","png","jpeg","gif");
                    
                    if(in_array($ext,$allowed_ext)){
                        $img = $this->upload_img($file,"package_img/");
                    }
                }
                $package_data = [
                    'package_name' => $package_name,
                    'package_people' => $no_of_people,
                    'package_price' => $price,
                    'package_type' => 0
                ];
                if($img){
                    $package_data["package_logo"] = $img;
                }

                if($id > 0){
                    $res = $this->admin_model->update_package($package_data, $id);
                    if($res){
                        $this->session->set_flashdata('success', "Package updated successfuly");
                        redirect(base_url().'admin/add_package/'.$id);
                    }else{
                        $this->session->set_flashdata('errors', "Something went wrong");
                    }

                }else{
                    $res = $this->admin_model->add_package($package_data);
                    if($res){
                        $this->session->set_flashdata('success', "Package created successfuly");
                        redirect(base_url().'admin/add_package');
                    }else{
                        $this->session->set_flashdata('errors', "Something went wrong");
                    }
                }

            }else{
                $this->session->set_flashdata('errors', $errors);
            }
        }


        $this->load->view('admin/packages/add-package', $data);
    }

    public function delete_package($id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $res = $this->admin_model->delete_package($id);

        if($res){
            $this->session->set_flashdata('success', 'Package deleted successfully');
        }else{
            $this->session->set_flashdata('errors', 'Something went wrong!');
        }
        redirect(base_url(). 'admin/packages');
    }

    public function get_packageDetails(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        if($this->input->post()){
            $id = $this->db->escape_str($this->input->post('id'));
            $res = $this->admin_model->get_packages(['packages.id' => $id]);
            if($res){
                echo json_encode($res);
            }else{
                echo json_encode(array());
            }
        }
    }

    // ============ Promo Codes =====================

    public function promocodes(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data = array();
        $data['promocode_data'] = $this->admin_model->get_promocodes();
        $this->load->view('admin/promocodes/promocodes-listing', $data);
    }

    public function add_promocode(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $errors = "";
        $data['event'] = 'Promo code';

        if($this->input->post()){
            $code = $this->db->escape_str($this->input->post('code'));
            $code = str_replace(' ', '', $code);
            $discount = $this->db->escape_str($this->input->post('discount'));
            $expiry_date = $this->db->escape_str($this->input->post('expiry_date'));
            
            $errors .= ($this->admin_model->check_existance('promo_codes', ['code' => $code]) ? "Promo code already exits" : "");
            
            if(!$errors){

                $promocode_data = [
                    'code' => $code,
                    'discount' => $discount,
                    'expiry_date' => $expiry_date,
                    'status' => 0
                ];

                $res = $this->admin_model->add_promocode($promocode_data);

                if($res){
                    $this->session->set_flashdata('success', "Promo code created successfully");
                    redirect(base_url().'admin/add_promocode');
                }else{
                    $this->session->set_flashdata('errors', "Something went wrong!");
                }

            }else{
                $this->session->set_flashdata('errors', $errors);
                redirect(base_url().'admin/add_promocode');
            }
            
        }

        $this->load->view('admin/promocodes/add-promocode', $data);
    }

    public function get_promocodeDetails(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        if($this->input->post()){
            $code = $this->db->escape_str($this->input->post('id'));

            $res = $this->admin_model->get_promocodes(['code' => $code]);
            if($res){
                echo json_encode($res);
            }else{
                echo json_encode(array());
            }
        }
    }

    public function delete_promocode($id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $res = $this->admin_model->delete_promocode($id);

        if($res){
            $this->session->set_flashdata('success', 'Promo code deleted successfully');
        }else{
            $this->session->set_flashdata('errors', 'Something went wrong!');
        }
        redirect(base_url(). 'admin/promocodes');
    }

    // ============ Receptionist =====================

     


    // ============ Categories =====================

    public function categories(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data['category_data'] = $this->admin_model->get_category_listing();
        $this->load->view('admin/categories/category-listing', $data);
    }

    public function add_category($id=0){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $data = ['category_name' => '', 'people_per_qr' => '', 'phone_allowed' => ''];
        $data['event'] = 'Add category';

        if($id > 0){
            $category_details = $this->admin_model->get_category(['id' => $id]);
            $category_details = (array)$category_details[0];
            //echo '<pre>';print_r($category_details);exit;
            $data['category_name'] = $category_details['name'];
            $data['people_per_qr'] = $category_details['people_per_qr'];
            $data['phone_allowed'] = $category_details['phones'];
            $data['event'] = "Update Category";
        }

        if($this->input->post()){
            $category_name = $this->db->escape_str($this->input->post('category_name'));
            $people_per_qr = $this->db->escape_str($this->input->post('people_per_qr'));
            $phone_allowed = $this->db->escape_str($this->input->post('phone_allowed'));


                $category_data = [
                    'name' => $category_name,
                    'people_per_qr' => $people_per_qr,
                    'phones' => $phone_allowed,
                    'type' => 0,
                    'user_id' => $this->session->userdata('admin_id')
                ];

                if($id > 0){
                    $res = $this->admin_model->update_category($category_data, $id);

                    if($res){
                        $this->session->set_flashdata('success', 'Category updated successfuly');
                        redirect(base_url().'admin/add_category');
                    }else{
                        $this->session->set_flashdata('errors', 'Something went wrong!');
                    }

                }else{
                    $res = $this->admin_model->create_category($category_data);

                    if($res){
                        $this->session->set_flashdata('success', 'Category created successfuly');
                        redirect(base_url().'admin/add_category');
                    }else{
                        $this->session->set_flashdata('errors', 'Something went wrong!');
                    }
                }
        }


        $this->load->view('admin/categories/add-category', $data);
    }

    public function delete_category($id){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $res = $this->admin_model->delete_category($id);
        if($res){
            $this->session->set_flashdata('success', "Category deleted successfully");
            redirect(base_url().'admin/categories');
        }else{
            $this->session->set_flashdata('errors', "Something went wrong!");
            redirect(base_url().'admin/categories');
        }
    }

    // ============ Profile =====================

    function logout(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $this->session->unset_userdata('admin_id');
        $this->session->unset_userdata('user_role');
        redirect(base_url()."admin/index");
    }

    function edit_profile(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $id = $this->session->userdata('admin_id');
        $user_data = $this->admin_model->get_users(['id' => $id]);
        $user_data = (array)$user_data[0];
        //echo '<pre>';print_r($user_data);exit;
        $data['countries'] = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
        $data['id_card'] = $user_data['username']; 
        $data['first_name'] = $user_data['first_name']; 
        $data['last_name'] = $user_data['last_name']; 
        $data['user_img'] = $user_data['user_image']; 
        $data['city'] = $user_data['city']; 
        $data['country'] = $user_data['country']; 
        $data['email'] = $user_data['email']; 
        $data['phone'] = $user_data['phone']; 

        if(isset($_POST['change_pass'])){
            $curr_pass = $this->input->post('current_pass');
            $new_pass = $this->input->post('new_pass');
            $confirm_pass = $this->input->post('confirm_pass');
            
            $user = $this->admin_model->get_users(['id' => $this->session->userdata('admin_id')]);
            $user = $user[0];
        
            $errors = '';
            $errors .= (!($user->password === md5($curr_pass)) ? "Invalid current password<br>" : '');
            // $errors .= ( count($new_pass) <= 8 ? "Password length should be more than 8 characters" : '');
            $errors .= (!($new_pass === $confirm_pass) ? "Password doesn't match<br>" : '');
            if(!$errors){
                $pass_data = ['password' => md5($new_pass)];
                $update_pass = $this->admin_model->update_user($pass_data, ['id' => $user->id]);
                if($update_pass){
                    $this->session->set_flashdata('success', 'Password updated successfully');
                    redirect(base_url().'admin/edit_profile');
                }else{
                    $this->session->set_flashdata('errors', 'Something went wrong');
                }
            }else{
                $this->session->set_flashdata('errors', $errors);
            }
        }

        if(isset($_POST['update_profile'])){
            $id_card = $this->db->escape_str($this->input->post('id_card'));
            $first_name = $this->db->escape_str($this->input->post('first_name'));
            $last_name = $this->db->escape_str($this->input->post('last_name'));
            $city = $this->db->escape_str($this->input->post('city'));
            $country = $this->db->escape_str($this->input->post('country'));
            $email = $this->db->escape_str($this->input->post('email'));
            $phone = $this->db->escape_str($this->input->post('phone'));
            $img = false;
            if(isset($_FILES["user_image"])){
                $file = $_FILES["user_image"];
                $filename = $file["name"];
                $file_name_split = explode(".",$filename);
                $ext = end($file_name_split);
                $allowed_ext = array("jpg","png","jpeg","gif");
                
                if(in_array($ext,$allowed_ext)){
                    $img = $this->upload_img($file,"user_img/");
                }
            }
            $errors = "";
            // $errors = (!filter($email, FILTER_VALIDATE_EMAIL) ? "Invalid email<br>": "");

            if(!$errors){
                $user_data = [
                    'username' => $id_card,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'city' => $city,
                    'country' => $country,
                    'email' => $email,
                    'phone' => $phone
                ];
                if($img){
                    $user_data["user_image"] = $img;
                }
                
                //echo '<pre>'; print_r($user_data); exit;
                $res = $this->admin_model->update_user($user_data, ['id' => $id]);
                if($res){
                    $this->session->set_flashdata('success', "Data updated successfuly");
                    redirect(base_url().'admin/edit_profile');
                }else{
                    $this->session->set_flashdata('errors', "Something went wrong!");
                    redirect(base_url().'admin/edit_profile');
                }
            }
        }

        
        $this->load->view('admin/profile/edit-profile', $data);
    }

    function settings(){
        if(!$this->session->userdata('admin_id')){
            redirect(base_url('admin/login'));
        }
        $settings = $this->admin_model->get_settings();
        if($this->input->post()){
            $post_data = $this->input->post();
            $post_data['total_index'] = count($post_data['chat_api_instance_id']);
            foreach($post_data as $k=>$v){
               
                if($k == 'chat_api_instance_id' || $k == 'chat_api_token'){
                    $v = json_encode($v);
                }
                  $this->admin_model->save_settings($k,$v);

                // echo $k .' : ' . $v . '<br>';
            }
            // exit;
            $this->session->set_flashdata("success","Settings have been updated");
            redirect(base_url()."admin/settings");
        }
        $data = array();
        foreach($settings as $key => $value){
            if($value["setting_name"] == 'chat_api_instance_id' || $value["setting_name"] == 'chat_api_token'){
                $value["setting_value"] = json_decode($value["setting_value"]);
            }
            $data[$value["setting_name"]] = $value["setting_value"];
        }
        $this->load->view('admin/profile/site-settings',$data);
    }

    public function submit_design(){
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
                    $img = $this->upload_img($file,"event_card/");
                    $card_data = [
                        'design_card' => $img,
                        'event_id' => $event_id,
                        'designer_id' => $designer_id,
                    ];
    
                    //$res = $this->api_model->edit_event($event_data, $event_id);
                    $res = $this->api_model->submit_image($card_data);
                    $this->api_model->update_event_request(['design_status' => 3], ['event_id' => $event_id, 'designer_id' => $designer_id]);
    
                    if($res){
                        $res = true;
                    }else{
                        $res = false;
                    }
                }else{
                    $res = false;
                }

                echo "<script>
                        setTimeout(function () {
                        window.ReactNativeWebView.postMessage('{$res}')
                        }, 2000)
                    </script>";
                    exit;
            }

        $this->load->view('admin/upload_image');
    }

// ===================================================================================================================
    /**
     * ======================================
     *      Temp Data
     * =====================================
     */

    // , 'Atif' => "923345682416", 'Tango' => "923359799769", 'Mubashir' => "923238510272", 'Kazim' => '923055364344','Bhatti' => "923035954381", 'Haroon' => "923448876648", 'ananymour' => "923435951953", 'Ek or number' => '923119323607',


    function test($id){
        $amount = $this->admin_model->designer_total_earnings($id);
        $this->load->view('admin/profile/site-settings');
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
                        'receptionist_id' => $v->receptionist_id,
                        'event_id' => $id,
                        'price' => $price,
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
                    'receptionist_id' => $v->designer_id,
                    'event_id' => $id,
                    'price' => $price,
                ];

                $this->db->insert('designer_payment', $designer_data);
            }
            
        }
        
    }





     function message(){
       $num = [
             "923085032607",
            "923445178688",
            "923007004383",
            "923085162039",
            "923436697769",
            "923365179249",
            "923365269505",
            "923465321596",
            "923115186083",
            "923495010997",
            "923439123424",
            "923420487566",
            "923149544408",
            "923152344562",
            "923320597475",
            "923135294325",
            "923421102248",
            "923465107866",
            "923341567833",
            "923091816294",
            "923146777110",
            "923455338296",
            "923338155550",
            "923135801211",
            "923235314150",
            "923435299965",
            "923119550272",
            "923335497581",
            "923068512862",
            "923355199903",
            "923218517689",
            "923455088474",
            "923072428654",
            "923332512910",
            "923365398680",
            "923459037885",
            "923347413139",
            "923315315467",
            "923081431436",
            "923107135360",
            "923097367478",
            "923023625312",
            "923086199922",
            "923491909714",
            "923369827970",
            "923170052913",
            "923015279368",
            "923213433208",
            "923315758591",
            "923355821048",
            "923455557117",
            "923365861929",
            "923334185394",
            "923129706628",
            "923315133179",
            "923329057032",
            "923429424567",
            "923345318641",
            "923330536296",
            "923008302254",
            "923216505721",
            "923154735553",
            "923468482446",
            "923477153174",
            "923175018477",
            "923465087701",
            "923315312442",
            "923465405342",
            "923235228423",
            "923135106566",
            "923200206633",
            "923138990301",
            "923473027797",
            "923352981002",
            "923155106058",
            "923349556367",
            "923361015584",
            "923340487678",
            "923125203544",
            "923101891881",
            "923014076640",
            "923476119259",
            "923215508836",
            "923345565554",
            "923215604744",
            "923135369441",
            "923365470670",
            "923015604234",
            "923464848226",
            "923485498676",
            "923153988999",
            "923058600979",
            "923370677375",
            "923325205375",
            "923430531070",
            "923441230314",
            "923365171997",
            "923351599574",
            "923015058573",
            "923497883346",
            "923110634799",
            "923042376719",
            "923435534033",
            "923335227985",
            "923015714455",
            "923222404691",
            "923065357106",
            "923345032526",
            "923469555768",
            "923247764836",
            "923095630604",
            "923125836988",
            "923449021282",
            "923135194787",
            "923039175036",
            "923350515286",
            "923048181370",
            "923333701116",
            "923462722696",
            "923335673270",
            "923333909902",
            "923472370556",
            "923428566606",
            "923035273124",
            "923447689823",
            "923315445995",
            "923155039392",
            "923455483639",
            "923138165304",
            "923330559497",
            "923066222171",
            "923128083044",
            "923408484004",
            "923369552555",
            "923455160030",
            "923118004995",
            "923061758834",
            "923000171000",
            "923338075042",
            "923160054096",
            "923215249830",
            "923227995303",
            "923135106566"

        ];
        for($i = 1; $i <= 100; $i++){
            if($i % 10 !=0){
                $this->send_message(['Name' => $num[$i - 1]]);
            }else{
                sleep(60);
            }
        }
     }

    function send_message($contact){
        $resp = array();
        $path = base_url().'/images/vizweb.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $img = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($img);
        //echo $base64;exit;
      
            
                foreach ($contact as $name => $number){
                    $data = array("phone"=>$number,"body"=>$base64, 'filename' => 'vizzwebsolutions.jpg', 'caption' => 'We\'re Hiring Now! Send your resume at hr@vizzwebsolutons.com or contact us: 03335914152');
                    $sms = $this->curl_sms($data);
                    //print_r($sms);
                    // if($sms){
                    //     $resp[]=array("phone"=>$number,"body"=>$name,"sent"=>$sms->sent,"message_id"=>$sms->id);
                    // }else{
                    //     $resp[]=array("phone"=>$number,"body"=>$name,"sent"=>0);
                    // }

                    //$this->admin_model->send_message($sms->id,10);
                }

    }

    function get_random_number($total_index,$except) {
        do {
            $n = mt_rand(0,$total_index - 1);
        } while(in_array($n, array($except)));
    
        return $n;
    }


    function curl_sms($data=false){ 
        
        // $data = [
        //     'phone' => '923435951953', // Receivers phone
        //     'body' => 'Hello, Andrew!', // Message
        // ];
        //echo "<pre>";print_r($data);exit;
        $json = json_encode($data); // Encode data to JSON
        // URL for request POST /message
        $data['ackNotificationsOn'] = 1;
        $token = 'wrye347gdaatpwps';
        $instanceId = '276372';
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
        $result = file_get_contents($url, false, $options);
        $result = json_decode($result);
        //echo "<pre>";print_r($result);exit;
        return $result;
    }

    function webhook_1(){
        $token = 'vgtb4qvrmzel9i2s';
        $instanceId = '260578';
        // $url = 'https://api.chat-api.com/instance'.$instanceId.'/webhook?token='.$token;
        // $data = file_get_contents("php://input", 1);
        // print_r($data);

        $data = file_get_contents('https://api.chat-api.com/instance'.$instanceId.'/webhook?token='.$token, true);
        print_r($data);
    }

    function get_message(){
        $token = '9xdubnii0cof9fze';
        $instanceId = '244236';
        $url = 'https://api.chat-api.com/instance'.$instanceId.'/messages?token='.$token;
        $result = file_get_contents($url); // Send a request
        $data = json_decode($result, 1); // Parse JSON
        foreach($data['messages'] as $message){ // Echo every message
            echo "Sender:".$message['author']."<br>";
            echo "Message: ".$message['body']."<br>";
        }
    }

    
    function qr(){
        $number = '044543434';
        $event_id = 23;
        $data = [
            "Name" => "Zeeshan ali",
            "Phone" => $number,
        ];
        if($number){
            $this->load->library('ciqrcode');
            $para = 'This is a text to encode become QR Code';
            $params['data'] = $para;
            $params['level'] = 'H';
            $params['size'] = 3;
            $params['savename'] = FCPATH.'tes.png';
            $config['black'] = array(200,180,190); // array, default is array(255,255,255)
            $config['white'] = array(255,255,255); // array, default is array(0,0,0)
            $this->ciqrcode->initialize($config);
            $this->ciqrcode->generate($params);

            echo '<img src="'.base_url().'tes.png" />';
            //return $path;
        // echo '<img src="'.base_url().'/images/qr_codes/tes2.png" />';
        }else{
            return false;
        }
    }


    function generate_qr(){
        $this->qr("mjd", '1');
    }

    function merge_qr(){
        $this->load->library('image_lib');
        $config['image_library'] = 'gd2';

        $config['source_image'] = FCPATH.'images/event_card/2.jpg';
        $config['wm_overlay_path'] = FCPATH.'images/qr_codes/3.png'; 
        $config['wm_type'] = 'overlay';
        $config['wm_opacity'] = '100';
        // $config['wm_vrt_alignment'] = 'top';
        // $config['wm_hor_alignment'] = 'left';
        $config['wm_vrt_offset'] = '100';
        $config['wm_hor_offset'] = '20';
        $config['dynamic_output'] = true;
        $image_name = 'test.jpg';
        $config['new_image'] = FCPATH.$image_name;
        //$config['wm_padding'] = '20';
        //$this->image_lib->clear();
        $this->image_lib->initialize($config);
        $this->image_lib->watermark();
        //return $image_name;
        echo FCPATH.$image_name;
    }

    function prepare_cards($event_id){
        $event = $this->admin_model->get_events(['events.id' => $event_id]);
        $event_card = $event[0]->event_card;

        $new_dir = $this->get_dir($event_id);
        
        $participants = $this->admin_model->get_participants(['event_id' => $event_id]);

        for($i = 0; $i<count($participants); $i++){
            $qr_img = $participants[$i]->qr_img;
            $img_name = $this->merge_qr($new_dir,$qr_img,$event_card);

            $this->admin_model->update_participants(['id' => $participants[$i]->id], ['card_img' => $img_name]);

        }
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


    function get_qr(){
        $this->load->library('ciqrcode');

        $params['data'] = 'Zeeshan Ali';
        $params['level'] = 'H';
        $params['size'] = 3;
        $params['savename'] = FCPATH.'tes.png';
        $this->ciqrcode->generate($params);

        echo '<img src="'.base_url().'tes.png" />';
    }

    function test_pdf(){
        //Load the library
        $img = $_SERVER['DOCUMENT_ROOT']."/qinvite/images/event_card/5.jpg";
        //echo $img;exit;
        $this->load->library('pdf');

        //Set folder to save PDF to
        $this->pdf->folder($_SERVER['DOCUMENT_ROOT']."/qinvite/pdf/");

        //Set the filename to save/download as
        $this->pdf->filename('test.pdf');

        //Set the paper defaults
        $this->pdf->paper('a4', 'portrait');

        //Load html view
        $html = "<div>Qinvite Invitation cards</div>";
        for($x=0;$x<5;$x++){
            $html .= "<div style='text-align:center;height:100%;'><h1>Qinvite Invitation Card</h1><img src='".$img."' style='width:100%;height:auto;'><br><br><br><span style='color:red;'>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span> </div>";
        }
        $this->pdf->html($html);
        $this->pdf->create('save');
    }
}