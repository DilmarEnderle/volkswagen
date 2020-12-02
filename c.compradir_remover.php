<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pId_compradir = intval($_POST["id"]);

	$xAccess = explode(" ",getAccess());

	if ($sInside_tipo == 1 || !in_array("cd_solicitar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];

	$db = new Mysql();

	//verificar acesso
	$db->query("SELECT id FROM gelic_comprasrp WHERE id = $pId_compradir AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
	if ($db->nextRecord())
	{
		//deixar remover somente se nao encontrar tipo 5 (O envio da APL foi autorizado.)
		$db->query("SELECT tipo FROM gelic_comprasrp_historico WHERE id_comprasrp = $pId_compradir AND tipo = 5");
		if (!$db->nextRecord())
		{
			//marcar como removido
			$db->query("UPDATE gelic_comprasrp SET deletado = 1 WHERE id = $pId_compradir");
			$aReturn[0] = 1; //sucesso
		}
		else
		{
			$aReturn[0] = 8; //acesso negado para remover
		}
	}
	else
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}	
}
echo json_encode($aReturn);

?>
