<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pId_licitacao = intval($_POST["id"]);
	$db = new Mysql();

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		$data_hoje = date("Y-m-d");

		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$select = "lic.id";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
			LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$where = "lic.id = $pId_licitacao AND
			lic.deletado = 0 AND
			his.id IS NULL AND
			IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= '$data_hoje')";
	}
	else //BO
	{
		$select = "lic.id";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$where = "lic.id = $pId_licitacao AND
			lic.deletado = 0 AND
			his.id IS NULL";
	}


	$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
	if ($db->nextRecord())
	{
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

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = "index.php?p=cli_open&id=$pId_licitacao";
	}
}
echo json_encode($aReturn);

?>
