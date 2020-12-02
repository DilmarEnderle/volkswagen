<?php

require_once "include/config.php";
require_once "include/essential.php";

/* gets the data from a URL */
function get_data($url)
{
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pAba = intval($_POST["aba"]);
	$pOrdenacao = intval($_POST["ordenacao"]);
	$pOrdem = intval($_POST["ordem"]);
	$pRegioes = trim($_POST["regioes"]);
	$pEstados = trim($_POST["estados"]);
	$pInstancia = trim($_POST["instancia"]);
	$pStatus = trim($_POST["status"]);

	$db = new Mysql();
	$data_hoje = date("Y-m-d");

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$select = "lic.id,
			lic.datahora_entrega,
			lic.valor,
			cid.id AS id_cidade,
			cid.nome AS nome_cidade,
			cid.uf,
			cid.lat,
			cid.lng";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
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
		if ($pAba == 8) //varal
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
			lic.datahora_entrega,
			lic.valor,
			cid.id AS id_cidade,
			cid.nome AS nome_cidade,
			cid.uf,
			cid.lat,
			cid.lng";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2
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

	saveConfig("tab", $pAba);
	saveConfig("ordenacao", $pOrdenacao);
	saveConfig("ordem", $pOrdem);
	saveConfig("is_search", "0");

	$coords = '';
	$aPontos = array();

	$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id$having");
	while ($db->nextRecord())
	{
		$dId_cidade = $db->f("id_cidade");
		$dNome_cidade = utf8_encode($db->f("nome_cidade"));
		$dUf = $db->f("uf");
		$dLat = $db->f("lat");
		$dLng = $db->f("lng");

		if (strlen($dLat) > 0)
		{
			//usar lat/lng existentes
			$info = $dNome_cidade.' - '.$dUf;
			$info .= '<br>Nº da Licitação: '.$db->f("id");
			$info .= '<br>D.E.P: '.mysqlToBr(substr($db->f("datahora_entrega"),0,10));
			if ($db->f("valor") == 0.00)
				$info .= '<br>Valor: <span class=\"gray-88 italic\">- não informado -</span>';
			else
				$info .= '<br>Valor: R$ '.number_format($db->f("valor"),2,",",".");

			$info .= '<a class=\"bt-style-1\" href=\"index.php?p=cli_open&id='.$db->f("id").'\" style=\"position: absolute;right: 2px;bottom: 2px;height: 20px;line-height: 20px;font-size: 12px;\">Abrir</a>';
			$aPontos[] = array("tipo"=>"lic", "cidade"=>$dNome_cidade, "uf"=>$dUf, "lat"=>$dLat, "lng"=>$dLng, "info"=>$info, "p"=>0);
		}
		else
		{
			//buscar lat/lng para esta cidade
			$res = json_decode(get_data(str_replace(" ", "%20", "http://maps.google.com/maps/api/geocode/json?address=".removerAcentos($dNome_cidade)." - $dUf,Brazil")), true);
			if ($res["status"] == "OK")
			{
				$dLat = $res["results"][0]["geometry"]["location"]["lat"];
				$dLng = $res["results"][0]["geometry"]["location"]["lng"];

				//salvar coordenadas na tabela para a proxima vez
				$db->query("UPDATE gelic_cidades SET lat = '$dLat', lng = '$dLng' WHERE id = $dId_cidade",1);

				$info = $dNome_cidade.' - '.$dUf;
				$info .= '<br>No da Licitacao: '.$db->f("id");
				$info .= '<br>D.E.P: '.mysqlToBr(substr($db->f("datahora_entrega"),0,10));
				if ($db->f("valor") == 0.00)
					$info .= '<br>Valor: <span class=\"gray-88 italic\">- não informado -</span>';
				else
					$info .= '<br>Valor: R$ '.number_format($db->f("valor"),2,",",".");

				$info .= '<a class=\"bt-style-1\" href=\"index.php?p=cli_open&id='.$db->f("id").'\" style=\"position: absolute;right: 2px;bottom: 2px;height: 20px;line-height: 20px;font-size: 12px;\">Abrir</a>';
				$aPontos[] = array("tipo"=>"lic", "cidade"=>$dNome_cidade, "uf"=>$dUf, "lat"=>$dLat, "lng"=>$dLng, "info"=>$info, "p"=>0);
			}
		}
	}

	//encontrar pontos duplicados
	$aDup = array();
	if (count($aPontos) > 0)
	{
		for ($i=0; $i<count($aPontos); $i++)
		{
			for ($j=$i; $j<count($aPontos); $j++)
			{
				if ($j > $i)
				{
					if ($aPontos[$j]["p"] == 0 && $aPontos[$i]["lat"] == $aPontos[$j]["lat"] && $aPontos[$i]["lng"] == $aPontos[$j]["lng"])
					{
						$aPontos[$j]["p"] = 1;
						$aDup[$i][] = $i;
						$aDup[$i][] = $j;
					}
				}
			}
		}
	}

	//limpar indices duplicados
	foreach ($aDup as $k => $v)
		$aDup[$k] = array_unique($aDup[$k]);

	//espalhar pontos duplicados
	foreach ($aDup as $k => $v)
	{
		$quantos = count($aDup[$k]);
		$lat = floatval($aPontos[$aDup[$k][0]]["lat"]);
		$lng = floatval($aPontos[$aDup[$k][0]]["lng"]);
		$inc_deg = 360 / $quantos;
		$deg = 0;

		foreach ($aDup[$k] as $key => $val)
		{
			$new_lat = round($lat + (-0.0004 * cos($deg / 180 * M_PI)), 7);
			$new_lng = round($lng + (-0.0004 * sin($deg / 180 * M_PI)), 7);
			$deg += $inc_deg;

			$aPontos[$aDup[$k][$key]]["lat"] = $new_lat;
			$aPontos[$aDup[$k][$key]]["lng"] = $new_lng;
		}
	}

	for ($i=0; $i<count($aPontos); $i++)
		$coords .= ',{"id":'.$i.',"tipo":"'.$aPontos[$i]["tipo"].'","cidade":"'.$aPontos[$i]["cidade"].', '.$aPontos[$i]["uf"].'","lat":'.$aPontos[$i]["lat"].',"lng":'.$aPontos[$i]["lng"].',"info":"'.$aPontos[$i]["info"].'"}';


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
		$rtop = '<div class="lh-30 italic center"><span style="display:inline-block;color:#ee0000;padding:0 16px;background-color:#ffeeee;line-height:30px;">Mapa filtrado'.$rtop_text.'.<span></div>';

	$aReturn[0] = 1;
	$aReturn[1] = '<div id="mapa" style="width: 100%; height: 600px; background-color: #f1f1f1;"></div>';
	if (strlen($coords) > 0)
		$aReturn[2] = '['.substr($coords, 1).']';
	else
		$aReturn[2] = '['.$coords.']';

	$aReturn[3] = $rtop;
}
echo json_encode($aReturn);

?>
