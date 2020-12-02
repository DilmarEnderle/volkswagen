<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_mensagem", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();
	$pId_licitacao = intval($_POST["f-id_licitacao"]);
	$pMensagem = utf8_decode(trim($_POST["f-mensagem"]));
	$pMensagem = preg_replace("/\s+/", " ", $pMensagem);
	$pMensagem = $db->escapeString($pMensagem);
	$pAnexo = utf8_decode(trim($_POST["f-anexo"]));
	$now = date("Y-m-d H:i:s");

	$db->query("SELECT 
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
					cid.uf AS uf,
					uf.regiao_abv,
					(SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo
				FROM 
					gelic_licitacoes AS lic,
					gelic_modalidades AS mdl,
					gelic_cidades AS cid,
					gelic_uf AS uf
				WHERE
					lic.id = $pId_licitacao AND
					lic.deletado = 0 AND
					lic.id_modalidade = mdl.id AND
					lic.id_cidade = cid.id AND
					uf.uf = cid.uf
				HAVING
					ultimo_tipo <> 31");

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
		$dFase = $db->f("fase");
		$dNome_modalidade = $db->f("nome_modalidade");
		$dNome_cidade = $db->f("nome_cidade");
		$dUf = $db->f("uf");
		$dRegiao_abv = $db->f("regiao_abv");


		//processar arquivo anexo
		if ($pAnexo <> '' && file_exists(UPLOAD_DIR."~upmsga_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($dId_licitacao, $pAnexo, 'msga'.time().$sInside_id));

			//adicionar arquivo no S3 em vw/licchat/...
			uploadFileBucket(UPLOAD_DIR."~upmsga_".$sInside_id.".tmp", "vw/licchat/".$arquivo_md5);

			//remover arquivo temporario
			@unlink(UPLOAD_DIR."~upmsga_".$sInside_id.".tmp");

		 	$tEmail_arquivo = '<span style="font-weight: bold;">Arquivo anexo:</span> '.utf8_encode($pAnexo).'<br>';
		}
		else
		{
			$pAnexo = '';
			$arquivo_md5 = '';
			$tEmail_arquivo = '<span style="font-weight: bold;">Arquivo anexo:</span> <span style="font-style: italic;">não informado</span><br>';
		}



		//*********************************
		//***   MENSAGEM (1)   ***
		//*********************************

		//INSERIR HISTORICO
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, $sInside_id, 0, 1, 0, 0, '$now', '$pMensagem', '$pAnexo', '$arquivo_md5')");

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
<span style="font-weight: bold;">Mensagem:</span> '.utf8_encode(strip_tags($pMensagem)).'<br>
'.$tEmail_arquivo.'<br>'.rodapeEmail();
		$tTexto_sms = "GELIC - Nova mensagem referente a licitacao $dId_licitacao (".utf8_encode($dNome_cidade)." - $dUf)";



		$enviar_para_todos = true;
		$db->query("SELECT id FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo IN (2,41)");
		if ($db->nextRecord())
			$enviar_para_todos = false;


		// Notificar BOs e DNs
		if ($dFase == 1 && $enviar_para_todos)
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
		(cli.tipo = 1) OR
		(
			cli.tipo = 2 AND
			cli.id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $dId_licitacao)
		)
		OR
		(
			cli.tipo = 3 AND
			cli.id_parent IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $dId_licitacao)
		)
		OR
		(
			cli.tipo = 4 AND
			cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $dId_licitacao))
		)
	) AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0");
		else
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
		(cli.tipo = 1) OR
		(
			cli.tipo = 2 AND
			cli.id IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo IN (2,41))
		)
		OR
		(
			cli.tipo = 3 AND
			cli.id_parent IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo IN (2,41))
		)
		OR
		(
			cli.tipo = 4 AND
			cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso IN (SELECT DISTINCT(id_sender) FROM gelic_historico WHERE id_licitacao = $dId_licitacao AND tipo IN (2,41)))
		)
	) AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0");
		while ($db->nextRecord())
		{
			$dNt_email = json_decode($db->f("nt_email"), true);
			$dNt_sms = json_decode($db->f("nt_celular"), true);

			if ($db->f("tipo") == 1) //BO
			{
				if (in_array("A", str_split($dNt_email["ntf"])) && in_region("A", $dUf, $dNt_email["reg"]))
					queueMessage(17, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_BOF, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');
	
				if (in_array("A", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0 && in_region("A", $dUf, $dNt_sms["reg"]))
					queueMessage(17, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_BOF, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
			}
			else
			{
				if (in_array("B", str_split($dNt_email["ntf"])))
					queueMessage(28, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', '');
	
				if (in_array("B", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
					queueMessage(28, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
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
