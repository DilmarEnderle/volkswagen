<?php

require_once "include/config.php";
require_once "include/essential.php";

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

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pId_lote = intval($_POST["f-id-lote"]);
	$pId_item = intval($_POST["f-id-item"]);

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

	//countar numero de itens no lote
	$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_lote = $pId_lote");
	$db->nextRecord();
	if ($db->f("total") == 1) $pId_item = 0;
		
	if ($pId_item == 0)
	{
		//*** remover lote ***

		//verificar se existe alguma APL associada a algum item deste lote
		$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item IN (SELECT id FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_lote = $pId_lote)");
		if ($db->nextRecord())
		{
			$aReturn[0] = 7; //existem APLs
			echo json_encode($aReturn);
			exit;
		}

		//remover
		$db->query("DELETE FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_lote = $pId_lote");
		$db->query("DELETE FROM gelic_licitacoes_lotes WHERE id_licitacao = $pId_licitacao AND id = $pId_lote");

		//total itens da licitacao
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao");
		$db->nextRecord();
		$dTotal_itens = intval($db->f("total"));

		// Se nao encontrar nenhum item valido para os DNs (remove da fila de envio)
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_tipo_veiculo > 0 AND acompanhamento = 0");
		$db->nextRecord();
		if ($db->f("total") == 0)
			$db->query("DELETE FROM gelic_mensagens WHERE id_notificacao = 27 AND ((metodo = 1 AND assunto LIKE '%(LIC $pId_licitacao)%') OR (metodo = 2 AND mensagem LIKE '%(LIC $pId_licitacao)%'))");

		$aReturn[0] = 1; //success
		$aReturn[1] = 1; //lote YES
		$aReturn[2] = $dTotal_itens;
	}
	else
	{
		//*** remover item ***

		//verificar se existe alguma APL associada ao item
		$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = $pId_item");
		if ($db->nextRecord())
		{
			$aReturn[0] = 7; //existem APLs
			echo json_encode($aReturn);
			exit;
		}
			
		//remover
		$db->query("DELETE FROM gelic_licitacoes_itens WHERE id = $pId_item");

		//total itens da licitacao
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao");
		$db->nextRecord();
		$dTotal_itens = intval($db->f("total"));

		// Se nao encontrar nenhum item valido para os DNs (remove da fila de envio)
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_tipo_veiculo > 0 AND acompanhamento = 0");
		$db->nextRecord();
		if ($db->f("total") == 0)
			$db->query("DELETE FROM gelic_mensagens WHERE id_notificacao = 27 AND ((metodo = 1 AND assunto LIKE '%(LIC $pId_licitacao)%') OR (metodo = 2 AND mensagem LIKE '%(LIC $pId_licitacao)%'))");


		$aReturn[0] = 1; //success
		$aReturn[1] = 0; //lote NO
		$aReturn[2] = $dTotal_itens;
	}
} 
echo json_encode($aReturn);

?>
