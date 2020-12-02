<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	$db = new Mysql();
	$pId_cd = intval($_POST["id-cd"]);

	$pAve = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-apr-ave"])))));
	$pQuantidade = intval($_POST["f-apr-quantidade"]);
	$pModel_code = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-apr-model"])))));
	$pCor = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-apr-cor"])))));
	$pOpcionais_pr = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-apr-opcionais"])))));
	$pPreco_publico = trim($_POST["f-apr-preco-publico"]);
	$pDesconto_vw = trim($_POST["f-apr-desconto-vw"]);
	$pComissao_dn = trim($_POST["f-apr-comissao-dn"]);
	$pPlanta = intval($_POST["f-apr-planta"]);
	$pPrazo_de_entrega = intval($_POST["f-apr-prazo-entrega"]);
	$pTransformacao = intval($_POST["f-apr-transformacao"]);
	if ($pTransformacao == 0)
		$pValor_da_transformacao = "";
	else
		$pValor_da_transformacao = trim($_POST["f-apr-valor-transf"]);
	$pAnexo = utf8_decode(trim($_POST["anexo"]));
	$pCondicoes = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-apr-condicoes"])))));

	$pPreco_publico = str_replace(array(".","R","$"," "), "", $pPreco_publico);
	$pPreco_publico = str_replace(",", ".", $pPreco_publico);
	if (strlen($pPreco_publico) == 0) $pPreco_publico = '0.00';

	$pDesconto_vw = str_replace(array("%"," "), "", $pDesconto_vw);
	$pDesconto_vw = str_replace(",", ".", $pDesconto_vw);
	if (strlen($pDesconto_vw) == 0) $pDesconto_vw = '0.00';

	$pComissao_dn = str_replace(array("%"," "), "", $pComissao_dn);
	$pComissao_dn = str_replace(",", ".", $pComissao_dn);
	if (strlen($pComissao_dn) == 0) $pComissao_dn = '0.00';

	$pValor_da_transformacao = str_replace(array(".","R","$"," "), "", $pValor_da_transformacao);
	$pValor_da_transformacao = str_replace(",", ".", $pValor_da_transformacao);
	if (strlen($pValor_da_transformacao) == 0) $pValor_da_transformacao = '0.00';

	$now = date("Y-m-d H:i:s");

	$xAccess = explode(" ",getAccess());

	//deixar somente o back office daqui pra frente
	if ($sInside_tipo <> 1 || !in_array("cd_apl_aprovar", $xAccess))
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}


	//verificar se a APL pode ser aprovada
	$db->query("
SELECT 
	apl.id,
	(SELECT tipo FROM gelic_comprasrp_apl_historico WHERE id_apl = apl.id AND tipo < 5 ORDER BY id DESC LIMIT 1) AS tipo
FROM 
	gelic_comprasrp_apl AS apl 
WHERE 
	apl.id = (SELECT MAX(id) FROM gelic_comprasrp_apl WHERE id_comprasrp = $pId_cd)");
	if ($db->nextRecord())
	{
		$dId_apl = $db->f("id");
		if (!$db->f("tipo") == 1) //nao preenchida pelo cliente
		{
			$aReturn[0] = 9;
			echo json_encode($aReturn);
			exit;
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		//APROVAR
		$db->query("INSERT INTO gelic_comprasrp_apl_historico VALUES (NULL, $dId_apl, $sInside_id, 2, '$ip', 0, 0, '$now', '$pCondicoes')");
		$dId_apl_historico = $db->li();
		$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $pId_cd, $dId_apl, $sInside_id, 42, $dId_apl_historico, '$now', '', '', '')");
		$dId_historico = $db->li();

		//ANEXO
		$arquivo_md5 = "";
		if ($pAnexo <> '' && file_exists(UPLOAD_DIR."~upcdaprc_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($pId_cd, $pAnexo, 'cdapr'.time().$sInside_id));

			//adicionar arquivo no S3 em vw/cdapr/...
			uploadFileBucket(UPLOAD_DIR."~upcdaprc_".$sInside_id.".tmp", "vw/cdapr/".$arquivo_md5);

			//remover arquivo temporario
			@unlink(UPLOAD_DIR."~upcdaprc_".$sInside_id.".tmp");
		}

		//INSERIR CAMPOS DE APROVACAO
		$db->query("INSERT INTO gelic_comprasrp_apl_aprovadas VALUES (NULL, $dId_apl, $dId_apl_historico, 1, '$pAve', $pQuantidade, '$pModel_code', '$pCor', '$pOpcionais_pr', $pPreco_publico, $pPrazo_de_entrega, $pDesconto_vw, $pComissao_dn, $pPlanta, $pValor_da_transformacao, '$pAnexo', '$arquivo_md5')");



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
	IF (cli.id_parent > 0, clip.id, cli.id) AS id_parent,
	(SELECT data_hora FROM gelic_comprasrp_historico WHERE id_comprasrp = cdsrp.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS data_hora
FROM
	gelic_comprasrp AS cdsrp
    INNER JOIN gelic_clientes AS cli ON cli.id = cdsrp.id_cliente
    LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	cdsrp.id = $pId_cd");
		$db->nextRecord();
		$dId_parent = $db->f("id_parent");
	

		$tEmail_srp = '';
		if ($db->f("tipo") == 2)
			$tEmail_srp = '<span style="font-weight: bold;">Número do SRP:</span> '.utf8_encode($db->f("numero_srp")).'<br>
<span style="font-weight: bold;">Data do SRP:</span> '.mysqlToBr($db->f("data_srp")).'<br>';

		$tEmail_assunto = 'GELIC - APL Aprovada (COMPRA DIRETA/SRP)';
		$tEmail_mensagem = 'APL Aprovada referente a seguinte solicitação de Compra Direta/SRP.<br><br>
<span style="font-weight: bold;">DN:</span> '.utf8_encode($db->f("nome_dn")).'<br>
<span style="font-weight: bold;">Tipo da Solicitação:</span> '.$db->f("tipo_desc").'<br>
<span style="font-weight: bold;">Data/Hora da Solicitação:</span> '.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.substr($db->f("data_hora"),11,5).'<br>
<span style="font-weight: bold;">Nome do Órgão Público:</span> '.utf8_encode($db->f("orgao")).'<br>
<span style="font-weight: bold;">Descritivo do Veículo:</span> '.utf8_encode($db->f("descritivo_veiculo")).'<br>
'.$tEmail_srp.'<span style="font-weight: bold;">Quantidade:</span> '.$db->f("quantidade").'<br>
<span style="font-weight: bold;">Valor:</span> R$ '.number_format($db->f("valor"),2,",",".").'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
		$tTexto_sms = 'GELIC - APL Aprovada (COMPRA DIRETA/SRP) DN: '.utf8_encode($db->f("nome_dn"));


		// Notificar ADMINs
		$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
		while ($db->nextRecord())
		{
			if (in_array("M", str_split($db->f("nt_email"))))
				queueMessage(13, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("M", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
				queueMessage(13, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
		}


		//notificar DNs
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

			if (in_array("O", str_split($dNt_email["ntf"])))
				queueMessage(41, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("O", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
				queueMessage(41, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
		}
		//-----------------------------------------
		//-----------------------------------------
		//-----------------------------------------


		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
