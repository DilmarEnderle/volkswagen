<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$db = new Mysql();
	$db->query("UPDATE gelic_clientes SET online = ".time()." WHERE id = ".$_SESSION[SESSION_ID]);
}
else
{
	echo "o";
}

?>
