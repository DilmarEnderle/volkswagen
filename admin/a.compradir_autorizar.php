<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cd_autorizar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pId_cd = intval($_POST["id-cd"]);
	$now = date("Y-m-d H:i:s");

	$aTipo = array();
	$aTipo[1] = 'Solicitação de Compra Direta';
	$aTipo[2] = 'Adesão à ata de Registro de Preços';

	$db = new Mysql();

	//verificar se ja foi autorizado
	$db->query("SELECT id FROM gelic_comprasrp_historico WHERE id_comprasrp = $pId_cd AND tipo = 5 LIMIT 1");
	if (!$db->nextRecord())
	{
		$db->query("
SELECT
	cdsrp.tipo,
	cdsrp.orgao,
	cdsrp.numero_srp,
	cdsrp.data_srp,
	cdsrp.descritivo_veiculo,
	cdsrp.quantidade,
	cdsrp.valor,
	IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome_cliente,
	IF (cli.id_parent > 0, clip.id, cli.id) AS id_parent,
	(SELECT data_hora FROM gelic_comprasrp_historico WHERE id_comprasrp = cdsrp.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS data_hora
FROM
	gelic_comprasrp AS cdsrp
	INNER JOIN gelic_clientes AS cli ON cli.id = cdsrp.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	cdsrp.id = $pId_cd AND
	cdsrp.deletado = 0");
		if ($db->nextRecord())
		{
			$dTipo = $db->f("tipo");
			$dOrgao = $db->f("orgao");
			$dNumero_srp = $db->f("numero_srp");
			$dData_srp = $db->f("data_srp");
			$dDescrivito_veiculo = $db->f("descritivo_veiculo");
			$dQuantidade = $db->f("quantidade");
			$dValor = 'R$ '.number_format($db->f("valor"), 2, ",", ".");
			$dNome_cliente = $db->f("nome_cliente");
			$dId_parent = $db->f("id_parent");
			$dDatahora = $db->f("data_hora");

			$tEmail_srp = '';
			if ($dTipo == 2)
				$tEmail_srp = '<span style="font-weight: bold;">Número do SRP:</span> '.utf8_encode($dNumero_srp).'<br>
<span style="font-weight: bold;">Data do SRP:</span> '.mysqlToBr($dData_srp).'<br>';


			//autorizar
			$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $pId_cd, 0, $sInside_id, 5, 0, '$now', '', '', '')");


			//-----------------------------------------
			//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
			//-----------------------------------------
			$tEmail_assunto = 'GELIC - O envio da APL foi autorizado (Compra Direta/SRP)';
			$tEmail_mensagem = 'O envio da APL para a seguinte solicitação de Compra Direta/SRP foi autorizado.<br><br>
<span style="font-weight: bold;">Tipo da Solicitação:</span> '.$aTipo[$dTipo].'<br>
<span style="font-weight: bold;">Data/Hora da Solicitação:</span> '.mysqlToBr(substr($dDatahora,0,10)).' '.substr($dDatahora,11,5).'<br>
<span style="font-weight: bold;">Nome do Órgão Público:</span> '.utf8_encode($dOrgao).'<br>
<span style="font-weight: bold;">Descritivo do Veículo:</span> '.utf8_encode($dDescrivito_veiculo).'<br>
'.$tEmail_srp.'
<span style="font-weight: bold;">Quantidade:</span> '.$dQuantidade.'<br>
<span style="font-weight: bold;">Valor:</span> '.$dValor.'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
			$tTexto_sms = 'GELIC - O envio da APL foi autorizado (Compra Direta/SRP)';


			// Notificar DNs
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

				if (in_array("N", str_split($dNt_email["ntf"])))
					queueMessage(40, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("N", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
					queueMessage(40, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
			//-----------------------------------------
			//-----------------------------------------
			//-----------------------------------------


			$aReturn[0] = 1; //sucesso
			$aReturn[1] = "a.compradir_abrir.php?id=".$pId_cd."&t=".time();
		}
	}
}
echo json_encode($aReturn);

?>
