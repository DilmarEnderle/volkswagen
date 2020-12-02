<?php

require_once "include/config.php";
require_once "include/essential.php";

$gId_licitacao = 0;
if (isset($_GET["id"]))
	$gId_licitacao = intval($_GET["id"]);


$db = new Mysql();

$aStatus = array();
$aStatus[0] = '---';
$db->query("SELECT id, descricao, cor_texto, cor_fundo FROM gelic_status ORDER BY id");
while ($db->nextRecord())
	$aStatus[$db->f("id")] = '<a style="float: left; width:30px;">'.$db->f("id").'</a><a style="float: left; color:#'.$db->f("cor_texto").'; background-color:#'.$db->f("cor_fundo").';">'.utf8_encode($db->f("descricao")).'</a>';


$aAba = array();
$aAba[0] = '---';
$aAba[7] = 'Oportunidades';
$aAba[8] = 'Varal';
$aAba[9] = 'Em Participação';
$aAba[10] = 'Resultado Perdidas';
$aAba[11] = 'Resultado Ganhas';
$aAba[12] = 'Calendário';
$aAba[14] = 'APL';
$aAba[15] = 'Não Pertinente';
$aAba[16] = 'Expiradas';
$aAba[17] = 'Todas';


$aEvent = array();
$aEvent[13] = 'Troca de fase ({{FR}} - {{TO}})';
$aEvent[14] = 'Final/Existe APL ({{F}})';
$aEvent[31] = 'Admin encerrou licitação';
$aEvent[32] = 'Admin reverteu o encerramento da licitação';
$aEvent[36] = 'Admin alterou o status manualmente';
$aEvent[41] = 'APL Enviada';
$aEvent[42] = 'APL Aprovada';
$aEvent[43] = 'APL Reprovada';
$aEvent[44] = 'APL Aprovação Revertida';
$aEvent[45] = 'APL Reprovação Revertida';


$aGrupo = array();
$aGrupo[1] = 'ADMIN';
$aGrupo[2] = 'BACK OFFICE';
$aGrupo[3] = 'DN APL';
$aGrupo[4] = 'OUTRO DN';


echo '<!DOCTYPE html>
<html lang="pt">
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: Arial; }
		div { overflow: hidden; }
	</style>
</head>
<body>';


$db->query("SELECT tipo, id_valor_1, id_valor_2, data_hora, texto FROM gelic_historico WHERE id_licitacao = $gId_licitacao AND tipo IN (13,14,31,32,36,41,42,43,44,45) ORDER BY id");
while ($db->nextRecord())
{
	$dTipo = $db->f("tipo");
	$dData_hora = $db->f("data_hora");
	$aTexto = json_decode($db->f("texto"), true);

	$evento = $aEvent[$dTipo];
	if ($dTipo == 13)
	{
		$evento = str_replace("{{FR}}", $db->f("id_valor_1"), $evento);
		$evento = str_replace("{{TO}}", $db->f("id_valor_2"), $evento);
	}
	else if ($dTipo == 14)
	{
		$evento = str_replace("{{F}}", $db->f("id_valor_1"), $evento);
	}

	echo '<div style="background-color: #555555; color: #ffffff; text-align: left; line-height: 30px; padding-left: 12px;">'.$dData_hora.' ---- '.$evento.'</div>';
	if (strlen($db->f("texto")) > 0)
	{
		echo '<div style="border-bottom: 1px solid #eeeeee;">
			<span style="float: left; width: 100%; font-weight: bold;">FROM:</span>
		</div>';

		for ($i=0; $i<count($aTexto["fr"]); $i++)
		{
			echo '<div style="border-bottom: 1px solid #eeeeee;">
				<span style="float: left; width: 20%; padding-left:20px; box-sizing: border-box;">'.$aGrupo[$aTexto["fr"][$i]["grupo"]].'</span>
				<span style="float: left; width: 30%;"><a style="float: left; width:30px;">'.$aTexto["fr"][$i]["aba"].'</a><a class="float: left;">'.$aAba[$aTexto["fr"][$i]["aba"]].'</a></span>
				<span style="float: left; width: 40%;">'.$aStatus[$aTexto["fr"][$i]["status"]].'</span>';

			if (isset($aTexto["fr"][$i]["fixo"]))
				echo '<span style="float: left; width: 10%;">'.$aTexto["fr"][$i]["fixo"].'</span>';

			echo '</div>';
		}

		echo '<div style="border-bottom: 1px solid #eeeeee;">
			<span style="float: left; width: 100%; font-weight: bold;">TO:</span>
		</div>';

		for ($i=0; $i<count($aTexto["to"]); $i++)
		{
			echo '<div style="border-bottom: 1px solid #eeeeee;">
				<span style="float: left; width: 20%; padding-left:20px; box-sizing: border-box;">'.$aGrupo[$aTexto["to"][$i]["grupo"]].'</span>
				<span style="float: left; width: 30%;"><a style="float: left; width:30px;">'.$aTexto["to"][$i]["aba"].'</a><a class="float: left;">'.$aAba[$aTexto["to"][$i]["aba"]].'</a></span>
				<span style="float: left; width: 40%;">'.$aStatus[$aTexto["to"][$i]["status"]].'</span>';

			if (isset($aTexto["to"][$i]["fixo"]))
				echo '<span style="float: left; width: 10%;">'.$aTexto["to"][$i]["fixo"].'</span>';

			echo '</div>';
		}
	}
}
echo '</body></html>';

?>
