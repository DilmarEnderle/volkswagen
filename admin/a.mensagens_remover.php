<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("msg_excluir", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pIds = $_POST["ids"];
	$db = new Mysql();
	$db->query("DELETE FROM gelic_mensagens WHERE id IN ($pIds)");
	$aReturn[0] = 1; //sucesso
} 
echo json_encode($aReturn);

?>
