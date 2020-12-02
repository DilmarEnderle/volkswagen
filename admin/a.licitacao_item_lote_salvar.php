<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$db = new Mysql();

	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_lote = intval($_POST["id-lote"]);
	$pLote = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["lote"])))));

	$db->query("UPDATE gelic_licitacoes_lotes SET lote = '$pLote' WHERE id = $pId_lote AND id_licitacao = $pId_licitacao");
	$db->query("SELECT lote FROM gelic_licitacoes_lotes WHERE id = $pId_lote AND id_licitacao = $pId_licitacao");
	$db->nextRecord();

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = utf8_encode($db->f("lote"));
} 
echo json_encode($aReturn);

?>
