<?php

$gKey = "";
if (isset($_GET["k"]))
	$gKey = $_GET["k"];

if ($gKey <> "9837d3f9cc7d39b8c83690de79ca0924")
{
	echo "Acesso Negado.";
	exit;
}

// ------------------------------------------------------
// Lista logins efetuados por clientes tipo 2 (DNs)
// por data decrescente e último login do cliente no dia
// ------------------------------------------------------

require_once "include/config.php";
require_once "include/essential.php";
require_once "../Phpexcel-1.8.0/PHPExcel.php";

$hoje = date("Ymd_His");

$db = new Mysql();

$phpexcel = new PHPExcel();
$phpexcel->getProperties()->setCreator("GELIC")
						->setLastModifiedBy("GELIC")
						->setTitle("Acessos dos Usuários")
						->setSubject("Logins")
						->setDescription("Acessos no sistema GELIC")
						->setKeywords("office 2007 openxml php gelic")
						->setCategory("Logins");
							 
$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
$phpexcel->getDefaultStyle()->getFont()->setSize(10);


$row = 1;
$phpexcel->setActiveSheetIndex(0)
		->setCellValue("A$row", 'Data')
		->setCellValue("B$row", 'Hora')
		->setCellValue("C$row", 'Nome do DN')
		->setCellValue("D$row", 'N° do DN')
		->setCellValue("E$row", 'IP')
		->setCellValue("F$row", 'Região');

$db->query("SELECT DATE(data_hora) AS data, DAYOFWEEK(data_hora) AS diasemana FROM gelic_log_login GROUP BY data ORDER BY data_hora DESC");
while ($db->nextRecord())
{
	if ($db->Row[0] % 2 == 0)
		$cd = "f0f0f0";
	else
		$cd = "e0e0e0";

	$dData = $db->f("data");

	$db->query("
SELECT 
	log.data_hora, 
	log.ip,
	log.tipo,
	cli.nome,
	cli.dn,
	cli.id_parent,
	clidn.dn AS parent_dn,
	uf.regiao,
	uf.uf,
	(SELECT MAX(data_hora) FROM gelic_log_login WHERE DATE(data_hora) = '$dData' AND id_clienteusuario = cli.id AND tipo IN (2,3)) AS dh
FROM 
	gelic_log_login AS log,
	gelic_clientes AS cli
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE 
	log.tipo IN (2,3) AND
	cli.id = log.id_clienteusuario AND
	DATE(data_hora) = '$dData'
GROUP BY 
	log.id_clienteusuario 
ORDER BY 
	cli.nome",1);
	while ($db->nextRecord(1))
	{
		if ($db->f("tipo",1) == 3)
			$dn = $db->f("parent_dn",1);
		else
			$dn = $db->f("dn",1);

		if (in_array($dn, array(232323,242424,252525,262626,272727)))
			$dn = 'POOL '.$db->f("uf",1);

		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", mysqlToBr(substr($db->f("dh",1), 0, 10)))
			->setCellValue("B$row", substr($db->f("dh",1), 11))
			->setCellValue("C$row", utf8_encode($db->f("nome",1)))
			->setCellValue("D$row", $dn)
			->setCellValue("E$row", $db->f("ip",1))
			->setCellValue("F$row", utf8_encode($db->f("regiao",1)));
	}
}

$phpexcel->getActiveSheet()->setTitle('Acessos');
$phpexcel->getActiveSheet()->getStyle("A1:F1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$phpexcel->getActiveSheet()->getStyle("A1:F1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
$phpexcel->getActiveSheet()->getStyle("A1:F1")->getFont()->setBold(true);
$phpexcel->getActiveSheet()->getStyle("A2:B$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$phpexcel->getActiveSheet()->getStyle("D2:F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$phpexcel->getActiveSheet()->getStyle("D2:D$row")->getFont()->getColor()->setARGB("ffff0000");
$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
$phpexcel->getActiveSheet()->setAutoFilter("A1:F$row");

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Acessos_gelic_'.$hoje.'.xlsx"');
header('Cache-Control: max-age=0');
$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
$obwriter->save('php://output');
exit;

?>
