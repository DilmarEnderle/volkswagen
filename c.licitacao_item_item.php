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
	$pId_lote = intval($_POST["id-lote"]);
	$pId_item = intval($_POST["id-item"]);

	$db = new Mysql();

	$aBr = array("b","r");

	$aTipo_veiculos = array();
	$aTipo_veiculos[0] = array('&lt;não informado&gt;','');
	$aTipo_veiculos[1] = array('Hatch Popular','<img src="img/car-hatch-{{BR}}.png" style="float:right;border:0;margin-top:7px;">');
	$aTipo_veiculos[2] = array('Hatch Premium','<img src="img/car-hatch-{{BR}}.png" style="float:right;border:0;margin-top:7px;">');
	$aTipo_veiculos[3] = array('Sedan Popular','<img src="img/car-sedan-{{BR}}.png" style="float:right;border:0;margin-top:8px;">');
	$aTipo_veiculos[4] = array('Sedan Premium','<img src="img/car-sedan-{{BR}}.png" style="float:right;border:0;margin-top:8px;">');
	$aTipo_veiculos[5] = array('Pick-up Popular','<img src="img/car-pickup-{{BR}}.png" style="float:right;border:0;margin-top:8px;">');
	$aTipo_veiculos[6] = array('Pick-up Premium','<img src="img/car-pickup-{{BR}}.png" style="float:right;border:0;margin-top:8px;">');
	$aTipo_veiculos[7] = array('Station Wagon','<img src="img/car-station-{{BR}}.png" style="float:right;border:0;margin-top:8px;">');
	$aTipo_veiculos[8] = array('Não pertinente','');
	$aTipo_veiculos[9] = array('Não disponível','');

	$aModelos = array();
	$aModelos[0] = '&lt;não informado&gt;';
	$aModelos[1] = 'Up! 1.0';
	$aModelos[2] = 'Gol 1.0';
	$aModelos[3] = 'Gol 1.6';
	$aModelos[4] = 'Fox 1.0';
	$aModelos[5] = 'Fox 1.6';
	$aModelos[6] = 'Golf 1.0';
	$aModelos[7] = 'Golf 1.4';
	$aModelos[8] = 'Golf 1.6';
	$aModelos[9] = 'Golf 2.0';
	$aModelos[10] = 'Voyage 1.0';
	$aModelos[11] = 'Voyage 1.6';
	$aModelos[12] = 'Jetta 1.4';
	$aModelos[13] = 'Jetta 2.0';
	$aModelos[14] = 'Saveiro 1.6';
	$aModelos[15] = 'Amarok 2.0';
	$aModelos[16] = 'CrossFox 1.6';
	$aModelos[17] = 'SpaceFox 1.6';
	$aModelos[18] = 'Incompatível';
	$aModelos[19] = 'Não disponível';

	//carrega item
	$db->query("
SELECT
	itm.item,
	itm.marca,
	itm.id_modelo,
	itm.id_tipo_veiculo,
	itm.modelo,
	itm.descricao,
	itm.quantidade,
	itm.valor,
	itm.transformacao
FROM
	gelic_licitacoes_itens AS itm
WHERE
	itm.id = $pId_item AND
	itm.id_licitacao = $pId_licitacao AND
	itm.id_lote = ".$pId_lote);
	if ($db->nextRecord())
	{
		$dItem = utf8_encode($db->f("item"));
		$dMarca = utf8_encode($db->f("marca"));
		$dId_modelo = intval($db->f("id_modelo"));
		$dId_tipo_veiculo = intval($db->f("id_tipo_veiculo"));
		$dDescricao = utf8_encode(nl2br($db->f("descricao")));
		$dQuantidade = intval($db->f("quantidade"));
		if ($dQuantidade == 0)
			$dQuantidade = 1;
		$dTransformacao = intval($db->f("transformacao"));

		if ($dItem == "") 
			$dItem = '<span id="cell-1" class="item-vl gray-88 cell italic">&lt;não informado&gt;</span>';
		else
			$dItem = '<span id="cell-1" class="item-vl cell">'.$dItem.'</span>';

		if ($dMarca == "")
			$dMarca = '<span id="cell-2" class="item-vl gray-88 cell italic">&lt;não informado&gt;</span>';
		else
			$dMarca = '<span id="cell-2" class="item-vl cell">'.$dMarca.'</span>';

		if ($dId_modelo == 0)
			$dModelo = '<span id="cell-3" class="item-vl cell gray-88 italic">'.$aModelos[$dId_modelo].'</span>';
		else
			$dModelo = '<span id="cell-3" class="item-vl cell">'.$aModelos[$dId_modelo].'</span>';

		//modelo antigo
		$dModelo_antigo = '';
		if ($dId_modelo == 18 && $db->f("modelo") <> '')
			$dModelo_antigo = '<span id="modelo-antigo" class="item-vl" style="margin-left: 160px; border: 0; line-height: 21px; padding-bottom: 10px; font-style: italic; color: #993333;">'.utf8_encode($db->f("modelo")).'</span>';

		if ($dDescricao == "")
			$dDescricao = '<span id="cell-4" class="item-vl-desc gray-88 cell italic">&lt;não informado&gt;</span>';
		else
			$dDescricao = '<span id="cell-4" class="item-vl-desc cell">'.$dDescricao.'</span>';

		if ($db->f("valor") == 0)
			$dValor = '<span id="cell-6" class="item-vl gray-88 cell italic">&lt;não informado&gt;</span>';
		else
			$dValor = '<span id="cell-6" class="item-vl cell">R$ '.number_format($db->f("valor"),2,",",".").'</span>';

		if ($dId_tipo_veiculo == 0)
			$dTipo_veiculo = '<span id="cell-7" class="item-vl cell gray-88 italic">'.$aTipo_veiculos[$dId_tipo_veiculo][0].'</span>';
		else
			$dTipo_veiculo = '<span id="cell-7" class="item-vl cell">'.$aTipo_veiculos[$dId_tipo_veiculo][0].str_replace("{{BR}}", $aBr[$dTransformacao], $aTipo_veiculos[$dId_tipo_veiculo][1]).'</span>';

			
		$dTotal = number_format($dQuantidade * $db->f("valor"), 2, ",", ".");


		// ************************************************
		// ***************** APL(s) ***********************
		// ************************************************

		$aAPL_B_style = array();
		$aAPL_B_style[1] = 'Aguardando aprovação'; //preenchida pelo cliente (preto)
		$aAPL_B_style[2] = 'Aprovada';             //aprovada (verde)
		$aAPL_B_style[4] = 'Reprovada';            //reprovada (vermelho)
		$aAPL_B_style[5] = 'Aguardando aprovação'; //preenchida aprovacao revertida (preto)
		$aAPL_B_style[6] = 'Aguardando aprovação'; //preenchida reprovacao revertida (preto)

		$dns = '';
		$apls = '';
		
		if ($sInside_tipo == 1) //BO
		{
			$db->query("
SELECT 
	DISTINCT(IF (cli.id_parent > 0, cli.id_parent, cli.id)) AS id_parent
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_cliente = cli.id AND apl.id_licitacao = $pId_licitacao AND apl.id_item = $pId_item");
			while ($db->nextRecord())
			{
				$dId_parent = $db->f("id_parent");
				$db->query("
SELECT
	cli.nome,
	apl.id_cliente,
	(SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_licitacoes_apl AS apl ON
    apl.id = (
		SELECT 
			MAX(id) 
		FROM 
			gelic_licitacoes_apl
		WHERE
			id_licitacao = $pId_licitacao AND
            id_item = $pId_item AND
			(id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))
		)
WHERE
	cli.id = $dId_parent",1);
				while ($db->nextRecord(1))
				{
					$dStatus_apl = $db->f("tipo",1);
					$dNome_cliente = utf8_encode($db->f("nome",1));

					if ($db->Row[0] % 2 == 1)
						$c = 'f1f1f1';
					else
						$c = 'e8e8e8';

					$dns .= '<div class="apl-dn-row" style="background-color:#'.$c.'">
						<span class="bold" style="margin-left:30px;">'.$dNome_cliente.'</span>
						<a id="apl-btn-'.$db->f("id_cliente",1).'" class="bt-apl-style-'.$dStatus_apl.' fr" href="javascript:void(0);" onclick="itemAPL_DN('.$pId_item.','.$db->f("id_cliente",1).');" style="height:24px;line-height:24px;font-size:13px;margin-top:2px;margin-right:30px;" title="'.$aAPL_B_style[$dStatus_apl].'">APL</a>
					</div>
					<div id="mul-'.$pId_item.'-'.$db->f("id_cliente",1).'" class="mul clear fl"></div>';
				}
			}			

			if ($dns != '')
				$apls = '<div class="fl clear" style="text-align: center; width: 100%; line-height: 30px; border-bottom: 1px solid #bbbbbb; margin-top: 20px; font-size: 18px;">APL(s)</div>'.$dns.'<div class="fl clear" style="height:1px; width: 100%; border-top: 1px solid #bbbbbb;"></div>';
		}
		else
		{
			if ($sInside_tipo == 2)
				$cliente_parent = $sInside_id;
			else if ($sInside_tipo == 3)
				$cliente_parent = $sInside_parent;
			else if ($sInside_tipo == 4)
				$cliente_parent = $_SESSION[SESSION_ID_DN];

			//verificar se este cliente ja enviou a APL para este item
			$db->query("
SELECT 
    cli.nome AS nome_cliente,
    cli.id_parent,
	cli.id AS id_cliente,
	clidn.nome AS nome_dn,
    (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM 
	gelic_licitacoes_apl AS apl
    INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
    LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE 
	apl.id = (
		SELECT 
			MAX(id) 
		FROM 
			gelic_licitacoes_apl 
		WHERE 
			id_licitacao = $pId_licitacao AND 
			id_item = $pId_item AND 
			(id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent)))");
			if ($db->nextRecord())
			{
				// APL encontrada
				$dStatus_apl = $db->f("tipo");
				$apls = '<div class="fl clear" style="text-align: center; width: 100%; line-height: 30px; margin-top: 20px; font-size: 18px;">
					<a id="apl-btn" class="bt-apl-style-'.$dStatus_apl.'" href="javascript:void(0);" onclick="itemAPL('.$pId_item.');" style="height:34px;line-height:34px;font-size:13px;margin-bottom:4px;width:100px;" title="'.$aAPL_B_style[$dStatus_apl].'">APL &#x21e3;</a>
				</div>
				<div id="mul-'.$pId_item.'" class="mul clear fl"></div>';
			}
			else
			{
				// Sem APL
				$apls = '<div class="fl clear" style="text-align: center; width: 100%; line-height: 30px; margin-top: 20px; font-size: 18px;">
					<a id="apl-btn" class="bt-style-2" href="javascript:void(0);" onclick="itemAPL('.$pId_item.');" style="height:34px;line-height:34px;font-size:13px;margin-bottom:4px;width:200px;" title="Não Preenchida">ENVIAR APL &#x21e3;</a>
				</div>
				<div id="mul-'.$pId_item.'" class="mul clear fl"></div>';
			}
		}
		// ************************************************
		// ************************************************
		// ************************************************




		$aReturn[0] = 1; //sucesso
		$aReturn[1] = '<div class="item-left">
			<span class="item-lb">Item:</span>
			'.$dItem.'
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>

			<span class="item-lb">Marca:</span>
			'.$dMarca.'
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>

			<span class="item-lb">Veículo Sugerido:</span>
			'.$dModelo.$dModelo_antigo.'
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>
		</div>
		<div class="item-right">
			<span class="item-lb">Tipo do Veículo:</span>
			'.$dTipo_veiculo.'
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>

			<span class="item-lb">Quantidade:</span>
			<span id="cell-5" class="item-vl cell">'.$dQuantidade.'</span>
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>

			<a class="cb'.$dTransformacao.'" href="javascript:void(0);" style="position: relative; float: left; margin-left: 160px; margin-top: 8px;">Transformação</a>
		</div>
		<div>
			<span class="item-lb">Descrição:</span>
			'.$dDescricao.'
		</div>
		<div class="item-left">
			<span class="item-lb">Valor Edital (R$):</span>
			'.$dValor.'
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>
		</div>
		<div class="item-right">
			<span style="float: right; line-height: 34px; font-size: 20px; margin: 15px 30px 0 0;"><span class="gray-88">Total do Item:</span> <span id="total-item">R$ '.$dTotal.'</span></span>
		</div>'.$apls;
	}
} 
echo json_encode($aReturn);

?>
