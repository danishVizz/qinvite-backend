<?php


function verifychecksum_eFromStr($str, $key, $checksumvalue) {

    $sadad_hash = decrypt_e($checksumvalue, $key);

    $salt = substr($sadad_hash, -4);
    $finalString = $str . "|" . $salt;
    $website_hash = hash("sha256", $finalString);
    $website_hash .= $salt;
    $validFlag = "FALSE";

    if ($website_hash == $sadad_hash) {
        $validFlag = "TRUE";
    }
    else {
        $validFlag = "FALSE";
    }
    return $validFlag;
}

function decrypt_e($crypt, $ky) {
    $ky = html_entity_decode($ky);
    $iv = "@@@@&&&&####$$$$";
    $data = openssl_decrypt($crypt, "AES-128-CBC", $ky, 0, $iv);
    return $data;
}

function getChecksumFromString($str, $key) {

    $salt = generateSalt_e(4);
    $finalString = $str . "|" . $salt;
    $hash = hash("sha256", $finalString);
    $hashString = $hash . $salt;
    $checksum = encrypt_e($hashString, $key);
    return $checksum;

}

function generateSalt_e($length) {

    $random = "";
    srand((double) microtime() * 1000000);
    $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
    $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
    $data .= "0FGH45OP89";
    for ($i = 0; $i < $length; $i++) {
        $random .= substr($data, (rand() % (strlen($data))), 1);
    }
    //return $random;
	return 'NMo9';
}

function encrypt_e($input, $ky) {
    $ky = html_entity_decode($ky);
    $iv = "@@@@&&&&####$$$$";
    $data = openssl_encrypt($input, "AES-128-CBC", $ky, 0, $iv);
    return $data;
}



        $sadad_checksum_array = [];
        $sadad__checksum_data = [];
        $txnDate = date("Y-m-d H:i:s");
    $secretKey = $api_key;//Your secret key
    $merchantID = $sadad_id;//Your merchant id/sadad id
    $sadad_checksum_array['merchant_id'] = $merchantID;
    $sadad_checksum_array['ORDER_ID'] = $order_id;
    $sadad_checksum_array['WEBSITE'] = $domain; //Should be same as domain name which is used to generate the secret key

    $sadad_checksum_array['TXN_AMOUNT'] = ($is_designer ? $pkg_price + $receptionist_price + $designer_price : $pkg_price + $receptionist_price);
    $sadad_checksum_array['CUST_ID'] = $user_id;
    //$sadad_checksum_array['EMAIL'] = $email;
    $sadad_checksum_array['MOBILE_NO'] = $phone;
    $sadad_checksum_array['SADAD_WEBCHECKOUT_PAGE_LANGUAGE'] = 'ENG';
    $sadad_checksum_array['CALLBACK_URL'] =
    'https://qinvite.vizzwebsolutions.com/payments/callback'; //replace with your callback url
    $sadad_checksum_array['txnDate'] = $txnDate;
    $sadad_checksum_array['productdetail'] =
    array(
        array(
            'order_id'=> $sadad_checksum_array['ORDER_ID'],
                'quantity'=>'1',
                'amount'=>$pkg_price,
            
            'type'=>'Package',
            'itemname'=>$pkg_name
    
        ),
        array(
            'order_id'=> $sadad_checksum_array['ORDER_ID'],
                'quantity'=>$no_of_receptionists,
                'amount'=>$receptionist_price,
            
            'type'=>'Receptionists',
            'itemname'=>'Receptionists' 
        
        )
        
    );
       
    if($is_designer){
        $sadad_checksum_array['productdetail'][] =
        array(
            'order_id'=> $sadad_checksum_array['ORDER_ID'],
                'quantity'=>'1',
                    'amount'=>$designer_price,
            
            'type'=>'Designer',
            'itemname'=>'Designer' 
            
        );
    }

        $sAry1 = [];
        $sadad_checksum_array1 = [];
        
        foreach ($sadad_checksum_array as $pK => $pV) {
	    if ($pK == 'checksumhash')
	        continue;
	    if (is_array($pV)) {
	        $prodSize = sizeof($pV);
	        for ($i = 0; $i < $prodSize; $i++) {
	            foreach ($pV[$i] as $innK => $innV) {
	                $sAry1[] = "<input type='hidden' name='productdetail[$i][" . $innK . "]' value='" . trim($innV) . "'/>";
	                $sadad_checksum_array1['productdetail'][$i][$innK] = trim($innV);
	            }
	        }
	    } else {
$sAry1[] = "<input type='hidden' name='" . $pK . "' id='" . $pK . "' value='" . trim($pV) . "'/>";
	        $sadad_checksum_array1[$pK] = trim($pV);
	    }
	 
	}




        $sadad__checksum_data['postData'] = $sadad_checksum_array1;
        $sadad__checksum_data['secretKey'] = $secretKey;

        $checksum = getChecksumFromString(json_encode($sadad__checksum_data), $secretKey . $merchantID);
    
       
        $sAry1[] = "<input type='hidden' name='checksumhash' value='" . $checksum . "'/>";


$action_url = 'https://sadadqa.com/webpurchase';
                
                echo '<form action="' . $action_url . '" method="post" name="gosadad">
                    ' . implode('', $sAry1) . '
                </form>
				
<script type="text/javascript">
                        document.gosadad.submit();
                    </script>';
?>

<!-- 

function getChecksumFromString($str, $key) { 

    $salt = generateSalt_e(4); 
    $finalString = $str . "|" . $salt; 
    $hash = hash("sha256", $finalString); 
    $hashString = $hash . $salt; 
    $checksum = encrypt_e($hashString, $key); 
    return $checksum; 
   
   } 
   
   function generateSalt_e($length) { 
   
    $random = ""; 
    srand((double) microtime() * 1000000); 
    $data = "AbcDE123IJKLMN67QRSTUVWXYZ"; 
    $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz"; 
    $data .= "0FGH45OP89"; 
    for ($i = 0; $i < $length; $i++) { 
    $random .= substr($data, (rand() % (strlen($data))), 1);  } 
    return $random; 
   } 
   
   function encrypt_e($input, $ky) { 
    $ky = html_entity_decode($ky); 
    $iv = "@@@@&&&&####$$$$"; 
    $data = openssl_encrypt($input, "AES-128-CBC", $ky, 0, $iv);  return $data; 
   } 
   
    $sadad_checksum_array = array(); 
    $sadad__checksum_data = array(); 
    $txnDate = "2020-09-19 13:01:33"; 
    $email ='example@example.com'; 
    $secretKey = 'ourkey'; 
    $merchantID = 'ourID'; 
    $sadad_checksum_array['merchant_id'] = $merchantID;  
    $sadad_checksum_array['ORDER_ID'] = '4641'; 
    $sadad_checksum_array['WEBSITE'] = 'ourdomain.com';  
    $sadad_checksum_array['TXN_AMOUNT'] = '50.00'; 
    $sadad_checksum_array['CUST_ID'] = $email; 
    $sadad_checksum_array['EMAIL'] = $email; 
    $sadad_checksum_array['MOBILE_NO'] = '54687258';  
    $sadad_checksum_array['SADAD_WEBCHECKOUT_PAGE_LANGUAGE'] = 'ENG';  
    $sadad_checksum_array['CALLBACK_URL'] = 'http://ourdomain.com/callback.php'; 
    $sadad_checksum_array['txnDate'] = $txnDate; 
    $sadad_checksum_array['productdetail'] = array( 
                                                array( 
                                                    'order_id'=> '4641', 
                                                    'itemname'=>'Sample Product', 
                                                    'amount'=>'50', 
                                                    'quantity'=>'1',
                                                    'type'=>'line_item' 
                                                ) 
                                            ); 
     
           $sadad__checksum_data['postData'] = $sadad_checksum_array;  
   $sadad__checksum_data['secretKey'] = $secretKey; 
   
   $sAry1 = array(); 
   
                   $sadad_checksum_array1 = array(); 
                   foreach($sadad_checksum_array as $pK => $pV){ 
                       if($pK=='checksumhash') continue; 
                       if(is_array($pV)){ 
                           $prodSize = sizeof($pV); 
                           for($i=0;$i<$prodSize;$i++){ 
                               foreach($pV[$i] as $innK => 
   $innV){ 
           $sAry1[] = ""; 
       $sadad_checksum_array1['productdetail'][$i][$innK] = 
   trim($innV); 
           } 
       } 
                       } else { 
                           $sAry1[] = ""; 
   $sadad_checksum_array1[$pK] = 
   trim($pV); 
           } 
       } 
   $sadad__checksum_data['postData'] = $sadad_checksum_array1;  
   $sadad__checksum_data['secretKey'] = $secretKey;  
   $mgd = getChecksumFromString(json_encode($sadad__checksum_data), $secretKey . 
   $merchantID); 
   //echo $mgd;

?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<form action="https://secure.sadadqa.com/webpurchasepage" method="post" id="paymentform" name="paymentform" data-link="https://secure.sadadqa.com/webpurchasepage">
        <input type="hidden" name="merchant_id" id="merchant_id" value="5761967">
        <input type="hidden" name="ORDER_ID" id="ORDER_ID" value="4641">

        <input type="hidden" name="WEBSITE" id="WEBSITE" value="ourdomain.com">
        <input type="hidden" name="TXN_AMOUNT" id="TXN_AMOUNT" value="50.00">

        <input type="hidden" name="CUST_ID" id="CUST_ID" value="example@example.com">
        <input type="hidden" name="EMAIL" id="EMAIL" value="example@example.com">

        <input type="hidden" name="MOBILE_NO" id="MOBILE_NO" value="54687258">
        <input type="hidden" name="SADAD_WEBCHECKOUT_PAGE_LANGUAGE" id="SADAD_WEBCHECKOUT_PAGE_LANGUAGE" value="ENG">

        <input type="hidden" name="CALLBACK_URL" id="CALLBACK_URL" value="http://ourdomain.com/callback.php">
        <input type="hidden" name="txnDate" id="txnDate" value="2020-09-19 13:01:33">

        <input type="hidden" name="productdetail[0][order_id]" value="4641">
        <input type="hidden" name="productdetail[0][itemname]" value="Sample Product">

        <input type="hidden" name="productdetail[0][amount]" value="50">
        <input type="hidden" name="productdetail[0][quantity]" value="1">

        <input type="hidden" name="productdetail[0][type]" value="line_item">
        <input type="hidden" name="checksumhash" value="<?=$mgd?>">
             
        <script type="text/javascript">
            document.paymentform.submit();
        </script>
</form>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-modal/2.2.6/js/bootstrap-modal.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-modal/2.2.6/js/bootstrap-modalmanager.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-modal/2.2.6/css/bootstrap-modal.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-modal/2.2.6/css/bootstrap-modal-bs3patch.min.css" crossorigin="anonymous">

    <style>
        .close-btn{ 
            height: auto; 
            width: auto; 
            -webkit-appearance: none !important; 
            background: none !important; 
            border: 0px; 
            position: absolute; 
            right: 10px; 
            z-index: 11; 
            cursor: pointer; 
            outline: 0px !important; 
            box-shadow: none; 
            top: 15px; 
        }
        .close, .close:hover{ 
            color: #000; 
            font-size:30px;
        }
        .modal-body{ 
            padding: 0px; 
            border-radius: 15px; 
        }
        #onlyiframe{ 
            width:100% !important; 
            height:100vh !important; 
            overflow: hidden !important; 
            border:0; 
            top: 0; 
            left: 0; 
            bottom: 0; 
            right: 0; 
        }
        #includeiframe{ 
            height:100vh !important; 
            overflow: hidden !important; 
            border:0; 
        }
        .modal-backdrop { 
            background-color: #000 !important; 
        } 
        ul.order_details{ 
            display: none !important; 
        }    
 </style>

  
<div id="container_div_sadad">
    <div class="modal fade not_hide_sadad" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <button type="button" class="close-btn" onClick="closemodal();" aria-label="Close"> 
                    <span class="close">×</span> 
                </button>
                <div class="modal-body">
                    <iframe name="includeiframe" id="includeiframe" frameborder="0" scrolling="no"></iframe> 
                </div>
            </div>
        </div>
    </div>
    <iframe name="onlyiframe" id="onlyiframe" border="0" class="not_hide_sadad" frameborder="0" scrolling="no"></iframe> 
    </div>
    
    <script>
        function closemodal()
        {
            $('#exampleModal').modal('hide');
                //When modal popup is closed (So payment is cancelled) 
        }
        jQuery(document).ready(function($){
            if ($('#showdialog').val() == 1) { 
                $('#exampleModal').modal('show'); 
                $('#paymentform').attr('target', 'includeiframe').submit(); 
                $('#onlyiframe').remove(); 
                } 
            else { $('#exampleModal').remove(); 
                $('#paymentform').attr('target', 'onlyiframe').submit(); 
            } 
                $('iframe').load(function() { 
                $(this).height( 
                $(this).contents().find("body").height() ); 
            if(this.contentWindow.location=='Your callback URL here'){ 
                //Customer redirected to callback URL withhin iFrame so do your further processing here. Redirect to success page or showing success/failed message. 
                } 
            }); 
        });
    </script> -->

