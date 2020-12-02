<?php

set_time_limit(0);
define("THIS_ID", 7);

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

$aEstados = array();
$aEstados["AC"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["AL"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["AP"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["AM"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["BA"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["CE"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["DF"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["ES"] = "'ES','MG','RJ'";
$aEstados["GO"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["MA"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["MT"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["MS"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["MG"] = "'ES','MG','RJ'";
$aEstados["PA"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["PB"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["PR"] = "'PR','RS','SC'";
$aEstados["PE"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["PI"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["RJ"] = "'ES','MG','RJ'";
$aEstados["RN"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["RS"] = "'PR','RS','SC'";
$aEstados["RO"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["RR"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
$aEstados["SC"] = "'PR','RS','SC'";
$aEstados["SP"] = "'SP'";
$aEstados["SE"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
$aEstados["TO"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";


$aFeriados = array();
$db->query("SELECT CONCAT(LPAD(mes,2,'0'),LPAD(dia,2,'0')) AS feriado FROM gelic_feriados");
while ($db->nextRecord()) { $aFeriados[] = $db->f("feriado"); } //array com valores em (mmdd)


$now = date("Y-m-d H:i:s");


//******************************************************************************
// VARRER FASE 1 (ADVE/POOL)
//******************************************************************************
$db->query("
SELECT
	lic.id,
	cid.uf,
	UNIX_TIMESTAMP(lic.datahora_limite) AS datahora_limite,
    (SELECT id FROM gelic_licitacoes_edital WHERE id_licitacao = lic.id LIMIT 1) AS edital
FROM 
	gelic_licitacoes AS lic 
    INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
WHERE 
	lic.final = 0 AND 
    lic.fase = 1 AND 
    lic.datahora_limite <= '$now'");
while ($db->nextRecord())
{
	$dId_licitacao = $db->f("id");
	$dUf = $db->f("uf");
	$dDatahora_limite = $db->f("datahora_limite");


	//verificar se o status do grupo 1 esta marcado como fixo
	$fixo = false;
	$db->query("SELECT id FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao AND grupo = 1 AND status_fixo = 1", 1);
	if ($db->nextRecord(1))
		$fixo = true;

	//nao deixar prosseguir de fase se o status estiver fixo
	if (strlen($db->f("edital")) > 0 && !$fixo)
	{
		$t_24horas = $dDatahora_limite;
		$pPointer = date("Ymdw", $t_24horas);
		$pDia_util = 0;
		while ($pDia_util == 0)
		{
			$t_24horas += 86400;
			$pPointer = date("Ymdw", $t_24horas);
			if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
				$pDia_util = 1;
		}
		$nova_datahora_limite = date("Y-m-d H:i:s", $t_24horas);

		//verificar se existe alguma APL (so ir para fase 2 se nao existirem APLs)
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_apl WHERE id_licitacao = $dId_licitacao",1);
		$db->nextRecord(1);
		if ($db->f("total",1) == 0)
		{
			//IR PARA FASE 2 (REGIAO)

			//atualizar fase, aprovar_apl e datahora_limite
			$db->query("UPDATE gelic_licitacoes SET fase = 2, aprovar_apl = 0, datahora_limite = '$nova_datahora_limite' WHERE id = $dId_licitacao",1);
	
			//anotar troca de fase
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, 0, 0, 13, 1, 2, '$now', '', '', '')",1);
			$dId_historico = $db->li();
	
			//adicionar DNs da regiao ainda nao na tabela
			$db->query("SELECT cli.id FROM gelic_clientes AS cli, gelic_cidades AS cid WHERE cid.uf IN (".$aEstados[$dUf].") AND cli.id_cidade = cid.id AND cli.id NOT IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $dId_licitacao)",1);
			while ($db->nextRecord(1))
				$db->query("INSERT INTO gelic_licitacoes_clientes VALUES (NULL, $dId_licitacao, ".$db->f("id",1).")",2);


			//STATUS E ABAS ANTES DA ALTERACAO
			$aLic_aba_status = array("fr"=>array(),"to"=>array());
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));


			//ABA
			$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 8 WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2,3,4)",1);

			//STATUS
			$db->query("UPDATE gelic_licitacoes_abas SET id_status = 4 WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2,3,4) AND status_fixo = 0",1);


			//STATUS E ABAS DEPOIS DA ALTERACAO
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			$db->query("UPDATE gelic_historico SET texto = '".json_encode($aLic_aba_status)."' WHERE id = $dId_historico",1);
		}
		else
		{
			//parar
			$db->query("UPDATE gelic_licitacoes SET final = 1 WHERE id = $dId_licitacao",1);

			//anotar final no historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, 0, 0, 14, 1, 0, '$now', '', '', '')",1);
		}
	}
	else
	{
		// SEM EDITAL OU STATUS FIXO (nao avanca de fase)
		// Somente adiciona mais 24 horas de limite a partir de agora
		$t_24horas = time();
		$pPointer = date("Ymdw", $t_24horas);
		$pDia_util = 0;
		while ($pDia_util == 0)
		{
			$t_24horas += 86400;
			$pPointer = date("Ymdw", $t_24horas);
			if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
				$pDia_util = 1;
		}
		$nova_datahora_limite = date("Y-m-d H:i:s", $t_24horas);

		$db->query("UPDATE gelic_licitacoes SET datahora_limite = '$nova_datahora_limite' WHERE id = $dId_licitacao",1);
	}
}






//******************************************************************************
// VARRER FASE 2 (REGIAO)
//******************************************************************************
$db->query("
SELECT 
	lic.id, 
	UNIX_TIMESTAMP(lic.datahora_limite) AS datahora_limite,
    (SELECT id FROM gelic_licitacoes_edital WHERE id_licitacao = lic.id LIMIT 1) AS edital
FROM 
	gelic_licitacoes AS lic
WHERE 
	lic.final = 0 AND 
    lic.fase = 2 AND 
    lic.datahora_limite <= '$now'");
while ($db->nextRecord())
{
	$dId_licitacao = $db->f("id");
	$dDatahora_limite = $db->f("datahora_limite");

	if (strlen($db->f("edital")) > 0)
	{
		$t_24horas = $dDatahora_limite;
		$pPointer = date("Ymdw", $t_24horas);
		$pDia_util = 0;
		while ($pDia_util == 0)
		{
			$t_24horas += 86400;
			$pPointer = date("Ymdw", $t_24horas);
			if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
				$pDia_util = 1;
		}
		$nova_datahora_limite = date("Y-m-d H:i:s", $t_24horas);

		//verificar se existe alguma APL (so ir para fase 3 se nao existirem APLs)
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_apl WHERE id_licitacao = $dId_licitacao",1);
		$db->nextRecord(1);
		if ($db->f("total",1) == 0)
		{
			//IR PARA FASE 3 (BRASIL)

			//atualizar fase, aprovar_apl e datahora_limite
			$db->query("UPDATE gelic_licitacoes SET fase = 3, aprovar_apl = 0, datahora_limite = '$nova_datahora_limite' WHERE id = $dId_licitacao",1);

			//anotar troca de fase
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, 0, 0, 13, 2, 3, '$now', '', '', '')",1);
			$dId_historico = $db->li();

			//adicionar DNs do brasil ainda nao na tabela
			$db->query("SELECT id FROM gelic_clientes WHERE id NOT IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $dId_licitacao)",1);
			while ($db->nextRecord(1))
				$db->query("INSERT INTO gelic_licitacoes_clientes VALUES (NULL, $dId_licitacao, ".$db->f("id",1).")",2);


			//STATUS E ABAS ANTES DA ALTERACAO
			$aLic_aba_status = array("fr"=>array(),"to"=>array());
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));


			//ABA
			$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 8 WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2,3,4)",1);

			//STATUS
			$db->query("UPDATE gelic_licitacoes_abas SET id_status = 5 WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2,3,4) AND status_fixo = 0",1);


			//STATUS E ABAS DEPOIS DA ALTERACAO
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			$db->query("UPDATE gelic_historico SET texto = '".json_encode($aLic_aba_status)."' WHERE id = $dId_historico",1);
		}
		else
		{
			//parar
			$db->query("UPDATE gelic_licitacoes SET final = 1, aprovar_apl = 1 WHERE id = $dId_licitacao",1);

			//STATUS E ABAS ANTES DA ALTERACAO
			$aLic_aba_status = array("fr"=>array(),"to"=>array());
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			//para os que enviaram APL mudar o status para "Aguardando Aprovação APL" somente se o status for "Aguardando prazo de envio da APL (30)"
			$db->query("UPDATE gelic_licitacoes_abas SET id_status = 3 WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2,3) AND status_fixo = 0 AND id_status = 30",1);

			//STATUS E ABAS DEPOIS DA ALTERACAO
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			//anotar final no historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, 0, 0, 14, 2, 0, '$now', '".json_encode($aLic_aba_status)."', '', '')",1);


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
	cid.uf AS uf
FROM 
	gelic_licitacoes AS lic,
	gelic_modalidades AS mdl,
	gelic_cidades AS cid
WHERE
	lic.id = $dId_licitacao AND
	lic.deletado = 0 AND
	lic.id_modalidade = mdl.id AND
	lic.id_cidade = cid.id",1);
			$db->nextRecord(1);

			$dId_licitacao = $db->f("id",1);
			$dOrgao = $db->f("orgao",1);
			$dObjeto = $db->f("objeto",1);
			$dImportante = $db->f("importante",1);
			$dValor = $db->f("valor",1);
			$dDatahora_abertura = $db->f("datahora_abertura",1);
			$dDatahora_entrega = $db->f("datahora_entrega",1);
			$dDatahora_limite = $db->f("datahora_limite",1);
			$dNome_modalidade = $db->f("nome_modalidade",1);
			$dNome_cidade = $db->f("nome_cidade",1);
			$dUf = $db->f("uf",1);

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
			$tEmail_assunto = 'GELIC - Término do Prazo Limite LIC '.$dId_licitacao;
			$tEmail_mensagem = 'O prazo limite para envio de APLs expirou. As APLs podem ser aprovadas/reprovadas para esta licitação.<br><br>
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
'.rodapeEmail();
			$tTexto_sms = "GELIC - Termino do Prazo Limite LIC $dId_licitacao (".utf8_encode($dNome_cidade)." - $dUf)";

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
	cli.deletado = 0",1);
			while ($db->nextRecord(1))
			{
				$dNt_email = json_decode($db->f("nt_email",1), true);
				$dNt_sms = json_decode($db->f("nt_celular",1), true);

				if (in_array("F", str_split($dNt_email["ntf"])) && in_region("F", $dUf, $dNt_email["reg"]))
					queueMessage(22, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_EMAIL, SYS_BOF, $db->f("id",1), $db->f("email",1), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("F", str_split($dNt_sms["ntf"])) && strlen($db->f("celular",1)) > 0 && in_region("F", $dUf, $dNt_sms["reg"]))
					queueMessage(22, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_SMS, SYS_BOF, $db->f("id",1), $db->f("celular",1), '', $tTexto_sms, '', '');
			}
			//-----------------------------------------
			//-----------------------------------------
			//-----------------------------------------
		}
	}
	else
	{
		// SEM EDITAL (nao avanca de fase)
		// Somente adiciona mais 24 horas de limite a partir de agora
		$t_24horas = time();
		$pPointer = date("Ymdw", $t_24horas);
		$pDia_util = 0;
		while ($pDia_util == 0)
		{
			$t_24horas += 86400;
			$pPointer = date("Ymdw", $t_24horas);
			if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
				$pDia_util = 1;
		}
		$nova_datahora_limite = date("Y-m-d H:i:s", $t_24horas);

		$db->query("UPDATE gelic_licitacoes SET datahora_limite = '$nova_datahora_limite' WHERE id = $dId_licitacao",1);
	}
}





//******************************************************************************
// VARRER FASE 3 (BRASIL)
//******************************************************************************
$db->query("
SELECT 
	lic.id, 
	(lic.datahora_limite < lic.datahora_abertura) AS aumentar_limite,
    (SELECT id FROM gelic_licitacoes_edital WHERE id_licitacao = lic.id LIMIT 1) AS edital
FROM 
	gelic_licitacoes AS lic
WHERE 
	lic.final = 0 AND 
    lic.fase = 3 AND 
    lic.datahora_limite <= '$now'");
while ($db->nextRecord())
{
	$dId_licitacao = $db->f("id");

	if (strlen($db->f("edital")) > 0)
	{
		//verificar se existe alguma APL
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_apl WHERE id_licitacao = $dId_licitacao",1);
		$db->nextRecord(1);
		if ($db->f("total",1) == 0)
		{
			if ($db->f("aumentar_limite") > 0)
			{
				//datahora_limite igual a datahora_abertura e liberar aprovacao
				$db->query("UPDATE gelic_licitacoes SET datahora_limite = datahora_abertura, aprovar_apl = 1 WHERE id = $dId_licitacao",1);
			}
			else
			{
				//parar e nao liberar aprovacao pois nao tem nenhuma apl
				$db->query("UPDATE gelic_licitacoes SET final = 1, aprovar_apl = 0 WHERE id = $dId_licitacao",1);

				//anotar final no historico
				$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, 0, 0, 14, 3, 0, '$now', '', '', '')",1);
			}
		}
		else
		{
			//parar
			$db->query("UPDATE gelic_licitacoes SET final = 1, aprovar_apl = 1 WHERE id = $dId_licitacao",1);


			//STATUS E ABAS ANTES DA ALTERACAO
			$aLic_aba_status = array("fr"=>array(),"to"=>array());
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			//para os que enviaram APL mudar o status para "Aguardando Aprovação APL" somente se o status for "Aguardando prazo de envio da APL (30)"
			$db->query("UPDATE gelic_licitacoes_abas SET id_status = 3 WHERE id_licitacao = $dId_licitacao AND grupo IN (1,2,3) AND status_fixo = 0 AND id_status = 30",1);

			//STATUS E ABAS DEPOIS DA ALTERACAO
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo",1);
			while ($db->nextRecord(1))
				$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo",1), "aba"=>$db->f("id_aba",1), "status"=>$db->f("id_status",1), "fixo"=>$db->f("status_fixo",1));

			//anotar final no historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, 0, 0, 14, 3, 0, '$now', '".json_encode($aLic_aba_status)."', '', '')",1);


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
	cid.uf AS uf
FROM 
	gelic_licitacoes AS lic,
	gelic_modalidades AS mdl,
	gelic_cidades AS cid
WHERE
	lic.id = $dId_licitacao AND
	lic.deletado = 0 AND
	lic.id_modalidade = mdl.id AND
	lic.id_cidade = cid.id",1);
			$db->nextRecord(1);

			$dId_licitacao = $db->f("id",1);
			$dOrgao = $db->f("orgao",1);
			$dObjeto = $db->f("objeto",1);
			$dImportante = $db->f("importante",1);
			$dValor = $db->f("valor",1);
			$dDatahora_abertura = $db->f("datahora_abertura",1);
			$dDatahora_entrega = $db->f("datahora_entrega",1);
			$dDatahora_limite = $db->f("datahora_limite",1);
			$dNome_modalidade = $db->f("nome_modalidade",1);
			$dNome_cidade = $db->f("nome_cidade",1);
			$dUf = $db->f("uf",1);

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
			$tEmail_assunto = 'GELIC - Término do Prazo Limite LIC '.$dId_licitacao;
			$tEmail_mensagem = 'O prazo limite para envio de APLs expirou. As APLs podem ser aprovadas/reprovadas para esta licitação.<br><br>
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
'.rodapeEmail();
			$tTexto_sms = "GELIC - Termino do Prazo Limite LIC $dId_licitacao (".utf8_encode($dNome_cidade)." - $dUf)";

			// Notificar BOs
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
	cli.tipo = 1 AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0",1);
			while ($db->nextRecord(1))
			{
				$dNt_email = json_decode($db->f("nt_email",1), true);
				$dNt_sms = json_decode($db->f("nt_celular",1), true);

				if (in_array("F", str_split($dNt_email["ntf"])) && in_region("F", $dUf, $dNt_email["reg"]))
					queueMessage(22, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_EMAIL, SYS_BOF, $db->f("id",1), $db->f("email",1), $tEmail_assunto, $tEmail_mensagem, '', '');

				if (in_array("F", str_split($dNt_sms["ntf"])) && strlen($db->f("celular",1)) > 0 && in_region("F", $dUf, $dNt_sms["reg"]))
					queueMessage(22, 0, basename(__FILE__).' ('.__LINE__.')', 0, M_SMS, SYS_BOF, $db->f("id",1), $db->f("celular",1), '', $tTexto_sms, '', '');
			}
			//-----------------------------------------
			//-----------------------------------------
			//-----------------------------------------
		}
	}
	else
	{
		// SEM EDITAL (nao avanca de fase)
		// Somente adiciona mais 24 horas de limite a partir de agora
		$t_24horas = time();
		$pPointer = date("Ymdw", $t_24horas);
		$pDia_util = 0;
		while ($pDia_util == 0)
		{
			$t_24horas += 86400;
			$pPointer = date("Ymdw", $t_24horas);
			if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
				$pDia_util = 1;
		}
		$nova_datahora_limite = date("Y-m-d H:i:s", $t_24horas);

		$db->query("UPDATE gelic_licitacoes SET datahora_limite = '$nova_datahora_limite' WHERE id = $dId_licitacao",1);
	}
}

$db->selectDB("gelic_gelic");
$db->query("UPDATE gelic_auto SET script_out = NOW() WHERE id = ".THIS_ID);
echo "Sucesso!";

?>
