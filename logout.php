<?php

session_start();
include './php/mwa.php';

if(isset($_GET['state'])){
  //var_dump($_GET['state']);

  $hashed_secret = hash('sha512',$_SESSION['secret']);
  // find hashed secret
  preg_match('/token=(.*)/', $_GET['state'], $re);
  if(!($hashed_secret == $re[1])){
    // State mismatch
    echo "<p>Session doesn't match</p>";
    header("Location: ".$LOGIN_URL);
  }

  session_destroy();
  header("Location: /");
}
//session_destroy();

?>
