<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_visualizar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		$aReturn[1] = 'Acesso restrito';
		$aReturn[2] = 0;
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["id-licitacao"]);

	$oOutput = '';

	$db = new Mysql();
	$dbGL = new Mysql("gelic_gelic");

	$db->query("SELECT cod_pregao, cod_uasg FROM gelic_licitacoes WHERE id = $pId_licitacao");
	$db->nextRecord();

	$hoje = date("Y-m-d");
	$ontem = date("Y-m-d", time()-86400);

	$dbGL->query("
SELECT
	msg.id,
	msg.quem_envia,
	msg.data_hora_mensagem,
	msg.mensagem
FROM
	pc_mensagens AS msg
	INNER JOIN pc_monitorar AS mon ON mon.id = msg.id_pc_monitorar AND mon.cod_pregao = ".$db->f("cod_pregao")." AND mon.cod_uasg = ".$db->f("cod_uasg")."
ORDER BY
	msg.data_hora_mensagem DESC");
	while ($dbGL->nextRecord())
	{
		if ($hoje == substr($dbGL->f("data_hora_mensagem"), 0, 10))
			$t = 't-hoje';
		else if ($ontem == substr($dbGL->f("data_hora_mensagem"), 0, 10))
			$t = 't-ontem';
		else
			$t = 't-antigo';

		$tEnvios = '';

		// Verificar ultimo email enviado desta mensagem do PC
		$db->query("SELECT data_hora_status FROM gelic_mensagens_log WHERE id_pc_mensagem = ".$dbGL->f("id")." AND metodo = ".M_EMAIL." ORDER BY id DESC LIMIT 1");
		if ($db->nextRecord())
			$tEnvios .= '<img src="img/mon-icon2.png"><span>'.mysqlToBr(substr($db->f("data_hora_status"), 0, 10)).'</span><span>'.substr($db->f("data_hora_status"), 11).'</span>';

		// Verificar ultimo sms enviado desta mensagem do PC
		$db->query("SELECT data_hora_status FROM gelic_mensagens_log WHERE id_pc_mensagem = ".$dbGL->f("id")." AND metodo = ".M_SMS." ORDER BY id DESC LIMIT 1");
		if ($db->nextRecord())
			$tEnvios .= '<img src="img/mon-icon3.png"><span>'.mysqlToBr(substr($db->f("data_hora_status"), 0, 10)).'</span><span>'.substr($db->f("data_hora_status"), 11).'</span>';

		if ($tEnvios != '')
			$tEnvios = '<a class="pc-envio" href="javascript:void(0);" onclick="pregoeiroChamaDetalhesEnvio('.$dbGL->f("id").');"><span>Envio(s):</span>'.$tEnvios.'</a>';
		else
			$tEnvios = '<a class="pc-envio-e"><span>Envio(s):</span><span>Nenhum</span></a>';

		$oOutput .= '<div class="pc-datahora">
			<img src="img/warn-blue.png">
			<span>'.mysqlToBr(substr($dbGL->f("data_hora_mensagem"), 0, 10)).'</span>
			<span>'.substr($dbGL->f("data_hora_mensagem"), 11).'</span>
			<span class="'.$t.'">'.timeAgo($dbGL->f("data_hora_mensagem")).'</span>
		</div>
		<div class="pc-msg"><span>'.utf8_encode($dbGL->f("quem_envia")).':</span> '.utf8_encode($dbGL->f("mensagem")).'</div>
		<div class="pc-envios">'.$tEnvios.'</div>';
	}

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oOutput;
	$aReturn[2] = $dbGL->nf();
} 
echo json_encode($aReturn);

?>
