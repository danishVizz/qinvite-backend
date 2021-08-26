<?php

class Payments extends CI_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('payment_model');
        $this->load->model('api_model');
    }

    function preview($data){
        echo '<pre>'; print_r($data);exit;
    }


    public function index(){
        if(!isset($_GET["event_id"])){
            exit("No event ID found");
        }

        $credentials = $this->payment_model->get_credentials();
        foreach($credentials as $k => $v){
            $data[$v->setting_name] = $v->setting_value;
        }

        
        $event_id = $this->input->get('event_id');
        $event = $this->payment_model->get_event_details($event_id);

        //  ========== Get receptionists price
        $receptionists = $this->api_model->get_event_receptionists($event_id);
        foreach($receptionists as $k => $receptionist){
            $meta = $this->api_model->get_usermeta(['user_id' => $receptionist->id]);
            if($meta){
                foreach($meta as $k => $v){
                    $receptionist->{$v->meta_key} = $v->meta_value;
                }
            }
        }
        $total_recp_price = 0;
        //echo "<pre>";print_r($receptionists);exit;
        foreach($receptionists as $k => $receptionist){
            $total_recp_price += $receptionist->user_price;
        }

        $event->total_receptionist_price = $total_recp_price;

        //  ========== Get designer price
            $designer = $this->api_model->check_designer_user(['event_id' => $event_id]);
            if($designer){
                $meta = $this->api_model->get_usermeta(['user_id' => $designer->designer_id]);
                if($meta){
                    foreach($meta as $k => $v){
                        $designer->{$v->meta_key} = $v->meta_value;
                    }
                }

                $event->designer_price = $designer->user_price;
            
                $data['designer_price'] = $event->designer_price;
                $data['is_designer'] = true;
            }else{
                $data['is_designer'] = false;
            }


        
        // $this->preview($event);

        $data['order_id'] = $event->id;
        $data['pkg_name'] = $event->package_detail->package_name;
        $data['pkg_price'] = $event->package_detail->package_price;
        $data['user_id'] = $event->user_detail->id;
        $data['email'] = $event->user_detail->email;
        $data['phone'] = $event->user_detail->phone;
        $data['no_of_receptionists'] = $event->no_of_receptionists;
        $data['receptionist_price'] = $event->total_receptionist_price;
        //$this->preview($data);
        $this->load->view('admin/payment',$data);
    }

    function upgrade_package(){
        $package_id = $this->db->escape_str($this->input->get('package_id'));
        $people = $this->db->escape_str($this->input->get('people'));
        $event_id = $this->db->escape_str($this->input->get('event_id'));
        $user_id = $this->db->escape_str($this->input->get('user_id'));
        $user = $this->api_model->get_user(['id' => $user_id]);
        $user = $user['data'][0];

        $package = $this->api_model->get_event_package(['id' => $package_id]);
        $price = $this->api_model->get_price();
        $new_price = $people * $price;
        $credentials = $this->payment_model->get_credentials();
        foreach($credentials as $k => $v){
            $data[$v->setting_name] = $v->setting_value;
        }

        $data['order_id'] = $event_id;
        $data['pkg_name'] = $package->package_name;
        $data['pkg_price'] = $new_price;
        $data['user_id'] = $user->id;
        $data['email'] = $user->email;
        $data['phone'] = $user->phone;
        // $this->preview($data);
        $this->load->view('admin/packages/upgrade-package',$data);
    }


    function generate_checksum(){
        $this->load->view('admin/checksum_gen');
    }

    public function callback(){
        $credentials = $this->payment_model->get_credentials();
        foreach($credentials as $k => $v){
            $data[$v->setting_name] = $v->setting_value;
        }

        
        function verifychecksum_eFromStr($str, $key, $checksumvalue){  
                $sadad_hash = decrypt_e($checksumvalue, $key); 
                $salt = substr($sadad_hash, -4); 
                $finalString = $str . "|" . $salt; 
                $website_hash = hash("sha256", $finalString); 
                $website_hash .= $salt; 
                $validFlag = "FALSE"; 
                if ($website_hash == $sadad_hash){ 
                    $validFlag = "TRUE"; 
                }else{ 
                    $validFlag = "FALSE"; 
                } 
                return $validFlag; 
        } 


        function decrypt_e($crypt, $ky) { 
            $ky = html_entity_decode($ky); 
            $iv = "@@@@&&&&####$$$$"; 
            $data = openssl_decrypt($crypt, "AES-128-CBC", $ky, 0, $iv);  return $data; 
        } 
    
        $merchantId = $data['sadad_id']; //Replace with your merchant ID
        $secretKey = $data['api_key']; //Replace with your secret Key
        
        //Save incoming checksumhash into a variable and then unset it because we don't use it while verifying the checksum
        $checksum_response = $_POST['checksumhash'];
        unset($_POST['checksumhash']); 
            
        $data_response = array();  
        $data_response['postData'] = $_POST;  //Incoming POST without checksumhash in it. 
        $data_response['secretKey'] = $secretKey;  
        $key = $secretKey . $merchantId; 

        if (verifychecksum_eFromStr(json_encode($data_response), $key, $checksum_response) === "TRUE") { 
            //You can further check response code and transaction status variables in $_POST to verify transaction is success or failed.
            // echo '<pre>'; print_r($data_response);exit;
            $trx_data = [
                "order_id" => $this->input->post('ORDERID'),
                "merchant_id" => $this->input->post('MID'),
                "transaction_no" => $this->input->post('transaction_number'),
                "status" => $this->input->post('STATUS'),
                "amount" => $this->input->post('TXNAMOUNT'),
                "transaction_status" => $this->input->post('transaction_status')
            ];
            
            $this->payment_model->insert_transaction($trx_data);
            //$this->preview($trx_data);
            if($trx_data['transaction_status'] == 3){
                $this->payment_model->adjust_payments($trx_data['order_id']);
                echo ' <script>
                        setTimeout(function () {
                        window.ReactNativeWebView.postMessage("Transaction successful")
                        }, 2000)
                    </script>'; 
            }else if($trx_data['transaction_status'] == 2){
                echo ' <script>
                        setTimeout(function () {
                        window.ReactNativeWebView.postMessage("Transaction failed")
                        }, 2000)
                    </script>'; 
            }
            
        }else{
            //The POST response has not come from Sadad. If you're sure it has come from Sadad, check you've passed correct secret key and merchant ID above.
            echo ' <script>
                        setTimeout(function () {
                            window.ReactNativeWebView.postMessage("Transaction failed")
                        }, 2000)
                    </script>'; 
        }
        // $this->load->view('admin/callback', $data);
    }

    function webhook(){
        $txt = file_get_contents("php://input");
        if(empty($txt)){
            $txt = $_POST;
        }
        
        $myfile = fopen("testfile.txt", "a");
        
        fwrite($myfile, $txt);
        fclose($myfile);
        $txt = json_decode($txt);
        $this->payment_model->message_ack($txt->ack[0]->id, $txt->ack[0]->status);
    }
}