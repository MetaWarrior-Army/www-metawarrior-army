<?php

include '../php/mwa.php';
include './config.php';

# Start server session
session_start();

// session should already be stored
if(isset($_GET['code']) && isset($_GET['scope']) && isset($_GET['state']) && isset($_SESSION['secret'])){
	$hashed_secret = hash('sha512',$_SESSION['secret']);
	$code = $_GET['code'];
	$scope = $_GET['scope'];
	// find hashed secret
	preg_match('/token=(.*)/', $_GET['state'], $re);
	if($hashed_secret == $re[1]){
		echo "<p>Session Matches</p>";
	}
	else{ // Redirect bad user
		echo "<p>Session doesn't match</p>";
		header("Location: /dev/login.php");
	}
}
else{ // redirect to login
	header("Location: /dev/login.php");
}

// Check to see if we already have an access_token
if(isset($_SESSION['access_token'])){
	echo "<p>Access Token: ".$_SESSION['access_token']->access_token."</p>";
	echo "<p>Expires In: ".$_SESSION['access_token']->expires_in."</p>";
	echo "<p>ID Token: ".$_SESSION['access_token']->id_token."</p>";
	echo "<p>Refresh Token: ".$_SESSION['access_token']->refresh_token."</p>";
	echo "<p>Scope: ".$_SESSION['access_token']->scope."</p>";
	echo "<p>Token Type: ".$_SESSION['access_token']->token_type."</p>";
}
else{
	// Session is valid, now let's verify the code we received is legit. 
	$auth_str = base64_encode($OAUTH_CLIENT_ID.":".$OAUTH_CLIENT_SECRET);
	$header = ['Authorization: Basic '.$auth_str];

	$post = [
		'client_id' => $OAUTH_CLIENT_ID,
		'code' => $_GET['code'],
		'grant_type' => 'authorization_code',
		'redirect_uri' => 'https://www.metawarrior.army/dev/callback.php'
	];

	$url = "https://auth.metawarrior.army/oauth2/token";
	$crl = curl_init($url);
	curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
	curl_setopt($crl, CURLOPT_POST,true);
	curl_setopt($crl, CURLOPT_POSTFIELDS,$post);
	curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);

	try {
		$resp = json_decode(curl_exec($crl));
	}
	catch (Exception $e){
		echo 'Caught exception: ', $e->getMessage();
	}

	echo "<p>Access Token: ".$_SESSION['access_token']->access_token."</p>";
	echo "<p>Expires In: ".$_SESSION['access_token']->expires_in."</p>";
	echo "<p>ID Token: ".$_SESSION['access_token']->id_token."</p>";
	echo "<p>Refresh Token: ".$_SESSION['access_token']->refresh_token."</p>";
	echo "<p>Scope: ".$_SESSION['access_token']->scope."</p>";
	echo "<p>Token Type: ".$_SESSION['access_token']->token_type."</p>";

}

?>

<html>
<body>
	<main>	
	</main>

</body>
</html>