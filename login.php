<?php
include("./includes/header.php");

if(!Core::$Users->isLogged()){
  require_once("./html/login_html.php");
}else{
  echo ALREADY_LOGGED;
}
?>
