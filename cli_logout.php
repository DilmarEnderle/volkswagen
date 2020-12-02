<?php
require_once "include/config.php";
session_start();
if (isset($_SESSION[SESSION_ID])) unset($_SESSION[SESSION_ID]);
if (isset($_SESSION[SESSION_TYPE])) unset($_SESSION[SESSION_TYPE]);
if (isset($_SESSION[SESSION_PARENT])) unset($_SESSION[SESSION_PARENT]);
if (isset($_SESSION[SESSION_NAME])) unset($_SESSION[SESSION_NAME]);
if (isset($_SESSION[SESSION_ID_DN])) unset($_SESSION[SESSION_ID_DN]);
setcookie(COOKIE_NAME, "", time()-3600);
header("location: ../");
?>
