<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId = 0;
	if (isset($_POST["id"]))
	{
		$pId = intval($_POST["id"]);
		$db = new Mysql();
		$aReturn[0] = 1; //sucesso
		$db->query("SELECT observacoes FROM gelic_atarp WHERE id = $pId");
		if ($db->nextRecord())
			$aReturn[1] = utf8_encode($db->f("observacoes"));
		else
			$aReturn[1] = '';
	}
}
echo json_encode($aReturn);

?>
