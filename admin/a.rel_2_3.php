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

	$aDns = array();

	$db->query("
SELECT
	cli.id,
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
	log.data_hora >= '$pPeriodo_fr 00:00:00' AND
    log.data_hora <= '$pPeriodo_to 23:59:59' AND
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
				id IN (SELECT id_clienteusuario FROM gelic_log_login WHERE tipo = 2)
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
	while ($db->nextRecord())
	{
		$t = $db->f("dn");
		if ($t == 232323) $t = "POOL PR";
		else if ($t == 242424) $t = "POOL RS";
		else if ($t == 252525) $t = "POOL DF";
		else if ($t == 262626) $t = "POOL MG";
		else if ($t == 272727) $t = "POOL SP";

		$db->query("
SELECT COUNT(*) AS total FROM
	gelic_log_login AS lg
WHERE
	lg.tipo IN (2,3,4) AND
	lg.data_hora >= '$pPeriodo_fr 00:00:00' AND
	lg.data_hora <= '$pPeriodo_to 23:59:59' AND
	(
		(lg.tipo = 2 AND lg.id_clienteusuario = ".$db->f("id").") OR
		(lg.tipo = 3 AND lg.id_clienteusuario IN (SELECT id FROM gelic_clientes WHERE id_parent = ".$db->f("id").")) OR
		(lg.tipo = 4 AND lg.id_clienteusuario IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = ".$db->f("id")."))
	)",1);
		$db->nextRecord(1);

		$aDns[] = array("dn"=>$t, "nome"=>utf8_encode($db->f("nome")), "regiao"=>utf8_encode($db->f("regiao")), "acessos"=>intval($db->f("total",1)));
	}


	foreach ($aDns as $k => $v)
		$acessos[$k] = $v["acessos"];

	array_multisort($acessos, SORT_DESC, $aDns, SORT_NUMERIC);


	if ($pFormato == "xlsx")
	{
		require_once "../../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Volume de acessos por DN")
			->setSubject("GELIC")
			->setDescription("Volume de acessos por DN")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Relatorio");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Período escolhido ('.mysqlToBr($pPeriodo_fr).' - '.mysqlToBr($pPeriodo_to).')');


		$row += 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'DN')
			->setCellValue("B$row", 'Nome do DN')
			->setCellValue("C$row", 'Região')
			->setCellValue("D$row", 'Acessos');

		$phpexcel->getActiveSheet()->getStyle("A$row:D$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A$row:D$row")->getFont()->setBold(true);

		for ($i=0; $i<count($aDns); $i++)
		{
			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $aDns[$i]["dn"])
				->setCellValue("B$row", $aDns[$i]["nome"])
				->setCellValue("C$row", $aDns[$i]["regiao"])
				->setCellValue("D$row", $aDns[$i]["acessos"]);
		}


		$phpexcel->getActiveSheet()->setTitle('Quantidade de acessos por ano');
		$phpexcel->getActiveSheet()->mergeCells("A1:D1");
		$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
		$phpexcel->getActiveSheet()->getStyle("A3:D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

		if (file_exists(UPLOAD_DIR."~rel_2_3_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~rel_2_3_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~rel_2_3_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
