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
	$pId = intval($_POST["id"]);

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

	$db->query("SELECT evento FROM gelic_licitacoes_itens_eventos WHERE id = $pId");
	if ($db->nextRecord())
	{
		$aReturn[0] = 1; //success
		$aReturn[1] = $db->f("evento");

		$db->query("DELETE FROM gelic_licitacoes_itens_eventos WHERE id = $pId");
	}
} 
echo json_encode($aReturn);

?>
