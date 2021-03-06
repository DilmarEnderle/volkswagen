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
	if ($sInside_tipo <> 1 || !in_array("lic_apl_reprovar", $xAccess))
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_apl = intval($_POST["f-id-apl"]);
	$pObservacoes = $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-observacoes"]))));
	$pData_limite = trim($_POST["f-data-limite"]);  // '' or 'dd/mm/yyyy'
	$pHora_limite = trim($_POST["f-hora-limite"]);  // '' or 'hh:mm'
	$now = date("Y-m-d H:i:s");

	//verificar se esta APL foi aprovada
	$db->query("
SELECT
	ahis.id,
	ahis.tipo,
	IF (clip.id_parent > 0, clip.id_parent, clip.id) AS id_parent
FROM
	gelic_licitacoes_apl_historico AS ahis
	INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
	LEFT JOIN gelic_clientes AS clip ON clip.id = apl.id_cliente
WHERE
	ahis.id_apl = $pId_apl
ORDER BY
	id DESC LIMIT 1");
	$db->nextRecord();

	if (!$db->f("tipo") == 4) //nao reprovada
	{
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}

	$dId_apl_historico = $db->f("id");
	$dId_parent = $db->f("id_parent");


	//pegar id_licitacao e fase
	$db->query("SELECT id, fase, datahora_limite <= NOW() AS prazo_limite_expirou, DATE_FORMAT(datahora_abertura, '%Y%m%d%H%i%s') AS datahora_abertura FROM gelic_licitacoes WHERE id = (SELECT id_licitacao FROM gelic_licitacoes_apl WHERE id = $pId_apl)");
	$db->nextRecord();
	$dId_licitacao = $db->f("id");
	$dFase = $db->f("fase");
	$dPrazo_limite_expirou = $db->f("prazo_limite_expirou");

	$update = "";

	if ($dPrazo_limite_expirou > 0)
	{
		if (strlen($pData_limite) == 0)
		{
			$aReturn[0] = 8; //prazo limite expirou precisa informar novo
			echo json_encode($aReturn);
			exit;
		}
	}

	$aDhl_his = '';
	if (strlen($pData_limite) > 0 && strlen($pHora_limite) > 0)
	{
		$d = explode("/", $pData_limite);
		$h = explode(":", $pHora_limite);

		$datahora_novoprazo_int = intval($d[2].$d[1].$d[0].$h[0].$h[1]."00");
		$datahora_hoje_int = intval(date("YmdHis"));
		$datahora_abertura_int = $db->f("datahora_abertura");

		if ($datahora_novoprazo_int < $datahora_hoje_int || $datahora_novoprazo_int > $datahora_abertura_int)
		{
			$aReturn[0] = 7; //prazo limite incorreto
			echo json_encode($aReturn);
			exit;
		}

		$update = "datahora_limite = '".$d[2]."-".$d[1]."-".$d[0]." ".$h[0].":".$h[1].":00', ";
		$aDhl_his = array("nova_datahora_limite"=>$d[2]."-".$d[1]."-".$d[0]." ".$h[0].":".$h[1].":00");
	}

	if (is_array($aDhl_his))
		$pObservacoes = json_encode($aDhl_his).$pObservacoes;

	//adicionar APL historico tipo 6
	$ip = $_SERVER['REMOTE_ADDR'];
	$db->query("INSERT INTO gelic_licitacoes_apl_historico VALUES (NULL, $pId_apl, $sInside_id, 6, '$ip', 0, 0, '$now', '$pObservacoes')");
	$dId_apl_historico = $db->li();

	//adicionar historico tipo 45 (APL Reprovação Revertida)
	$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, $pId_apl, $sInside_id, 0, 45, $dId_apl_historico, 0, '$now', '', '', '')");
	$dId_historico = $db->li();


	if ($update != "")
	{
		if ($dFase == 1)
			$db->query("UPDATE gelic_licitacoes SET ".$update."aprovar_apl = 1 WHERE id = $dId_licitacao");
		else
			$db->query("UPDATE gelic_licitacoes SET ".$update."final = 0, aprovar_apl = 0 WHERE id = $dId_licitacao");
	}



	//==========================
	// Atualizar tabela EAR
	//==========================
	$db->query("SELECT 
		(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $dId_licitacao AND id_item = itm.id AND (id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))) HAVING tipo IN (1,5,6)) AS t) AS enviadas,
		(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $dId_licitacao AND id_item = itm.id AND (id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))) HAVING tipo = 2) AS t) AS aprovadas,
		(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $dId_licitacao AND id_item = itm.id AND (id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))) HAVING tipo = 4) AS t) AS reprovadas");
	$db->nextRecord();
	$enviadas = $db->f("enviadas");
	$aprovadas = $db->f("aprovadas");
	$reprovadas = $db->f("reprovadas");

	$db->query("SELECT id FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $dId_licitacao AND id_cliente = $dId_parent");
	if ($db->nextRecord())
		$db->query("UPDATE gelic_licitacoes_apl_ear SET enviadas = $enviadas, aprovadas = $aprovadas, reprovadas = $reprovadas WHERE id_licitacao = $dId_licitacao AND id_cliente = $dId_parent");
	else
		$db->query("INSERT INTO gelic_licitacoes_apl_ear VALUES (NULL, $dId_licitacao, $dId_parent, $enviadas, $aprovadas, $reprovadas)");
	//==========================



	//STATUS E ABAS ANTES DA ALTERACAO
	$aLic_aba_status = array("fr"=>array(),"to"=>array());
	$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo");
	while ($db->nextRecord())
		$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));


	//*************************************************************************************
	//****************************** ALTERACAO DE ABA/STATUS ******************************
	//*************************************************************************************
	if ($dFase == 1)
	{
		//resetar abas
		$db->query("DELETE FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao");

		if ($aprovadas == 0 && $reprovadas == 0)
		{
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 14, 3, 0)"); //ADMIN
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 14, 3, 0)"); //BO
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 3, 14, 3, 0)"); //DN
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 4, 8, 26, 0)"); //OUTRO DN	
		}
		else if ($reprovadas > 0)
		{
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 14, 19, 0)"); //ADMIN
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 14, 19, 0)"); //BO
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 3, 14, 19, 0)"); //DN
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 4, 8, 28, 0)"); //OUTRO DN	
		}
		else if ($aprovadas > 0)
		{
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 9, 8, 0)"); //ADMIN
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 9, 8, 0)"); //BO
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 3, 9, 8, 0)"); //DN
			$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 4, 8, 27, 0)"); //OUTRO DN

			if ($enviadas > 0)
			{
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 14, 8, 0)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 14, 8, 0)"); //BO
			}
		}
	}
	else
	{
		$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $dId_licitacao");
		$db->nextRecord();
			
		$total_enviadas = intval($db->f("enviadas"));
		$total_aprovadas = intval($db->f("aprovadas"));
		$total_reprovadas = intval($db->f("reprovadas"));

		// 1. Se encontrar algum cliente com APLs aprovadas e nenhuma reprovada entao colocar na aba "Em Participacao" para o DN, ADMIN e BO
		$db->query("SELECT id FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $dId_licitacao AND aprovadas > 0 AND reprovadas = 0");
		if ($db->nextRecord())
		{
			$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 9, id_status = 8 WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2,3)");

			//remover da aba APL (ADMIN e BO)
			$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2)");
			$db->nextRecord();
			if ($db->f("total") == 4)
				$db->query("DELETE FROM gelic_licitacoes_abas WHERE id IN
			        (
			        SELECT id
			        FROM
			            (
			                SELECT id
			                FROM gelic_licitacoes_abas
			                WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2)
			                ORDER BY id DESC
			                LIMIT 2
			            ) a
			        )");

			// 2. Se tiver alguma APL pendente manter na aba APL também (ADMIN e BO)
			if ($total_enviadas > 0)
			{
				//adicionar/manter na aba APL tambem
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 14, 8, 0)");
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 14, 8, 0)");
			}
		}

		// 3. Se todos clientes tiver pelo menos 1 APL reprovada entao remover da aba "Em Participacao" para o ADMIN e BO
		$db->query("SELECT SUM(reprovadas) AS reprovadas, COUNT(*) AS total FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $dId_licitacao HAVING reprovadas > 0 AND reprovadas = total");
		if ($db->nextRecord() || $total_aprovadas == 0)
		{
			$db->query("DELETE FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2)");

			//readicionar na aba "APL" (ADMIN e BO)
			if ($total_reprovadas > 0)
			{
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 14, 19, 0)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 14, 19, 0)"); //BO
			}
			else if ($total_aprovadas > 0)
			{
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 14, 8, 0)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 14, 8, 0)"); //BO
			}
			else
			{
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 14, 3, 0)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 14, 3, 0)"); //BO
			}
		}
	}
	//*************************************************************************************
	//********************************         END         ********************************
	//*************************************************************************************


	//STATUS E ABAS DEPOIS DA ALTERACAO
	$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo");
	while ($db->nextRecord())
		$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));

	$db->query("UPDATE gelic_historico SET texto = '".json_encode($aLic_aba_status)."' WHERE id = $dId_historico");


	//-----------------------------------------
	//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
	//-----------------------------------------
	$db->query("
SELECT
	apl.id_licitacao,
    IF (cli.id_parent > 0, clip.nome, cli.nome) AS dn_nome,
	IF (cli.id_parent > 0, clip.id, cli.id) AS id_parent,
	IF (cli.id_parent > 0, clip.dn, cli.dn) AS dn,
    itm.item,
    lot.lote,
    lic.orgao,
    uf.uf,
    uf.regiao_abv
FROM
	gelic_licitacoes_apl AS apl
	INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
	INNER JOIN gelic_licitacoes AS lic ON lic.id = apl.id_licitacao
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	apl.id = $pId_apl");
	$db->nextRecord();
	$dId_parent = $db->f("id_parent");

	$tEmail_assunto = 'GELIC - APL Reprovação Revertida (LIC '.$db->f("id_licitacao").')';
	$tEmail_assunto_admin = $db->f("regiao_abv").'('.$db->f("uf").') - DN '.$db->f("dn").' - APL Reprovação Revertida - LIC '.$db->f("id_licitacao");


	$tEmail_mensagem = 'APL Reprovação Revertida.<br><br>
<span style="font-weight: bold;">Licitação:</span> '.$db->f("id_licitacao").'<br>
<span style="font-weight: bold;">DN:</span> '.utf8_encode($db->f("dn_nome")).'<br>
<span style="font-weight: bold;">Lote:</span> '.utf8_encode($db->f("lote")).'<br>
<span style="font-weight: bold;">Item:</span> '.utf8_encode($db->f("item")).'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
	$tTexto_sms = 'GELIC - APL Reprovacao Revertida LIC '.$db->f("id_licitacao").' LOTE: '.utf8_encode($db->f("lote")).' ITEM: '.utf8_encode($db->f("item")).' DN: '.$db->f("dn");


	// Notificar ADMINs
	$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
	while ($db->nextRecord())
	{
		if (in_array("H", str_split($db->f("nt_email"))))
			queueMessage(8, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto_admin, $tEmail_mensagem, '', '');

		if (in_array("H", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
			queueMessage(8, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
	}


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

		if (in_array("L", str_split($dNt_email["ntf"])))
			queueMessage(38, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

		if (in_array("L", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
			queueMessage(38, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
	}
	//-----------------------------------------
	//-----------------------------------------
	//-----------------------------------------


	$aReturn[0] = 1; //sucesso
	$aReturn[1] = "index.php?p=cli_open&id=$dId_licitacao";
}
echo json_encode($aReturn);

?>
