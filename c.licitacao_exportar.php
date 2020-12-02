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

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$select = "lic.id,
			lic.instancia,
			lic.orgao,
			lic.objeto,
			lic.importante,
			lic.datahora_abertura,
			lic.datahora_entrega,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			DATEDIFF(DATE(lic.datahora_entrega), CURRENT_DATE()) AS data_entrega_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			lic.link,
			lic.numero,
			lic.data_hora,
			lic.srp,
			lic.meepp,
			lic.prazo_entrega_produto,
			lic.prazo_entrega_produto_uteis,
			lic.numero_rastreamento,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf AS uf,
			labas.id_status,
			hdec.id AS declinou,
			edi.id AS edital,
			ata.id AS ata";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
			INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
			INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
			LEFT JOIN gelic_licitacoes_edital AS edi ON edi.id_licitacao = lic.id
			LEFT JOIN gelic_licitacoes_ata AS ata ON ata.id_licitacao = lic.id
			LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
			LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$data_hoje = date("Y-m-d");
		$where = "lic.id = $pId_licitacao AND
			lic.deletado = 0 AND
			his.id IS NULL AND
			IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3) AND
			IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= '$data_hoje')";
	}
	else if ($sInside_tipo == 1) //BO
	{
		$select = "lic.id,
			lic.instancia,
			lic.orgao,
			lic.objeto,
			lic.importante,
			lic.datahora_abertura,
			lic.datahora_entrega,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			DATEDIFF(DATE(lic.datahora_entrega), CURRENT_DATE()) AS data_entrega_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			lic.link,
			lic.numero,
			lic.data_hora,
			lic.srp,
			lic.meepp,
			lic.prazo_entrega_produto,
			lic.prazo_entrega_produto_uteis,
			lic.numero_rastreamento,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf AS uf,
			labas.id_status,
			edi.id AS edital,
			ata.id AS ata";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2
			INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
			INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
			LEFT JOIN gelic_licitacoes_edital AS edi ON edi.id_licitacao = lic.id
			LEFT JOIN gelic_licitacoes_ata AS ata ON ata.id_licitacao = lic.id
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$where = "lic.id = $pId_licitacao AND
			lic.deletado = 0 AND
			his.id IS NULL";
	}

	$db = new Mysql();

	$aInstancia = array();
	$aInstancia[1] = "Municipal";
	$aInstancia[2] = "Estadual";
	$aInstancia[3] = "Federal";

	$aSimnao = array('<span class="gray-aa">- não informado -</span>','Sim','Não');
	$aPrazoentregaproduto = array('<span class="gray-aa">- não informado -</span>','dias úteis','dias corridos');

	$aTipo_veiculos = array();
	$aTipo_veiculos[0] = '';
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
	$aModelos[0] = '';
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

	$aEstados = array();
	$aEstados["AC"] = utf8_decode("Região 6");
	$aEstados["AL"] = utf8_decode("Região 5");
	$aEstados["AP"] = utf8_decode("Região 6");
	$aEstados["AM"] = utf8_decode("Região 6");
	$aEstados["BA"] = utf8_decode("Região 5");
	$aEstados["CE"] = utf8_decode("Região 5");
	$aEstados["DF"] = utf8_decode("Região 6");
	$aEstados["ES"] = utf8_decode("Região 4");
	$aEstados["GO"] = utf8_decode("Região 6");
	$aEstados["MA"] = utf8_decode("Região 6");
	$aEstados["MT"] = utf8_decode("Região 6");
	$aEstados["MS"] = utf8_decode("Região 6");
	$aEstados["MG"] = utf8_decode("Região 4");
	$aEstados["PA"] = utf8_decode("Região 6");
	$aEstados["PB"] = utf8_decode("Região 5");
	$aEstados["PR"] = utf8_decode("Região 3");
	$aEstados["PE"] = utf8_decode("Região 5");
	$aEstados["PI"] = utf8_decode("Região 5");
	$aEstados["RJ"] = utf8_decode("Região 4");
	$aEstados["RN"] = utf8_decode("Região 5");
	$aEstados["RS"] = utf8_decode("Região 3");
	$aEstados["RO"] = utf8_decode("Região 6");
	$aEstados["RR"] = utf8_decode("Região 6");
	$aEstados["SC"] = utf8_decode("Região 3");
	$aEstados["SP"] = utf8_decode("Região 1/2");
	$aEstados["SE"] = utf8_decode("Região 5");
	$aEstados["TO"] = utf8_decode("Região 6");
	
	$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
	if ($db->nextRecord())
	{
		$tTmp = '
		<div class="onecont" style="border-bottom:0;">
			<ul class="onecont_left" style="padding-left: 10px;">
				<li><div>DN(s)</div><div>{{CLI}}</div></li>
				<li><div>Nº da Licitação</div><div>{{LIC_NUMERO}}</div></li>
				<li><div>Data/Hora de Abertura</div><div>{{LIC_DHAB}} ({{LIC_DHAB_D}})</div></li>
				<li><div>Data/Hora de Entrega</div><div>{{LIC_DHEN}} ({{LIC_DHEN_D}})</div></li>
				<li><div>Modalidade</div><div>{{LIC_MOD}}</div></li>
				<li><div>Instância</div><div>{{LIC_INSTANCIA}}</div></li>
				<li><div>É SRP?</div><div>{{LIC_SRP}}</div></li>
				<li><div>Participação ME/EPP?</div><div>{{LIC_MEEPP}}</div></li>
				<li><div>Número de Rastreamento</div><div>{{LIC_RAST}}</div></li>
				<li><div>Prazo de Entrega do Veículo</div><div>{{LIC_PRAZO_ENTREGA_PRODUTO}}</div></li>
				<li><div>Órgão Público</div><div>{{LIC_ORGAO}}</div></li>
				<li><div>Localização</div><div>{{CID_NOME}} - {{CID_UF}}</div></li>
				<li><div>Link</div><div class="none">{{LIC_LINK}}</div></li>
				<li><div>Valor Estimado</div><div>{{LIC_VALOR}}</div></li>
				<li><div>Status</div><div id="d-status">{{LIC_STATUS}}</div></li>
			</ul>
			<div class="numid">
				<span class="one">Número de Identificação</span>
				<span class="two">{{LIC_ID}}</span>
			</div>
			<div style="position: absolute; right: 10px; bottom: 10px;">
				{{LIC_ATA}}
				{{LIC_EDITAL}}
			</div>
		</div>
		<div class="twocont" style="border-top: 0;">
			<span style="display:block;line-height:30px;padding-left:10px;background-color:#aaaaaa;color:#ffffff;font-weight:bold;">Objeto</span>
			<div class="obj-conteudo" style="padding: 10px;">
				<div style="overflow: hidden;">
					<span class="t12 fl">{{LIC_DATA}}</span>
					<span class="t12 fl ml-10 gray-88">{{LIC_HORA}}</span>
					<span class="t12 fr gray-88">{{LIC_TEMPO}}</span>
				</div>
				<div class="t13 lh-17" style="padding: 10px 0 0 0; overflow: hidden;">
					{{LIC_OBJETO}}
				</div>
			</div>
			<span style="display:block;line-height:30px;padding-left:10px;background-color:#aaaaaa;color:#ffffff;font-weight:bold;">Importante</span>
			<div class="imp-conteudo" style="background-color: #fae5e9; padding: 10px;">
				{{LIC_IMPORTANTE}}
			</div>
			<span style="display:block;line-height:30px;padding-left:10px;background-color:#aaaaaa;color:#ffffff;font-weight:bold;">Itens ({{TOTAL_ITENS}})</span>
			<div class="itens-conteudo" style="padding: 14px 0px; display: block; background-color: rgb(255, 255, 255);">
				<div class="item-linha"><!-- line --></div>
				{{ITENS}}
			</div>
		</div>';

		$declinou = false;
		if (($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) && $db->f("declinou") > 0)
			$declinou = true;


		if ($declinou)
		{
			$status = '<span style="color: #ffffff; background-color: #ff0000; padding: 2px 6px; float: left;">SEM INTERESSE</span>';
		}
		else
		{
			if (in_array($db->f("id_status"), array(8,19))) // APL Aprovada, APL Reprovada
			{
				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
					$db->query("SELECT enviadas, aprovadas, reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = ".$db->f("id")." AND id_cliente = $cliente_parent",1);
				else
					$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = ".$db->f("id"),1);

				$db->nextRecord(1);

				$status = '';

				if ($db->f("fase") == 1)
				{
					if ($db->f("enviadas",1) > 0)
						$status .= '<span style="color: #ffffff; background-color: #827c7c; padding: 2px 6px; float: left;">APL em Análise - GELIC ('.$db->f("enviadas",1).')</span>';
				}
				else
				{
					if ($db->f("enviadas",1) > 0)
						$status .= '<span style="color: #050000; background-color: #ffe600; padding: 2px 6px; float: left;">APL Aguardando Aprovação ('.$db->f("enviadas",1).')</span>';
				}

				if ($db->f("aprovadas",1) > 0)
					$status .= '<span style="color: #ffffff; background-color: #00b318; padding: 2px 6px; float: left;">APL Aprovada ('.$db->f("aprovadas",1).')</span>';

				if ($db->f("reprovadas",1) > 0)
					$status .= '<span style="color: #ffffff; background-color: #ed0000; padding: 2px 6px; float: left;">APL Reprovada ('.$db->f("reprovadas",1).')</span>';
			}
			else
			{
				$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = ".$db->f("id_status"),1);
				$db->nextRecord(1);
				$status = '<span style="color: #'.$db->f("cor_texto",1).'; background-color: #'.$db->f("cor_fundo",1).'; padding: 2px 6px; float: left;">'.utf8_encode($db->f("descricao",1)).'</span>';
			}
		}

		$dab_h = substr($db->f("datahora_abertura"),11,5);
		$den_h = substr($db->f("datahora_entrega"),11,5);
		if ($dab_h == "00:00") $dab_h = "--:--";
		if ($den_h == "00:00") $den_h = "--:--";

		$tTmp = str_replace("{{LIC_STATUS}}", $status, $tTmp);
		$dLimite = segundosConv($db->f("limite"));
		$tTmp = str_replace("{{LIC_ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{LIC_NUMERO}}", $db->f("numero"), $tTmp);
		$tTmp = str_replace("{{LIC_DHAB}}", mysqlToBr(substr($db->f("datahora_abertura"),0,10)).' '.$dab_h, $tTmp);
		$tTmp = str_replace("{{LIC_DHEN}}", mysqlToBr(substr($db->f("datahora_entrega"),0,10)).' '.$den_h, $tTmp);
		$tTmp = str_replace("{{LIC_DHAB_D}}", niceDays($db->f("data_abertura_dias")), $tTmp);
		$tTmp = str_replace("{{LIC_DHEN_D}}", niceDays($db->f("data_entrega_dias")), $tTmp);
		$tTmp = str_replace("{{LIC_MOD}}", utf8_encode($db->f("nome_modalidade")), $tTmp);
		$tTmp = str_replace("{{LIC_ORGAO}}", utf8_encode(stripslashes($db->f("orgao"))), $tTmp);
		$tTmp = str_replace("{{CID_NOME}}", utf8_encode($db->f("nome_cidade")), $tTmp);
		$tTmp = str_replace("{{CID_UF}}", $db->f("uf"), $tTmp);

		if ($db->f("instancia") > 0)
			$tTmp = str_replace("{{LIC_INSTANCIA}}", $aInstancia[$db->f("instancia")], $tTmp);
		else
			$tTmp = str_replace("{{LIC_INSTANCIA}}", '<a class="gray-aa">- não informado -</a>', $tTmp);

		if (strlen($db->f("link")) > 0)
			$tTmp = str_replace("{{LIC_LINK}}", '<a class="ablue" href="'.$db->f("link").'" target="_blank">'.$db->f("link").'</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_LINK}}", '<span class="gray-aa">- não informado -</span>', $tTmp);

		if ($db->f("valor") == 0.00)
			$tTmp = str_replace("{{LIC_VALOR}}", '<span class="gray-aa">- não informado -</span>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_VALOR}}", "R$ ".number_format($db->f("valor"),2,',','.'), $tTmp);

		$tTmp = str_replace("{{LIC_SRP}}", $aSimnao[$db->f("srp")], $tTmp);
		$tTmp = str_replace("{{LIC_MEEPP}}", $aSimnao[$db->f("meepp")], $tTmp);

		if (strlen($db->f("numero_rastreamento")) > 0)
			$tTmp = str_replace("{{LIC_RAST}}", '<a class="ablue" href="http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI='.$db->f("numero_rastreamento").'" target="_blank">'.$db->f("numero_rastreamento").'</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_RAST}}", '<span class="gray-aa">- não informado -</span>', $tTmp);

		$tTmp = str_replace("{{LIC_PRAZO_ENTREGA_PRODUTO}}", $db->f("prazo_entrega_produto")." ".$aPrazoentregaproduto[$db->f("prazo_entrega_produto_uteis")], $tTmp);


		if ($db->f("fase") == 1)
		{
			$DNs = '';
			$db->query("SELECT nome FROM gelic_clientes WHERE id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = ".$db->f("id").")",1);
			while ($db->nextRecord(1))
				$DNs .= '<br>'.utf8_encode($db->f("nome",1));

			$tTmp = str_replace("{{CLI}}", substr($DNs,4), $tTmp);
		}
		else if ($db->f("fase") == 2)
		{
			$tTmp = str_replace("{{CLI}}", utf8_encode($aEstados[$db->f("uf")]), $tTmp);
		}
		else
		{
			$tTmp = str_replace("{{CLI}}", "Brasil", $tTmp);
		}


		//EDITAL
		if (strlen($db->f("edital")) > 0)
			$tTmp = str_replace("{{LIC_EDITAL}}", '<span class="gray-88" style="height: 26px; line-height: 26px;">[&nbsp;contém&nbsp;edital&nbsp;]</span>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_EDITAL}}", '', $tTmp);

		//ATA
		if (strlen($db->f("ata")) > 0)
			$tTmp = str_replace("{{LIC_ATA}}", '<span class="gray-88" style="height: 26px; line-height: 26px;">[&nbsp;contém&nbsp;ata&nbsp;]</span>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_ATA}}", '', $tTmp);

	
		$tTmp = str_replace("{{LIC_DATA}}", mysqlToBr(substr($db->f("data_hora"),0,10)), $tTmp);
		$tTmp = str_replace("{{LIC_HORA}}", substr($db->f("data_hora"),11), $tTmp);
		$tTmp = str_replace("{{LIC_TEMPO}}", timeAgo($db->f("data_hora")), $tTmp);
		$tTmp = str_replace("{{LIC_OBJETO}}", utf8_encode(stripslashes($db->f("objeto"))), $tTmp);
		$tTmp = str_replace("{{LIC_IMPORTANTE}}", utf8_encode(stripslashes($db->f("importante"))), $tTmp);



		//*********************************************************************************************
		//******************************************* ITENS *******************************************
		//*********************************************************************************************
		$oItens = '';
		$total_itens = 0;
		$db->query("SELECT id, lote FROM gelic_licitacoes_lotes WHERE id_licitacao = $pId_licitacao AND id IN (SELECT id_lote FROM gelic_licitacoes_itens WHERE id_licitacao = $pId_licitacao AND id_tipo_veiculo > 0 AND acompanhamento = 0) ORDER BY id");
		while ($db->nextRecord())
		{
			//LOTES
			$dId_lote = $db->f("id");
			$oItens .= '<div id="lote-'.$dId_lote.'">
				<div class="lote">
					<span class="t12 bold lote-item">Item</span>
					<span class="t12 bold lote-marca">Marca</span>
					<span class="t12 bold lote-modelo">Modelo</span>
					<span class="t12 bold lote-veiculo">T.Veículo</span>
					<span class="t12 bold lote-transformacao">T</span>
					<span class="t12 bold lote-descricao">Descrição do Item</span>
					<span class="t12 bold lote-quantidade">Quant.</span>
					<span class="t12 bold lote-valor">R$ Edital</span>
					<span class="t12 bold lote-total">Total por Item</span>
				</div>
				<div class="t12 italic bold lote-nome"><span id="'.$dId_lote.'-0-0" class="t12 cell cust-lote">Lote: '.utf8_encode($db->f("lote")).'</span></div>';


			//ITENS
			$db->query("
SELECT 
	id, 
	item, 
	marca, 
	id_modelo,
	id_tipo_veiculo, 
	transformacao,
	descricao, 
	quantidade, 
	valor
FROM 
	gelic_licitacoes_itens 
WHERE 
	id_licitacao = $pId_licitacao AND 
	id_lote = $dId_lote AND
	id_tipo_veiculo > 0 AND
	acompanhamento = 0
ORDER BY 
	id",1);
			while ($db->nextRecord(1))
			{
				if ($db->f("quantidade",1) > 0)
					$dTotal_item = number_format($db->f("valor",1)*$db->f("quantidade",1),2,",",".");
				else
					$dTotal_item = number_format($db->f("valor",1),2,",",".");

				if ($db->f("transformacao",1) > 0)
					$trans = '<span class="item-transformacao"><img src="../img/check1010.png" style="opacity:0.7;"></span>';
				else
					$trans = '<span class="item-transformacao">&nbsp;</span>';

				$oItens .= '<div id="item-'.$db->f("id",1).'" class="item">
					<span id="'.$dId_lote.'-'.$db->f("id",1).'-0" class="t11 cell item-item">'.utf8_encode(emptySpace($db->f("item",1))).'</span>
					<span id="'.$dId_lote.'-'.$db->f("id",1).'-1" class="t11 cell item-marca">'.utf8_encode(emptySpace($db->f("marca",1))).'</span>
					<span id="'.$dId_lote.'-'.$db->f("id",1).'-2" class="t11 cell item-modelo">'.emptySpace($aModelos[$db->f("id_modelo",1)]).'</span>
					<span id="'.$dId_lote.'-'.$db->f("id",1).'-3" class="t11 cell item-veiculo">'.emptySpace($aTipo_veiculos[$db->f("id_tipo_veiculo",1)]).'</span>
					'.$trans.'
					<span id="'.$dId_lote.'-'.$db->f("id",1).'-4" class="t11 cell item-descricao">'.utf8_encode(emptySpace($db->f("descricao",1))).'</span>
					<span id="'.$dId_lote.'-'.$db->f("id",1).'-5" class="t11 cell item-quantidade">'.emptyZero($db->f("quantidade",1)).'</span>
					<span id="'.$dId_lote.'-'.$db->f("id",1).'-6" class="t11 cell item-valor">R$ '.number_format($db->f("valor",1),2,",",".").'</span>';


				$oItens .= '
					<span class="t11 item-total-valor itve edital">R$ '.$dTotal_item.'</span>
					<div class="item-divider left-50"></div>
					<div class="item-divider left-121"></div>
					<div class="item-divider left-196"></div>
					<div class="item-divider left-271"></div>
					<div class="item-divider left-292"></div>
					<div class="item-divider left-651"></div>
					<div class="item-divider left-702"></div>
					<div class="item-divider left-819"></div>
				</div>';

				$total_itens += 1;



				//APLs
				$oAPL = '';
				$aApl = array();
				if ($sInside_tipo == 1) //BO
					$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao ORDER BY id",2);
				else
					$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = ".$db->f("id",1)." AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))",2);

				while ($db->nextRecord(2))
					$aApl[] = $db->f("id",2);

				for ($i=0; $i<count($aApl); $i++)
				{
					$db->query("
	SELECT 
		apl.*,
		itm.item,
		lot.lote,
		IF (cli.id_parent > 0, clip.nome, cli.nome) AS dn_nome,
		his.data_hora AS enviou_data_hora,
		cli_env.nome AS enviou_nome,
		his.ip AS enviou_ip,
		cli_env.id_parent AS enviou_parent
	FROM
		gelic_licitacoes_apl AS apl
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
		INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
		INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
		LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
		INNER JOIN (SELECT * FROM gelic_licitacoes_apl_historico WHERE id_apl = ".$aApl[$i]." AND tipo = 1 ORDER BY id DESC LIMIT 1) AS his
		INNER JOIN gelic_clientes AS cli_env ON cli_env.id = his.id_cliente
	WHERE
		apl.id = ".$aApl[$i],2);
					if ($db->nextRecord(2))
					{
						$dDn_nome = utf8_encode($db->f("dn_nome",2));
						$dId_licitacao = $db->f("id_licitacao",2);
						$dItem = utf8_encode($db->f("item",2));
						$dLote = utf8_encode($db->f("lote",2));
						$dEnviou_data_hora = $db->f("enviou_data_hora",2);
						$dEnviou_ip = $db->f("enviou_ip",2);

						$por = utf8_encode($db->f("enviou_nome",2));
						if ($db->f("enviou_parent",2) > 0)
							$por .= ' (DN: '.$dDn_nome.')';

						$dVersao = intval($db->f("versao",2));
						$dNome_orgao = utf8_encode($db->f("nome_orgao",2));
						$dData_licitacao = mysqlToBr($db->f("data_licitacao",2));
						$dRegistro_precos = intval($db->f("registro_precos",2));
						$dCnpj_faturamento = $db->f("cnpj_faturamento",2);
						$dEstado = $db->f("estado",2);
						$dModalidade_venda = intval($db->f("modalidade_venda",2));
						$dSite_pregao_eletronico = utf8_encode($db->f("site_pregao_eletronico",2));
						$dNumero_licitacao = utf8_encode($db->f("numero_licitacao",2));
						$dParticipante_nome = utf8_encode($db->f("participante_nome",2));
						$dParticipante_cpf = $db->f("participante_cpf",2);
						$dParticipante_rg = utf8_encode($db->f("participante_rg",2));
						$dParticipante_telefone = $db->f("participante_telefone",2);
						$dParticipante_endereco = utf8_encode($db->f("participante_endereco",2));
						$dV1_documentacao_selecionados = json_decode($db->f("v1_documentacao_selecionados",2));
						$dV1_documentacao_outros = utf8_encode($db->f("v1_documentacao_outros",2));
						$dModel_code = utf8_encode($db->f("model_code",2));
						$dCor = utf8_encode($db->f("cor",2));
						$dAno_modelo = utf8_encode($db->f("ano_modelo",2));
						$dMotorizacao = utf8_encode($db->f("motorizacao",2));
						$dPotencia = utf8_encode($db->f("potencia",2));
						$dCombustivel = utf8_encode($db->f("combustivel",2));
						$dOpcionais_pr = utf8_encode($db->f("opcionais_pr",2));
						$dEficiencia_energetica = intval($db->f("eficiencia_energetica",2));
						$dTransformacao = intval($db->f("transformacao",2));
						$dTransformacao_tipo = utf8_encode($db->f("transformacao_tipo",2));
						$dTransformacao_prototipo = intval($db->f("transformacao_prototipo",2));
						$dTransformacao_detalhar = utf8_encode($db->f("transformacao_detalhar",2));
						$dTransformacao_amarok_nome_arquivo	= utf8_encode($db->f("transformacao_amarok_nome_arquivo",2));
						$dTransformacao_amarok_arquivo = $db->f("transformacao_amarok_arquivo",2);
						$dAcessorios = intval($db->f("acessorios",2));
						$dEmplacamento = intval($db->f("emplacamento",2));
						$dLicenciamento = intval($db->f("licenciamento",2));
						$dIpva = intval($db->f("ipva",2));
						$dGarantia = intval($db->f("garantia",2));
						$dGarantia_prazo = intval($db->f("garantia_prazo",2));
						$dGarantia_prazo_outro = utf8_encode($db->f("garantia_prazo_outro",2));
						$dRevisao_embarcada = intval($db->f("revisao_embarcada",2));
						$dQuantidade_revisoes_inclusas = ($db->f("quantidade_revisoes_inclusas",2) == 0) ? "" : $db->f("quantidade_revisoes_inclusas",2);
						$dLimite_km = intval($db->f("limite_km",2));
						$dLimite_km_km = ($db->f("limite_km_km",2) == 0) ? "" : $db->f("limite_km_km",2);
						$dPreco_publico_vw = ($db->f("preco_publico_vw",2) == '0.00') ? "" : "R$ ".number_format($db->f("preco_publico_vw",2), 2, ",", ".");
						$dPreco_ref_edital = ($db->f("preco_ref_edital",2) == '0.00') ? "" : "R$ ".number_format($db->f("preco_ref_edital",2), 2, ",", ".");
						$dQuantidade_veiculos = ($db->f("quantidade_veiculos",2) == 0) ? "" : $db->f("quantidade_veiculos",2);
						$dDn_venda = utf8_encode($db->f("dn_venda",2));
						$dDn_venda_estado = utf8_encode($db->f("dn_venda_estado",2));
						$dRepasse_concessionario = ($db->f("repasse_concessionario",2) == 0) ? "" : $db->f("repasse_concessionario",2);
						$dDn_entrega = utf8_encode($db->f("dn_entrega",2));
						$dDn_entrega_estado = utf8_encode($db->f("dn_entrega_estado",2));
						$dPrazo_entrega = ($db->f("prazo_entrega",2) == 0) ? "" : $db->f("prazo_entrega",2);
						$dValidade_proposta = ($db->f("validade_proposta",2) == 0) ? "" : $db->f("validade_proposta",2);
						$dVigencia_contrato = utf8_encode($db->f("vigencia_contrato",2));
						$dPrazo_pagamento = ($db->f("prazo_pagamento",2) == 0) ? "" : $db->f("prazo_pagamento",2);
						$dV1_prazo_pagamento = utf8_encode($db->f("v1_prazo_pagamento",2));
						$dV1_desconto = utf8_encode($db->f("v1_desconto",2));
						$dV1_preco_maximo = ($db->f("v1_preco_maximo",2) == '0.00') ? "" : "R$ ".number_format($db->f("v1_preco_maximo",2), 2, ",", ".");
						$dV1_numero_pool = utf8_encode($db->f("v1_numero_pool",2));
						$dAve = utf8_encode($db->f("ave",2));
						$dMultas_sansoes = utf8_encode($db->f("multas_sansoes",2));
						$dGarantia_contrato = intval($db->f("garantia_contrato",2));
						$dPrazo = utf8_encode($db->f("prazo",2));
						$dValor = utf8_encode($db->f("valor",2));
						$dOrigem_verba = intval($db->f("origem_verba",2));
						$dOrigem_verba_tipo = intval($db->f("origem_verba_tipo",2));
						$dIsencao_impostos = intval($db->f("isencao_impostos",2));
						$dImposto_indicar = utf8_encode($db->f("imposto_indicar",2));
						$dObservacoes = utf8_encode($db->f("observacoes",2));

						$amarok_upload_anexo = '';

						if ($dVersao == 2)
						{
							if ($dTransformacao_amarok_arquivo != '' && file_exists(UPLOAD_DIR."apl/".$dTransformacao_amarok_arquivo))
							{
								$pShort_file_name = $db->f("transformacao_amarok_nome_arquivo",2);
								if (strlen($pShort_file_name) > 84)
									$pShort_file_name = substr($pShort_file_name, 0, 73)."...".substr($pShort_file_name, -8);

								$file_size = formatSizeUnits(filesize(UPLOAD_DIR."apl/".$dTransformacao_amarok_arquivo));
								$amarok_upload_anexo = 'Anexo: <span>'.utf8_encode($pShort_file_name).'</span> ('.$file_size.')';
							}
						}


						$tAcessorios = '';
						$db->query("SELECT acessorio, valor FROM gelic_licitacoes_apl_acessorios WHERE id_apl = ".$aApl[$i]." ORDER BY id",2);
						while ($db->nextRecord(2))
						{
							$dAcessorio = utf8_encode($db->f("acessorio",2));
							$dValor_ace = ($db->f("valor",2) == '0.00') ? "" : "R$ ".number_format($db->f("valor",2), 2, ",", ".");

							$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
								<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[2].'</span>
								<span class="apl-lb w-576 bg-2 white lpad">'.$dAcessorio.'</span>
								<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[2].' (R$)</span>
								<span class="apl-lb w-168 bg-2 white rpad right">'.$dValor_ace.'</span>
							</div>';
						}

						if ($dVersao == 2)
						{
							if ($dAcessorios == 2)
								$tAcessorios = '';
							else
								$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>';

							$aPrazo = array();
							$aPrazo[] = '';
							$aPrazo[] = '12 meses';
							$aPrazo[] = '24 meses';
							$aPrazo[] = '36 meses';

							if ($dGarantia_prazo < 4)
								$dGarantia_prazo_str = $aPrazo[$dGarantia_prazo];
							else
								$dGarantia_prazo_str = $dGarantia_prazo_outro;

							$kmkm = '<span class="apl-lb bg-2" style="width:234px;">&nbsp;</span>';
							if ($dLimite_km == 1)
							$kmkm = '<span class="apl-lb bg-2 center" style="width:40px;color:#f1f1f1;border-left:1px solid #cccccc;">KM:</span>
								<span class="apl-lb bg-2 white lpad" style="width:187px;">'.$dLimite_km_km.'</span>';
						}


						$m = '20';
						if ($i > 0)
							$m = '40';

						if ($dVersao == 1)
							$oAPL .= '
								<div class="apl-row" style="margin: '.$m.'px 0 4px 0; text-align: center;">
									<span><a class="bold">APL do DN:</a> '.$dDn_nome.'&nbsp;&nbsp;&nbsp;<a class="bold">Licitação:</a> '.$dId_licitacao.'&nbsp;&nbsp;&nbsp;<a class="bold">Lote:</a> '.$dLote.'&nbsp;&nbsp;&nbsp;<a class="bold">Item:</a> '.$dItem.'</span>
								</div>
								<div class="apl-row apl-bt apl-br apl-bb apl-bl">
									<span class="apl-lb w-238 bg-1 white center">Nome do Órgão</span>
									<span class="apl-lb w-388 bg-2 white lpad">'.$dNome_orgao.'</span>
									<span class="apl-lb w-180 bg-1 white center">Data da Licitação</span>
									<span class="apl-lb w-138 bg-2 white lpad">'.$dData_licitacao.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-238 bg-1 white center">ID Licitação</span>
									<span class="apl-lb w-388 bg-2 white lpad">'.$dId_licitacao.'</span>
									<span class="apl-lb w-180 bg-imp center">Registro de Preços</span>
									<div class="apl-lb w-144 bg-2">
										<a class="rbw'.(int)in_array($dRegistro_precos,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dRegistro_precos,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Modalidade da Venda</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:76px;">
									<a class="rb'.(int)in_array($dModalidade_venda, array(1)).'" style="left:40px;top:14px;">Compra Direta</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(2)).'" style="left:250px;top:14px;">Tomada de Preços</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(3)).'" style="left:460px;top:14px;">Pregão Presencial</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(4)).'" style="left:670px;top:14px;">Adesão a Registro de Preços</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(5)).'" style="left:40px;bottom:13px;">Carta Convite</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(6)).'" style="left:250px;bottom:13px;">Concorrência</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(7)).'" style="left:460px;bottom:13px;">Pregão Eletrônico</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(8)).'" style="left:670px;bottom:13px;">Aditivo Contratual</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-238 bg-1 white center">Site Pregão Eletrônico</span>
									<span class="apl-lb w-388 bg-3 lpad">'.$dSite_pregao_eletronico.'</span>
									<span class="apl-lb w-180 bg-1 white center">Nº da Licitação</span>
									<span class="apl-lb w-138 bg-3 lpad">'.$dNumero_licitacao.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Documentação: Documentos Solicitados pelo Órgão (Licitações/Compra Direta)</span>
									<span class="apl-lb w-100p bg-1 white center italic">Dados do Participante pela VW (Concessionário / Pool)</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100 bg-1 white center">Nome</span>
									<span class="apl-lb w-880 bg-3 lpad">'.$dParticipante_nome.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100 bg-1 white center">RG</span>
									<span class="apl-lb w-368 bg-3 lpad">'.$dParticipante_rg.'</span>
									<span class="apl-lb w-100 bg-1 white center">CPF</span>
									<span class="apl-lb w-376 bg-3 lpad">'.$dParticipante_cpf.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Relacione os Documentos</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3" style="height: 165px;">
									<a class="cb'.(int)in_array(1, $dV1_documentacao_selecionados).'" style="left:40px;top:14px;">Atestado de Capacidade Técnica</a>
									<a class="cb'.(int)in_array(2, $dV1_documentacao_selecionados).'" style="left:40px;top:34px;">Ato Constitutivo</a>
									<a class="cb'.(int)in_array(3, $dV1_documentacao_selecionados).'" style="left:40px;top:54px;">Balanço Patrimonial</a>
									<a class="cb'.(int)in_array(4, $dV1_documentacao_selecionados).'" style="left:40px;top:74px;">Certidão de Tributos Estaduais</a>
									<a class="cb'.(int)in_array(5, $dV1_documentacao_selecionados).'" style="left:40px;top:94px;">Certidão de Tributos Federais / Divida Ativa da União (Internet)</a>
									<a class="cb'.(int)in_array(6, $dV1_documentacao_selecionados).'" style="left:40px;top:114px;">Certidão de Tributos Municipais</a>
									<a class="cb'.(int)in_array(7, $dV1_documentacao_selecionados).'" style="left:40px;top:134px;">CND INSS (Internet)</a>
									<a class="cb'.(int)in_array(8, $dV1_documentacao_selecionados).'" style="left:490px;top:14px;">CNDT - Certidão Negativa de Débitos Trabalhistas (Internet)</a>
									<a class="cb'.(int)in_array(9, $dV1_documentacao_selecionados).'" style="left:490px;top:34px;">CNPJ (Internet)</a>
									<a class="cb'.(int)in_array(10, $dV1_documentacao_selecionados).'" style="left:490px;top:54px;">Falência e Concordata</a>
									<a class="cb'.(int)in_array(11, $dV1_documentacao_selecionados).'" style="left:490px;top:74px;">FGTS (Internet)</a>
									<a class="cb'.(int)in_array(12, $dV1_documentacao_selecionados).'" style="left:490px;top:94px;">Ficha de Inscrição Estadual)</a>
									<a class="cb'.(int)in_array(13, $dV1_documentacao_selecionados).'" style="left:490px;top:114px;">Ficha de Inscrição Municipal</a>
									<a class="cb'.(int)in_array(14, $dV1_documentacao_selecionados).'" style="left:490px;top:134px;">Procuração</a>
								</div>
								<div class="apl-row apl-br apl-bl bg-3">
									<span class="apl-lb w-100p italic" style="margin-left: 40px;">Relacione outros documentos que não constam entre os citados acima:</span>
								</div>
								<div class="apl-row apl-br apl-bl">
									<span class="apl-textarea" style="margin-top:0;">'.nl2br($dV1_documentacao_outros).'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100 bg-3 center">Model Code</span>
									<span class="apl-lb w-100 bg-2 white lpad">'.$dModel_code.'</span>
									<span class="apl-lb w-100 bg-3 center">Cor</span>
									<span class="apl-lb w-100 bg-2 white lpad">'.$dCor.'</span>
									<span class="apl-lb w-128 bg-3 center">Opcionais (PR\'s)</span>
									<span class="apl-lb bg-2 white lpad" style="width:410px;">'.$dOpcionais_pr.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100 bg-3 center">Motorização</span>
									<span class="apl-lb w-100 bg-2 white lpad">'.$dMotorizacao.'</span>
									<span class="apl-lb w-100 bg-3 center">Potência</span>
									<span class="apl-lb w-100 bg-2 white lpad">'.$dPotencia.'</span>
									<span class="apl-lb w-128 bg-3 center">Combustível</span>
									<span class="apl-lb w-150 bg-2 white lpad">'.$dCombustivel.'</span>
									<span class="apl-lb w-130 bg-imp center">Transformação</span>
									<div class="apl-lb w-130 bg-2">
										<a class="rbw'.(int)in_array($dTransformacao, array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dTransformacao, array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Detalhar Transformação</span>
								</div>
								<div class="apl-row apl-br apl-bl apl-bb">
									<span class="apl-textarea">'.nl2br($dTransformacao_detalhar).'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-300 bg-imp center">Garantia do(s) Veículo(s):</span>
									<div class="apl-lb bg-3" style="width:656px;">
										<a class="rb'.(int)in_array($dGarantia, array(1)).'" style="left:40px;top:7px;">Garantia Padrão</a>
										<a class="rb'.(int)in_array($dGarantia, array(2)).'" style="left:300px;top:7px;">Garantia Diferenciada</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Acessórios</span>
								</div>
								'.$tAcessorios.'
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Informações da Proposta</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-130 bg-3 center">Preço Público</span>
									<span class="apl-lb w-160 bg-2 white lpad">'.$dPreco_publico_vw.'</span>
									<span class="apl-lb w-100 bg-3 center">Desconto</span>
									<span class="apl-lb w-118 bg-2 white lpad">'.$dV1_desconto.'</span>
									<span class="apl-lb w-128 bg-3 center">Repasse (%)</span>
									<span class="apl-lb w-60 bg-2 white lpad">'.$dRepasse_concessionario.'</span>
									<span class="apl-lb w-130 bg-3 center">DN de Venda</span>
									<span class="apl-lb bg-2 white lpad" style="width:106px;">'.$dDn_venda.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-130 bg-3 center">Preço Máximo</span>
									<span class="apl-lb w-160 bg-2 white lpad">'.$dV1_preco_maximo.'</span>
									<span class="apl-lb w-160 bg-3 center">Validade da Proposta</span>
									<span class="apl-lb w-252 bg-2 white lpad">'.$dValidade_proposta.'</span>
									<span class="apl-lb w-130 bg-3 center">DN de Entrega</span>
									<span class="apl-lb bg-2 white lpad" style="width:106px;">'.$dDn_entrega.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-130 bg-3 center">Prazo de Entrega</span>
									<span class="apl-lb w-160 bg-2 white lpad">'.$dPrazo_entrega.'</span>
									<span class="apl-lb w-160 bg-3 center">Prazo de Pagamento</span>
									<span class="apl-lb w-252 bg-2 white lpad">'.$dV1_prazo_pagamento.'</span>
									<span class="apl-lb w-130 bg-3 center">Quantidade</span>
									<span class="apl-lb bg-2 white lpad" style="width:106px;">'.$dQuantidade_veiculos.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-130 bg-3 center">AVE</span>
									<span class="apl-lb w-160 bg-2 white lpad">'.$dAve.'</span>
									<span class="apl-lb w-100 bg-3 center">Nº do Pool</span>
									<span class="apl-lb bg-2 white lpad" style="width:554px;">'.$dV1_numero_pool.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Origem da Verba</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 41px;">
									<a class="rb'.(int)in_array($dOrigem_verba, array(1)).'" style="left:40px;top:12px;">Federal</a>
									<a class="rb'.(int)in_array($dOrigem_verba, array(2)).'" style="left:250px;top:12px;">Estadual</a>
									<a class="rb'.(int)in_array($dOrigem_verba, array(3)).'" style="left:460px;top:12px;">Municipal</a>
									<a class="rb'.(int)in_array($dOrigem_verba, array(4)).'" style="left:670px;top:12px;">Convenio</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Isenções de Impostos do Órgão</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:106px;">
									<a class="rb'.(int)in_array($dIsencao_impostos, array(1)).'" style="left:40px;top:14px;">Não possui isenção</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(2)).'" style="left:250px;top:14px;">IPI</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(3)).'" style="left:460px;top:14px;">ICMS Substituto</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(4)).'" style="left:40px;bottom:43px;">IPI + ICMS</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(5)).'" style="left:250px;bottom:43px;">ICMS</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(6)).'" style="left:460px;bottom:43px;">IPI + ICMS Substituto</a>
									<span class="apl-lb italic" style="position: absolute; bottom: 6px; left: 40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via e-mail Lei/Decreto para que seja confirmado pelo Tributário.</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Observações Gerais</span>
								</div>
								<div class="apl-row apl-br apl-bl apl-bb">
									<span class="apl-textarea">'.nl2br($dObservacoes).'</span>
								</div>
								<div class="apl-row" style="text-align: left; font-size: 11px; padding: 0 40px;">APL preenchida e enviada em <span class="bold">'.mysqlToBr(substr($dEnviou_data_hora,0,10)).'</span> às <span class="bold gray-88">'.substr($dEnviou_data_hora,11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$dEnviou_ip.'</span></div>';
						else if ($dVersao == 2)
							$oAPL .= '
								<div class="apl-row" style="margin: '.$m.'px 0 4px 0; text-align: center;">
									<span><a class="bold">APL do DN:</a> '.$dDn_nome.'&nbsp;&nbsp;&nbsp;<a class="bold">Licitação:</a> '.$dId_licitacao.'&nbsp;&nbsp;&nbsp;<a class="bold">Lote:</a> '.$dLote.'&nbsp;&nbsp;&nbsp;<a class="bold">Item:</a> '.$dItem.'</span>
								</div>
								<div class="apl-row apl-bt apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">Nome do Órgão</span>
									<span class="apl-lb bg-2 white lpad" style="width:388px;">'.$dNome_orgao.'</span>
									<span class="apl-lb bg-1 white center" style="width:182px;">Data da Licitação</span>
									<span class="apl-lb bg-2 white lpad" style="width:134px;">'.$dData_licitacao.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">Licitação Nro.</span>
									<span class="apl-lb bg-2 white lpad" style="width:388px;">'.$dId_licitacao.'</span>
									<span class="apl-lb bg-imp center" style="width:182px;">Registro de Preços</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw'.(int)in_array($dRegistro_precos,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dRegistro_precos,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">CNPJ de Faturamento</span>
									<span class="apl-lb bg-2 white lpad" style="width:388px;">'.$dCnpj_faturamento.'</span>
									<span class="apl-lb bg-1 white center" style="width:182px;">Estado</span>
									<span class="apl-lb bg-2 white lpad" style="width:134px;">'.$dEstado.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Modalidade da Venda</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:76px;">
									<a class="rb'.(int)in_array($dModalidade_venda, array(1)).'" style="left:40px;top:14px;">Compra Direta</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(2)).'" style="left:250px;top:14px;">Tomada de Preços</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(3)).'" style="left:460px;top:14px;">Pregão Presencial</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(4)).'" style="left:670px;top:14px;">Adesão a Registro de Preços</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(5)).'" style="left:40px;bottom:13px;">Carta Convite</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(6)).'" style="left:250px;bottom:13px;">Concorrência</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(7)).'" style="left:460px;bottom:13px;">Pregão Eletrônico</a>
									<a class="rb'.(int)in_array($dModalidade_venda, array(8)).'" style="left:670px;bottom:13px;">Aditivo Contratual</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">Site Pregão Eletrônico</span>
									<span class="apl-lb bg-3 lpad" style="width:388px;">'.$dSite_pregao_eletronico.'</span>
									<span class="apl-lb bg-1 white center" style="width:182px;">Nº da Licitação</span>
									<span class="apl-lb bg-3 lpad" style="width:134px;">'.$dNumero_licitacao.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center italic">Dados do Participante</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:274px;">Nome</span>
									<span class="apl-lb bg-3 lpad" style="width:434px;">'.$dParticipante_nome.'</span>
									<span class="apl-lb bg-1 white center" style="width:102px;">CPF</span>
									<span class="apl-lb bg-3 lpad" style="width:134px;">'.$dParticipante_cpf.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:274px;">RG</span>
									<span class="apl-lb bg-3 lpad" style="width:434px;">'.$dParticipante_rg.'</span>
									<span class="apl-lb bg-1 white center" style="width:102px;">Telefone</span>
									<span class="apl-lb bg-3 lpad" style="width:134px;">'.$dParticipante_telefone.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:274px;">Endereço p/ Envio da Documentação</span>
									<span class="apl-lb bg-3 lpad" style="width:676px;">'.$dParticipante_endereco.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:132px;">Model Code</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dModel_code.'</span>
									<span class="apl-lb bg-3 center" style="width:102px;">Cor</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dCor.'</span>
									<span class="apl-lb bg-3 center" style="width:130px;">Ano/Modelo</span>
									<span class="apl-lb bg-2 white lpad" style="width:322px;">'.$dAno_modelo.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:132px;">Motorização</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dMotorizacao.'</span>
									<span class="apl-lb bg-3 center" style="width:102px;">Potência</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dPotencia.'</span>
									<span class="apl-lb bg-3 center" style="width:130px;">Combustível</span>
									<span class="apl-lb bg-2 white lpad" style="width:322px;">'.$dCombustivel.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:132px;">Opcionais (PR\'s)</span>
									<span class="apl-lb bg-2 white lpad" style="width:460px;">'.$dOpcionais_pr.'</span>
									<span class="apl-lb bg-3 center" style="width:218px;">Eficiência Energética CONPET</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw'.(int)in_array($dEficiencia_energetica,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dEficiencia_energetica,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Transformação (Custo VW)</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-imp center" style="width:132px;">Transformação</span>
									<div class="apl-lb bg-2" style="width:132px;">
										<a class="rbw'.(int)in_array($dTransformacao,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dTransformacao,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:102px;">Tipo</span>
									<span class="apl-lb bg-2 white lpad" style="width:312px;">'.$dTransformacao_tipo.'</span>
									<span class="apl-lb bg-3 center" style="width:132px;">Protótipo</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw'.(int)in_array($dTransformacao_prototipo,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dTransformacao_prototipo,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bl bg-3">
									<span class="apl-lb w-100p italic" style="margin-left:40px;">Detalhar transformação</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3">
									<span class="apl-textarea" style="margin-top:0;">'.nl2br($dTransformacao_detalhar).'</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3" style="padding-top:10px;padding-bottom:20px;">
									<span class="apl-cl-label">Check List Transformação AMAROK</span>
									<div class="apl-upl-ready">
										<span>'.str_replace(' ','&nbsp;',$amarok_upload_anexo).'</span>
									</div>
								</div>
								<div class="apl-row apl-bt apl-br apl-bb apl-bl bg-1 white center">
									<div style="display:inline-block;overflow:hidden;">
										<span class="apl-lb">Acessórios (Custo pago pelo DN)</span>
										<a class="rbw'.(int)in_array($dAcessorios,array(1)).'" style="position:relative;float:left;margin:8px 0 0 40px;">Sim</a>
										<a class="rbw'.(int)in_array($dAcessorios,array(2)).'" style="position:relative;float:left;margin:8px 0 0 14px;">Não</a>
									</div>
								</div>
								'.$tAcessorios.'
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:184px;">Emplacamento</span>
									<div class="apl-lb bg-2" style="width:132px;">
										<a class="rbw'.(int)in_array($dEmplacamento,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dEmplacamento,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:184px;">Licenciamento</span>
									<div class="apl-lb bg-2" style="width:132px;">
										<a class="rbw'.(int)in_array($dLicenciamento,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dLicenciamento,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:184px;">IPVA</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw'.(int)in_array($dIpva,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dIpva,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Garantia e Revisões</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-imp center" style="width:302px;">Garantia</span>
									<div class="apl-lb bg-2" style="width:654px;">
										<a class="rbw'.(int)in_array($dGarantia,array(1)).'" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Padrão</a>
										<a class="rbw'.(int)in_array($dGarantia,array(2)).'" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Diferenciada</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:302px;">Prazo Garantia <a class="italic">(12/24/36 Meses)</a></span>
									<span class="apl-lb bg-2 white lpad" style="width:324px;">'.$dGarantia_prazo_str.'</span>
									<span class="apl-lb bg-3 center" style="width:184px;">Revisão Embarcada</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw'.(int)in_array($dRevisao_embarcada,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dRevisao_embarcada,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:302px;">Quantidade de Revisões Inclusas</span>
									<span class="apl-lb bg-2 white lpad" style="width:78px;">'.$dQuantidade_revisoes_inclusas.'</span>
									<span class="apl-lb bg-3 center" style="width:184px;">Limite de KM</span>
									<div class="apl-lb bg-2" style="width:152px;">
										<a class="rbw'.(int)in_array($dLimite_km,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dLimite_km,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
									'.$kmkm.'
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-3 center" style="color:#666666;"><a style="font-weight:bold;color:#ff0000;">Atenção:</a> AMAROK LIMITE 100 MIL KM&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GARANTIA PADRÃO 3 ANOS</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Informações da Proposta</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">Preço Público VW</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dPreco_publico_vw.'</span>
									<span class="apl-lb bg-3 center" style="width:152px;">Preço Ref. Edital</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dPreco_ref_edital.'</span>
									<span class="apl-lb bg-3 center" style="width:194px;">Quantidade de Veículos</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;">'.$dQuantidade_veiculos.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">DN de Venda</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_venda.'</span>
									<span class="apl-lb bg-3 center" style="width:152px;">Estado DN de Venda</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_venda_estado.'</span>
									<span class="apl-lb bg-3 center" style="width:194px;">Repasse Concessionário (%)</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;">'.$dRepasse_concessionario.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">DN de Entrega</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_entrega.'</span>
									<span class="apl-lb bg-3 center" style="width:152px;">Estado DN de Venda</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_entrega_estado.'</span>
									<span class="apl-lb bg-3 center" style="width:194px;">Prazo Entrega</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;">'.$dPrazo_entrega.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">Validade da Proposta</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dValidade_proposta.'</span>
									<span class="apl-lb bg-3 center" style="width:152px;">Vigência do Contrato</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dVigencia_contrato.'</span>
									<span class="apl-lb bg-3 center" style="width:194px;">Prazo Pagamento</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;">'.$dPrazo_pagamento.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:244px;">AVE (enviar eletronic. no Sivolks)</span>
									<span class="apl-lb bg-2 white lpad" style="width:264px;">'.$dAve.'</span>
									<span class="apl-lb bg-3 center" style="width:284px;">Multas e Sanções – Indicar ítem do Edital</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;">'.$dMultas_sansoes.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:244px;">Garantia de Contrato</span>
									<div class="apl-lb bg-2" style="width:270px;">
										<a class="rbw'.(int)in_array($dGarantia_contrato,array(1)).'" style="left:14px;top:7px;">Sim</a>
										<a class="rbw'.(int)in_array($dGarantia_contrato,array(2)).'" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:72px;">Prazo</span>
									<span class="apl-lb bg-2 white lpad" style="width:134px;">'.$dPrazo.'</span>
									<span class="apl-lb bg-3 center" style="width:72px;">Valor</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;">'.$dValor.'</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Origem da Verba</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3" style="height:41px;">
									<a class="rb'.(int)in_array($dOrigem_verba, array(1)).'" style="left:40px;top:12px;">Federal</a>
									<a class="rb'.(int)in_array($dOrigem_verba, array(2)).'" style="left:250px;top:12px;">Estadual</a>
									<a class="rb'.(int)in_array($dOrigem_verba, array(3)).'" style="left:460px;top:12px;">Municipal</a>
									<a class="rb'.(int)in_array($dOrigem_verba, array(4)).'" style="left:670px;top:12px;">Convenio</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-2" style="height:41px;">
									<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(1)).'" style="left:40px;top:12px;">A Vista</a>
									<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(2)).'" style="left:250px;top:12px;">A Prazo</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Isenções de Impostos do Órgão</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3" style="height:80px;">
									<a class="rb'.(int)in_array($dIsencao_impostos, array(1)).'" style="left:40px;top:14px;">Não possui isenção</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(2)).'" style="left:250px;top:14px;">IPI</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(3)).'" style="left:460px;top:14px;">ICMS Substituto</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(4)).'" style="left:40px;top:46px;">IPI + ICMS</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(5)).'" style="left:250px;top:46px;">ICMS</a>
									<a class="rb'.(int)in_array($dIsencao_impostos, array(6)).'" style="left:460px;top:46px;">IPI + ICMS Substituto</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-3">
									<span class="apl-lb w-100p italic" style="margin-left:40px;">Indicar item do edital ou Lei que confirme a marcação acima</span>
									<span class="apl-textarea" style="margin-top:0;">'.nl2br($dImposto_indicar).'</span>
									<span class="apl-lb w-100p italic" style="margin-left:40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via chat GELIC Lei/Decreto para que seja confirmado pelo Tributário.</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Observações Gerais</span>
								</div>
								<div class="apl-row apl-br apl-bl apl-bb">
									<span class="apl-textarea">'.nl2br($dObservacoes).'</span>
								</div>
								<div class="apl-row" style="text-align: left; font-size: 11px; padding: 0 40px;">APL preenchida e enviada em <span class="bold">'.mysqlToBr(substr($dEnviou_data_hora,0,10)).'</span> às <span class="bold gray-88">'.substr($dEnviou_data_hora,11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$dEnviou_ip.'</span></div>';

						if ($pId_licitacao == 1010)
							$noop = '
								<div class="apl-row apl-bt apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">Nome do Órgão</span>
									<span class="apl-lb bg-2 white lpad" style="width:388px;"></span>
									<span class="apl-lb bg-1 white center" style="width:182px;">Data da Licitação</span>
									<span class="apl-lb bg-2 white lpad" style="width:134px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">Licitação Nro.</span>
									<span class="apl-lb bg-2 white lpad" style="width:388px;"></span>
									<span class="apl-lb bg-imp center" style="width:182px;">Registro de Preços</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">CNPJ de Faturamento</span>
									<span class="apl-lb bg-2 white lpad" style="width:388px;"></span>
									<span class="apl-lb bg-1 white center" style="width:182px;">Estado</span>
									<span class="apl-lb bg-2 white lpad" style="width:134px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Modalidade da Venda</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:76px;">
									<a class="rb0" style="left:40px;top:14px;">Compra Direta</a>
									<a class="rb0" style="left:250px;top:14px;">Tomada de Preços</a>
									<a class="rb0" style="left:460px;top:14px;">Pregão Presencial</a>
									<a class="rb0" style="left:670px;top:14px;">Adesão a Registro de Preços</a>
									<a class="rb0" style="left:40px;bottom:13px;">Carta Convite</a>
									<a class="rb0" style="left:250px;bottom:13px;">Concorrência</a>
									<a class="rb0" style="left:460px;bottom:13px;">Pregão Eletrônico</a>
									<a class="rb0" style="left:670px;bottom:13px;">Aditivo Contratual</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:240px;">Site Pregão Eletrônico</span>
									<span class="apl-lb bg-3 lpad" style="width:388px;"></span>
									<span class="apl-lb bg-1 white center" style="width:182px;">Nº da Licitação</span>
									<span class="apl-lb bg-3 lpad" style="width:134px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center italic">Dados do Participante</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:274px;">Nome</span>
									<span class="apl-lb bg-3 lpad" style="width:434px;"></span>
									<span class="apl-lb bg-1 white center" style="width:102px;">CPF</span>
									<span class="apl-lb bg-3 lpad" style="width:134px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:274px;">RG</span>
									<span class="apl-lb bg-3 lpad" style="width:434px;"></span>
									<span class="apl-lb bg-1 white center" style="width:102px;">Telefone</span>
									<span class="apl-lb bg-3 lpad" style="width:134px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-1 white center" style="width:274px;">Endereço p/ Envio da Documentação</span>
									<span class="apl-lb bg-3 lpad" style="width:676px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:132px;">Model Code</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;"></span>
									<span class="apl-lb bg-3 center" style="width:102px;">Cor</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;"></span>
									<span class="apl-lb bg-3 center" style="width:130px;">Ano/Modelo</span>
									<span class="apl-lb bg-2 white lpad" style="width:322px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:132px;">Motorização</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;"></span>
									<span class="apl-lb bg-3 center" style="width:102px;">Potência</span>
									<span class="apl-lb bg-2 white lpad" style="width:126px;"></span>
									<span class="apl-lb bg-3 center" style="width:130px;">Combustível</span>
									<span class="apl-lb bg-2 white lpad" style="width:322px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:132px;">Opcionais (PR\'s)</span>
									<span class="apl-lb bg-2 white lpad" style="width:460px;"></span>
									<span class="apl-lb bg-3 center" style="width:218px;">Eficiência Energética CONPET</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Transformação (Custo VW)</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-imp center" style="width:132px;">Transformação</span>
									<div class="apl-lb bg-2" style="width:132px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:102px;">Tipo</span>
									<span class="apl-lb bg-2 white lpad" style="width:312px;"></span>
									<span class="apl-lb bg-3 center" style="width:132px;">Protótipo</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bl bg-3">
									<span class="apl-lb w-100p italic" style="margin-left:40px;">Detalhar transformação</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3">
									<span class="apl-textarea" style="margin-top:0;"></span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3" style="padding-top:10px;padding-bottom:20px;">
									<span class="apl-cl-label">Check List Transformação AMAROK</span>
									<div class="apl-upl-ready">
										<span></span>
									</div>
								</div>
								<div class="apl-row apl-bt apl-br apl-bb apl-bl bg-1 white center">
									<div style="display:inline-block;overflow:hidden;">
										<span class="apl-lb">Acessórios (Custo pago pelo DN)</span>
										<a class="rbw0" style="position:relative;float:left;margin:8px 0 0 40px;">Sim</a>
										<a class="rbw0" style="position:relative;float:left;margin:8px 0 0 14px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:184px;">Emplacamento</span>
									<div class="apl-lb bg-2" style="width:132px;">
										<a class="rbw" style="left:14px;top:7px;">Sim</a>
										<a class="rbw" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:184px;">Licenciamento</span>
									<div class="apl-lb bg-2" style="width:132px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:184px;">IPVA</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Garantia e Revisões</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-imp center" style="width:302px;">Garantia</span>
									<div class="apl-lb bg-2" style="width:654px;">
										<a class="rbw0" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Padrão</a>
										<a class="rbw0" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Diferenciada</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:302px;">Prazo Garantia <a class="italic">(12/24/36 Meses)</a></span>
									<span class="apl-lb bg-2 white lpad" style="width:324px;"></span>
									<span class="apl-lb bg-3 center" style="width:184px;">Revisão Embarcada</span>
									<div class="apl-lb bg-2" style="width:140px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:302px;">Quantidade de Revisões Inclusas</span>
									<span class="apl-lb bg-2 white lpad" style="width:78px;"></span>
									<span class="apl-lb bg-3 center" style="width:184px;">Limite de KM</span>
									<div class="apl-lb bg-2" style="width:152px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-3 center" style="color:#666666;"><a style="font-weight:bold;color:#ff0000;">Atenção:</a> AMAROK LIMITE 100 MIL KM&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GARANTIA PADRÃO 3 ANOS</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Informações da Proposta</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">Preço Público VW</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:152px;">Preço Ref. Edital</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:194px;">Quantidade de Veículos</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">DN de Venda</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:152px;">Estado DN de Venda</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:194px;">Repasse Concessionário (%)</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">DN de Entrega</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:152px;">Estado DN de Venda</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:194px;">Prazo Entrega</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:152px;">Validade da Proposta</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:152px;">Vigência do Contrato</span>
									<span class="apl-lb bg-2 white lpad" style="width:144px;"></span>
									<span class="apl-lb bg-3 center" style="width:194px;">Prazo Pagamento</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:244px;">AVE (enviar eletronic. no Sivolks)</span>
									<span class="apl-lb bg-2 white lpad" style="width:264px;"></span>
									<span class="apl-lb bg-3 center" style="width:284px;">Multas e Sanções – Indicar ítem do Edital</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb bg-3 center" style="width:244px;">Garantia de Contrato</span>
									<div class="apl-lb bg-2" style="width:270px;">
										<a class="rbw0" style="left:14px;top:7px;">Sim</a>
										<a class="rbw0" style="left:70px;top:7px;">Não</a>
									</div>
									<span class="apl-lb bg-3 center" style="width:72px;">Prazo</span>
									<span class="apl-lb bg-2 white lpad" style="width:134px;"></span>
									<span class="apl-lb bg-3 center" style="width:72px;">Valor</span>
									<span class="apl-lb bg-2 white lpad" style="width:152px;"></span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Origem da Verba</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3" style="height:41px;">
									<a class="rb0" style="left:40px;top:12px;">Federal</a>
									<a class="rb0" style="left:250px;top:12px;">Estadual</a>
									<a class="rb0" style="left:460px;top:12px;">Municipal</a>
									<a class="rb0" style="left:670px;top:12px;">Convenio</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-2" style="height:41px;">
									<a class="rbw0" style="left:40px;top:12px;">A Vista</a>
									<a class="rbw0" style="left:250px;top:12px;">A Prazo</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Isenções de Impostos do Órgão</span>
								</div>
								<div class="apl-row apl-br apl-bl bg-3" style="height:80px;">
									<a class="rb0" style="left:40px;top:14px;">Não possui isenção</a>
									<a class="rb0" style="left:250px;top:14px;">IPI</a>
									<a class="rb0" style="left:460px;top:14px;">ICMS Substituto</a>
									<a class="rb0" style="left:40px;top:46px;">IPI + ICMS</a>
									<a class="rb0" style="left:250px;top:46px;">ICMS</a>
									<a class="rb0" style="left:460px;top:46px;">IPI + ICMS Substituto</a>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl bg-3">
									<span class="apl-lb w-100p italic" style="margin-left:40px;">Indicar item do edital ou Lei que confirme a marcação acima</span>
									<span class="apl-textarea" style="margin-top:0;"></span>
									<span class="apl-lb w-100p italic" style="margin-left:40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via chat GELIC Lei/Decreto para que seja confirmado pelo Tributário.</span>
								</div>
								<div class="apl-row apl-br apl-bb apl-bl">
									<span class="apl-lb w-100p bg-1 white center">Observações Gerais</span>
								</div>
								<div class="apl-row apl-br apl-bl apl-bb">
									<span class="apl-textarea"></span>
								</div>
								<div class="apl-row" style="text-align: left; font-size: 11px; padding: 0 40px;">APL preenchida e enviada em <span class="bold"></span> às <span class="bold gray-88"></span> por <span class="bold italic"></span> de <span class="red"></span></div>';

						$oInfo = '';
						$aPlanta = array("","TBT/SP (0024-46)","SBC/SP","SJP/PR (0103-84)");
						$db->query("
	SELECT 
		his.tipo,
		his.texto,
		his.data_hora,
		his.ip, 
		cli.nome,
		apr.ave,
		apr.quantidade,
		apr.model_code,
		apr.cor,
		apr.opcionais_pr,
		apr.preco_publico,
		apr.prazo_de_entrega,
		apr.planta,
		apr.desconto_vw,
		apr.comissao_dn,
		apr.valor_da_transformacao,
		apr.nome_arquivo,
		apr.arquivo,
		item.item,
		lote.lote,
		(SELECT descricao FROM gelic_motivos WHERE id = his.id_valor_1) AS motivo, 
		(SELECT descricao FROM gelic_motivos WHERE id = his.id_valor_2) AS submotivo
	FROM 
		gelic_licitacoes_apl_historico AS his
		INNER JOIN gelic_clientes AS cli ON cli.id = his.id_cliente
		INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ".$aApl[$i]."
		INNER JOIN gelic_licitacoes_itens AS item ON item.id = apl.id_item
		INNER JOIN gelic_licitacoes_lotes AS lote ON lote.id = item.id_lote
		LEFT JOIN gelic_licitacoes_apl_aprovadas AS apr ON apr.id_apl_historico = his.id AND apr.ativo = 1
	WHERE 
		his.id_apl = ".$aApl[$i]." AND
		his.tipo IN (2,4)
	ORDER BY 
		his.id 
	DESC LIMIT 1",2);
						if ($db->nextRecord(2))
						{
							if ($db->f("tipo",2) == 2)
							{
								$apr_tbl = '';

								if (strlen($db->f("ave",2)) > 0)
								{
									if ($db->f("valor_da_transformacao",2) > 0)
										$valor_transf = '<tr><td class="apr-tbl-lb">VALOR DA TRANSFORMAÇÃO:</td><td class="apr-tbl-vl">R$ '.number_format($db->f("valor_da_transformacao",2),2,',','.').'</td></tr>';
									else
										$valor_transf = '';

									if (strlen($db->f("arquivo",2)) > 0)
										$anexo = '<tr><td class="apr-tbl-lb">ANEXO:</td><td><a class="ablue" href="arquivos/apr/'.$db->f("arquivo",2).'" target="_blank">'.utf8_encode($db->f("nome_arquivo",2)).'</a></td></tr>';
									else
										$anexo = '';

									$apr_tbl = '<br><br><table class="apr-tbl">
						<tr>
							<td class="apr-tbl-lb">LOTE:</td>
							<td class="apr-tbl-vl">'.utf8_encode($db->f("lote",2)).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">ITEM:</td>
							<td class="apr-tbl-vl">'.utf8_encode($db->f("item",2)).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">AVE:</td>
							<td class="apr-tbl-vl">'.utf8_encode($db->f("ave",2)).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">QUANTIDADE:</td>
							<td class="apr-tbl-vl">'.$db->f("quantidade",2).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">MODEL CODE:</td>
							<td class="apr-tbl-vl">'.utf8_encode($db->f("model_code",2)).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">COR:</td>
							<td class="apr-tbl-vl">'.utf8_encode($db->f("cor",2)).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">OPCIONAIS (PR\'s):</td>
							<td class="apr-tbl-vl">'.utf8_encode($db->f("opcionais_pr",2)).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">PREÇO PÚBLICO:</td>
							<td class="apr-tbl-vl">R$ '.number_format($db->f("preco_publico",2),2,',','.').'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">PRAZO DE ENTREGA:</td>
							<td class="apr-tbl-vl">'.$db->f("prazo_de_entrega",2).'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">PLANTA:</td>
							<td class="apr-tbl-vl">'.$aPlanta[$db->f("planta",2)].'</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">DESCONTO VW:</td>
							<td class="apr-tbl-vl">'.number_format($db->f("desconto_vw",2),2,',','.').' %</td>
						</tr>
						<tr>
							<td class="apr-tbl-lb">COMISSÃO DN:</td>
							<td class="apr-tbl-vl">'.number_format($db->f("comissao_dn",2),2,',','.').' %</td>
						</tr>
						'.$valor_transf.$anexo.'
					</table>';
								}


								$oInfo .= '<div class="apl-row bold green t20" style="margin: 20px 0 0 0; padding: 0 40px; text-align: left; line-height: normal;">Aprovada!</div>';
								$oInfo .= '<div class="apl-row" style="text-align: left; font-size: 11px; padding: 0 40px;">APL aprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",2),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",2),11).'</span> por <span class="bold italic">'.utf8_encode($db->f("nome",2)).'</span> de <span class="red">'.$db->f("ip",2).'</span>'.$apr_tbl.'{{TEXT}}</div>';
							}
							else if ($db->f("tipo",2) == 4)
							{
								$oInfo .= '<div class="apl-row bold red t20" style="margin: 20px 0 0 0; padding: 0 40px; text-align: left; line-height: normal;">Reprovada.</div>';
								$oInfo .= '<div class="apl-row" style="text-align: left; font-size: 11px; padding: 0 40px;">APL reprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",2),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",2),11).'</span> por <span class="bold italic">'.utf8_encode($db->f("nome",2)).'</span> de <span class="red">'.$db->f("ip",2).'</span>{{TEXT}}</div>';
							}

							$t = '';
							$dTexto = '';
							if (strlen($db->f("texto",2)) > 0)
								$dTexto = utf8_encode($db->f("texto",2));

							if ($db->f("tipo",2) == 2 && strlen($dTexto) > 0)
								$t .= '<br><br><span class="bold">Observações:</span><br>'.nl2br($dTexto);
							else if ($db->f("tipo",2) == 4)
							{
								$t .= '<br><br><span class="bold">Motivo:</span><br><span class="gray-88">'.utf8_encode($db->f("motivo",2)).'</span>';
								if (strlen($db->f("submotivo",2)))
									$t .= ' <span class="gray-88 italic">('.utf8_encode($db->f("submotivo",2)).')</span>';

								if (strlen($dTexto) > 0)
									$t .= '<br><br><span class="bold">Observações:</span><br>'.nl2br($dTexto);
							}
							$oInfo = str_replace("{{TEXT}}", $t, $oInfo);
						}
						$oInfo .= '<div style="height:40px; border-bottom: 1px solid #666666;"></div>';
						$oAPL .= $oInfo;

					}
				}
				$oItens .= $oAPL;
			}
			$oItens .= '</div>';
		}

		$tTmp = str_replace("{{TOTAL_ITENS}}", $total_itens, $tTmp);
		$tTmp = str_replace("{{ITENS}}", $oItens, $tTmp);

		//*********************************************************************************************
		//*********************************************************************************************
		//*********************************************************************************************








		//*********************************************************************************************
		//***************************************** HISTORICO *****************************************
		//*********************************************************************************************
		$oHistorico = '<div style="height:40px;"></div>';
		$db->query("
SELECT 
	his.id,
	his.tipo,
	his.id_sender,
	his.id_valor_1,
	his.id_valor_2,
	his.data_hora,
	his.texto,
	his.nome_arquivo,
	his.arquivo,
	his.id_apl,
	usr.nome AS usuario_admin,
	cli.tipo AS tipo_cliente,
	cli.id_parent,
	cli.nome AS nome_cliente,
	clidn.nome AS nome_dn,
	vcli.id_parent AS vid_parent,
	vcli.nome AS vnome_cliente,
	vclidn.nome AS vnome_dn,
	mot.descricao AS motivo,
	smot.descricao AS submotivo,
	(SELECT id FROM gelic_licitacoes_apl_historico WHERE id = his.id_valor_1) AS id_ahis
FROM 
	gelic_historico AS his 
	LEFT JOIN gelic_admin_usuarios AS usr ON usr.id = his.id_sender

	LEFT JOIN gelic_clientes AS cli ON cli.id = his.id_sender
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent

	LEFT JOIN gelic_clientes AS vcli ON vcli.id = his.id_valor_1
	LEFT JOIN gelic_clientes AS vclidn ON vclidn.id = vcli.id_parent

	LEFT JOIN gelic_motivos AS mot ON mot.id = his.id_valor_1
	LEFT JOIN gelic_motivos AS smot ON smot.id = his.id_valor_2

    LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id = his.id_apl
WHERE 
	his.id_licitacao = $pId_licitacao AND
 	his.tipo IN (1,2,13,22,23,31,32,33,34,39,41,42,43,44,45)
ORDER BY 
	his.id");
		while ($db->nextRecord())
		{
			if ($db->f("tipo") == 1) //1: Mensagem admin para cliente
			{
				$oHistorico .= '<div class="fourcont">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt-fa">
						<p>'.utf8_encode($db->f("texto")).'</p>';

				if ($db->f("arquivo") != '')
				{
					$oHistorico .= '<p style="overflow: hidden; margin-top: 17px;">
						<span class="t13 gray-88 fl lh-22 italic">Arquivo:</span>
						<span class="t13 orange fl ml-8 lh-20" style="border: 1px solid #bebebe; padding: 0 10px; box-sizing: border-box; background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					</p>';
				}

				$oHistorico .= '</div></div>';
			}
			else if ($db->f("tipo") == 2) //2: Mensagem cliente para admin
			{
				if ($db->f("tipo_cliente") == 1)
					$dNome = 'BO: '.$db->f("nome_cliente");
				else
					$dNome = $db->f("nome_cliente");

				if ($db->f("id_parent") > 0)
					$dNome .= ' / '.$db->f("nome_dn");

				$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($dNome).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p>'.utf8_encode($db->f("texto")).'</p>';

				if ($db->f("arquivo") != '')
				{
					$oHistorico .= '<p style="overflow: hidden;">
						<span class="t13 gray-88 fl lh-22 italic">Arquivo:</span>
						<span class="t13 orange fl ml-8 lh-20" style="border: 1px solid #bebebe; padding: 0 10px; box-sizing: border-box; background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					</p>';
				}

				$oHistorico .= '</div></div>';
			}
			else if ($db->f("tipo") == 13) //13: Troca de fase
			{
				if ($db->f("id_valor_1") == 0 && $db->f("id_valor_2") == 1)
				{
					$oHistorico .= '<div class="fourcont bg-troca">
						<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
						<span class="day">'.timeAgo($db->f("data_hora")).'</span>
						<div class="txt">
							<p>
								<span class="bold cor-inicio">Início do prazo de 24hs para envio da APL exclusivo para ADVE/POOL</span>
							</p>
						</div>
					</div>';
				}
				else if ($db->f("id_valor_1") == 0 && $db->f("id_valor_2") == 2)
				{
					//pegar regiao
					$db->query("
	SELECT
		uf.regiao
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
	WHERE
		lic.id = $pId_licitacao",1);
					$db->nextRecord(1);

					$oHistorico .= '<div class="fourcont bg-troca">
						<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
						<span class="day">'.timeAgo($db->f("data_hora")).'</span>
						<div class="txt">
							<p>
								<span class="bold cor-inicio">Início do prazo de 24hs para envio da '.utf8_encode($db->f("regiao",1)).'</span>
							</p>
						</div>
					</div>';
				}
				else if ($db->f("id_valor_1") == 1 && $db->f("id_valor_2") == 2)
				{
					//pegar regiao
					$db->query("
	SELECT
		uf.regiao
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
	WHERE
		lic.id = $pId_licitacao",1);
					$db->nextRecord(1);

					$oHistorico .= '<div class="fourcont bg-troca">
						<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
						<span class="day">'.timeAgo($db->f("data_hora")).'</span>
						<div class="txt">
							<p>
								<span class="bold cor-fim">Fim do prazo de envio ADVE/POOL</span> => <span class="bold cor-inicio">Início do prazo de 24hs para envio da '.utf8_encode($db->f("regiao",1)).'</span>
							</p>
						</div>
					</div>';
				}
				else if ($db->f("id_valor_1") == 2 && $db->f("id_valor_2") == 3)
				{
					//pegar regiao
					$db->query("
	SELECT
		uf.regiao
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
	WHERE
		lic.id = $pId_licitacao",1);
					$db->nextRecord(1);

					$oHistorico .= '<div class="fourcont bg-troca">
						<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
						<span class="day">'.timeAgo($db->f("data_hora")).'</span>
						<div class="txt">
							<p>
								<span class="bold cor-fim">Fim do prazo de envio da '.utf8_encode($db->f("regiao",1)).'</span> => <span class="bold cor-inicio">Início do prazo de envio (Brasil)</span>
							</p>
						</div>
					</div>';
				}
			}
			else if ($db->f("tipo") == 22) //22: Cliente não tem interesse
			{
				$dNome = $db->f("nome_cliente");

				if ($db->f("id_parent") > 0)
					$dNome .= ' / '.$db->f("nome_dn");

				$subm = '';
				if (strlen($db->f("submotivo")) > 0)
					$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

				$msg = '';
				if (strlen($db->f("texto")) > 0)
					$msg = '<br><span class="normal">'.nl2br(utf8_encode($db->f("texto"))).'</span>';

				$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($dNome).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="bold italic">Não tenho interesse em participar desta licitação.<br>
						Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.$msg.'</p>
					</div>';

				$oHistorico .= '</div>';
			}
			else if ($db->f("tipo") == 23) //23: Cliente não tem interesse (revertido)
			{
				$subm = '';
				if (strlen($db->f("submotivo")) > 0)
					$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

				$msg = '';
				if (strlen($db->f("texto")) > 0)
					$msg = '<br><span class="normal">'.nl2br(utf8_encode($db->f("texto"))).'</span>';

				$dNome = $db->f("nome_cliente");

				if ($db->f("id_parent") > 0)
					$dNome .= ' / '.$db->f("nome_dn");

				$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($dNome).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="bold italic">Não tenho interesse em participar desta licitação. <span style="font-weight:normal;color:#888888;">(revertido)</span><br>
						Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.$msg.'</p>
					</div>';

				$oHistorico .= '</div>';
			}
			else if ($db->f("tipo") == 31) //31: Admin encerrou licitação
			{
				$subm = '';
				if (strlen($db->f("submotivo")) > 0)
					$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

				$oHistorico .= '<div class="fourcont">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="bold italic">Licitação encerrada pela administração.<br>
						Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.'</p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 32) //32: Admin reverteu o encerramento da licitação
			{
				$oHistorico .= '<div class="fourcont">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="bold italic">Encerramento revertido.</p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 33) //33: Admin reverteu o desinteresse do cliente
			{
				$dNome = $db->f("vnome_cliente");
				if ($db->f("vid_parent") > 0)
					$dNome .= ' / '.$db->f("vnome_dn");			

				$oHistorico .= '<div class="fourcont">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="bold italic">Declínio de interesse revertido.<br>
						<span class="normal gray-88">'.utf8_encode($dNome).'</span></p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 34) //34: Admin prorrogou a abertura da licitação
			{
				$subm = '';
				if (strlen($db->f("submotivo")) > 0)
					$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

				$oHistorico .= '<div class="fourcont">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="bold italic" style="margin:0;">Licitação prorrogada.</p><p class="prrrr">'.utf8_encode(str_replace("{{MOT}}", '<span class="italic"><a class="bold">Motivo:</a> '.utf8_encode($db->f("motivo")).$subm.'</span>', $db->f("texto"))).'</p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 39) //39: Admin encerrou licitação (revertido)
			{
				$subm = '';
				if (strlen($db->f("submotivo")) > 0)
					$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

				$oHistorico .= '<div class="fourcont">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="bold italic">Licitação encerrada pela administração. <span style="font-weight:normal;color:#888888;">(revertido)</span><br>
						Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.'</p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 41) //41: APL Enviada
			{
				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

				$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span>LOTE: '.utf8_encode($db->f("lote",1)).'&nbsp;&nbsp;ITEM: '.utf8_encode($db->f("item",1)).'</p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p><span class="bold orange">APL enviada</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip",1).'</span></p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 42) //42: APL Aprovada
			{
				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';


				if ($sInside_tipo == 1) //BO
					$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
						<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
				else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
					$oHistorico .= '<div class="fourcont" style="position: relative;">
						<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

				$oHistorico .= '
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="t13" style="margin:0;"><span class="bold t-green">APL aprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
					</div>';

				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 41 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';


				$oHistorico .= '
					<div class="txt">
						<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL enviada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 43) //43: APL Reprovada
			{
				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

				if ($sInside_tipo == 1) //BO
					$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
						<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
				else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
					$oHistorico .= '<div class="fourcont" style="position: relative;">
						<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

				$oHistorico .= '
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="t13" style="margin:0;"><span class="bold t-red">APL reprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
					</div>';

				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 41 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

				$oHistorico .= '
					<div class="txt">
						<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL enviada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 44) //44: APL Aprovação Revertida
			{
				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	ahis.texto,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

				$dTexto = '';
				if (strlen($db->f("texto",1)) > 0)
				{
					$dTexto = $db->f("texto",1);

					if ($dTexto{0} == "{")
						$dTexto = substr($dTexto, strpos($dTexto,"}") + 1);

					if (strlen($dTexto) > 0)
						$dTexto = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($dTexto));
				}

				if ($sInside_tipo == 1) //BO
					$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
						<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
				else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
					$oHistorico .= '<div class="fourcont" style="position: relative;"><p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

				$oHistorico .= '
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="t13" style="margin:0;"><span class="bold t-blue">Aprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span>'.$dTexto.'</p>
					</div>';

				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 42 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

				$oHistorico .= '
					<div class="txt">
						<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL aprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
					</div>
				</div>';
			}
			else if ($db->f("tipo") == 45) //45: APL Reprovação Revertida
			{
				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	ahis.texto,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

				$dTexto = '';
				if (strlen($db->f("texto",1)) > 0)
				{
					$dTexto = $db->f("texto",1);

					if ($dTexto{0} == "{")
						$dTexto = substr($dTexto, strpos($dTexto,"}") + 1);

					if (strlen($dTexto) > 0)
						$dTexto = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($dTexto));
				}

				if ($sInside_tipo == 1) //BO
					$oHistorico .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
						<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
				else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
					$oHistorico .= '<div class="fourcont" style="position: relative;"><p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

				$oHistorico .= '
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p class="t13" style="margin:0;"><span class="bold t-blue">Reprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span>'.$dTexto.'</p>
					</div>';

				$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 43 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
				$db->nextRecord(1);
				$por = utf8_encode($db->f("nome",1));
				if ($db->f("id_parent",1) > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

				$oHistorico .= '
					<div class="txt">
						<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL reprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
					</div>
				</div>';
			}

		}


		//*********************************************************************************************
		//*********************************************************************************************
		//*********************************************************************************************


		$tHtml = '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Licitação ('.$pId_licitacao.')</title>
	<style>
		body { font-family: \'Trebuchet MS\',\'Tahoma\'; font-size: 14px; }

		.apl-row {
			position: relative;
			width: 100%;
			overflow: hidden;
			box-sizing: border-box;
			page-break-inside: avoid;
			}

		.apl-bt { border-top: 1px solid #666666; }
		.apl-br { border-right: 1px solid #666666; }
		.apl-bb { border-bottom: 1px solid #666666; }
		.apl-bl { border-left: 1px solid #666666; }

		.apl-lb {
			position: relative;
			display: inline-block;
			float: left;
			line-height: 30px;
			height: 30px;
			}

		.w-60 { width: 60px; }
		.w-98 { width: 98px; }
		.w-100 { width: 100px; }
		.w-118 { width: 118px; }
		.w-126 { width: 126px; }
		.w-128 { width: 128px; }
		.w-130 { width: 130px; }
		.w-136 { width: 136px; }
		.w-138 { width: 138px; }
		.w-144 { width: 144px; }
		.w-150 { width: 150px; }
		.w-160 { width: 160px; }
		.w-168 { width: 168px; }
		.w-180 { width: 180px; }
		.w-238 { width: 238px; }
		.w-252 { width: 252px; }
		.w-260 { width: 260px; }
		.w-300 { width: 300px; }
		.w-368 { width: 368px; }
		.w-372 { width: 372px; }
		.w-376 { width: 376px; }
		.w-388 { width: 388px; }
		.w-396 { width: 396px; }
		.w-410 { width: 410px; }
		.w-554 { width: 554px; }
		.w-576 { width: 576px; }
		.w-584 { width: 584px; }
		.w-636 { width: 636px; }
		.w-704 { width: 704px; }
		.w-844 { width: 844px; }		
		.w-850 { width: 850px; }
		.w-100p { width: 100%; }

		.bg-1 { background-color: #386eb1; }
		.bg-2 { background-color: #aaaaaa; }
		.bg-3 { background-color: #ffffff; }
		.bg-4 { background-color: #f1f1f1; }
		.bg-imp { background-color: #ee0000; color: #ffffff; font-weight: bold; }

		.white { color: #ffffff; }
		.red { color: #ff0000; }
		.green { color: #00b400; }
		.orange { color: #ff6600; }
		.blue { color: #1065bc; }
		.center { text-align: center; }
		.right { text-align: right; }
		.lpad { padding-left: 6px; }
		.rpad { padding-right: 6px; }
		.italic { font-style: italic; }
		.bold { font-weight: bold; }
		.gray-aa { color: #aaaaaa; }
		.gray-88 { color: #888888; }
		.t12 { font-size: 12px; }
		.t13 { font-size: 13px; }
		.t20 { font-size: 20px; }
		.fl { float: left; }
		.fr { float: right; }
		.ml-8 { margin-left: 8px; }
		.ml-10 { margin-left: 10px; }
		.lh-17 { line-height: 17px; }
		.lh-20 { line-height: 20px; }
		.lh-22 { line-height: 22px; }
		.normal { font-weight: normal; font-style: normal; }

		.cb0 {
			position: absolute;
			height: 17px;
			background-image: url(\'../img/cb0.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.cb1 {
			position: absolute;
			height: 17px;
			background-image: url(\'../img/cb1.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rbw0 {
			position: absolute;
			height: 17px;
			background-image: url(\'../img/rbw0.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #f1f1f1;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rbw1 {
			position: absolute;
			height: 17px;
			background-image: url(\'../img/rbw1.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #f1f1f1;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rb0 {
			position: absolute;
			height: 17px;
			background-image: url(\'../img/rb0.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rb1 {
			position: absolute;
			height: 17px;
			background-image: url(\'../img/rb1.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.apl-textarea {
			float: left;
			clear: both;
			margin: 10px 0 10px 40px;
			box-sizing: border-box;
			width: 868px;
			border: 1px dotted #cccccc;
			padding: 6px;
			}

		.apl-cl-label {
			clear: both;
			float: left;
			margin-left: 40px;
			background-color: #386eb1;
			color: #ffffff;
			line-height: 26px;
			padding: 0 10px;
			}

		.apl-upl-ready {
			position: relative;
			clear: both;
			margin-left: 40px;
			width: 868px;
			height: 38px;
			border: 1px solid #dddddd;
			box-sizing: border-box;
			}

		.apl-upl-ready > span {
			float: left;
			line-height: 36px;
			color: #888888;
			font-style: italic;
			margin-left: 10px;
			}

		.apl-upl-ready > span > span {
			color: #ff0000;
			}

ul { list-style: none; }

.onecont {
	background-color: #f0f0f1;
	border: 1px solid #dfdfdf;
	overflow: hidden;
	padding: 10px;
	position: relative;
	}

.onecont_left {
	float: left;
	width: 550px;
	margin: 0;
	}

.onecont_left li {
	font-size: 13px;
	line-height: 21px;
	white-space: nowrap;
	}

.onecont_left li div {
	float: left;
	width: 220px;
	}

.onecont_left li .alert { color: #ff0000; }

.onecont_left li .alert span {
	background-color: #ff363a;
	color: #282828;
	display: inline-block;
	font-weight: bold;
	line-height: 17px;
	margin: 2px 0 2px 15px;
	padding: 0 10px;
	}

.numid {
	color: #910017;
	float: right;
	font-weight: bold;
	margin: 8px 8px 0 0;
	text-align: center;
	width: 100px;
	}

.numid .one {
	display: block;
	font-size: 12px;
	line-height: 15px;
	margin: 0 0 -3px;
	}

.numid .two {
	display: block;
	font-size: 40px;
	line-height: 40px;
	}

.twocont {
	background-color: #f0f0f1;
	border: 1px solid #dfdfdf;
	margin: 0 0 4px;
	overflow: hidden;
	}

.fourcont {
	border: 1px solid #dfdfdf;
	font-size: 13px;
	margin: 0 0 5px;
	overflow: hidden;
	padding: 0 10px;
	page-break-inside: avoid;
	}

.fourcont .day {
	color: #888888;
	float: right;
	font-weight: bold;
	line-height: 35px;
	}

.fourcont .txt {
	clear: both;
	overflow: hidden;
	padding: 0 10px;
	}

.fourcont .txt p {
	line-height: 17px;
	margin: 0 0 17px;
	}

.threecont_tit {
	background: url("../img/ico_return.png") no-repeat scroll 10px center transparent;
	float: left;
	font-size: 13px;
	line-height: 15px;
	margin: 18px 0 9px;
	padding: 0 0 0 30px;
	}

.threecont_tit span {
	color: #959595;
	margin: 0 10px;
	}

.threecont .day {
	color: #888888;
	float: right;
	font-size: 13px;
	font-weight: bold;
	line-height: 15px;
	margin: 10px 10px 0 0;
	}

.txt-fa {
	clear: both;
	overflow: hidden;
	padding: 0 10px;
	margin-bottom: 17px;
	}

.txt-fa p { margin: 0; }

.usuario-admin {
	float: left;
	line-height: 15px;
	margin: 18px 0 0 10px;
	font-weight: bold;
	}

.cor-inicio { color: #45843d; }
.cor-fim { color: #3f7a99; }
.bg-troca { background-color: #f8f8ec; }

.item-linha { width: 100%; height: 1px; background-color: #444444; }
.lote { position: relative; width: 100%; border-bottom: 1px solid #444444; border-right: 1px solid #444444; margin: 0 auto; background-color: #c0c0c0; overflow: hidden; box-sizing: border-box; }
.lote-item { display: block; width: 50px; line-height: 40px; text-align: center; border-right: 1px solid #444444; border-left: 1px solid #444444; padding: 0; float: left; }
.lote-marca { display: block; width: 70px; line-height: 40px; text-align: center; border-right: 1px solid #444444; padding: 0; float: left; }
.lote-modelo { display: block; width: 74px; line-height: 40px; text-align: center; border-right: 1px solid #444444; padding: 0; float: left; }
.lote-veiculo { display: block; width: 74px; line-height: 40px; text-align: center; border-right: 1px solid #444444; padding: 0; float: left; }
.lote-transformacao { display: block; width: 20px; line-height: 40px; text-align: center; border-right: 1px solid #444444; padding: 0; float: left; }
.lote-descricao { display: block; width: 358px; line-height: 40px; text-align: center; border-right: 1px solid #444444; padding: 0; float: left; }
.lote-quantidade { display: block; width: 50px; line-height: 40px; text-align: center; border-right: 1px solid #444444; padding: 0; float: left; }
.lote-valor { position: relative; display: block; width: 116px; line-height: 40px; text-align: center; border-right: 1px solid #444444; padding: 0; float: left; }
.lote-total { display: block; width: 136px; line-height: 40px; text-align: center; padding: 0; float: left; }
.lote-nome { position: relative; width: 100%; height: 29px; border-bottom: 1px solid #444444; text-align: center; line-height: 29px; background-color: #dedede; border-right: 1px solid #444444; border-left: 1px solid #444444; box-sizing: border-box; }

.item { position: relative; width: 100%; border: 1px solid #444444; border-top: none; margin: 0 auto; box-sizing: border-box; overflow: hidden; }
.item-item { display: block; width: 50px; line-height: 28px; float: left; text-align: center; padding: 0; white-space: nowrap; }
.item-marca { display: block; width: 70px; line-height: 18px; float: left; text-align: center; padding: 5px 0; margin-left: 1px; }
.item-modelo { display: block; width: 74px; line-height: 18px; float: left; text-align: center; padding: 5px 0; margin-left: 1px; }
.item-veiculo { display: block; width: 74px; line-height: 18px; float: left; text-align: center; padding: 5px 0; margin-left: 1px; }
.item-transformacao { display: block; width: 20px; line-height: 28px; float: left; text-align: center; padding: 0; margin-left: 1px; white-space: nowrap; }
.item-descricao { display: block; width: 358px; line-height: 18px; float: left; text-align: left; padding: 5px; margin-left: 1px; box-sizing: border-box; }
.item-quantidade { display: block; width: 50px; line-height: 28px; float: left; text-align: center; padding: 0; margin-left: 1px; white-space: nowrap; }
.item-valor { display: block; width: 116px; line-height: 28px; float: left; text-align: right; padding-right: 3px; margin-left: 1px; box-sizing: border-box; }
.item-total-valor { display: block; width: 133px; line-height: 28px; float: left; text-align: right; padding-right: 3px; margin-left: 1px; box-sizing: border_box; }
.item-apl { position: absolute; right: 2px; top: 2px; width: 34px; line-height: 24px !important; height: 24px !important; padding: 0 !important; font-size: 13px !important; font-weight: bold; }
.item-informar { line-height: 22px !important; height: 21px !important; font-size: 13px !important; padding: 0 14px !important; background-color: #000000 !important; text-transform: none !important; }

.item-divider { position: absolute; top: 0; width: 1px; height: 100%; background-color: #444444; }
.left-50 { left: 50px; }
.left-121 { left: 121px; }
.left-196 { left: 196px; }
.left-271 { left: 271px; }
.left-292 { left: 292px; }
.left-651 { left: 651px; }
.left-702 { left: 702px; }
.left-819 { left: 819px; }


.apr-tbl {
	border: 1px solid #dddddd;
	border-collapse: collapse;
	width: 100%;
	}

.apr-tbl tr { background-color: #ffffff; }

.apr-tbl td {
	border: 1px solid #dddddd;
	line-height: 21px;
	padding: 0 0 0 8px;
	}

.apr-tbl-lb {
	width: 180px;
	}

.apr-tbl-vl {
	color: #444444;
	}

.apr-tbl tr:nth-child(even) { background-color: #f8f8f8; }


	</style>
</head>
<body>
<div style="width:960px;overflow:hidden;">
'.($pId_licitacao == 1010?$noop:$tTmp.$oHistorico).'
</div>
</body>
</html>';

		$oFile = fopen(UPLOAD_DIR."~licitacao_exp_".$sInside_id.".html", "w");
		fwrite($oFile, $tHtml);
		fclose($oFile);

		exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~licitacao_exp_".$sInside_id.".html ".UPLOAD_DIR."~licitacao_exp_".$sInside_id.".pdf");

		//@unlink(UPLOAD_DIR."~licitacao_exp_".$sInside_id.".html");

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $pId_licitacao;
	}
}
echo json_encode($aReturn);

?>
