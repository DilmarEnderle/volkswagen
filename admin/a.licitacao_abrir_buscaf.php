<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$db = new Mysql();
	
	$pFiltro_dn = trim($_POST["f-dn"]);
	$pFiltro_status = trim($_POST["f-status"]);
	$pFiltro_estados = trim($_POST["f-estados"]);
	$pFiltro_cidades = trim($_POST["f-cidades"]);
	$pFiltro_regioes = trim($_POST["f-regioes"]);
	$pFiltro_ultimas = intval($_POST["f-ultimas"]);
	$pFiltro_orgao = preg_replace("/\s+/", " ", strip_tags(trim(utf8_decode($_POST["f-orgao"]))));
	$pFiltro_adve = intval($_POST["f-adve"]);
	$pFiltro_numero = preg_replace("/\s+/", " ", strip_tags(trim(utf8_decode($_POST["f-numero"]))));
	$pFiltro_data_de = trim($_POST["f-data-de"]);
	$pFiltro_data_ate = trim($_POST["f-data-ate"]);


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


	//salvar tab para este usuario
	$db->query("UPDATE gelic_admin_usuarios_config SET valor = '0' WHERE id_admin_usuario = $sInside_id AND config = 'tab'");
}
echo json_encode($aReturn);

?>
