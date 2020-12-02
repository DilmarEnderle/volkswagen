<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$oAbas = '';

	$db = new Mysql();

	$db->query("SELECT id, nome, tipo FROM gelic_abas ORDER BY ordem");
	while ($db->nextRecord())
	{
		if ($db->f("id") == 16) //expiradas
			$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes WHERE deletado = 0 AND datahora_abertura < NOW()",1);
		else if ($db->f("id") == 17) //todas
			$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		LEFT JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
	WHERE
		lic.deletado = 0 AND
	    (itm.id IS NULL OR itm.id_tipo_veiculo <> 8)
	GROUP BY
		lic.id
) AS t",1);
		else if ($db->f("id") == 15) //nao pertinente
			$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
	WHERE
		lic.deletado = 0
	GROUP BY
		lic.id
	HAVING
		SUM(itm.id_tipo_veiculo = 8) = SUM(itm.id > 0)
) AS t",1);
		else //outras abas
			$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND grupo = 1 AND id_aba = ".$db->f("id")."
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		his.id IS NULL
	GROUP BY
		lic.id
) AS t",1);

		$db->nextRecord(1);
		$dTotal = intval($db->f("total",1));

		if ($dTotal == 0)
			$dCount = '';
		else
			$dCount = '<span>'.$dTotal.'</span>';

		$add = '';
		if ($db->f("tipo") == "F" || $db->f("tipo") == "N" || $db->f("tipo") == "A")
			$oAbas .= '<a id="tab-'.$db->f("id").'" class="aba0 tb" data-tipo="'.$db->f("tipo").'" href="javascript:void(0);" onclick="goTab('.$db->f("id").');">'.utf8_encode($db->f("nome")).$dCount.'</a>';
		else
			$oAbas .= '<a id="tab-'.$db->f("id").'" class="aba0 tb" data-tipo="'.$db->f("tipo").'" href="javascript:void(0);" onclick="goTab('.$db->f("id").');" style="padding-left: 24px;"><img class="img0" src="img/etapa-calendario.png">&nbsp;</a>';
	}

	$db->query("SELECT valor FROM gelic_admin_usuarios_config WHERE id_admin_usuario = $sInside_id AND config = 'tab'");
	$db->nextRecord();
	$dTab = intval($db->f("valor"));

	$dSrch = 1;
	$dSrch_id = "";

	$dFiltro_dn = '[]';
	$dFiltro_status = '[]';
	$dFiltro_estados = '[]';
	$dFiltro_cidades = '[]';
	$dFiltro_regioes = '[]';
	$dFiltro_ultimas = '[]';
	$dFiltro_orgao = '';
	$dFiltro_orgao_color = '282828';
	$dFiltro_orgao_border = 'cccccc';
	$dFiltro_adve = '';
	$dFiltro_adve_color = '282828';
	$dFiltro_adve_border = 'cccccc';
	$dFiltro_numero = '';
	$dFiltro_numero_color = '282828';
	$dFiltro_numero_border = 'cccccc';
	$dFiltro_data_de = '';
	$dFiltro_data_de_color = '282828';
	$dFiltro_data_de_border = 'cccccc';
	$dFiltro_data_ate = '';
	$dFiltro_data_ate_color = '282828';
	$dFiltro_data_ate_border = 'cccccc';


	$db->query("SELECT valor FROM gelic_admin_usuarios_config WHERE id_admin_usuario = $sInside_id AND config = 'search'");
	if ($db->nextRecord())
	{
		$json_string = $db->f("valor");
		$a = json_decode(utf8_encode($json_string), true);
		$dSrch = $a["search"];
		$dSrch_id = $a["search_1"];
		if ($dSrch_id == 0) $dSrch_id = "";

	
		$dFiltro_orgao = $a["search_2"]["orgao"];
		$dFiltro_orgao = preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $dFiltro_orgao);
		if ($dFiltro_orgao <> '')
		{
			$dFiltro_orgao_color = 'ff0000';
			$dFiltro_orgao_border = 'ee0000';
		}

		if (strlen($a["search_2"]["dn"]) > 0)
		{
			$add_to_status = '';
			$db->query("SELECT id, nome FROM gelic_clientes WHERE id IN (".$a["search_2"]["dn"].")");
			while ($db->nextRecord())
				$add_to_status .= ',{v:'.$db->f("id").',lb:"'.utf8_encode($db->f("nome")).'",uf:""}';

			if (strlen($add_to_status) > 0)
				$dFiltro_dn = '['.substr($add_to_status, 1).']';
		}
		
		if (strlen($a["search_2"]["status"]) > 0)
		{
			$add_to_status = '';
			$db->query("SELECT id, descricao FROM gelic_status WHERE id IN (".$a["search_2"]["status"].")");
			while ($db->nextRecord())
				$add_to_status .= ',{v:'.$db->f("id").',lb:"'.utf8_encode($db->f("descricao")).'",uf:""}';

			if (strlen($add_to_status) > 0)
				$dFiltro_status = '['.substr($add_to_status, 1).']';
		}

		if (strlen($a["search_2"]["estados"]) > 0)
		{
			$add_to_estados = '';
			$e = array();
			$e = explode(",", $a["search_2"]["estados"]);
			function singleQuotes($s) { return "'".$s."'"; }
			$e = array_map("singleQuotes", $e);

			$db->query("SELECT uf, estado FROM gelic_uf WHERE uf IN (".implode(",",$e).")");
			while ($db->nextRecord())
				$add_to_estados .= ',{v:"'.$db->f("uf").'",lb:"'.utf8_encode($db->f("estado")).'",uf:""}';

			if (strlen($add_to_estados) > 0)
				$dFiltro_estados = '['.substr($add_to_estados, 1).']';
		}

		if (strlen($a["search_2"]["cidades"]) > 0)
		{
			$add_to_cidades = '';
			$db->query("SELECT id, nome, uf FROM gelic_cidades WHERE id IN (".$a["search_2"]["cidades"].")");
			while ($db->nextRecord())
				$add_to_cidades .= ',{v:'.$db->f("id").',lb:"'.utf8_encode($db->f("nome")).'",uf:"'.$db->f("uf").'"}';

			if (strlen($add_to_cidades) > 0)
				$dFiltro_cidades = '['.substr($add_to_cidades, 1).']';
		}

		if (strlen($a["search_2"]["regioes"]) > 0)
		{
			$add_to_regioes = '';

			$aReg = array();
			$aReg[1] = "Região 1/2";
			$aReg[3] = "Região 3";
			$aReg[4] = "Região 4";
			$aReg[5] = "Região 5";
			$aReg[6] = "Região 6";

			$r = explode(",", $a["search_2"]["regioes"]);

			foreach ($aReg as $key => $value)
			{
				if (in_array($key, $r))
					$add_to_regioes .= ',{v:'.$key.',lb:"'.$value.'",uf:""}';
			}

			if (strlen($add_to_regioes) > 0)
				$dFiltro_regioes = '['.substr($add_to_regioes, 1).']';
		}

		if ($a["search_2"]["ultimas"] > 0)
		{
			$aUlt = array();
			$aUlt[1] = "Últimas 3";
			$aUlt[2] = "Últimas 10";
			$aUlt[3] = "Últimas 25";
			$aUlt[4] = "Últimas 50";
			$aUlt[5] = "Últimas 100";
			$aUlt[6] = "Últimas 250";
			$aUlt[7] = "Últimas 500";
			$aUlt[8] = "Últimas 1000";

			$dFiltro_ultimas = '[{v:'.$a["search_2"]["ultimas"].',lb:"'.$aUlt[$a["search_2"]["ultimas"]].'",uf:""}]';
		}

		$dFiltro_adve = $a["search_2"]["adve"];
		if ($dFiltro_adve > 0)
		{
			$dFiltro_adve_color = 'ff0000';
			$dFiltro_adve_border = 'ee0000';
		}
		else
			$dFiltro_adve = '';

		$dFiltro_numero = $a["search_2"]["numero"];
		$dFiltro_numero = preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $dFiltro_numero);
		if ($dFiltro_numero <> '')
		{
			$dFiltro_numero_color = 'ff0000';
			$dFiltro_numero_border = 'ee0000';
		}

		$dFiltro_data_de = $a["search_2"]["data_de"];
		if ($dFiltro_data_de <> '' && isValidBrDate($dFiltro_data_de))
		{
			$dFiltro_data_de_color = 'ff0000';
			$dFiltro_data_de_border = 'ee0000';
		}
		else
			$dFiltro_data_de = '';


		$dFiltro_data_ate = $a["search_2"]["data_ate"];
		if ($dFiltro_data_ate <> '' && isValidBrDate($dFiltro_data_ate))
		{
			$dFiltro_data_ate_color = 'ff0000';
			$dFiltro_data_ate_border = 'ee0000';
		}
		else
			$dFiltro_data_ate = '';
	}


	$tPage = new Template("a.licitacao.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{ABAS}}", $oAbas);
	$tPage->replace("{{TAB}}", $dTab);
	$tPage->replace("{{SRCH}}", $dSrch);
	$tPage->replace("{{SRCH_ID}}", $dSrch_id);
	$tPage->replace("{{FILTRO_DN}}", $dFiltro_dn);
	$tPage->replace("{{FILTRO_STATUS}}", $dFiltro_status);
	$tPage->replace("{{FILTRO_ESTADOS}}", $dFiltro_estados);
	$tPage->replace("{{FILTRO_CIDADES}}", $dFiltro_cidades);
	$tPage->replace("{{FILTRO_REGIOES}}", $dFiltro_regioes);
	$tPage->replace("{{FILTRO_ULTIMAS}}", $dFiltro_ultimas);
	$tPage->replace("{{FILTRO_OP}}", $dFiltro_orgao);
	$tPage->replace("{{FILTRO_OP_COLOR}}", $dFiltro_orgao_color);
	$tPage->replace("{{FILTRO_OP_BORDER}}", $dFiltro_orgao_border);
	$tPage->replace("{{FILTRO_ADVE}}", $dFiltro_adve);
	$tPage->replace("{{FILTRO_ADVE_COLOR}}", $dFiltro_adve_color);
	$tPage->replace("{{FILTRO_ADVE_BORDER}}", $dFiltro_adve_border);
	$tPage->replace("{{FILTRO_NUMERO}}", $dFiltro_numero);
	$tPage->replace("{{FILTRO_NUMERO_COLOR}}", $dFiltro_numero_color);
	$tPage->replace("{{FILTRO_NUMERO_BORDER}}", $dFiltro_numero_border);
	$tPage->replace("{{FILTRO_DATA_DE}}", $dFiltro_data_de);
	$tPage->replace("{{FILTRO_DATA_DE_COLOR}}", $dFiltro_data_de_color);
	$tPage->replace("{{FILTRO_DATA_DE_BORDER}}", $dFiltro_data_de_border);
	$tPage->replace("{{FILTRO_DATA_ATE}}", $dFiltro_data_ate);
	$tPage->replace("{{FILTRO_DATA_ATE_COLOR}}", $dFiltro_data_ate_color);
	$tPage->replace("{{FILTRO_DATA_ATE_BORDER}}", $dFiltro_data_ate_border);
	$tPage->replace("{{VERSION}}", VERSION);

	echo $tPage->body;
	
}
else 
{
	header("location: index.php");
}

?>
