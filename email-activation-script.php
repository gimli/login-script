<?php
include("./includes/header.php");

$email = $Core->get['email'];
$hash = $Core->get['hash'];

$d = Core::$Sql->prepare("SELECT * FROM `email_activations` WHERE `email` = '$email' AND `hash` = ".$hash);
$d->execute();
if($d->rowCount() < 0){
   $d = Core::$Sql->prepare("UPDATE `account` SET `active` = '1' WHERE `email` = '$email'");
   $d->execute();
   if($d->rowCount() < 0){
      $q = Core::$Sql->prepare("DELETE FROM `email_activations` WHERE `email` = '$email' AND `hash` = ".$hash);
      $q->execute();
      if(!$q->rowCount()){ echo ERROR_UNABLE_TO_DELETE_EMAIL_ACTIVATION; }
      $Core->redirect("index.php?EmailActivation=Success");
   }
}
include("./includes/footer.php");
?>
