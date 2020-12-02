<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	//deixar somente o REP daqui pra frente
	if ($sInside_tipo <> 4)
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_dn = intval($_POST["f-dn"]);

	$db = new Mysql();

	//verifica se ainda tenho acesso no DN e se o DN esta ativo
	$db->query("
SELECT
	ac.id_cliente_acesso
FROM
	gelic_clientes_acesso AS ac
	INNER JOIN gelic_clientes AS dn ON dn.id = ac.id_cliente_acesso
WHERE
	ac.id_cliente = $sInside_id AND
    ac.id_cliente_acesso = $pId_dn AND
	dn.ativo > 0 AND
	deletado = 0");
	if ($db->nextRecord())
	{
		saveConfig("dn", $pId_dn);
		$_SESSION[SESSION_ID_DN] = $pId_dn;
		$aReturn[0] = 1; //sucesso
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
