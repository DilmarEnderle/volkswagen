<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pAba = intval($_POST["aba"]);
	$pStart = intval($_POST["start"]);
	$pMais = intval($_POST["mais"]);
	$pOrdenacao = intval($_POST["ordenacao"]);
	$pOrdem = intval($_POST["ordem"]);
	$pRegioes = trim($_POST["regioes"]);
	$pEstados = trim($_POST["estados"]);
	$pInstancia = trim($_POST["instancia"]);
	$pStatus = trim($_POST["status"]);

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

	$db = new Mysql();
	
	$tRow = '<tr>
		<td class="lic_list_date {{CLR}}" style="line-height:normal;height:{{HT}}px;">{{DEP}}{{DIAS}}</td>
		<td class="lic_list_txt" style="text-align: left;">
			<a class="idx_obj" href="index.php?p=cli_open&id={{ID}}">
				<p style="padding: 14px 0;">
					<span class="lista-orgao">{{ORG}}, {{CID}} - {{UF}}</span><br>
					{{MOD}}<br>
					{{SRP}}
					Data de Abertura: {{DAB}}<br>
					<span class="lista-cliente">{{CLI}}</span><br>
					{{OBJ}}
				</p>
			</a>
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


	$data_hoje = date("Y-m-d");
	$oRows = "";

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
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
			lic.datahora_abertura,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			lic.srp,
		    lic.fase,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf,
			labas.id_status,
			hdec.id AS declinou,
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
			LEFT JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente
			LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$having = "";

		if ($pAba == 7) //oportunidades
		{
			$where = "lic.deletado = 0 AND
				DATE(lic.datahora_abertura) >= '$data_hoje' AND
				his.id IS NULL AND
				IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3) AND
				labas.id_aba = 7 AND
				hdec.id IS NULL";
		}
		else if ($pAba == 8) //varal
		{
			$where_estado = "";
			if ($pEstados != "")
			{
				$a = explode(",", $pEstados);
				for ($i=0; $i<count($a); $i++)
					$a[$i] = "'".$a[$i]."'";

				if (count($a) == 1)
					$where_estado = " AND cid.uf = ".$a[0];
				else
					$where_estado = " AND cid.uf IN (".implode(",",$a).")";
			}

			saveConfig("reg", $pEstados);

			$where_instancia = "0";
			if ($pInstancia != "")
				$where_instancia = "0,".$pInstancia;

			saveConfig("instancia", $pInstancia);

			$where = "lic.deletado = 0 AND
				DATE(lic.datahora_abertura) >= '$data_hoje' AND
				lic.instancia IN ($where_instancia) AND
				his.id IS NULL AND
				(
					(
						IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3) AND
						labas.id_aba = 8
					)
					OR
					(
						hdec.id IS NOT NULL
					)
				)$where_estado";
		}
		else if ($pAba == 14) //apl
		{
			$where = "lic.deletado = 0 AND
				DATE(lic.datahora_abertura) >= '$data_hoje' AND
				hdec.id IS NULL AND
				his.id IS NULL AND
				IF (
					lic.fase = 1, 
					IF (apl.id IS NULL, labas.grupo = 4 AND labas.id_aba = 14, labas.grupo = 3 AND labas.id_aba = 14), 
					(apl.id IS NOT NULL AND ear.reprovadas > 0) OR (apl.id IS NOT NULL AND (ear.aprovadas IS NULL OR ear.aprovadas = 0)) AND labas.grupo = 3
				)";
		}
		else if ($pAba == 9) //em participacao
		{
			$where = "lic.deletado = 0 AND
				hdec.id IS NULL AND
				his.id IS NULL AND
				IF (
					lic.fase = 1, 
					IF (apl.id IS NULL, labas.grupo = 4 AND labas.id_aba = 9 AND DATE(lic.datahora_abertura) >= '$data_hoje', labas.grupo = 3 AND labas.id_aba = 9 AND DATE(lic.datahora_abertura) >= '$data_hoje'), 
					apl.id IS NOT NULL AND ear.aprovadas > 0 AND (ear.reprovadas IS NULL OR ear.reprovadas = 0) AND labas.grupo = 3
				)";
		}
		else if (in_array($pAba, array(10,11))) //resultado perdidas, resultado ganhas
		{
			$where = "lic.deletado = 0 AND
				apl.id IS NOT NULL AND
				labas.grupo = 3 AND
				labas.id_aba = $pAba AND
				hdec.id IS NULL AND
				his.id IS NULL";
		}
		else if ($pAba == 16) //expiradas
		{
			$where = "lic.deletado = 0 AND
				DATE(lic.datahora_abertura) < '$data_hoje' AND
				hdec.id IS NULL AND
				his.id IS NULL AND
				IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3)";
		}
		else if ($pAba == 17) //todas
		{
			$from .= " LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id";
			$where = "lic.deletado = 0 AND
				hdec.id IS NULL AND
				his.id IS NULL AND
				IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3) AND
				(it.id IS NULL OR it.id_tipo_veiculo <> 8)";
		}
		else if ($pAba == 15) //nao pertinente
		{
			$from .= " LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id";
			$where = "lic.deletado = 0 AND
				hdec.id IS NULL AND
				his.id IS NULL AND
				IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3)";
			$having = " HAVING SUM(it.id_tipo_veiculo = 8) = SUM(it.id > 0)";
		}
	}
	else if ($sInside_tipo == 1) //BO
	{
		$where_regiao = "";

		$a = array();
		if ($pRegioes != '')
			$a = array_map('intval', explode(',', $pRegioes));

		$where_regiao_estados = array();
		if (in_array(1, $a)) array_push($where_regiao_estados, "'SP'"); //Região 1/2 – Estado de São Paulo
		if (in_array(3, $a)) array_push($where_regiao_estados, "'RS'","'PR'","'SC'"); //Região 3 – Estados do RS, PR e SC
		if (in_array(4, $a)) array_push($where_regiao_estados, "'RJ'","'ES'","'MG'"); //Região 4 – Estados do RJ, ES e MG
		if (in_array(5, $a)) array_push($where_regiao_estados, "'AL'","'BA'","'CE'","'PB'","'PE'","'PI'","'RN'","'SE'"); //Região 5 – Nordeste menos Maranhão
		if (in_array(6, $a)) array_push($where_regiao_estados, "'AC'","'AP'","'AM'","'DF'","'GO'","'MA'","'MT'","'MS'","'PA'","'RO'","'RR'","'TO'"); //Região 6 – Centro Oeste + Norte + Maranhão

		if (count($where_regiao_estados) > 0)
		{
			if (count($where_regiao_estados) == 1)
				$where_regiao = " AND cid.uf = ".$where_regiao_estados[0];
			else
				$where_regiao = " AND cid.uf IN (".implode(",",$where_regiao_estados).")";
		}

		saveConfig("reg", $pRegioes);

		$select = "lic.id, 
			lic.objeto,
			lic.orgao, 
			lic.datahora_entrega,
			lic.datahora_abertura,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			lic.srp,
		    lic.fase,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf,
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
			LEFT JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$having = "";

		if (in_array($pAba, array(7,8,14))) //oportunidades, varal, apl
		{
			$where = "lic.deletado = 0 AND
				DATE(lic.datahora_abertura) >= '$data_hoje' AND
				his.id IS NULL AND
				labas.id_aba = ".$pAba.$where_regiao;

			if ($pAba == 14 && $pStatus != "")
			{
				$a = array_map('intval', explode(',', $pStatus));

				$add_to_where_OR = "";
				for ($i=0; $i<count($a); $i++)
				{
					if ($a[$i] == 8)
						$add_to_where_OR .= " OR (labas.id_status = 19 AND ear.aprovadas > 0)";
					else if ($a[$i] == 19)
						$add_to_where_OR .= " OR (labas.id_status = 8 AND ear.reprovadas > 0)";
					else if ($a[$i] == 17)
						$add_to_where_OR .= " OR (labas.id_status IN (8,19) AND lic.fase = 1 AND ear.enviadas > 0)";
					else if ($a[$i] == 3)
						$add_to_where_OR .= " OR (labas.id_status IN (8,19) AND lic.fase > 1 AND ear.enviadas > 0)";
				}

				if ($add_to_where_OR == "")
					$where .= " AND labas.id_status IN ($pStatus)";
				else
					$where .= " AND ((labas.id_status IN ($pStatus))$add_to_where_OR)";
			}

			saveConfig("status", $pStatus);
		}
		else if (in_array($pAba, array(9,10,11))) //em participacao, resultado perdidas, resultado ganhas
		{
			$where = "lic.deletado = 0 AND
				his.id IS NULL AND
				labas.id_aba = ".$pAba.$where_regiao;
		}
		else if ($pAba == 16) //expiradas
		{
			$where = "lic.deletado = 0 AND
				DATE(lic.datahora_abertura) < '$data_hoje' AND
				his.id IS NULL".$where_regiao;
		}
		else if ($pAba == 17) //todas
		{
			$from .= " LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id";
			$where = "lic.deletado = 0 AND
				his.id IS NULL AND
				(it.id IS NULL OR it.id_tipo_veiculo <> 8)".$where_regiao;
		}
		else if ($pAba == 15) //nao pertinentes
		{
			$from .= " LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id";
			$where = "lic.deletado = 0 AND
				his.id IS NULL".$where_regiao;
			$having = " HAVING SUM(it.id_tipo_veiculo = 8) = SUM(it.id > 0)";
		}
	}

	$aOrdenacao = array();
	$aOrdenacao[1] = "lic.data_hora";
	$aOrdenacao[2] = "lic.datahora_entrega";	
	$aOrdenacao[3] = "mensagem_data_hora";	
	$aOrdenacao[4] = "lic.valor";
	$aOrdenacao[5] = "total_vei_apl";

	$ordem = "";
	if ($pOrdem == 2)
		$ordem = " DESC";

	saveConfig("tab", $pAba);
	saveConfig("ordenacao", $pOrdenacao);
	saveConfig("ordem", $pOrdem);
	saveConfig("is_search", "0");

	$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id$having ORDER BY ".$aOrdenacao[$pOrdenacao].$ordem." LIMIT $pStart,".LPP);
	$pStart += $db->nf();
	if ($db->nf() == 0) $pStart = 0;
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
						$status .= '<span style="background-color:#ffe600;color:#050000;">APL Aguardando Aprovação ('.$db->f("enviadas",1).')</span>';
				}

				$tTmp = str_replace("{{STT}}", '<tr><td colspan="4" class="lic_list_alert" valign="top"><p style="color:#ffffff;float:right;">'.$status.'</p></td></tr>', $tTmp);
			}
			else
			{
				$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = ".$db->f("id_status"),1);
				$db->nextRecord(1);
				$tTmp = str_replace("{{STT}}", '<tr><td colspan="4" class="lic_list_alert" valign="top"><span class="blue" style="color:#'.$db->f("cor_texto",1).';background-color:#'.$db->f("cor_fundo",1).';">'.utf8_encode($db->f("descricao",1)).'</span></td></tr>', $tTmp);
			}
		}

		$tTmp = str_replace("{{DEP}}", mysqlToBr(substr($db->f("datahora_entrega"),0,10)), $tTmp);
		$tTmp = str_replace("{{DAB}}", mysqlToBr(substr($db->f("datahora_abertura"),0,10)), $tTmp);
		$tTmp = str_replace("{{ORG}}", utf8_encode($db->f("orgao")), $tTmp);
		$tTmp = str_replace("{{CID}}", utf8_encode($db->f("nome_cidade")), $tTmp);
		$tTmp = str_replace("{{UF}}", $db->f("uf"), $tTmp);
		$tTmp = str_replace("{{MOD}}", utf8_encode($db->f("nome_modalidade")), $tTmp);
		$tTmp = str_replace("{{OBJ}}", utf8_encode(clipString(strip_tags(stripslashes($db->f("objeto"))),220)), $tTmp);
		if ($db->f("valor") == 0.00)
			$tTmp = str_replace("{{VLR}}", '<span class="gray_aa">Não Informado</span>', $tTmp);
		else
			$tTmp = str_replace("{{VLR}}", "R$ ".number_format($db->f("valor"),2,",","."), $tTmp);

		if ($db->f("srp") == 1)
			$tTmp = str_replace("{{SRP}}", '<span class="t-red">SRP</span><br>', $tTmp);
		else
			$tTmp = str_replace("{{SRP}}", "", $tTmp);


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



	if ($pMais == 0)
	{
		$rtop = '';
		$rtop_text = '';

		if ($pRegioes != "" || $pEstados != "")
			$rtop_text = ' por região';

		if ($sInside_tipo <> 1 && $pAba == 8 && strlen($pInstancia) < 5)
		{
			if ($rtop_text != '')
				$rtop_text .= ' e por instância';
			else
				$rtop_text .= ' por instância';
		}

		if ($sInside_tipo == 1 && $pAba == 14 && $pStatus != "")
		{
			if ($rtop_text != '')
				$rtop_text .= ' e por status';
			else
				$rtop_text .= ' por status';
		}

		if (strlen($rtop_text) > 0)
			$rtop = '<div class="lh-30 italic center"><span style="display:inline-block;color:#ee0000;padding:0 16px;background-color:#ffeeee;line-height:30px;">Listagem filtrada'.$rtop_text.'.<span></div>';


		if ($oRows == '')
			$oOutput = $rtop.'<div style="margin: 60px 0; text-align: center; color: #000000;">Nenhuma Licitação!</div>';
		else
			$oOutput = $rtop.'<table cellpading="0" cellspacing="0" class="lic_list" style="margin-top: 0;">
			<thead>
				<tr>
					<th>D.E.P.</th>
					<th>Produto/Serviço</th>
					<th>Valor Estimado</th>
					<th>Últimas Atualizações</th>
				</tr>
			</thead>
			<tbody id="mais">'.$oRows.'</tbody>
		</table>';
	}
	else
	{
		$oOutput = $oRows;
	}

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
	$aReturn[2] = $pStart;
}

echo json_encode($aReturn);

?>
