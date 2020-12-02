<?php 

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFile_name = $_FILES["f-upload"]["name"];
	$pFile_size = $_FILES["f-upload"]["size"];
	$pExt = '';
	$tmp = explode('.',$pFile_name);
	if (count($tmp) > 0)
		$pExt = end($tmp);

	if (strlen($pExt) > 0)
		$new_file_name = md5(time()).'.'.strtolower($pExt);
	else
		$new_file_name = md5(time());

	if (file_exists(UPLOAD_DIR."/~up_".$sInside_id.".tmp"))
		unlink(UPLOAD_DIR."/~up_".$sInside_id.".tmp");

	if (move_uploaded_file($_FILES["f-upload"]["tmp_name"], UPLOAD_DIR."/~up_".$sInside_id.".tmp"))
		echo $new_file_name.'_'.formatSizeUnits($pFile_size);
}

?>
