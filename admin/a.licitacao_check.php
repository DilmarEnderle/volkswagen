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
	$pCampo = trim($_POST["field"]);
	$pChecked = intval($_POST["checked"]);

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

	$pChecked = $pChecked ^ 1;

	if (in_array($pCampo, array('documentacao_irregular','sem_cadastro_no_portal')))
	{
		$db->query("UPDATE gelic_licitacoes SET $pCampo = $pChecked WHERE id = $pId_licitacao");

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = 'itm-cb'.$pChecked.' fl ml-60';
	}
} 
echo json_encode($aReturn);
?>
