<?php

include './php/mwa.php';

error_log("TEST");

// Start server session
session_start();

error_log("SESSION STARTED");

// This will validate our session or redirect our user to login
// This page depends on an active session. We prefer the access_token for getting the /userinfo from the OAuth server

if(!isset($_SESSION['access_token'])){
  error_log("NO ACCESS TOKEN");
  // we don't have an access_token
  // do we have what we need to get an access_token?
  if(isset($_GET['code']) && isset($_GET['scope']) && isset($_GET['state']) && isset($_SESSION['secret'])){
    error_log("WE HAVE OUR QUERY VARIABLES");
    $hashed_secret = hash('sha512',$_SESSION['secret']);
    $code = $_GET['code'];
    // This is a string ex: "scope1 scope2 scope3" etc. 
    $scope = $_GET['scope'];
    
    
    // find hashed secret
    preg_match('/token=(.*)/', $_GET['state'], $re);
    if(!($hashed_secret == $re[1])){
      // State mismatch
      echo "<p>Session doesn't match</p>";
      header("Location: ".$LOGIN_URL);
    }
    else{
      error_log("SESSION MATCH");
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
      'redirect_uri' => $OAUTH_REDIRECT_URL,
    ];
    $crl = curl_init($OAUTH_TOKEN_ENDPOINT);
    curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
    curl_setopt($crl, CURLOPT_POST,true);
    curl_setopt($crl, CURLOPT_POSTFIELDS,$post);
    curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
    // query the oauth server
    try {
      $token_resp = json_decode(curl_exec($crl));
      curl_close($crl);
    }
    catch (Exception $e){
      echo 'Caught exception: ', $e->getMessage();
      exit();
    }
    // store the access_token
    $_SESSION['access_token'] = $token_resp;
    
  }
  else{
    // No authorization_code or access_token, redirect user
    header("Location: ".$LOGIN_URL);

  }
}

// Userinfo for address and validating token
$header = ['Authorization: Bearer '.$_SESSION['access_token']->access_token, 'Content-type: application/json'];
$crl = curl_init($OAUTH_USERINFO_ENDPOINT);
curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
curl_setopt($crl, CURLOPT_POST,true);
curl_setopt($crl, CURLOPT_POSTFIELDS,json_encode($getUserPost));
curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
try{
  $userinfo_resp = curl_exec($crl);
  $userinfo = json_decode($userinfo_resp);
  curl_close($crl);
}
catch(Exception $e){
  echo "Caught exception: ", $e->getMessage();
}

// Setup Logout with hydra oauth2
$hashed_secret = hash('sha512',$_SESSION['secret']);
$state=urlencode("token=".$hashed_secret);
$oauth_logout_url = $OAUTH_LOGOUT_ENDPOINT."?client_id=".$OAUTH_CLIENT_ID."&id_token_hint=".$_SESSION['access_token']->id_token."&post_logout_redirect_uri=".urlencode('https://www.metawarrior.army/dev/logout.php')."&state=".$state;

// Setup shortened address
$front_addr = substr($userinfo->sub,0,5);
$end_addr = substr($userinfo->sub,-4);
$nick_addr = $front_addr.'...'.$end_addr;

// Get user's current obj from api
if(isset($userinfo)){
  // Use access_token to get user information
  $getUserPost = [
    'address' => $userinfo->sub,
  ];
  $header = ['Authorization: Bearer '.$_SESSION['access_token']->access_token, 'Content-type: application/json'];
  $crl = curl_init($GET_USER_API_URL);
  curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
  curl_setopt($crl, CURLOPT_POST,true);
  curl_setopt($crl, CURLOPT_POSTFIELDS,json_encode($getUserPost));
  curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
  try{
    $get_usr_resp = curl_exec($crl);
    curl_close($crl);
  }
  catch(Exception $e){
    echo "Caught exception: ", $e->getMessage();
  }

  $userObj = json_decode($get_usr_resp);
  //var_dump($userObj);
  $_SESSION['userinfo'] = $userObj;
}

?>

<!DOCTYPE html>
<html lang="en" class="h-100" data-bs-theme="auto">
  <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="admin">
    <title>User Profile - MetaWarrior Army</title>

    <link rel="canonical" href="https://www.metawarrior.army/profile.php">

    <!-- Favicons -->

    <meta name="theme-color" content="#712cf9">

    
    <!-- Custom styles for this template -->
    <link href="/css/index.css" rel="stylesheet">
    <link href="/css/profile.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/jdenticon@3.2.0/dist/jdenticon.min.js"
        integrity="sha384-yBhgDqxM50qJV5JPdayci8wCfooqvhFYbIKhv0hTtLvfeeyJMJCscRfFNKIxt43M"
        crossorigin="anonymous">
</script>
  </head>
  <body class="d-flex h-100 text-center text-bg-dark">
    
    
<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
  <header class="mb-auto">
    <div>
      <h3 class="float-md-start mb-0">MetaWarrior Army</h3>
      <nav class="nav nav-masthead justify-content-center float-md-end">
        <a class="nav-link fw-bold py-1 px-0" aria-current="page" href="/">Home</a>
		<a class="nav-link fw-bold py-1 px-0 active" href="/dev/callback.php">Profile</a>
      </nav>
    </div>
  </header>

  <main class="px-3">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-8 col-lg-6">
        <!-- Section Heading-->
        <div class="section_heading text-center wow fadeInUp" data-wow-delay="0.2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
          <h3><u>Welcome</u> <br><?php
            // manipulate the user's wallet address here
            //echo($userinfo->sub);
            if($userObj->username){
              echo $userObj->username;
            }
            else{
              echo($nick_addr);
            }
            
          ?></h3>
          <div class="line"></div>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <!-- Single Advisor-->
      <div class="col-12 col-sm-8 col-lg-6 w-100">
        <div class="single_advisor_profile wow fadeInUp" data-wow-delay="0.2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">

          <!-- Team Thumb-->
          <svg width="80" height="80" data-jdenticon-value="<?php echo $userinfo->sub ?>"></svg>  

          <script src="/js/chooseUsername.js"></script>

          <!-- Choose Username Form -->

          <?php

              if($userObj->username){
                if($userObj->nft_0_tx){
                  echo "<br><a href=\"https://testnet-zkevm.polygonscan.com/tx/".$userObj->nft_0_tx."\" target=\"_blank\" class=\"link-light\">Proof of Membership</a>";

                }
                else{
                  echo "<br>You still have to <a href=\"https://nft.metawarrior.army/\" class=\"link-light\">Mint</a> your NFT!";
                }
              }
              else{
                echo "<br>Welcome to MetaWarrior Army. <br>To become a Member you must choose a username and <b><a href=\"https://nft.metawarrior.army\" class=\"link-light\">Mint</a></b> your NFT.";
              }

          ?>
                    
            <!-- Social Info-->
            <div class="social-info"><a href="#"><i class="fa fa-facebook"></i></a><a href="#"><i class="fa fa-twitter"></i></a><a href="#"><i class="fa fa-linkedin"></i></a></div>
          
          <!-- Team Details-->
          <div class="single_advisor_details_info">
            <h3 class="designation" id="username"><?php if($userObj->username){echo $userObj->username;}else{echo "[]";}?></h3>  
            <h6><?php echo($nick_addr); ?></h6>
            
            <p>:::</p>
            <?php

              if($userObj->username){
                echo "<p><b><u>Your User Object: </u></b></p>";
                echo "<p className=\"small\">";
                foreach($userObj as $key => $value){
                  echo "$value : $key<br>";
                  //var_dump($value);
                }
              }

            ?>
            </p>
            <p class="lead">
              <a href="<?php echo $oauth_logout_url; ?>" class="btn btn-lg btn-light fw-bold border-white bg-white">Logout</a>
            </p>

          </div>
        </div>
      </div>
      
    </div>
  </div>


    <p class="lead">---</p>

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
<script src="/js/chooseUsername.js"></script>

    </body>
</html>