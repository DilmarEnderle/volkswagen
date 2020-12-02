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
		$pPeriodo_fr = brToMysql($pPeriodo_fr); // yyyy-mm-dd
	else
	{
		$db->query("SELECT DATE(MIN(data_hora)) AS dt FROM gelic_licitacoes");
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


	if ($pFormato == "xlsx")
	{
		require_once "../../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Licitações por Data de Insersão")
			->setSubject("GELIC")
			->setDescription("Licitações por Data de Insersão")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Relatorio");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);
		
		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Período escolhido ('.mysqlToBr($pPeriodo_fr).' - '.mysqlToBr($pPeriodo_to).')');

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Licitação')
			->setCellValue("B$row", 'Data/Hora Cadastro')
			->setCellValue("C$row", 'Órgão Público')
			->setCellValue("D$row", 'Data/Hora Abertura')
			->setCellValue("E$row", 'Nº da Licitação')
			->setCellValue("F$row", 'Quem Cadastrou')
			->setCellValue("G$row", 'Status');

		$db->query("
SELECT
	lic.id,
	lic.orgao,
	lic.data_hora,
	lic.datahora_abertura,
	lic.numero,
	lic.fase,
	usr.nome,
	labas.id_status
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 11
	INNER JOIN gelic_admin_usuarios AS usr ON usr.id = his.id_sender
	INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 1
WHERE
	lic.deletado = 0 AND
	lic.data_hora BETWEEN '".$pPeriodo_fr." 00:00:00' AND '".$pPeriodo_to." 23:59:59'
ORDER BY
	lic.data_hora DESC");
		while ($db->nextRecord())
		{
			$status = '';
			if (in_array($db->f("id_status"), array(8,19))) // APL Aprovada, APL Reprovada
			{
				$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = ".$db->f("id"),1);
				$db->nextRecord(1);

				if ($db->f("fase") == 1)
				{
					if ($db->f("enviadas",1) > 0)
						$status .= 'APL em Análise - GELIC ('.$db->f("enviadas",1).'), ';
				}
				else
				{
					if ($db->f("enviadas",1) > 0)
						$status .= 'APL Aguardando Aprovação ('.$db->f("enviadas",1).'), ';
				}

				if ($db->f("aprovadas",1) > 0)
					$status .= 'APL Aprovada ('.$db->f("aprovadas",1).'), ';

				if ($db->f("reprovadas",1) > 0)
					$status .= 'APL Reprovada ('.$db->f("reprovadas",1).'), ';
			}
			else
			{
				$db->query("SELECT descricao FROM gelic_status WHERE id = ".$db->f("id_status"),1);
				$db->nextRecord(1);
				$status = utf8_encode($db->f("descricao",1));
			}

			$status = preg_replace('/, $/', '', $status);


			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $db->f("id"))
				->setCellValue("B$row", mysqlToBr(substr($db->f("data_hora"),0,10))." ".substr($db->f("data_hora"),11))
				->setCellValue("C$row", utf8_encode($db->f("orgao")))
				->setCellValue("D$row", mysqlToBr(substr($db->f("datahora_abertura"),0,10))." ".substr($db->f("datahora_abertura"),11))
				->setCellValue("E$row", utf8_encode($db->f("numero")))
				->setCellValue("F$row", utf8_encode($db->f("nome")))
				->setCellValue("G$row", $status);

		}

		$phpexcel->getActiveSheet()->mergeCells("A1:G1");
		$phpexcel->getActiveSheet()->getStyle("A1:A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);		
		$phpexcel->getActiveSheet()->getStyle("A2:G2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:G2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A2:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("B2:F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("G2:G$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(46);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(46);

		if (file_exists(UPLOAD_DIR."~rel_4_5_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~rel_4_5_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~rel_4_5_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
