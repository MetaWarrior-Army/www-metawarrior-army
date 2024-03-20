<?php

include './php/mwa.php';

// Start server session
session_start();

// This will validate our session or redirect our user to login
// This page depends on an active session. We prefer the access_token for getting the /userinfo from the OAuth server

if(!isset($_SESSION['access_token']) or isset($_SESSION['access_token']->error)){
  // we don't have an access_token
  // do we have what we need to get an access_token?
  if(isset($_GET['code']) && isset($_GET['scope']) && isset($_GET['state']) && isset($_SESSION['secret'])){
    // This user is already logged in. We should redirect them to the profile page.
    header("Location: ".$PROFILE_URL);    
  }
  else{
    // No authorization_code or access_token
    // This is okay, we can display the homepage
  }
}
else{
  // User has an access token, redirect them to their profile page
  header("Location: ".$PROFILE_URL);    
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

    <link rel="canonical" href="https://www.metawarrior.army/index.php">

    <!-- favicon -->
    <link rel="icon" type="image/x-icon" href="/media/img/logo.ico"></link>

    <meta name="theme-color" content="#712cf9">
  </head>


  <body class="d-flex h-100 text-center text-bg-dark">
    
    <div class="container d-flex w-100 h-100 p-3 mx-auto flex-column">
    
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
                    <a class="nav-link active" aria-current="page" href="/">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/mission">Mission</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="/about">About Us</a>
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

      <main class="px-3 mb-5">
      
        <div class="container mt-5 mb-5">
          <div class="row p-4 pb-0 pe-lg-0 pt-lg-5 align-items-center rounded-3 shadow-lg">
            <div class=" col-lg-7 p-3 p-lg-5 pt-lg-3">
              <h1 class="display-1 mb-5">⚔️</h1>
              <h1 class="display-4 fw-bold lh-1 mb-5">An Army Standing for Sovereignty and Autonomy on the Digital Frontier</h1>
              <p class="lead mt-5 mb-5">We believe freedom online is possible and are fostering a community of individuals dedicated to pursuing this freedom for everyone.</p>
              <div class="d-grid gap-2 mt-5 d-md-flex justify-content-md-center mb-4 mb-lg-3">
                <a href="/mission" class="btn btn-lg btn-outline-info fw-bold">Read the Mission</a>
                <a href="/sitrep" class="btn btn-lg btn-outline-warning fw-bold">Get the SITREP</a>
                
                
              </div>
            </div>
            <div class="col-lg-4 offset-lg-1 p-0 overflow-hidden ">
                <img class="rounded-lg-3" src="/media/img/laptop_boots.png" alt="" width="400">
            </div>
          </div>
        </div>

        <div class="b-example-divider"></div>

        <div class="px-4 pt-5 mb-5 text-center">
          <h1 class="display-4 fw-bold">A User Owned and Governed Social Platform</h1>
          <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">A social platform by the people, for the people; providing an alternative to privately owned, centrally governed solutions where users have no rights. .</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mb-5">
              <a href="/about" type="button" class="btn btn-outline-warning btn-lg px-4 me-sm-3 fw-bold">About Us</a>
              <!--
              <button type="button" class="btn btn-outline-secondary btn-lg px-4">Secondary</button>
              -->
            </div>
          </div>
          <div class="overflow-hidden" style="max-height: 30vh;">
            <div class="container px-5">
              <img src="/media/img/mission_banner.png" class="img-fluid border rounded-3 shadow-lg mb-4" alt="Example image" width="1080" loading="lazy">
            </div>
          </div>
        </div>

        <div class="b-example-divider"></div>

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