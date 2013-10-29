#!/usr/bin/php -q
<?php
include('./includes/header.php');

$q = Core::$Sql->prepare("SELECT * FROM `account` WHERE `email` = 'admin@isengard.dk'");
$q->execute();


if(!Core::$Users->isLogged()){
  echo "Hi Guest :)!";
  echo "<a href='login.php'>login her</a>";
}else{
  echo Core::$Users->userInfo()->username." is logged - <a href='logout.php'>logout</a><br/>";
  echo "Your ip: ".Core::$Users->determineIP();
}

?>
