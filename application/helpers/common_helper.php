<?php







function get_curr_user(){

    $ci = & get_instance();

    $ci->load->model('admin_model');

    $id = $ci->session->userdata('admin_id');



    $user = $ci->admin_model->get_users(['id' => $id]);



    return $user[0];

}



function get_setting($key=""){

    $ci = & get_instance();

    if($key !=""){

        $ci->db->where("setting_name",$key);

    }

    $res = $ci->db->get("settings");

    if($res->num_rows() > 0){

        return $res->row()->setting_value;

    }else{

        return false;

    }



}


function apiExpired($instanceId, $token){

    
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.chat-api.com/instance{$instanceId}/checkPhone?token={$token}&phone=+97455513391",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $response = json_decode($response);
    if($response->error){
        return true;
    }else{
        return false;
    }
}