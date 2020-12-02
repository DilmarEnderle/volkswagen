<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_check", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["id-licitacao"]);

	$db = new Mysql();
	$db->query("SELECT aprovar_apl FROM gelic_licitacoes WHERE id = $pId_licitacao");
	$db->nextRecord();
	$dAprovar_apl = $db->f("aprovar_apl");
	if ($dAprovar_apl == 1)
	{
		//disable
		$db->query("UPDATE gelic_licitacoes SET aprovar_apl = 0 WHERE id = $pId_licitacao");
		$aReturn[0] = 1; //successo
		$aReturn[1] = false;
	}
	else
	{
		//enable only if it has APL
		$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao");
		if ($db->nextRecord())
		{
			//enable
			$db->query("UPDATE gelic_licitacoes SET aprovar_apl = 1 WHERE id = $pId_licitacao");
			$aReturn[0] = 1; //successo
			$aReturn[1] = true;
		}
		else
		{
			$aReturn[0] = 2; //sem licitacao
		}
	}
} 
echo json_encode($aReturn);

?>
