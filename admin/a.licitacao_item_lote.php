<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_editar", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_lote = intval($_POST["id-lote"]);

	$db = new Mysql();
	$db->query("SELECT lote FROM gelic_licitacoes_lotes WHERE id = $pId_lote AND id_licitacao = $pId_licitacao");
	if ($db->nextRecord())
	{
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = '
		<div class="ultimate-row">
			<input id="i-lote" class="iText" type="text" placeholder="- nome lote -" maxlength="40" value="'.htmlentities(utf8_encode($db->f("lote"))).'" style="float: left; width: 100%;">
		</div>';
	}
} 
echo json_encode($aReturn);

?>
