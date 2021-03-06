<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_lote = intval($_POST["id-lote"]);
	$pId_item = intval($_POST["id-item"]);

	$db = new Mysql();

	$aTipo_veiculos = array();
	$aTipo_veiculos[0] = '&lt;não informado&gt;';
	$aTipo_veiculos[1] = 'Hatch Popular';
	$aTipo_veiculos[2] = 'Hatch Premium';
	$aTipo_veiculos[3] = 'Sedan Popular';
	$aTipo_veiculos[4] = 'Sedan Premium';
	$aTipo_veiculos[5] = 'Pick-up Popular';
	$aTipo_veiculos[6] = 'Pick-up Premium';
	$aTipo_veiculos[7] = 'Station Wagon';
	$aTipo_veiculos[8] = 'Não pertinente';
	$aTipo_veiculos[9] = 'Não disponível';

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
	$aModelos[20] = 'Polo 1.0';
	$aModelos[21] = 'Polo 1.6';
	$aModelos[22] = 'Polo 200';
	$aModelos[23] = 'Amarok 3.0';
	$aModelos[24] = 'Virtus 1.0';
	$aModelos[25] = 'Virtus 1.6';

	//carrega item
	$db->query("
SELECT 
	itm.item, 
	itm.marca, 
	itm.id_modelo,
	itm.id_tipo_veiculo,
	itm.modelo,
	itm.transformacao, 
	itm.acompanhamento,
	itm.descricao, 
	itm.quantidade, 
	itm.valor, 
	DATE(lic.datahora_abertura) <= CURRENT_DATE() AS passou_abertura,
	(SELECT id FROM gelic_licitacoes_itens_participantes WHERE id_item = itm.id AND deletado = 0 LIMIT 1) AS participantes 
FROM 
	gelic_licitacoes_itens AS itm
	INNER JOIN gelic_licitacoes AS lic ON lic.id = itm.id_licitacao
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
		$dAcompanhamento = intval($db->f("acompanhamento"));

		if ($dItem == "") 
			$dItem = '<span id="cell-1" class="item-vl cell gray-88 italic">&lt;não informado&gt;</span>';
		else
			$dItem = '<span id="cell-1" class="item-vl cell">'.$dItem.'</span>';

		if ($dMarca == "")
			$dMarca = '<span id="cell-2" class="item-vl cell gray-88 italic">&lt;não informado&gt;</span>';
		else
			$dMarca = '<span id="cell-2" class="item-vl cell">'.$dMarca.'</span>';

		if ($dId_modelo == 0)
			$dModelo = '<span id="cell-3" class="item-vl cell gray-88 italic">'.$aModelos[$dId_modelo].'</span>';
		else
			$dModelo = '<span id="cell-3" class="item-vl cell">'.$aModelos[$dId_modelo].'</span>';

		//modelo antigo
		$dModelo_antigo = '';
		if ($dId_modelo == 18 && $db->f("modelo") <> '')
			$dModelo_antigo = '<span id="modelo-antigo" class="item-vl" style="margin-left: 160px; border: 0; line-height: 21px; padding-bottom: 6px; font-style: italic; color: #993333;">'.utf8_encode($db->f("modelo")).'</span>';

		if ($dDescricao == "")
			$dDescricao = '<span id="cell-4" class="item-vl-desc cell gray-88 italic">&lt;não informado&gt;</span>';
		else
			$dDescricao = '<span id="cell-4" class="item-vl-desc cell">'.$dDescricao.'</span>';

		if ($db->f("valor") == 0)
			$dValor = '<span id="cell-6" class="item-vl cell gray-88 italic">&lt;não informado&gt;</span>';
		else
			$dValor = '<span id="cell-6" class="item-vl cell">R$ '.number_format($db->f("valor"),2,",",".").'</span>';


		if ($db->f("passou_abertura") > 0)
		{
			if (strlen($db->f("participantes")) > 0)
				$partic = '<a class="partic2" href="javascript:void(0);" onclick="participantes('.$pId_item.');">Participantes</a>';
			else
				$partic = '<a class="partic1" href="javascript:void(0);" onclick="participantes('.$pId_item.');">Inserir Resultado</a>';
		}
		else
			$partic = '<a class="partic0" href="javascript:void(0);">N/D</a>';

		if ($dId_tipo_veiculo == 0)
			$dTipo_veiculo = '<span id="cell-7" class="item-vl cell gray-88 italic">'.$aTipo_veiculos[$dId_tipo_veiculo].'</span>';
		else
			$dTipo_veiculo = '<span id="cell-7" class="item-vl cell">'.$aTipo_veiculos[$dId_tipo_veiculo].'</span>';
			
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
					<a class="bt-apl-style-'.$dStatus_apl.' fr" href="javascript:void(0);" onclick="itemAPL_DN('.$pId_item.','.$db->f("id_cliente",1).');" style="height:24px;line-height:24px;font-size:13px;margin-top:2px;margin-right:30px;" title="'.$aAPL_B_style[$dStatus_apl].'">APL</a>
				</div>
				<div id="mul-'.$pId_item.'-'.$db->f("id_cliente",1).'" class="mul clear fl"></div>';
			}
		}


		if ($dns != '')
			$apls = '<div class="fl clear" style="text-align: center; width: 100%; line-height: 30px; border-bottom: 1px solid #bbbbbb; margin-top: 20px; font-size: 18px;">APL(s)</div>'.$dns.'<div class="fl clear" style="height:1px; width: 100%; border-top: 1px solid #bbbbbb;"></div>';
		// ************************************************
		// ************************************************
		// ************************************************

		$sp = '';
		if ($dns == '')
			$sp = 'border-top: 1px solid #bbbbbb;padding-top:20px;';

		
		$check_esclarecimento = 0;
		$check_impugnacao = 0;
		$check_recurso = 0;
		$check_mandado_seguranca = 0;
		$check_notificacoes = 0;
		$check_participacao_estoque = 0;
		$check_inabilitado = 0;	
		$check_derrota = 0;
		$check_arrematado = 0;
		$check_sem_prazo_participacao = 0;
		$check_sem_interesse_dn = 0;
		$check_sem_participacao_apl = 0;
		$check_nao_pertinente = 0;
		$db->query("SELECT * FROM gelic_licitacoes_itens_check WHERE id_item = $pId_item");
		if ($db->nextRecord())
		{
			$check_esclarecimento = $db->f("esclarecimento");
			$check_impugnacao = $db->f("impugnacao");
			$check_recurso = $db->f("recurso");
			$check_mandado_seguranca = $db->f("mandado_seguranca");
			$check_notificacoes = $db->f("notificacoes");
			$check_participacao_estoque = $db->f("participacao_estoque");
			$check_inabilitado = $db->f("inabilitado");	
			$check_derrota = $db->f("derrota");
			$check_arrematado = $db->f("arrematado");
			$check_sem_prazo_participacao = $db->f("sem_prazo_participacao");
			$check_sem_interesse_dn = $db->f("sem_interesse_dn");
			$check_sem_participacao_apl = $db->f("sem_participacao_apl");
			$check_nao_pertinente = $db->f("nao_pertinente");
		}

		$checks = '<div class="fl clear" style="text-align: left; width: 100%; margin-top: 20px; '.$sp.'">
			<div style="float:left;margin-left:30px;width:333px;">
				<a class="itm-cb'.$check_esclarecimento.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="esclarecimento"><img src="img/loader20.gif">Esclarecimento</a>
			</div>
			<div style="float:left;width:332px;">
				<a class="itm-cb'.$check_participacao_estoque.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="participacao_estoque"><img src="img/loader20.gif">Participação via estoque</a>
			</div>
			<div style="float:left;width:333px;">
				<a class="itm-cb'.$check_sem_prazo_participacao.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="sem_prazo_participacao"><img src="img/loader20.gif">Sem prazo para participação</a>
			</div>

			<div style="float:left;margin-left:30px;width:333px;">
				<a class="itm-cb'.$check_impugnacao.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="impugnacao"><img src="img/loader20.gif">Impugnação</a>
			</div>
			<div style="float:left;width:332px;">
				<a id="inab" class="itm-cb'.$check_inabilitado.'" href="javascript:void(0);" onclick="inabilitadoClick();" data-field="inabilitado"><img src="img/loader20.gif">Inabilitado</a>
			</div>
			<div style="float:left;width:333px;">
				<a id="sint" class="itm-cb'.$check_sem_interesse_dn.'" href="javascript:void(0);" onclick="semInteresseClick();" data-field="sem_interesse_dn"><img src="img/loader20.gif">Sem interesse DN - Aberto</a>
			</div>

			<div style="float:left;margin-left:30px;width:333px;">
				<a class="itm-cb'.$check_recurso.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="recurso"><img src="img/loader20.gif">Recurso ou Contrarrazão</a>
			</div>
			<div style="float:left;width:332px;">
				<a class="itm-cb'.$check_derrota.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="derrota"><img src="img/loader20.gif">Derrota</a>
			</div>
			<div style="float:left;width:333px;">
				<a id="spart" class="itm-cb'.$check_sem_participacao_apl.'" href="javascript:void(0);" onclick="semParticipacaoClick();" data-field="sem_participacao_apl"><img src="img/loader20.gif">Sem participação (com APL)</a>
			</div>

			<div style="float:left;margin-left:30px;width:333px;">
				<a class="itm-cb'.$check_mandado_seguranca.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="mandado_seguranca"><img src="img/loader20.gif">Mandado de segurança</a>
			</div>
			<div style="float:left;width:332px;">
				<a class="itm-cb'.$check_arrematado.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="arrematado"><img src="img/loader20.gif">Arrematado</a>
			</div>
			<div style="float:left;width:333px;">
				<a class="itm-cb'.$check_nao_pertinente.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="nao_pertinente"><img src="img/loader20.gif">Não pertinente</a>
			</div>

			<div style="float:left;margin-left:30px;width:333px;">
				<a class="itm-cb'.$check_notificacoes.'" href="javascript:void(0);" onclick="atualizarCheck(this);" data-field="notificacoes"><img src="img/loader20.gif">Notificações</a>
			</div>
		</div>';


		$aPen_date = array('','','','','');
		$db->query("SELECT id, evento, data_evento FROM gelic_licitacoes_itens_eventos WHERE id_item = $pId_item ORDER BY evento, data_evento");
		while ($db->nextRecord())
		{
			$aPen_date[intval($db->f("evento"))-1] .= '<div style="overflow:hidden;">
				<span class="disp-date">'.mysqlToBr($db->f("data_evento")).'</span>
				<a class="rem-date" href="javascript:void(0);" onclick="remDate('.$db->f("id").');" title="Remover">-</a>
			</div>';
		}

		$checks .= '<div class="fl clear" style="text-align: left; width: 100%; margin-top: 20px;">
			<div style="border-top: 1px solid #eeeeee; overflow:hidden;">
				<div style="float:left;margin-left:30px;">
					<span style="float:left;line-height:30px;font-weight:bold;">Empenho Recebido</span>
					<a class="add-date" href="javascript:void(0);" onclick="addDate(1);" title="Inserir Data">+</a>
					<div id="pen-date-1" style="clear:both;overflow:hidden;">
						'.$aPen_date[0].'
					</div>
				</div>
				<div style="float:left;margin-left:70px;">
					<span style="float:left;line-height:30px;font-weight:bold;">Faturamento</span>
					<a class="add-date" href="javascript:void(0);" onclick="addDate(2);" title="Inserir Data">+</a>
					<div id="pen-date-2" style="clear:both;overflow:hidden;">
						'.$aPen_date[1].'
					</div>
				</div>
				<div style="float:left;margin-left:70px;">
					<span style="float:left;line-height:30px;font-weight:bold;">Contrato Assinado</span>
					<a class="add-date" href="javascript:void(0);" onclick="addDate(3);" title="Inserir Data">+</a>
					<div id="pen-date-3" style="clear:both;overflow:hidden;">
						'.$aPen_date[2].'
					</div>
				</div>
				<div style="float:left;margin-left:70px;">
					<span style="float:left;line-height:30px;font-weight:bold;">Objeto Entregue</span>
					<a class="add-date" href="javascript:void(0);" onclick="addDate(4);" title="Inserir Data">+</a>
					<div id="pen-date-4" style="clear:both;overflow:hidden;">
						'.$aPen_date[3].'
					</div>
				</div>
				<div style="float:left;margin-left:70px;">
					<span style="float:left;line-height:30px;font-weight:bold;">Pagamento</span>
					<a class="add-date" href="javascript:void(0);" onclick="addDate(5);" title="Inserir Data">+</a>
					<div id="pen-date-5" style="clear:both;overflow:hidden;">
						'.$aPen_date[4].'
					</div>
				</div>
			</div>
		</div>';


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

			<a class="check-chk'.$dTransformacao.'" href="javascript:void(0);" onclick="atualizarSingle(this,1);" style="float: left; margin: 13px 0 0 160px;">Transformação</a>
			<a class="check-chk'.$dAcompanhamento.'" href="javascript:void(0);" onclick="atualizarSingle(this,2);" style="float: left; margin: 13px 0 0 40px;">Acompanhamento</a>
		</div>
		<div>
			<span class="item-lb">Descrição:</span>
			'.$dDescricao.'
		</div>
		<div class="item-left">
			<span class="item-lb">Valor Edital (R$):</span>
			'.$dValor.'
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>

			<span class="item-lb">Participantes:</span>
			'.$partic.'
			<div style="display:inline-block;float:left;clear:both;height:2px;"></div>
		</div>
		<div class="item-right">
			<span style="float: right; line-height: 34px; font-size: 20px; margin-right: 75px; margin-top: 36px;"><a class="gray-88">Total do Item:</a> <a id="total-item">R$ '.$dTotal.'</a></span>
		</div>'.$apls.$checks;

		if ($apls == '')
			$aReturn[2] = 0;
		else
			$aReturn[2] = 1;
	}
} 
echo json_encode($aReturn);

?>
