<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$gEstado = $_GET["f-estado"];

	$db = new Mysql();
	$oRows = "";
	$db->query("SELECT id, nome FROM gelic_cidades WHERE uf = '$gEstado' AND id > 0 ORDER BY nome");
	while ($db->nextRecord())
		$oRows .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
	
	if (strlen($oRows) > 0)
		echo '<option value="0">- cidade -</option>'.$oRows;
	else
		echo '<option value="0">- selecione um estado primeiro -</option>';		
} 

?>
