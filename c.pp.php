<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo <> 2)
		exit;

	$gChecked = intval($_GET["checked"]);

	$db = new Mysql();
	$db->query("SELECT id FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'popup'");
	if ($gChecked && !$db->nextRecord())
		$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $sInside_id, 'popup', 1)");
	else if (!$gChecked)
		$db->query("DELETE FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'popup'");
}

?>
