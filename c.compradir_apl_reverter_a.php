<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	$xAccess = explode(" ",getAccess());

	//deixar somente o back office daqui pra frente
	if ($sInside_tipo <> 1 || !in_array("cd_apl_aprovar", $xAccess))
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_apl = intval($_POST["id-apl"]);
	$pObservacoes = $db->escapeString(strip_tags(trim(utf8_decode($_POST["observacoes"]))));
	$now = date("Y-m-d H:i:s");


	//verificar se esta APL foi aprovada
	$db->query("SELECT id, tipo FROM gelic_comprasrp_apl_historico WHERE id_apl = $pId_apl ORDER BY id DESC LIMIT 1");
	$db->nextRecord();
	if (!$db->f("tipo") == 2)
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}
	else
	{
		$dId_apl_historico = $db->f("id");

		//pegar id_cd
		$db->query("SELECT id_comprasrp FROM gelic_comprasrp_apl WHERE id = $pId_apl");
		$db->nextRecord();
		$dId_cd = $db->f("id_comprasrp");

		//alterar APL historico tipo 2 para tipo 7
		$db->query("UPDATE gelic_comprasrp_apl_historico SET tipo = 7 WHERE id = $dId_apl_historico");

		//adicionar APL historico tipo 5
		$ip = $_SERVER['REMOTE_ADDR'];
		$db->query("INSERT INTO gelic_comprasrp_apl_historico VALUES (NULL, $pId_apl, $sInside_id, 5, '$ip', 0, 0, '$now', '$pObservacoes')");
		$dId_apl_historico = $db->li();

		//adicionar historico tipo 44 (APL Aprovação Revertida)
		$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $dId_cd, $pId_apl, $sInside_id, 44, $dId_apl_historico, '$now', '', '', '')");

		//desativar registro de aprovacao
		$db->query("UPDATE gelic_comprasrp_apl_aprovadas SET ativo = 0 WHERE id_apl = $pId_apl");




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
	cdsrp.id = $dId_cd");
		$db->nextRecord();
		$dId_parent = $db->f("id_parent");
	

		$tEmail_srp = '';
		if ($db->f("tipo") == 2)
			$tEmail_srp = '<span style="font-weight: bold;">Número do SRP:</span> '.utf8_encode($db->f("numero_srp")).'<br>
<span style="font-weight: bold;">Data do SRP:</span> '.mysqlToBr($db->f("data_srp")).'<br>';

		$tEmail_assunto = 'GELIC - Aprovação Revertida (COMPRA DIRETA/SRP)';
		$tEmail_mensagem = 'APL Aprovação Revertida referente a seguinte solicitação de Compra Direta/SRP.<br><br>
<span style="font-weight: bold;">DN:</span> '.utf8_encode($db->f("nome_dn")).'<br>
<span style="font-weight: bold;">Tipo da Solicitação:</span> '.$db->f("tipo_desc").'<br>
<span style="font-weight: bold;">Data/Hora da Solicitação:</span> '.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.substr($db->f("data_hora"),11,5).'<br>
<span style="font-weight: bold;">Nome do Órgão Público:</span> '.utf8_encode($db->f("orgao")).'<br>
<span style="font-weight: bold;">Descritivo do Veículo:</span> '.utf8_encode($db->f("descritivo_veiculo")).'<br>
'.$tEmail_srp.'<span style="font-weight: bold;">Quantidade:</span> '.$db->f("quantidade").'<br>
<span style="font-weight: bold;">Valor:</span> R$ '.number_format($db->f("valor"),2,",",".").'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
		$tTexto_sms = 'GELIC - APL Aprovação Revertida (COMPRA DIRETA/SRP) DN: '.utf8_encode($db->f("nome_dn"));



		// Notificar ADMINs
		$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
		while ($db->nextRecord())
		{
			if (in_array("O", str_split($db->f("nt_email"))))
				queueMessage(15, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("O", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
				queueMessage(15, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
		}


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

			if (in_array("Q", str_split($dNt_email["ntf"])))
				queueMessage(43, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("Q", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
				queueMessage(43, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
		}
		//-----------------------------------------
		//-----------------------------------------
		//-----------------------------------------



		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
