<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pTipo = intval($_POST["f-tipo"]); //1: busca por ID, 2: busca por filtros
	$db = new Mysql();

	$where = "lic.deletado = 0 AND his.id IS NULL";
	if ($pTipo == 1)
	{
		//ID
		$pId_licitacao = intval($_POST["f-id"]);
		$where .= " AND lic.id = $pId_licitacao";


		//****** SALVAR BUSCA ********
		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'search'");
		if ($db->nextRecord())
		{
			$json_string = $db->f("valor");

			$a = json_decode($json_string, true);
			$a["search"] = 1;
			$a["search_1"] = $pId_licitacao;

			$json_string = json_encode($a);
			$db->query("UPDATE gelic_clientes_config SET valor = '$json_string' WHERE id_cliente = $sInside_id AND config = 'search'");
		}
		else
		{
			$json_string = '{"search":1,"search_1":'.$pId_licitacao.',"search_2":{"data_de":"","data_ate":"","estado":"","cidade":"","orgao":"","modalidade":0}}';
			$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $sInside_id, 'search', '$json_string')");
		}
		//**********************************************************************************
	}
	else
	{
		$pDa_fr = trim($_POST["f-da-fr"]);  // 'dd/mm/aaaaa'
		$pDa_to = trim($_POST["f-da-to"]);  // 'dd/mm/aaaaa'
		$pEstado = trim($_POST["f-estado"]);
		$pId_cidade = intval($_POST["f-cidade"]);
		$pOrgao = preg_replace("/\s+/", " ", strip_tags(trim(utf8_decode($_POST["f-orgao"]))));
		$pId_modalidade = intval($_POST["f-modalidade"]);

		//modalidade
		if ($pId_modalidade > 0)
			$where .= " AND lic.id_modalidade = $pId_modalidade";

		//cidade - estado
		if ($pId_cidade > 0)
			$where .= " AND lic.id_cidade = $pId_cidade";
		else if (strlen($pEstado) > 0)
			$where .= " AND cid.uf = '$pEstado'";

		//data de abertura de
		if (strlen($pDa_fr) == 10 && isValidBrDate($pDa_fr))
			$where .= " AND lic.datahora_abertura >= '".usToMysql(brToUs($pDa_fr))." 00:00:00'";

		//data de abertura ate
		if (strlen($pDa_to) == 10 && isValidBrDate($pDa_to))
			$where .= " AND lic.datahora_abertura <= '".usToMysql(brToUs($pDa_to))." 23:59:59'";

		//orgao
		if (strlen($pOrgao) > 0)
			$where .= " AND lic.orgao LIKE '%$pOrgao%'";




		//****** SALVAR FILTROS DE BUSCA ********
		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'search'");
		if ($db->nextRecord())
		{
			$json_string = $db->f("valor");

			$a = json_decode($json_string, true);
			$a["search"] = 2;
			$a["search_2"]["data_de"] = $pDa_fr;
			$a["search_2"]["data_ate"] = $pDa_to;
			$a["search_2"]["estado"] = $pEstado;
			$a["search_2"]["cidade"] = $pId_cidade;
			$a["search_2"]["orgao"] = utf8_encode($pOrgao);
			$a["search_2"]["modalidade"] = $pId_modalidade;

			$json_string = json_encode($a);			
			$db->query("UPDATE gelic_clientes_config SET valor = '$json_string' WHERE id_cliente = $sInside_id AND config = 'search'");
		}
		else
		{
			$a = array();
			$a["search"] = 2;
			$a["search_1"] = "";
			$a["search_2"]["data_de"] = $pDa_fr;
			$a["search_2"]["data_ate"] = $pDa_to;
			$a["search_2"]["estado"] = $pEstado;
			$a["search_2"]["cidade"] = $pId_cidade;
			$a["search_2"]["orgao"] = utf8_encode($pOrgao);
			$a["search_2"]["modalidade"] = $pId_modalidade;

			$json_string = json_encode($a);
			$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $sInside_id, 'search', '$json_string')");
		}
		//**********************************************************************************
	}

	saveConfig("is_search", "1");

	$tRow = '<tr>
		<td class="lic_list_date {{CLR}}" style="line-height:normal;height:{{HT}}px;">{{DEP}}{{DIAS}}</td>
		<td class="lic_list_txt" style="text-align: left;">
			<a class="idx_obj" href="index.php?p=cli_open&id={{ID}}"><p style="padding: 14px 0;"><span class="lista-cliente">{{CLI}}</span><br>{{OBJ}}<br><span class="lista-orgao">{{ORG}}, {{CID}} - {{UF}}</span><br>{{MOD}}</p></a>
		</td>
		<td class="lic_list_cash">{{VLR}}</td>
		<td class="lic_list_load" style="padding:0;position:relative;width:190px;">
			<div class="lst-items">
				<ul class="lks" style="margin: 0;">
					<li style="margin: 0;"><a class="btn-icon" href="index.php?p=cli_open&id={{ID}}">{{MSG_D}}<img src="img/icon_{{MSG_I}}.png" title="{{MSG_T}}"></a></li>
				</ul>
				<ul style="margin: 0; padding: 0 6px;">
					<li style="margin: 0; line-height: 21px; text-align: center; font-size: 14px; background-color: #f3f3f3;">{{ID}}</li>
				</ul>			
			</div>
			<div class="lst-cars">{{CARROS}}</div>
		</td>
	</tr>
	{{STT}}
	<tr><td colspan="4" style="height:6px;background-color:#ffffff;"></td></tr>';



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

	$oRows = '';

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		$data_hoje = date("Y-m-d");

		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$select = "lic.id, 
			lic.objeto,
			lic.orgao, 
			lic.datahora_entrega,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf,
			lic.fase,
			hdec.id AS declinou,
			labas.id_status,
			(SELECT MAX(id) FROM gelic_historico WHERE id_licitacao = lic.id AND tipo IN (1,2)) AS mensagem_global,
			(SELECT MAX(data_hora) FROM gelic_historico WHERE id_licitacao = lic.id AND tipo IN (1,2)) AS mensagem_data_hora,
			(SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (1,2) AND acompanhamento = 0) AS hatches,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (3,4) AND acompanhamento = 0) AS sedans,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (5,6) AND acompanhamento = 0) AS pickups,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo = 7 AND acompanhamento = 0) AS stations,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo = 9 AND acompanhamento = 0) AS nds,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (1,2) AND acompanhamento = 0 AND transformacao > 0) AS hatches_t,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (3,4) AND acompanhamento = 0 AND transformacao > 0) AS sedans_t,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (5,6) AND acompanhamento = 0 AND transformacao > 0) AS pickups_t,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo = 7 AND acompanhamento = 0 AND transformacao > 0) AS stations_t,
			(SELECT SUM(IF(quantidade = 0, 1, quantidade)) FROM gelic_licitacoes_itens WHERE id IN (SELECT DISTINCT(id_item) FROM gelic_licitacoes_apl WHERE id_licitacao = lic.id)) AS total_vei_apl";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
			INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
			INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
			LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
			LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$where .= " AND IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= '$data_hoje') AND IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3)";
	}
	else //BO
	{
		$select = "lic.id, 
			lic.objeto,
			lic.orgao, 
			lic.datahora_entrega,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf,
			lic.fase,
			labas.id_status,
			(SELECT MAX(id) FROM gelic_historico WHERE id_licitacao = lic.id AND tipo IN (1,2)) AS mensagem_global,
			(SELECT MAX(data_hora) FROM gelic_historico WHERE id_licitacao = lic.id AND tipo IN (1,2)) AS mensagem_data_hora,
			(SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (1,2) AND acompanhamento = 0) AS hatches,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (3,4) AND acompanhamento = 0) AS sedans,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (5,6) AND acompanhamento = 0) AS pickups,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo = 7 AND acompanhamento = 0) AS stations,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo = 9 AND acompanhamento = 0) AS nds,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (1,2) AND acompanhamento = 0 AND transformacao > 0) AS hatches_t,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (3,4) AND acompanhamento = 0 AND transformacao > 0) AS sedans_t,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN (5,6) AND acompanhamento = 0 AND transformacao > 0) AS pickups_t,
		    (SELECT SUM(quantidade) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo = 7 AND acompanhamento = 0 AND transformacao > 0) AS stations_t,
			(SELECT SUM(IF(quantidade = 0, 1, quantidade)) FROM gelic_licitacoes_itens WHERE id IN (SELECT DISTINCT(id_item) FROM gelic_licitacoes_apl WHERE id_licitacao = lic.id)) AS total_vei_apl";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2
			INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
			INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";
	}
	
	// die("SELECT $select FROM $from WHERE $where GROUP BY lic.id");

	$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
	$dTotal = $db->nf();
	while ($db->nextRecord())
	{
		$declinou = false;
		if (($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) && $db->f("declinou") > 0)
			$declinou = true;

		$dLimite = segundosConv($db->f("limite"));

		$tTmp = $tRow;


		//--------  carros ---------
		$height = 0;
		$tCarros = '';
		if (strlen($db->f("hatches")) > 0)
		{
			$cl = 'b';
			if ($db->f("hatches_t") == $db->f("hatches") && $db->f("hatches_t") > 0)
				$cl = 'r';
			else if ($db->f("hatches_t") < $db->f("hatches") && $db->f("hatches_t") > 0)
				$cl = 'h';

			$q = 1;
			if ($db->f("hatches") > 0) $q = $db->f("hatches");
			$tCarros .= '<div class="car-hatch mt-8"><img src="img/car-hatch-'.$cl.'.png" title="Hatch"><span class="t11 bold t-black">'.$q.'</span></div>';

			$height += 26;
		}

		if (strlen($db->f("sedans")) > 0)
		{
			$cl = 'b';
			if ($db->f("sedans_t") == $db->f("sedans") && $db->f("sedans_t") > 0)
				$cl = 'r';
			else if ($db->f("sedans_t") < $db->f("sedans") && $db->f("sedans_t") > 0)
				$cl = 'h';

			$q = 1;
			if ($db->f("sedans") > 0) $q = $db->f("sedans");
			$tCarros .= '<div class="car-sedan mt-8"><img src="img/car-sedan-'.$cl.'.png" title="Sedan"><span class="t11 bold t-black">'.$q.'</span></div>';

			$height += 23;
		}

		if (strlen($db->f("stations")) > 0)
		{
			$cl = 'b';
			if ($db->f("stations_t") == $db->f("stations") && $db->f("stations_t") > 0)
				$cl = 'r';
			else if ($db->f("stations_t") < $db->f("stations") && $db->f("stations_t") > 0)
				$cl = 'h';

			$q = 1;
			if ($db->f("stations") > 0) $q = $db->f("stations");
			$tCarros .= '<div class="car-station mt-8"><img src="img/car-station-'.$cl.'.png" title="Station Wagon"><span class="t11 bold t-black">'.$q.'</span></div>';

			$height += 23;
		}

		if (strlen($db->f("pickups")) > 0)
		{
			$cl = 'b';
			if ($db->f("pickups_t") == $db->f("pickups") && $db->f("pickups_t") > 0)
				$cl = 'r';
			else if ($db->f("pickups_t") < $db->f("pickups") && $db->f("pickups_t") > 0)
				$cl = 'h';

			$q = 1;
			if ($db->f("pickups") > 0) $q = $db->f("pickups");
			$tCarros .= '<div class="car-pickup mt-8"><img src="img/car-pickup-'.$cl.'.png" title="Pickup"><span class="t11 bold t-black">'.$q.'</span></div>';

			$height += 23;
		}

		if (strlen($db->f("nds")) > 0)
		{
			$q = 1;
			if ($db->f("nds") > 0) $q = $db->f("nds");
			$tCarros .= '<div class="car-nd mt-8"><img src="img/car-nd.png" title="Não Disponível"><span class="t11 bold t-black">'.$q.'</span></div>';

			$height += 23;
		}

		$tCarros .= '<div style="margin-top:10px;text-align:center;line-height:17px;font-size:11px;color:#000000;">Com APL</div>
			<div style="text-align:center;">
				<span style="display:inline-block;font-size:11px;background-color:#b0b0b0;color:#ffffff;border-radius:10px;line-height:20px;text-align:center;min-width:20px;padding:0 6px;box-sizing:border-box;">'.intval($db->f("total_vei_apl")).'</span>
			</div>';

		$height += 55;
		if ($height < 114)
			$height = 114;

		$tTmp = str_replace("{{HT}}", $height, $tTmp);
		$tTmp = str_replace("{{CARROS}}", $tCarros, $tTmp);
		//--------  carros ---------



		if ($declinou)
		{
			$tTmp = str_replace("{{STT}}", '<tr><td colspan="4" class="lic_list_alert" valign="top"><span class="blue" style="color:#ffffff;background-color:#ff0000;">SEM INTERESSE</span></td></tr>', $tTmp);
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
				if ($db->f("reprovadas",1) > 0)
					$status .= '<span style="background-color:#ed0000;">APL Reprovada ('.$db->f("reprovadas",1).')</span>';

				if ($db->f("aprovadas",1) > 0)
					$status .= '<span style="background-color:#00b318;">APL Aprovada ('.$db->f("aprovadas",1).')</span>';

				if ($db->f("fase") == 1)
				{
					if ($db->f("enviadas",1) > 0)
						$status .= '<span style="background-color:#827c7c;">APL em Análise - GELIC ('.$db->f("enviadas",1).')</span>';
				}
				else
				{
					if ($db->f("enviadas",1) > 0)
						$status .= '<span style="background-color: #ffe600; color: #050000;">APL Aguardando Aprovação ('.$db->f("enviadas",1).')</span>';
				}

				$tTmp = str_replace("{{STT}}", '<tr><td colspan="4" class="lic_list_alert" valign="top"><p style="color:#ffffff;float: right;">'.$status.'</p></td></tr>', $tTmp);
			}
			else
			{
				$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = ".$db->f("id_status"),1);
				$db->nextRecord(1);
				$tTmp = str_replace("{{STT}}", '<tr><td colspan="4" class="lic_list_alert" valign="top"><span class="blue" style="color:#'.$db->f("cor_texto",1).';background-color:#'.$db->f("cor_fundo",1).';">'.utf8_encode($db->f("descricao",1)).'</span></td></tr>', $tTmp);
			}
		}

		$tTmp = str_replace("{{DEP}}", mysqlToBr(substr($db->f("datahora_entrega"),0,10)), $tTmp);
		$tTmp = str_replace("{{ORG}}", utf8_encode($db->f("orgao")), $tTmp);
		$tTmp = str_replace("{{CID}}", utf8_encode($db->f("nome_cidade")), $tTmp);
		$tTmp = str_replace("{{UF}}", $db->f("uf"), $tTmp);
		$tTmp = str_replace("{{MOD}}", utf8_encode($db->f("nome_modalidade")), $tTmp);
		$tTmp = str_replace("{{OBJ}}", utf8_encode(clipString(strip_tags(stripslashes($db->f("objeto"))),220)), $tTmp);
		if ($db->f("valor") == 0.00)
			$tTmp = str_replace("{{VLR}}", '<span class="gray_aa">Não Informado</span>', $tTmp);
		else
			$tTmp = str_replace("{{VLR}}", "R$ ".number_format($db->f("valor"),2,",","."), $tTmp);

		$tTmp = str_replace("{{DIAS}}", "", $tTmp);


		if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
		{
			$db->query("SELECT nome FROM gelic_clientes WHERE id = $cliente_parent",1);
			$db->nextRecord(1);
			$tTmp = str_replace("{{CLI}}", utf8_encode($db->f("nome",1)), $tTmp);
		}
		else
		{
			if ($db->f("fase") == 1)
			{
				$DNs = '';
				$db->query("SELECT nome FROM gelic_clientes WHERE id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = ".$db->f("id").")",1);
				while ($db->nextRecord(1))
					$DNs .= '<br>'.utf8_encode($db->f("nome",1));

				$tTmp = str_replace("{{CLI}}", substr($DNs, 4), $tTmp);
			}
			else if ($db->f("fase") == 2)
			{
				$tTmp = str_replace("{{CLI}}", utf8_encode($aEstados[$db->f("uf")]), $tTmp);
			}
			else
			{
				$tTmp = str_replace("{{CLI}}", "Brasil", $tTmp);
			}
		}


		// **** ICONE DE MENSAGEM ***
		if ($db->f("mensagem_global") > 0)
		{
			//buscar historico
			$db->query("SELECT data_hora FROM gelic_historico WHERE id = ".$db->f("mensagem_global"),1);
			$db->nextRecord(1);
			$tTmp = str_replace("{{MSG_D}}", timeAgo($db->f("data_hora",1)), $tTmp);
		}
		else
			$tTmp = str_replace("{{MSG_D}}", "---", $tTmp);

		$tTmp = str_replace("{{MSG_T}}", "Mensagens", $tTmp);
		$tTmp = str_replace("{{MSG_I}}", "msg0", $tTmp);
		// **** FIM ICONE DE MENSAGEM ***


		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);

		if (!$declinou)
		{
			if ($db->f("data_abertura_dias") < 0)
				$tTmp = str_replace("{{CLR}}", "black", $tTmp); //black
			else
			{
				if ($dLimite["h"] >= 2)
					$tTmp = str_replace("{{CLR}}", "green", $tTmp); //green
				else if ($dLimite["h"] > 0 && $dLimite["h"] < 2)
					$tTmp = str_replace("{{CLR}}", "bright-red", $tTmp); //bright red
				else if ($dLimite["h"] <= 0)
					$tTmp = str_replace("{{CLR}}", "dark-red", $tTmp); //red
			}
		}
		else
			$tTmp = str_replace("{{CLR}}", "gray", $tTmp); //gray


		$oRows .= $tTmp;
	}


	if (strlen($oRows) == 0) $oRows = '<tr><td colspan="4">Nenhuma Licitação!</td></tr>';
	$aReturn[0] = 1;
	$aReturn[1] = '<div id="res-busca" style="line-height:30px;background-color:#ffeeee;padding-left:6px;font-weight:bold;">Resultado(s) da Busca (<span class="t-red">'.$dTotal.'</span>)</div><table cellpading="0" cellspacing="0" class="lic_list" style="margin-top:0;">
		<thead>
			<tr>
				<th>D.E.P.</th>
				<th>Produto/Serviço</th>
				<th>Valor Estimado</th>
				<th>Últimas Atualizações</th>
			</tr>
		</thead>
		<tbody>'.$oRows.'</tbody>
	</table>';
}
echo json_encode($aReturn);

?>
