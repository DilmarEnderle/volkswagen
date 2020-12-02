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


	$id_modelo = 0;
	$id_tipo_veiculo = 0;

	// Verificar se a licitacao tem edital
	$db->query("SELECT id FROM gelic_licitacoes_edital WHERE id_licitacao = $pId_licitacao LIMIT 1");
	if (!$db->nextRecord())
	{
		$id_modelo = 19; //Não disponível
		$id_tipo_veiculo = 9; //Não disponível
	}

	// Verificar status da licitacao
	$db->query("SELECT id_status FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo = 1");
	$db->nextRecord();
	if (in_array($db->f("id_status"), array(10,55)))
	{
		$id_modelo = 19; //Não disponível
		$id_tipo_veiculo = 9; //Não disponível
	}

	if ($pId_lote > 0)
	{
		//inserir item no lote existente
		$db->query("SELECT MAX(id)-MIN(id)+2 AS seq, COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_lote = $pId_lote");
		$db->nextRecord();
		if ($db->f("total") == 0)
			$dSeq = 1;
		else
			$dSeq = $db->f("seq");

		$db->query("INSERT INTO gelic_licitacoes_itens VALUES (NULL, $pId_licitacao, $pId_lote, '$dSeq', 'Volkswagen', $id_modelo, $id_tipo_veiculo, '', 0, 0, '', 1, 0.00)");
		$dId_item = $db->li();

		$aReturn[0] = 1;         //sucesso
		$aReturn[1] = $pId_lote; //id do lote
		$aReturn[2] = $dId_item; //id do item
		$aReturn[3] = 0;         //novo lote NAO
		$aReturn[4] = '<div id="item-'.$dId_item.'" class="tab"><span>ITEM: '.$dSeq.'</span><a class="itm-x" href="javascript:void(0);" onclick="itemRemover('.$pId_licitacao.','.$pId_lote.','.$dId_item.',false);" title="Remover Item"></a></div>';
	}
	else
	{
		//criar novo lote
		$db->query("SELECT MAX(id)-MIN(id)+2 AS seq, COUNT(*) AS total FROM gelic_licitacoes_lotes WHERE id_licitacao = $pId_licitacao");
		$db->nextRecord();
		if ($db->f("total") == 0)
			$dSeq = 1;
		else
			$dSeq = $db->f("seq");

		$db->query("INSERT INTO gelic_licitacoes_lotes VALUES (NULL, $pId_licitacao, '$dSeq')");
		$dId_lote = $db->li();
			
		//inserir item
		$db->query("INSERT INTO gelic_licitacoes_itens VALUES (NULL, $pId_licitacao, $dId_lote, '1', 'Volkswagen', $id_modelo, $id_tipo_veiculo, '', 0, 0, '', 1, 0.00)");
		$dId_item = $db->li();

		$aReturn[0] = 1;         //sucesso
		$aReturn[1] = $dId_lote; //id do lote
		$aReturn[2] = $dId_item; //id do item
		$aReturn[3] = 1;         //novo lote SIM
		$aReturn[4] = '<div id="lote-'.$dId_lote.'" class="lot">
				<a class="lot-x" href="javascript:void(0);" onclick="itemRemover('.$pId_licitacao.','.$dId_lote.',0,false);" title="Remover Lote"></a>
				<span class="lot-nome">LOTE: '.$dSeq.'</span>
				<div id="item-'.$dId_item.'" class="tab"><span>ITEM: 1</span><a class="itm-x" href="javascript:void(0);" onclick="itemRemover('.$pId_licitacao.','.$dId_lote.','.$dId_item.',false);" title="Remover Item"></a></div>
				<div class="tab-add" title="Adicionar Item" onclick="itemAdicionar('.$pId_licitacao.','.$dId_lote.');">+</div>
			</div>';
	}
	
	//total de itens
	$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao");
	$db->nextRecord();
	$aReturn[5] = $db->f("total"); //total itens
}
echo json_encode($aReturn);

?>
