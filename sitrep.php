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
      'attribs' => ['source' => 'www.metawarrior.army/sitrep.php', 'version' => 0.1 ]
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

    $subscribed = true;

    curl_close($ch);
  }
}

// Below is the HTML the user will interact with.
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

    <link rel="canonical" href="https://www.metawarrior.army/index.php">

    <!-- favicon -->
    <link rel="icon" type="image/x-icon" href="/media/img/logo.ico"></link>

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
    

    
<div class="container-fluid d-flex w-100 h-100 p-3 mx-auto flex-column">
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
                <a class="nav-link active" href="/sitrep">SITREP</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" aria-current="page" href="/roadmap">Roadmap</a>
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

  <main class="px-3">


    <div class="container  my-5">
      <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 align-items-center rounded-3 shadow-lg">
        <div class=" col-lg-7 p-3 p-lg-5 pt-lg-3">
          <h3 class="display-7 fw-bold lh-1">Get the SITREP</h3>
          <p class="lead">The official MetaWarrior Army Newsletter.</p>
          <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-4 mb-lg-3">
            <?php
              if(!$subscribed){
                include('php/subscribe_form.php');
              }
              else{
                echo "<p class=\"small\">Received!</p>"; 
                echo "<h6 class=\"small text-info\">Check your email for a letter to confirm your subscription.</h6>";
              }
            ?>



            
          </div>
        </div>
        <div class="col-lg-4 offset-lg-1 p-0 overflow-hidden ">
          <img class="rounded-lg-3" src="/media/img/radio0.png" alt="" width="500">
        </div>
      </div>
    </div>
  


  </main>

  <footer class="mt-auto text-white-50">
    <!-- footer text example -->
    <?php
      include('php/footer.php');
    ?>
  </footer>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<script src="/js/subscribe.js"></script>

    </body>
</html>