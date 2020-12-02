<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$pFormato = trim($_POST["formato"]);
	$pPeriodo_fr = $_POST["periodo-fr"];
	$pPeriodo_to = $_POST["periodo-to"];

	$db = new Mysql();

	//VALIDAR DATA PERIODO DE
	if (strlen($pPeriodo_fr) == 10 && isValidBrDate($pPeriodo_fr))
		$pPeriodo_fr = brToUs($pPeriodo_fr); // mm/dd/yyyy
	else
	{
		$db->query("SELECT DATE(datahora_abertura) AS data_abertura FROM gelic_licitacoes WHERE deletado = 0 ORDER BY datahora_abertura LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_fr = mysqlToUs($db->f("data_abertura"));
		else
			$pPeriodo_fr = date("m/d/Y");
	}


	//VALIDAR DATA PERIODO ATE
	if (strlen($pPeriodo_to) == 10 && isValidBrDate($pPeriodo_to))
		$pPeriodo_to = brToUs($pPeriodo_to); // mm/dd/yyyy
	else
	{
		$db->query("SELECT DATE(datahora_abertura) AS data_abertura FROM gelic_licitacoes WHERE deletado = 0 ORDER BY datahora_abertura DESC LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_to = mysqlToUs($db->f("data_abertura"));
		else
			$pPeriodo_to = date("m/d/Y");
	}


	//SE O PERIODO DE FOR MAIOR DO QUE O PERIODO ATE (INVERTER)
	if (intval(str_replace("-","",usToMysql($pPeriodo_fr))) > intval(str_replace("-","",usToMysql($pPeriodo_to))))
	{
		$t = $pPeriodo_to;
		$pPeriodo_to = $pPeriodo_fr;
		$pPeriodo_fr = $t;
	}



	if ($pFormato == "xlsx")
	{
		$aResult = array();
		$aResult[0] = array("n"=>"Licitações (todas)","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[1] = array("n"=>"Licitações com registro de vitória","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[2] = array("n"=>"Licitações com registro de derrota","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[3] = array("n"=>"Licitações com APL enviada e com aprovação da fábrica sem registro de resultado","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[4] = array("n"=>"Licitações com modelo VW compatível e sem envio de APL","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[5] = array("n"=>"Licitações SEM modelo VW compatível","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[6] = array("n"=>"Licitações com status 'Retirar edital presencialmente' ou 'Aviso de Publicação - Aguardando Edital'","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[7] = array("n"=>"Total de licitações participadas","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult[8] = array("n"=>"Licitações potenciais","total_lic"=>0,"lic"=>array(),"total_vei"=>0);

		$aResult_m = array();
		$aResult_m[0] = array("n"=>"Licitações (todas)","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[1] = array("n"=>"Licitações com registro de vitória","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[2] = array("n"=>"Licitações com registro de derrota","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[3] = array("n"=>"Licitações com APL enviada e com aprovação da fábrica sem registro de resultado","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[4] = array("n"=>"Licitações com modelo VW compatível e sem envio de APL","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[5] = array("n"=>"Licitações SEM modelo VW compatível","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[6] = array("n"=>"Licitações com status 'Retirar edital presencialmente' ou 'Aviso de Publicação - Aguardando Edital'","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[7] = array("n"=>"Total de licitações participadas","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_m[8] = array("n"=>"Licitações potenciais","total_lic"=>0,"lic"=>array(),"total_vei"=>0);

		$aResult_e = array();
		$aResult_e[0] = array("n"=>"Licitações (todas)","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[1] = array("n"=>"Licitações com registro de vitória","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[2] = array("n"=>"Licitações com registro de derrota","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[3] = array("n"=>"Licitações com APL enviada e com aprovação da fábrica sem registro de resultado","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[4] = array("n"=>"Licitações com modelo VW compatível e sem envio de APL","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[5] = array("n"=>"Licitações SEM modelo VW compatível","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[6] = array("n"=>"Licitações com status 'Retirar edital presencialmente' ou 'Aviso de Publicação - Aguardando Edital'","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[7] = array("n"=>"Total de licitações participadas","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_e[8] = array("n"=>"Licitações potenciais","total_lic"=>0,"lic"=>array(),"total_vei"=>0);

		$aResult_f = array();
		$aResult_f[0] = array("n"=>"Licitações (todas)","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[1] = array("n"=>"Licitações com registro de vitória","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[2] = array("n"=>"Licitações com registro de derrota","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[3] = array("n"=>"Licitações com APL enviada e com aprovação da fábrica sem registro de resultado","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[4] = array("n"=>"Licitações com modelo VW compatível e sem envio de APL","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[5] = array("n"=>"Licitações SEM modelo VW compatível","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[6] = array("n"=>"Licitações com status 'Retirar edital presencialmente' ou 'Aviso de Publicação - Aguardando Edital'","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[7] = array("n"=>"Total de licitações participadas","total_lic"=>0,"lic"=>array(),"total_vei"=>0);
		$aResult_f[8] = array("n"=>"Licitações potenciais","total_lic"=>0,"lic"=>array(),"total_vei"=>0);



		//==========================================================================
		// Licitações com registro de vitória
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult[1]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult[1]["lic"][] = $db->f("id");

		if (count($aResult[1]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult[1]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}

			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult[1]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com registro de derrota
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (20,21,35,46,59)
GROUP BY
	lic.id");
		$aResult[2]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult[2]["lic"][] = $db->f("id");

		if (count($aResult[2]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_licitacao IN (".implode(",",$aResult[2]["lic"]).")");
			$db->nextRecord();
			$aResult[2]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com APL enviada e com aprovação da fábrica sem registro de resultado
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
	INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.aprovadas > 0
WHERE
	lic.deletado = 0 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status NOT IN (20,21,35,46,59,25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult[3]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult[3]["lic"][] = $db->f("id");


		if (count($aResult[3]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult[3]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}


			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult[3]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com modelo VW compatível e sem envio de APL
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	lic.id NOT IN (SELECT id_licitacao FROM gelic_licitacoes_apl_ear) AND
	itm.id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17)
GROUP BY
	lic.id");
		$aResult[4]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult[4]["lic"][] = $db->f("id");

		if (count($aResult[4]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17) AND
	id_licitacao IN (".implode(",",$aResult[4]["lic"]).") AND
	id NOT IN (SELECT id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult[4]["lic"])."))");
			$db->nextRecord();
			$aResult[4]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações SEM modelo VW compatível
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	itm.id_modelo IN (0,18,19)
GROUP BY
	lic.id");
		$aResult[5]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult[5]["lic"][] = $db->f("id");

		if (count($aResult[5]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult[5]["lic"]).")");
			$db->nextRecord();
			$aResult[5]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com status “Retirar edital presencialmente” ou “Aviso de Publicação - Aguardando Edital”
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (10,55)
GROUP BY
	lic.id");
		$aResult[6]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult[6]["lic"][] = $db->f("id");

		/*if (count($aResult[6]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult[6]["lic"]).")");
			$db->nextRecord();
			$aResult[6]["total_vei"] = $db->f("total");
		}*/
		$aResult[6]["total_vei"] = $aResult[6]["total_lic"];
		//==========================================================================



		//==========================================================================
		// Total de licitações participadas
		//==========================================================================
		$aResult[7]["total_lic"] = $aResult[1]["total_lic"] + $aResult[2]["total_lic"] + $aResult[3]["total_lic"];
		$aResult[7]["total_vei"] = $aResult[1]["total_vei"] + $aResult[2]["total_vei"] + $aResult[3]["total_vei"];
		array_merge($aResult[7]["lic"], $aResult[1]["lic"], $aResult[2]["lic"], $aResult[3]["lic"]);



		//==========================================================================
		// Licitações potenciais
		//==========================================================================
		$aResult[8]["total_lic"] = $aResult[4]["total_lic"] + $aResult[7]["total_lic"];
		$aResult[8]["total_vei"] = $aResult[4]["total_vei"] + $aResult[7]["total_vei"];
		array_merge($aResult[8]["lic"], $aResult[4]["lic"], $aResult[7]["lic"]);



		//==========================================================================
		// Licitações (todas)
		//==========================================================================
		$aResult[0]["total_lic"] = $aResult[1]["total_lic"] + $aResult[2]["total_lic"] + $aResult[3]["total_lic"] + $aResult[4]["total_lic"] + $aResult[5]["total_lic"] + $aResult[6]["total_lic"];
		$aResult[0]["total_vei"] = $aResult[1]["total_vei"] + $aResult[2]["total_vei"] + $aResult[3]["total_vei"] + $aResult[4]["total_vei"] + $aResult[5]["total_vei"] + $aResult[6]["total_vei"];
		array_merge($aResult[0]["lic"], $aResult[1]["lic"], $aResult[2]["lic"], $aResult[3]["lic"], $aResult[4]["lic"], $aResult[5]["lic"], $aResult[6]["lic"]);




//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************




		//==========================================================================
		// Licitações com registro de vitória
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult_m[1]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_m[1]["lic"][] = $db->f("id");

		if (count($aResult_m[1]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_m[1]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}

			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult_m[1]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com registro de derrota
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (20,21,35,46,59)
GROUP BY
	lic.id");
		$aResult_m[2]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_m[2]["lic"][] = $db->f("id");

		if (count($aResult_m[2]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_licitacao IN (".implode(",",$aResult_m[2]["lic"]).")");
			$db->nextRecord();
			$aResult_m[2]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com APL enviada e com aprovação da fábrica sem registro de resultado
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
	INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.aprovadas > 0
WHERE
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status NOT IN (20,21,35,46,59,25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult_m[3]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_m[3]["lic"][] = $db->f("id");


		if (count($aResult_m[3]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_m[3]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}


			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult_m[3]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com modelo VW compatível e sem envio de APL
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	lic.id NOT IN (SELECT id_licitacao FROM gelic_licitacoes_apl_ear) AND
	itm.id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17)
GROUP BY
	lic.id");
		$aResult_m[4]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_m[4]["lic"][] = $db->f("id");

		if (count($aResult_m[4]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17) AND
	id_licitacao IN (".implode(",",$aResult_m[4]["lic"]).") AND
	id NOT IN (SELECT id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_m[4]["lic"])."))");
			$db->nextRecord();
			$aResult_m[4]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações SEM modelo VW compatível
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	itm.id_modelo IN (0,18,19)
GROUP BY
	lic.id");
		$aResult_m[5]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_m[5]["lic"][] = $db->f("id");

		if (count($aResult_m[5]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult_m[5]["lic"]).")");
			$db->nextRecord();
			$aResult_m[5]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com status “Retirar edital presencialmente” ou “Aviso de Publicação - Aguardando Edital”
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (10,55)
GROUP BY
	lic.id");
		$aResult_m[6]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_m[6]["lic"][] = $db->f("id");

		/*if (count($aResult_m[6]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult_m[6]["lic"]).")");
			$db->nextRecord();
			$aResult_m[6]["total_vei"] = $db->f("total");
		}*/
		$aResult_m[6]["total_vei"] = $aResult_m[6]["total_lic"];
		//==========================================================================



		//==========================================================================
		// Total de licitações participadas
		//==========================================================================
		$aResult_m[7]["total_lic"] = $aResult_m[1]["total_lic"] + $aResult_m[2]["total_lic"] + $aResult_m[3]["total_lic"];
		$aResult_m[7]["total_vei"] = $aResult_m[1]["total_vei"] + $aResult_m[2]["total_vei"] + $aResult_m[3]["total_vei"];
		array_merge($aResult_m[7]["lic"], $aResult_m[1]["lic"], $aResult_m[2]["lic"], $aResult_m[3]["lic"]);



		//==========================================================================
		// Licitações potenciais
		//==========================================================================
		$aResult_m[8]["total_lic"] = $aResult_m[4]["total_lic"] + $aResult_m[7]["total_lic"];
		$aResult_m[8]["total_vei"] = $aResult_m[4]["total_vei"] + $aResult_m[7]["total_vei"];
		array_merge($aResult_m[8]["lic"], $aResult_m[4]["lic"], $aResult_m[7]["lic"]);



		//==========================================================================
		// Licitações (todas)
		//==========================================================================
		$aResult_m[0]["total_lic"] = $aResult_m[1]["total_lic"] + $aResult_m[2]["total_lic"] + $aResult_m[3]["total_lic"] + $aResult_m[4]["total_lic"] + $aResult_m[5]["total_lic"] + $aResult_m[6]["total_lic"];
		$aResult_m[0]["total_vei"] = $aResult_m[1]["total_vei"] + $aResult_m[2]["total_vei"] + $aResult_m[3]["total_vei"] + $aResult_m[4]["total_vei"] + $aResult_m[5]["total_vei"] + $aResult_m[6]["total_vei"];
		array_merge($aResult_m[0]["lic"], $aResult_m[1]["lic"], $aResult_m[2]["lic"], $aResult_m[3]["lic"], $aResult_m[4]["lic"], $aResult_m[5]["lic"], $aResult_m[6]["lic"]);




//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************




		//==========================================================================
		// Licitações com registro de vitória
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 2 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult_e[1]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_e[1]["lic"][] = $db->f("id");

		if (count($aResult_e[1]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_e[1]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}

			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult_e[1]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com registro de derrota
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 2 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (20,21,35,46,59)
GROUP BY
	lic.id");
		$aResult_e[2]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_e[2]["lic"][] = $db->f("id");

		if (count($aResult_e[2]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_licitacao IN (".implode(",",$aResult_e[2]["lic"]).")");
			$db->nextRecord();
			$aResult_e[2]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com APL enviada e com aprovação da fábrica sem registro de resultado
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
	INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.aprovadas > 0
WHERE
	lic.deletado = 0 AND
	lic.instancia = 2 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status NOT IN (20,21,35,46,59,25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult_e[3]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_e[3]["lic"][] = $db->f("id");


		if (count($aResult_e[3]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_e[3]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}


			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult_e[3]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com modelo VW compatível e sem envio de APL
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 2 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	lic.id NOT IN (SELECT id_licitacao FROM gelic_licitacoes_apl_ear) AND
	itm.id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17)
GROUP BY
	lic.id");
		$aResult_e[4]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_e[4]["lic"][] = $db->f("id");

		if (count($aResult_e[4]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17) AND
	id_licitacao IN (".implode(",",$aResult_e[4]["lic"]).") AND
	id NOT IN (SELECT id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_e[4]["lic"])."))");
			$db->nextRecord();
			$aResult_e[4]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações SEM modelo VW compatível
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 2 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	itm.id_modelo IN (0,18,19)
GROUP BY
	lic.id");
		$aResult_e[5]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_e[5]["lic"][] = $db->f("id");

		if (count($aResult_e[5]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult_e[5]["lic"]).")");
			$db->nextRecord();
			$aResult_e[5]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com status “Retirar edital presencialmente” ou “Aviso de Publicação - Aguardando Edital”
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 2 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (10,55)
GROUP BY
	lic.id");
		$aResult_e[6]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_e[6]["lic"][] = $db->f("id");

		/*if (count($aResult_e[6]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult_e[6]["lic"]).")");
			$db->nextRecord();
			$aResult_e[6]["total_vei"] = $db->f("total");
		}*/
		$aResult_e[6]["total_vei"] = $aResult_e[6]["total_lic"];
		//==========================================================================



		//==========================================================================
		// Total de licitações participadas
		//==========================================================================
		$aResult_e[7]["total_lic"] = $aResult_e[1]["total_lic"] + $aResult_e[2]["total_lic"] + $aResult_e[3]["total_lic"];
		$aResult_e[7]["total_vei"] = $aResult_e[1]["total_vei"] + $aResult_e[2]["total_vei"] + $aResult_e[3]["total_vei"];
		array_merge($aResult_e[7]["lic"], $aResult_e[1]["lic"], $aResult_e[2]["lic"], $aResult_e[3]["lic"]);



		//==========================================================================
		// Licitações potenciais
		//==========================================================================
		$aResult_e[8]["total_lic"] = $aResult_e[4]["total_lic"] + $aResult_e[7]["total_lic"];
		$aResult_e[8]["total_vei"] = $aResult_e[4]["total_vei"] + $aResult_e[7]["total_vei"];
		array_merge($aResult_e[8]["lic"], $aResult_e[4]["lic"], $aResult_e[7]["lic"]);



		//==========================================================================
		// Licitações (todas)
		//==========================================================================
		$aResult_e[0]["total_lic"] = $aResult_e[1]["total_lic"] + $aResult_e[2]["total_lic"] + $aResult_e[3]["total_lic"] + $aResult_e[4]["total_lic"] + $aResult_e[5]["total_lic"] + $aResult_e[6]["total_lic"];
		$aResult_e[0]["total_vei"] = $aResult_e[1]["total_vei"] + $aResult_e[2]["total_vei"] + $aResult_e[3]["total_vei"] + $aResult_e[4]["total_vei"] + $aResult_e[5]["total_vei"] + $aResult_e[6]["total_vei"];
		array_merge($aResult_e[0]["lic"], $aResult_e[1]["lic"], $aResult_e[2]["lic"], $aResult_e[3]["lic"], $aResult_e[4]["lic"], $aResult_e[5]["lic"], $aResult_e[6]["lic"]);




//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************
//***********************************************************************************************************************************************************




		//==========================================================================
		// Licitações com registro de vitória
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 3 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult_f[1]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_f[1]["lic"][] = $db->f("id");

		if (count($aResult_f[1]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_f[1]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}

			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult_f[1]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com registro de derrota
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 3 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (20,21,35,46,59)
GROUP BY
	lic.id");
		$aResult_f[2]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_f[2]["lic"][] = $db->f("id");

		if (count($aResult_f[2]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_licitacao IN (".implode(",",$aResult_f[2]["lic"]).")");
			$db->nextRecord();
			$aResult_f[2]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com APL enviada e com aprovação da fábrica sem registro de resultado
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
	INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.aprovadas > 0
WHERE
	lic.deletado = 0 AND
	lic.instancia = 3 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status NOT IN (20,21,35,46,59,25,38,39,40,43,47)
GROUP BY
	lic.id");
		$aResult_f[3]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_f[3]["lic"][] = $db->f("id");


		if (count($aResult_f[3]["lic"]) > 0)
		{
			$aItens = array();
			$db->query("SELECT id, id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_f[3]["lic"]).")");
			while ($db->nextRecord())
			{
				$dId_apl = $db->f("id");
				$dId_item = $db->f("id_item");

				$db->query("
SELECT
	tipo
FROM
	gelic_licitacoes_apl_historico
WHERE
	id_apl = $dId_apl
ORDER BY
	id DESC
LIMIT 1",1);
				$db->nextRecord(1);
				if ($db->f("tipo",1) == 2 && !in_array($dId_item, $aItens))
					$aItens[] = $dId_item;
			}


			if (count($aItens) > 0)
			{
				$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id IN (".implode(",",$aItens).")");
				$db->nextRecord();
				$aResult_f[3]["total_vei"] = $db->f("total");
			}
		}
		//==========================================================================



		//==========================================================================
		// Licitações com modelo VW compatível e sem envio de APL
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 3 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	lic.id NOT IN (SELECT id_licitacao FROM gelic_licitacoes_apl_ear) AND
	itm.id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17)
GROUP BY
	lic.id");
		$aResult_f[4]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_f[4]["lic"][] = $db->f("id");

		if (count($aResult_f[4]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17) AND
	id_licitacao IN (".implode(",",$aResult_f[4]["lic"]).") AND
	id NOT IN (SELECT id_item FROM gelic_licitacoes_apl WHERE id_licitacao IN (".implode(",",$aResult_f[4]["lic"])."))");
			$db->nextRecord();
			$aResult_f[4]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações SEM modelo VW compatível
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 3 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	itm.id_modelo IN (0,18,19)
GROUP BY
	lic.id");
		$aResult_f[5]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_f[5]["lic"][] = $db->f("id");

		if (count($aResult_f[5]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult_f[5]["lic"]).")");
			$db->nextRecord();
			$aResult_f[5]["total_vei"] = $db->f("total");
		}
		//==========================================================================



		//==========================================================================
		// Licitações com status “Retirar edital presencialmente” ou “Aviso de Publicação - Aguardando Edital”
		//==========================================================================
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS abas ON abas.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 3 AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	abas.grupo = 1 AND
	abas.id_status IN (10,55)
GROUP BY
	lic.id");
		$aResult_f[6]["total_lic"] = $db->nf();
		while ($db->nextRecord())
			$aResult_f[6]["lic"][] = $db->f("id");

		/*if (count($aResult_f[6]["lic"]) > 0)
		{
			$db->query("
SELECT
	SUM(IF(quantidade = 0, 1, quantidade)) AS total
FROM
	gelic_licitacoes_itens
WHERE
	id_modelo IN (0,18,19) AND
	id_licitacao IN (".implode(",",$aResult_f[6]["lic"]).")");
			$db->nextRecord();
			$aResult_f[6]["total_vei"] = $db->f("total");
		}*/
		$aResult_f[6]["total_vei"] = $aResult_f[6]["total_lic"];
		//==========================================================================



		//==========================================================================
		// Total de licitações participadas
		//==========================================================================
		$aResult_f[7]["total_lic"] = $aResult_f[1]["total_lic"] + $aResult_f[2]["total_lic"] + $aResult_f[3]["total_lic"];
		$aResult_f[7]["total_vei"] = $aResult_f[1]["total_vei"] + $aResult_f[2]["total_vei"] + $aResult_f[3]["total_vei"];
		array_merge($aResult_f[7]["lic"], $aResult_f[1]["lic"], $aResult_f[2]["lic"], $aResult_f[3]["lic"]);



		//==========================================================================
		// Licitações potenciais
		//==========================================================================
		$aResult_f[8]["total_lic"] = $aResult_f[4]["total_lic"] + $aResult_f[7]["total_lic"];
		$aResult_f[8]["total_vei"] = $aResult_f[4]["total_vei"] + $aResult_f[7]["total_vei"];
		array_merge($aResult_f[8]["lic"], $aResult_f[4]["lic"], $aResult_f[7]["lic"]);



		//==========================================================================
		// Licitações (todas)
		//==========================================================================
		$aResult_f[0]["total_lic"] = $aResult_f[1]["total_lic"] + $aResult_f[2]["total_lic"] + $aResult_f[3]["total_lic"] + $aResult_f[4]["total_lic"] + $aResult_f[5]["total_lic"] + $aResult_f[6]["total_lic"];
		$aResult_f[0]["total_vei"] = $aResult_f[1]["total_vei"] + $aResult_f[2]["total_vei"] + $aResult_f[3]["total_vei"] + $aResult_f[4]["total_vei"] + $aResult_f[5]["total_vei"] + $aResult_f[6]["total_vei"];
		array_merge($aResult_f[0]["lic"], $aResult_f[1]["lic"], $aResult_f[2]["lic"], $aResult_f[3]["lic"], $aResult_f[4]["lic"], $aResult_f[5]["lic"], $aResult_f[6]["lic"]);


		require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Licitações e Veículos")
			->setSubject("licitacoes")
			->setDescription("Licitações e Veículos GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Resultados");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A1", 'Período escolhido ('.usToBr($pPeriodo_fr).' - '.usToBr($pPeriodo_to).')');

		$row = 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Descrição')
			->setCellValue("B$row", 'Total Licitações')
			->setCellValue("C$row", 'Total Veículos');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[0]["n"])
			->setCellValue("B$row", $aResult[0]["total_lic"])
			->setCellValue("C$row", $aResult[0]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[1]["n"])
			->setCellValue("B$row", $aResult[1]["total_lic"])
			->setCellValue("C$row", $aResult[1]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[2]["n"])
			->setCellValue("B$row", $aResult[2]["total_lic"])
			->setCellValue("C$row", $aResult[2]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[3]["n"])
			->setCellValue("B$row", $aResult[3]["total_lic"])
			->setCellValue("C$row", $aResult[3]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[7]["n"])
			->setCellValue("B$row", $aResult[7]["total_lic"])
			->setCellValue("C$row", $aResult[7]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[4]["n"])
			->setCellValue("B$row", $aResult[4]["total_lic"])
			->setCellValue("C$row", $aResult[4]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[8]["n"])
			->setCellValue("B$row", $aResult[8]["total_lic"])
			->setCellValue("C$row", $aResult[8]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[5]["n"])
			->setCellValue("B$row", $aResult[5]["total_lic"])
			->setCellValue("C$row", $aResult[5]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult[6]["n"])
			->setCellValue("B$row", $aResult[6]["total_lic"])
			->setCellValue("C$row", $aResult[6]["total_vei"]);



		$row += 3;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'MUNICIPAL')
			->setCellValue("B$row", 'Total Licitações')
			->setCellValue("C$row", 'Total Veículos');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[0]["n"])
			->setCellValue("B$row", $aResult_m[0]["total_lic"])
			->setCellValue("C$row", $aResult_m[0]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[1]["n"])
			->setCellValue("B$row", $aResult_m[1]["total_lic"])
			->setCellValue("C$row", $aResult_m[1]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[2]["n"])
			->setCellValue("B$row", $aResult_m[2]["total_lic"])
			->setCellValue("C$row", $aResult_m[2]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[3]["n"])
			->setCellValue("B$row", $aResult_m[3]["total_lic"])
			->setCellValue("C$row", $aResult_m[3]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[7]["n"])
			->setCellValue("B$row", $aResult_m[7]["total_lic"])
			->setCellValue("C$row", $aResult_m[7]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[4]["n"])
			->setCellValue("B$row", $aResult_m[4]["total_lic"])
			->setCellValue("C$row", $aResult_m[4]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[8]["n"])
			->setCellValue("B$row", $aResult_m[8]["total_lic"])
			->setCellValue("C$row", $aResult_m[8]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[5]["n"])
			->setCellValue("B$row", $aResult_m[5]["total_lic"])
			->setCellValue("C$row", $aResult_m[5]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_m[6]["n"])
			->setCellValue("B$row", $aResult_m[6]["total_lic"])
			->setCellValue("C$row", $aResult_m[6]["total_vei"]);




		$row += 3;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'ESTADUAL')
			->setCellValue("B$row", 'Total Licitações')
			->setCellValue("C$row", 'Total Veículos');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[0]["n"])
			->setCellValue("B$row", $aResult_e[0]["total_lic"])
			->setCellValue("C$row", $aResult_e[0]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[1]["n"])
			->setCellValue("B$row", $aResult_e[1]["total_lic"])
			->setCellValue("C$row", $aResult_e[1]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[2]["n"])
			->setCellValue("B$row", $aResult_e[2]["total_lic"])
			->setCellValue("C$row", $aResult_e[2]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[3]["n"])
			->setCellValue("B$row", $aResult_e[3]["total_lic"])
			->setCellValue("C$row", $aResult_e[3]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[7]["n"])
			->setCellValue("B$row", $aResult_e[7]["total_lic"])
			->setCellValue("C$row", $aResult_e[7]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[4]["n"])
			->setCellValue("B$row", $aResult_e[4]["total_lic"])
			->setCellValue("C$row", $aResult_e[4]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[8]["n"])
			->setCellValue("B$row", $aResult_e[8]["total_lic"])
			->setCellValue("C$row", $aResult_e[8]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[5]["n"])
			->setCellValue("B$row", $aResult_e[5]["total_lic"])
			->setCellValue("C$row", $aResult_e[5]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_e[6]["n"])
			->setCellValue("B$row", $aResult_e[6]["total_lic"])
			->setCellValue("C$row", $aResult_e[6]["total_vei"]);




		$row += 3;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'FEDERAL')
			->setCellValue("B$row", 'Total Licitações')
			->setCellValue("C$row", 'Total Veículos');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[0]["n"])
			->setCellValue("B$row", $aResult_f[0]["total_lic"])
			->setCellValue("C$row", $aResult_f[0]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[1]["n"])
			->setCellValue("B$row", $aResult_f[1]["total_lic"])
			->setCellValue("C$row", $aResult_f[1]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[2]["n"])
			->setCellValue("B$row", $aResult_f[2]["total_lic"])
			->setCellValue("C$row", $aResult_f[2]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[3]["n"])
			->setCellValue("B$row", $aResult_f[3]["total_lic"])
			->setCellValue("C$row", $aResult_f[3]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[7]["n"])
			->setCellValue("B$row", $aResult_f[7]["total_lic"])
			->setCellValue("C$row", $aResult_f[7]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[4]["n"])
			->setCellValue("B$row", $aResult_f[4]["total_lic"])
			->setCellValue("C$row", $aResult_f[4]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[8]["n"])
			->setCellValue("B$row", $aResult_f[8]["total_lic"])
			->setCellValue("C$row", $aResult_f[8]["total_vei"]);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[5]["n"])
			->setCellValue("B$row", $aResult_f[5]["total_lic"])
			->setCellValue("C$row", $aResult_f[5]["total_vei"]);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $aResult_f[6]["n"])
			->setCellValue("B$row", $aResult_f[6]["total_lic"])
			->setCellValue("C$row", $aResult_f[6]["total_vei"]);



		$phpexcel->getActiveSheet()->setTitle('Licitações e Veículos');
		$phpexcel->getActiveSheet()->mergeCells("A1:C1");
		$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(90);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$phpexcel->getActiveSheet()->getStyle("A2:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("B2:C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		if (file_exists(UPLOAD_DIR."~licitacoes_veiculos_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~licitacoes_veiculos_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~licitacoes_veiculos_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
