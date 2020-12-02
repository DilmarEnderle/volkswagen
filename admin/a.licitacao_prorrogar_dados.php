<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_prorrogar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["id_licitacao"]);

	$db = new Mysql();


	//--- data/hora ---
	$db->query("
SELECT 
	lic.datahora_abertura, 
	lic.datahora_entrega,
	(SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo 
FROM 
	gelic_licitacoes AS lic
WHERE 
	lic.id = $pId_licitacao AND 
	lic.deletado = 0");
	if ($db->nextRecord())
	{
		if ($db->f("ultimo_tipo") == 31)
		{
			$aReturn[0] = 8;
			echo json_encode($aReturn);
			exit;
		}
		else
		{
			$dData_abertura = mysqlToBr(substr($db->f("datahora_abertura"),0,10));
			$dHora_abertura = substr($db->f("datahora_abertura"),11,5);
			if ($dHora_abertura == "00:00") $dHora_abertura = "";

			$dData_entrega = mysqlToBr(substr($db->f("datahora_entrega"),0,10));
			$dHora_entrega = substr($db->f("datahora_entrega"),11,5);
			if ($dHora_entrega == "00:00") $dHora_entrega = "";
		}
	}
	else
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}



	//--- motivos ---
	$tMotivos = '<select id="i-motivo" class="iText" style="width: 100%; height: 34px;" onchange="listarSubmotivos();"><option value="0">- escolha o motivo -</option>';
	$db->query("SELECT id, descricao FROM gelic_motivos WHERE tipo = 20 AND id_parent = 0 ORDER BY descricao");
	while ($db->nextRecord())
		$tMotivos .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';
	$tMotivos .= '</select>';



	$oOutput = '
		<div class="ultimate-row">Nova Data/Hora de Abertura</div>
		<div class="ultimate-row">
			<input id="i-data-abertura" class="iText" type="text" placeholder="dd/mm/aaaa" name="f-data_abertura" maxlength="20" value="'.$dData_abertura.'" style="float: left; width: 100px;">
			<input id="i-hora-abertura" class="iText" type="text" placeholder="hh:mm (24h)" name="f-hora_abertura" maxlength="10" value="'.$dHora_abertura.'" style="float: left; width: 100px; border-left: 0;">
		</div>
		<div class="ultimate-row" style="margin-top: 20px;">Nova Data/Hora de Entrega</div>
		<div class="ultimate-row">
			<input id="i-data-entrega" class="iText" type="text" placeholder="dd/mm/aaaa" name="f-data_abertura" maxlength="20" value="'.$dData_entrega.'" style="float: left; width: 100px;">
			<input id="i-hora-entrega" class="iText" type="text" placeholder="hh:mm (24h)" name="f-hora_abertura" maxlength="10" value="'.$dHora_entrega.'" style="float: left; width: 100px; border-left: 0;">
		</div>
		<div class="ultimate-row" style="margin-top: 20px;">Motivo</div>
		<div class="ultimate-row">'.$tMotivos.'</div>
		<div class="ultimate-row submotivo" style="margin-top: 20px; display: none;">Submotivo</div>
		<div class="ultimate-row submotivo" style="display: none;"><select id="i-submotivo" class="iText" style="width: 100%; height: 34px;"><option value="0">- escolha um submotivo (opcional) -</option></select></div>
		<div class="ultimate-row" style="margin-top: 20px;">Observação</div>
		<div class="ultimate-row">
			<textarea id="i-observacao" class="iText" style="width: 100%; height: 100px; resize: none; padding: 6px;" placeholder="(opcional)"></textarea>
		</div>
		<div id="ultimate-error"></div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
