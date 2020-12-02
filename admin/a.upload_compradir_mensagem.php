<?php 

require_once "include/config.php";
require_once "include/essential.php";

$xReturn = 'ERRO{^=?!?=^}0';
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFile_name = $_FILES["f-upload"]["name"];
	$pFile_size = $_FILES["f-upload"]["size"];


	if (file_exists(UPLOAD_DIR."/~upcdmsga_".$sInside_id.".tmp"))
	 	unlink(UPLOAD_DIR."/~upcdmsga_".$sInside_id.".tmp");

	if (move_uploaded_file($_FILES["f-upload"]["tmp_name"], UPLOAD_DIR."/~upcdmsga_".$sInside_id.".tmp"))
		$xReturn = $pFile_name.'{^=?!?=^}'.formatSizeUnits($pFile_size);
}
echo $xReturn;

?>
