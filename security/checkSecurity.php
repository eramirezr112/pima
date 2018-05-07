<?php 

  session_start();
  if (!isset($_SESSION["login_token"])) {
    header("Location: ../");
  }

?>