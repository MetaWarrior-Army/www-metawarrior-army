<?php
# Start server session

include '../php/mwa.php';
include './config.php';


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
	else{
		echo "<p>Session doesn't match</p>";
		header("Location: /dev/login.php");
	}
}
else{
	header("Location: /dev/login.php");
}

// Session is valid, now let's verify the code we received is legit. 
$auth_str = base64_encode($OAUTH_CLIENT_ID.":".$OAUTH_CLIENT_SECRET);
$header = ['Authorization: Basic '.$auth_str];

var_dump($header);

$post = [
	'client_id' => $OAUTH_CLIENT_ID,
	//'client_secret' => $OAUTH_CLIENT_SECRET,
	'code' => $_GET['code'],
	'grant_type' => 'authorization_code',
	'redirect_uri' => 'https://www.metawarrior.army/dev/callback.php'
];
	

$js_post = json_encode($post);

var_dump($js_post);
$url = "https://auth.metawarrior.army/oauth2/token";
$crl = curl_init($url);
curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
curl_setopt($crl, CURLOPT_POST,true);
curl_setopt($crl, CURLOPT_POSTFIELDS,$post);
//rl_setopt($crl, CURLOPT_RETURNTRANSFER, true);

$resp = curl_exec($crl);



curl_close($crl);

var_dump($resp);



// Below is the HTML the user will interact with.
?>

	
	
	
</main>

</body>
</html>