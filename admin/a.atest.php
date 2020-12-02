<?php

require_once "include/config.php";
require_once "include/essential.php";

$db = new Mysql();
$db1 = new Mysql();

$ontem = date('Y-m-d', strtotime("-1 days"));
$db->query("SELECT id FROM gelic_licitacoes WHERE datahora_abertura >= '$ontem 00:00:00' AND datahora_abertura <= '$ontem 23:59:59'");
while ($db->nextRecord())
{
	$dId_licitacao = $db->f("id");
	echo '<br>'.$dId_licitacao;		
}

?>
