<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("sta_excluir", $xAccess))
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

		$db->query("SELECT id FROM gelic_licitacoes_abas WHERE id_status = $pId");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //status existe em outras tabelas
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT texto FROM gelic_historico WHERE tipo IN (13,14,31,32,36,41,42,43,44,45)");
		while ($db->nextRecord())
		{
			if (strlen($db->f("texto")) > 0)
			{
				$a = json_decode($db->f("texto"), true);
				if (
					$a["fr"][0]["status"] == $pId ||
					$a["fr"][1]["status"] == $pId ||
					$a["fr"][2]["status"] == $pId ||
					$a["fr"][3]["status"] == $pId ||
					$a["to"][0]["status"] == $pId ||
					$a["to"][1]["status"] == $pId ||
					$a["to"][2]["status"] == $pId ||
					$a["to"][3]["status"] == $pId
				)
				{
					$aReturn[0] = 8; //status existe em outras tabelas
					echo json_encode($aReturn);
					exit;
					break;
				}

				if (in_array($pId, array(8,27,19,28)))
				{
					if (
						in_array($a["fr"][0]["status"], array(8,27,19,28)) ||
						in_array($a["fr"][1]["status"], array(8,27,19,28)) ||
						in_array($a["fr"][2]["status"], array(8,27,19,28)) ||
						in_array($a["fr"][3]["status"], array(8,27,19,28)) ||
						in_array($a["to"][0]["status"], array(8,27,19,28)) ||
						in_array($a["to"][1]["status"], array(8,27,19,28)) ||
						in_array($a["to"][2]["status"], array(8,27,19,28)) ||
						in_array($a["to"][3]["status"], array(8,27,19,28))
					)
					{
						$aReturn[0] = 8; //status existe em outras tabelas
						echo json_encode($aReturn);
						exit;
						break;
					}
				}
			}
		}

		$db->query("DELETE FROM gelic_status WHERE id = $pId");
		$aReturn[0] = 1; //sucesso
	}
} 
echo json_encode($aReturn);

?>
