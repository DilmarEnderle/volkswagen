<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];


	//--- Ajustar inside_id se for Representante ---
	if ($sInside_tipo == 4) //REP
		$sInside_id = $_SESSION[SESSION_ID_DN];
	//----------------------------------------------


	$xAccess = explode(" ",getAccess());

	if ($sInside_tipo == 1 || !in_array("cd_solicitar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_compradir = intval($_POST["f-id"]);
	$pTipo = intval($_POST["f-tipo"]);
	$pOrgao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-orgao"])))));
	$pDescritivo_veiculo = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-descritivo"])))));
	if ($pTipo == 2)
	{
		$pNumero_srp = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-numero-srp"])))));
		$pData_srp = trim($_POST["f-data-srp"]);  // 'dd/mm/yyyy'
		$a = explode("/", $pData_srp);
		$pData_srp = $a[2]."-".$a[1]."-".$a[0];
	}
	else
	{
		$pNumero_srp = "";
		$pData_srp = "0000-00-00";
	}
	$pQuantidade = intval($_POST["f-quantidade"]);
	$pValor = trim($_POST["f-valor"]);
	$pValor = str_replace(array(".","R","$"," "),"",$pValor);
	$pValor = str_replace(",",".",$pValor);
	if (strlen($pValor) == 0) $pValor = '0.00';
	$pId_atarp = intval($_POST["f-id_atarp"]);
	$pAnexo = utf8_decode(trim($_POST["f-anexo"]));
	$now = date("Y-m-d H:i:s");


	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];

	$db->query("SELECT id FROM gelic_atarp WHERE id = $pId_atarp");
	if (!$db->nextRecord())
		$pId_atarp = 0; //not found

	
	if ($pId_compradir == 0)
	{
		//---------- NOVA ----------
		if (file_exists(UPLOAD_DIR."~upcompc_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($pId_compradir, $pAnexo, 'comp'.time().$sInside_id));
			uploadFileBucket(UPLOAD_DIR."~upcompc_".$sInside_id.".tmp", "vw/comp/".$arquivo_md5);
			unlink(UPLOAD_DIR."/~upcompc_".$sInside_id.".tmp");

			//inserir registro
			$db->query("INSERT INTO gelic_comprasrp VALUES (NULL, $sInside_id, $pId_atarp, $pTipo, '$pOrgao', '$pNumero_srp', '$pData_srp', '$pDescritivo_veiculo', $pQuantidade, $pValor, 0)");
			$dId_compradir = $db->li();

			//inserir no historico
			$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $dId_compradir, 0, $sInside_id, 1, 0, '$now', '', '$pAnexo', '$arquivo_md5')");

	
			//-----------------------------------------
			//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
			//-----------------------------------------
			$db->query("
SELECT
	IF (cdsrp.tipo = 1, 'Solicitação de Compra Direta', 'Adesão à ata de Registro de Preços') AS tipo_desc,
	cdsrp.tipo,
    cdsrp.orgao,
    cdsrp.descritivo_veiculo,
    cdsrp.numero_srp,
	cdsrp.data_srp,
	cdsrp.quantidade,
	cdsrp.valor,
    IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome_dn,
	IF (cli.id_parent > 0, clip.dn, cli.dn) AS dn,
	cid.uf,
	(SELECT data_hora FROM gelic_comprasrp_historico WHERE id_comprasrp = cdsrp.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS data_hora
FROM
	gelic_comprasrp AS cdsrp
    INNER JOIN gelic_clientes AS cli ON cli.id = cdsrp.id_cliente
    LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
WHERE
	cdsrp.id = $dId_compradir");
			$db->nextRecord();
			$dUf = $db->f("uf"); //para filtrar notif. enviadas para o BO

			$tEmail_srp = '';
			if ($db->f("tipo") == 2)
				$tEmail_srp = '<span style="font-weight: bold;">Número do SRP:</span> '.utf8_encode($db->f("numero_srp")).'<br>
<span style="font-weight: bold;">Data do SRP:</span> '.mysqlToBr($db->f("data_srp")).'<br>';

			$tEmail_assunto = 'GELIC - Nova solicitação (COMPRA DIRETA/SRP)';
			$tEmail_mensagem = 'Nova solicitação de Compra Direta/SRP conforme os seguintes dados.<br><br>
<span style="font-weight: bold;">DN:</span> '.utf8_encode($db->f("nome_dn")).'<br><br>
<span style="font-weight: bold;">Tipo da Solicitação:</span> '.$db->f("tipo_desc").'<br>
<span style="font-weight: bold;">Data/Hora da Solicitação:</span> '.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.substr($db->f("data_hora"),11,5).'<br>
<span style="font-weight: bold;">Nome do Órgão Público:</span> '.utf8_encode($db->f("orgao")).'<br>
<span style="font-weight: bold;">Descritivo do Veículo:</span> '.utf8_encode($db->f("descritivo_veiculo")).'<br>
'.$tEmail_srp.'
<span style="font-weight: bold;">Quantidade:</span> '.$db->f("quantidade").'<br>
<span style="font-weight: bold;">Valor:</span> R$ '.number_format($db->f("valor"),2,",",".").'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
			$tTexto_sms = 'GELIC - Nova solicitacao (COMPRA DIRETA/SRP) DN: '.utf8_encode($db->f("nome_dn"));


			// Notificar ADMINs
			$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
			while ($db->nextRecord())
			{
				if (in_array("J", str_split($db->f("nt_email"))))
					queueMessage(10, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("J", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
					queueMessage(10, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}

			// Notificar BOs
			$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_clientes WHERE tipo = 1 AND notificacoes = 1 AND ativo = 1 AND deletado = 0");
			while ($db->nextRecord())
			{
				$dNt_email = json_decode($db->f("nt_email"), true);
				$dNt_sms = json_decode($db->f("nt_celular"), true);

				if (in_array("G", str_split($dNt_email["ntf"])) && in_region("G", $dUf, $dNt_email["reg"]))
					queueMessage(23, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_BOF, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("G", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0 && in_region("G", $dUf, $dNt_sms["reg"]))
					queueMessage(23, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_BOF, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
			//-----------------------------------------
			//-----------------------------------------
			//-----------------------------------------


			$aReturn[0] = 1; //sucesso
		}
	}
	else
	{
		//---------- SALVAR ----------
		
		//atualizar registro
		$db->query("UPDATE gelic_comprasrp SET tipo = $pTipo, orgao = '$pOrgao', numero_srp = '$pNumero_srp', data_srp = '$pData_srp', descritivo_veiculo = '$pDescritivo_veiculo', quantidade = $pQuantidade, valor = $pValor WHERE id = $pId_compradir");

		if ($pAnexo != "KEEP" && file_exists(UPLOAD_DIR."~upcompc_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($pId_compradir, $pAnexo, 'comp'.time().$sInside_id));
			uploadFileBucket(UPLOAD_DIR."~upcompc_".$sInside_id.".tmp", "vw/comp/".$arquivo_md5);
			unlink(UPLOAD_DIR."/~upcompc_".$sInside_id.".tmp");
		}
		else
		{
			$pAnexo = "";
			$arquivo_md5 = "";
		}

		//inserir no historico
		$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $pId_compradir, 0, $sInside_id, 1, 0, '$now', '', '$pAnexo', '$arquivo_md5')");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
