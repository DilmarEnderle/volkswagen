<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cnf_editar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();
	$pId = intval($_POST["id"]);
	$db->query("SELECT id FROM gelic_admin_usuarios WHERE id_perfil = $pId");
	if (!$db->nextRecord())
	{
		//nao esta sendo usado por algum usuario
		$db->query("DELETE FROM gelic_admin_usuarios_perfis WHERE id = $pId");
		$aReturn[0] = 1; //sucesso
	}
	else
		$aReturn[0] = 8; //sendo usado
}
echo json_encode($aReturn);

?>
