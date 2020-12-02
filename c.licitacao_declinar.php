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

	if ($sInside_tipo == 1 || !in_array("lic_interesse", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pId_motivo = intval($_POST["f-id-motivo"]);
	$pId_submotivo = intval($_POST["f-id-submotivo"]);
	$pObservacoes = utf8_decode(trim($_POST["f-observacoes"]));

	$db = new Mysql();
	$now = date("Y-m-d H:i:s");

	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];

	//verificar se este DN ja declinou
	$db->query("SELECT id FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND tipo IN (22,31)");
	if ($db->nextRecord())
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}


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
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	lic.id = $pId_licitacao AND
	lic.deletado = 0");
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
		$db->query("SELECT cli.nome, clidn.nome AS nome_dn, IF (cli.id_parent > 0, clidn.dn, cli.dn) AS dn FROM gelic_clientes AS cli LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent WHERE cli.id = $sInside_id");
		$db->nextRecord();
		$dNome_cliente = $db->f("nome");
		$dDn = $db->f("dn");

		if ($sInside_tipo == 3)
			$dNome_cliente .= ' (DN: '.$db->f("nome_dn").')';

		//inserir historico
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 22, $pId_motivo, $pId_submotivo, '$now', '$pObservacoes', '', '')");


		$dab_h = substr($dDatahora_abertura,11,5);
		$den_h = substr($dDatahora_entrega,11,5);
		$pzl_h = substr($dDatahora_limite,11,5);

		if ($dab_h == "00:00") $dab_h = "--:--";
		if ($den_h == "00:00") $den_h = "--:--";
		if ($pzl_h == "00:00") $pzl_h = "--:--";

		//-----------------------------------------
		//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
		//-----------------------------------------
		//$tEmail_assunto = 'GELIC - O DN declinou a participação LIC '.$dId_licitacao;
		$tEmail_assunto_admin = $dRegiao_abv.'('.$dUf.') - DN '.$dDn.' - Sem Interesse - LIC '.$dId_licitacao;

		$tEmail_mensagem = 'O DN não tem interesse em participar da seguinte licitação.<br><br>
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
<span style="font-weight: bold;">Usuário/DN:</span> '.utf8_encode($dNome_cliente).'<br><br>
'.rodapeEmail();
		$tTexto_sms = 'GELIC - A licitacao '.$dId_licitacao.' foi declinada por '.utf8_encode($dNome_cliente);


		// Notificar ADMINs
		$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
		if ($db->nextRecord())
		{
			if (in_array("C", str_split($db->f("nt_email"))))
				queueMessage(3, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto_admin, $tEmail_mensagem, '', '');
	
			if (in_array("C", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
				queueMessage(3, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
		}
		//-----------------------------------------
		//-----------------------------------------
		//-----------------------------------------


		$aReturn[0] = 1; //sucesso
		$aReturn[1] = "index.php?p=cli_open&id=$dId_licitacao";
	}
}
echo json_encode($aReturn);

?>
