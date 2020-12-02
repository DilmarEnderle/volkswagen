<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$db = new Mysql();
	$dClientes = "";
	$db->query("SELECT id, nome FROM gelic_clientes WHERE ativo = 1 AND id_parent = 0 AND tipo = 2 ORDER BY nome");
	while ($db->nextRecord())
		$dClientes .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';

	$tPage = new Template("a.relatorios.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{CLIENTES}}", $dClientes);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
