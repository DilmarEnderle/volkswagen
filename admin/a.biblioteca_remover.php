<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("bib_excluir", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId = intval($_POST["id"]);

	$db = new Mysql();
	$db->query("SELECT tipo, item FROM gelic_biblioteca WHERE id = $pId");
	$db->nextRecord();
	$dTipo = $db->f("tipo");
	$dItem = $db->f("item");

	if ($dTipo == 0) //remover arquivo
		@unlink(UPLOAD_DIR."/lib/".$dItem);

	//remover registro
	$db->query("DELETE FROM gelic_biblioteca WHERE id = $pId");

	//recount
	$db->query("SELECT COUNT(*) AS total FROM gelic_biblioteca");
	$db->nextRecord();

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $db->f("total");
}
echo json_encode($aReturn);

?>
