
          

<?php
  // EMAIL CHECK
  if($_SESSION['userinfo']->email_active){
    echo '<li>                  <div class="icon-square d-inline-flex align-items-center justify-content-center fs-8 flex-shrink-0 me-3">
    ✉️
    <a class="dropdown-item text-light" href="https://mail.metawarrior.army">Email</a><div class="icon-square d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0 me-3">
    </li>';
  }
  else{
    echo '<li><a class="dropdown-item disabled text-light" href="#">Email</a></li>';
  }
?>

<?php
  // Social Check
  if($_SESSION['userinfo']->email_active){
    echo '<li><div class="icon-square d-inline-flex align-items-center justify-content-center fs-8 flex-shrink-0 me-3">
    📢
  <a class="dropdown-item text-light" href="https://mastodon.metawarrior.army">Social</a></div></li>';
  }
  else{
    echo '<li><a class="dropdown-item disabled text-light" href="#">Social</a></li>';
  }
?>

<?php
  // Chat check
  if($_SESSION['userinfo']->email_active){
    echo '<li><div class="icon-square d-inline-flex align-items-center justify-content-center fs-8 flex-shrink-0 me-3">
    💬
    <a class="dropdown-item text-light" href="https://matrix.metawarrior.army">Chat</a></div></li>';
  }
  else{
    echo '<li><a class="dropdown-item disabled text-light" href="#">Chat</a></li>';
  }
?>

<?php
  // Discourse Check
  if($_SESSION['userinfo']->email_active){
    echo '<li><div class="icon-square d-inline-flex align-items-center justify-content-center fs-8 flex-shrink-0 me-3">
    📝
    <a class="dropdown-item text-light" href="https://discourse.metawarrior.army">Discourse</a></div></li>';
  }
  else{
    echo '<li><a class="dropdown-item disabled text-light" href="#">Discourse</a></li>';
  }
?>

            

