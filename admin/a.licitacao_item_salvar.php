<?php

require_once "include/config.php";
require_once "include/essential.php";
require_once "a.licitacao_ntfnova.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_editar", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_lote = intval($_POST["id-lote"]);
	$pId_item = intval($_POST["id-item"]);
	$pCampo = trim($_POST["campo"]);
	$pVal = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["val"])))));

	$db->query("SELECT tipo FROM gelic_historico WHERE id_licitacao = $pId_licitacao ORDER BY id DESC LIMIT 1");
	if ($db->nextRecord())
	{
		if ($db->f("tipo") == 31)
		{
			$aReturn[0] = 8; //encerrada
			echo json_encode($aReturn);
			exit;
		}
	}

	if ($pCampo == "quantidade")
	{
		$pVal = intval($pVal);
		if ($pVal == 0)
			$pVal = 1;
		$db->query("UPDATE gelic_licitacoes_itens SET $pCampo = $pVal WHERE id = $pId_item");
	}
	else if ($pCampo == "id_modelo")
	{
		$pVal = intval($pVal);
		$db->query("UPDATE gelic_licitacoes_itens SET $pCampo = $pVal WHERE id = $pId_item");

		/**
		* @see Vinculações entre carros e seus tipos
		*/
		$id_tipo_veiculo = 0;
		if (in_array($pVal,array(1,2,3,4,5))) $id_tipo_veiculo = 1;
		else if (in_array($pVal,array(6,7,8,9))) $id_tipo_veiculo = 2;
		else if (in_array($pVal,array(10,11))) $id_tipo_veiculo = 3;
		else if (in_array($pVal,array(12,13))) $id_tipo_veiculo = 4;
		else if ($pVal == 14) $id_tipo_veiculo = 5;
		else if (in_array($pVal,array(15,23))) $id_tipo_veiculo = 6; // Amarok Pick-up Premium
		else if (in_array($pVal,array(16,17))) $id_tipo_veiculo = 7;
		else if ($pVal == 19) $id_tipo_veiculo = 9;
		else if (in_array($pVal,array(24,25))) $id_tipo_veiculo = 4; // Virtus = sedan premium
		else if (in_array($pVal,array(20,21,22))) $id_tipo_veiculo = 2; // Polo Hatch Premium

		
		if ($id_tipo_veiculo > 0)
			$db->query("UPDATE gelic_licitacoes_itens SET id_tipo_veiculo = $id_tipo_veiculo WHERE id = $pId_item");

		ntfNovaLicitacao($pId_licitacao);
	}
	else if ($pCampo == "id_tipo_veiculo")
	{
		$pVal = intval($pVal);

		// Pegar modelo
		$db->query("SELECT id_modelo FROM gelic_licitacoes_itens WHERE id = $pId_item");
		$db->nextRecord();
		$dId_modelo = intval($db->f("id_modelo"));

		if ($dId_modelo == 0 || $dId_modelo == 18)
			$db->query("UPDATE gelic_licitacoes_itens SET $pCampo = $pVal WHERE id = $pId_item");

		ntfNovaLicitacao($pId_licitacao);
	}
	else if ($pCampo == "valor")
	{
		$pVal = str_replace(array(".","R","$"," "), "", $pVal);
		$pVal = str_replace(",", ".", $pVal);
		if (strlen($pVal) == 0) $pVal = '0.00';
			$db->query("UPDATE gelic_licitacoes_itens SET $pCampo = $pVal WHERE id = $pId_item");
	}
	else
	{
		$db->query("UPDATE gelic_licitacoes_itens SET $pCampo = '$pVal' WHERE id = $pId_item");
	}


	$aTipo_veiculos = array();
	$aTipo_veiculos[0] = '&lt;não informado&gt;';
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
	$aModelos[0] = '&lt;não informado&gt;';
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
	$aModelos[20] = 'Polo 1.0';
	$aModelos[21] = 'Polo 1.6';
	$aModelos[22] = 'Polo 200';
	$aModelos[23] = 'Amarok 3.0';
	$aModelos[24] = 'Virtus 1.0';
	$aModelos[25] = 'Virtus 1.6';

	$db->query("SELECT item, marca, id_modelo, id_tipo_veiculo, descricao, quantidade, valor, IF (quantidade > 0, valor*quantidade, valor) AS total_item FROM gelic_licitacoes_itens WHERE id = $pId_item");
	$db->nextRecord();
	
	$aReturn[0] = 1; //sucesso
	$aReturn[1] = utf8_encode($db->f("item"));
	$aReturn[2] = utf8_encode($db->f("marca"));
	$aReturn[3] = $aModelos[intval($db->f("id_modelo"))];
	$aReturn[4] = utf8_encode(nl2br($db->f("descricao")));
	$aReturn[5] = intval($db->f("quantidade"));
	$aReturn[6] = "R$ ".number_format($db->f("valor"),2,",",".");
	$aReturn[7] = $aTipo_veiculos[intval($db->f("id_tipo_veiculo"))];
	$aReturn[8] = $db->f("id_tipo_veiculo");
	$aReturn[9] = "R$ ".number_format($db->f("total_item"), 2, ",", ".");
	$aReturn[10] = $db->f("id_modelo");
}
echo json_encode($aReturn);
?>
