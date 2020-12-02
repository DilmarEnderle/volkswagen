<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (in_array($_POST["a"], $xAccess))
		$aReturn[0] = 1; //acesso permitido
} 
echo json_encode($aReturn);

?>
