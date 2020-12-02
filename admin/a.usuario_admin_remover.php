<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("usr_excluir", $xAccess))
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

		$db->query("SELECT id FROM gelic_comprasrp_historico WHERE tipo IN (2,4) AND id_sender = $pId LIMIT 1");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //usuario existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_historico WHERE tipo IN (1,11,12,31,32,33,34,35,36) AND id_sender = $pId LIMIT 1");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //usuario existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_mensagens WHERE (id_origem = $pId AND tipo_destino = ".ADMIN_CLIENTE.") OR (id_destino = $pId AND tipo_destino = ".CLIENTE_ADMIN.") LIMIT 1");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //usuario existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_mensagens_log WHERE (id_origem = $pId AND tipo_destino = ".ADMIN_CLIENTE.") OR (id_destino = $pId AND tipo_destino = ".CLIENTE_ADMIN.") LIMIT 1");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //usuario existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_log_login WHERE tipo = 1 AND id_clienteusuario = $pId LIMIT 1");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //usuario existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("DELETE FROM gelic_admin_usuarios WHERE id = $pId");
		$db->query("DELETE FROM gelic_admin_usuarios_config WHERE id_admin_usuario = $pId");
		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
