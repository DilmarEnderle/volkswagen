<?php

require_once "include/config.php";
require_once "include/essential.php";

$tPage = new Template("a.a.html");
$tPage->replace("{{VERSION}}", time());
echo $tPage->body;

?>
