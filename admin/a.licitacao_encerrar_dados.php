<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_encerrar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	//--- motivos ---
	$tMotivos = '<select id="i-motivo-encerrar" class="iText" style="width: 100%; height: 34px;" onchange="listarSubmotivosEncerramento();"><option value="0">- escolha o motivo -</option>';
	$db->query("SELECT id, descricao FROM gelic_motivos WHERE tipo = 10 AND id_parent = 0 ORDER BY descricao");
	while ($db->nextRecord())
		$tMotivos .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';
	$tMotivos .= '</select>';

	$oOutput = '
		<div class="ultimate-row" style="margin-top: 14px; line-height: 23px;">Deseja realmente encerrar esta licitação?</div>
		<div class="ultimate-row">'.$tMotivos.'</div>
		<div class="ultimate-row submotivo-encerrar" style="margin-top: 20px; display: none;">Submotivo</div>
		<div class="ultimate-row submotivo-encerrar" style="display: none;"><select id="i-submotivo-encerrar" class="iText" style="width: 100%; height: 34px;"><option value="0">- escolha um submotivo (opcional) -</option></select></div>

		<div class="ultimate-row" style="margin-top: 20px; line-height: 23px;">ATA</div>
		<div id="upl-btn-ata" class="ultimate-row" style="border: 1px solid #cccccc; padding: 1px; box-sizing: border-box;">
			<a class="bt-style-2 fl" href="javascript:void(0);" onclick="selectFileATA();">Anexar Ata</a>
			<span class="t11 red fr" style="line-height: 30px; margin-right: 9px;">Máx. 100 MB</span>
		</div>

		<div id="upl-loading-ata" class="ultimate-row" style="display: none; border: 1px solid #cccccc; box-sizing: border-box; height: 34px;">
			<div id="upl-bar-ata" style="position: absolute; left: 1px; top: 1px; width: 468px; height: 30px; background-color: #e2e99e;"></div>
			<span id="upl-per-ata" class="t11 gray-4c" style="display: block; position: absolute; left: 0; top: 0; line-height: 32px; width: 470px; text-align: center;">Carregando...</span>
		</div>

		<div id="upl-ready-ata" class="ultimate-row" style="display: none; border: 1px solid #cccccc; box-sizing: border-box; height: 34px;">
			<span id="upl-filename-ata" class="t12 red fl ml-10 italic" style="line-height: 32px;">nome</span>
			<span id="upl-filesize-ata" class="t12 red fr gray-88 italic" style="line-height: 32px; margin-right: 36px;">size</span>
			<a class="btn-x24" href="javascript:void(0);" onclick="cancelUploadATA();" style="display: block; position: absolute; right: 4px; top: 4px;" title="Cancelar"></a>
		</div>

		<div id="ultimate-error"></div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
