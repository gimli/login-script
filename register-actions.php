<?php
include("./includes/header_simple.php");

$email = $_POST['email'];
$emailConfirm = $_POST['emailConfirm'];

$username = $_POST['username'];

$password = $_POST['password'];
$passwordConfirm = $_POST['passwordConfirm'];

if($email != $emailConfirm){ $Core->redirect("register.php?e=email_dont_match"); }
if($password != $passwordConfirm){ $Core->redirect("register.php?e=password_dont_match"); } 

Core::$Users->userRegister($email, $username, $password);


?>
