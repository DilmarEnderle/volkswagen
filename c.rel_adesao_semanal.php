<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1) //BO
	{
		$pFormato = trim($_POST["formato"]);
		$pPeriodo_fr = $_POST["periodo-fr"];
		$pPeriodo_to = $_POST["periodo-to"];

		$aDns_total = array();
		$aSemana_total = array();

		$db = new Mysql();

		//VALIDAR DATA PERIODO DE
		if (strlen($pPeriodo_fr) == 10)
		{
			if (isValidBrDate($pPeriodo_fr))
				$pPeriodo_fr = brToUs($pPeriodo_fr);
			else
				$pPeriodo_fr = "11/29/2015";
		}
		else
		{
			$db->query("SELECT DATE(data_hora) AS periodo_fr FROM gelic_log_login ORDER BY id LIMIT 1");
			if ($db->nextRecord())
				$pPeriodo_fr = mysqlToUs($db->f("periodo_fr"));
			else
				$pPeriodo_fr = "11/29/2015";
		}

		//VALIDAR DATA PERIODO ATE
		if (strlen($pPeriodo_to) == 10)
		{
			if (isValidBrDate($pPeriodo_to))
				$pPeriodo_to = brToUs($pPeriodo_to);
			else
				$pPeriodo_to = date("m/d/Y");
		}
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


		//AJUSTAR DATA PERIODO DE PARA O INICIO DA SEMANA
		$day_of_week = date('w', strtotime($pPeriodo_fr)); //day of the week selected 0..6 sun..sat
		if ($day_of_week > 0)
			$pPeriodo_fr = date("m/d/Y", strtotime($pPeriodo_fr." -".$day_of_week." day"));

		//AJUSTAR DATA PERIODO ATE PARA O FINAL DA SEMANA
		$day_of_week = date('w', strtotime($pPeriodo_to)); //day of the week selected 0..6 sun..sat
		if ($day_of_week < 6)
			$pPeriodo_to = date("m/d/Y", strtotime($pPeriodo_to." +".(6-$day_of_week)." day"));
	

		$semana_fr = usToMysql($pPeriodo_fr);
		$semana_to = date("Y-m-d", strtotime($semana_fr." +6 day")); 	
		$semana_to_int = intval(str_replace("-","",$semana_to));
		$stop_int = intval(date("Ymd", strtotime($pPeriodo_to)));
		$at = 1;
		while ($semana_to_int <= $stop_int)
		{
			$curr = count($aDns_total);

			$db->query("SELECT 
				IF (clip.id > 0, clip.id, log.id_clienteusuario) AS dn
			FROM 
				gelic_log_login AS log
			    INNER JOIN gelic_clientes AS cli ON cli.id = log.id_clienteusuario
			    LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
			WHERE
				log.id_clienteusuario NOT IN (1,591) AND
				log.tipo IN (2,3) AND
			    log.data_hora BETWEEN '".$semana_fr." 00:00:00' AND '".$semana_to." 23:59:59'
			GROUP BY
				dn");
			while ($db->nextRecord())
				$aDns_total[] = $db->f("dn");

			$aDns_total = array_unique($aDns_total);
			$novos = count($aDns_total) - $curr;	
			$aSemana_total[] = array("semana"=>$at,"semana_fr"=>$semana_fr." 00:00:00","semana_to"=>$semana_to." 23:59:59","novos"=>$novos,"cumulativo"=>count($aDns_total));

			$semana_fr = date("Y-m-d", strtotime($semana_fr." +7 day"));
			$semana_to = date("Y-m-d", strtotime($semana_fr." +6 day"));		
			$semana_to_int = intval(str_replace("-","",$semana_to));
			$at += 1;
		}



		if ($pFormato == "xlsx")
		{
			require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
			$phpexcel = new PHPExcel();
			$phpexcel->getProperties()->setCreator("GELIC")
				->setLastModifiedBy("GELIC")
				->setTitle("Adesão ao Sistema (Semanal)")
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
				->setCellValue("A$row", 'Semana')
				->setCellValue("B$row", 'Periodo de')
				->setCellValue("C$row", 'Periodo até')
				->setCellValue("D$row", 'Novos')
				->setCellValue("E$row", 'Cumulativo');

			for ($i=0; $i<count($aSemana_total); $i++)
			{
				$row += 1;
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", $aSemana_total[$i]["semana"])
					->setCellValue("B$row", mysqlToBr(substr($aSemana_total[$i]["semana_fr"],0,10)))
					->setCellValue("C$row", mysqlToBr(substr($aSemana_total[$i]["semana_to"],0,10)))
					->setCellValue("D$row", $aSemana_total[$i]["novos"])
					->setCellValue("E$row", $aSemana_total[$i]["cumulativo"]);
			}

			$phpexcel->getActiveSheet()->mergeCells("A1:E1");
			$phpexcel->getActiveSheet()->setTitle('Adesão ao Sistema (Semanal)');
			$phpexcel->getActiveSheet()->getStyle("A1:E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A2:E2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A2:E2")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);

			if (file_exists(UPLOAD_DIR."~adesao_semanal_".$sInside_id.".xlsx"))
				unlink(UPLOAD_DIR."~adesao_semanal_".$sInside_id.".xlsx");

			$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$obwriter->save(UPLOAD_DIR."~adesao_semanal_".$sInside_id.".xlsx");
	
			$aReturn[0] = 1; //sucesso
		}
		else if ($pFormato == "pdf")
		{
			$max = 0;
			for ($i=0; $i<count($aSemana_total); $i++)
				if ($max < $aSemana_total[$i]["cumulativo"]) { $max = $aSemana_total[$i]["cumulativo"]; }

			$top_val = $max;

			while (!($max % 40) == 0) { $max += 1; }

			$tick_1 = $max / 4 * 1;
			$tick_2 = $max / 4 * 2;
			$tick_3 = $max / 4 * 3;
			$tick_4 = $max / 4 * 4;

			$rows = '';
			for ($i=0; $i<count($aSemana_total); $i++)
			{
				if ($max == 0)
				{
					$b1_width = 0;
					$b2_width = 0;
				}
				else
				{
					$b1_width = round(600 * $aSemana_total[$i]["novos"] / $max);
					$b2_width = round(600 * $aSemana_total[$i]["cumulativo"] / $max);
				}

				if ($b1_width < 30)
					$b1 = '<div class="bar-1" style="width:'.$b1_width.'px;"></div><span class="b1-text-out" style="left:'.(241+$b1_width+10).'px;">'.$aSemana_total[$i]["novos"].'</span>';
				else
					$b1 = '<div class="bar-1" style="width:'.$b1_width.'px;"><span>'.$aSemana_total[$i]["novos"].'</span></div>';

				if ($b2_width < 30)
					$b2 = '<div class="bar-2" style="width:'.$b2_width.'px;"></div><span class="b2-text-out" style="left:'.(241+$b2_width+10).'px;">'.$aSemana_total[$i]["cumulativo"].'</span>';
				else
					$b2 = '<div class="bar-2" style="width:'.$b2_width.'px;"><span>'.$aSemana_total[$i]["cumulativo"].'</span></div>';

				$rows .= '<div class="chart-row">
					<div class="left-line"></div>
					<div class="mark-1"></div>
					<div class="mark-2"></div>
					<div class="mark-3"></div>
					<div class="mark-4"></div>
					'.$b1.$b2.'
					<div class="bar-2" style="width:'.$b2_width.'px;"><span>'.$aSemana_total[$i]["cumulativo"].'</span></div>
					<span class="left-info-line-1">Semana&nbsp;'.$aSemana_total[$i]["semana"].'</span>
					<span class="left-info-line-2">'.mysqlToBr(substr($aSemana_total[$i]["semana_fr"],0,10)).' - '.mysqlToBr(substr($aSemana_total[$i]["semana_to"],0,10)).'</span>
				</div>';
			}

			$tHtml = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Adesão so sistema (Semanal)</title>
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

		.bar-1 {
			position: absolute;
			width: 600px;
			height: 20px;
			background-color: #f4b400;
			top: 18px;
			left: 241px;
			}

		.bar-1 span {
			float: right;
			line-height: 20px;
			margin-right: 10px;
			color: #ffffff;
			}

		.b1-text-out {
			position: absolute;
			left: 0;
			top: 18px;
			line-height: 20px;
			color: #f4b400;
			}

		.bar-2 {
			position: absolute;
			width: 600px;
			height: 20px;
			background-color: #4285f4;
			top: 42px;
			left: 241px;
			}

		.bar-2 span {
			float: right;
			line-height: 20px;
			margin-right: 10px;
			color: #ffffff;
			}

		.b2-text-out {
			position: absolute;
			left: 0;
			top: 42px;
			line-height: 20px;
			color: #4285f4;
			}
		
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
			top: 40px;
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
		Adesão ao Sistema (Semanal)
	</div>
	<div class="sub-title">
		Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')
	</div>
	<div class="legenda">
		<span class="sq-novos"></span>
		<span class="txt">Novos</span>
		<span class="sq-cumulativo"></span>
		<span class="txt">Cumulativo</span>
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

			$oFile = fopen(UPLOAD_DIR."~adesao_semanal_".$sInside_id.".html", "w");
			fwrite($oFile, $tHtml);
			fclose($oFile);

			exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~adesao_semanal_".$sInside_id.".html ".UPLOAD_DIR."~adesao_semanal_".$sInside_id.".pdf");
			@unlink(UPLOAD_DIR."~adesao_semanal_".$sInside_id.".html");

			$aReturn[0] = 1; //sucesso
		}
	}
}
echo json_encode($aReturn);

?>
