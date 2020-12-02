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
	$pId_motivo = intval($_POST["id-motivo"]);

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

	//mark as checked/unchecked
	$db->query("SELECT id FROM gelic_licitacoes_itens_check WHERE id_item = $pId_item");
	if ($db->nextRecord())
		$db->query("UPDATE gelic_licitacoes_itens_check SET sem_participacao_apl = ".(int)($pId_motivo > 0).", sem_participacao_apl_id_motivo = $pId_motivo WHERE id_item = $pId_item");
	else
		$db->query("INSERT INTO gelic_licitacoes_itens_check VALUES (NULL, $pId_item, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, ".(int)($pId_motivo > 0).", $pId_motivo, 0)");

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = 'itm-cb'.(int)($pId_motivo > 0);
} 
echo json_encode($aReturn);
?>
