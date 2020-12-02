<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("msg_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">FILA/ENVIO DE MENSAGENS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);

		echo $tPage->body;
		exit;
	}

	$tPage = new Template("a.mensagens.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
