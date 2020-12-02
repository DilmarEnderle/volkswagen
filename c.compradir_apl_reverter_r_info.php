<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	//deixar somente BO daqui pra frente
	if ($sInside_tipo <> 1)
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();
	$db->query("SELECT texto FROM gelic_texto WHERE id = 15");
	$db->nextRecord();
	$dObservacoes = utf8_encode($db->f("texto"));

	$oOutput = '
		<div class="ultimate-row" style="margin-top: 20px;">Observações Gerais <span class="gray-88 italic">(opcional)</span></div>
		<div class="ultimate-row"><textarea id="i-obs" class="apl-textarea" style="width: 100%; height: 140px;">'.$dObservacoes.'</textarea></div>
		<div id="ultimate-error"></div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
