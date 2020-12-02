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
	$pEvento = intval($_POST["evento"]);
	$pData = trim($_POST["data"]);

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

	if (isValidBrDate($pData))
	{
		$data = brToMysql($pData);
		$db->query("SELECT id FROM gelic_licitacoes_itens_eventos WHERE id_item = $pId_item AND evento = $pEvento AND data_evento = '$data'");
		if ($db->nextRecord())
		{
			$aReturn[0] = 6; //ja existe
			echo json_encode($aReturn);
			exit;
		}
		else
		{
			$db->query("INSERT INTO gelic_licitacoes_itens_eventos VALUES (NULL, $pId_item, $pEvento, '$data')");
			$aReturn[0] = 1; //sucesso
		}
	}
	else
	{
		$aReturn[0] = 7; //data invalida
		echo json_encode($aReturn);
		exit;
	}
} 
echo json_encode($aReturn);
?>
