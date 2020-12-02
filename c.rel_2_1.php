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
		$db->query("SELECT DATE(data_hora) AS dt FROM gelic_log_login ORDER BY id LIMIT 1");
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

	//AJUSTAR DATA PERIODO DE PARA O INICIO DO MES
	$pPeriodo_fr = $pPeriodo_fr_yr."-".$pPeriodo_fr_mo."-01";
	
	//AJUSTAR DATA PERIODO ATE PARA O FINAL DO MES
	$pPeriodo_to = $pPeriodo_to_yr."-".$pPeriodo_to_mo."-".cal_days_in_month(CAL_GREGORIAN, $pPeriodo_to_mo, $pPeriodo_to_yr);


	$aMonths = array();

	$mes = $pPeriodo_fr_mo;
	$ano = $pPeriodo_fr_yr;
	$current_mo = intval($pPeriodo_fr_yr.str_pad($pPeriodo_fr_mo,2,"0",STR_PAD_LEFT));
	$stop_mo = intval($pPeriodo_to_yr.str_pad($pPeriodo_to_mo,2,"0",STR_PAD_LEFT));
	while ($current_mo <= $stop_mo)
	{
		$db->query("
SELECT
	cli.dn,
	cli.nome,
	uf.regiao
FROM
	gelic_log_login AS log
    INNER JOIN gelic_clientes AS cli ON cli.id = log.id_clienteusuario
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	log.tipo IN (2,3,4) AND
	MONTH(log.data_hora) = $mes AND
	YEAR(log.data_hora) = $ano AND
    cli.adve > 0 AND
    cli.tipo = 2 AND
	(
		cli.id IN (
			SELECT
				id
			FROM
				gelic_clientes
			WHERE
				tipo = 2 AND
				id IN (SELECT id_clienteusuario FROM gelic_log_login WHERE tipo = 2 AND MONTH(log.data_hora) = $mes AND YEAR(log.data_hora) = $ano)
			GROUP BY
				id
		) OR
		cli.id IN (
			SELECT
				id_parent
			FROM
				gelic_clientes
			WHERE
				tipo = 3 AND
				id IN (SELECT id_clienteusuario FROM gelic_log_login WHERE tipo = 3)
			GROUP BY
				id_parent
        ) OR
		cli.id IN (
			SELECT
				acesso.id_cliente_acesso
			FROM
				gelic_clientes AS cliente
				INNER JOIN gelic_clientes_acesso AS acesso ON acesso.id_cliente = cliente.id
			WHERE
				cliente.tipo = 4 AND
				cliente.id IN (SELECT id_clienteusuario FROM gelic_log_login WHERE tipo = 4)
			GROUP BY
				acesso.id_cliente_acesso
        )
	)
GROUP BY
	cli.id");

	
		$aDns = array();
		if ($pDetalhamento == 1)
		{
			while ($db->nextRecord())
			{
				$t = $db->f("dn");
				if ($t == 232323) $t = "POOL PR";
				else if ($t == 242424) $t = "POOL RS";
				else if ($t == 252525) $t = "POOL DF";
				else if ($t == 262626) $t = "POOL MG";
				else if ($t == 272727) $t = "POOL SP";

				$aDns[] = array("dn"=>$t, "nome"=>utf8_encode($db->f("nome")), "regiao"=>utf8_encode($db->f("regiao")));
			}
		}

		$aMonths[] = array("mes"=>str_pad($mes,2,"0",STR_PAD_LEFT), "ano"=>$ano, "quantidade"=>$db->nf(), "dns"=>$aDns);

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
			->setTitle("Quantidade de acessos por mês")
			->setSubject("GELIC")
			->setDescription("Quantidade de acessos por mês")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Relatorio");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Período escolhido ('.mysqlToBr($pPeriodo_fr).' - '.mysqlToBr($pPeriodo_to).')');

		$row += 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Mês/Ano')
			->setCellValue("B$row", 'Acessos');

		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ff888888');
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->getColor()->setRGB('ffffff');


		for ($i=0; $i<count($aMonths); $i++)
		{
			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $aMonths[$i]["mes"].'/'.$aMonths[$i]["ano"])
				->setCellValue("B$row", $aMonths[$i]["quantidade"]);

			$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);


			if ($pDetalhamento == 1)
			{
				//listar dns que aderiram no mes
				if (count($aMonths[$i]["dns"]) > 0)
				{
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", 'DN')
						->setCellValue("B$row", 'Nome do DN')
						->setCellValue("C$row", 'Região');

					$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setBold(true);
					$phpexcel->getActiveSheet()->getStyle("A$row:C$row")->getFont()->setItalic(true);

					for ($j=0; $j<count($aMonths[$i]["dns"]); $j++)
					{
						$row += 1;
						$phpexcel->setActiveSheetIndex(0)
							->setCellValue("A$row", $aMonths[$i]["dns"][$j]["dn"])
							->setCellValue("B$row", $aMonths[$i]["dns"][$j]["nome"])
							->setCellValue("C$row", $aMonths[$i]["dns"][$j]["regiao"]);
					}

					$row += 1;
				}
			}
		}


		$phpexcel->getActiveSheet()->setTitle('Quantidade de acessos por mês');

		if ($pDetalhamento == 1)
		{
			$phpexcel->getActiveSheet()->mergeCells("A1:C1");
			$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$phpexcel->getActiveSheet()->getStyle("A3:C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		}
		else
		{
			$phpexcel->getActiveSheet()->mergeCells("A1:B1");
			$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(28);
			$phpexcel->getActiveSheet()->getStyle("A3:C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		}

		if (file_exists(UPLOAD_DIR."~bo_rel_2_1_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~bo_rel_2_1_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~bo_rel_2_1_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
