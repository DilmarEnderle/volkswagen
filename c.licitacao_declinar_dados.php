<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1) //BO
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["f-id-licitacao"]);

	$db = new Mysql();

	//--- motivos ---
	$tMotivos = '<select id="i-motivo-declinar" class="iText" style="width: 100%; height: 34px;" onchange="listarSubmotivosDeclinio();"><option value="0">- escolha o motivo -</option>';
	$db->query("SELECT id, descricao FROM gelic_motivos WHERE tipo = 30 AND id_parent = 0 ORDER BY descricao");
	while ($db->nextRecord())
		$tMotivos .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';
	$tMotivos .= '</select>';

	$oOutput = '
		<div class="ultimate-row" style="margin-top: 14px; line-height: 23px;">Deseja realmente declinar a participação?</div>
		<div class="ultimate-row">'.$tMotivos.'</div>
		<div class="ultimate-row submotivo-declinar" style="margin-top: 20px; display: none;">Submotivo</div>
		<div class="ultimate-row submotivo-declinar" style="display: none;"><select id="i-submotivo-declinar" class="iText" style="width: 100%; height: 34px;"><option value="0">- escolha um submotivo (opcional) -</option></select></div>
		<div class="ultimate-row" style="margin-top: 20px;">Observações</div>
		<div class="ultimate-row"><textarea id="i-obs-declinar" class="apl-textarea" style="width: 100%; height: 100px; border-style: solid;"></textarea></div>
		<div id="ultimate-error"></div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
