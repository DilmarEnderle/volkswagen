<?php 

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array("0");
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$pFile_name = utf8_decode($_FILES["f-upload-apr"]["name"]);
	$pFile_size = $_FILES["f-upload-apr"]["size"];

	if (file_exists(UPLOAD_DIR."/~upaprc_".$sInside_id.".tmp"))
		unlink(UPLOAD_DIR."/~upaprc_".$sInside_id.".tmp");

	if (move_uploaded_file($_FILES["f-upload-apr"]["tmp_name"], UPLOAD_DIR."/~upaprc_".$sInside_id.".tmp"))
	{
		$aReturn[0] = "1"; //sucesso
		$aReturn[1] = utf8_encode($pFile_name); //LONG FILE NAME

		$pShort_file_name = $pFile_name;
		if (strlen($pShort_file_name) > 74)
			$pShort_file_name = substr($pShort_file_name, 0, 63)."...".substr($pShort_file_name, -8);

		$aReturn[2] = utf8_encode($pShort_file_name); //SHORT FILE NAME
		$aReturn[3] = formatSizeUnits($pFile_size); //FILE SIZE
	}
}
echo json_encode($aReturn);

?>
