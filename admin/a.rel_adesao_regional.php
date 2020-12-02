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

	$aDNs = array();
	$aRegiao = array();
	$aRegiao[] = array("r"=>"Região 1/2", "q"=>0, "bc"=>1);
	$aRegiao[] = array("r"=>"Região 3", "q"=>0, "bc"=>3);
	$aRegiao[] = array("r"=>"Região 4", "q"=>0, "bc"=>4);
	$aRegiao[] = array("r"=>"Região 5", "q"=>0, "bc"=>5);
	$aRegiao[] = array("r"=>"Região 6", "q"=>0, "bc"=>6);

	$db = new Mysql();


	//VALIDAR DATA PERIODO DE
	if (strlen($pPeriodo_fr) == 10 && isValidBrDate($pPeriodo_fr))
		$pPeriodo_fr = brToUs($pPeriodo_fr); // mm/dd/yyyy
	else
	{
		$db->query("SELECT data_hora FROM gelic_log_login ORDER BY id LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_fr = mysqlToUs($db->f("data_hora"));
		else
			$pPeriodo_fr = date("m/d/Y");
	}


	//VALIDAR DATA PERIODO ATE
	if (strlen($pPeriodo_to) == 10 && isValidBrDate($pPeriodo_to))
		$pPeriodo_to = brToUs($pPeriodo_to); // mm/dd/yyyy
	else
		$pPeriodo_to = date("m/d/Y");


	//SE O PERIODO DE FOR MAIOR DO QUE O PERIODO ATE (INVERTER)
	if (intval(str_replace("-","",usToMysql($pPeriodo_fr))) > intval(str_replace("-","",usToMysql($pPeriodo_to))))
	{
		$t = $pPeriodo_to;
		$pPeriodo_to = $pPeriodo_fr;
		$pPeriodo_fr = $t;
	}


	$data_selecionada_fr = usToBr($pPeriodo_fr);
	$data_selecionada_to = usToBr($pPeriodo_to);


	$db->query("
SELECT 
	IF (clip.id > 0, clip.id, log.id_clienteusuario) AS dn,
	IF(uf.regiao_abv = 'REG 1/2', 0, IF(uf.regiao_abv = 'REG 03', 1, IF(uf.regiao_abv = 'REG 04', 2, IF(uf.regiao_abv = 'REG 05', 3, 4)))) AS reg
FROM 
	gelic_log_login AS log
	INNER JOIN gelic_clientes AS cli ON cli.id = log.id_clienteusuario
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	log.id_clienteusuario NOT IN (1,591) AND
	log.tipo IN (2,3) AND
	log.data_hora BETWEEN '".usToMysql($pPeriodo_fr)." 00:00:00' AND '".usToMysql($pPeriodo_to)." 23:59:59'
ORDER BY
	log.id");
	while ($db->nextRecord())
	{
		if (!in_array($db->f("dn"), $aDNs))
		{
			$aRegiao[$db->f("reg")]["q"] += 1;
			$aDNs[] = $db->f("dn");
		}
	}


	function compare_q($a, $b)
	{
	    if ($a["q"] == $b["q"]) return 0;
	    return ($a["q"] > $b["q"]) ? -1 : 1;
	}
	usort($aRegiao, "compare_q");

	$t = 0;
	for ($i=0; $i<count($aRegiao); $i++)
		$t += $aRegiao[$i]["q"];

	$aRegiao[] = array("r"=>"TOTAL", "q"=>$t, "bc"=>7);



	if ($pFormato == "xlsx")
	{
		require_once "../../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Adesão ao Sistema (Regional)")
			->setSubject("Acessos")
			->setDescription("Adesão ao Sistema GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Acessos");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A1", 'Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')');

		$row = 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Região')
			->setCellValue("B$row", 'Acessos');

		for ($i=0; $i<count($aRegiao); $i++)
		{
			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $aRegiao[$i]["r"])
				->setCellValue("B$row", $aRegiao[$i]["q"]);
		}

		$phpexcel->getActiveSheet()->mergeCells("A1:B1");
		$phpexcel->getActiveSheet()->setTitle('Adesão ao Sistema (Regional)');
		$phpexcel->getActiveSheet()->getStyle("A1:B$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A2:B2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:B2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

		if (file_exists(UPLOAD_DIR."~adesao_regional_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~adesao_regional_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~adesao_regional_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
	else if ($pFormato == "pdf")
	{
		$max = 0;
		for ($i=0; $i<count($aRegiao); $i++)
			if ($max < $aRegiao[$i]["q"]) { $max = $aRegiao[$i]["q"]; }

		$top_val = $max;

		while (!($max % 40) == 0) { $max += 1; }

		$tick_1 = $max / 4 * 1;
		$tick_2 = $max / 4 * 2;
		$tick_3 = $max / 4 * 3;
		$tick_4 = $max / 4 * 4;

		$rows = '';
		for ($i=0; $i<count($aRegiao); $i++)
		{
			if ($max == 0)
			{
				//$b1_width = 0;
				$b2_width = 0;
			}
			else
			{
				//$b1_width = round(600 * $aDia_total[$i]["novos"] / $max);
				$b2_width = round(600 * $aRegiao[$i]["q"] / $max);
			}
		
			//if ($b1_width < 30)
			//	$b1 = '<div class="bar-1" style="width:'.$b1_width.'px;"></div><span class="b1-text-out" style="left:'.(241+$b1_width+10).'px;">'.$aDia_total[$i]["novos"].'</span>';
			//else
			//	$b1 = '<div class="bar-1" style="width:'.$b1_width.'px;"><span>'.$aDia_total[$i]["novos"].'</span></div>';

			if ($b2_width < 30)
				$b2 = '<div class="bar bar-'.$aRegiao[$i]["bc"].'" style="width:'.$b2_width.'px;"></div><span class="text-out" style="left:'.(241+$b2_width+10).'px;">'.$aRegiao[$i]["q"].'</span>';
			else
				$b2 = '<div class="bar bar-'.$aRegiao[$i]["bc"].'" style="width:'.$b2_width.'px;"><span class="text-in">'.$aRegiao[$i]["q"].'</span></div>';

			$rows .= '<div class="chart-row">
				<div class="left-line"></div>
				<div class="mark-1"></div>
				<div class="mark-2"></div>
				<div class="mark-3"></div>
				<div class="mark-4"></div>
				'.$b2.'
				<span class="left-info-line-2">'.str_replace(" ","&nbsp;",$aRegiao[$i]["r"]).'</span>
			</div>';
		}

		$tHtml = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Adesão so sistema (Regional)</title>
	<style>
		body { font-family: \'Trebuchet MS\',\'Tahoma\'; font-size: 14px; color: #000000; }

		.title {
			text-align: center;
			font-size: 30px;
			font-weight: bold;
			overflow: hidden;
			}

		.sub-title {
			text-align: center;
			font-size: 12pt;
			overflow: hidden;
			padding-bottom: 20px;
			}

		.legenda {
			position: relative;
			padding-bottom: 60px;
			}

		.sq-novos {
			display: inline-block;
			position: relative;
			width: 20px;
			height: 20px;
			float: left;
			background-color: #f4b400;
			}

		.txt {
			float: left;
			line-height: 20px;
			font-size: 11px;
			margin-left: 6px;
			}

		.sq-cumulativo {
			display: inline-block;
			position: relative;
			width: 20px;
			height: 20px;
			float: left;
			margin-left: 40px;
			background-color: #4285f4;
			}

		.chart-row {
			position: relative;
			width: 100%;
			overflow: hidden;
			height: 80px;
			page-break-inside: avoid;
			}
	
		.left-line {
			position: absolute;
			top: 0;
			left: 240px;
			width: 1px;
			height: 100px;
			background-color: #444444;
			}

		.mark-1 {
			position: absolute;
			left: 390px;
			top: 0;
			width: 1px;
			height: 100%;
			background-color: #cccccc;
			}

		.mark-2 {
			position: absolute;
			left: 540px;
			top: 0;
			width: 1px;
			height: 100%;
			background-color: #cccccc;
			}

		.mark-3 {
			position: absolute;
			left: 690px;
			top: 0;
			width: 1px;
			height: 100%;
			background-color: #cccccc;
			}

		.mark-4 {
			position: absolute;
			left: 840px;
			top: 0;
			width: 1px;
			height: 100%;
			background-color: #cccccc;
			}
		
		.bar {
			position: absolute;
			width: 600px;
			height: 20px;
			top: 30px;
			left: 241px;
			}

		.bar-1 { background-color: #4285f4; }
		.bar-1 > .text-out { position: absolute; left: 0; top: 30px; line-height: 20px; color: #4285f4; }
		.bar-1 > .text-in { float: right; line-height: 20px; margin-right: 10px; color: #ffffff; }

		.bar-3 { background-color: #f4c70f; }
		.bar-3 > .text-out { position: absolute; left: 0; top: 30px; line-height: 20px; color: #f4c70f; }
		.bar-3 > .text-in { float: right; line-height: 20px; margin-right: 10px; color: #ffffff; }

		.bar-4 { background-color: #5bb95c; }
		.bar-4 > .text-out { position: absolute; left: 0; top: 30px; line-height: 20px; color: #5bb95c; }
		.bar-4 > .text-in { float: right; line-height: 20px; margin-right: 10px; color: #ffffff; }

		.bar-5 { background-color: #aa67b2; }
		.bar-5 > .text-out { position: absolute; left: 0; top: 30px; line-height: 20px; color: #aa67b2; }
		.bar-5 > .text-in { float: right; line-height: 20px; margin-right: 10px; color: #ffffff; }

		.bar-6 { background-color: #fd430b; }
		.bar-6 > .text-out { position: absolute; left: 0; top: 30px; line-height: 20px; color: #fd430b; }
		.bar-6 > .text-in { float: right; line-height: 20px; margin-right: 10px; color: #ffffff; }

		.bar-7 { background-color: #202020; }
		.bar-7 > .text-out { position: absolute; left: 0; top: 30px; line-height: 20px; color: #202020; }
		.bar-7 > .text-in { float: right; line-height: 20px; margin-right: 10px; color: #ffffff; }

		
		.left-info-line-1 {
			position: absolute;
			right: 720px;
			top: 19px;
			line-height: 21px;
			font-size: 14px;
			font-weight: bold;
			}

		.left-info-line-2 {
			position: absolute;
			right: 720px;
			top: 30px;
			line-height: 21px;
			font-size: 11px;
			color: #666666;
			}

		.ticks {
			position: relative;
			width: 100%;
			height: 40px;
			}

		.m0 {
			position: absolute;
			left: 180px;
			top: 0;
			line-height: 30px;
			color: #888888;
			font-size: 11px;
			width: 120px;
			text-align: center;
			}

		.m1 {
			position: absolute;
			left: 330px;
			top: 0;
			line-height: 30px;
			color: #888888;
			font-size: 11px;
			width: 120px;
			text-align: center;
			}

		.m2 {
			position: absolute;
			left: 480px;
			top: 0;
			line-height: 30px;
			color: #888888;
			font-size: 11px;
			width: 120px;
			text-align: center;
			}

		.m3 {
			position: absolute;
			left: 630px;
			top: 0;
			line-height: 30px;
			color: #888888;
			font-size: 11px;
			width: 120px;
			text-align: center;
			}

		.m4 {
			position: absolute;
			left: 780px;
			top: 0;
			line-height: 30px;
			color: #888888;
			font-size: 11px;
			width: 120px;
			text-align: center;
			}
	</style>
</head>
<body>
<div style="width: 950px; overflow: hidden;">
	<div class="title">
		Adesão ao Sistema (Regional)
	</div>
	<div class="sub-title">
		Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')
	</div>
	'.$rows.'
	<div class="ticks">
		<span class="m0">0</span>
		<span class="m1">'.$tick_1.'</span>
		<span class="m2">'.$tick_2.'</span>
		<span class="m3">'.$tick_3.'</span>
		<span class="m4">'.$tick_4.'</span>
	</div>
</div>
</body>
</html>';

		$oFile = fopen(UPLOAD_DIR."~adesao_regional_".$sInside_id.".html", "w");
		fwrite($oFile, $tHtml);
		fclose($oFile);

		exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~adesao_regional_".$sInside_id.".html ".UPLOAD_DIR."~adesao_regional_".$sInside_id.".pdf");
		@unlink(UPLOAD_DIR."~adesao_regional_".$sInside_id.".html");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
