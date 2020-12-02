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
	if (!in_array("lic_mensagem", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pMensagem = utf8_decode(trim($_POST["f-mensagem"]));
	$pMensagem = strip_tags($pMensagem);
	$pMensagem = nl2br($pMensagem);
	$pMensagem = preg_replace("/\s+/", " ", $pMensagem);
	$pMensagem = $db->escapeString($pMensagem);
	$pAnexo = utf8_decode(trim($_POST["f-anexo"]));
	
	$now = date("Y-m-d H:i:s");

	$db->query("SELECT id FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND tipo IN (22,31)");
	if ($db->nextRecord())
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}


	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$add_to_from = " INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent";
	}
	else if ($sInside_tipo == 1) //BO
		$add_to_from = "";

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
	uf.regiao_abv
FROM 
	gelic_licitacoes AS lic$add_to_from
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	lic.id = $pId_licitacao AND
	lic.deletado = 0
GROUP BY
	lic.id");
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
		$dRegiao_abv = $db->f("regiao_abv");

		//buscar nome usuario/dn/bo
		$db->query("
SELECT
	cli.nome,
	clip.nome AS nome_dn,
	IF (cli.id_parent > 0, clip.dn, cli.dn) AS dn 
FROM
	gelic_clientes AS cli
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	cli.id = $sInside_id");
		$db->nextRecord();
		$dNome_cliente = $db->f("nome");
		$dDn = $db->f("dn");

		if ($sInside_tipo == 3) //DN FILHO
			$dNome_cliente .= ' (DN: '.$db->f("nome_dn").')';

		if ($sInside_tipo == 1) //BO
			$dNome_cliente .= ' (BO)';

		//processar anexo
		if ($pAnexo <> '' && file_exists(UPLOAD_DIR."~upmsgc_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($dId_licitacao, $pAnexo, 'msgc'.time().$sInside_id));

			//adicionar arquivo no S3 em vw/licchat/...
			uploadFileBucket(UPLOAD_DIR."~upmsgc_".$sInside_id.".tmp", "vw/licchat/".$arquivo_md5);

			//remover arquivo temporario
			@unlink(UPLOAD_DIR."~upmsgc_".$sInside_id.".tmp");


		 	$tEmail_arquivo = '<span style="font-weight: bold;">Arquivo anexo:</span> '.utf8_encode($pAnexo).'<br>';
		}
		else
		{
			$pAnexo = '';
			$arquivo_md5 = '';
			$tEmail_arquivo = '<span style="font-weight: bold;">Arquivo anexo:</span> <span style="font-style: italic;">não informado</span><br>';
		}

		//*********************************
		//***   MENSAGEM (2)   ***
		//*********************************

		//INSERIR HISTORICO
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, $sInside_id, 0, 2, 0, 0, '$now', '$pMensagem', '$pAnexo', '$arquivo_md5')");
	

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
		$tEmail_assunto = 'GELIC - Nova Mensagem LIC '.$dId_licitacao;

		if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
			$tEmail_assunto_admin = $dRegiao_abv.'('.$dUf.') - DN '.$dDn.' - Nova Mensagem - LIC '.$dId_licitacao;
		else
			$tEmail_assunto_admin = $dRegiao_abv.'('.$dUf.') - Nova Mensagem - LIC '.$dId_licitacao;

		$tEmail_mensagem = 'Nova mensagem referente à seguinte licitação.<br><br>
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
<span style="font-weight: bold;">Usuário/DN:</span> '.utf8_encode($dNome_cliente).'<br>
<span style="font-weight: bold;">Mensagem:</span> '.utf8_encode($pMensagem).'<br>
'.$tEmail_arquivo.'<br>
'.rodapeEmail();
		if ($sInside_tipo == 1) //BO
			$tTexto_sms = 'GELIC - Nova mensagem referente a licitacao '.$dId_licitacao;
		else
			$tTexto_sms = 'GELIC - Nova mensagem referente a licitacao '.$dId_licitacao.' DN: '.$dDn;


		// Decidir para quem enviar mensagem de aviso
		$aAdmins = array();

		// Buscar ultimo ADMIN que enviou mensagem
		$db->query("SELECT id_sender FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo = 1 ORDER BY id DESC LIMIT 1");
		if ($db->nextRecord())
		{
			$aAdmins[] = $db->f("id_sender");
		}
		else
		{
			// Se nao encontrar entao buscar ADMIN que adicionou a licitacao
			$db->query("SELECT id_sender FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo = 11");
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

		if (strlen($incluir) > 0)
		{
			$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE id IN ($incluir) AND notificacoes = 1 AND ativo = 1");
			while ($db->nextRecord())
			{
				if ($sInside_tipo == 1) //BO
				{
					if (in_array("B", str_split($db->f("nt_email"))))
						queueMessage(2, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto_admin, $tEmail_mensagem, '', '');

					if (in_array("B", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
						queueMessage(2, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
				}
				else
				{
					if (in_array("A", str_split($db->f("nt_email"))))
						queueMessage(1, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto_admin, $tEmail_mensagem, '', '');

					if (in_array("A", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
						queueMessage(1, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
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

				if (in_array("C", str_split($dNt_email["ntf"])))
					queueMessage(29, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("C", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
					queueMessage(29, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
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

				if (in_array("B", str_split($dNt_email["ntf"])) && in_region("B", $dUf, $dNt_email["reg"]))
					queueMessage(18, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_BOF, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("B", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0 && in_region("B", $dUf, $dNt_sms["reg"]))
					queueMessage(18, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_BOF, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
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
