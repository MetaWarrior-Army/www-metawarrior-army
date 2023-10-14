<?php
# Start server session

include '../php/mwa.php';
include './config.php';


session_start();

# Generate secret for individual session (Server Side Security)
$secret = generateNonce(64);
$hashed_secret = hash('sha512',$secret);
# Store secret in session
$_SESSION['secret']=$secret;

# Generate nonce for OAuth call (Provider Side Security)
$nonce = generateNonce(64);

// SETUP LOGIN TO Ory Hydra

# Identify OAuth Provider Endpoint
$auth_endpoint = 'https://auth.metawarrior.army/oauth2/auth';
# Set OAuth Parameters
$response_type="code";
$client_id=$OAUTH_CLIENT_ID;
$scope=urlencode("profile");
$redirect_url='https://www.metawarrior.army/dev/callback.php'; // Redirect URI is preconfigured with the provider. In this example we use login.php
$state=urlencode("token=".$hashed_secret);

// Complete Google OAuth URL
# This is the URL we send the user to for signing-in/signing-up
$oauth_url = $auth_endpoint."?client_id=".$client_id."&response_type=".$response_type."&redirect_uri=".$redirect_url."&scope=".$scope."&state=".$state;

// Below is the HTML the user will interact with.
?>


<a id="google-login-button" class="btn btn-block btn-lg btn-social btn-google" href="
	<?php 
		echo $oauth_url; 

	?>
	">
		MWA
	</a>
	
	
	
</main>

</body>
</html>