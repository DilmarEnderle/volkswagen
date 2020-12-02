<?php

require_once "include/config.php";
require_once "include/essential.php";

$oSubmotivos = '<option value="0"></option>';
if (isInside())
{
	$pId = 0;
	$pTipo = intval($_POST["tipo"]);
	$oSubmotivos = '<option value="0"></option>';

	$db = new Mysql();
	$db->query("SELECT id, descricao FROM gelic_motivos WHERE id <> $pId AND tipo = $pTipo AND id_parent = 0 ORDER BY descricao");
	while ($db->nextRecord())
		$oSubmotivos .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';

	echo $oSubmotivos;
}

?>
