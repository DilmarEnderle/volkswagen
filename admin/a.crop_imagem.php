<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$gFile = $_GET["im"];
	$x1 = intval($_GET["x1"]);
	$y1 = intval($_GET["y1"]);
	$x2 = intval($_GET["x2"]);
	$y2 = intval($_GET["y2"]);
	$w = $x2-$x1;
	$h = $y2-$y1;
	$sw = intval($_GET["sw"]);
	$sh = intval($_GET["sh"]);
	
	if ($w > 0 && $h > 0)
	{
		if (file_exists(UPLOAD_DIR."im/".$gFile))
		{
			$size = getimagesize(UPLOAD_DIR."im/".$gFile);
			$crop_x1 = intval($x1 / $sw * $size[0]);
			$crop_y1 = intval($y1 / $sh * $size[1]);
			$crop_x2 = intval($x2 / $sw * $size[0]);
			$crop_y2 = intval($y2 / $sh * $size[1]);
			$crop_w = $crop_x2-$crop_x1;
			$crop_h = $crop_y2-$crop_y1;
			
			$iSource = imagecreatefrompng(UPLOAD_DIR."im/".$gFile);
			$iFinal = imagecreatetruecolor($crop_w,$crop_h);
			imagecopyresampled($iFinal,$iSource,0,0,$crop_x1,$crop_y1,$crop_w,$crop_h,$crop_w,$crop_h);
			imagepng($iFinal,UPLOAD_DIR."im/~".$gFile);
			imagedestroy($iSource);
			imagedestroy($iFinal);
	
			unlink(UPLOAD_DIR."im/".$gFile);
			rename(UPLOAD_DIR."im/~".$gFile,UPLOAD_DIR."im/".$gFile);
		}
	}
}

?>
