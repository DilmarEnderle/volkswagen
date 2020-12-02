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
		$db->query("SELECT DATE(data_hora) AS dt FROM gelic_vw.gelic_licitacoes_apl_historico WHERE tipo = 1 ORDER BY id LIMIT 1");
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

	$pPeriodo_fr_mo = substr($pPeriodo_fr,5,2);
	$pPeriodo_fr_yr = substr($pPeriodo_fr,0,4);

	$pPeriodo_to_mo = substr($pPeriodo_to,5,2);
	$pPeriodo_to_yr = substr($pPeriodo_to,0,4);


	$aMonths = array();

	$mes = $pPeriodo_fr_mo;
	$ano = $pPeriodo_fr_yr;
	$current_mo = intval($pPeriodo_fr_yr.str_pad($pPeriodo_fr_mo,2,"0",STR_PAD_LEFT));
	$stop_mo = intval($pPeriodo_to_yr.str_pad($pPeriodo_to_mo,2,"0",STR_PAD_LEFT));
	while ($current_mo <= $stop_mo)
	{
		$first_of_month = $ano."-".str_pad($mes,2,"0",STR_PAD_LEFT)."-01 00:00:00";
		$last_of_month = $ano."-".str_pad($mes,2,"0",STR_PAD_LEFT)."-".cal_days_in_month(CAL_GREGORIAN, $mes, $ano)." 23:59:59";

		$aApls = array();
		$db->query("
SELECT * FROM
(
SELECT
	apl.id_licitacao,
	apl.quantidade_veiculos,
	ahis.tipo AS apl_status,
	ahis.data_hora AS apl_status_data_hora,
	ahis.texto,
	itm.item,
	IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome_dn,
	CONCAT(apl.id_licitacao,IF (cli.id_parent > 0, cli.id_parent, cli.id),apl.id_item) AS grp,
	(SELECT data_hora FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS datah
FROM
	gelic_licitacoes_apl AS apl
	INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_apl_historico AS ahis ON ahis.id_apl = apl.id AND ahis.id = (SELECT MAX(id) FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id)
WHERE
	apl.id = (
		SELECT
			MAX(id)
		FROM
			gelic_licitacoes_apl
		WHERE
			id_licitacao = apl.id_licitacao AND
			id_item = apl.id_item AND
			id_cliente = apl.id_cliente
		)
ORDER BY
	datah DESC
) AS m
GROUP BY
	grp
HAVING
	datah >= '".$first_of_month."' AND
	datah <= '".$last_of_month."'
ORDER BY
	datah");
		if ($pDetalhamento == 1)
		{
			while ($db->nextRecord())
			{
				$atuada = '';

				$tempo = '';
				if ($db->f("apl_status") == 2 || $db->f("apl_status") == 4)
				{
					$atuada = $db->f("apl_status_data_hora");

					$d1 = strtotime($db->f("datah"));
					$d2 = strtotime($db->f("apl_status_data_hora"));
					$a = segundosConv($d2 - $d1);
					$tempo = $a["d"].'d '.$a["h"].'h '.$a["m"].'m '.$a["s"].'s';
				}

				$motivo = '';
				if ($db->f("apl_status") == 4)
					$motivo = utf8_encode($db->f("texto"));

				$aApls[] = array("licitacao"=>$db->f("id_licitacao"), "item"=>utf8_encode($db->f("item")), "quantidade_veiculos"=>$db->f("quantidade_veiculos"), "nome_dn"=>utf8_encode($db->f("nome_dn")), "enviada_em"=>$db->f("datah"), "apl_status"=>$db->f("apl_status"), "atuada_em"=>$atuada, "tempo"=>$tempo, "motivo"=>$motivo);
			}
		}


		$aMonths[] = array("mes"=>str_pad($mes,2,"0",STR_PAD_LEFT),"ano"=>$ano, "quantidade"=>$db->nf(), "apls"=>$aApls);
		$mes += 1;
		if ($mes > 12)
		{
			$mes = 1;
			$ano += 1;
		}
		$current_mo = intval($ano.str_pad($mes,2,"0",STR_PAD_LEFT));
	}


	if ($pFormato == "xlsx")
	{
		require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("APLs enviadas por mês")
			->setSubject("GELIC")
			->setDescription("APLs enviadas por mês")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Relatorio");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Período escolhido ('.mysqlToBr($pPeriodo_fr).' - '.mysqlToBr($pPeriodo_to).')');

		$row += 3;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Mês/Ano')
			->setCellValue("B$row", 'APLs Enviadas');

		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ff888888');
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->getColor()->setRGB('ffffff');


		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A2", 'Total de APLs no período: 0');

		$total = 0;
		for ($i=0; $i<count($aMonths); $i++)
		{
			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $aMonths[$i]["mes"].'/'.$aMonths[$i]["ano"])
				->setCellValue("B$row", $aMonths[$i]["quantidade"]);

			$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);

			$total += count($aMonths[$i]["apls"]);

			if ($pDetalhamento == 1)
			{
				$aApl_status = array();
				$aAPL_status[1] = 'Aguardando atuação';
				$aAPL_status[2] = 'APROVADA';
				$aAPL_status[4] = 'REPROVADA';
				$aAPL_status[5] = 'Aguardando atuação';
				$aAPL_status[6] = 'Aguardando atuação';
				

				//listar apls do mes
				if (count($aMonths[$i]["apls"]) > 0)
				{
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", 'Licitação')
						->setCellValue("B$row", 'Item')
						->setCellValue("C$row", 'Qtde. Veículos')
						->setCellValue("D$row", 'DN que enviou')
						->setCellValue("E$row", 'APL enviada em')
						->setCellValue("F$row", 'Status da APL')
						->setCellValue("G$row", 'Atuada em')
						->setCellValue("H$row", 'Tempo de atuação')
						->setCellValue("I$row", 'Motivo de Reprovação');

					$phpexcel->getActiveSheet()->getStyle("A$row:I$row")->getFont()->setBold(true);
					$phpexcel->getActiveSheet()->getStyle("A$row:I$row")->getFont()->setItalic(true);

					for ($j=0; $j<count($aMonths[$i]["apls"]); $j++)
					{
						$row += 1;
						$phpexcel->setActiveSheetIndex(0)
							->setCellValue("A$row", $aMonths[$i]["apls"][$j]["licitacao"])
							->setCellValue("B$row", $aMonths[$i]["apls"][$j]["item"])
							->setCellValue("C$row", $aMonths[$i]["apls"][$j]["quantidade_veiculos"])
							->setCellValue("D$row", $aMonths[$i]["apls"][$j]["nome_dn"])
							->setCellValue("E$row", mysqlToBr(substr($aMonths[$i]["apls"][$j]["enviada_em"],0,10))." ".substr($aMonths[$i]["apls"][$j]["enviada_em"],11))
							->setCellValue("F$row", $aAPL_status[$aMonths[$i]["apls"][$j]["apl_status"]])
							->setCellValue("G$row", $aMonths[$i]["apls"][$j]["atuada_em"])
							->setCellValue("H$row", $aMonths[$i]["apls"][$j]["tempo"])
							->setCellValue("I$row", $aMonths[$i]["apls"][$j]["motivo"]);
					}

					$row += 1;
				}
			}
		}

		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A2", 'Total de APLs no período: '.$total);


		$phpexcel->getActiveSheet()->setTitle('APLs enviadas por mês');

		if ($pDetalhamento == 1)
		{
			$phpexcel->getActiveSheet()->mergeCells("A1:D1");
			$phpexcel->getActiveSheet()->mergeCells("A2:D2");
			$phpexcel->getActiveSheet()->getStyle("A1:A2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getStyle("A2")->getFont()->setItalic(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
			$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
			$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(50);
			$phpexcel->getActiveSheet()->getStyle("A4:I$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
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

		if (file_exists(UPLOAD_DIR."~bo_rel_3_1_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~bo_rel_3_1_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~bo_rel_3_1_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
