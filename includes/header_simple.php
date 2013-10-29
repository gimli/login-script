<?php
define('IN_SCRIPT',true);

if(!defined("STDIN")){
   define("STDIN", fopen('php://stdin','r'));
}

include("./includes/class.core.php");

$Core = new Core();
?>
