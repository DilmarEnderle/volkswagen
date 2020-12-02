<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pId_licitacao = intval($_POST["id-licitacao"]);

	$db = new Mysql();

	$oOutput = '<div style="overflow: hidden; height: 32px;">
		<a class="sbl"></a>
		<a class="sbr"></a>
	</div>
	<div class="tabs">
		<div class="scroll-container">';

	
	if ($sInside_tipo == 1) //BO
		$add_select = "(SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = itm.id_licitacao AND id_item = itm.id LIMIT 1) AS apl";
	else
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$add_select = "(SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = itm.id_licitacao AND id_item = itm.id AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent)) LIMIT 1) AS apl";
	}

	
	$first_item = 0;

	$db->query("SELECT id, lote FROM gelic_licitacoes_lotes WHERE id_licitacao = $pId_licitacao AND id IN (SELECT id_lote FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_tipo_veiculo > 0 AND acompanhamento = 0) ORDER BY id");
	while ($db->nextRecord())
	{
		$oOutput .= '<div id="lote-'.$db->f("id").'" class="lot">
				<span class="lot-nome">LOTE: '.utf8_encode($db->f("lote")).'</span>';	

		$db->query("
SELECT 
	itm.id, 
    itm.item,
    $add_select
FROM 
	gelic_licitacoes_itens AS itm
WHERE 
	itm.id_licitacao = $pId_licitacao AND 
	itm.id_tipo_veiculo > 0 AND 
	itm.acompanhamento = 0 AND 
    itm.id_lote = ".$db->f("id")." 
ORDER BY 
	itm.id",1);
		while ($db->nextRecord(1))
		{
			$first_item = $db->f("id",1);

			if ($db->f("item",1) == "")
				$dItem = 'n/d';
			else
				$dItem = utf8_encode($db->f("item",1));

			if ($db->f("apl",1) > 0)
				$oOutput .= '<div id="item-'.$db->f("id",1).'" class="tab"><span>ITEM: '.$dItem.'</span><span class="comapl"></span></div>';
			else
				$oOutput .= '<div id="item-'.$db->f("id",1).'" class="tab"><span>ITEM: '.$dItem.'</span></div>';
		}

		$oOutput .= '</div>';
	}

	$oOutput .= '
		</div>
	</div>';

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oOutput;

	//contar itens
	$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_tipo_veiculo > 0 AND acompanhamento = 0");
	$db->nextRecord();
	$aReturn[2] = $db->f("total"); //total de itens

	$aReturn[3] = $first_item;
}
echo json_encode($aReturn);

?>
