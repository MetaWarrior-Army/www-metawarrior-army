<?php

# Start server session
session_start();
include './php/mwa.php';

$subscribed = false;

if($_POST['check']){
  if($_POST['email']){

    // Get the name
    $name_re = '/^(.*)@.*$/';
    preg_match($name_re, $_POST['email'], $matches);
    $name = $matches[1];


    // We have an email address, let's POST to listmonk ;)
    $listmonk_uri = $LISTMONK_API_URL."/api/subscribers";
    //var_dump($listmonk_uri);
    $data = [
      'email' => $_POST['email'],
      'name' => $name,
      'status' => 'enabled',
      'lists' => [$SUB_LIST],
      'attribs' => ['source' => 'subscribe.php']
    ];
    //var_dump($data);
    
    $username = 'mwa';
    $password = $LISTMONK_ADMIN_PASSWORD;

    $headers = array('Authorization: Basic '.base64_encode($username.':'.$password), 'Content-Type: application/json');

    $ch = curl_init($listmonk_uri);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $ret = json_decode(curl_exec($ch));

    if($ret->data->id){
      $subscribed = true;
    }
    else{
      $subscribed = false;
    }

    curl_close($ch);
  }
}

// Below is the HTML the user will interact with.
?>
<!doctype html>
<html lang="en">
  <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="admin">
    <title>Subscribe to MetaWarrior Army</title>

    <link rel="canonical" href="https://www.metawarrior.army/subscribe.php">

    

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
  <header class="mb-auto mb-3">
    <div>
      <h3 class="float-md-start text-info">MetaWarrior Army</h3>
      <nav class="nav nav-masthead justify-content-center float-md-end">
        <a class="nav-link fw-bold py-1 px-0" aria-current="page" href="/">Home</a>
		<a class="nav-link fw-bold py-1 px-0 active text-info" href="/signup">Subscribe</a>
        <!--
        <a class="nav-link fw-bold py-1 px-0" href="#">Features</a>
        <a class="nav-link fw-bold py-1 px-0" href="#">Contact</a>
        -->
      </nav>
    </div>
  </header>

  <main class="px-3 mt-5">
    <h1>Get the SITREP</h1>
    <p class="lead">Sign up for updates and news on everything MetaWarrior Army.</p>
    <!-- <p class="lead">Use your crypto wallet to login and sign up for MetaWarrior Army (MWA).</p> -->
    <hr>

    <?php
      if(!$subscribed){
        include('php/subscribe_form.php');
      }
      else{
        echo "<h4>Thanks for subscribing!</h4>";
      }


    ?>



    

  <footer class="mt-5 text-white-50">
    <!-- footer text example -->
    
    <?php
      include('php/footer.php');
    ?>
    
  </footer>

  </main>

  
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<script src="/js/subscribe.js"></script>

    </body>
</html>