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

	$aMeses = array();

	$db = new Mysql();

	//VALIDAR DATA PERIODO DE
	if (strlen($pPeriodo_fr) == 10 && isValidBrDate($pPeriodo_fr))
		$pPeriodo_fr = brToUs($pPeriodo_fr); // mm/dd/yyyy
	else
	{
		$db->query("SELECT DATE(data_hora) AS data FROM gelic_licitacoes_apl_historico ORDER BY data_hora LIMIT 1");
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
		$aAPLS_total = array();
		$aAPLS_aprovadas = array();
		$aAPLS_reprovadas = array();
		$aAPLS_sem_atuacao = array();
		
		$dVei_total = 0;
		$dVei_aprovadas = 0;
		$dVei_reprovadas = 0;
		$dVei_sem_atuacao = 0;

		$db->query("
SELECT
	apl.id,
	CONCAT(apl.id_licitacao,IF (cli.id_parent > 0, cli.id_parent, cli.id),apl.id_item) AS str,
	(SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM
	gelic_licitacoes_apl AS apl
    INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
	INNER JOIN gelic_licitacoes_apl_historico AS ahis ON ahis.id = (SELECT MAX(id) FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id AND tipo = 1)
WHERE
	MONTH(ahis.data_hora) = $mes_fr AND
	YEAR(ahis.data_hora) = $ano_fr
GROUP BY
	str");
		while ($db->nextRecord())
		{
			$aAPLS_total[] = $db->f("id");

			if ($db->f("tipo") == 2)
				$aAPLS_aprovadas[] = $db->f("id");
			else if ($db->f("tipo") == 4)
				$aAPLS_reprovadas[] = $db->f("id");
			else
				$aAPLS_sem_atuacao[] = $db->f("id");
		}



		// Contar Veiculos Total
		if (count($aAPLS_total) > 0)
		{
			$db->query("
SELECT
	SUM(IF (quantidade > 0, quantidade, 1)) AS veiculos
FROM
	gelic_licitacoes_itens
WHERE
	id IN (SELECT DISTINCT(id_item) FROM gelic_licitacoes_apl WHERE id IN (".implode(",",$aAPLS_total)."))");
			$db->nextRecord();
			$dVei_total = $db->f("veiculos");
		}



		// Contar Veiculos Aprovadas
		if (count($aAPLS_total) > 0)
		{
			$db->query("
SELECT
	SUM(IF (quantidade > 0, quantidade, 1)) AS veiculos
FROM
	gelic_licitacoes_itens
WHERE
	id IN (SELECT DISTINCT(id_item) FROM gelic_licitacoes_apl WHERE id IN (".implode(",",$aAPLS_aprovadas)."))");
			$db->nextRecord();
			$dVei_aprovadas = $db->f("veiculos");
		}



		// Contar Veiculos Aprovadas
		if (count($aAPLS_total) > 0)
		{
			$db->query("
SELECT
	SUM(IF (quantidade > 0, quantidade, 1)) AS veiculos
FROM
	gelic_licitacoes_itens
WHERE
	id IN (SELECT DISTINCT(id_item) FROM gelic_licitacoes_apl WHERE id IN (".implode(",",$aAPLS_reprovadas)."))");
			$db->nextRecord();
			$dVei_reprovadas = $db->f("veiculos");
		}



		// Contar Veiculos Sem Atuacao
		if (count($aAPLS_total) > 0)
		{
			$db->query("
SELECT
	SUM(IF (quantidade > 0, quantidade, 1)) AS veiculos
FROM
	gelic_licitacoes_itens
WHERE
	id IN (SELECT DISTINCT(id_item) FROM gelic_licitacoes_apl WHERE id IN (".implode(",",$aAPLS_sem_atuacao)."))");
			$db->nextRecord();
			$dVei_sem_atuacao = $db->f("veiculos");
		}





		if (count($aAPLS_total) > 0)
			$aMeses[] = array("seq"=>$at, "mes"=>$mes_fr, "ano"=>$ano_fr, "apl_total"=>count($aAPLS_total), "apl_aprovadas"=>count($aAPLS_aprovadas), "apl_reprovadas"=>count($aAPLS_reprovadas), "apl_sem_atuacao"=>(count($aAPLS_total)-count($aAPLS_aprovadas)-count($aAPLS_reprovadas)), "vei_total"=>$dVei_total, "vei_aprovadas"=>$dVei_aprovadas, "vei_reprovadas"=>$dVei_reprovadas, "vei_sem_atuacao"=>$dVei_sem_atuacao);

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
		require_once "../../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("APLs Enviadas por Mês")
			->setSubject("Acessos")
			->setDescription("APLs Enviadas GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("APL");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A1", 'Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')');

		$row = 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Mês')
			->setCellValue("B$row", 'Periodo (mês/ano)')
			->setCellValue("C$row", 'APLs Total')
			->setCellValue("D$row", 'APLs Aprovadas')
			->setCellValue("E$row", 'APLs Reprovadas')
			->setCellValue("F$row", 'APLs Sem Atuação')
			->setCellValue("G$row", 'Veículos Total')
			->setCellValue("H$row", 'Veículos APLs Aprovadas')
			->setCellValue("I$row", 'Veículos APLs Reprovadas')
			->setCellValue("J$row", 'Veículos APLs Sem Atuação');

		for ($i=0; $i<count($aMeses); $i++)
		{
			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $aMeses[$i]["seq"])
				->setCellValue("B$row", $aMeses[$i]["mes"]."/".$aMeses[$i]["ano"])
				->setCellValue("C$row", $aMeses[$i]["apl_total"])
				->setCellValue("D$row", $aMeses[$i]["apl_aprovadas"])
				->setCellValue("E$row", $aMeses[$i]["apl_reprovadas"])
				->setCellValue("F$row", $aMeses[$i]["apl_sem_atuacao"])
				->setCellValue("G$row", $aMeses[$i]["vei_total"])
				->setCellValue("H$row", $aMeses[$i]["vei_aprovadas"])
				->setCellValue("I$row", $aMeses[$i]["vei_reprovadas"])
				->setCellValue("J$row", $aMeses[$i]["vei_sem_atuacao"]);
		}

		$phpexcel->getActiveSheet()->mergeCells("A1:J1");
		$phpexcel->getActiveSheet()->setTitle('APLs Enviadas e Veículos');
		$phpexcel->getActiveSheet()->getStyle("A1:J$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A2:J2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:J2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(22);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(22);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(22);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(22);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(26);
		$phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(26);
		$phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(26);

		if (file_exists(UPLOAD_DIR."~apls_enviadas_veiculos_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~apls_enviadas_veiculos_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~apls_enviadas_veiculos_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
	else if ($pFormato == "pdf")
	{
		$max = 0;
		for ($i=0; $i<count($aMeses); $i++)
		{
			if ($max < $aMeses[$i]["apl_total"]) { $max = $aMeses[$i]["apl_total"]; }
			if ($max < $aMeses[$i]["vei_total"]) { $max = $aMeses[$i]["vei_total"]; }
		}

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

		if (count($aMeses) == 0)
			$output .= '<div class="mark-5"></div></div>';

		for ($i=0; $i<count($aMeses); $i++)
		{
			if ($max == 0)
				$bh = 0;
			else
				$bh = round($graph_height * $aMeses[$i]["apl_total"] / $max);

			if ($max == 0)
				$vh = 0;
			else
				$vh = round($graph_height * $aMeses[$i]["vei_total"] / $max);

			$cols .= '<div class="chart-col">
				<div class="bar" style="height:'.$bh.'px;"></div>
				<div class="bar-v" style="height:'.$vh.'px;"></div>
				<span class="lb" style="bottom:'.($bh+14).'px;">'.$aMeses[$i]["apl_total"].'</span>
				<span class="lb-v" style="bottom:'.($vh+14).'px;">'.$aMeses[$i]["vei_total"].'</span>
			</div>';
			
			$dates .= '<span class="txt" style="left:'.($j*$col_width+40).'px;"><a class="bold">Mês '.$aMeses[$i]["seq"].'</a><br>'.$aMeses[$i]["mes"].'/'.$aMeses[$i]["ano"].'<br><a style="color:#176f03;">Aprovadas/Vei: '.$aMeses[$i]["apl_total"].'/'.$aMeses[$i]["vei_total"].'</a><br><a style="color:#ff0000;">Reprovadas/Vei: '.$aMeses[$i]["apl_reprovadas"].'/'.$aMeses[$i]["vei_reprovadas"].'</a><br><a style="color:#777777;">Sem Atuação/Vei: '.$aMeses[$i]["apl_sem_atuacao"].'/'.$aMeses[$i]["vei_sem_atuacao"].'</a></span>';
			$j += 1;			

			if (($i+1) % $bars_per_graph == 0 || ($i+1) == count($aMeses))
			{
				$output .= $cols.'<div class="mark-5"></div></div>
				<div class="holder" style="height:120px;margin-top:-10px;">
					'.$dates.'
				</div></div>';
		
				$cols = '';
				$dates = '';
				$j = 0;

				if (($i+1) < count($aMeses))
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
	<title>APLs Enviadas e Veículos</title>
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
			width: '.round($bar_width/2).'px;
			background-color: #4285f4;
			}
		.bar-v {
			position: absolute;
			left: '.($bar_left+round($bar_width/2)).'px;
			bottom: 10px;
			width: '.round($bar_width/2).'px;
			background-color: #fec615;
			}
	
		.lb {	
			position: absolute;
			left: '.$bar_left.'px;
			bottom: 0;
			width: '.round($bar_width/2).'px;
			text-align: center;
			font-size: 11px;
			color: #0c47a7;
			}
		.lb-v {	
			position: absolute;
			left: '.($bar_left+round($bar_width/2)).'px;
			bottom: 0;
			width: '.round($bar_width/2).'px;
			text-align: center;
			font-size: 11px;
			color: #000000;
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
		APLs Enviadas e Veículos
	</div>
	<div class="sub-title">
		Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')
	</div>
	<div style="overflow: hidden;margin-bottom:10px;">
		<span style="display:inline-block;width: 22px;height: 22px;background-color:#4285f4;float:left;"></span>
		<span style="float:left;line-height: 22px;margin-left: 8px;">APLs Total</span>
		<span style="display:inline-block;width: 22px;height: 22px;background-color:#fec615;float:left;margin-left:60px;"></span>
		<span style="float:left;line-height: 22px;margin-left: 8px;">Veículos</span>
	</div>
	'.$output.'
</div>
</body>
</html>';

		$oFile = fopen(UPLOAD_DIR."~apls_enviadas_veiculos_".$sInside_id.".html", "w");
		fwrite($oFile, $tHtml);
		fclose($oFile);

		exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~apls_enviadas_veiculos_".$sInside_id.".html ".UPLOAD_DIR."~apls_enviadas_veiculos_".$sInside_id.".pdf");
		@unlink(UPLOAD_DIR."~apls_enviadas_veiculos_".$sInside_id.".html");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
