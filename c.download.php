<?php
	
require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	if (file_exists(UPLOAD_DIR."~licitacao_exp_".$sInside_id.".pdf"))
	{
		$gId_licitacao = $_GET["id"];
		$now = date("Ymd_His");
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="Licitacao_'.$gId_licitacao.'_'.$now.'.pdf"'); 
		header('Content-Length:'.filesize(UPLOAD_DIR."~licitacao_exp_".$sInside_id.".pdf"));
		readfile(UPLOAD_DIR."~licitacao_exp_".$sInside_id.".pdf");
	}
}

?>
