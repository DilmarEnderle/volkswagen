<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	$db = new Mysql();

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pId_item = intval($_POST["f-id-item"]);
	$pId_cliente = intval($_POST["f-id-cliente"]);
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
	$pAnexo = utf8_decode(trim($_POST["f-apr-anexo"]));
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
	if ($sInside_tipo <> 1 || !in_array("lic_apl_aprovar", $xAccess))
	{
		logThis(date("Y-m-d H:i:s")." Acesso Restrito: (lic_apl_aprovar) BO: ".$sInside_id);
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}

	//pegar fase e aprovar apl
	$db->query("SELECT final, fase, aprovar_apl FROM gelic_licitacoes WHERE id = $pId_licitacao");
	$db->nextRecord();
	$dFase = $db->f("fase");

	if ($db->f("aprovar_apl") == 0)
	{
		logThis(date("Y-m-d H:i:s")." Acesso Restrito: (aprovar_apl) BO: ".$sInside_id);
		$aReturn[0] = 9;
		echo json_encode($aReturn);
		exit;
	}

	$db->query("
SELECT
	apl.id,
	IF (clip.id_parent > 0, clip.id_parent, clip.id) AS id_parent,
	(SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM
	gelic_licitacoes_apl AS apl
	LEFT JOIN gelic_clientes AS clip ON clip.id = apl.id_cliente
WHERE
	apl.id = (
		SELECT 
			MAX(id) 
		FROM 
			gelic_licitacoes_apl 
		WHERE 
			id_licitacao = $pId_licitacao AND 
			id_item = $pId_item AND 
			id_cliente = $pId_cliente
	)");
	if ($db->nextRecord())
	{
		$dId_apl = $db->f("id");
		$dId_parent = $db->f("id_parent");

		if (!in_array($db->f("tipo"), array(1,5,6))) //nao preenchida pelo cliente
		{
			logThis(date("Y-m-d H:i:s")." Acesso Restrito: (nao preenchida pelo cliente) BO: ".$sInside_id." APL: ".$dId_apl);
			$aReturn[0] = 9;
			echo json_encode($aReturn);
			exit;
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		//APROVAR
		$db->query("INSERT INTO gelic_licitacoes_apl_historico VALUES (NULL, $dId_apl, $sInside_id, 2, '$ip', 0, 0, '$now', '$pCondicoes')");
		$dId_apl_historico = $db->li();
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, $dId_apl, $sInside_id, 0, 42, $dId_apl_historico, 0, '$now', '', '', '')");
		$dId_historico = $db->li();

		//ANEXO
		$arquivo_md5 = "";
		if (strlen($pAnexo) > 0 && file_exists(UPLOAD_DIR."~upaprc_".$sInside_id.".tmp"))
		{
			if (!is_dir(UPLOAD_DIR."apr")) mkdir(UPLOAD_DIR."apr");
			$arquivo_md5 = strtolower(getFilename($pId_licitacao, $pAnexo, 'apr'.time().$sInside_id));
			rename(UPLOAD_DIR."~upaprc_".$sInside_id.".tmp", UPLOAD_DIR."apr/".$arquivo_md5);
		}

		//INSERIR CAMPOS DE APROVACAO
		$db->query("INSERT INTO gelic_licitacoes_apl_aprovadas VALUES (NULL, $dId_apl, $dId_apl_historico, 1, '$pAve', $pQuantidade, '$pModel_code', '$pCor', '$pOpcionais_pr', $pPreco_publico, $pPrazo_de_entrega, $pDesconto_vw, $pComissao_dn, $pPlanta, $pValor_da_transformacao, '$pAnexo', '$arquivo_md5')");



		//==========================
		// Atualizar tabela EAR
		//==========================
		$db->query("SELECT 
			(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = itm.id AND (id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))) HAVING tipo IN (1,5,6)) AS t) AS enviadas,
			(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = itm.id AND (id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))) HAVING tipo = 2) AS t) AS aprovadas,
			(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = itm.id AND (id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))) HAVING tipo = 4) AS t) AS reprovadas");
		$db->nextRecord();
		$enviadas = $db->f("enviadas");
		$aprovadas = $db->f("aprovadas");
		$reprovadas = $db->f("reprovadas");
	
		$db->query("SELECT id FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $pId_licitacao AND id_cliente = $dId_parent");
		if ($db->nextRecord())
			$db->query("UPDATE gelic_licitacoes_apl_ear SET enviadas = $enviadas, aprovadas = $aprovadas, reprovadas = $reprovadas WHERE id_licitacao = $pId_licitacao AND id_cliente = $dId_parent");
		else
			$db->query("INSERT INTO gelic_licitacoes_apl_ear VALUES (NULL, $pId_licitacao, $dId_parent, $enviadas, $aprovadas, $reprovadas)");
		//==========================



		//STATUS E ABAS ANTES DA ALTERACAO
		$aLic_aba_status = array("fr"=>array(),"to"=>array());
		$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao ORDER BY grupo");
		while ($db->nextRecord())
			$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));


		//*************************************************************************************
		//****************************** ALTERACAO DE ABA/STATUS ******************************
		//*************************************************************************************

		if ($dFase == 1)
		{
			// Se tiver alguma APL reprovada entao NAO alterar abas/status
			if ($reprovadas == 0)
			{
				$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 9, id_status = 8 WHERE id_licitacao = $pId_licitacao AND grupo IN (1,2,3)");
				$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 8, id_status = 27 WHERE id_licitacao = $pId_licitacao AND grupo = 4");

				//remover da aba APL
				$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo IN (1,2)");
				$db->nextRecord();
				if ($db->f("total") == 4)
					$db->query("DELETE FROM gelic_licitacoes_abas WHERE id IN
				        (
				        SELECT id
				        FROM
				            (
				                SELECT id
				                FROM gelic_licitacoes_abas
				                WHERE id_licitacao = $pId_licitacao AND grupo IN (1,2)
				                ORDER BY id DESC
				                LIMIT 2
				            ) a
				        )");

				// Se tiver alguma APL pendente manter na aba APL também (ADMIN e BO)
				if ($enviadas > 0)
				{
					//adicionar/manter na aba APL tambem
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 1, 14, 8, 0)");
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 2, 14, 8, 0)");
				}
			}
		}
		else
		{
			$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $pId_licitacao");
			$db->nextRecord();
			
			$total_enviadas = intval($db->f("enviadas"));
			$total_aprovadas = intval($db->f("aprovadas"));
			$total_reprovadas = intval($db->f("reprovadas"));

			// 1. Se encontrar algum cliente com APLs aprovadas e nenhuma reprovada entao colocar na aba "Em Participacao" para o DN, ADMIN e BO
			$db->query("SELECT id FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $pId_licitacao AND aprovadas > 0 AND reprovadas = 0");
			if ($db->nextRecord())
			{
				$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 9, id_status = 8 WHERE id_licitacao = $pId_licitacao AND grupo IN (1,2,3)");

				//remover da aba APL (ADMIN e BO)
				$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo IN (1,2)");
				$db->nextRecord();
				if ($db->f("total") == 4)
					$db->query("DELETE FROM gelic_licitacoes_abas WHERE id IN
				        (
				        SELECT id
				        FROM
				            (
				                SELECT id
				                FROM gelic_licitacoes_abas
				                WHERE id_licitacao = $pId_licitacao AND grupo IN (1,2)
				                ORDER BY id DESC
				                LIMIT 2
				            ) a
				        )");

				// 2. Se tiver alguma APL pendente manter na aba APL também (ADMIN e BO)
				if ($total_enviadas > 0)
				{
					//adicionar/manter na aba APL tambem
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 1, 14, 8, 0)");
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 2, 14, 8, 0)");
				}
			}

			// 3. Se todos clientes tiver pelo menos 1 APL reprovada entao remover da aba "Em Participacao" para o ADMIN e BO
			$db->query("SELECT SUM(reprovadas) AS reprovadas, COUNT(*) AS total FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $pId_licitacao HAVING reprovadas > 0 AND reprovadas = total");
			if ($db->nextRecord())
			{
				$db->query("DELETE FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo IN (1,2)");

				//readicionar na aba "APL" (ADMIN e BO)
				if ($total_reprovadas > 0)
				{
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 1, 14, 19, 0)"); //ADMIN
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 2, 14, 19, 0)"); //BO
				}
				else if ($total_aprovadas > 0)
				{
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 1, 14, 8, 0)"); //ADMIN
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 2, 14, 8, 0)"); //BO
				}
				else
				{
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 1, 14, 3, 0)"); //ADMIN
					$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 2, 14, 3, 0)"); //BO
				}
			}
		}
		//*************************************************************************************
		//********************************         END         ********************************
		//*************************************************************************************


		//STATUS E ABAS DEPOIS DA ALTERACAO
		$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao ORDER BY grupo");
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
	apl.id = $dId_apl");
		$db->nextRecord();
		$dId_parent = $db->f("id_parent");

		$tEmail_assunto = 'GELIC - APL Aprovada (LIC '.$db->f("id_licitacao").')';
		$tEmail_assunto_admin = $db->f("regiao_abv").'('.$db->f("uf").') - DN '.$db->f("dn").' - APL Aprovada - LIC '.$db->f("id_licitacao");

		$tEmail_mensagem = 'APL Aprovada.<br><br>
<span style="font-weight: bold;">Licitação:</span> '.$db->f("id_licitacao").'<br>
<span style="font-weight: bold;">DN:</span> '.utf8_encode($db->f("dn_nome")).'<br>
<span style="font-weight: bold;">Lote:</span> '.utf8_encode($db->f("lote")).'<br>
<span style="font-weight: bold;">Item:</span> '.utf8_encode($db->f("item")).'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
		$tTexto_sms = 'GELIC - APL Aprovada LIC '.$db->f("id_licitacao").' LOTE: '.utf8_encode($db->f("lote")).' ITEM: '.utf8_encode($db->f("item")).' DN: '.$db->f("dn");


		// Notificar ADMINs
		$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
		while ($db->nextRecord())
		{
			if (in_array("E", str_split($db->f("nt_email"))))
				queueMessage(5, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto_admin, $tEmail_mensagem, '', '');

			if (in_array("E", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
				queueMessage(5, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
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

			if (in_array("I", str_split($dNt_email["ntf"])))
				queueMessage(35, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, BOF_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');

			if (in_array("I", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
				queueMessage(35, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, BOF_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
		}
		//-----------------------------------------
		//-----------------------------------------
		//-----------------------------------------


		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
