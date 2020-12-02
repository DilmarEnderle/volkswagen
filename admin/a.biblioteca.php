<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("bib_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">BIBLIOTECA</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);

		echo $tPage->body;
		exit;
	}

	$db = new Mysql();
	
	$db->query("SELECT COUNT(*) AS total FROM gelic_biblioteca");
	$db->nextRecord();
	$dTotal = $db->f("total");

	$tPage = new Template("a.biblioteca.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TOTAL}}", $dTotal);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
