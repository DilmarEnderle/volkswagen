<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFormato = trim($_POST["formato"]);
	$pStatus = trim($_POST["status"]);
	$pPeriodo_fr = $_POST["periodo-fr"];
	$pPeriodo_to = $_POST["periodo-to"];
	$pDetalhamento = intval($_POST["detalhamento"]);
	$db = new Mysql();

	//VALIDAR DATA PERIODO DE
	if (strlen($pPeriodo_fr) == 10 && isValidBrDate($pPeriodo_fr))
		$pPeriodo_fr = brToMysql($pPeriodo_fr); // yyyy-mm-dd
	else
	{
		$db->query("SELECT DATE(datahora_abertura) AS dt FROM gelic_licitacoes WHERE deletado = 0 ORDER BY datahora_abertura LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_fr = $db->f("dt");
		else
			$pPeriodo_fr = date("Y-m-d");
	}

	//VALIDAR DATA PERIODO ATE
	if (strlen($pPeriodo_to) == 10 && isValidBrDate($pPeriodo_to))
		$pPeriodo_to = brToMysql($pPeriodo_to); // yyyy-mm-dd
	else
		$pPeriodo_to = date("Y-m-d");

	//SE O PERIODO DE FOR MAIOR DO QUE O PERIODO ATE (INVERTER)
	if (intval(str_replace("-","",$pPeriodo_fr)) > intval(str_replace("-","",$pPeriodo_to)))
	{
		$t = $pPeriodo_to;
		$pPeriodo_to = $pPeriodo_fr;
		$pPeriodo_fr = $t;
	}

	$aStatus = array();


	//for each status
	$db->query("SELECT id, descricao FROM gelic_status WHERE id IN ($pStatus) ORDER BY descricao");
	while ($db->nextRecord())
	{
		$add_to_where = "";
		$add_to_where_OR = "";

		if ($db->f("id") == 8)
			$add_to_where_OR .= " OR (labas.id_status = 19 AND ear.aprovadas > 0)";
		else if ($db->f("id") == 19)
			$add_to_where_OR .= " OR (labas.id_status = 8 AND ear.reprovadas > 0)";
		else if ($db->f("id") == 17)
			$add_to_where_OR .= " OR (labas.id_status IN (8,19) AND lic.fase = 1 AND ear.enviadas > 0)";
		else if ($db->f("id") == 3)
			$add_to_where_OR .= " OR (labas.id_status IN (8,19) AND lic.fase > 1 AND ear.enviadas > 0)";

		if ($add_to_where_OR == "")
			$add_to_where .= " AND labas.id_status = ".$db->f("id");
		else
			$add_to_where .= " AND ((labas.id_status = ".$db->f("id").")$add_to_where_OR)";

		$aLicitacoes = array();
		$db->query("
SELECT
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 1
	LEFT JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.datahora_abertura >= '$pPeriodo_fr 00:00:00' AND
	lic.datahora_abertura <= '$pPeriodo_to 23:59:59'$add_to_where
GROUP BY
	lic.id",1);
		while ($db->nextRecord(1))
			$aLicitacoes[] = $db->f("id",1);

		$total_veiculos = 0;
		if (count($aLicitacoes) > 0)
		{
			$db->query("SELECT SUM(IF(quantidade = 0, 1, quantidade)) AS total FROM gelic_licitacoes_itens WHERE id_licitacao IN (".implode(",",$aLicitacoes).")",1);
			$db->nextRecord(1);
			$total_veiculos = $db->f("total",1);
		}

		$aStatus[] = array("status"=>utf8_encode($db->f("descricao")), "licitacoes"=>$aLicitacoes, "veiculos"=>$total_veiculos);
	}


	if ($pFormato == "xlsx")
	{
		require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Relatório por status")
			->setSubject("GELIC")
			->setDescription("Relatório por status")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Relatorio");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Período escolhido ('.mysqlToBr($pPeriodo_fr).' - '.mysqlToBr($pPeriodo_to).')');

		$row += 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Status')
			->setCellValue("B$row", 'Licitações')
			->setCellValue("C$row", 'Veículos');

		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ff888888');
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->getColor()->setRGB('ffffff');


		for ($i=0; $i<count($aStatus); $i++)
		{
			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $aStatus[$i]["status"])
				->setCellValue("B$row", count($aStatus[$i]["licitacoes"]))
				->setCellValue("C$row", $aStatus[$i]["veiculos"]);

			if ($pDetalhamento == 1)
			{
				$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
				$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);

				if (count($aStatus[$i]["licitacoes"]) > 0)
				{
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", '')
						->setCellValue("B$row", 'Licitação')
						->setCellValue("C$row", 'Órgão')
						->setCellValue("D$row", 'Cidade')
						->setCellValue("E$row", 'Estado')
						->setCellValue("F$row", 'Número')
						->setCellValue("G$row", 'Data/Hora Abertura');

					$phpexcel->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
					$phpexcel->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setItalic(true);

					$db->query("
SELECT
	lic.id,
	lic.orgao,
	lic.datahora_abertura,
	lic.numero,
	cid.nome AS cidade,
	cid.uf AS estado
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
WHERE
	lic.id IN (".implode(",",$aStatus[$i]["licitacoes"]).")
ORDER BY
	lic.datahora_abertura");
					while ($db->nextRecord())
					{
						$row += 1;
						$phpexcel->setActiveSheetIndex(0)
							->setCellValue("B$row", $db->f("id"))
							->setCellValue("C$row", utf8_encode($db->f("orgao")))
							->setCellValue("D$row", utf8_encode($db->f("cidade")))
							->setCellValue("E$row", $db->f("estado"))
							->setCellValue("F$row", utf8_encode($db->f("numero")))
							->setCellValue("G$row", mysqlToBr(substr($db->f("datahora_abertura"),0,10))." ".substr($db->f("datahora_abertura"),11));
					}
				}
			}
		}


		$phpexcel->getActiveSheet()->setTitle('Relatório por status');
		$phpexcel->getActiveSheet()->mergeCells("A1:C1");
		$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);

		if ($pDetalhamento == 1)
		{
			$phpexcel->getActiveSheet()->mergeCells("A1:F1");
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(80);
			$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
			$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
			$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$phpexcel->getActiveSheet()->getStyle("A4:G$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		}
		else
		{
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$phpexcel->getActiveSheet()->getStyle("A4:C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		}

		if (file_exists(UPLOAD_DIR."~bo_rel_5_1_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~bo_rel_5_1_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~bo_rel_5_1_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
