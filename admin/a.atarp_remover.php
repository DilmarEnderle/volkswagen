<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("atarp_excluir", $xAccess))
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
		
		$db->query("SELECT id FROM gelic_comprasrp WHERE id_atarp = $pId");
		if (!$db->nextRecord())
		{
			//remover anexos
			$db->query("SELECT arquivo FROM gelic_atarp_anexos WHERE id_atarp = $pId");
			while ($db->nextRecord())
				removeFileBucket("vw/atarp/".$db->f("arquivo"));

			$db->query("DELETE FROM gelic_atarp_anexos WHERE id_atarp = $pId");
			$db->query("DELETE FROM gelic_atarp WHERE id = $pId");
			$aReturn[0] = 1; //sucesso
		}
		else
			$aReturn[0] = 8; //sendo usado
	}
}
echo json_encode($aReturn);

?>
