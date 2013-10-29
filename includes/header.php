<?php
define('IN_SCRIPT',true);

if(!defined("STDIN")){
   define("STDIN", fopen('php://stdin','r'));
}
   error_reporting(E_ALL); 
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
include("includes/class.core.php");

$Core = new Core();
?>
