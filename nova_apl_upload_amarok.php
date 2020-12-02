<?php 

require_once "include/config.php";
require_once "include/essential.php";

$jReturn = new stdClass();
$jReturn->status = 0;
$jReturn->long_filename = "";
$jReturn->short_filename = "";
$jReturn->file_size = "";
$jReturn->file = "";

//if (isInside())
//{
	$sInside_id = 9999;

	$pFile_name = utf8_decode($_FILES["f-upload"]["name"]);
	$pFile_size = $_FILES["f-upload"]["size"];

	if (file_exists(UPLOAD_DIR."/~upload_apl_amarok_".$sInside_id.".tmp"))
		unlink(UPLOAD_DIR."/~upload_apl_amarok_".$sInside_id.".tmp");

	if (move_uploaded_file($_FILES["f-upload"]["tmp_name"], UPLOAD_DIR."/~upload_apl_amarok_".$sInside_id.".tmp"))
	{
		$jReturn->status = 1; //NEW FILE
		$jReturn->long_filename = utf8_encode($pFile_name); //LONG FILE NAME

		$pShort_file_name = $pFile_name;
		if (strlen($pShort_file_name) > 84)
			$pShort_file_name = substr($pShort_file_name, 0, 73)."...".substr($pShort_file_name, -8);

		$jReturn->short_filename = utf8_encode($pShort_file_name); //SHORT FILE NAME
		$jReturn->file_size = formatSizeUnits($pFile_size); //FILE SIZE
	}
//}
echo json_encode($jReturn);

?>
