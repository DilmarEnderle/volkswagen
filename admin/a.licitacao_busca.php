<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_visualizar", $xAccess))
	{
		$oRows = '<div class="content-inside" style="padding-top: 20px;">
			<div class="full-row" style="padding: 0 0 2px 0; border-bottom: 1px solid #666666;">
				<span class="t18 bold lh-30 fl red">Acesso Restrito!</span>
			</div>
			<div class="t14" style="position: relative; margin: 40px auto; width: 500px; text-align: center; border: 1px solid #999999; padding: 20px 0;">
				<span class="bold">LICITAÇÕES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span><br><br><br>
				<a class="bt-style-1" href="javascript:window.history.back();" style="display: inline-block;">Ok</a>
			</div>
		</div>';

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $oRows;
		$aReturn[2] = '';
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pTipo = intval($_POST["f-tipo"]); //1: busca por ID, 2: busca por filtros

	$add_to_where = "";
	// $order_by_and_limit = " ORDER BY lic.datahora_abertura LIMIT 1000";
	$order_by_and_limit = " ORDER BY lic.id DESC LIMIT 1000";
	$db = new Mysql();
	
	if ($pTipo == 1)
	{
		$pBusca = intval($_POST["f-busca"]);
		$add_to_where .= " AND lic.id = $pBusca";
	
		//****** SALVAR BUSCA ********
		$db->query("SELECT valor FROM gelic_admin_usuarios_config WHERE id_admin_usuario = $sInside_id AND config = 'search'");
		if ($db->nextRecord())
		{
			$json_string = $db->f("valor");

			$a = json_decode($json_string, true);
			$a["search"] = 1;
			$a["search_1"] = $pBusca;

			$json_string = json_encode($a);
			$db->query("UPDATE gelic_admin_usuarios_config SET valor = '$json_string' WHERE id_admin_usuario = $sInside_id AND config = 'search'");
		}
		else
		{
			$json_string = '{"search":1,"search_1":'.$pBusca.',"search_2":{"dn":"","status":"","estados":"","cidades":"","regioes":"","ultimas":0,"orgao":"","adve":0,"numero":"","data_de":"","data_ate":""}}';
			$db->query("INSERT INTO gelic_admin_usuarios_config VALUES (NULL, $sInside_id, 'search', '$json_string')");
		}
		//**********************************************************************************
	}
	else
	{
		$pFiltro_dn = trim($_POST["f-dn"]);
		$pFiltro_status = trim($_POST["f-status"]);
		$aStatus = array_filter(explode(",", $pFiltro_status));
		$pFiltro_estados = trim($_POST["f-estados"]);
		$pFiltro_cidades = trim($_POST["f-cidades"]);
		$pFiltro_regioes = trim($_POST["f-regioes"]);
		$pFiltro_ultimas = intval($_POST["f-ultimas"]);
		$pFiltro_orgao = preg_replace("/\s+/", " ", strip_tags(trim(utf8_decode($_POST["f-orgao"]))));
		$pFiltro_adve = intval($_POST["f-adve"]);
		$pFiltro_numero = preg_replace("/\s+/", " ", strip_tags(trim(utf8_decode($_POST["f-numero"]))));
		$pFiltro_data_de = trim($_POST["f-data-de"]);
		$pFiltro_data_ate = trim($_POST["f-data-ate"]);

		if ($pFiltro_orgao <> '')
			$add_to_where .= " AND lic.orgao LIKE '%$pFiltro_orgao%'";

		if ($pFiltro_numero <> '')
			$add_to_where .= " AND lic.numero LIKE '%$pFiltro_numero%'";

		if (isValidBrDate($pFiltro_data_de))
			$add_to_where .= " AND lic.datahora_abertura >= '".brToMysql($pFiltro_data_de)." 00:00:00'";
		else
			$pFiltro_data_de = "";

		if (isValidBrDate($pFiltro_data_ate))
			$add_to_where .= " AND lic.datahora_abertura <= '".brToMysql($pFiltro_data_ate)." 23:59:59'";
		else
			$pFiltro_data_ate = "";

		if (strlen($pFiltro_dn) > 0)
			$add_to_where .= " AND licc.id_cliente IN ($pFiltro_dn)";


		if (count($aStatus) > 0)
		{
			$add_to_where_OR = "";
			for ($i=0; $i<count($aStatus); $i++)
			{
				if ($aStatus[$i] == 8)
					$add_to_where_OR .= " OR (labas.id_status = 19 AND ear.aprovadas > 0)";
				else if ($aStatus[$i] == 19)
					$add_to_where_OR .= " OR (labas.id_status = 8 AND ear.reprovadas > 0)";
				else if ($aStatus[$i] == 17)
					$add_to_where_OR .= " OR (labas.id_status IN (8,19) AND lic.fase = 1 AND ear.enviadas > 0)";
				else if ($aStatus[$i] == 3)
					$add_to_where_OR .= " OR (labas.id_status IN (8,19) AND lic.fase > 1 AND ear.enviadas > 0)";
			}

			if ($add_to_where_OR == "")
				$add_to_where .= " AND labas.id_status IN ($pFiltro_status)";
			else
				$add_to_where .= " AND ((labas.id_status IN ($pFiltro_status))$add_to_where_OR)";
		}


		if ($pFiltro_adve > 0)
			$add_to_where .= " AND cid.adve = $pFiltro_adve AND cli.adve = $pFiltro_adve";

		$e = array();
		if (strlen($pFiltro_estados) > 0)
		{
			$e = explode(",", $pFiltro_estados);
			function singleQuotes($s) { return "'".$s."'"; }
			$e = array_map("singleQuotes", $e);
		}

		if (strlen($pFiltro_cidades) > 0)
			$add_to_where .= " AND cid.id IN ($pFiltro_cidades)";

		if (strlen($pFiltro_regioes) > 0)
		{
			if (strpos($pFiltro_regioes, "1") !== false) array_push($e, "'SP'");
			if (strpos($pFiltro_regioes, "3") !== false) array_push($e, "'PR'","'RS'","'SC'");
			if (strpos($pFiltro_regioes, "4") !== false) array_push($e, "'ES'","'MG'","'RJ'");
			if (strpos($pFiltro_regioes, "5") !== false) array_push($e, "'AL'","'BA'","'CE'","'PB'","'PE'","'PI'","'RN'","'SE'");
			if (strpos($pFiltro_regioes, "6") !== false) array_push($e, "'AC'","'AM'","'AP'","'DF'","'GO'","'MA'","'MS'","'MT'","'PA'","'RO'","'RR'","'TO'");
		}

		$e = array_unique($e);
		if (count($e) > 0)
			$add_to_where .= " AND cid.uf IN (".implode(",",$e).")";

		$aUlt[1] = "Últimas 3";
		$aUlt[2] = "Últimas 10";
		$aUlt[3] = "Últimas 25";
		$aUlt[4] = "Últimas 50";
		$aUlt[5] = "Últimas 100";
		$aUlt[6] = "Últimas 250";
		$aUlt[7] = "Últimas 500";
		$aUlt[8] = "Últimas 1000";

		if ($pFiltro_ultimas == 1) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 3";
		if ($pFiltro_ultimas == 2) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 10";
		if ($pFiltro_ultimas == 3) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 25";
		if ($pFiltro_ultimas == 4) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 50";
		if ($pFiltro_ultimas == 5) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 100";
		if ($pFiltro_ultimas == 6) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 250";
		if ($pFiltro_ultimas == 7) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 500";
		if ($pFiltro_ultimas == 8) $order_by_and_limit = " ORDER BY lic.id DESC LIMIT 1000";


		//****** SALVAR FILTROS DE BUSCA ********
		$db->query("SELECT valor FROM gelic_admin_usuarios_config WHERE id_admin_usuario = $sInside_id AND config = 'search'");
		if ($db->nextRecord())
		{
			$json_string = $db->f("valor");

			$a = json_decode($json_string, true);
			$a["search"] = 2;
			$a["search_2"]["dn"] = $pFiltro_dn;
			$a["search_2"]["status"] = $pFiltro_status;
			$a["search_2"]["estados"] = $pFiltro_estados;
			$a["search_2"]["cidades"] = $pFiltro_cidades;
			$a["search_2"]["regioes"] = $pFiltro_regioes;
			$a["search_2"]["ultimas"] = $pFiltro_ultimas;
			$a["search_2"]["orgao"] = utf8_encode($pFiltro_orgao);
			$a["search_2"]["adve"] = $pFiltro_adve;
			$a["search_2"]["numero"] = utf8_encode($pFiltro_numero);
			$a["search_2"]["data_de"] = $pFiltro_data_de;
			$a["search_2"]["data_ate"] = $pFiltro_data_ate;

			$json_string = json_encode($a);			
			$db->query("UPDATE gelic_admin_usuarios_config SET valor = '$json_string' WHERE id_admin_usuario = $sInside_id AND config = 'search'");
		}
		else
		{
			$a = array();
			$a["search"] = 2;
			$a["search_1"] = "";
			$a["search_2"]["dn"] = $pFiltro_dn;
			$a["search_2"]["status"] = $pFiltro_status;
			$a["search_2"]["estados"] = $pFiltro_estados;
			$a["search_2"]["cidades"] = $pFiltro_cidades;
			$a["search_2"]["regioes"] = $pFiltro_regioes;
			$a["search_2"]["ultimas"] = $pFiltro_ultimas;
			$a["search_2"]["orgao"] = utf8_encode($pFiltro_orgao);
			$a["search_2"]["adve"] = $pFiltro_adve;
			$a["search_2"]["numero"] = utf8_encode($pFiltro_numero);
			$a["search_2"]["data_de"] = $pFiltro_data_de;
			$a["search_2"]["data_ate"] = $pFiltro_data_ate;

			$json_string = json_encode($a);
			$db->query("INSERT INTO gelic_admin_usuarios_config VALUES (NULL, $sInside_id, 'search', '$json_string')");
		}
		//**********************************************************************************
	}

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


	$tRow = '<div class="content-inside mt-2" style="border: 1px solid #aaaaaa; background-color: #ebebeb; min-height: 100px;">
		<div class="dep-box {{CLR}}">
			<span class="t12">{{DEP}}</span>
			<span class="t11 mt-10">{{DIAS}}</span>
			<a href="a.licitacao_abrir.php?id={{ID}}"></a>
		</div>
		<a class="row-item-conteudo" href="a.licitacao_abrir.php?id={{ID}}">
			<span class="row-item-cliente bold lh-24 t13">{{CLI}}</span>
			<span class="row-item-objeto t13 gray-33">{{OBJ}}</span>
			<span class="row-item-orgao t12 italic mt-10"><span>{{ORGP}}, {{CID}} - {{UF}}</span></span>
			<span class="row-item-modalidade t12 gray-88">{{MOD}}</span>
			<span class="row-item-num t12">Nº da Licitação: {{NUM}}</span>
		</a>
		<a class="md-icon" href="a.licitacao_abrir.php?id={{ID}}" style="right: 79px;">{{MSG_D}}<img src="img/icon_{{MSG_I}}.png" title="{{MSG_T}}"></a>
		<span class="t11 verdana row-item-valor">{{VLR}}</span>
		<span class="row-item-numero dark-red">Nº {{ID}}</span>
		{{STA}} 
	</div>';


	//salvar tab para este usuario
	// $db->query("UPDATE gelic_admin_usuarios_config SET valor = '0' WHERE id_admin_usuario = $sInside_id AND config = 'tab'");

	$oRows = "";



	$db->query("
SELECT 
	lic.id, 
	lic.orgao, 
	lic.objeto, 
	lic.datahora_abertura, 
	lic.datahora_entrega, 
	DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
	UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
	lic.fase,
	lic.valor,
	lic.numero,
	cid.nome AS cidade, 
	cid.uf AS uf,
	mdl.nome AS modalidade,
	labas.id_status,
	(SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo,
	(SELECT MAX(id) FROM gelic_historico WHERE id_licitacao = lic.id AND tipo IN (1,2)) AS mensagem_global
FROM 
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id
	INNER JOIN gelic_clientes AS cli ON cli.id = licc.id_cliente
	INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 1
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
	LEFT JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id
WHERE
	lic.deletado = 0 $add_to_where
GROUP BY
	lic.id $order_by_and_limit");

	$total_recs = $db->nf();
	while ($db->nextRecord())
	{
		$tTmp = $tRow;


		if (in_array($db->f("id_status"), array(8,19))) // APL Aprovada, APL Reprovada
		{
			$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = ".$db->f("id"),1);
			$db->nextRecord(1);

			$status = '<div class="row-item-status" style="background-color:#dedede;font-size: 13px;">';

			if ($db->f("fase") == 1)
			{
				if ($db->f("enviadas",1) > 0)
					$status .= '<span style="background-color:#827c7c;color:#ffffff;line-height:26px;display:inline-block;padding:0 10px;">APL em Análise - GELIC ('.$db->f("enviadas",1).')</span>';
			}
			else
			{
				if ($db->f("enviadas",1) > 0)
					$status .= '<span style="background-color:#ffe600;color:#050000;line-height:26px;display:inline-block;padding:0 10px;">APL Aguardando Aprovação ('.$db->f("enviadas",1).')</span>';
			}

			if ($db->f("aprovadas",1) > 0)
				$status .= '<span style="background-color:#00b318;color:#ffffff;line-height:26px;display:inline-block;padding:0 10px;">APL Aprovada ('.$db->f("aprovadas",1).')</span>';
	
			if ($db->f("reprovadas",1) > 0)
				$status .= '<span style="background-color:#ed0000;color:#ffffff;line-height:26px;display:inline-block;padding:0 10px;">APL Reprovada ('.$db->f("reprovadas",1).')</span>';

			$status .= '</div>';
		}
		else
		{
			$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = ".$db->f("id_status"),1);
			$db->nextRecord(1);
			$status = '<span class="row-item-status" style="color: #'.$db->f("cor_texto",1).'; background-color: #'.$db->f("cor_fundo",1).';">'.utf8_encode($db->f("descricao",1)).'</span>';
		}


		$tTmp = str_replace("{{STA}}", $status, $tTmp);

		$dLimite = segundosConv($db->f("limite"));

		$tTmp = str_replace("{{DEP}}", mysqlToBr(substr($db->f("datahora_entrega"),0,10)), $tTmp);
		$tTmp = str_replace("{{OBJ}}", utf8_encode(clipString(strip_tags(stripslashes($db->f("objeto"))), 220)), $tTmp);
		$tTmp = str_replace("{{ORGP}}", utf8_encode($db->f("orgao")), $tTmp);
		$tTmp = str_replace("{{CID}}", utf8_encode($db->f("cidade")), $tTmp);
		$tTmp = str_replace("{{UF}}", $db->f("uf"), $tTmp);
		$tTmp = str_replace("{{MOD}}", utf8_encode($db->f("modalidade")), $tTmp);
		if ($db->f("valor") == 0.00)
			$tTmp = str_replace("{{VLR}}", '<a class="gray-aa">Não Informado</a>', $tTmp);
		else
			$tTmp = str_replace("{{VLR}}", "R$ ".number_format($db->f("valor"),2,',','.'), $tTmp);

		$tTmp = str_replace("{{DIAS}}", '<br><br><span style="font-size: 11px;">Prazo Limite<br>'.$dLimite["h"].'h '.$dLimite["m"].'m</span>', $tTmp);


		if ($db->f("numero") == '')
			$tTmp = str_replace("{{NUM}}", '<span class="gray-88 italic">- não informado -</span>', $tTmp);
		else
			$tTmp = str_replace("{{NUM}}", utf8_encode($db->f("numero")), $tTmp);


		if ($db->f("fase") == 1)
		{
			$DNs = '';
			$db->query("SELECT nome FROM gelic_clientes WHERE id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = ".$db->f("id").") ORDER BY dn",1);
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


		if ($db->f("ultimo_tipo") != 31) //nao encerrada
		{
			if ($db->f("data_abertura_dias") < 0)
				$tTmp = str_replace("{{CLR}}", "dep-box-black", $tTmp); //black
			else
			{
				if ($dLimite["h"] >= 2)
					$tTmp = str_replace("{{CLR}}", "dep-box-green", $tTmp); //green
				else if ($dLimite["h"] > 0 && $dLimite["h"] < 2)
					$tTmp = str_replace("{{CLR}}", "dep-box-bright-red", $tTmp); //bright red
				else if ($dLimite["h"] <= 0)
					$tTmp = str_replace("{{CLR}}", "dep-box-dark-red", $tTmp); //red
			}
		}
		else
			$tTmp = str_replace("{{CLR}}", "dep-box-gray", $tTmp); //gray
			
		$oRows .= $tTmp;
	}
	
	$aReturn[0] = 1; //success
	$aReturn[1] = '
		<div class="content-inside" style="background-color: #ababab; height: 26px; margin-top: 2px;">
			<span class="bold abs lh-26" style="left: 8px;">D.E.P.</span>
			<span class="bold abs lh-26" style="left: 96px;">Cliente/Objeto/Órgão/Localização/Modalidade</span>
			<span class="bold abs lh-26" style="right: 220px;">Valor Estimado</span>
			<span class="bold abs lh-26" style="right: 8px;">Últimas Atualizações</span>
		</div>'.$oRows;
	$aReturn[2] = 'Resultado(s) da Busca (<a class="red">'.$total_recs.'</a>)';
} 

echo json_encode($aReturn);

?>
