<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1 || $sInside_tipo == 3 || $sInside_tipo == 4) //BO, DN FILHO, REP
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

		$db->query("SELECT id, tipo, id_parent FROM gelic_clientes WHERE id = $pId");
		if ($db->nextRecord())
		{
			if ($db->f("tipo") == 3) //DN FILHO
			{
				$db->query("UPDATE gelic_clientes SET deletado = 1 WHERE id = $pId");
				$aReturn[0] = 1; //sucesso
			}
			else if ($db->f("tipo") == 4) //REP
			{
				$dId_cliente = $db->f("id");

				$now = date("Y-m-d H:i:s");
				$ip = $_SERVER['REMOTE_ADDR'];

				//inserir no historico
				$db->query("INSERT INTO gelic_clientes_acesso_historico VALUES (NULL, 2, '$now', '$ip', $sInside_id, $dId_cliente, 0)");

				//remover acesso
				$db->query("DELETE FROM gelic_clientes_acesso WHERE id_cliente_acesso = $sInside_id AND id_cliente = $dId_cliente");

				$aReturn[0] = 1; //sucesso
			}
			else
			{
				$aReturn[0] = 9; //acesso restrito
				echo json_encode($aReturn);
				exit;
			}
		}
		else
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}
	}
}
echo json_encode($aReturn);

?>
