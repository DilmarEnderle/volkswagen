<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFormato = trim($_POST["formato"]);
	$db = new Mysql();


	$aTotal = array();
	$aCom_acessos = array();
	$aSem_acessos = array();
	$aInativos = array();

	//TOTAL
	$db->query("SELECT cli.dn, cli.nome, uf.regiao FROM gelic_clientes AS cli INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf WHERE cli.id > 1 AND cli.tipo = 2 ORDER BY cli.dn");
	while ($db->nextRecord())
		$aTotal[] = array("dn"=>$db->f("dn"), "nome"=>$db->f("nome"), "regiao"=>$db->f("regiao"));

	//COM ACESSOS
	$db->query("
SELECT
	cli.id,
	cli.dn,
	cli.nome,
	uf.regiao
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	cli.id > 1 AND
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
	cli.id
ORDER BY
	cli.dn");
	while ($db->nextRecord())
		$aCom_acessos[] = array("id"=>$db->f("id"), "dn"=>$db->f("dn"), "nome"=>$db->f("nome"), "regiao"=>$db->f("regiao"));

	//SEM ACESSOS
	$db->query("SELECT cli.dn, cli.nome, uf.regiao FROM gelic_clientes AS cli INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf WHERE cli.id > 1 AND cli.tipo = 2 AND cli.id NOT IN (".implode(",", array_column($aCom_acessos, "id")).") ORDER BY cli.dn");
	while ($db->nextRecord())
		$aSem_acessos[] = array("dn"=>$db->f("dn"), "nome"=>$db->f("nome"), "regiao"=>$db->f("regiao"));


	//INATIVOS
	$aUltimos_30_dias = array();
	$db->query("
SELECT
	IF (cli.tipo = 2, cli.id, cli.id_parent) AS id_dn
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
WHERE
	ahis.tipo = 1 AND
    ahis.data_hora > DATE_SUB(NOW(), INTERVAL 31 DAY)
GROUP BY
	id_dn");
	while ($db->nextRecord())
		$aUltimos_30_dias[] = $db->f("id_dn");

	$aBuscar_inativos = array_diff(array_column($aCom_acessos, "id"), $aUltimos_30_dias);

	$db->query("SELECT cli.dn, cli.nome, uf.regiao FROM gelic_clientes AS cli INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf WHERE cli.id > 1 AND cli.tipo = 2 AND cli.id IN (".implode(",", $aBuscar_inativos).") ORDER BY cli.dn");
	while ($db->nextRecord())
		$aInativos[] = array("dn"=>$db->f("dn"), "nome"=>$db->f("nome"), "regiao"=>$db->f("regiao"));


	if ($pFormato == "xlsx")
	{
		require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Acessos de DNs")
			->setSubject("Acessos")
			->setDescription("Acessos de DNs GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Acessos");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);


		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'GRUPO')
			->setCellValue("B$row", 'QUANTIDADE');

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", "Total")
			->setCellValue("B$row", count($aTotal));

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", "Com acessos")
			->setCellValue("B$row", count($aCom_acessos));

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", "Sem acessos")
			->setCellValue("B$row", count($aSem_acessos));

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", "Inativos")
			->setCellValue("B$row", count($aInativos));



		$row += 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'TOTAL');

		$merge_row_total = $row;

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'DN')
			->setCellValue("E$row", 'NOME')
			->setCellValue("F$row", 'REGIÃO');


		for ($i=0; $i<count($aTotal); $i++)
		{
			$t = $aTotal[$i]["dn"];
			if ($t == 232323) $t = "POOL PR";
			else if ($t == 242424) $t = "POOL RS";
			else if ($t == 252525) $t = "POOL DF";
			else if ($t == 262626) $t = "POOL MG";
			else if ($t == 272727) $t = "POOL SP";

			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("D$row", $t)
				->setCellValue("E$row", utf8_encode($aTotal[$i]["nome"]))
				->setCellValue("F$row", utf8_encode($aTotal[$i]["regiao"]));
		}



		$row += 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'COM ACESSOS');

		$merge_row_com_acessos = $row;

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'DN')
			->setCellValue("E$row", 'NOME')
			->setCellValue("F$row", 'REGIÃO');


		for ($i=0; $i<count($aCom_acessos); $i++)
		{
			$t = $aCom_acessos[$i]["dn"];
			if ($t == 232323) $t = "POOL PR";
			else if ($t == 242424) $t = "POOL RS";
			else if ($t == 252525) $t = "POOL DF";
			else if ($t == 262626) $t = "POOL MG";
			else if ($t == 272727) $t = "POOL SP";

			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("D$row", $t)
				->setCellValue("E$row", utf8_encode($aCom_acessos[$i]["nome"]))
				->setCellValue("F$row", utf8_encode($aCom_acessos[$i]["regiao"]));
		}


		$row += 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'SEM ACESSOS');

		$merge_row_sem_acessos = $row;

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'DN')
			->setCellValue("E$row", 'NOME')
			->setCellValue("F$row", 'REGIÃO');


		for ($i=0; $i<count($aSem_acessos); $i++)
		{
			$t = $aSem_acessos[$i]["dn"];
			if ($t == 232323) $t = "POOL PR";
			else if ($t == 242424) $t = "POOL RS";
			else if ($t == 252525) $t = "POOL DF";
			else if ($t == 262626) $t = "POOL MG";
			else if ($t == 272727) $t = "POOL SP";

			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("D$row", $t)
				->setCellValue("E$row", utf8_encode($aSem_acessos[$i]["nome"]))
				->setCellValue("F$row", utf8_encode($aSem_acessos[$i]["regiao"]));
		}



		$row += 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'INATIVOS');

		$merge_row_inativos = $row;

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("D$row", 'DN')
			->setCellValue("E$row", 'NOME')
			->setCellValue("F$row", 'REGIÃO');


		for ($i=0; $i<count($aInativos); $i++)
		{
			$t = $aInativos[$i]["dn"];
			if ($t == 232323) $t = "POOL PR";
			else if ($t == 242424) $t = "POOL RS";
			else if ($t == 252525) $t = "POOL DF";
			else if ($t == 262626) $t = "POOL MG";
			else if ($t == 272727) $t = "POOL SP";

			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("D$row", $t)
				->setCellValue("E$row", utf8_encode($aInativos[$i]["nome"]))
				->setCellValue("F$row", utf8_encode($aInativos[$i]["regiao"]));
		}

		$phpexcel->getActiveSheet()->mergeCells("D$merge_row_total:F$merge_row_total");
		$phpexcel->getActiveSheet()->mergeCells("D$merge_row_com_acessos:F$merge_row_com_acessos");
		$phpexcel->getActiveSheet()->mergeCells("D$merge_row_sem_acessos:F$merge_row_sem_acessos");
		$phpexcel->getActiveSheet()->mergeCells("D$merge_row_inativos:F$merge_row_inativos");
		$phpexcel->getActiveSheet()->setTitle('Acessos de DNs');
		$phpexcel->getActiveSheet()->getStyle("A1:A5")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("B1:B5")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A1:B1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A1:B$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$phpexcel->getActiveSheet()->mergeCells("C2:F2");
		$phpexcel->getActiveSheet()->mergeCells("C3:F3");
		$phpexcel->getActiveSheet()->mergeCells("C4:F4");
		$phpexcel->getActiveSheet()->mergeCells("C5:F5");

		$phpexcel->setActiveSheetIndex(0)->setCellValue("C2", 'Quantidade total de DNs cadastrados');
		$phpexcel->setActiveSheetIndex(0)->setCellValue("C3", 'Quantidade de DNs que acessaram, ao menos, uma vez o sistema');
		$phpexcel->setActiveSheetIndex(0)->setCellValue("C4", 'Quantidade de DNs que nunca acessaram o sistema');
		$phpexcel->setActiveSheetIndex(0)->setCellValue("C5", 'Quantidade de DNs que não enviam APL a mais de 30 dias - inclui somente DNs com acessos');

		$phpexcel->getActiveSheet()->getStyle("C2")->getFont()->setItalic(true);
		$phpexcel->getActiveSheet()->getStyle("C3")->getFont()->setItalic(true);
		$phpexcel->getActiveSheet()->getStyle("C4")->getFont()->setItalic(true);
		$phpexcel->getActiveSheet()->getStyle("C5")->getFont()->setItalic(true);

		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);

		$phpexcel->getActiveSheet()->getStyle("D$merge_row_total")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_total")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_total")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_total+1).":F".($merge_row_total+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_total+1).":F".($merge_row_total+1))->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_total+1).":F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

		$phpexcel->getActiveSheet()->getStyle("D$merge_row_com_acessos")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_com_acessos")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_com_acessos")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_com_acessos+1).":F".($merge_row_com_acessos+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_com_acessos+1).":F".($merge_row_com_acessos+1))->getFont()->setBold(true);

		$phpexcel->getActiveSheet()->getStyle("D$merge_row_sem_acessos")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_sem_acessos")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_sem_acessos")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_sem_acessos+1).":F".($merge_row_sem_acessos+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_sem_acessos+1).":F".($merge_row_sem_acessos+1))->getFont()->setBold(true);

		$phpexcel->getActiveSheet()->getStyle("D$merge_row_inativos")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_inativos")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D$merge_row_inativos")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_inativos+1).":F".($merge_row_inativos+1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("D".($merge_row_inativos+1).":F".($merge_row_inativos+1))->getFont()->setBold(true);


		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);


		if (file_exists(UPLOAD_DIR."~acessos_dns_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~acessos_dns_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~acessos_dns_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
