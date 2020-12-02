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

		$aSemanas = array();

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
			$db->query("SELECT COUNT(*) AS total FROM
(
SELECT 
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
	dn
) AS t");
			$db->nextRecord();
			if ($db->f("total") > 0)
				$aSemanas[] = array("semana"=>$at, "semana_fr"=>$semana_fr." 00:00:00", "semana_to"=>$semana_to." 23:59:59", "quantidade"=>$db->f("total"));

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
				->setTitle("Volume de Acessos por Semana")
				->setSubject("Acessos")
				->setDescription("Volume de Acessos GELIC")
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
				->setCellValue("D$row", 'Acessos');

			for ($i=0; $i<count($aSemanas); $i++)
			{
				$row += 1;
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", $aSemanas[$i]["semana"])
					->setCellValue("B$row", mysqlToBr(substr($aSemanas[$i]["semana_fr"],0,10)))
					->setCellValue("C$row", mysqlToBr(substr($aSemanas[$i]["semana_to"],0,10)))
					->setCellValue("D$row", $aSemanas[$i]["quantidade"]);
			}

			$phpexcel->getActiveSheet()->mergeCells("A1:D1");
			$phpexcel->getActiveSheet()->setTitle('Volume de Acessos por Semana');
			$phpexcel->getActiveSheet()->getStyle("A1:D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A2:D2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A2:D2")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);


			if (file_exists(UPLOAD_DIR."~volume_semanal_".$sInside_id.".xlsx"))
				unlink(UPLOAD_DIR."~volume_semanal_".$sInside_id.".xlsx");

			$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$obwriter->save(UPLOAD_DIR."~volume_semanal_".$sInside_id.".xlsx");

			$aReturn[0] = 1; //sucesso
		}
		else if ($pFormato == "pdf")
		{
			$max = 0;
			for ($i=0; $i<count($aSemanas); $i++)
				if ($max < $aSemanas[$i]["quantidade"]) { $max = $aSemanas[$i]["quantidade"]; }

			$top_val = $max;

			while (!($max % 20) == 0) { $max += 1; }

			$tick_1 = round($max / 4 * 1);
			$tick_2 = round($max / 4 * 2);
			$tick_3 = round($max / 4 * 3);
			$tick_4 = round($max / 4 * 4);

			$graph_height = 180;
			$graph_width = 920;
			$bars_per_graph = 6;
			$col_width = floor($graph_width / $bars_per_graph);
			$bar_width = floor($col_width / 2);
			$bar_left = floor(($col_width / 2) - ($bar_width / 2));


			$mark_1_bottom = $graph_height + 10;
			$mark_2_bottom = round($graph_height / 4 * 3) + 10;
			$mark_3_bottom = round($graph_height / 4 * 2) + 10;
			$mark_4_bottom = round($graph_height / 4) + 10;
			$mark_5_bottom = 10;

			$m1_bottom = $mark_1_bottom - 10;
			$m2_bottom = $mark_2_bottom - 10;
			$m3_bottom = $mark_3_bottom - 10;
			$m4_bottom = $mark_4_bottom - 10;
			$m5_bottom = $mark_5_bottom - 10;

			$cols = '';
			$dates = '';
			$output = '<div class="block"><div class="holder">
					<div class="left-scale">
						<span class="m1">'.$tick_4.'</span>
						<span class="m2">'.$tick_3.'</span>
						<span class="m3">'.$tick_2.'</span>
						<span class="m4">'.$tick_1.'</span>
						<span class="m5">0</span>
					</div>
					<div class="mark-1"></div>
					<div class="mark-2"></div>
					<div class="mark-3"></div>
					<div class="mark-4"></div>';
			$j = 0;

			if (count($aSemanas) == 0)
				$output .= '<div class="mark-5"></div></div>';

			for ($i=0; $i<count($aSemanas); $i++)
			{
				if ($max == 0)
					$bh = 0;
				else
					$bh = round($graph_height * $aSemanas[$i]["quantidade"] / $max);

				$cols .= '<div class="chart-col">
					<div class="bar" style="height:'.$bh.'px;"></div>
					<span class="lb" style="bottom:'.($bh+14).'px;">'.$aSemanas[$i]["quantidade"].'</span>
				</div>';
			
				$dates .= '<span class="txt" style="left:'.($j*$col_width+40).'px;"><a class="bold">Semana '.$aSemanas[$i]["semana"].'</a><br>'.mysqlToBr(substr($aSemanas[$i]["semana_fr"],0,10)).' - '.mysqlToBr(substr($aSemanas[$i]["semana_to"],0,10)).'</span>';
				$j += 1;			

				if (($i+1) % $bars_per_graph == 0 || ($i+1) == count($aSemanas))
				{
					$output .= $cols.'<div class="mark-5"></div></div>
					<div class="holder" style="height:120px;margin-top:-10px;">
						'.$dates.'
					</div></div>';
		
					$cols = '';
					$dates = '';
					$j = 0;

					if (($i+1) < count($aSemanas))
						$output .= '<div class="block"><div class="holder">
						<div class="left-scale">
							<span class="m1">'.$tick_4.'</span>
							<span class="m2">'.$tick_3.'</span>
							<span class="m3">'.$tick_2.'</span>
							<span class="m4">'.$tick_1.'</span>
							<span class="m5">0</span>
						</div>
						<div class="mark-1"></div>
						<div class="mark-2"></div>
						<div class="mark-3"></div>
						<div class="mark-4"></div>';
				}
			}


			$tHtml = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Volume de Acessos por Semana</title>
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

		.block {
			position: relative;
			overflow: hidden;
			page-break-inside: avoid;
			}

		.holder {
			position: relative;
			overflow: hidden;
			}

		.chart-col {
			position: relative;
			width: '.$col_width.'px;
			height: '.($graph_height+40).'px;
			float: left;
			}

		.bar {
			position: absolute;
			left: '.$bar_left.'px;
			bottom: 10px;
			width: '.$bar_width.'px;
			background-color: #4285f4;
			}
	
		.lb {	
			position: absolute;
			left: 0;
			bottom: 0;
			width: '.$col_width.'px;
			text-align: center;
			font-size: 11px;
			color: #0c47a7;
			}

		.txt {
			position: absolute;
			top: 6px;
			left: 0;
			width: '.$col_width.'px;
			font-size: 11px;
			text-align: center;
			}

		.bold { font-weight: bold; font-size: 12px; }

		.left-scale {
			position: relative;
			float: left;
			width: 40px;
			height: '.($graph_height+40).'px;
			}

		.mark-1 {
			position: absolute;
			right: 0;
			bottom: '.$mark_1_bottom.'px;
			width: '.$graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.mark-2 {
			position: absolute;
			right: 0;
			bottom: '.$mark_2_bottom.'px;
			width: '.$graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.mark-3 {
			position: absolute;
			right: 0;
			bottom: '.$mark_3_bottom.'px;
			width: '.$graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.mark-4 {
			position: absolute;
			right: 0;
			bottom: '.$mark_4_bottom.'px;
			width: '.$graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.mark-5 {
			position: absolute;
			right: 0;
			bottom: '.$mark_5_bottom.'px;
			width: '.$graph_width.'px;
			height: 1px;
			background-color: #000000;
			}

		.m1 {
			position: absolute;
			right: 8px;
			bottom: '.$m1_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.m2 {
			position: absolute;
			right: 8px;
			bottom: '.$m2_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.m3 {
			position: absolute;
			right: 8px;
			bottom: '.$m3_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.m4 {
			position: absolute;
			right: 8px;
			bottom: '.$m4_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.m5 {
			position: absolute;
			right: 8px;
			bottom: 0;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}
	</style>
</head>
<body>
<div style="width: 960px; overflow: hidden;">
	<div class="title">
		Volume de Acessos por Semana
	</div>
	<div class="sub-title">
		Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')
	</div>
	'.$output.'
</div>
</body>
</html>';

			$oFile = fopen(UPLOAD_DIR."~volume_semanal_".$sInside_id.".html", "w");
			fwrite($oFile, $tHtml);
			fclose($oFile);

			exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~volume_semanal_".$sInside_id.".html ".UPLOAD_DIR."~volume_semanal_".$sInside_id.".pdf");
			@unlink(UPLOAD_DIR."~volume_semanal_".$sInside_id.".html");

			$aReturn[0] = 1; //sucesso
		}
	}
}
echo json_encode($aReturn);

?>
