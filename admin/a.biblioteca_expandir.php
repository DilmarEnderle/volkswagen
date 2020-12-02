<?php

require_once "include/config.php";
require_once "include/essential.php";

$dObs = "";
if (isInside())
{
	$pId = intval($_POST["id"]);
	$db = new Mysql();
	$db->query("SELECT observacao FROM gelic_biblioteca WHERE id = $pId");
	$db->nextRecord();
	$dObs = utf8_encode($db->f("observacao"));
}
echo $dObs;

?>
