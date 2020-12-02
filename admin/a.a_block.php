<?php

require_once "include/config.php";
require_once "include/essential.php";

$return = array();
$block_start = $_GET["block_start"];
$block_limit = $_GET["block_limit"];
$return[0] = $block_start;
$return[1] = 0;
$return[2] = [];

$db = new Mysql();
$db->query("SELECT id FROM gelic_historico WHERE id >= $block_start AND arquivo <> '' ORDER BY id LIMIT $block_limit");
while ($db->nextRecord())
	array_push($return[2], $db->f("id"));

if (count($return[2]) > 0)
	$return[1] = $return[2][count($return[2])-1];
else
	$return[1] = $block_start;

echo json_encode($return);

?>
