<?php

include '../php/mwa.php';

// Start server session
session_start();

// This will validate our session or redirect our user to login
// This page depends on an active session. We prefer the access_token for getting the /userinfo from the OAuth server
if(!isset($_SESSION['userinfo'])){
	if(!isset($_SESSION['access_token'])){
		// we don't have an access_token
		// do we have what we need to get an access_token?
		if(isset($_GET['code']) && isset($_GET['scope']) && isset($_GET['state']) && isset($_SESSION['secret'])){
			$hashed_secret = hash('sha512',$_SESSION['secret']);
			$code = $_GET['code'];
			$scope = $_GET['scope'];
			
			// find hashed secret
			preg_match('/token=(.*)/', $_GET['state'], $re);
			if(!($hashed_secret == $re[1])){
				// State mismatch
				echo "<p>Session doesn't match</p>";
				header("Location: ".$LOGIN_URL);
			}

			// Okay, let's get the access_token
			// use the authorization code provided
			// Build the query
			$auth_str = base64_encode($OAUTH_CLIENT_ID.":".$OAUTH_CLIENT_SECRET);
			$header = ['Authorization: Basic '.$auth_str];
			$post = [
				'client_id' => $OAUTH_CLIENT_ID,
				'code' => $_GET['code'],
				'grant_type' => 'authorization_code',
				'redirect_uri' => 'https://www.metawarrior.army/dev/callback.php'
			];
			$crl = curl_init($OAUTH_TOKEN_ENDPOINT);
			curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
			curl_setopt($crl, CURLOPT_POST,true);
			curl_setopt($crl, CURLOPT_POSTFIELDS,$post);
			curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
			// query the oauth server
			try {
				$token_resp = json_decode(curl_exec($crl));
			}
			catch (Exception $e){
				echo 'Caught exception: ', $e->getMessage();
				exit();
			}
			// store the access_token
			$_SESSION['access_token'] = $token_resp;

			// since we got a new access_token, let's get the userinfo
			// build the query
			$header = ['Authorization: Bearer '.$_SESSION['access_token']->access_token, 'Content-type: application/json'];
			$crl = curl_init($OAUTH_USERINFO_ENDPOINT);
			curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($crl, CURLOPT_HEADER, 0);
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			// query the oauth server
			try{
				$usr_resp = curl_exec($crl);
			}
			catch(Exception $e){
				echo "Caught exception: ", $e->getMessage();
			}
			// Store the userinfo
			$_SESSION['userinfo'] = $usr_resp;

		}
		else{
			// No authorization_code or access_token, redirect user
			header("Location: ".$LOGIN_URL);

		}
	}
}


?>
<!doctype html>
<html lang="en" class="h-100" data-bs-theme="auto">
  <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="admin">
    <title>MetaWarrior Army</title>

    <link rel="canonical" href="https://www.metawarrior.army/dev/login.php">

    

    <!-- Favicons -->

    <meta name="theme-color" content="#712cf9">


    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }

      .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
      }

      .bi {
        vertical-align: -.125em;
        fill: currentColor;
      }

      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }

      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }

      .btn-bd-primary {
        --bd-violet-bg: #712cf9;
        --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

        --bs-btn-font-weight: 600;
        --bs-btn-color: var(--bs-white);
        --bs-btn-bg: var(--bd-violet-bg);
        --bs-btn-border-color: var(--bd-violet-bg);
        --bs-btn-hover-color: var(--bs-white);
        --bs-btn-hover-bg: #6528e0;
        --bs-btn-hover-border-color: #6528e0;
        --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
        --bs-btn-active-color: var(--bs-btn-hover-color);
        --bs-btn-active-bg: #5a23c8;
        --bs-btn-active-border-color: #5a23c8;
      }
      .bd-mode-toggle {
        z-index: 1500;
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="/css/index.css" rel="stylesheet">
  </head>
  <body class="d-flex h-100 text-center text-bg-dark">
    
    
<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  <header class="mb-auto">
    <div>
      <h3 class="float-md-start mb-0">MetaWarrior Army</h3>
      <nav class="nav nav-masthead justify-content-center float-md-end">
        <a class="nav-link fw-bold py-1 px-0" aria-current="page" href="/">Home</a>
		<a class="nav-link fw-bold py-1 px-0 active" href="/dev/callback.php">callback</a>
        <!--
        <a class="nav-link fw-bold py-1 px-0" href="#">Features</a>
        <a class="nav-link fw-bold py-1 px-0" href="#">Contact</a>
        -->
      </nav>
    </div>
  </header>

  <main class="px-3">
    <h1>Welcome to MetaWarrior Army</h1>
    <p class="lead">---</p>
	<?php
	if(isset($_SESSION['userinfo'])){
		$userinfo = json_decode($_SESSION['userinfo']);
		echo "<p class=\"lead\">".$userinfo->sub."</p>";
		echo "<p class=\"lead\">".$userinfo->username."</p>";
	}


	?>


    <p class="lead">
      <a href="/dev/logout.php" class="btn btn-lg btn-light fw-bold border-white bg-white">Logout</a>
    </p>

  </main>

  <footer class="mt-auto text-white-50">
    <!-- footer text example -->
    <!--
    <p>Cover template for <a href="https://getbootstrap.com/" class="text-white">Bootstrap</a>, by <a href="https://twitter.com/mdo" class="text-white">@mdo</a>.</p>
    -->
  </footer>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

    </body>
</html>