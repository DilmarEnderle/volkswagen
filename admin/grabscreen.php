<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
	$sInside_id = $_SESSION[SESSION_ID];
else
	$sInside_id = 0;

$width = $_POST["w"];
$height = $_POST["h"];
$now = date("Y-m-d H:i:s");
logThis($now.' ['.$sInside_id.'] '.$width.'x'.$height);

?>
