<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFormato = trim($_POST["formato"]);
	$db = new Mysql();

	if ($pFormato == "xlsx")
	{
		require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Total de DNs")
			->setSubject("GELIC")
			->setDescription("Total de DNs")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Relatorio");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);


		$db->query("SELECT cli.dn, cli.nome, uf.regiao FROM gelic_clientes AS cli INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf WHERE cli.tipo = 2 AND cli.adve > 0");
		$total_dns = $db->nf();

		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'TOTAL DNs')
			->setCellValue("B$row", $total_dns)
			->setCellValue("C$row", '');

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'DN')
			->setCellValue("B$row", 'Nome do DN')
			->setCellValue("C$row", 'Região');


		while ($db->nextRecord())
		{
			$row += 1;

			$t = $db->f("dn");
			if ($t == 232323) $t = "POOL PR";
			else if ($t == 242424) $t = "POOL RS";
			else if ($t == 252525) $t = "POOL DF";
			else if ($t == 262626) $t = "POOL MG";
			else if ($t == 272727) $t = "POOL SP";

			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $t)
				->setCellValue("B$row", utf8_encode($db->f("nome")))
				->setCellValue("C$row", utf8_encode($db->f("regiao")));
		}

		$phpexcel->getActiveSheet()->setTitle('Total de DNs');
		$phpexcel->getActiveSheet()->getStyle("A1:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("B1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$phpexcel->getActiveSheet()->getStyle("A2:C2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:C2")->getFont()->setBold(true);

		$phpexcel->getActiveSheet()->getStyle("B2:C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

		if (file_exists(UPLOAD_DIR."~bo_rel_1_1_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~bo_rel_1_1_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~bo_rel_1_1_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
