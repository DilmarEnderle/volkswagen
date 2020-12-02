<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId = intval($_POST["id"]);
	$db = new Mysql();
	$db->query("UPDATE gelic_licitacoes_itens_participantes SET deletado = 1 WHERE id = $pId");
	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
