<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1) //BO
	{
		$pFormato = trim($_POST["formato"]);
		$pPeriodo_fr = $_POST["periodo-fr"];
		$pPeriodo_to = $_POST["periodo-to"];
		$pTv = $_POST["tv"];
		$pLang = $_POST["lang"];

		$aLang = array();

		//English
		$aLang["en"]["titulo"] = "Auctions & Vehicles";
		$aLang["en"]["lic"] = "Auctions";
		$aLang["en"]["veh"] = "Vehicles";
		$aLang["en"]["bar1"] = "Auctions";
		$aLang["en"]["bar2"] = "Auction Notice (not taken)";
		$aLang["en"]["bar3"] = "Total Auction Potential";
		$aLang["en"]["bar4"] = "Not Attended";
		$aLang["en"]["bar5"] = "Attended";
		$aLang["en"]["bar6"] = "On going";
		$aLang["en"]["bar7"] = "Lost";
		$aLang["en"]["bar8"] = "Won";
		$aLang["en"]["sub1"] = "Authorization request not sent";
		$aLang["en"]["sub2"] = "Approved without participation";
		$aLang["en"]["sub3"] = "Declined authorization";
		$aLang["en"]["sub4"] = "No reply";
		$aLang["en"]["periodo"] = "Reporting period";
		$aLang["en"]["ate"] = "to";
		$aLang["en"]["mes"] = array("January","February","March","April","May","June","July","August","September","October","November","December");

		//Portugues
		$aLang["pt"]["titulo"] = "Licitações & Veículos";
		$aLang["pt"]["lic"] = "Licitações";
		$aLang["pt"]["veh"] = "Veículos";
		$aLang["pt"]["bar1"] = "Licitações";
		$aLang["pt"]["bar2"] = "Editais não retirados";
		$aLang["pt"]["bar3"] = "Licitações Potenciais";
		$aLang["pt"]["bar4"] = "Não Participadas";
		$aLang["pt"]["bar5"] = "Participadas";
		$aLang["pt"]["bar6"] = "Em<br>andamento";
		$aLang["pt"]["bar7"] = "Derrota";
		$aLang["pt"]["bar8"] = "Vitória";
		$aLang["pt"]["sub1"] = "Pedido de autorização não enviado";
		$aLang["pt"]["sub2"] = "Aprovadas sem participação";
		$aLang["pt"]["sub3"] = "Autorização declinada";
		$aLang["pt"]["sub4"] = "Sem resposta";
		$aLang["pt"]["periodo"] = "Período do Relatório";
		$aLang["pt"]["ate"] = "até";
		$aLang["pt"]["mes"] = array("Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");

		$aLic = array();
		$aVeh = array();
	
		$db = new Mysql();

		//VALIDAR DATA PERIODO DE
		if (strlen($pPeriodo_fr) == 10 && isValidBrDate($pPeriodo_fr))
			$pPeriodo_fr = brToUs($pPeriodo_fr); // mm/dd/yyyy
		else
		{
			$db->query("SELECT data_hora FROM gelic_log_login ORDER BY id LIMIT 1");
			if ($db->nextRecord())
				$pPeriodo_fr = mysqlToUs($db->f("data_hora"));
			else
				$pPeriodo_fr = date("m/d/Y");
		}

		//VALIDAR DATA PERIODO ATE
		if (strlen($pPeriodo_to) == 10 && isValidBrDate($pPeriodo_to))
			$pPeriodo_to = brToUs($pPeriodo_to); // mm/dd/yyyy
		else
			$pPeriodo_to = date("m/d/Y");


		//SE O PERIODO DE FOR MAIOR DO QUE O PERIODO ATE (INVERTER)
		if (intval(str_replace("-","",usToMysql($pPeriodo_fr))) > intval(str_replace("-","",usToMysql($pPeriodo_to))))
		{
			$t = $pPeriodo_to;
			$pPeriodo_to = $pPeriodo_fr;
			$pPeriodo_fr = $t;
		}

		$data_selecionada_fr = usToBr($pPeriodo_fr);
		$data_selecionada_to = usToBr($pPeriodo_to);

		$data_inicio = usToMysql($pPeriodo_fr);
		$data_final = usToMysql($pPeriodo_to);
	



		//****************************
		//********* AUCTIONS *********
		//****************************

		//===========================
		// AUCTIONS
		//===========================
		// Deverá contar a quantidade de licitações no sistema VW.
		// Considerando apenas as licitações que tenham modelo 
		// Volkswagen compatível. (1: Hatch, 2: Sedan, 8: Station Wagon, 3: Picape)
		// Esta informação é retirada do sistema do campo tipo_veiculo.
		// Não contabilizar licitações com os status:
		//   => Licitação Cancelada (44)
		//   => Licitação Deserta (31)
		//   => Licitação Fracassada (45)
		//   => Licitação Revogada (48)
		//   => Licitação Suspensa (37)
		//   => Duplicada (51) adicionada
		//   => Demanda Jurídica - Recurso ou Contra razão (12) adicionada
		//===========================
		$db->query("SELECT COUNT(*) AS total FROM
(
	SELECT 
		lic.id 
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (44,31,45,48,37,51,12)
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59'
	GROUP BY 
		lic.id
) AS t");
		$db->nextRecord();
		$aLic[] = array("total"=>$db->f("total"), "label"=>$aLang[$pLang]["bar1"], "bgcolor"=>"4285f4", "color"=>"ffffff");



		//===========================
		// AUCTION NOTICE (NOT TAKEN)
		//===========================
		// Deverá contabilizar a quantidade de Licitações marcadas no sistema com os status:
		//   => Retirar edital presencialmente (10)
		//   => Aviso de Publicação – Aguardando Edital (55)
		//===========================
		$db->query("SELECT COUNT(*) AS total FROM
(
	SELECT 
		lic.id 
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status IN (10,55)
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59'
	GROUP BY 
		lic.id
) AS t");
		$db->nextRecord();
		$aLic[] = array("total"=>$db->f("total"), "label"=>$aLang[$pLang]["bar2"], "bgcolor"=>"b2b2b2", "color"=>"33434c");



		//===========================
		// TOTAL AUCTION POTENTIAL
		//===========================
		// Contagem da diferença entre a primeira barra e a segunda barra.
		// Total Auction Potential = Auctions - Auction Notice (Not Taken)
		//===========================
		$aLic[] = array("total"=>$aLic[0]["total"]-$aLic[1]["total"], "label"=>$aLang[$pLang]["bar3"], "bgcolor"=>"b2b2b2", "color"=>"33434c");



		//===========================
		// NOT ATTENDED
		//===========================
		// Contabilizar as Licitações sem participação, considerando:
		//   => APL não enviadas – Sem status definido
		//   => APL aprovada e sem participação – Status: Sem participação Volkswagen (36)
		//   => APL sem resposta – Status: Sem aprovação da fábrica (24)
		//   => APL Reprovada (19)
		//   => Impedimento de licitar - Apontamento CADIN (52)
		//   => Impedimento de licitar - Descritivo limitador (50)
		//   => Impedimento de licitar - Documento de habilitação irregular (56)
		//   => Impedimento de licitar - Exclusiva ME/EPP (41)
		//   => Impedimento de licitar - Índices Financeiros (49)
		//   => Impedimento de licitar - Sem cadastro no portal de compras (54)
		//   => Pendência de documento – BK (16)
		//   => Sem tempo hábil para envio da documentação (34)
		//   Excluir status (10,55,44,31,45,48,37,51,12) - Isso inclui somente as licitacoes da barra "auctions" e exclui as "auction notice - not taken")
		//===========================
		$db->query("SELECT COUNT(*) AS total FROM 
(
	SELECT 
		lic.id
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
        LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
		(
			(
				apl.id IS NULL
			)
			OR
			(
				aba.id_status IN (24,36,52,50,56,41,49,54,16,34)
	        )
	        OR
	        (
				apl.id IS NOT NULL AND
	            aplh.tipo = 4 AND 
                aba.id_aba <> 9
	        )
		) AND
		aba.id_status NOT IN (3,30,32,23,17,38,21,20,35,43,18,39,33,46,47,15,14,40,25)
	GROUP BY
		lic.id
) AS t");
		$db->nextRecord();
		$aLic[] = array("total"=>$db->f("total"), "label"=>$aLang[$pLang]["bar4"], "bgcolor"=>"5f5f5f", "color"=>"33434c");



		//===========================
		// ATTENDED
		//===========================
		// Deverá contabilizar a quantidade de Licitações com os seguintes status:
		//   => Aguardando Aprovação APL (3)
		//   => Aguardando prazo de envio da APL (30)
		//   => Aguardando ata da sessão (32)
		//   => Aguardando disputa (23)
		//   => APL Aprovada (8)
		//   => APL em Análise – GELIC (17)
		//   => Contrato assinado (38)
		//   => Derrota no randômico (21)
		//   => Derrota por preço (20)
		//   => Desclassificado / Inabilitado (35)
		//   => Empenho recebido (43)
		//   => Envelope enviado – rastreando (18)
		//   => Faturado (39)
		//   => Participação via estoque (33)
		//   => Participação via estoque – Ganha (46)
		//   => Participação via estoque – Perdida (47)
		//   => Pedido de Esclarecimento (15)
		//   => Preparação de Habilitação (14)
		//   => Veículo entregue (40)
		//   => Vitória (25)
		//   Excluir status (10,55,44,31,45,48,37,51,12) - Isso inclui somente as licitacoes da barra "auctions" e exclui as "auction notice - not taken")
		//===========================
		$db->query("SELECT COUNT(*) AS total FROM
(
	SELECT 
		lic.id 
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
        LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
		(
			(
				apl.id IS NOT NULL AND
				aplh.tipo = 2 AND
				aba.id_aba = 9
			)
		    OR
			(
				aba.id_status IN (3,30,32,23,17,38,21,20,35,43,18,39,33,46,47,15,14,40,25)
			)
        ) AND
		aba.id_status NOT IN (52,50,56,41,49,54,16,34,36,24)
	GROUP BY 
		lic.id
) AS t");
		$db->nextRecord();
		$aLic[] = array("total"=>$db->f("total"), "label"=>$aLang[$pLang]["bar5"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");



		//===========================
		// ON GOING
		//===========================
		// Deverá contabilizar a quantidade de Licitações com os seguintes status:
		//   => Aguardando Aprovação APL (3)
		//   => Aguardando prazo de envio da APL (30)
		//   => Aguardando ata da sessão (32)
		//   => Aguardando disputa (23)
		//   => APL Aprovada (8)
		//   => APL em Análise – GELIC (17)
		//   => Envelope enviado – rastreando (18)
		//   => Participação via estoque (33)
		//   => Pedido de Esclarecimento (15)
		//   => Preparação de Habilitação (14)
		//===========================
		$db->query("SELECT COUNT(*) AS total FROM
(
	SELECT 
		lic.id 
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
        LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
		(
			(
				apl.id IS NOT NULL AND
				aplh.tipo = 2 AND
				aba.id_aba = 9
			)
		    OR
			(
				aba.id_status IN (3,30,32,23,17,18,33,15,14)
			)
        ) AND
		aba.id_status NOT IN (52,50,56,41,49,54,16,34,36,24,21,20,35,47,38,43,39,46,40,25)
	GROUP BY 
		lic.id
) AS t");
		$db->nextRecord();
		$aLic[] = array("total"=>$db->f("total"), "label"=>$aLang[$pLang]["bar6"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");



		//===========================
		// LOST
		//===========================
		// Deverá contabilizar a quantidade de Licitações com os seguintes status:
		//   => Derrota no randômico (21)
		//   => Derrota por preço (20)
		//   => Desclassificado / Inabilitado (35)
		//   => Participação via estoque – Perdida (47)
		//===========================
		$db->query("SELECT COUNT(*) AS total FROM
(
	SELECT 
		lic.id 
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
		aba.id_status IN (21,20,35,47)
	GROUP BY 
		lic.id
) AS t");
		$db->nextRecord();
		$aLic[] = array("total"=>$db->f("total"), "label"=>$aLang[$pLang]["bar7"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");



		//===========================
		// WON
		//===========================
		// Deverá contabilizar a quantidade de Licitações com os seguintes status:
		//   => Contrato assinado (38)
		//   => Empenho recebido (43)
		//   => Faturado (39)
		//   => Participação via estoque – Ganha (46)
		//   => Veículo entregue (40)
		//   => Vitória (25)
		//===========================
		$db->query("SELECT COUNT(*) AS total FROM
(
	SELECT 
		lic.id 
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
		aba.id_status IN (38,43,39,46,40,25)
	GROUP BY 
		lic.id
) AS t");
		$db->nextRecord();
		$aLic[] = array("total"=>$db->f("total"), "label"=>$aLang[$pLang]["bar8"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");


	
		//===========================
		// Authorization request not sent
		// Approved without participation
		// Declined authorization
		// No reply
		//===========================
		$sub_valor = array(0,0,0,0);
		$db->query("
	SELECT 
		lic.id,
		apl.id AS id_apl,
		aba.id_status,
		aba.id_aba,
		aplh.tipo
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
        LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
		(
			(
				apl.id IS NULL
			)
			OR
			(
				aba.id_status IN (24,36,52,50,56,41,49,54,16,34)
	        )
	        OR
	        (
				apl.id IS NOT NULL AND
	            aplh.tipo = 4 AND 
                aba.id_aba <> 9
	        )
		) AND
		aba.id_status NOT IN (3,30,32,23,17,38,21,20,35,43,18,39,33,46,47,15,14,40,25)
	GROUP BY
		lic.id");
		while ($db->nextRecord())
		{
			if (strlen($db->f("id_apl")) == 0)
				$sub_valor[0] += 1;
			else if ($db->f("id_status") == 36)
				$sub_valor[1] += 1;
			else if ($db->f("tipo") == 4 && $db->f("id_aba") <> 9)
				$sub_valor[2] += 1;
			else
				$sub_valor[3] += 1;
		}






		//****************************
		//********* VEHICLES *********
		//****************************


		//===========================
		// AUCTIONS
		//===========================
		$db->query("SELECT 
	SUM(IF(quantidade = 0, 1, quantidade)) AS total 
FROM 
	gelic_licitacoes_itens
WHERE 
	id_licitacao IN (
		SELECT 
			lic.id 
		FROM 
			gelic_licitacoes AS lic 
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (44,31,45,48,37,51,12)
		WHERE 
			lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59'
		GROUP BY 
			lic.id
	) AND 
    id_tipo_veiculo IN ($pTv) AND acompanhamento = 0");
		$db->nextRecord();
		$aVeh[] = array("total"=>intval($db->f("total")), "label"=>$aLang[$pLang]["bar1"], "bgcolor"=>"4285f4", "color"=>"ffffff");



		//===========================
		// AUCTION NOTICE (NOT TAKEN)
		//===========================
		$db->query("SELECT 
	SUM(IF(quantidade = 0, 1, quantidade)) AS total 
FROM 
	gelic_licitacoes_itens
WHERE 
	id_licitacao IN (
		SELECT 
			lic.id 
		FROM 
			gelic_licitacoes AS lic 
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status IN (10,55)
		WHERE 
			lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59'
		GROUP BY 
			lic.id
	) AND 
    id_tipo_veiculo IN ($pTv) AND acompanhamento = 0");
		$db->nextRecord();
		$aVeh[] = array("total"=>intval($db->f("total")), "label"=>$aLang[$pLang]["bar2"], "bgcolor"=>"b2b2b2", "color"=>"33434c");



		//===========================
		// TOTAL AUCTION POTENTIAL
		//===========================
		$aVeh[] = array("total"=>$aVeh[0]["total"]-$aVeh[1]["total"], "label"=>$aLang[$pLang]["bar3"], "bgcolor"=>"b2b2b2", "color"=>"33434c");



		//===========================
		// NOT ATTENDED
		//===========================
		$db->query("SELECT 
	SUM(IF(quantidade = 0, 1, quantidade)) AS total 
FROM 
	gelic_licitacoes_itens
WHERE 
	id_licitacao IN (
		SELECT 
			lic.id
		FROM 
			gelic_licitacoes AS lic 
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
			LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
	        LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
		WHERE 
			lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
			(
				(
					apl.id IS NULL
				)
				OR
				(
					aba.id_status IN (24,36,52,50,56,41,49,54,16,34)
		        )
		        OR
		        (
					apl.id IS NOT NULL AND
		            aplh.tipo = 4 AND 
	                aba.id_aba <> 9
		        )
			) AND
			aba.id_status NOT IN (3,30,32,23,17,38,21,20,35,43,18,39,33,46,47,15,14,40,25)
		GROUP BY
			lic.id
	) AND 
    id_tipo_veiculo IN ($pTv) AND acompanhamento = 0");
		$db->nextRecord();
		$aVeh[] = array("total"=>intval($db->f("total")), "label"=>$aLang[$pLang]["bar4"], "bgcolor"=>"5f5f5f", "color"=>"33434c");



		//===========================
		// ATTENDED
		//===========================
		$db->query("SELECT 
	SUM(IF(quantidade = 0, 1, quantidade)) AS total 
FROM 
	gelic_licitacoes_itens
WHERE 
	id_licitacao IN (
		SELECT 
			lic.id 
		FROM 
			gelic_licitacoes AS lic 
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
			LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
	        LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
		WHERE 
			lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
			(
				(
					apl.id IS NOT NULL AND
					aplh.tipo = 2 AND
					aba.id_aba = 9
				)
			    OR
				(
					aba.id_status IN (3,30,32,23,17,38,21,20,35,43,18,39,33,46,47,15,14,40,25)
				)
	        ) AND
			aba.id_status NOT IN (52,50,56,41,49,54,16,34,36,24)
		GROUP BY 
			lic.id
	) AND 
    id_tipo_veiculo IN ($pTv) AND acompanhamento = 0");
		$db->nextRecord();
		$aVeh[] = array("total"=>intval($db->f("total")), "label"=>$aLang[$pLang]["bar5"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");



		//===========================
		// ON GOING
		//===========================
		$db->query("SELECT 
	SUM(IF(quantidade = 0, 1, quantidade)) AS total 
FROM 
	gelic_licitacoes_itens
WHERE 
	id_licitacao IN (
		SELECT 
			lic.id 
		FROM 
			gelic_licitacoes AS lic 
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
			LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
	        LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
		WHERE 
			lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
			(
				(
					apl.id IS NOT NULL AND
					aplh.tipo = 2 AND
					aba.id_aba = 9
				)
			    OR
				(
					aba.id_status IN (3,30,32,23,17,18,33,15,14)
				)
	        ) AND
			aba.id_status NOT IN (52,50,56,41,49,54,16,34,36,24,21,20,35,47,38,43,39,46,40,25)
		GROUP BY 
			lic.id
	) AND 
    id_tipo_veiculo IN ($pTv) AND acompanhamento = 0");
		$db->nextRecord();
		$aVeh[] = array("total"=>intval($db->f("total")), "label"=>$aLang[$pLang]["bar6"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");



		//===========================
		// LOST
		//===========================
		$db->query("SELECT 
	SUM(IF(quantidade = 0, 1, quantidade)) AS total 
FROM 
	gelic_licitacoes_itens
WHERE 
	id_licitacao IN (
		SELECT 
			lic.id 
		FROM 
			gelic_licitacoes AS lic 
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
		WHERE 
			lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
			aba.id_status IN (21,20,35,47)
		GROUP BY 
			lic.id
	) AND 
    id_tipo_veiculo IN ($pTv) AND acompanhamento = 0");
		$db->nextRecord();
		$aVeh[] = array("total"=>intval($db->f("total")), "label"=>$aLang[$pLang]["bar7"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");



		//===========================
		// WON
		//===========================
		$db->query("SELECT 
	SUM(IF(quantidade = 0, 1, quantidade)) AS total 
FROM 
	gelic_licitacoes_itens
WHERE 
	id_licitacao IN (
		SELECT 
			lic.id 
		FROM 
			gelic_licitacoes AS lic 
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
		WHERE 
			lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
			aba.id_status IN (38,43,39,46,40,25)
		GROUP BY 
			lic.id
	) AND 
    id_tipo_veiculo IN ($pTv) AND acompanhamento = 0");
		$db->nextRecord();
		$aVeh[] = array("total"=>intval($db->f("total")), "label"=>$aLang[$pLang]["bar8"], "bgcolor"=>"5f5f5f", "color"=>"ffffff");



		//===========================
		// Authorization request not sent
		// Approved without participation
		// Declined authorization
		// No reply
		//===========================
		$vsub_valor = array(0,0,0,0);
		$db->query("
	SELECT 
		lic.id,
		apl.id AS id_apl,
		aba.id_status,
		aba.id_aba,
		aplh.tipo,
		(SELECT SUM(IF(quantidade = 0, 1, quantidade)) FROM gelic_licitacoes_itens WHERE id_licitacao = lic.id AND id_tipo_veiculo IN ($pTv) AND acompanhamento = 0) AS carros
	FROM 
		gelic_licitacoes AS lic 
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo IN ($pTv) AND itm.acompanhamento = 0
		INNER JOIN gelic_licitacoes_abas AS aba ON aba.id_licitacao = lic.id AND aba.grupo = 1 AND aba.id_status NOT IN (10,55,44,31,45,48,37,51,12)
		LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
		LEFT JOIN gelic_licitacoes_apl_historico AS aplh ON aplh.id_apl = apl.id
	WHERE 
		lic.datahora_abertura BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59' AND
		(
			(
				apl.id IS NULL
			)
			OR
			(
				aba.id_status IN (24,36,52,50,56,41,49,54,16,34)
			)
			OR
			(
				apl.id IS NOT NULL AND
				aplh.tipo = 4 AND 
				aba.id_aba <> 9
			)
		) AND
		aba.id_status NOT IN (3,30,32,23,17,38,21,20,35,43,18,39,33,46,47,15,14,40,25)
	GROUP BY
		lic.id");
		while ($db->nextRecord())
		{
			if (strlen($db->f("id_apl")) == 0)
				$vsub_valor[0] += $db->f("carros");
			else if ($db->f("id_status") == 36)
				$vsub_valor[1] += $db->f("carros");
			else if ($db->f("tipo") == 4 && $db->f("id_aba") <> 9)
				$vsub_valor[2] += $db->f("carros");
			else
				$vsub_valor[3] += $db->f("carros");
		}




		if ($pFormato == "xlsx")
		{
			require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
			$phpexcel = new PHPExcel();
			$phpexcel->getProperties()->setCreator("GELIC")
				->setLastModifiedBy("GELIC")
				->setTitle("Estatística de Resultados")
				->setSubject("Acessos")
				->setDescription("Estatística de Resultados GELIC")
				->setKeywords("office 2007 openxml php gelic")
				->setCategory("Resultado");
								 
			$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
			$phpexcel->getDefaultStyle()->getFont()->setSize(10);

			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A1", 'Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')');

			$row = 2;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $aLang[$pLang]["lic"])
				->setCellValue("B$row", 'Total');

			for ($i=0; $i<count($aLic); $i++)
			{
				$row += 1;
	
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", str_replace('<br>', ' ', $aLic[$i]["label"]))
					->setCellValue("B$row", $aLic[$i]["total"]);

				if ($i == 3)
				{
					// Authorization request not sent
					// Approved without participation
					// Declined authorization
					// No reply
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$row", $aLang[$pLang]["sub1"])->setCellValue("B$row", $sub_valor[0]);

					$row += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$row", $aLang[$pLang]["sub2"])->setCellValue("B$row", $sub_valor[1]);

					$row += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$row", $aLang[$pLang]["sub3"])->setCellValue("B$row", $sub_valor[2]);

					$row += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$row", $aLang[$pLang]["sub4"])->setCellValue("B$row", $sub_valor[3]);
				}
			}

			$phpexcel->getActiveSheet()->mergeCells("A1:B1");
			$phpexcel->getActiveSheet()->setTitle('Estatística de Resultados');
			$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A2:B2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A2:B2")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(46);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);

			//vehicles
			$vrow = $row + 2;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$vrow", $aLang[$pLang]["veh"])
				->setCellValue("B$vrow", 'Total');

			for ($i=0; $i<count($aVeh); $i++)
			{
				$vrow += 1;

				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$vrow", str_replace('<br>', ' ', $aVeh[$i]["label"]))
					->setCellValue("B$vrow", $aVeh[$i]["total"]);

				if ($i == 3)
				{
					// Authorization request not sent
					// Approved without participation
					// Declined authorization
					// No reply
					$vrow += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$vrow", $aLang[$pLang]["sub1"])->setCellValue("B$vrow", $vsub_valor[0]);

					$vrow += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$vrow", $aLang[$pLang]["sub2"])->setCellValue("B$vrow", $vsub_valor[1]);

					$vrow += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$vrow", $aLang[$pLang]["sub3"])->setCellValue("B$vrow", $vsub_valor[2]);

					$vrow += 1;
					$phpexcel->setActiveSheetIndex(0)->setCellValue("A$vrow", $aLang[$pLang]["sub4"])->setCellValue("B$vrow", $vsub_valor[3]);
				}
			}

			$phpexcel->getActiveSheet()->getStyle("A".($row+2).":B".($row+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A".($row+2).":B".($row+2))->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getStyle("A2:A$vrow")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$phpexcel->getActiveSheet()->getStyle("B2:B$vrow")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
			$phpexcel->getActiveSheet()->getStyle("A1:B$vrow")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

			$phpexcel->getActiveSheet()->getStyle("A7:B10")->getFont()->setItalic(true);
			$phpexcel->getActiveSheet()->getStyle("A7:B10")->getFont()->getColor()->setARGB("FF808080");
			$phpexcel->getActiveSheet()->getStyle("A7:A10")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			$phpexcel->getActiveSheet()->getStyle("A21:B24")->getFont()->setItalic(true);
			$phpexcel->getActiveSheet()->getStyle("A21:B24")->getFont()->getColor()->setARGB("FF808080");
			$phpexcel->getActiveSheet()->getStyle("A21:A24")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

			if (file_exists(UPLOAD_DIR."~vw_auction_".$sInside_id.".xlsx"))
				unlink(UPLOAD_DIR."~vw_auction_".$sInside_id.".xlsx");

			$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$obwriter->save(UPLOAD_DIR."~vw_auction_".$sInside_id.".xlsx");

			$aReturn[0] = 1; //sucesso
		}
		else if ($pFormato == "pdf")
		{
			//bar 0
			$bar0_top = 24;
			$bar0_height = 324;
			$bar0_top_txt = round($bar0_height / 2) - 16 + 24;


			//bar 1
			$bar1_top = 24;
			if ($aLic[0]["total"] == 0)
				$bar1_height = round(324 * $aLic[1]["total"] / 1);
			else
				$bar1_height = round(324 * $aLic[1]["total"] / $aLic[0]["total"]);

			if ($bar1_height == 0) $bar1_height = 1;
			$bar1_top_txt = round($bar1_height / 2) - 16 + 24;
			$bar1_txt_bg = ' background-color: #'.$aLic[1]["bgcolor"].';';
			$gap1_top = $bar1_top + $bar1_height - 1;


			//bar 2
			$bar2_top = $gap1_top + 1;
			if ($aLic[2]["total"] == 0)
				$bar2_height = 1;
			else
				$bar2_height = 324 - $bar1_height;

			if ($bar2_height == 0) $bar2_height = 1;
			$bar2_top_txt = round($bar2_height / 2) - 16 + $bar2_top;
			$bar2_txt_bg = ' background-color: #'.$aLic[2]["bgcolor"].';';
			$gap2_top = $bar2_top - 1;


			//bar 3
			$bar3_top = $bar2_top;
			if ($aLic[2]["total"] == 0)
				$bar3_height = round($bar2_height * $aLic[3]["total"] / 1);
			else
				$bar3_height = round($bar2_height * $aLic[3]["total"] / $aLic[2]["total"]);

			if ($bar3_height == 0) $bar3_height = 1;
			//$bar3_top_txt = round($bar3_height / 2) - 16 + $bar3_top;
			$bar3_top_txt = $bar3_top - 36;
			//$bar3_txt_bg = ' background-color: #'.$aLic[3]["bgcolor"].';';
			$bar3_txt_bg = '';
			$gap3_top = $bar3_top + $bar3_height - 1;


			//bar 4
			$bar4_top = $gap3_top + 1;
			if ($aLic[4]["total"] == 0)
				$bar4_height = 1;
			else
				$bar4_height = 324 - $bar3_height - $bar1_height;

			if ($bar4_height == 0) $bar4_height = 1;
			$bar4_top_txt = round($bar4_height / 2) - 16 + $bar4_top;
			$bar4_txt_bg = ' background-color: #'.$aLic[4]["bgcolor"].';';
			$gap4_top = $gap3_top;


			//bar 5
			$bar5_top = $bar4_top;
			if ($aLic[4]["total"] == 0)
				$bar5_height = round($bar4_height * $aLic[5]["total"] / 1);
			else
				$bar5_height = round($bar4_height * $aLic[5]["total"] / $aLic[4]["total"]);

			if ($bar5_height == 0) $bar5_height = 1;
			$bar5_top_txt = round($bar5_height / 2) - 16 + $bar5_top;
			$bar5_txt_bg = ' background-color: #'.$aLic[5]["bgcolor"].';';
			$gap5_top = $bar5_top + $bar5_height - 1;


			//bar 6
			$bar6_top = $gap5_top + 1;
			if ($aLic[4]["total"] == 0)
				$bar6_height = round($bar4_height * $aLic[6]["total"] / 1);
			else
				$bar6_height = round($bar4_height * $aLic[6]["total"] / $aLic[4]["total"]);

			if ($bar6_height == 0) $bar6_height = 1;
			$bar6_top_txt = round($bar6_height / 2) - 16 + $bar6_top;
			$bar6_txt_bg = ' background-color: #'.$aLic[6]["bgcolor"].';';
			$gap6_top = $bar6_top + $bar6_height - 1;


			//bar 7
			$bar7_top = $gap6_top + 1;
			if ($aLic[7]["total"] == 0)
				$bar7_height = 1;
			else
				$bar7_height = $bar4_height - $bar5_height - $bar6_height;

			if ($bar7_height == 0) $bar7_height = 1;
			$bar7_top_txt = round($bar7_height / 2) - 16 + $bar7_top;
			$bar7_txt_bg = ' background-color: #'.$aLic[7]["bgcolor"].';';





			//vbar 0
			$vbar0_top = 24;
			$vbar0_height = 324;
			$vbar0_top_txt = round($vbar0_height / 2) - 16 + 24;


			//vbar 1
			$vbar1_top = 24;
			if ($aVeh[0]["total"] == 0)
				$vbar1_height = round(324 * $aVeh[1]["total"] / 1);
			else
				$vbar1_height = round(324 * $aVeh[1]["total"] / $aVeh[0]["total"]);

			if ($vbar1_height == 0) $vbar1_height = 1;
			$vbar1_top_txt = round($vbar1_height / 2) - 16 + 24;
			$vbar1_txt_bg = ' background-color: #'.$aVeh[1]["bgcolor"].';';
			$vgap1_top = $vbar1_top + $vbar1_height - 1;


			//vbar 2
			$vbar2_top = $vgap1_top + 1;
			if ($aVeh[2]["total"] == 0)
				$vbar2_height = 1;
			else
				$vbar2_height = 324 - $vbar1_height;

			if ($vbar2_height == 0) $vbar2_height = 1;
			$vbar2_top_txt = round($vbar2_height / 2) - 16 + $vbar2_top;
			$vbar2_txt_bg = ' background-color: #'.$aVeh[2]["bgcolor"].';';
			$vgap2_top = $vbar2_top - 1;


			//vbar 3
			$vbar3_top = $vbar2_top;
			if ($aVeh[2]["total"] == 0)
				$vbar3_height = round($vbar2_height * $aVeh[3]["total"] / 1);
			else
				$vbar3_height = round($vbar2_height * $aVeh[3]["total"] / $aVeh[2]["total"]);

			if ($vbar3_height == 0) $vbar3_height = 1;
			//$vbar3_top_txt = round($vbar3_height / 2) - 16 + $vbar3_top;
			$vbar3_top_txt = $vbar3_top - 36;
			//$vbar3_txt_bg = ' background-color: #'.$aVeh[3]["bgcolor"].';';
			$vbar3_txt_bg = '';
			$vgap3_top = $vbar3_top + $vbar3_height - 1;


			//vbar 4
			$vbar4_top = $vgap3_top + 1;
			if ($aVeh[4]["total"] == 0)
				$vbar4_height = 1;
			else
				$vbar4_height = 324 - $vbar3_height - $vbar1_height;

			if ($vbar4_height == 0) $vbar4_height = 1;
			$vbar4_top_txt = round($vbar4_height / 2) - 16 + $vbar4_top;
			$vbar4_txt_bg = ' background-color: #'.$aVeh[4]["bgcolor"].';';
			$vgap4_top = $vgap3_top;


			//vbar 5
			$vbar5_top = $vbar4_top;
			if ($aVeh[4]["total"] == 0)
				$vbar5_height = round($vbar4_height * $aVeh[5]["total"] / 1);
			else
				$vbar5_height = round($vbar4_height * $aVeh[5]["total"] / $aVeh[4]["total"]);

			if ($vbar5_height == 0) $vbar5_height = 1;
			$vbar5_top_txt = round($vbar5_height / 2) - 16 + $vbar5_top;
			$vbar5_txt_bg = ' background-color: #'.$aVeh[5]["bgcolor"].';';
			$vgap5_top = $vbar5_top + $vbar5_height - 1;


			//vbar 6
			$vbar6_top = $vgap5_top + 1;
			if ($aVeh[4]["total"] == 0)
				$vbar6_height = round($vbar4_height * $aVeh[6]["total"] / 1);
			else
				$vbar6_height = round($vbar4_height * $aVeh[6]["total"] / $aVeh[4]["total"]);

			if ($vbar6_height == 0) $vbar6_height = 1;
			$vbar6_top_txt = round($vbar6_height / 2) - 16 + $vbar6_top;
			$vbar6_txt_bg = ' background-color: #'.$aVeh[6]["bgcolor"].';';
			$vgap6_top = $vbar6_top + $vbar6_height - 1;


			//vbar 7
			$vbar7_top = $vgap6_top + 1;
			if ($aVeh[7]["total"] == 0)
				$vbar7_height = 1;
			else
				$vbar7_height = $vbar4_height - $vbar5_height - $vbar6_height;

			if ($vbar7_height == 0) $vbar7_height = 1;
			$vbar7_top_txt = round($vbar7_height / 2) - 16 + $vbar7_top;
			$vbar7_txt_bg = ' background-color: #'.$aVeh[7]["bgcolor"].';';


			if (substr($data_selecionada_fr, 6) == substr($data_selecionada_to, 6))
				$title = substr($data_selecionada_fr, 6).' – '.$aLang[$pLang]["titulo"];
			else
				$title = substr($data_selecionada_fr, 6).', '.substr($data_selecionada_to, 6).' – '.$aLang[$pLang]["titulo"];
	
			$reporting_period = $aLang[$pLang]["periodo"].' – '.$data_selecionada_fr.' '.$aLang[$pLang]["ate"].' '.$data_selecionada_to;



			//auctions (subdivisoes NOT ATTENDED)
			$sub = array();
			$bar3_height_clear = $bar3_height - 2;
		
			if ($aLic[3]["total"] == 0)
			{
				$sub[] = 0;
				$sub[] = 0;
				$sub[] = 0;
				$sub[] = 0;
			}
			else
			{
				$sub[] = round($bar3_height_clear * $sub_valor[0] / $aLic[3]["total"]);
				$sub[] = round($bar3_height_clear * $sub_valor[1] / $aLic[3]["total"]);
				$sub[] = round($bar3_height_clear * $sub_valor[2] / $aLic[3]["total"]);
				$sub[] = round($bar3_height_clear * $sub_valor[3] / $aLic[3]["total"]);
			}

			if ($sub[0] == 0) $sub[0] = 1;
			if ($sub[1] == 0) $sub[1] = 1;
			if ($sub[2] == 0) $sub[2] = 1;
			if ($sub[3] == 0) $sub[3] = 1;

			if (array_sum($sub) > $bar3_height_clear)
			{
				$idx = array_keys($sub, max($sub));
				$sub[$idx[0]] -= (array_sum($sub) - $bar3_height_clear);
			}
			else if (array_sum($sub) < $bar3_height_clear)
			{
				$idx = array_keys($sub, min($sub));
				$sub[$idx[0]] += ($bar3_height_clear - array_sum($sub));
			}

			$mid = array();
			$mid[] = round($sub[0] / 2);
			$mid[] = round($sub[1] / 2) + $sub[0];
			$mid[] = round($sub[2] / 2) + $sub[0] + $sub[1];
			$mid[] = round($sub[3] / 2) + $sub[0] + $sub[1] + $sub[2];

			$bottom_y = 327 - ($bar4_height + 28) - ($bar2_top - 24);





			//vehicles (subdivisoes NOT ATTENDED)
			$vsub = array();
			$vbar3_height_clear = $vbar3_height - 2;

			if ($aVeh[3]["total"] == 0)
			{
				$vsub[] = 0;
				$vsub[] = 0;
				$vsub[] = 0;
				$vsub[] = 0;
			}
			else
			{
				$vsub[] = round($vbar3_height_clear * $vsub_valor[0] / $aVeh[3]["total"]);
				$vsub[] = round($vbar3_height_clear * $vsub_valor[1] / $aVeh[3]["total"]);
				$vsub[] = round($vbar3_height_clear * $vsub_valor[2] / $aVeh[3]["total"]);
				$vsub[] = round($vbar3_height_clear * $vsub_valor[3] / $aVeh[3]["total"]);
			}

			if ($vsub[0] == 0) $vsub[0] = 1;
			if ($vsub[1] == 0) $vsub[1] = 1;
			if ($vsub[2] == 0) $vsub[2] = 1;
			if ($vsub[3] == 0) $vsub[3] = 1;

			if (array_sum($vsub) > $vbar3_height_clear)
			{
				$vidx = array_keys($vsub, max($vsub));
				$vsub[$vidx[0]] -= (array_sum($vsub) - $vbar3_height_clear);
			}
			else if (array_sum($vsub) < $vbar3_height_clear)
			{
				$vidx = array_keys($vsub, min($vsub));
				$vsub[$vidx[0]] += ($vbar3_height_clear - array_sum($vsub));
			}

			$vmid = array();
			$vmid[] = round($vsub[0] / 2);
			$vmid[] = round($vsub[1] / 2) + $vsub[0];
			$vmid[] = round($vsub[2] / 2) + $vsub[0] + $vsub[1];
			$vmid[] = round($vsub[3] / 2) + $vsub[0] + $vsub[1] + $vsub[2];

			$vbottom_y = 327 - ($vbar4_height + 28) - ($vbar2_top - 24);


			$tHtml = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Estatística de Resultados</title>
	<style>
		body { font-family: \'Arial\',\'Trebuchet MS\',\'Tahoma\'; font-size: 14px; color: #000000; }

		.header { 
			position: relative;
			height: 92px;
		}

		.header span { 
			position: absolute;
			left: 0;
			top: 0;
			font-size: 60px;
			font-weight: bold;
			color: #000000;
		}

		.header img {
			position: absolute;
			right: 0;
			top: 0;
			border: 0;
			width: 131px;
			height: 92px;
		}

		.gap { height: 32px; }

		.info { position: relative; height: 372px; }
	
		.bottom-line {
			position: absolute;
			right: 0;
			bottom: 22px;
			width: 1416px;
			height: 2px;
			background-color: #003c65;
		}

		.section {
			float: left;
			width: 60px;
			height: 372px;
		}

		.section span {
			float: left;
			background-color: #003c65;
			color: #ffffff;
			font-size: 40px;
			font-weight: bold;
			line-height: 60px;
			text-align: center;
			width: 324px;
			margin-top: 24px;
			-webkit-transform: rotate(-90deg);
			-webkit-transform-origin: 162px 162px;
		}

		.vgap { float: left; width: 54px; height: 372px; }
		.vgap-24 { position: relative; float: left; width: 24px; height: 372px; }
		.vgap-24 div { position: absolute; left: 0; top: 23px; width: 24px; border-bottom: 1px dotted #666666; }
		.bar { position: relative; float: left; width: 154px; height: 372px; }
		.bar-box { position: absolute; left: 0; top: 24px; width: 154px; height: 324px; box-sizing: border-box; }
		.bar-txt { position: absolute; left: 0; top: 24px; width: 154px; text-align: center; }
		.bar-txt span { display: inline-block; line-height: 32px; font-size: 28px; font-weight: bold; padding: 0 6px; }

		.labels { height: 62px; }

		.labels span {
			float: left;
			line-height: 30px;
			text-align: center;
			width: 178px;
			font-size: 24px;
			font-weight: bold;
			color: #444444;
		}

		.legend_top_right {
			position: absolute;
			right: 0;
			top: 0;
			padding: 0;
			margin: 0;
			font-size: 28px;
			line-height: 38px;
		}

		.legend_top_right span {
			float: left;
			clear: both;
		}

		.sq1 {
			display: inline-block;
			width: 24px;
			height: 24px;
			background-color: #277ab0;
			margin-right: 10px;
		}

		.sq2 {
			display: inline-block;
			width: 24px;
			height: 24px;
			background-color: #394952;
			margin-right: 10px;
		}

		.sq3 {
			display: inline-block;
			width: 24px;
			height: 24px;
			background-color: #8f99a5;
			margin-right: 10px;
		}

		.sq4 {
			display: inline-block;
			width: 24px;
			height: 24px;
			background-color: #dfdfdf;
			margin-right: 10px;
		}

		.divider { height: 62px; }

		.sub1 {
			height: '.$sub[0].'px;
			background-color: #277ab0;
		}

		.sub2 {
			height: '.$sub[1].'px;
			background-color: #394952;
		}

		.sub3 {
			height: '.$sub[2].'px;
			background-color: #8f99a5;
		}

		.sub4 {
			height: '.$sub[3].'px;
			background-color: #dfdfdf;
		}

		.na_labels {
			position: absolute;
			left: 868px;
			bottom: '.($bar4_height + 28).'px;
			margin: 0;
			padding: 0;
			line-height: 40px;
			font-size: 24px;
		}

		.vsub1 {
			height: '.$vsub[0].'px;
			background-color: #277ab0;
		}

		.vsub2 {
			height: '.$vsub[1].'px;
			background-color: #394952;
		}

		.vsub3 {
			height: '.$vsub[2].'px;
			background-color: #8f99a5;
		}

		.vsub4 {
			height: '.$vsub[3].'px;
			background-color: #dfdfdf;
		}

		.vna_labels {
			position: absolute;
			left: 868px;
			bottom: '.($vbar4_height + 28).'px;
			margin: 0;
			padding: 0;
			line-height: 40px;
			font-size: 24px;
		}

		.footer {
			position: relative;
			height: 92px;
		}

		.footer span {
			position: absolute;
			left: 0;
			bottom: 0;
			font-size: 28px;
			font-weight: bold;
			color: #444444;
		}

		.footer img {
			position: absolute;
			right: 0;
			bottom: 0;
			width: 92px;
			height: 92px;
		}

	</style>
</head>
<body>
<div style="width: 1522px; overflow: hidden;">
	<div class="header">
		<span>'.$title.'</span>
		<img src="../admin/img/brflag.png">
	</div>
	<div class="gap"></div>
	<div class="info">
		<div class="bottom-line"></div>
		<div class="section"><span>'.$aLang[$pLang]["lic"].'</span></div>
		<div class="vgap"></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$bar0_top.'px; height: '.$bar0_height.'px; background-color: #'.$aLic[0]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$bar0_top_txt.'px;"><span style="color: #'.$aLic[0]["color"].'">'.$aLic[0]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$bar1_top.'px; height: '.$bar1_height.'px; background-color: #'.$aLic[1]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$bar1_top_txt.'px;"><span style="color: #'.$aLic[1]["color"].';'.$bar1_txt_bg.'">'.$aLic[1]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$gap1_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$bar2_top.'px; height: '.$bar2_height.'px; background-color: #'.$aLic[2]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$bar2_top_txt.'px;"><span style="color: #'.$aLic[2]["color"].';'.$bar2_txt_bg.'">'.$aLic[2]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$gap2_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$bar3_top.'px; height: '.$bar3_height.'px; background-color: #'.$aLic[3]["bgcolor"].'; border: 1px solid #33434c;"><div class="sub1"></div><div class="sub2"></div><div class="sub3"></div><div class="sub4"></div></div>
			<div class="bar-txt" style="top: '.$bar3_top_txt.'px;"><span style="color: #'.$aLic[3]["color"].';'.$bar3_txt_bg.'">'.$aLic[3]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$gap3_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$bar4_top.'px; height: '.$bar4_height.'px; background-color: #'.$aLic[4]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$bar4_top_txt.'px;"><span style="color: #'.$aLic[4]["color"].';'.$bar4_txt_bg.'">'.$aLic[4]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$gap4_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$bar5_top.'px; height: '.$bar5_height.'px; background-color: #'.$aLic[5]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$bar5_top_txt.'px;"><span style="color: #'.$aLic[5]["color"].';'.$bar5_txt_bg.'">'.$aLic[5]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$gap5_top.'px;"></div></div>		
		<div class="bar">
			<div class="bar-box" style="top: '.$bar6_top.'px; height: '.$bar6_height.'px; background-color: #'.$aLic[6]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$bar6_top_txt.'px;"><span style="color: #'.$aLic[6]["color"].';'.$bar6_txt_bg.'">'.$aLic[6]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$gap6_top.'px;"></div></div>		
		<div class="bar">
			<div class="bar-box" style="top: '.$bar7_top.'px; height: '.$bar7_height.'px; background-color: #'.$aLic[7]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$bar7_top_txt.'px;"><span style="color: #'.$aLic[7]["color"].';'.$bar7_txt_bg.'">'.$aLic[7]["total"].'</span></div>
		</div>
		<p class="na_labels">
			<span>'.$sub_valor[0].'</span><br>
			<span>'.$sub_valor[1].'</span><br>
			<span>'.$sub_valor[2].'</span><br>
			<span>'.$sub_valor[3].'</span>
		</p>
		<svg height="340" width="300" style="position:absolute; left: 770px; top: '.($bar3_top+1).'px;">
			<line x1="0" y1="'.$mid[0].'" x2="90" y2="'.($bottom_y-120).'" style="stroke:rgb(0,0,0);stroke-width:1" />
			<line x1="0" y1="'.$mid[1].'" x2="90" y2="'.($bottom_y-80).'" style="stroke:rgb(0,0,0);stroke-width:1" />
			<line x1="0" y1="'.$mid[2].'" x2="90" y2="'.($bottom_y-40).'" style="stroke:rgb(0,0,0);stroke-width:1" />
			<line x1="0" y1="'.$mid[3].'" x2="90" y2="'.$bottom_y.'" style="stroke:rgb(0,0,0);stroke-width:1" />
		</svg>
		<p class="legend_top_right">
			<span><a class="sq1"></a>'.$aLang[$pLang]["sub1"].'</span>
			<span><a class="sq2"></a>'.$aLang[$pLang]["sub2"].'</span>
			<span><a class="sq3"></a>'.$aLang[$pLang]["sub3"].'</span>
			<span><a class="sq4"></a>'.$aLang[$pLang]["sub4"].'</span>
		</p>
	</div>
	<div class="labels">
		<span style="margin-left: 102px;">'.$aLic[0]["label"].'</span>
		<span>'.$aLic[1]["label"].'</span>
		<span>'.$aLic[2]["label"].'</span>
		<span>'.$aLic[3]["label"].'</span>
		<span>'.$aLic[4]["label"].'</span>
		<span>'.$aLic[5]["label"].'</span>
		<span>'.$aLic[6]["label"].'</span>
		<span style="margin-left:12px; width: 154px;">'.$aLic[7]["label"].'</span>
	</div>
	<div class="divider"></div>
	<div class="info">
		<div class="bottom-line"></div>
		<div class="section"><span>'.$aLang[$pLang]["veh"].'</span></div>
		<div class="vgap"></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar0_top.'px; height: '.$vbar0_height.'px; background-color: #'.$aVeh[0]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$vbar0_top_txt.'px;"><span style="color: #'.$aVeh[0]["color"].'">'.$aVeh[0]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar1_top.'px; height: '.$vbar1_height.'px; background-color: #'.$aVeh[1]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$vbar1_top_txt.'px;"><span style="color: #'.$aVeh[1]["color"].';'.$vbar1_txt_bg.'">'.$aVeh[1]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$vgap1_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar2_top.'px; height: '.$vbar2_height.'px; background-color: #'.$aVeh[2]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$vbar2_top_txt.'px;"><span style="color: #'.$aVeh[2]["color"].';'.$vbar2_txt_bg.'">'.$aVeh[2]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$vgap2_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar3_top.'px; height: '.$vbar3_height.'px; background-color: #'.$aVeh[3]["bgcolor"].'; border: 1px solid #33434c;"><div class="vsub1"></div><div class="vsub2"></div><div class="vsub3"></div><div class="vsub4"></div></div>
			<div class="bar-txt" style="top: '.$vbar3_top_txt.'px;"><span style="color: #'.$aVeh[3]["color"].';'.$vbar3_txt_bg.'">'.$aVeh[3]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$vgap3_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar4_top.'px; height: '.$vbar4_height.'px; background-color: #'.$aVeh[4]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$vbar4_top_txt.'px;"><span style="color: #'.$aVeh[4]["color"].';'.$vbar4_txt_bg.'">'.$aVeh[4]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$vgap4_top.'px;"></div></div>
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar5_top.'px; height: '.$vbar5_height.'px; background-color: #'.$aVeh[5]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$vbar5_top_txt.'px;"><span style="color: #'.$aVeh[5]["color"].';'.$vbar5_txt_bg.'">'.$aVeh[5]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$vgap5_top.'px;"></div></div>		
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar6_top.'px; height: '.$vbar6_height.'px; background-color: #'.$aVeh[6]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$vbar6_top_txt.'px;"><span style="color: #'.$aVeh[6]["color"].';'.$vbar6_txt_bg.'">'.$aVeh[6]["total"].'</span></div>
		</div>
		<div class="vgap-24"><div style="top: '.$vgap6_top.'px;"></div></div>		
		<div class="bar">
			<div class="bar-box" style="top: '.$vbar7_top.'px; height: '.$vbar7_height.'px; background-color: #'.$aVeh[7]["bgcolor"].';"></div>
			<div class="bar-txt" style="top: '.$vbar7_top_txt.'px;"><span style="color: #'.$aVeh[7]["color"].';'.$vbar7_txt_bg.'">'.$aVeh[7]["total"].'</span></div>
		</div>
		<p class="vna_labels">
			<span>'.$vsub_valor[0].'</span><br>
			<span>'.$vsub_valor[1].'</span><br>
			<span>'.$vsub_valor[2].'</span><br>
			<span>'.$vsub_valor[3].'</span>
		</p>
		<svg height="340" width="300" style="position:absolute; left: 770px; top: '.($vbar3_top+1).'px;">
			<line x1="0" y1="'.$vmid[0].'" x2="90" y2="'.($vbottom_y-120).'" style="stroke:rgb(0,0,0);stroke-width:1" />
			<line x1="0" y1="'.$vmid[1].'" x2="90" y2="'.($vbottom_y-80).'" style="stroke:rgb(0,0,0);stroke-width:1" />
			<line x1="0" y1="'.$vmid[2].'" x2="90" y2="'.($vbottom_y-40).'" style="stroke:rgb(0,0,0);stroke-width:1" />
			<line x1="0" y1="'.$vmid[3].'" x2="90" y2="'.$vbottom_y.'" style="stroke:rgb(0,0,0);stroke-width:1" />
		</svg>
		<p class="legend_top_right">
			<span><a class="sq1"></a>'.$aLang[$pLang]["sub1"].'</span>
			<span><a class="sq2"></a>'.$aLang[$pLang]["sub2"].'</span>
			<span><a class="sq3"></a>'.$aLang[$pLang]["sub3"].'</span>
			<span><a class="sq4"></a>'.$aLang[$pLang]["sub4"].'</span>
		</p>
	</div>
	<div class="labels">
		<span style="margin-left: 102px;">'.$aVeh[0]["label"].'</span>
		<span>'.$aVeh[1]["label"].'</span>
		<span>'.$aVeh[2]["label"].'</span>
		<span>'.$aVeh[3]["label"].'</span>
		<span>'.$aVeh[4]["label"].'</span>
		<span>'.$aVeh[5]["label"].'</span>
		<span>'.$aVeh[6]["label"].'</span>
		<span style="margin-left:12px; width: 154px;">'.$aVeh[7]["label"].'</span>
	</div>
	<div class="footer">
		<span>'.$reporting_period.'</span>
		<img src="../admin/img/vwlogo.png">
	</div>
</div>
</body>
</html>';

			$oFile = fopen(UPLOAD_DIR."~vw_auction_".$sInside_id.".html", "w");
			fwrite($oFile, $tHtml);
			fclose($oFile);

			exec(PATH_HTMTOPDF." --orientation landscape --page-size Letter --image-quality 100 ".UPLOAD_DIR."~vw_auction_".$sInside_id.".html ".UPLOAD_DIR."~vw_auction_".$sInside_id.".pdf");
			@unlink(UPLOAD_DIR."~vw_auction_".$sInside_id.".html");

			$aReturn[0] = 1; //sucesso
		}
	}
}
echo json_encode($aReturn);

?>
