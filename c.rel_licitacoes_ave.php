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
		require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Detalhamento AVEs")
			->setSubject("Aves")
			->setDescription("Detalhamento AVEs GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("AVE");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A1", 'Período escolhido ('.usToBr($pPeriodo_fr).' - '.usToBr($pPeriodo_to).')');

		$row = 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Número GELIC')
			->setCellValue("B$row", 'Nome do Órgão')
			->setCellValue("C$row", 'Data de Abertura')
			->setCellValue("D$row", 'AVE')
			->setCellValue("E$row", 'DN Enviou APL')
			->setCellValue("F$row", 'Model Code')
			->setCellValue("G$row", 'Qtd.')
			->setCellValue("H$row", 'Prazo de Entrega')
			->setCellValue("I$row", 'Validade da Proposta')
			->setCellValue("J$row", 'Status APL')
			->setCellValue("K$row", 'Resultado');


		$aStatus = array('','Enviada','Aprovada','','Reprovada','Aprovação Revertida','Reprovação Revertida');

		$db->query("
SELECT
	lic.id,
	lic.orgao,
	lic.datahora_abertura,
	lic.fase,
	labas.id_status
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 1
WHERE
	lic.id IN (SELECT id_licitacao FROM gelic_licitacoes_apl) AND
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	lic.deletado = 0
GROUP BY
	lic.id");
		while ($db->nextRecord())
		{
			$dId_licitacao = $db->f("id");
			$dOrgao = utf8_encode($db->f("orgao"));
			$dDatahora_abertura = $db->f("datahora_abertura");
			$dResultado = "";

			if (in_array($db->f("id_status"), array(8,19))) // APL Aprovada, APL Reprovada
			{
				$r = array();
				$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = ".$db->f("id"),1);
				$db->nextRecord(1);

				if ($db->f("fase") == 1)
				{
					if ($db->f("enviadas",1) > 0)
						$r[] = "APL em Análise - GELIC (".$db->f("enviadas",1).")";
				}
				else
				{
					if ($db->f("enviadas",1) > 0)
						$r[] = "APL Aguardando Aprovação (".$db->f("enviadas",1).")";
				}
	
				if ($db->f("aprovadas",1) > 0)
					$r[] = "APL Aprovada (".$db->f("aprovadas",1).")";

				if ($db->f("reprovadas",1) > 0)
					$r[] = "APL Reprovada (".$db->f("reprovadas",1).")";

				$dResultado = implode(", ", $r);
			}
			else
			{
				$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = ".$db->f("id_status"),1);
				$db->nextRecord(1);
				$dResultado = utf8_encode($db->f("descricao",1));
			}




			$db->query("
SELECT 
	DISTINCT(IF (cli.id_parent > 0, cli.id_parent, cli.id)) AS id_parent
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_cliente = cli.id AND apl.id_licitacao = $dId_licitacao",1);
			while ($db->nextRecord(1))
			{
				$dId_parent = $db->f("id_parent",1);
				$db->query("SELECT nome FROM gelic_clientes WHERE id = $dId_parent",2);
				$db->nextRecord(2);
				$dNome_cliente = utf8_encode($db->f("nome",2));

				$db->query("
SELECT
	apl.id,
	apl.ave,
	apl.model_code,
	apl.prazo_de_entrega,
	apl.validade_da_proposta,
	apl.quantidade,
    (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM
	gelic_licitacoes_itens AS itm
	INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND 
    apl.id = (
		SELECT
			MAX(id) 
		FROM
			gelic_licitacoes_apl
		WHERE
			id_licitacao = $dId_licitacao AND
            id_item = itm.id AND
            (id_cliente = $dId_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $dId_parent))
		)",2);
				while ($db->nextRecord(2))
				{
					$dAve = utf8_encode($db->f("ave",2));
					$dModel_code = utf8_encode($db->f("model_code",2));
					$dPrazo_de_entrega = intval($db->f("prazo_de_entrega",2));
					$dValidade_da_proposta = intval($db->f("validade_da_proposta",2));
					$dQuantidade = intval($db->f("quantidade",2));
					$dStatus = intval($db->f("tipo",2));

					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", $dId_licitacao)
						->setCellValue("B$row", $dOrgao)
						->setCellValue("C$row", $dDatahora_abertura)
						->setCellValue("D$row", $dAve)
						->setCellValue("E$row", $dNome_cliente)
						->setCellValue("F$row", $dModel_code)
						->setCellValue("G$row", $dQuantidade)
						->setCellValue("H$row", $dPrazo_de_entrega)
						->setCellValue("I$row", $dValidade_da_proposta)
						->setCellValue("J$row", $aStatus[$dStatus])
						->setCellValue("K$row", $dResultado);
				}
			}
		}


		$phpexcel->getActiveSheet()->setTitle('Detalhamento AVEs');
		$phpexcel->getActiveSheet()->mergeCells("A1:K1");
		$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A2:K2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:K2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
		$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
		$phpexcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
		$phpexcel->getActiveSheet()->getStyle("A2:K$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("A2:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("C2:D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("G2:I$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

		if (file_exists(UPLOAD_DIR."~licitacoes_ave_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~licitacoes_ave_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~licitacoes_ave_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
