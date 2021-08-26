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