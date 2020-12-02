<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_reverter_desinteresse", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pId_cliente = intval($_POST["f-id-cliente"]);

	$db = new Mysql();

	$db->query("SELECT id FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND id_sender = $pId_cliente AND tipo = 22");
	if ($db->nextRecord())
	{
		$dId_historico = $db->f("id");
		$now = date("Y-m-d H:i:s");

		//reverter declinio
		$db->query("UPDATE gelic_historico SET tipo = 23 WHERE id = $dId_historico");

		//inserir no historico
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 33, $pId_cliente, 0, '$now', '', '', '')");

		//pegar info para email
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
	lic.fase,
	mdl.nome AS nome_modalidade,
	cid.nome AS nome_cidade,
	cid.uf AS uf
FROM 
	gelic_licitacoes AS lic,
	gelic_modalidades AS mdl,
	gelic_cidades AS cid
WHERE
	lic.id = $pId_licitacao AND
	lic.deletado = 0 AND
	lic.id_modalidade = mdl.id AND
	lic.id_cidade = cid.id");
		$db->nextRecord();

		$dId_licitacao = $db->f("id");
		$dOrgao = $db->f("orgao");
		$dObjeto = $db->f("objeto");
		$dImportante = $db->f("importante");
		$dValor = $db->f("valor");
		$dDatahora_abertura = $db->f("datahora_abertura");
		$dDatahora_entrega = $db->f("datahora_entrega");
		$dDatahora_limite = $db->f("datahora_limite");
		$dFase = $db->f("fase");
		$dNome_modalidade = $db->f("nome_modalidade");
		$dNome_cidade = $db->f("nome_cidade");
		$dUf = $db->f("uf");

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
		$tEmail_assunto = 'GELIC - Declínio de participação revertido LIC '.$dId_licitacao;
		$tEmail_mensagem = 'O declínio de participação foi revertido referente à seguinte licitação.<br><br>
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
		$tTexto_sms = 'GELIC - Licitacao '.$dId_licitacao.' declinio de participacao revertido.';

		$dId_parent = $pId_cliente;
		$db->query("SELECT id_parent FROM gelic_clientes WHERE id = $pId_cliente AND tipo <> 2");
		if ($db->nextRecord())
			$dId_parent = $db->f("id_parent");
		
		
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
		(cli.tipo = 2 AND cli.id = $dId_parent) OR
		(cli.tipo = 3 AND cli.id_parent = $dId_parent) OR
		(cli.tipo = 4 AND cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = $dId_parent))
	) AND
	cli.notificacoes = 1 AND
	cli.ativo = 1 AND
	cli.deletado = 0");
		while ($db->nextRecord())
		{
			$dNt_email = json_decode($db->f("nt_email"), true);
			$dNt_sms = json_decode($db->f("nt_celular"), true);

			if (in_array("G", str_split($dNt_email["ntf"])))
				queueMessage(33, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("G", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
				queueMessage(33, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
		}
		//-----------------------------------------
		//-----------------------------------------
		//-----------------------------------------



		$aReturn[0] = 1; //sucesso
	}
} 
echo json_encode($aReturn);

?>
