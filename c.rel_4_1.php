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

	$aLicitacoes_m = array();
	$aLicitacoes_e = array();
	$aLicitacoes_f = array();

	if ($pDetalhamento == 1)
	{
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
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '$pPeriodo_fr 00:00:00' AND
	lic.datahora_abertura <= '$pPeriodo_to 23:59:59'
ORDER BY
	lic.datahora_abertura");
		while ($db->nextRecord())
			$aLicitacoes_m[] = array("id"=>$db->f("id"), "orgao"=>utf8_encode($db->f("orgao")), "datahora_abertura"=>$db->f("datahora_abertura"), "numero"=>utf8_encode($db->f("numero")), "cidade"=>utf8_encode($db->f("cidade")), "estado"=>$db->f("estado"));

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
	lic.deletado = 0 AND
	lic.instancia = 2 AND
	lic.datahora_abertura >= '$pPeriodo_fr 00:00:00' AND
	lic.datahora_abertura <= '$pPeriodo_to 23:59:59'
ORDER BY
	lic.datahora_abertura");
		while ($db->nextRecord())
			$aLicitacoes_e[] = array("id"=>$db->f("id"), "orgao"=>utf8_encode($db->f("orgao")), "datahora_abertura"=>$db->f("datahora_abertura"), "numero"=>utf8_encode($db->f("numero")), "cidade"=>utf8_encode($db->f("cidade")), "estado"=>$db->f("estado"));

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
	lic.deletado = 0 AND
	lic.instancia = 3 AND
	lic.datahora_abertura >= '$pPeriodo_fr 00:00:00' AND
	lic.datahora_abertura <= '$pPeriodo_to 23:59:59'
ORDER BY
	lic.datahora_abertura");
		while ($db->nextRecord())
			$aLicitacoes_f[] = array("id"=>$db->f("id"), "orgao"=>utf8_encode($db->f("orgao")), "datahora_abertura"=>$db->f("datahora_abertura"), "numero"=>utf8_encode($db->f("numero")), "cidade"=>utf8_encode($db->f("cidade")), "estado"=>$db->f("estado"));
	}
	else
	{
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes WHERE deletado = 0 AND instancia = 1 AND datahora_abertura >= '$pPeriodo_fr 00:00:00' AND datahora_abertura <= '$pPeriodo_to 23:59:59'");
		$db->nextRecord();
		$aLicitacoes_m = array("total"=>$db->f("total"));

		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes WHERE deletado = 0 AND instancia = 2 AND datahora_abertura >= '$pPeriodo_fr 00:00:00' AND datahora_abertura <= '$pPeriodo_to 23:59:59'");
		$db->nextRecord();
		$aLicitacoes_e = array("total"=>$db->f("total"));

		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes WHERE deletado = 0 AND instancia = 3 AND datahora_abertura >= '$pPeriodo_fr 00:00:00' AND datahora_abertura <= '$pPeriodo_to 23:59:59'");
		$db->nextRecord();
		$aLicitacoes_f = array("total"=>$db->f("total"));
	}

	if ($pFormato == "xlsx")
	{
		require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Quantitativo total de processos Estaduais, Federais e Municipais")
			->setSubject("GELIC")
			->setDescription("Quantitativo total de processos Estaduais, Federais e Municipais")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Relatorio");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Período escolhido ('.mysqlToBr($pPeriodo_fr).' - '.mysqlToBr($pPeriodo_to).')');

		$row += 3;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Instância')
			->setCellValue("B$row", 'Licitações');

		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ff888888');
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->getColor()->setRGB('ffffff');

		if ($pDetalhamento == 1)
		{
			$total_m = count($aLicitacoes_m);
			$total_e = count($aLicitacoes_e);
			$total_f = count($aLicitacoes_f);
			
		}
		else
		{
			$total_m = $aLicitacoes_m["total"];
			$total_e = $aLicitacoes_e["total"];
			$total_f = $aLicitacoes_f["total"];
		}

		$phpexcel->setActiveSheetIndex(0)->setCellValue("A2", 'Total de Licitações no período: '.($total_m + $total_e + $total_f));


		// output MUNICIPAL
		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", "Municipal")
			->setCellValue("B$row", $total_m);

		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);

		if ($pDetalhamento == 1)
		{
			if (count($aLicitacoes_m) > 0)
			{
				$row += 1;
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", 'Licitação')
					->setCellValue("B$row", 'Órgão')
					->setCellValue("C$row", 'Cidade')
					->setCellValue("D$row", 'Estado')
					->setCellValue("E$row", 'Número')
					->setCellValue("F$row", 'Data/Hora Abertura');

				$phpexcel->getActiveSheet()->getStyle("A$row:F$row")->getFont()->setBold(true);
				$phpexcel->getActiveSheet()->getStyle("A$row:F$row")->getFont()->setItalic(true);

				for ($i=0; $i<count($aLicitacoes_m); $i++)
				{
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", $aLicitacoes_m[$i]["id"])
						->setCellValue("B$row", $aLicitacoes_m[$i]["orgao"])
						->setCellValue("C$row", $aLicitacoes_m[$i]["cidade"])
						->setCellValue("D$row", $aLicitacoes_m[$i]["estado"])
						->setCellValue("E$row", $aLicitacoes_m[$i]["numero"])
						->setCellValue("F$row", mysqlToBr(substr($aLicitacoes_m[$i]["datahora_abertura"],0,10))." ".substr($aLicitacoes_m[$i]["datahora_abertura"],11));
				}
			}
			$row += 1;
		}


		// output ESTADUAL
		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", "Estadual")
			->setCellValue("B$row", $total_e);

		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);

		if ($pDetalhamento == 1)
		{
			if (count($aLicitacoes_e) > 0)
			{
				$row += 1;
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", 'Licitação')
					->setCellValue("B$row", 'Órgão')
					->setCellValue("C$row", 'Cidade')
					->setCellValue("D$row", 'Estado')
					->setCellValue("E$row", 'Número')
					->setCellValue("F$row", 'Data/Hora Abertura');

				$phpexcel->getActiveSheet()->getStyle("A$row:F$row")->getFont()->setBold(true);
				$phpexcel->getActiveSheet()->getStyle("A$row:F$row")->getFont()->setItalic(true);

				for ($i=0; $i<count($aLicitacoes_e); $i++)
				{
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", $aLicitacoes_e[$i]["id"])
						->setCellValue("B$row", $aLicitacoes_e[$i]["orgao"])
						->setCellValue("C$row", $aLicitacoes_e[$i]["cidade"])
						->setCellValue("D$row", $aLicitacoes_e[$i]["estado"])
						->setCellValue("E$row", $aLicitacoes_e[$i]["numero"])
						->setCellValue("F$row", mysqlToBr(substr($aLicitacoes_e[$i]["datahora_abertura"],0,10))." ".substr($aLicitacoes_e[$i]["datahora_abertura"],11));
				}
			}
			$row += 1;
		}




		// output FEDERAL
		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", "Federal")
			->setCellValue("B$row", $total_f);

		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);

		if ($pDetalhamento == 1)
		{
			if (count($aLicitacoes_f) > 0)
			{
				$row += 1;
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", 'Licitação')
					->setCellValue("B$row", 'Órgão')
					->setCellValue("C$row", 'Cidade')
					->setCellValue("D$row", 'Estado')
					->setCellValue("E$row", 'Número')
					->setCellValue("F$row", 'Data/Hora Abertura');

				$phpexcel->getActiveSheet()->getStyle("A$row:F$row")->getFont()->setBold(true);
				$phpexcel->getActiveSheet()->getStyle("A$row:F$row")->getFont()->setItalic(true);

				for ($i=0; $i<count($aLicitacoes_f); $i++)
				{
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", $aLicitacoes_f[$i]["id"])
						->setCellValue("B$row", $aLicitacoes_f[$i]["orgao"])
						->setCellValue("C$row", $aLicitacoes_f[$i]["cidade"])
						->setCellValue("D$row", $aLicitacoes_f[$i]["estado"])
						->setCellValue("E$row", $aLicitacoes_f[$i]["numero"])
						->setCellValue("F$row", mysqlToBr(substr($aLicitacoes_f[$i]["datahora_abertura"],0,10))." ".substr($aLicitacoes_f[$i]["datahora_abertura"],11));
				}
			}
			$row += 1;
		}


		$phpexcel->getActiveSheet()->setTitle('Quantitativo total de processos');

		if ($pDetalhamento == 1)
		{
			$phpexcel->getActiveSheet()->mergeCells("A1:F1");
			$phpexcel->getActiveSheet()->mergeCells("A2:F2");
			$phpexcel->getActiveSheet()->getStyle("A1:A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getStyle("A2")->getFont()->setItalic(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(80);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
			$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
			$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$phpexcel->getActiveSheet()->getStyle("A4:F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		}
		else
		{
			$phpexcel->getActiveSheet()->mergeCells("A1:B1");
			$phpexcel->getActiveSheet()->mergeCells("A2:B2");
			$phpexcel->getActiveSheet()->getStyle("A1:A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getStyle("A2")->getFont()->setItalic(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(28);
			$phpexcel->getActiveSheet()->getStyle("A4:B$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		}

		if (file_exists(UPLOAD_DIR."~bo_rel_4_1_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~bo_rel_4_1_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~bo_rel_4_1_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
