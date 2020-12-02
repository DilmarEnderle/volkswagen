<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("msg_reenviar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pIds = $_POST["ids"];
	$now = date("Y-m-d H:i:s");
	$db = new Mysql();
	$db->query("UPDATE gelic_mensagens SET status = 0, data_hora_status = '$now', resultado = '' WHERE id IN ($pIds)");
	$aReturn[0] = 1; //sucesso
} 
echo json_encode($aReturn);

?>
