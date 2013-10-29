<?php
include("./includes/header_simple.php");

$email = $_POST['email'];
$password = $_POST['password'];
$check = $_POST['check'];

// we still need to add str length check max 30 characters
// and add error messages in login.php & mysql_real_qoute($str)
// our strings.
$Core::$Users->userLogin($email, $password, $check);

?>
