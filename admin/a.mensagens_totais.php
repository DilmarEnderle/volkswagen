<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,0,0,0,0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("msg_visualizar", $xAccess))
	{
		echo json_encode($aReturn);
		exit;
	}

	$pFiltro_recip = $_POST["filtro-recip"];
	$pFiltro_notif = $_POST["filtro-notif"];
	$pFiltro_metod = $_POST["filtro-metod"];

	$where = "";

	if (strlen($pFiltro_recip) > 0)
		$where .= " AND id_destino IN ($pFiltro_recip)";

	if (strlen($pFiltro_notif) > 0)
		$where .= " AND id_notificacao IN ($pFiltro_notif)";

	if (strlen($pFiltro_metod) > 0)
		$where .= " AND metodo IN ($pFiltro_metod)";

	$db = new Mysql();

	// Contar NOVAS, PROCESSANDO, ERRO
	$db->query("
SELECT
	SUM(status = 0) AS novas, 
	SUM(status = 1) AS processando, 
	SUM(status > 1) AS erro
FROM
	gelic_mensagens
WHERE
	id > 0$where
HAVING
	novas IS NOT NULL AND
	processando IS NOT NULL AND
	erro IS NOT NULL");
	if ($db->nextRecord())
	{
		$aReturn[1] = $db->f("novas");
		$aReturn[2] = $db->f("processando");
		$aReturn[3] = $db->f("erro");
	}

	// Contar SUCESSO
	$db->query("SELECT COUNT(*) AS total FROM gelic_mensagens_log WHERE id > 0$where");
	if ($db->nextRecord())
		$aReturn[4] = $db->f("total");
} 
echo json_encode($aReturn);

?>
