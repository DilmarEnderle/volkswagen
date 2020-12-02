<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_licitacao = intval($_POST["f-id-licitacao"]);

	$db = new Mysql();

	$oOutput = '<div style="overflow: hidden; height: 32px;">
		<a class="sbl"></a>
		<a class="sbr"></a>
	</div>
	<div class="tabs">
		<div class="scroll-container">';

	$db->query("SELECT id, lote FROM gelic_licitacoes_lotes WHERE id_licitacao = $pId_licitacao ORDER BY id");
	while ($db->nextRecord())
	{
		$oOutput .= '<div id="lote-'.$db->f("id").'" class="lot">
				<a class="lot-x" href="javascript:void(0);" onclick="itemRemover('.$pId_licitacao.','.$db->f("id").',0,false);" title="Remover Lote"></a>
				<span class="lot-nome">LOTE: '.utf8_encode($db->f("lote")).'</span>';	
	
		$db->query("SELECT itm.id, itm.item, (SELECT id FROM gelic_licitacoes_apl WHERE id_item = itm.id LIMIT 1) AS apl FROM gelic_licitacoes_itens AS itm WHERE itm.id_licitacao = $pId_licitacao AND itm.id_lote = ".$db->f("id")." ORDER BY itm.id",1);
		while ($db->nextRecord(1))
		{
			if ($db->f("item",1) == "")
				$dItem = 'n/d';
			else
				$dItem = utf8_encode($db->f("item",1));

			if ($db->f("apl",1) > 0)
				$oOutput .= '<div id="item-'.$db->f("id",1).'" class="tab"><span>ITEM: '.$dItem.'</span><a class="itm-x" href="javascript:void(0);" onclick="itemRemover('.$pId_licitacao.','.$db->f("id").','.$db->f("id",1).',false);" title="Remover Item"></a><span class="comapl"></span></div>';
			else
				$oOutput .= '<div id="item-'.$db->f("id",1).'" class="tab"><span>ITEM: '.$dItem.'</span><a class="itm-x" href="javascript:void(0);" onclick="itemRemover('.$pId_licitacao.','.$db->f("id").','.$db->f("id",1).',false);" title="Remover Item"></a></div>';
		}

		$oOutput .= '<div class="tab-add" title="Adicionar Item" onclick="itemAdicionar('.$pId_licitacao.','.$db->f("id").');">+</div></div>';
	}

	$oOutput .= '
			<div class="lot" style="padding: 0;">
				<div class="lot-add" title="Adicionar Lote" onclick="itemAdicionar('.$pId_licitacao.',0);">+</div>
			</div>
		</div>
	</div>';

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oOutput;

	//contar itens
	$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao");
	$db->nextRecord();
	$aReturn[2] = $db->f("total"); //total de itens
}
echo json_encode($aReturn);

?>
