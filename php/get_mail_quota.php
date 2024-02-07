<?php
if($_SESSION['userinfo']->email_active){

    // Userinfo for address and validating token
    $SECRET_HASHED = hash('sha512',$MAIL_API_SECRET);
    $data = array( 'key' => $SECRET_HASHED, 'username' => $_SESSION['userinfo']->username."@metawarrior.army");


    $header = ['Content-type: application/json'];
    $crl = curl_init($MAIL_QUOTA_API_URL);
    curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
    curl_setopt($crl, CURLOPT_POST,true);
    curl_setopt($crl, CURLOPT_POSTFIELDS,json_encode($data));
    curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
    try{
        $getquota_resp = curl_exec($crl);
        $quota_obj = json_decode($getquota_resp);
        curl_close($crl);
    }
    catch(Exception $e){
        echo "Caught exception: ", $e->getMessage();
    }

    echo "<div class=\"progress\" role=\"progressbar\" aria-label=\"Basic example\" aria-valuenow=\"".round($quota_obj->percent,0)."\" aria-valuemin=\"0\" aria-valuemax=\"100\">
            <div class=\"progress-bar\" style=\"width: ".round($quota_obj->percent,0)."%\">".round($quota_obj->percent,0)."%</div>
            </div>";
}
else{
    echo "<p class=\"lead\">NaN</p>";
}




?>