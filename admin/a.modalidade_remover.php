<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("mod_excluir", $xAccess))
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

		$db->query("SELECT id FROM gelic_licitacoes WHERE id_modalidade = $pId");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //status existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("DELETE FROM gelic_modalidades WHERE id = $pId");
		$aReturn[0] = 1; //sucesso
	}
} 
echo json_encode($aReturn);

?>
