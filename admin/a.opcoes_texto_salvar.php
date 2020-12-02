<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cnf_editar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	//POST VALUES
	$pId = intval($_POST["f-id"]);
	$pTexto = preg_replace("/\s+/", " ", $db->escapeString(trim(utf8_decode($_POST["f-texto"]))));

	$db->query("UPDATE gelic_texto SET texto = '$pTexto' WHERE id = $pId");
	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
