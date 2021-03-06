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
		$aMes_total = array();

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
		$pPeriodo_fr_mo = substr($pPeriodo_fr,0,2);
		$pPeriodo_fr_yr = substr($pPeriodo_fr,6,4);



		//VALIDAR DATA PERIODO ATE
		if (strlen($pPeriodo_to) == 10 && isValidBrDate($pPeriodo_to))
			$pPeriodo_to = brToUs($pPeriodo_to); // mm/dd/yyyy
		else
			$pPeriodo_to = date("m/d/Y");

		$pPeriodo_to_mo = substr($pPeriodo_to,0,2);
		$pPeriodo_to_yr = substr($pPeriodo_to,6,4);



		//SE O PERIODO DE FOR MAIOR DO QUE O PERIODO ATE (INVERTER)
		if (intval($pPeriodo_fr_yr.$pPeriodo_fr_mo) > intval($pPeriodo_to_yr.$pPeriodo_to_mo))
		{
			$t = $pPeriodo_to;
			$t_mo = $pPeriodo_to_mo;
			$t_yr = $pPeriodo_to_yr;

			$pPeriodo_to = $pPeriodo_fr;
			$pPeriodo_to_mo = $pPeriodo_fr_mo;
			$pPeriodo_to_yr = $pPeriodo_fr_yr;

			$pPeriodo_fr = $t;
			$pPeriodo_fr_mo = $t_mo;
			$pPeriodo_fr_yr = $t_yr;
		}


		$data_selecionada_fr = usToBr($pPeriodo_fr);
		$data_selecionada_to = usToBr($pPeriodo_to);


		//AJUSTAR DATA PERIODO DE PARA O INICIO DO MES
		$pPeriodo_fr = $pPeriodo_fr_mo."/01/".$pPeriodo_fr_yr;
	
		//AJUSTAR DATA PERIODO ATE PARA O FINAL DO MES
		$pPeriodo_to = $pPeriodo_to_mo."/".cal_days_in_month(CAL_GREGORIAN, $pPeriodo_to_mo, $pPeriodo_to_yr)."/".$pPeriodo_to_yr;



		$mes_fr = $pPeriodo_fr_mo;
		$ano_fr = $pPeriodo_fr_yr;
		$mes_int = intval($ano_fr.str_pad($mes_fr,2,"0",STR_PAD_LEFT));
		$stop_int = intval($pPeriodo_to_yr.$pPeriodo_to_mo);
		$at = 1;
		while ($mes_int <= $stop_int)
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
				MONTH(log.data_hora) = $mes_fr AND
				YEAR(log.data_hora) = $ano_fr
			GROUP BY 
				dn");
			while ($db->nextRecord())
				$aDns_total[] = $db->f("dn");

			$aDns_total = array_unique($aDns_total);
			$novos = count($aDns_total) - $curr;	
			$aMes_total[] = array("seq"=>$at,"mes"=>$mes_fr,"ano"=>$ano_fr,"novos"=>$novos,"cumulativo"=>count($aDns_total));

			$mes_fr += 1;
			if ($mes_fr > 12)
			{
				$mes_fr = 1;
				$ano_fr += 1;
			}
			$mes_int = intval($ano_fr.str_pad($mes_fr,2,"0",STR_PAD_LEFT));
			$at += 1;
		}





		if ($pFormato == "xlsx")
		{
			require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
			$phpexcel = new PHPExcel();
			$phpexcel->getProperties()->setCreator("GELIC")
				->setLastModifiedBy("GELIC")
				->setTitle("Adesão ao Sistema (Mensal)")
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
				->setCellValue("A$row", 'Mês')
				->setCellValue("B$row", 'Periodo (mês/ano)')
				->setCellValue("C$row", 'Novos')
				->setCellValue("D$row", 'Cumulativo');

			for ($i=0; $i<count($aMes_total); $i++)
			{
				$row += 1;
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", $aMes_total[$i]["seq"])
					->setCellValue("B$row", $aMes_total[$i]["mes"]."/".$aMes_total[$i]["ano"])
					->setCellValue("C$row", $aMes_total[$i]["novos"])
					->setCellValue("D$row", $aMes_total[$i]["cumulativo"]);
			}

			$phpexcel->getActiveSheet()->mergeCells("A1:D1");
			$phpexcel->getActiveSheet()->setTitle('Adesão ao Sistema (Mensal)');
			$phpexcel->getActiveSheet()->getStyle("A1:D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A2:D2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A2:D2")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);

			if (file_exists(UPLOAD_DIR."~adesao_mensal_".$sInside_id.".xlsx"))
				unlink(UPLOAD_DIR."~adesao_mensal_".$sInside_id.".xlsx");

			$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$obwriter->save(UPLOAD_DIR."~adesao_mensal_".$sInside_id.".xlsx");

			$aReturn[0] = 1; //sucesso
		}
		else if ($pFormato == "pdf")
		{
			$max = 0;
			for ($i=0; $i<count($aMes_total); $i++)
				if ($max < $aMes_total[$i]["cumulativo"]) { $max = $aMes_total[$i]["cumulativo"]; }

			$top_val = $max;

			while (!($max % 40) == 0) { $max += 1; }

			$tick_1 = $max / 4 * 1;
			$tick_2 = $max / 4 * 2;
			$tick_3 = $max / 4 * 3;
			$tick_4 = $max / 4 * 4;

			$rows = '';
			for ($i=0; $i<count($aMes_total); $i++)
			{
				if ($max == 0)
				{
					$b1_width = 0;
					$b2_width = 0;
				}
				else
				{
					$b1_width = round(600 * $aMes_total[$i]["novos"] / $max);
					$b2_width = round(600 * $aMes_total[$i]["cumulativo"] / $max);
				}

				if ($b1_width < 30)
					$b1 = '<div class="bar-1" style="width:'.$b1_width.'px;"></div><span class="b1-text-out" style="left:'.(241+$b1_width+10).'px;">'.$aMes_total[$i]["novos"].'</span>';
				else
					$b1 = '<div class="bar-1" style="width:'.$b1_width.'px;"><span>'.$aMes_total[$i]["novos"].'</span></div>';

				if ($b2_width < 30)
					$b2 = '<div class="bar-2" style="width:'.$b2_width.'px;"></div><span class="b2-text-out" style="left:'.(241+$b2_width+10).'px;">'.$aMes_total[$i]["cumulativo"].'</span>';
				else
					$b2 = '<div class="bar-2" style="width:'.$b2_width.'px;"><span>'.$aMes_total[$i]["cumulativo"].'</span></div>';

				$rows .= '<div class="chart-row">
					<div class="left-line"></div>
					<div class="mark-1"></div>
					<div class="mark-2"></div>
					<div class="mark-3"></div>
					<div class="mark-4"></div>
					'.$b1.$b2.'
					<div class="bar-2" style="width:'.$b2_width.'px;"><span>'.$aMes_total[$i]["cumulativo"].'</span></div>
					<span class="left-info-line-1">Mês&nbsp;'.$aMes_total[$i]["seq"].'</span>
					<span class="left-info-line-2">'.$aMes_total[$i]["mes"]."/".$aMes_total[$i]["ano"].'</span>
				</div>';
			}

			$tHtml = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Adesão so sistema (Mensal)</title>
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
		Adesão ao Sistema (Mensal)
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

			$oFile = fopen(UPLOAD_DIR."~adesao_mensal_".$sInside_id.".html", "w");
			fwrite($oFile, $tHtml);
			fclose($oFile);

			exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~adesao_mensal_".$sInside_id.".html ".UPLOAD_DIR."~adesao_mensal_".$sInside_id.".pdf");
			@unlink(UPLOAD_DIR."~adesao_mensal_".$sInside_id.".html");

			$aReturn[0] = 1; //sucesso
		}
	}
}
echo json_encode($aReturn);

?>
