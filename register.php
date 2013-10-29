<?php
include("./includes/header.php");

if(!Core::$Users->isLogged()){
  require_once("./html/register_html.php");
}else{
  echo ALREADY_LOGGED;
}
?>
