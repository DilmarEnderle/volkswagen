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
			->setTitle("Resultados por Item")
			->setSubject("Resultados")
			->setDescription("Resultados por Item GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Resultados");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Licitação')
			->setCellValue("B$row", 'Lote')
			->setCellValue("C$row", 'Item')
			->setCellValue("D$row", 'Razão Social')
			->setCellValue("E$row", 'CNPJ')
			->setCellValue("F$row", 'DN Venda')
			->setCellValue("G$row", 'Fabricante')
			->setCellValue("H$row", 'Modelo')
			->setCellValue("I$row", 'R$ Valor Final')
			->setCellValue("J$row", 'Vencedor');

		$db->query("
SELECT 
	lic.id AS id_licitacao,
    lot.lote,
    itm.item,
    par.razao_social,
    par.cnpj,
    par.dn_venda,
    par.fabricante,
    par.modelo,
    par.valor_final,
    par.vencedor
FROM
	gelic_licitacoes_itens_participantes AS par
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = par.id_item
    INNER JOIN gelic_licitacoes AS lic ON lic.id = itm.id_licitacao
    INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
ORDER BY
	lic.id, lot.lote, itm.item, par.razao_social");
		while ($db->nextRecord())
		{
			$vencedor = "Não";
			if ($db->f("vencedor") > 0) $vencedor = "Sim";

			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $db->f("id_licitacao"))
				->setCellValue("B$row", utf8_encode($db->f("lote")))
				->setCellValue("C$row", utf8_encode($db->f("item")))
				->setCellValue("D$row", utf8_encode($db->f("razao_social")))
				->setCellValue("E$row", $db->f("cnpj"))
				->setCellValue("F$row", utf8_encode($db->f("dn_venda")))
				->setCellValue("G$row", utf8_encode($db->f("fabricante")))
				->setCellValue("H$row", utf8_encode($db->f("modelo")))
				->setCellValue("I$row", number_format($db->f("valor_final"),2,".",","))
				->setCellValue("J$row", $vencedor);
		}

		$phpexcel->getActiveSheet()->setTitle('Resultados por Item');
		$phpexcel->getActiveSheet()->getStyle("A1:J1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A1:J1")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(14);
		$phpexcel->getActiveSheet()->getStyle("I2:I$row")->getNumberFormat()->setFormatCode('[$R$-416] #.##0,00;[RED]-[$R$-416] #.##0,00');
		$phpexcel->getActiveSheet()->getStyle("I1:I$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$phpexcel->getActiveSheet()->getStyle("J1:J$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A1:C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("E1:F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


		if (file_exists(UPLOAD_DIR."~participantes_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~participantes_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~participantes_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
