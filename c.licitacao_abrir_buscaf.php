<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();

	$pDa_fr = trim($_POST["f-da-fr"]);  // 'dd/mm/aaaaa'
	$pDa_to = trim($_POST["f-da-to"]);  // 'dd/mm/aaaaa'
	$pEstado = trim($_POST["f-estado"]);
	$pId_cidade = intval($_POST["f-cidade"]);
	$pOrgao = preg_replace("/\s+/", " ", strip_tags(trim(utf8_decode($_POST["f-orgao"]))));
	$pId_modalidade = intval($_POST["f-modalidade"]);

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

	saveConfig("is_search", "1");
}
echo json_encode($aReturn);

?>
