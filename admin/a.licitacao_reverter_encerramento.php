<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_reverter_encerramento", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pId_licitacao = intval($_POST["f-id-licitacao"]);

	$db = new Mysql();

	//verificar se a licitacao esta encerrada
	$db->query("
SELECT
	lic.id,
	lic.orgao,
	lic.objeto,
	lic.importante,
	lic.valor,
	lic.datahora_abertura,
	lic.datahora_entrega,
	lic.datahora_limite,
	mdl.nome AS nome_modalidade,
	cid.nome AS nome_cidade,
	cid.uf AS uf,
	(SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo
FROM
	gelic_licitacoes AS lic,
	gelic_modalidades AS mdl,
	gelic_cidades AS cid
WHERE
	lic.id = $pId_licitacao AND
	lic.deletado = 0 AND
	lic.id_modalidade = mdl.id AND
	lic.id_cidade = cid.id
HAVING
	ultimo_tipo = 31");
	if ($db->nextRecord())
	{
		$dId_licitacao = $db->f("id");
		$dOrgao = $db->f("orgao");
		$dObjeto = $db->f("objeto");
		$dImportante = $db->f("importante");
		$dValor = $db->f("valor");
		$dDatahora_abertura = $db->f("datahora_abertura");
		$dDatahora_entrega = $db->f("datahora_entrega");
		$dDatahora_limite = $db->f("datahora_limite");
		$dNome_modalidade = $db->f("nome_modalidade");
		$dNome_cidade = $db->f("nome_cidade");
		$dUf = $db->f("uf");


		$now = date("Y-m-d H:i:s");

		//remover ATA se existente
		$db->query("SELECT arquivo FROM gelic_licitacoes_ata WHERE id_licitacao = $dId_licitacao");
		if ($db->nextRecord())
		{
			removeFileBucket("vw/licata/".$db->f("arquivo"));
			$db->query("DELETE FROM gelic_licitacoes_ata WHERE id_licitacao = $dId_licitacao");
		}

		//STATUS E ABAS ANTES DA ALTERACAO
		$aLic_aba_status = array("fr"=>array(),"to"=>array());
		$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo");
		while ($db->nextRecord())
			$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));


		//voltar para aba e status antes do encerramento (pegar do back office que era o mesmo antes do encerramento)
		$db->query("SELECT id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao AND grupo = 2");
		$db->nextRecord();
		$dId_aba = $db->f("id_aba");
		$dId_status = $db->f("id_status");
		$dStatus_fixo = $db->f("status_fixo");
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $dId_aba, id_status = $dId_status, status_fixo = $dStatus_fixo WHERE id_licitacao = $dId_licitacao AND grupo = 1");


		//STATUS E ABAS DEPOIS DA ALTERACAO
		$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo");
		while ($db->nextRecord())
			$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));

		//inserir no historico
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, $sInside_id, 0, 32, 0, 0, '$now', '".json_encode($aLic_aba_status)."', '', '')");
		$db->query("UPDATE gelic_historico SET tipo = 39 WHERE id_licitacao = $dId_licitacao AND tipo = 31");


		if ($dValor == '0.00')
			$dValor = '<span style="font-style: italic;">não informado</span>';
		else
			$dValor = 'R$ '.number_format($dValor, 2, ",", ".");

		$dab_h = substr($dDatahora_abertura,11,5);
		$den_h = substr($dDatahora_entrega,11,5);
		$pzl_h = substr($dDatahora_limite,11,5);
		if ($dab_h == "00:00") $dab_h = "--:--";
		if ($den_h == "00:00") $den_h = "--:--";
		if ($pzl_h == "00:00") $pzl_h = "--:--";

		//-----------------------------------------
		//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
		//-----------------------------------------
		$tEmail_assunto = 'GELIC - Licitação Encerramento revertido LIC '.$dId_licitacao;
		$tEmail_mensagem = 'O encerramento da seguinte licitação foi revertido pelo administrador.<br><br>
<span style="font-weight: bold;">Número de Identificação:</span> '.$dId_licitacao.'<br>
<span style="font-weight: bold;">Data/Hora de Abertura:</span> '.mysqlToBr(substr($dDatahora_abertura,0,10)).' '.$dab_h.'<br>
<span style="font-weight: bold;">Data/Hora de Entrega:</span> '.mysqlToBr(substr($dDatahora_entrega,0,10)).' '.$den_h.'<br>
<span style="font-weight: bold;">Prazo Limite:</span> '.mysqlToBr(substr($dDatahora_limite,0,10)).' '.$pzl_h.'<br>
<span style="font-weight: bold;">Modalidade:</span> '.utf8_encode($dNome_modalidade).'<br>
<span style="font-weight: bold;">Órgão Público:</span> '.utf8_encode($dOrgao).'<br>
<span style="font-weight: bold;">Localização:</span> '.utf8_encode($dNome_cidade).' - '.$dUf.'<br>
<span style="font-weight: bold;">Valor Estimado:</span> '.$dValor.'<br>
<span style="font-weight: bold;">Objeto:</span> '.clipString(strip_tags(utf8_encode($dObjeto)),800).'<br>
<span style="font-weight: bold;">Importante:</span> '.clipString(strip_tags(utf8_encode($dImportante)),800).'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
		$tTexto_sms = "GELIC - Licitacao $dId_licitacao encerramento revertido. (".utf8_encode($dNome_cidade)." - $dUf)";


		// Notificar BOs e DNs
		$db->query("
SELECT 
	cli.id,
	cli.tipo,
	cli.email, 
	cli.celular, 
	cli.nt_email, 
	cli.nt_celular 
FROM 
	gelic_clientes AS cli
WHERE
	(
		(cli.tipo = 1) OR
		(cli.tipo = 2 AND cli.id IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo IN (2,41))) OR
		(cli.tipo = 3 AND cli.id IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo IN (2,41))) OR
		(cli.tipo = 4 AND cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $dId_licitacao) AND id_cliente_acesso IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo IN (2,41))))
	) AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0");
		while ($db->nextRecord())
		{
			$dNt_email = json_decode($db->f("nt_email"), true);
			$dNt_sms = json_decode($db->f("nt_celular"), true);

			if ($db->f("tipo") == 1) //BO
			{
				if (in_array("D", str_split($dNt_email["ntf"])) && in_region("D", $dUf, $dNt_email["reg"]))
					queueMessage(20, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_BOF, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("D", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0 && in_region("D", $dUf, $dNt_sms["reg"]))
					queueMessage(20, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_BOF, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
			else
			{
				if (in_array("F", str_split($dNt_email["ntf"])))
					queueMessage(32, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("F", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
					queueMessage(32, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
		}
		//-----------------------------------------
		//-----------------------------------------
		//-----------------------------------------


		$aReturn[0] = 1; //success
	}
} 
echo json_encode($aReturn);

?>
