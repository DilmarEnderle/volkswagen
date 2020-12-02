<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$gUf = '';
	$aUf = array('AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO');
	
	if (isset($_GET["uf"]))
		$gUf = strtoupper(trim($_GET["uf"]));

	if (!in_array($gUf, $aUf))
		$gUf = '';

	$tPage = new Template("a.cidade.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{UF}}", $gUf);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
