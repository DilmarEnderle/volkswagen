<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_check", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_item = intval($_POST["id-item"]);
	$pCampo = trim($_POST["field"]);
	$pChecked = intval($_POST["checked"]);

	$db = new Mysql();
	$db->query("SELECT tipo FROM gelic_historico WHERE id_licitacao = $pId_licitacao ORDER BY id DESC LIMIT 1");
	if ($db->nextRecord())
	{
		if ($db->f("tipo") == 31)
		{
			$aReturn[0] = 8; //encerrada
			echo json_encode($aReturn);
			exit;
		}
	}

	if ($pChecked == 0)
		$checked = 1;
	else
		$checked = 0;


	//mark as checked/unchecked
	$db->query("SELECT id FROM gelic_licitacoes_itens_check WHERE id_item = $pId_item");
	if (!$db->nextRecord())
		$db->query("INSERT INTO gelic_licitacoes_itens_check VALUES (NULL, $pId_item, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");

	$db->query("UPDATE gelic_licitacoes_itens_check SET $pCampo = $checked WHERE id_item = $pId_item");


	$aReturn[0] = 1; //sucesso
	$aReturn[1] = 'itm-cb'.$checked;
} 
echo json_encode($aReturn);
?>
