<?php

# Start server session
session_start();
include './php/mwa.php';


if(isset($_SESSION['secret']) && isset($_SESSION['access_token']->id_token)){
  // Setup Logout with hydra oauth2
  $hashed_secret = hash('sha512',$_SESSION['secret']);
  $state=urlencode("token=".$hashed_secret);
  $oauth_logout_url = $OAUTH_LOGOUT_ENDPOINT."?client_id=".$OAUTH_CLIENT_ID."&id_token_hint=".$_SESSION['access_token']->id_token."&post_logout_redirect_uri=".urlencode('https://www.metawarrior.army/logout')."&state=".$state;
}
else{
  header("Location: /login");
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

    <link rel="canonical" href="https://www.metawarrior.army/login.php">

    

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

    
  </head>
  <body class="d-flex h-100 text-center text-bg-dark">
    
<div class="container-fluid d-flex w-100 h-100 p-3 mx-auto flex-column">
  <header class="mb-auto">
    <div>
      <h3 class="float-md-start"><a href="/"><img src="/media/img/mwa_logo0.png" width="300px" class="img-fluid p-3"></a></h3>
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
                <a class="nav-link" aria-current="page" href="/mission">Mission</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" aria-current="page" href="/about">About Us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="/login">Login</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/sitrep">SITREP</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/roadmap">Roadmap</a>
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
    <div class="container-fluid w-75 rounded shadow">
      <h1 class="display-1">🧾</h1>
      <h1 class="mt-3 display-4 fw-bold mb-5">Join MetaWarrior Army</h1>
      <p class="lead">To complete your membership, you'll need to mint an NFT. If you've already minted your NFT, try logging out and logging back in.</p>
      <?php
        if(isset($_GET['username'])){
          echo "<p class=\"lead\">Your username <span class=\"fw-bold text-info\">".$_GET['username']."</span> has been secured for now.";
        }
      ?>
      <p>
        <a href="https://nft.metawarrior.army" class="btn btn-lg btn-outline-info fw-bold mb-3 m-3">Mint NFT</a>
        <a href="<?php echo $oauth_logout_url; ?>" class="btn btn-lg btn-outline-warning fw-bold mb-3 m-3">Logout</a>
      </p>
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

    </body>
</html>