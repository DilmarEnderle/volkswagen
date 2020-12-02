<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_check", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_item = intval($_POST["id-item"]);
	$aSelected = array_values(array_filter(explode(",", trim($_POST["selected"]))));

	$db = new Mysql();
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

	for ($i=0; $i<count($aSelected); $i++)
	{
		$db->query("SELECT id FROM gelic_licitacoes_itens_check_inabilitado WHERE id_item = $pId_item AND id_motivo = ".$aSelected[$i]);
		if (!$db->nextRecord())
			$db->query("INSERT INTO gelic_licitacoes_itens_check_inabilitado VALUES (NULL, $pId_item, ".$aSelected[$i].")");
	}

	if (count($aSelected) > 0)
		$sel = implode(",",$aSelected);
	else
		$sel = "0";

	$db->query("DELETE FROM gelic_licitacoes_itens_check_inabilitado WHERE id_item = $pId_item AND id_motivo NOT IN ($sel)");

	if (count($aSelected) > 0)
		$checked = 1;
	else
		$checked = 0;


	//mark as checked/unchecked
	$db->query("SELECT id FROM gelic_licitacoes_itens_check WHERE id_item = $pId_item");
	if ($db->nextRecord())
		$db->query("UPDATE gelic_licitacoes_itens_check SET inabilitado = $checked WHERE id_item = $pId_item");
	else
		$db->query("INSERT INTO gelic_licitacoes_itens_check VALUES (NULL, $pId_item, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0)");



	$aReturn[0] = 1; //sucesso
	$aReturn[1] = 'itm-cb'.$checked;
} 
echo json_encode($aReturn);
?>
