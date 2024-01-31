<?php

# Start server session
session_start();
include './php/mwa.php';


# Generate secret for individual session (Server Side Security)
$secret = generateNonce(64);
$hashed_secret = hash('sha512',$secret);
# Store secret in session
$_SESSION['secret']=$secret;

# Generate nonce for OAuth call (Provider Side Security)
$nonce = generateNonce(64);

// SETUP LOGIN TO Ory Hydra

# Set OAuth Parameters
$response_type="code";
$client_id=$OAUTH_CLIENT_ID;
$scope = "openid profile";

$redirect_url=$OAUTH_REDIRECT_URL; // Redirect URI is preconfigured with the provider. In this example we use login.php
$state=urlencode("token=".$hashed_secret);

// Complete Google OAuth URL
# This is the URL we send the user to for signing-in/signing-up
$oauth_url = $OAUTH_AUTH_ENDPOINT."?client_id=".$client_id."&response_type=".$response_type."&redirect_uri=".$redirect_url."&scope=".$scope."&state=".$state;

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
    <title>MetaWarrior Army - SignUp</title>

    <link rel="canonical" href="https://www.metawarrior.army/signup.php">

    

    <!-- Favicons -->
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
  <header class="mb-auto mb-3">
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
                <a class="nav-link active" aria-current="page" href="/signup">Signup</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/sitrep">SITREP</a>
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

  <main class="px-0 mt-5 mb-5">
    <div class="container-fluid w-50 rounded shadow">
              <h1 class="mt-3">Become a Member</h1>
              <!-- <p class="lead">Use your crypto wallet to login and sign up for MetaWarrior Army (MWA).</p> -->

              <div class="text-center">
              <!-- <img class="d-block mx-auto mb-4" src="/docs/5.3/assets/brand/bootstrap-logo.svg" alt="" width="72" height="57">
              <h1 class="display-5 fw-bold text-body-emphasis">Centered hero</h1> -->
              <div class="col-lg-6 mx-auto">
                <p class="lead">Control your digital destiny in the MetaWarrior Army.</p>

                <button type="button" class="btn btn-outline-light btn-lg px-4 mb-3" onclick="location.href='<?php echo $oauth_url; ?>'">Signup</button>
                
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                  <p class="small">Use your Web3 wallet to sign up and become a member at MetaWarrior Army.</p>
                </div>
              </div>
            </div>
            <hr>

          <div class="container mt-5">
              <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
                <div class="col-10 col-sm-8 col-lg-6">
                  <p class="lead">Gear up!</p>
                  <p class="small">If you're familiar with crypto and blockchain technology but need a wallet, MWA recommends <b>MetaMask</b>.</p>
                </div>
                <div class="col-lg-6">
                  <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="window.open('https://metamask.io');">Get MetaMask</button>
                  <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <!-- <button type="button" class="btn btn-primary btn-lg px-4 me-md-2">Primary</button> -->
                  </div>
                </div>
              </div>
            </div>

            <div class="container px-0 py-0">
              <div class="row flex-lg-row-reverse align-items-center g-5 py-0">
                <div class="col-10 col-sm-8 col-lg-6">
                  <p class="lead">Discover a New World.</p>
                  <p class="small">If you're unfamiliar with Web3, what it is or how you use it, don't worry!</p>
                </div>
                <div class="col-lg-6">
                  <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="window.open('https://learn.metamask.io/');">Learn More</button>
                
                  <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    
                  </div>
                </div>
              </div>
            </div>

  
    </div>
  </main>

  <footer class="mt-5 text-white-50">
    <!-- footer text example -->
    
    <?php
      include('php/footer.php');
    ?>
    
  </footer>
  
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

    </body>
</html>