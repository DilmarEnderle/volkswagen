<?php
require_once "include/config.php";
session_start();
if (isset($_SESSION[SESSION_ID])) unset($_SESSION[SESSION_ID]);
if (isset($_SESSION[SESSION_NAME])) unset($_SESSION[SESSION_NAME]);
setcookie(COOKIE_NAME, "", time()-3600);
header("location: index.php");
?>
