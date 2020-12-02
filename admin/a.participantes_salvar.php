<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$db = new Mysql();

	$pId = intval($_POST["f-id"]);
	$pId_item = intval($_POST["f-id-item"]);
	$pRazao_social = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-razao"])))));
	$pCnpj = trim($_POST["f-cnpj"]);
	$pValor_final = trim($_POST["f-valor-final"]);
	$pValor_final = str_replace(array(".","R","$"," "),"",$pValor_final);
	$pValor_final = str_replace(",",".",$pValor_final);
	if (strlen($pValor_final) == 0) $pValor_final = '0.00';
	$pFabricante = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-fabricante"])))));
	$pModelo = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-modelo"])))));
	$pDn_venda = intval($_POST["f-dn-venda"]);
	$pVencedor = intval($_POST["f-vencedor"]);
	$pInabilitado = intval($_POST["f-inabilitado"]);

	if ($pId == 0)
	{
		//INSERIR
		$db->query("SELECT DATE(lic.datahora_abertura) <= CURRENT_DATE() AS passou_abertura FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes AS lic ON lic.id = itm.id_licitacao WHERE itm.id = $pId_item");
		if ($db->nextRecord())
		{
			if ($db->f("passou_abertura") > 0)
			{
				//ok
				if ($pVencedor > 0)
					$db->query("UPDATE gelic_licitacoes_itens_participantes SET vencedor = 0 WHERE id_item = $pId_item AND deletado = 0");

				$db->query("INSERT INTO gelic_licitacoes_itens_participantes VALUES (NULL, $pId_item, '$pRazao_social', '$pCnpj', '$pDn_venda', '$pFabricante', '$pModelo', $pValor_final, $pVencedor, $pInabilitado, 0)");

				$aReturn[0] = 1; //sucesso
				$aReturn[1] = $pId_item;
			}
			else
			{
				$aReturn[0] = 9; //acesso restrito
			}
		}
		else
		{
			$aReturn[0] = 9; //acesso restrito
		}
	}
	else
	{
		//SALVAR
		$db->query("SELECT id_item FROM gelic_licitacoes_itens_participantes WHERE id = $pId AND deletado = 0");
		if ($db->nextRecord())
		{
			$dId_item = $db->f("id_item");

			if ($pVencedor > 0)
				$db->query("UPDATE gelic_licitacoes_itens_participantes SET vencedor = 0 WHERE id_item = $dId_item AND deletado = 0");

			$db->query("UPDATE gelic_licitacoes_itens_participantes SET razao_social = '$pRazao_social', cnpj = '$pCnpj', dn_venda = '$pDn_venda', fabricante = '$pFabricante', modelo = '$pModelo', valor_final = $pValor_final, vencedor = $pVencedor, inabilitado = $pInabilitado WHERE id = $pId AND deletado = 0");

			$aReturn[0] = 1; //sucesso
			$aReturn[1] = $dId_item;
		}
		else
		{
			$aReturn[0] = 9; //acesso restrito
		}
	}
}
echo json_encode($aReturn);

?>
