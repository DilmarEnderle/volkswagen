<?php 

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array("0");
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFile_name = utf8_decode($_FILES["f-upload"]["name"]);
	$pFile_size = $_FILES["f-upload"]["size"];
	$arquivo_md5 = strtolower(getFilename(mt_rand(9,999999), $pFile_name, 'atarp'.time().$sInside_id));

	if (move_uploaded_file($_FILES["f-upload"]["tmp_name"], UPLOAD_DIR."~upatarp_".$sInside_id."_".$arquivo_md5))
	{
	 	$aReturn[0] = "1"; //sucesso
	 	$aReturn[1] = utf8_encode($pFile_name); //LONG FILE NAME

	 	$pShort_file_name = $pFile_name;
	 	if (strlen($pShort_file_name) > 74)
	 		$pShort_file_name = substr($pShort_file_name, 0, 63)."...".substr($pShort_file_name, -8);

	 	$aReturn[2] = utf8_encode($pShort_file_name); //SHORT FILE NAME
	 	$aReturn[3] = formatSizeUnits($pFile_size); //FILE SIZE
	 	$aReturn[4] = $arquivo_md5; //UNIQUE FILE NAME
	}
}
echo json_encode($aReturn);
?>
