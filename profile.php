<?php
// Config and helper functions
include './php/mwa.php';

// Start server session
session_start();

// This will validate our session or redirect our user to login
// This page depends on an active session. We prefer the access_token for getting the /userinfo from the OAuth server
if(!isset($_SESSION['access_token']) or isset($_SESSION['access_token']->error)){
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

if(!isset($_SESSION['userinfo']->username)){
  // Userinfo for address and validating token
  $header = ['Authorization: Bearer '.$_SESSION['access_token']->access_token, 'Content-type: application/json'];
  $crl = curl_init($OAUTH_USERINFO_ENDPOINT);
  curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
  curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
  try{
    $userinfo_resp = curl_exec($crl);
    $userinfo = json_decode($userinfo_resp);
    curl_close($crl);
  }
  catch(Exception $e){
    echo "Caught exception: ", $e->getMessage();
  }

  // Get user's current obj from api
  if(isset($userinfo)){
    // Use access_token to get user information
    $getUserPost = [
      'address' => $userinfo->sub,
    ];
    $header = ['Authorization: Bearer '.$_SESSION['access_token']->access_token, 'Content-type: application/json'];
    $crl = curl_init($GET_USER_API_URL);
    //OIDC User Info
    //$crl = curl_init("https://auth.metawarrior.army/userinfo");
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

    $_SESSION['userinfo'] = $userObj;
  }
}
else{
  $userObj = $_SESSION['userinfo'];
}

// Setup Logout with hydra oauth2
$hashed_secret = hash('sha512',$_SESSION['secret']);
$state=urlencode("token=".$hashed_secret);
$oauth_logout_url = $OAUTH_LOGOUT_ENDPOINT."?client_id=".$OAUTH_CLIENT_ID."&id_token_hint=".$_SESSION['access_token']->id_token."&post_logout_redirect_uri=".urlencode('https://www.metawarrior.army/logout')."&state=".$state;

// Setup shortened address
$front_addr = substr($userObj->address,0,5);
$end_addr = substr($userObj->address,-4);
$nick_addr = $front_addr.'...'.$end_addr;

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
    <link rel="icon" type="image/x-icon" href="/media/img/logo.ico"></link>
    <meta name="theme-color" content="#712cf9">

    
    <link href="/css/profile.css" rel="stylesheet">

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/187f3aa9d6.js" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/jdenticon@3.2.0/dist/jdenticon.min.js"
        integrity="sha384-yBhgDqxM50qJV5JPdayci8wCfooqvhFYbIKhv0hTtLvfeeyJMJCscRfFNKIxt43M"
        crossorigin="anonymous">
    </script>

    <script src="https://discourse.metawarrior.army/javascripts/embed-topics.js"></script>


  </head>
  <body class="d-flex h-100 text-center text-bg-dark">
    
    
<div class="container d-flex w-100 h-100 p-3 mx-auto flex-column">
  
  <header class="mb-auto">
    <div>
      <h3 class="float-md-start"><img src="/media/img/mwa_logo0.png" width="300px" class="img-fluid p-3"></h3>
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark float-md-end">
        <div class="container-fluid">
          <a class="navbar-brand" href="#"><img src="/media/img/icon.png" width="32px"></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link" aria-current="page" href="/">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/sitrep">SITREP</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/roadmap">Roadmap</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link active dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Profile
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-light bg-dark" aria-labelledby="navbarDropdown">

                  <?php 
                    //var_dump($_SESSION['userinfo']);

                    if($userObj->nft_0_tx){
                      include('php/usermenu.php');
                    }
                          
                  ?>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <div class="icon-square d-inline-flex align-items-center justify-content-center fs-8 flex-shrink-0 me-3">
                      <span class="p-1"><i class="p-1 fa fa-sign-out" aria-hidden="true"></i></span>
                      <a class="dropdown-item text-light" href="<?php echo $oauth_logout_url; ?>">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Disabled -->
              <!--
              <li class="nav-item">
                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
              </li>
              -->
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </header>

  <main>

    <div class="container-fluid rounded shadow mt-5">
      <div class="row mb-2">
        
        <div class="col">
          <svg width="80" height="80" data-jdenticon-value="<?php echo $_SESSION['userinfo']->address ?>"></svg>  
          <?php

            if($userObj->username){
              if($userObj->nft_0_tx){
                echo "<br><a href=\"".$BLOCKEXPLORER.$userObj->nft_0_tx."\" target=\"_blank\" class=\"link-info\">NFT Proof of Membership</a>";

              }
            }

          ?>

          <h2 class="designation" id="username"><?php if($userObj->username){echo $userObj->username;}else{echo "";}?></h2>  
          <h6><?php echo($nick_addr); ?></h6>
          <p>:::</p>
          <hr>
        </div>
     
      </div>
      <div class="row mb-3">
        <div class="col" 
          <?php
            if($userObj->username){
              if($userObj->nft_0_tx){
                echo "hidden=\"true\"";   
              }
              else{
              }
            }
            else{
            }
          ?>
        >
          <div class="card text-light bg-dark rounded shadow">
            <h5 class="card-header">Notifications</h5>
            <div class="card-body">
              <?php
                if($userObj->username){
                  if($userObj->nft_0_tx){
                    
                  }
                  else{
                    echo "<br>You still have to <a href=\"https://nft.metawarrior.army/\" class=\"link-light\">Mint</a> your NFT!";
                  }
                }
                else{
                  echo "<br>Welcome to MetaWarrior Army. <br>To become a Member you must choose a username and <b><a href=\"https://nft.metawarrior.army\" class=\"link-info\">Mint your NFT</a></b>.";
                }
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col">
          <div class="card text-light bg-dark rounded shadow" style="width: 13rem;">
            <h5 class="card-header">Your Services</h5>
            <div class="card-body">
              <?php 
                include('php/services.php');
              ?>
            </div>
          </div>
        </div>
        <div class="col-9">
          <div class="card text-light bg-dark rounded shadow mb-2">
            <h5 class="card-header">Email Usage</h5>
            <div class="card-body">
              <?php
                include('php/get_mail_quota.php');
              ?>
            </div>
          </div>



          <div class="card text-light bg-dark rounded shadow">
            <h5 class="card-header">Latest Discourse Posts</h5>
            <div class="card-body">
              <d-topics-list style="width: 50rem;" template="complete" allow-create="true" discourse-url="https://discourse.metawarrior.army/" per-page="5"></d-topics-list>
            </div>
          </div>
        </div>

      </div>
    </div>

      

  </main>

  <footer class="mt-5 text-white-50">
      <?php
        include('php/footer.php');
      ?>
    </footer>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>


    </body>
</html>