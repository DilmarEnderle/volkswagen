<?php

require_once "include/config.php";
require_once "include/essential.php";

$oSubmotivos = '<option value="0"></option>';
if (isInside())
{
	$pId_motivo = $_POST["f-id-motivo"];
	$oSubmotivos = '';
	$db = new Mysql();
	$db->query("SELECT id, descricao FROM gelic_motivos WHERE id_parent > 0 AND id_parent = $pId_motivo ORDER BY descricao");
	while ($db->nextRecord())
		$oSubmotivos .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';

	if (strlen($oSubmotivos) > 0)
		echo '<option value="0">- escolha um submotivo (opcional) -</option>'.$oSubmotivos;
}

?>
