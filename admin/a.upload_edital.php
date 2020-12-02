<?php 

require_once "include/config.php";
require_once "include/essential.php";

$jReturn = new stdClass();
$jReturn->id = 0;
$jReturn->status = 0;
$jReturn->long_filename = "";
$jReturn->short_filename = "";
$jReturn->file_size = "";
$jReturn->file_md5 = "";
$jReturn->publish_date = "";
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFile_name = utf8_decode($_FILES["f-upload"]["name"]);
	$pFile_size = $_FILES["f-upload"]["size"];
	$arquivo_md5 = strtolower(getFilename(mt_rand(9,999999), $pFile_name, 'edital'.time().$sInside_id));

	if (move_uploaded_file($_FILES["f-upload"]["tmp_name"], UPLOAD_DIR."~upedital_".$sInside_id."_".$arquivo_md5))
	{
		$jReturn->status = 1; //SUCCESS
		$jReturn->long_filename = utf8_encode($pFile_name);
		$jReturn->short_filename = $pFile_name;
		if (strlen($jReturn->short_filename) > 56)
			$jReturn->short_filename = substr($jReturn->short_filename, 0, 45)."...".substr($jReturn->short_filename, -8);
		$jReturn->short_filename = utf8_encode($jReturn->short_filename);
		$jReturn->file_size = formatSizeUnits($pFile_size);
		$jReturn->file_md5 = $arquivo_md5;
	}
}
echo json_encode($jReturn);
?>
