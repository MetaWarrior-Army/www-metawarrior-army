<?php

session_start();
#include './php/mwa.php';

session_destroy();
header("Location: /");


?>
