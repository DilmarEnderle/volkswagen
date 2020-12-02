<?php

require_once "include/config.php";
require_once "include/essential.php";

$oCell = '';
if (isInside())
{
	$gId_lote = intval($_GET["id-lote"]);
	$gId_item = intval($_GET["id-item"]);
	$gCampo = trim($_GET["campo"]);

	$db = new Mysql();
	$db->query("SELECT $gCampo AS campo FROM gelic_licitacoes_itens WHERE id = $gId_item AND id_lote = $gId_lote");
	if ($db->nextRecord())
	{
		if ($gCampo == "quantidade" && $db->f("campo") == 0)
			$oCell = '1';
		else if ($gCampo == "valor")
		{
			if ($db->f("campo") > 0)
				$oCell = "R$ ".number_format($db->f("campo"),2,",",".");
		}
		else
		{
			$oCell = utf8_encode($db->f("campo"));
		}
	}
}
echo $oCell;

?>
