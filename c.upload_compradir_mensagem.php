<?php 

require_once "include/config.php";
require_once "include/essential.php";

$xReturn = 'ERRO{^=?!?=^}0';
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	//--- Ajustar inside_id se for Representante ---
	if ($sInside_tipo == 4) //REP
		$sInside_id = $_SESSION[SESSION_ID_DN];
	//----------------------------------------------


	$pFile_name = $_FILES["f-upload"]["name"];
	$pFile_size = $_FILES["f-upload"]["size"];

	if (file_exists(UPLOAD_DIR."/~upcdmsgc_".$sInside_id.".tmp"))
		unlink(UPLOAD_DIR."/~upcdmsgc_".$sInside_id.".tmp");

	if (move_uploaded_file($_FILES["f-upload"]["tmp_name"], UPLOAD_DIR."/~upcdmsgc_".$sInside_id.".tmp"))
		$xReturn = $pFile_name.'{^=?!?=^}'.formatSizeUnits($pFile_size);
}
echo $xReturn;

?>
