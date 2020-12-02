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
	if (!in_array("cd_mensagem", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_cd = intval($_POST["id-cd"]);
	$pMensagem = utf8_decode(trim($_POST["mensagem"]));
	$pMensagem = strip_tags($pMensagem);
	$pMensagem = nl2br($pMensagem);
	$pMensagem = preg_replace("/\s+/", " ", $pMensagem);
	$pMensagem = $db->escapeString($pMensagem);
	$pAnexo = utf8_decode($_POST["anexo"]);
	
	$now = date("Y-m-d H:i:s");

	$add_to_where = "";
	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$add_to_where = " AND (cdsrp.id_cliente = $cliente_parent OR cdsrp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))";
	}
	//else if ($sInside_tipo == 1) //BO
	//{
	//	$aReturn[0] = 9; //acesso restrito
	//	echo json_encode($aReturn);
	//	exit;
	//}

	$aTipo = array();
	$aTipo[1] = 'Solicitação de Compra Direta';
	$aTipo[2] = 'Adesão à ata de Registro de Preços';

	$db->query("
SELECT 
	cdsrp.id,
	cdsrp.tipo,
	cdsrp.orgao,
	cdsrp.numero_srp,
	cdsrp.data_srp,
	cdsrp.descritivo_veiculo,
	cdsrp.quantidade,
	cdsrp.valor,
	IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome_dn,
	IF (cli.id_parent > 0, clip.id, cli.id) AS id_parent,
	cid.uf,
	(SELECT data_hora FROM gelic_comprasrp_historico WHERE id_comprasrp = cdsrp.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS data_hora
FROM 
	gelic_comprasrp AS cdsrp
	INNER JOIN gelic_clientes AS cli ON cli.id = cdsrp.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
WHERE
	cdsrp.id = $pId_cd AND
	cdsrp.deletado = 0$add_to_where");
	if ($db->nextRecord())
	{
		$dId_parent = $db->f("id_parent");
		$dTipo = $db->f("tipo");
		$dData_hora = $db->f("data_hora");
		$dOrgao = $db->f("orgao");
		$dNumero_srp = $db->f("numero_srp");
		$dData_srp = $db->f("data_srp");
		$dDescritivo_veiculo = $db->f("descritivo_veiculo");
		$dQuantidade = $db->f("quantidade");
		$dValor = $db->f("valor");
		$dNome_cliente = $db->f("nome_dn");
		$dUf = $db->f("uf"); // para filtrar notif. enviadas para o BO

		//processar anexo
		if ($pAnexo <> '---' && file_exists(UPLOAD_DIR."/~upcdmsgc_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($pId_cd, $pAnexo, 'cdchat'.time().$sInside_id));
			uploadFileBucket(UPLOAD_DIR."~upcdmsgc_".$sInside_id.".tmp", "vw/cdchat/".$arquivo_md5);
			unlink(UPLOAD_DIR."/~upcdmsgc_".$sInside_id.".tmp");
			$tEmail_arquivo = '<span style="font-weight: bold;">Arquivo anexo:</span> '.utf8_encode($pAnexo).'<br>';
		}
		else
		{
			$pAnexo = "";
			$arquivo_md5 = "";
			$tEmail_arquivo = '<span style="font-weight: bold;">Arquivo anexo:</span> <span style="font-style: italic;">não informado</span><br>';
		}



		//INSERIR HISTORICO
		if ($sInside_tipo == 1)
			$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $pId_cd, 0, $sInside_id, 4, 0, '$now', '$pMensagem', '$pAnexo', '$arquivo_md5')");
		else
			$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $pId_cd, 0, $sInside_id, 3, 0, '$now', '$pMensagem', '$pAnexo', '$arquivo_md5')");





		//-----------------------------------------
		//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
		//-----------------------------------------
		$tEmail_srp = '';
		if ($dTipo == 2)
			$tEmail_srp = '<span style="font-weight: bold;">Número do SRP:</span> '.utf8_encode($dNumero_srp).'<br>
<span style="font-weight: bold;">Data do SRP:</span> '.mysqlToBr($dData_srp).'<br>';


		$tEmail_assunto = 'GELIC - Nova Mensagem (Compra Direta/SRP)';
		$tEmail_mensagem = 'Nova mensagem referente à seguinte solicitação Compra Direta/SRP.<br><br>
<span style="font-weight: bold;">DN:</span> '.utf8_encode($dNome_cliente).'<br>
<span style="font-weight: bold;">Tipo da Solicitação:</span> '.$aTipo[$dTipo].'<br>
<span style="font-weight: bold;">Data/Hora da Solicitação:</span> '.mysqlToBr(substr($dData_hora,0,10)).' '.substr($dData_hora,11).'<br>
<span style="font-weight: bold;">Nome do Órgão Público:</span> '.utf8_encode($dOrgao).'<br>
<span style="font-weight: bold;">Descritivo do Veículo:</span> '.utf8_encode($dDescritivo_veiculo).'<br>
'.$tEmail_srp.'
<span style="font-weight: bold;">Quantidade:</span> '.$dQuantidade.'<br>
<span style="font-weight: bold;">Valor:</span> R$ '.number_format($dValor, 2, ",", ".").'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
<span style="font-weight: bold;">Mensagem:</span> '.utf8_encode($pMensagem).'<br>
'.$tEmail_arquivo.'<br>
'.rodapeEmail();
		$tTexto_sms = 'GELIC - Nova Mensagem (Compra Direta/SRP) DN: '.utf8_encode($dNome_cliente);


		// Decidir para quem enviar mensagem de aviso
		$aAdmins = array();

		// Buscar ultimo ADMIN que enviou mensagem
		$db->query("SELECT id_sender FROM gelic_comprasrp_historico WHERE id_comprasrp = $pId_cd AND tipo = 2 ORDER BY id DESC LIMIT 1");
		if ($db->nextRecord())
		{
			$aAdmins[] = $db->f("id_sender");
		}
		else
		{
			// Se nao encontrar entao buscar ADMIN que autorizou o envio da APL
			$db->query("SELECT id_sender FROM gelic_comprasrp_historico WHERE id_comprasrp = $pId_cd AND tipo = 5 ORDER BY id DESC LIMIT 1");
			if ($db->nextRecord())
				$aAdmins[] = $db->f("id_sender");
		}

		// Incluir tambem todos os ADMINS com perfil = 1
		$db->query("SELECT id FROM gelic_admin_usuarios WHERE id_perfil = 1");
		while ($db->nextRecord())
			$aAdmins[] = $db->f("id");

		// Remover duplicados
		$aAdmins = array_unique($aAdmins);	
		$incluir = implode(",",$aAdmins);


		// Notificar ADMINs
		if (strlen($incluir) > 0)
		{
			$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE id IN ($incluir) AND notificacoes = 1 AND ativo = 1");
			while ($db->nextRecord())
			{
				if ($sInside_tipo == 1)
				{
					if (in_array("R", str_split($db->f("nt_email"))))
						queueMessage(46, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

					if (strlen($db->f("celular")) > 0 && in_array("R", str_split($db->f("nt_celular"))))
						queueMessage(46, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
				}
				else
				{
					if (in_array("K", str_split($db->f("nt_email"))))
						queueMessage(11, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

					if (strlen($db->f("celular")) > 0 && in_array("K", str_split($db->f("nt_celular"))))
						queueMessage(11, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
				}
			}
		}


		if ($sInside_tipo == 1) //BO
		{
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
		(cli.tipo = 3 AND (cli.id = $dId_parent OR cli.id IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))) OR
		(cli.tipo = 4 AND cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = $dId_parent))
	) AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0");
			while ($db->nextRecord())
			{
				$dNt_email = json_decode($db->f("nt_email"), true);
				$dNt_sms = json_decode($db->f("nt_celular"), true);

				if (in_array("S", str_split($dNt_email["ntf"])))
					queueMessage(48, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("S", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
					queueMessage(48, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
		}
		else
		{
			// Notificar BOs
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
	cli.tipo = 1 AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0");
			while ($db->nextRecord())
			{
				$dNt_email = json_decode($db->f("nt_email"), true);
				$dNt_sms = json_decode($db->f("nt_celular"), true);

				if (in_array("I", str_split($dNt_email["ntf"])) && in_region("I", $dUf, $dNt_email["reg"]))
					queueMessage(25, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_BOF, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("I", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0 && in_region("I", $dUf, $dNt_sms["reg"]))
					queueMessage(25, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_BOF, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
		}
		//-----------------------------------------
		//-----------------------------------------
		//-----------------------------------------



		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
