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

	//--- motivos ---
	$tMotivos = '<select id="i-motivo-declinar-apl" class="iText" style="width: 100%; height: 34px;" onchange="listarSubmotivosAPL();"><option value="0">- escolha o motivo -</option>';
	$db->query("SELECT id, descricao FROM gelic_motivos WHERE tipo = 40 AND id_parent = 0 ORDER BY descricao");
	while ($db->nextRecord())
		$tMotivos .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';
	$tMotivos .= '</select>';

	$db->query("SELECT texto FROM gelic_texto WHERE id = 3");
	$db->nextRecord();
	$dObservacoes = utf8_encode($db->f("texto"));

	$oOutput = '
		<div class="ultimate-row" style="margin-top: 14px; line-height: 23px;">Selecione o motivo de reprovação.</div>
		<div class="ultimate-row">'.$tMotivos.'</div>
		<div class="ultimate-row submotivo-declinar-apl" style="margin-top: 20px; display: none;">Submotivo</div>
		<div class="ultimate-row submotivo-declinar-apl" style="display: none;"><select id="i-submotivo-declinar-apl" class="iText" style="width: 100%; height: 34px;"><option value="0">- escolha um submotivo (opcional) -</option></select></div>
		<div class="ultimate-row" style="margin-top: 20px;">Observações Gerais</div>
		<div class="ultimate-row"><textarea id="i-obs" class="apl-textarea" style="width: 100%; height: 200px;">'.$dObservacoes.'</textarea></div>
		<div id="ultimate-error"></div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
