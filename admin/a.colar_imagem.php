<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$r_array = array("",0,0);

	$pFile = $_POST["pfn"];
	$pImage_data = $_POST["imgdata"];
	if (strlen($pFile) > 0)
	{
		if (file_exists(UPLOAD_DIR."~".$pFile))
			unlink(UPLOAD_DIR."~".$pFile);
	}
	else
		$pFile = "~".strtolower(md5($sInside_id.mt_rand(9,999999).time())).".png";
	
	$r_array[0] = $pFile;
	$just_data = substr($pImage_data, strpos($pImage_data,",")+1);
	$just_data = str_replace(" ", "+", $just_data);
	file_put_contents(UPLOAD_DIR.$pFile, base64_decode($just_data));
	//chmod(UPLOAD_DIR."im/".$pFile,0777);
	$size = getimagesize(UPLOAD_DIR.$pFile);
	
	$w = $size[0];
	$h = $size[1];
	
	if ($w > 800)
	{
		$s = 800 / $w;
		$w = 800;
		$h = intval($h * $s);
	}
	
	if ($h > 800)
	{
		$s = 800 / $h;
		$h = 800;
		$w = intval($w * $s);
	}
	
	$r_array[1] = $w;
	$r_array[2] = $h;
	
	$t = 200;
	if ($h > 290)
	{
		$t -= intval((($h - 290) / 2));
		if ($t < 30) $t = 30;
	}
	$r_array[3] = $t;

	echo json_encode($r_array);
}

?>
