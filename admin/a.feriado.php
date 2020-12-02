<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("fer_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">FERIADOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);
		
		echo $tPage->body;
		exit;
	}

	$tRow = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<a class="alnk t14 abs lh-30 pl-10" href="a.feriado_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{D}}</a>
		<a class="alnk t14 abs lh-30 lf-500" href="a.feriado_editar.php?id={{ID}}">{{DIA}}</a>
		<a class="abs fixo{{FX}} lf-800 tp-5 lh-20">{{F}}</a>
		<a href="javascript:void(0);" onclick="removerFeriado({{ID}},false);" title="Remover Feriado"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
	</div>';

	$aFixo = array();
	$aFixo[0] = utf8_decode("Não");
	$aFixo[1] = "Sim";

	$aMes = array();
	$aMes[1] = "Janeiro";
	$aMes[2] = "Fevereiro";
	$aMes[3] = utf8_decode("Março");
	$aMes[4] = "Abril";
	$aMes[5] = "Maio";
	$aMes[6] = "Junho";
	$aMes[7] = "Julho";
	$aMes[8] = "Agosto";
	$aMes[9] = "Setembro";
	$aMes[10] = "Outubro";
	$aMes[11] = "Novembro";
	$aMes[12] = "Dezembro";
	
	$db = new Mysql();
	
	$oRows = "";
	
	$db->query("SELECT COUNT(*) AS total FROM gelic_feriados");
	$db->nextRecord();
	$total_recs = $db->f("total");
	
	$query = "SELECT * FROM gelic_feriados ORDER BY mes,dia";
	$db->query($query);
	while ($db->nextRecord())
	{
		$tTmp = $tRow;
		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{D}}", $db->f("nome"), $tTmp);
		$tTmp = str_replace("{{DIA}}", $db->f("dia")." - ".$aMes[$db->f("mes")], $tTmp);
		$tTmp = str_replace("{{FX}}", $db->f("fixo"), $tTmp);
		$tTmp = str_replace("{{F}}", $aFixo[$db->f("fixo")], $tTmp);
		$oRows .= $tTmp;
	}
	
	$tPage = new Template("a.feriado.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TOTAL}}", $total_recs);
	$tPage->replace("{{ROWS}}", utf8_encode($oRows));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
