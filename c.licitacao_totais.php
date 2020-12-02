<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

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


		// OPORTUNIDADES
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
		lic.deletado = 0 AND 
		DATE(lic.datahora_abertura) >= '$data_hoje' AND
		his.id IS NULL AND
		IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3) AND
		labas.id_aba = 7 AND
		hdec.id IS NULL
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[1] = $db->f("total");


		// VARAL
		$where_estado = "";
		$dEstados = "";
		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'reg'");
		if ($db->nextRecord())
			$dEstados = $db->f("valor");

		if ($dEstados != "")
		{
			$a = explode(",", $dEstados);
			for ($i=0; $i<count($a); $i++)
				$a[$i] = "'".$a[$i]."'";

			if (count($a) == 1)
				$where_estado = " AND cid.uf = ".$a[0];
			else
				$where_estado = " AND cid.uf IN (".implode(",",$a).")";
		}


		$where_instancia = "0";
		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'instancia'");
		if ($db->nextRecord() && $db->f("valor") != "")
			$where_instancia = $db->f("valor");

		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
		lic.deletado = 0 AND
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
		)$where_estado
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[2] = $db->f("total");

		
		// APL
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
		LEFT JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
		lic.deletado = 0 AND
		DATE(lic.datahora_abertura) >= '$data_hoje' AND
		hdec.id IS NULL AND
		his.id IS NULL AND
		IF (
			lic.fase = 1,
			IF (apl.id IS NULL, labas.grupo = 4 AND labas.id_aba = 14, labas.grupo = 3 AND labas.id_aba = 14),
			(apl.id IS NOT NULL AND ear.reprovadas > 0) OR (apl.id IS NOT NULL AND (ear.aprovadas IS NULL OR ear.aprovadas = 0)) AND labas.grupo = 3
		)
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[3] = $db->f("total");


		// EM PARTICIPACAO
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
		LEFT JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
		lic.deletado = 0 AND 
		hdec.id IS NULL AND
		his.id IS NULL AND
		IF (
			lic.fase = 1, 
			IF (apl.id IS NULL, labas.grupo = 4 AND labas.id_aba = 9 AND DATE(lic.datahora_abertura) >= '$data_hoje', labas.grupo = 3 AND labas.id_aba = 9 AND DATE(lic.datahora_abertura) >= '$data_hoje'), 
			apl.id IS NOT NULL AND ear.aprovadas > 0 AND (ear.reprovadas IS NULL OR ear.reprovadas = 0) AND labas.grupo = 3
		)
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[4] = $db->f("total");


		// RESULTADO PERDIDAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
		lic.deletado = 0 AND
		labas.grupo = 3 AND
		labas.id_aba = 10 AND
		hdec.id IS NULL AND
		his.id IS NULL
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[5] = $db->f("total");


		// RESULTADO GANHAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
		lic.deletado = 0 AND
		labas.grupo = 3 AND
		labas.id_aba = 11 AND
		hdec.id IS NULL AND
		his.id IS NULL
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[6] = $db->f("total");


		// EXPIRADAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
		lic.deletado = 0 AND
		DATE(lic.datahora_abertura) < '$data_hoje' AND
		hdec.id IS NULL AND
		his.id IS NULL
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[7] = $db->f("total");


		// TODAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
		LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id
	WHERE
		lic.deletado = 0 AND
		hdec.id IS NULL AND
		his.id IS NULL AND
		(it.id IS NULL OR it.id_tipo_veiculo <> 8)
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[8] = $db->f("total");


		// NAO PERTINENTE
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
		LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
		LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id
	WHERE
		lic.deletado = 0 AND
		hdec.id IS NULL AND
		his.id IS NULL
	GROUP BY
		lic.id
	HAVING
		SUM(it.id_tipo_veiculo = 8) = SUM(it.id > 0)
) AS t");
		$db->nextRecord();
		$aReturn[9] = $db->f("total");
	}
	else if ($sInside_tipo == 1) //BO
	{
		$dRegioes = "";
		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'reg'");
		if ($db->nextRecord())
			$dRegioes = $db->f("valor");


		$dStatus = "";
		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'status'");
		if ($db->nextRecord())
			$dStatus = $db->f("valor");


		$where_regiao = "";

		$a = array();
		if ($dRegioes != '')
			$a = array_map('intval', explode(',', $dRegioes));

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


		// OPORTUNIDADES
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2 AND labas.id_aba = 7
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		DATE(lic.datahora_abertura) >= '$data_hoje' AND
		his.id IS NULL$where_regiao
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[1] = $db->f("total");


		// VARAL
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2 AND labas.id_aba = 8
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		DATE(lic.datahora_abertura) >= '$data_hoje' AND
		his.id IS NULL$where_regiao
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[2] = $db->f("total");


		// APL
		$where_status = "";
		if ($dStatus != "")
		{
			$a = array_map('intval', explode(',', $dStatus));

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
				$where_status .= " AND labas.id_status IN ($dStatus)";
			else
				$where_status .= " AND ((labas.id_status IN ($dStatus))$add_to_where_OR)";
		}

		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2 AND labas.id_aba = 14
		LEFT JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		DATE(lic.datahora_abertura) >= '$data_hoje' AND
		his.id IS NULL$where_regiao$where_status
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[3] = $db->f("total");


		// EM PARTICIPACAO
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2 AND labas.id_aba = 9
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		his.id IS NULL$where_regiao
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[4] = $db->f("total");


		// RESULTADO PERDIDAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2 AND labas.id_aba = 10
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		his.id IS NULL$where_regiao
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[5] = $db->f("total");


		// RESULTADO GANHAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2 AND labas.id_aba = 11
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		his.id IS NULL$where_regiao
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[6] = $db->f("total");


		// EXPIRADAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
    	lic.deletado = 0 AND
		DATE(lic.datahora_abertura) < '$data_hoje' AND
		his.id IS NULL$where_regiao
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[7] = $db->f("total");


		// TODAS
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
    	lic.deletado = 0 AND
		his.id IS NULL AND
		(it.id IS NULL OR it.id_tipo_veiculo <> 8)$where_regiao
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aReturn[8] = $db->f("total");


		// NAO PERTINENTE
		$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
		LEFT JOIN gelic_licitacoes_itens AS it ON it.id_licitacao = lic.id
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
    	lic.deletado = 0 AND
		his.id IS NULL$where_regiao
	GROUP BY
		lic.id
	HAVING
		SUM(it.id_tipo_veiculo = 8) = SUM(it.id > 0)
) AS t");
		$db->nextRecord();
		$aReturn[9] = $db->f("total");
	}
	
	$aReturn[0] = 1; //success
}

echo json_encode($aReturn);

?>
