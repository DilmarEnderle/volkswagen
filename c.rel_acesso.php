<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$aAcesso = array();
	$db = new Mysql();
	$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = 0 AND config = 'relatorios'");
	if ($db->nextRecord())
		$aAcesso = explode(" ", $db->f("valor"));

	if (in_array($_POST["a"], $aAcesso))
		$aReturn[0] = 1; //acesso permitido
}
echo json_encode($aReturn);

?>
