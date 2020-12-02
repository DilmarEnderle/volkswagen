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

	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();

	$pId_licitacao = intval($_POST["f-id_licitacao"]);
	$pData_abertura = trim($_POST["f-data_abertura"]);  // 'dd/mm/yyyy'
	$pHora_abertura = trim($_POST["f-hora_abertura"]);  // '' or 'hh:mm'
	$pData_entrega = trim($_POST["f-data_entrega"]);    // 'dd/mm/yyyy'
	$pHora_entrega = trim($_POST["f-hora_entrega"]);  // '' or 'hh:mm'
	$pId_motivo = intval($_POST["f-motivo"]);
	$pId_submotivo = intval($_POST["f-submotivo"]);
	$pObservacao = $db->escapeString(nl2br(strip_tags(trim(utf8_decode($_POST["f-observacao"])))));
	$now = date("Y-m-d H:i:s");


	//**** DATA DE ABERTURA ****
	if (isValidBrDate($pData_abertura))
		$pData_abertura = brToMysql($pData_abertura);
	else
		$pData_abertura = "0000-00-00";
	

	//**** HORA DE ABERTURA ****
	if (strlen($pHora_abertura) > 0)
	{
		$pHora_abertura_aux = explode(":", $pHora_abertura);
		if (intval($pHora_abertura_aux[0]) > 23 || intval($pHora_abertura_aux[1]) > 59)
		{
			$aReturn[0] = 2; //hora abertura invalida
			echo json_encode($aReturn);
			exit;
		}
		$pHora_abertura = " ".$pHora_abertura.":00";
	}
	else
		$pHora_abertura .= " 00:00:00";


	//**** DATA DE ENTREGA ****
	if (isValidBrDate($pData_entrega))
		$pData_entrega = brToMysql($pData_entrega);
	else
		$pData_entrega = "0000-00-00";


	//**** HORA DE ENTREGA ****
	if (strlen($pHora_entrega) > 0)
	{
		$pHora_entrega_aux = explode(":", $pHora_entrega);
		if (intval($pHora_entrega_aux[0]) > 23 || intval($pHora_entrega_aux[1]) > 59)
		{
			$aReturn[0] = 8; //hora entrega invalida
			echo json_encode($aReturn);
			exit;
		}
		$pHora_entrega = " ".$pHora_entrega.":00";
	}
	else
		$pHora_entrega .= " 00:00:00";


	$dVerificar_datas = true;

	if ($dVerificar_datas)
	{
		$pData_hoje_int = intval(date("Ymd")); //para calculo de datas

		//------- verificar data de abertura -----------
		$pData_abertura_int = intval(str_replace("-","",$pData_abertura));
		if ($pData_abertura_int < $pData_hoje_int) //verifica se a data de abertura eh menor do que hoje
		{
			$aReturn[0] = 3; //data de abertura invalida
			echo json_encode($aReturn);
			exit;
		}

		//--------- verificar data de entrega ----------
		$pData_entrega_int = intval(str_replace("-","",$pData_entrega));
		if ($pData_entrega_int < $pData_hoje_int || $pData_entrega_int > $pData_abertura_int) //verifica se a data de entrega eh menor do que hoje ou maior do que a data de abertura
		{
			$aReturn[0] = 4; //data de entrega inválida
			echo json_encode($aReturn);
			exit;
		}
	}

	$pDatahora_abertura = $pData_abertura.$pHora_abertura;
	$pDatahora_entrega = $pData_entrega.$pHora_entrega;


	//--- dados corretos - aplicar alteracoes ---
	$db->query("
SELECT
	lic.datahora_abertura,
	lic.datahora_entrega,
	lic.orgao,
	lic.objeto,
	lic.importante,
	lic.valor,
	mdl.nome AS nome_modalidade,
	cid.nome AS nome_cidade,
	cid.uf AS uf,
	uf.regiao_abv
FROM
	gelic_licitacoes AS lic,
	gelic_modalidades AS mdl,
	gelic_cidades AS cid,
	gelic_uf AS uf
WHERE
	lic.id = $pId_licitacao AND
	lic.deletado = 0 AND
	lic.id_modalidade = mdl.id AND
	lic.id_cidade = cid.id AND
	cid.uf = uf.uf");
	if ($db->nextRecord())
	{
		$dDatahora_abertura = $db->f("datahora_abertura");
		$dDatahora_entrega = $db->f("datahora_entrega");

		$dOrgao = $db->f("orgao");
		$dObjeto = $db->f("objeto");
		$dImportante = $db->f("importante");
		$dValor = $db->f("valor");
		$dNome_modalidade = $db->f("nome_modalidade");
		$dNome_cidade = $db->f("nome_cidade");
		$dUf = $db->f("uf");
		$dRegiao_abv = $db->f("regiao_abv");

		if ($dValor == '0.00')
			$dValor = '<span style="font-style: italic;">não informado</span>';
		else
			$dValor = 'R$ '.number_format($dValor, 2, ",", ".");





		//verificar se a data/hora de abertura sao diferentes
		if (substr($dDatahora_abertura, 0, 16) == substr($pDatahora_abertura, 0, 16))
		{
			$aReturn[0] = 6; //não houve alteração na data ou hora
			echo json_encode($aReturn);
			exit;
		}


		$dDab_h = substr($dDatahora_abertura,11,5);
		$dDen_h = substr($dDatahora_entrega,11,5);
		$pDab_h = substr($pDatahora_abertura,11,5);
		$pDen_h = substr($pDatahora_entrega,11,5);

		if ($dDab_h == "00:00") $dDab_h = "--:--";
		if ($dDen_h == "00:00") $dDen_h = "--:--";
		if ($pDab_h == "00:00") $pDab_h = "--:--";
		if ($pDen_h == "00:00") $pDen_h = "--:--";


		//passou todos os testes - gerar campo texto
		$texto = '<span class="bold italic">De:</span> Data/Hora Abertura: <a class="gray-28">'.mysqlToBr(substr($dDatahora_abertura,0,10)).' '.$dDab_h.'</a>&nbsp;&nbsp;&nbsp;&nbsp;Data/Hora Entrega: <a class="gray-28">'.mysqlToBr(substr($dDatahora_entrega,0,10)).' '.$dDab_h.'</a><br>
		<span class="bold italic">Para:</span> Data/Hora Abertura: <a class="gray-28">'.mysqlToBr(substr($pDatahora_abertura,0,10)).' '.$pDab_h.'</a>&nbsp;&nbsp;&nbsp;&nbsp;Data/Hora Entrega: <a class="gray-28">'.mysqlToBr(substr($pDatahora_entrega,0,10)).' '.$pDen_h.'</a>';
		if (strlen($pObservacao) > 0) $texto .= utf8_decode('<br>Observação: ').$pObservacao;

		//aplicar alteracoes
		$db->query("UPDATE gelic_licitacoes SET datahora_abertura = '$pDatahora_abertura', datahora_entrega = '$pDatahora_entrega' WHERE id = $pId_licitacao");
		if ($db->afrows() > 0)
		{
			//inserir no historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 34, $pId_motivo, $pId_submotivo, '$now', '$texto', '', '')");

			$db->query("SELECT descricao FROM gelic_motivos WHERE id = $pId_motivo");
			$db->nextRecord();
			$dMotivo = utf8_encode($db->f("descricao"));

			$db->query("SELECT descricao FROM gelic_motivos WHERE id = $pId_submotivo");
			if ($db->nextRecord())
				$dMotivo .= ' <span style="font-style:italic;">('.utf8_encode($db->f("descricao")).')</span>';


	
			if (strlen($pObservacao) == 0)
				$pObservacao = utf8_decode('<span style="font-style: italic;">não informado</span>');


			//-----------------------------------------
			//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
			//-----------------------------------------
			$tEmail_assunto = 'GELIC - Licitação Prorrogada LIC '.$pId_licitacao;
			$tEmail_assunto_admin = $dRegiao_abv.'('.$dUf.') - Licitação Prorrogada - LIC '.$pId_licitacao;

			$tEmail_mensagem = 'A seguinte licitação foi prorrogada.<br><br>
<span style="font-weight: bold;">Número de Identificação:</span> '.$pId_licitacao.'<br>
<span style="font-weight: bold;">Modalidade:</span> '.utf8_encode($dNome_modalidade).'<br>
<span style="font-weight: bold;">Órgão Público:</span> '.utf8_encode($dOrgao).'<br>
<span style="font-weight: bold;">Localização:</span> '.utf8_encode($dNome_cidade).' - '.$dUf.'<br>
<span style="font-weight: bold;">Valor Estimado:</span> '.$dValor.'<br>
<span style="font-weight: bold;">Objeto:</span> '.clipString(strip_tags(utf8_encode($dObjeto)),800).'<br>
<span style="font-weight: bold;">Importante:</span> '.clipString(strip_tags(utf8_encode($dImportante)),800).'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
<span style="font-weight: bold;">DE:</span><br>
<span style="font-weight: bold;">Data/Hora de Abertura:</span> '.mysqlToBr(substr($dDatahora_abertura,0,10)).' '.$dDab_h.'<br>
<span style="font-weight: bold;">Data/Hora de Entrega:</span> '.mysqlToBr(substr($dDatahora_entrega,0,10)).' '.$dDen_h.'<br><br>
<span style="font-weight: bold;">PARA:</span><br>
<span style="font-weight: bold;">Data/Hora de Abertura:</span> '.mysqlToBr(substr($pDatahora_abertura,0,10)).' '.$pDab_h.'<br>
<span style="font-weight: bold;">Data/Hora de Entrega:</span> '.mysqlToBr(substr($pDatahora_entrega,0,10)).' '.$pDen_h.'<br><br>
<span style="font-weight: bold;">Motivo:</span> '.$dMotivo.'<br>
<span style="font-weight: bold;">Observação:</span> '.utf8_encode($pObservacao).'<br><br>
'.rodapeEmail();
			$tTexto_sms = "GELIC - Licitacao $pId_licitacao foi prorrogada.";

			// Notificar DNs
			$db->query("
SELECT 
	cli.id,
	cli.email, 
	cli.celular, 
	cli.nt_email, 
	cli.nt_celular 
FROM 
	gelic_clientes AS cli
WHERE
	(
		(cli.tipo = 2 AND cli.id IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND tipo IN (2,41))) OR
		(cli.tipo = 3 AND cli.id IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND tipo IN (2,41))) OR
		(cli.tipo = 4 AND cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $pId_licitacao) AND id_cliente_acesso IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND tipo IN (2,41))))
	) AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0");
			while ($db->nextRecord())
			{
				$dNt_email = json_decode($db->f("nt_email"), true);
				$dNt_sms = json_decode($db->f("nt_celular"), true);

				if (in_array("D", str_split($dNt_email["ntf"])))
					queueMessage(30, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');
	
				if (in_array("D", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
					queueMessage(30, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}


			// Notificar ADMINs
			$db->query("
SELECT 
	adm.id,
	adm.email, 
	adm.celular, 
	adm.nt_email, 
	adm.nt_celular 
FROM 
	gelic_admin_usuarios AS adm
WHERE
	adm.notificacoes = 1 AND 
	adm.ativo = 1");
			while ($db->nextRecord())
			{
				if (in_array("I", str_split($db->f("nt_email"))))
					queueMessage(9, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto_admin, $tEmail_mensagem, '', '');

				if (in_array("I", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
					queueMessage(9, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
			//-----------------------------------------
			//-----------------------------------------
			//-----------------------------------------
		}
		$aReturn[0] = 1;
	}
} 
echo json_encode($aReturn);

?>
