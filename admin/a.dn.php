<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$pId_cidade = $_POST["f-cidade"];
	if ($pId_cidade == 0)
	{
		echo '<span class="gray-88 italic" style="line-height:24px;">- selecione uma cidade -</span>';
		exit;
	}

	$db = new Mysql();
	$oOutput = '';
	$db->query("SELECT adve FROM gelic_cidades WHERE id = $pId_cidade AND adve > 0");
	if ($db->nextRecord())
	{
		$dAdve = $db->f("adve");
		$db->query("SELECT nome FROM gelic_clientes WHERE tipo = 2 AND adve = $dAdve ORDER BY dn");
		while ($db->nextRecord())
			$oOutput .= '<br>'.utf8_encode($db->f("nome"));

		if (strlen($oOutput) > 0)
			$oOutput = substr($oOutput, 4);
		else
			$oOutput = '<span class="red italic" style="line-height:24px;">Nenhum DN encontrado!</span>';	
	}
	else
	{
		$oOutput = '<span class="red italic" style="line-height:24px;">Nenhum DN encontrado!</span>';
	}

	echo $oOutput;
}

?>
