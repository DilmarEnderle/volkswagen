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

	$pId_apl = intval($_POST["f-id-apl"]);

	$db = new Mysql();


	$db->query("SELECT texto FROM gelic_texto WHERE id = 5");
	$db->nextRecord();
	$dObservacoes = utf8_encode($db->f("texto"));

	$db->query("
SELECT
	apl.id,
    lic.datahora_abertura,
    lic.datahora_limite,
	DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
    UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite
FROM
	gelic_licitacoes_apl AS apl
    INNER JOIN gelic_licitacoes AS lic ON lic.id = apl.id_licitacao
WHERE
	apl.id = $pId_apl");
	$db->nextRecord();

	$dLimite = segundosConv($db->f("limite"));

	if ($db->f("limite") < 0)
		$dLimite_cor = "ff0000";
	else
		$dLimite_cor = "00c400";

	if ($db->f("data_abertura_dias") < 0)
		$dLimite_bg = "282828"; //black
	else
	{
		if ($dLimite["h"] >= 2)
			$dLimite_bg = "176f03"; //green
		else if ($dLimite["h"] > 0 && $dLimite["h"] < 2)
			$dLimite_bg = "cf0017"; //bright red
		else if ($dLimite["h"] <= 0)
			$dLimite_bg = "910017"; //dark red
	}

	$dab_h = substr($db->f("datahora_abertura"),11,5);
	if ($dab_h == "00:00") $dab_h = "--:--";

	$dal_h = substr($db->f("datahora_limite"),11,5);
	if ($dal_h == "00:00") $dal_h = "--:--";

	$oOutput = '
		<div class="ultimate-row" style="padding: 10px; border: 1px solid #dfdfdf; background-color: #f0f0f1; box-sizing: border-box;">
			<div>Data/Hora Abertura: <span>'.mysqlToBr(substr($db->f("datahora_abertura"),0,10)).' '.$dab_h.' ('.niceDays($db->f("data_abertura_dias")).')</span></div>
			<div style="margin-top: 10px; line-height: 23px;">Prazo Limite Atual: <span class="bold" style="color: #'.$dLimite_cor.';">'.mysqlToBr(substr($db->f("datahora_limite"),0,10)).' '.$dal_h.'</span><span class="fr" style="padding: 0 6px; line-height: 23px; color: #ffffff; background-color:#'.$dLimite_bg.'">'.$dLimite["h"].'h '.$dLimite["m"].'m</span></div>
		</div>
		<div class="ultimate-row np" style="margin-top: 20px; line-height: 23px;"><span class="italic t-red t13">Verifique se o Prazo Limite Atual é suficiente para que o DN possa re-enviar a APL. Utilize os campos abaixo para informar um novo Prazo Limite. Deixe em branco para manter o atual.</span><br><br>Novo Prazo Limite</div>
		<div class="ultimate-row np">
			<input id="i-pl-data" class="iText fl" type="text" placeholder="dd/mm/aaaa" maxlength="10" style="width: 134px;">
			<input id="i-pl-hora" class="iText fl" type="text" placeholder="hh:mm (24h)" maxlength="10" style="width: 120px; margin-left: 4px;">
		</div>
		<div class="ultimate-row" style="margin-top: 20px;">Observações Gerais</div>
		<div class="ultimate-row"><textarea id="i-pl-obs" class="apl-textarea" style="width: 100%; height: 140px;">'.$dObservacoes.'</textarea></div>
		<div id="ultimate-error"></div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
