<?php 

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array("status">=0, "long_filename"=>"", "short_filename"=>"", "file_size"=>"", "is_new"=>1);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFile_name = utf8_decode($_FILES["f-upload"]["name"]);
	$pFile_size = $_FILES["f-upload"]["size"];

	if (file_exists(UPLOAD_DIR."~updoc_".$sInside_id.".tmp"))
		unlink(UPLOAD_DIR."~updoc_".$sInside_id.".tmp");

	if (move_uploaded_file($_FILES["f-upload"]["tmp_name"], UPLOAD_DIR."~updoc_".$sInside_id.".tmp"))
	{
	 	$aReturn["status"] = 1; //sucesso
	 	$aReturn["long_filename"] = utf8_encode($pFile_name); //LONG FILE NAME

	 	$pShort_file_name = $pFile_name;
	 	if (strlen($pShort_file_name) > 56)
	 		$pShort_file_name = substr($pShort_file_name, 0, 45)."...".substr($pShort_file_name, -8);

	 	$aReturn["short_filename"] = utf8_encode($pShort_file_name); //SHORT FILE NAME
	 	$aReturn["file_size"] = formatSizeUnits($pFile_size); //FILE SIZE
	}
}
echo json_encode($aReturn);

?>
