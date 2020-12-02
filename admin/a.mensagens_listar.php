<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'',0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("msg_visualizar", $xAccess))
	{
		$aReturn[1] = '<div style="color:#ff0000;">Acesso restrito.</div>';
		$aReturn[2] = 0;
		echo json_encode($aReturn);
		exit;
	}


	$pStart = intval($_POST["start"]);
	$pTab = intval($_POST["tab"]);
	$pFiltro_recip = trim($_POST["filtro-recip"]);
	$pFiltro_notif = trim($_POST["filtro-notif"]);
	$pFiltro_metod = trim($_POST["filtro-metod"]);
	$recs_per_load = 50;

	$where = "";
	if (strlen($pFiltro_recip) > 0)
		$where .= " AND msg.id_destino IN ($pFiltro_recip)";

	if (strlen($pFiltro_notif) > 0)
		$where .= " AND msg.id_notificacao IN ($pFiltro_notif)";

	if (strlen($pFiltro_metod) > 0)
		$where .= " AND msg.metodo IN ($pFiltro_metod)";

	if ($pTab == 0)
		$where .= " AND msg.status = 0";
	else if ($pTab == 1)
		$where .= " AND msg.status = 1";
	else if ($pTab == 2)
		$where .= " AND msg.status > 1";


	$db = new Mysql();
	$oRows = '';

	$mtbl = "gelic_mensagens";
	if ($pTab == 3)
		$mtbl .= "_log";

	$db->query("
SELECT
	msg.id,
	msg.metodo,
	msg.origem,
	msg.destino,
	msg.assunto,
	msg.mensagem,
	msg.anexos,
	msg.data_hora_inserido,
	msg.data_hora_status,
	msg.status,
	msg.data_hora_status,
	msg.resultado
FROM
	$mtbl AS msg
WHERE
	msg.id > 0$where
ORDER BY
	msg.id DESC
LIMIT $pStart,$recs_per_load");

	$aMetodo = array();
	$aMetodo[1] = '<span class="m-email">EMAIL</span>';
	$aMetodo[2] = '<span class="m-sms">SMS</span>';

	$pStart += $db->nf();
	while ($db->nextRecord())
	{
		$dAssunto = utf8_encode(clipString(strip_tags(stripslashes($db->f("assunto"))), 26));
		if ($dAssunto == '')
			$dAssunto = '&nbsp;';

		$dAnexos = '<span class="m-anexo">&nbsp;</span>';
		if (strlen($db->f("anexos")) > 0 && $db->f("metodo") == 1)
			$dAnexos = '<span class="m-anexo"><img src="img/attach.png"></span>';

		$dCheck_box = '';
		if ($pTab == 0 || $pTab == 2)
			$dCheck_box = '<a class="mch0" href="javascript:void(0);" onclick="selecionarMensagem(this,'.$db->f("id").');"></a>';

		$dInfo = '';
		if ($pTab == 0) //novas
			$dInfo = '<div style="clear: both; color: #0077ee;">
				<span style="line-height: 21px; float: left; margin-left: 70px;">ORG: '.$db->f("origem").'</span>
				<span style="line-height: 21px; float: right; margin-right: 70px;">INS: '.$db->f("data_hora_inserido").'</span>
			</div>';
		if ($pTab == 1) //processando
			$dInfo = '<div style="clear: both; color: #f0b400;">
				<span style="line-height: 21px; float: left; margin-left: 70px;">ORG: '.$db->f("origem").'</span>
				<span style="line-height: 21px; float: right; margin-right: 70px;">ENV: '.$db->f("data_hora_status").'</span>
				<span style="line-height: 21px; float: right; margin-right: 20px;">INS: '.$db->f("data_hora_inserido").'</span>
			</div>';
		else if ($pTab == 2) //erro
			$dInfo = '<div style="clear: both; color: #ef0000;">
				<span style="line-height: 21px; float: left; margin-left: 70px;">ORG: '.$db->f("origem").'</span>
				<span style="line-height: 21px; float: right; margin-right: 70px;">ENV: '.$db->f("data_hora_status").'</span>
				<span style="line-height: 21px; float: right; margin-right: 20px;">INS: '.$db->f("data_hora_inserido").'</span>
				<span style="line-height: 21px; float: left; margin-left: 70px; clear: both;">STA: '.$db->f("status").'</span>
				<span style="line-height: 21px; float: left; margin-left: 20px;">RES: '.utf8_encode(strip_tags(stripslashes($db->f("resultado")))).'</span>
			</div>';
		else if ($pTab == 3) //sucesso
			$dInfo = '<div style="clear: both; color: #00c400;">
				<span style="line-height: 21px; float: left; margin-left: 70px;">ORG: '.$db->f("origem").'</span>
				<span style="line-height: 21px; float: right; margin-right: 70px;">ENV: '.$db->f("data_hora_status").'</span>
				<span style="line-height: 21px; float: right; margin-right: 20px;">INS: '.$db->f("data_hora_inserido").'</span>
				<span style="line-height: 21px; float: left; margin-left: 70px; clear: both;">RES: '.utf8_encode(strip_tags(stripslashes($db->f("resultado")))).'</span>
			</div>';

		$oRows .= '<div id="msg-'.$db->f("id").'" class="m'.intval($db->Row[0] % 2).'">
			<div style="float: left; width:60px; height: 21px; border-bottom: 1px solid #aaaaaa;">
				'.$aMetodo[$db->f("metodo")].'
			</div>
			<span class="m-destino">'.clipString($db->f("destino"), 34).'</span>
			<span class="m-assunto">'.$dAssunto.'</span>
			<a class="msg-lnk" href="javascript:void(0);" onclick="abrirMensagem('.$db->f("id").');"><span class="m-msg">'.utf8_encode(clipString(strip_tags(stripslashes($db->f("mensagem"))), 64)).'</span></a>
			'.$dAnexos.'<div class="cb-holder" style="float: right; width: 50px; height:21px; border-bottom: 1px solid #aaaaaa;">'.$dCheck_box.'</div>'.$dInfo.'
		</div>';
	}

	if ($oRows == '')
		$pStart = 0;
	
	$aReturn[1] = $oRows;
	$aReturn[2] = $pStart;
} 
echo json_encode($aReturn);

?>
