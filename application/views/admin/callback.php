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
        } else { 
        $validFlag = "FALSE"; 
        } 
        return $validFlag; 
        } 
        function decrypt_e($crypt, $ky) { 
        $ky = html_entity_decode($ky); 
        $iv = "@@@@&&&&####$$$$"; 
        $data = openssl_decrypt($crypt, "AES-128-CBC", $ky, 0, $iv);  return $data; 
        } 

        $merchantId = $sadad_id; //Replace with your merchant ID
        $secretKey = $api_key; //Replace with your secret Key
		
		//Save incoming checksumhash into a variable and then unset it because we don't use it while verifying the checksum
        $checksum_response = $_POST['checksumhash'];
        unset($_POST['checksumhash']); 
         
        $data_repsonse = array();  
        $data_repsonse['postData'] = $_POST;  //Incoming POST without checksumhash in it. 
        $data_repsonse['secretKey'] = $secretKey;  
        $key = $secretKey . $merchantId; 

        if (verifychecksum_eFromStr(json_encode($data_repsonse), $key, $checksum_response) === "TRUE") { 
			//You can further check response code and transaction status variables in $_POST to verify transaction is success or failed.
			echo $checksum_response;
            echo '<pre>'; print_r($data_repsonse);
         echo 'Checksum TRUE'; 
        }else{
			//The POST response has not come from Sadad. If you're sure it has come from Sadad, check you've passed correct secret key and merchant ID above.
         echo 'Checksum False'; 
        }
?>