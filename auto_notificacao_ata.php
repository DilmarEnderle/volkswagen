<?php

set_time_limit(0);
define("THIS_ID", 9);

$gKey = "";
if (isset($_GET["k"]))
	$gKey = $_GET["k"];

if ($gKey <> "a32cbc5af5914dc4624e2d28cd6a139e")
{
	echo "Acesso Negado.";
	exit;
}

require_once "include/config.php";
require_once "include/essential.php";

$db = new Mysql();

$db->selectDB("gelic_gelic");
$db->query("SELECT id FROM gelic_auto WHERE id = ".THIS_ID." AND ativo > 0");
if (!$db->nextRecord())
{
	echo "Script desativado. (gelic_auto)";
	exit;
}
$db->query("UPDATE gelic_auto SET script_in = NOW() WHERE id = ".THIS_ID);
$db->selectDB("gelic_vw");


$now = date("Y-m-d H:i:s");

// Procurar licitacoes com APL aprovada
// com data/hora de abertura vencida mas nao por mais do que 24 horas
$db->query("
SELECT
	lic.id,
	lic.datahora_entrega
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.notif_ata = 0 AND
	lic.datahora_abertura < '$now' AND
	DATE_ADD(lic.datahora_abertura, INTERVAL 24 HOUR) > '$now' AND
	ear.aprovadas > 0
GROUP BY
	lic.id");
while ($db->nextRecord())
{
	$dId_licitacao = $db->f("id");
	$dDatahora_entrega = $db->f("datahora_entrega");

	$den_h = substr($dDatahora_entrega,11,5);
	if ($den_h == "00:00") $den_h = "--:--";
	
	// Processar para cada cliente com APL aprovada
	$db->query("SELECT id_cliente FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $dId_licitacao AND aprovadas > 0",1);
	while ($db->nextRecord(1))
	{
		$dId_parent = $db->f("id_cliente",1);

		// Notificar DNs (tipo 4)
		$db->query("
SELECT
	cli.id,
	cli.email,
	cli.celular,
	cli.nt_email,
	cli.nt_celular,
	dn.nome,
	dn.dn
FROM 
	gelic_clientes_acesso AS acc
    INNER JOIN gelic_clientes AS cli ON cli.id = acc.id_cliente
    INNER JOIN gelic_clientes AS dn ON dn.id = acc.id_cliente_acesso
WHERE
	acc.id_cliente_acesso = $dId_parent AND
	cli.tipo = 4 AND
	cli.notificacoes = 1 AND
	cli.ativo = 1 AND
	cli.deletado = 0",2);
		while ($db->nextRecord(2))
		{
			$dNt_email = json_decode($db->f("nt_email",2), true);
			$dNt_sms = json_decode($db->f("nt_celular",2), true);

			$tEmail_assunto = 'GELIC - Notificação de ATA LIC '.$dId_licitacao;
			$tEmail_mensagem = 'Prezado DN '.utf8_encode($db->f("nome",2)).', solicitamos que envie pelo sistema a ATA da Sessão referente ao processo '.$dId_licitacao.' realizado no dia '.mysqlToBr(substr($dDatahora_entrega,0,10)).' '.$den_h.'.<br><br>'.rodapeEmail();
			$tTexto_sms = 'GELIC - Prezado DN '.$db->f("dn",2).', solicitamos que envie pelo sistema a ATA da Sessao referente ao processo '.$dId_licitacao;

			if (in_array("H", str_split($dNt_email["ntf"])))
				queueMessage(34, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_EMAIL, SYS_DLR, $db->f("id",2), $db->f("email",2), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("H", str_split($dNt_sms["ntf"])) && strlen($db->f("celular",2)) > 0)
				queueMessage(34, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_SMS, SYS_DLR, $db->f("id",2), $db->f("celular",2), '', $tTexto_sms, '', '');
		}


		// Notificar DNs (tipos 2 e 3)
		$db->query("
SELECT
	cli.id,
	cli.email,
	cli.celular,
	cli.nt_email,
	cli.nt_celular,
    IF (cli.tipo = 2, cli.nome, son.nome) AS nome,
	IF (cli.tipo = 2, cli.dn, son.dn) AS dn
FROM 
	gelic_clientes AS cli
    LEFT JOIN gelic_clientes AS son ON son.id = cli.id_parent
WHERE
	(cli.id = $dId_parent OR cli.id_parent = $dId_parent) AND
	cli.tipo IN (2,3) AND
	cli.notificacoes = 1 AND
	cli.ativo = 1 AND
	cli.deletado = 0",2);
		while ($db->nextRecord(2))
		{
			$dNt_email = json_decode($db->f("nt_email",2), true);
			$dNt_sms = json_decode($db->f("nt_celular",2), true);

			$tEmail_assunto = 'GELIC - Notificação de ATA LIC '.$dId_licitacao;
			$tEmail_mensagem = 'Prezado DN '.utf8_encode($db->f("nome",2)).', solicitamos que envie pelo sistema a ATA da Sessão referente ao processo '.$dId_licitacao.' realizado no dia '.mysqlToBr(substr($dDatahora_entrega,0,10)).' '.$den_h.'.<br><br>'.rodapeEmail();
			$tTexto_sms = 'GELIC - Prezado DN '.$db->f("dn",2).', solicitamos que envie pelo sistema a ATA da Sessao referente ao processo '.$dId_licitacao;

			if (in_array("H", str_split($dNt_email["ntf"])))
				queueMessage(34, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_EMAIL, SYS_DLR, $db->f("id",2), $db->f("email",2), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("H", str_split($dNt_sms["ntf"])) && strlen($db->f("celular",2)) > 0)
				queueMessage(34, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_SMS, SYS_DLR, $db->f("id",2), $db->f("celular",2), '', $tTexto_sms, '', '');
		}
	}

	//atualizar campo notif_ata
	$db->query("UPDATE gelic_licitacoes SET notif_ata = 1 WHERE id = $dId_licitacao",1);
}


// Apos a data de abertura, alterar o status para "Aguardando ata da sessão" (32)
$hora = intval(date("G")); //0-23
if ($hora == 0)
{
	$ontem = date('Y-m-d', strtotime("-1 days"));
	$hoje = date("Y-m-d");
	$now = date("Y-m-d H:i:s");

	$db->query("SELECT id FROM gelic_licitacoes WHERE datahora_abertura >= '$ontem 00:00:00' AND datahora_abertura <= '$ontem 23:59:59'");
	while ($db->nextRecord())
	{
		$dId_licitacao = $db->f("id");

		$db->query("SELECT id FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo = 37 AND DATE(data_hora) = '$hoje'",1);
		if (!$db->nextRecord(1))
		{
			//STATUS E ABAS ANTES DA ALTERACAO
			$aLic_aba_status = array("fr"=>array(),"to"=>array());
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			//ATUALIZAR STATUS
			$db->query("UPDATE gelic_licitacoes_abas SET id_status = 32 WHERE id_licitacao = $dId_licitacao",1);

			//INSERIR NO HISTORICO
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, 0, 0, 37, 0, 0, '$now', '', '', '')",1);
			$dId_historico = $db->li();

			//STATUS E ABAS DEPOIS DA ALTERACAO
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			//ANOTAR ALTERACAO DE ABAS E STATUS
			$db->query("UPDATE gelic_historico SET texto = '".json_encode($aLic_aba_status)."' WHERE id = $dId_historico",1);
		}
	}
}


$db->selectDB("gelic_gelic");
$db->query("UPDATE gelic_auto SET script_out = NOW() WHERE id = ".THIS_ID);
echo "Sucesso!";

?>
