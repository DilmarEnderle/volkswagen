<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("mot_excluir", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId = 0;
	if (isset($_POST["id"]))
	{
		$pId = intval($_POST["id"]);
		$db = new Mysql();

		$db->query("SELECT id FROM gelic_licitacoes_apl_historico WHERE tipo = 4 AND (id_valor_1 = $pId OR id_valor_2 = $pId)");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //status existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_comprasrp_apl_historico WHERE tipo = 4 AND (id_valor_1 = $pId OR id_valor_2 = $pId)");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //status existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_historico WHERE tipo IN (22,23,31,34) AND (id_valor_1 = $pId OR id_valor_2 = $pId)");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //status existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("DELETE FROM gelic_motivos WHERE (id = $pId OR id_parent = $pId)");
		$aReturn[0] = 1; //sucesso
	}
} 
echo json_encode($aReturn);

?>
