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
		$aDNs = array();
		$db = new Mysql();
		$db->query("
SELECT
	cli.dn,
    cli.nome,
    cid.uf,
    uf.regiao,
    (SELECT id FROM gelic_log_login WHERE tipo = 3 AND id_clienteusuario = cli.id_parent LIMIT 1) AS child_has_access,
	IF (cli.id_cidade = 5370, (SELECT id FROM gelic_log_login WHERE tipo = 2 AND id_clienteusuario IN (SELECT id FROM gelic_clientes where id_cidade = 5370) LIMIT 1), NULL) AS pool_sp,
    IF (cli.id_cidade = 3314, (SELECT id FROM gelic_log_login WHERE tipo = 2 AND id_clienteusuario IN (SELECT id FROM gelic_clientes where id_cidade = 3314) LIMIT 1), NULL) AS pool_pr,
    IF (cli.id_cidade = 4266, (SELECT id FROM gelic_log_login WHERE tipo = 2 AND id_clienteusuario IN (SELECT id FROM gelic_clientes where id_cidade = 4266) LIMIT 1), NULL) AS pool_rs,
    IF (cli.id_cidade = 805, (SELECT id FROM gelic_log_login WHERE tipo = 2 AND id_clienteusuario IN (SELECT id FROM gelic_clientes where id_cidade = 805) LIMIT 1), NULL) AS pool_df,
    IF (cli.id_cidade = 1440, (SELECT id FROM gelic_log_login WHERE tipo = 2 AND id_clienteusuario IN (SELECT id FROM gelic_clientes where id_cidade = 1440) LIMIT 1), NULL) AS pool_mg
FROM 
	gelic_clientes AS cli
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
    INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	cli.id NOT IN (1,591) AND
	cli.tipo = 2 AND
    cli.id NOT IN (SELECT id_clienteusuario FROM gelic_log_login WHERE tipo = 2)
HAVING
	pool_sp IS NULL AND
    pool_pr IS NULL AND
    pool_rs IS NULL AND
    pool_df IS NULL AND
    pool_mg IS NULL
ORDER BY
	uf.regiao, cli.nome");
		while ($db->nextRecord())
		{
			if (strlen($db->f("child_has_access")) == 0)
				$aDNs[] = array("dn"=>$db->f("dn"), "nome"=>$db->f("nome"), "regiao"=>$db->f("regiao"));
		}


		if ($pFormato == "xlsx")
		{
			require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
			$phpexcel = new PHPExcel();
			$phpexcel->getProperties()->setCreator("GELIC")
				->setLastModifiedBy("GELIC")
				->setTitle("DNs que nunca acessaram")
				->setSubject("Acessos")
				->setDescription("Lista de DNs que nunca acessaram GELIC")
				->setKeywords("office 2007 openxml php gelic")
				->setCategory("Acessos");
								 
			$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
			$phpexcel->getDefaultStyle()->getFont()->setSize(10);

			$row = 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", 'DN')
				->setCellValue("B$row", 'Nome do DN')
				->setCellValue("C$row", 'Região');

			for ($i=0; $i<count($aDNs); $i++)
			{
				$row += 1;

				$t = $aDNs[$i]["dn"];
				if ($t == 232323) $t = "POOL PR";
				else if ($t == 242424) $t = "POOL RS";
				else if ($t == 252525) $t = "POOL DF";
				else if ($t == 262626) $t = "POOL MG";
				else if ($t == 272727) $t = "POOL SP";

				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", $t)
					->setCellValue("B$row", $aDNs[$i]["nome"])
					->setCellValue("C$row", utf8_encode($aDNs[$i]["regiao"]));
			}

			$phpexcel->getActiveSheet()->setTitle('DNs que nunca acessaram');
			$phpexcel->getActiveSheet()->getStyle("A1:C1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A1:C1")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getStyle("A1:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
			$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

			if (file_exists(UPLOAD_DIR."~dns_semacesso_".$sInside_id.".xlsx"))
				unlink(UPLOAD_DIR."~dns_semacesso_".$sInside_id.".xlsx");

			$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$obwriter->save(UPLOAD_DIR."~dns_semacesso_".$sInside_id.".xlsx");

			$aReturn[0] = 1; //sucesso
		}
		else if ($pFormato == "pdf")
		{
			$aData = array();
			$aData[] = array("n"=>"Nunca Acessaram", "total"=>count($aDNs), "c"=>"4285f4");

			$db->query("SELECT COUNT(*) AS total FROM gelic_clientes WHERE tipo = 2");
			$db->nextRecord();
			$aData[] = array("n"=>"TOTAL DNs", "total"=>$db->f("total"), "c"=>"202020");

			$max = 0;
			for ($i=0; $i<count($aData); $i++)
				if ($max < $aData[$i]["total"]) { $max = $aData[$i]["total"]; }

			$top_val = $max;
			while (!($max % 40) == 0) { $max += 1; }

			$tick_1 = $max / 4 * 1;
			$tick_2 = $max / 4 * 2;
			$tick_3 = $max / 4 * 3;
			$tick_4 = $max / 4 * 4;

			$graph_height = 180;
			$graph_width = 920;
			$bars_per_graph = 4;
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

			if (count($aData) == 0)
				$output .= '<div class="mark-5"></div></div>';



			for ($i=0; $i<count($aData); $i++)
			{
				if ($max == 0)
					$bh = 0;
				else
					$bh = round($graph_height * $aData[$i]["total"] / $max);

				$cols .= '<div class="chart-col">
					<div class="bar" style="height:'.$bh.'px; background-color:#'.$aData[$i]["c"].';"></div>
					<span class="lb" style="bottom:'.($bh+14).'px; color:#'.$aData[$i]["c"].';">'.$aData[$i]["total"].'</span>
				</div>';
			
				$dates .= '<span class="txt" style="left:'.($j*$col_width+40).'px;"><a class="bold">'.$aData[$i]["n"].'</a></span>';
				$j += 1;			

				if (($i+1) % $bars_per_graph == 0 || ($i+1) == count($aData))
				{
					$output .= $cols.'<div class="mark-5"></div></div>
					<div class="holder" style="height:120px;margin-top:-10px;">
						'.$dates.'
					</div></div>';
		
					$cols = '';
					$dates = '';
					$j = 0;

					if (($i+1) < count($aData))
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
	<title>DNs que nunca acessaram</title>
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
		DNs que nunca acessaram
	</div>
	<div class="sub-title">
		---
	</div>
	'.$output.'
</div>
</body>
</html>';

			$oFile = fopen(UPLOAD_DIR."~dns_semacesso_".$sInside_id.".html", "w");
			fwrite($oFile, $tHtml);
			fclose($oFile);

			exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~dns_semacesso_".$sInside_id.".html ".UPLOAD_DIR."~dns_semacesso_".$sInside_id.".pdf");
			@unlink(UPLOAD_DIR."~dns_semacesso_".$sInside_id.".html");

			$aReturn[0] = 1; //sucesso
		}
	}
}
echo json_encode($aReturn);

?>
