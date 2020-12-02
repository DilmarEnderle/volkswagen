<?php

require_once "include/config.php";
require_once "include/essential.php";
require_once "a.licitacao_ntfnova.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_editar", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_item = intval($_POST["id-item"]);
	$pValor = intval($_POST["valor"]);
	$pCheck = intval($_POST["chk"]);

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

	if ($pCheck == 1)
	{
		$db->query("UPDATE gelic_licitacoes_itens SET transformacao = $pValor WHERE id = $pId_item AND id_licitacao = $pId_licitacao");
	}
	else
	{
		$db->query("UPDATE gelic_licitacoes_itens SET acompanhamento = $pValor WHERE id = $pId_item AND id_licitacao = $pId_licitacao");
		ntfNovaLicitacao($pId_licitacao);
	}

	if ($db->Errno[0] == 0)
		$aReturn[0] = 1; //sucesso
} 
echo json_encode($aReturn);
?>
