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

	$limit = array();

	$aEstados = array();
	$aEstados["AC"] = 6;
	$aEstados["AL"] = 5;
	$aEstados["AP"] = 6;
	$aEstados["AM"] = 6;
	$aEstados["BA"] = 5;
	$aEstados["CE"] = 5;
	$aEstados["DF"] = 6;
	$aEstados["ES"] = 4;
	$aEstados["GO"] = 6;
	$aEstados["MA"] = 6;
	$aEstados["MT"] = 6;
	$aEstados["MS"] = 6;
	$aEstados["MG"] = 4;
	$aEstados["PA"] = 6;
	$aEstados["PB"] = 5;
	$aEstados["PR"] = 3;
	$aEstados["PE"] = 5;
	$aEstados["PI"] = 5;
	$aEstados["RJ"] = 4;
	$aEstados["RN"] = 5;
	$aEstados["RS"] = 3;
	$aEstados["RO"] = 6;
	$aEstados["RR"] = 6;
	$aEstados["SC"] = 3;
	$aEstados["SP"] = 1;
	$aEstados["SE"] = 5;
	$aEstados["TO"] = 6;

	$aDNs = array();
	$aRegiao = array();
	$aRegiao[] = array("id_regiao"=>1, "nome"=>"Região 1/2", "total"=>0, "bc"=>1);
	$aRegiao[] = array("id_regiao"=>3, "nome"=>"Região 3", "total"=>0, "bc"=>3);
	$aRegiao[] = array("id_regiao"=>4, "nome"=>"Região 4", "total"=>0, "bc"=>4);
	$aRegiao[] = array("id_regiao"=>5, "nome"=>"Região 5", "total"=>0, "bc"=>5);
	$aRegiao[] = array("id_regiao"=>6, "nome"=>"Região 6", "total"=>0, "bc"=>6);


	function add_from_log($vId, $vNumero_dn, $vUf, $vNome)
	{
		global $aDNs, $aRegiao, $aEstados;

		$dn_added = false;
		for ($i=0; $i<count($aDNs); $i++)
		{
			if ($aDNs[$i]["id_dn"] == $vId)
			{
				$aDNs[$i]["total"] += 1;
				$dn_added = true;
				break;
			}
		}

		if (!$dn_added)
		{
			$aDNs[] = array("id_dn"=>$vId, "numero_dn"=>$vNumero_dn, "dn_nome"=>$vNome, "id_regiao"=>$aEstados[$vUf], "total"=>1);
		}

		for ($i=0; $i<count($aRegiao); $i++)
		{
			if ($aRegiao[$i]["id_regiao"] == $aEstados[$vUf])
			{
				$aRegiao[$i]["total"] += 1;
				break;
			}
		}
	}


	function get_regiao_name($vId)
	{
		global $aRegiao;

		for ($i=0; $i<count($aRegiao); $i++)
		{
			if ($aRegiao[$i]["id_regiao"] == $vId)
			{
				return $aRegiao[$i]["nome"];
				break;
			}
		}
	}	


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


	$data_inicio = usToMysql($pPeriodo_fr);
	$data_final = usToMysql($pPeriodo_to);
	
	$db->query("
SELECT 
	IF (clip.id > 0, clip.id, log.id_clienteusuario) AS dn,
    IF (clip.id > 0, cidp.uf, cid.uf) AS uf,
    IF (clip.id > 0, clip.nome, cli.nome) AS dn_nome,
	IF (clip.id > 0, clip.dn, cli.dn) AS numero_dn,
	DATE(log.data_hora) AS dt
FROM 
	gelic_log_login AS log
    INNER JOIN gelic_clientes AS cli ON cli.id = log.id_clienteusuario
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
    INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	LEFT JOIN gelic_cidades AS cidp ON cidp.id = clip.id_cidade
WHERE
	log.id_clienteusuario NOT IN (1,591) AND
	log.tipo IN (2,3) AND
	log.data_hora BETWEEN '$data_inicio 00:00:00' AND '$data_final 23:59:59'");
	while ($db->nextRecord())
	{
		if (!in_array($db->f("numero_dn"),array(9999,10101)))
		{
			$v = $db->f("dt")."-".$db->f("numero_dn");
			if (!in_array($v, $limit))
			{
				add_from_log($db->f("dn"), $db->f("numero_dn"), $db->f("uf"), utf8_encode($db->f("dn_nome")));
				$limit[] = $v;
			}
		}
	}



	function compare_q($a, $b)
	{
	    if ($a["total"] == $b["total"]) return 0;
	    return ($a["total"] > $b["total"]) ? -1 : 1;
	}
	usort($aRegiao, "compare_q");

	$t = 0;
	for ($i=0; $i<count($aRegiao); $i++)
		$t += $aRegiao[$i]["total"];

	$aRegiao[] = array("id_regiao"=>7, "nome"=>"TOTAL", "total"=>$t, "bc"=>7);


	usort($aDNs, "compare_q");


	if ($pFormato == "xlsx")
	{
		require_once "../../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Volume de Acessos por Região")
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
			->setCellValue("A$row", 'DN')
			->setCellValue("B$row", 'Nome do DN')
			->setCellValue("C$row", 'Região')
			->setCellValue("D$row", 'Acessos')
			->setCellValue("F$row", 'Região')
			->setCellValue("G$row", 'Acessos');

		for ($i=0; $i<count($aDNs); $i++)
		{
			$row += 1;

			$t = $aDNs[$i]["numero_dn"];
			if ($t == 232323) $t = "POOL PR";
			else if ($t == 242424) $t = "POOL RS";
			else if ($t == 252525) $t = "POOL DF";
			else if ($t == 262626) $t = "POOL MG";
			else if ($t == 272727) $t = "POOL SP";

			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $t)
				->setCellValue("B$row", $aDNs[$i]["dn_nome"])
				->setCellValue("C$row", get_regiao_name($aDNs[$i]["id_regiao"]))
				->setCellValue("D$row", $aDNs[$i]["total"]);
		}

		$r = 2;
		for ($i=0; $i<count($aRegiao); $i++)
		{
			$r += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("F$r", $aRegiao[$i]["nome"])
				->setCellValue("G$r", $aRegiao[$i]["total"]);
		}

		$phpexcel->getActiveSheet()->mergeCells("A1:G1");
		$phpexcel->getActiveSheet()->setTitle('Volume de Acessos por Região');
		$phpexcel->getActiveSheet()->getStyle("A1:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("C1:D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A2:D2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:D2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("F2:G2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("F2:G2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("G2:G$r")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$phpexcel->getActiveSheet()->getStyle("F$r:G$r")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(46);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(2);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);


		if (file_exists(UPLOAD_DIR."~volume_regional_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~volume_regional_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~volume_regional_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
	else if ($pFormato == "pdf")
	{
		//*************************************************
		//*******************  REGIOES  *******************
		//*************************************************
		$r_max = 0;
		for ($i=0; $i<count($aRegiao); $i++)
			if ($r_max < $aRegiao[$i]["total"]) { $r_max = $aRegiao[$i]["total"]; }

		$r_top_val = $r_max;

		while (!($r_max % 20) == 0) { $r_max += 1; }

		$r_tick_1 = round($r_max / 4 * 1);
		$r_tick_2 = round($r_max / 4 * 2);
		$r_tick_3 = round($r_max / 4 * 3);
		$r_tick_4 = round($r_max / 4 * 4);

		$r_graph_height = 180;
		$r_graph_width = 920;
		$r_bars_per_graph = 6;
		$r_col_width = floor($r_graph_width / $r_bars_per_graph);
		$r_bar_width = floor($r_col_width / 2);
		$r_bar_left = floor(($r_col_width / 2) - ($r_bar_width / 2));

		$r_mark_1_bottom = $r_graph_height + 10;
		$r_mark_2_bottom = round($r_graph_height / 4 * 3) + 10;
		$r_mark_3_bottom = round($r_graph_height / 4 * 2) + 10;
		$r_mark_4_bottom = round($r_graph_height / 4) + 10;
		$r_mark_5_bottom = 10;

		$r_m1_bottom = $r_mark_1_bottom - 10;
		$r_m2_bottom = $r_mark_2_bottom - 10;
		$r_m3_bottom = $r_mark_3_bottom - 10;
		$r_m4_bottom = $r_mark_4_bottom - 10;
		$r_m5_bottom = $r_mark_5_bottom - 10;

		$r_cols = '';
		$r_caption = '';
		$r_output = '<div class="block"><div class="holder">
				<div class="r_left-scale">
					<span class="r_m1">'.$r_tick_4.'</span>
					<span class="r_m2">'.$r_tick_3.'</span>
					<span class="r_m3">'.$r_tick_2.'</span>
					<span class="r_m4">'.$r_tick_1.'</span>
					<span class="r_m5">0</span>
				</div>
				<div class="r_mark-1"></div>
				<div class="r_mark-2"></div>
				<div class="r_mark-3"></div>
				<div class="r_mark-4"></div>';

		$j = 0;

		if (count($aRegiao) == 0)
			$r_output .= '<div class="r_mark-5"></div></div>';

		for ($i=0; $i<count($aRegiao); $i++)
		{
			if ($r_max == 0)
				$bh = 0;
			else
				$bh = round($r_graph_height * $aRegiao[$i]["total"] / $r_max);

			$r_cols .= '<div class="r_chart-col">
				<div class="r_bar r_bar-'.$aRegiao[$i]["bc"].'" style="height:'.$bh.'px;"></div>
				<span class="r_lb r_lb-'.$aRegiao[$i]["bc"].'" style="bottom:'.($bh+14).'px;">'.$aRegiao[$i]["total"].'</span>
			</div>';
			
			$r_caption .= '<span class="r_txt" style="left:'.($j*$r_col_width+40).'px;"><a class="bold">'.$aRegiao[$i]["nome"].'</a></span>';
			$j += 1;			

			if (($i+1) % $r_bars_per_graph == 0 || ($i+1) == count($aRegiao))
			{
				$r_output .= $r_cols.'<div class="r_mark-5"></div></div>
				<div class="holder" style="height:120px;margin-top:-10px;">
					'.$r_caption.'
				</div></div>';
		
				$r_cols = '';
				$r_dates = '';
				$j = 0;

				if (($i+1) < count($aRegiao))
					$r_output .= '<div class="block"><div class="holder">
					<div class="r_left-scale">
						<span class="r_m1">'.$r_tick_4.'</span>
						<span class="r_m2">'.$r_tick_3.'</span>
						<span class="r_m3">'.$r_tick_2.'</span>
						<span class="r_m4">'.$r_tick_1.'</span>
						<span class="r_m5">0</span>
					</div>
					<div class="r_mark-1"></div>
					<div class="r_mark-2"></div>
					<div class="r_mark-3"></div>
					<div class="r_mark-4"></div>';
			}
		}
		//*************************************************
		//*************************************************
		//*************************************************






		//*********************************************
		//*******************  DNs  *******************
		//*********************************************
		$aDNs = array_slice($aDNs, 0, 42);

		$d_max = 0;
		for ($i=0; $i<count($aDNs); $i++)
			if ($d_max < $aDNs[$i]["total"]) { $d_max = $aDNs[$i]["total"]; }

		$d_top_val = $d_max;

		while (!($d_max % 20) == 0) { $d_max += 1; }

		$d_tick_1 = round($d_max / 4 * 1);
		$d_tick_2 = round($d_max / 4 * 2);
		$d_tick_3 = round($d_max / 4 * 3);
		$d_tick_4 = round($d_max / 4 * 4);

		$d_graph_height = 180;
		$d_graph_width = 920;
		$d_bars_per_graph = 7;
		$d_col_width = floor($d_graph_width / $d_bars_per_graph);
		$d_bar_width = floor($d_col_width / 2);
		$d_bar_left = floor(($d_col_width / 2) - ($d_bar_width / 2));

		$d_mark_1_bottom = $d_graph_height + 10;
		$d_mark_2_bottom = round($d_graph_height / 4 * 3) + 10;
		$d_mark_3_bottom = round($d_graph_height / 4 * 2) + 10;
		$d_mark_4_bottom = round($d_graph_height / 4) + 10;
		$d_mark_5_bottom = 10;

		$d_m1_bottom = $d_mark_1_bottom - 10;
		$d_m2_bottom = $d_mark_2_bottom - 10;
		$d_m3_bottom = $d_mark_3_bottom - 10;
		$d_m4_bottom = $d_mark_4_bottom - 10;
		$d_m5_bottom = $d_mark_5_bottom - 10;

		$d_cols = '';
		$d_caption = '';
		$d_output = '<div class="block"><div class="holder">
				<div class="d_left-scale">
					<span class="d_m1">'.$d_tick_4.'</span>
					<span class="d_m2">'.$d_tick_3.'</span>
					<span class="d_m3">'.$d_tick_2.'</span>
					<span class="d_m4">'.$d_tick_1.'</span>
					<span class="d_m5">0</span>
				</div>
				<div class="d_mark-1"></div>
				<div class="d_mark-2"></div>
				<div class="d_mark-3"></div>
				<div class="d_mark-4"></div>';

		if (count($aDNs) == 0)
			$d_output .= '<div class="d_mark-5"></div></div>';

		for ($i=0; $i<count($aDNs); $i++)
		{
			if ($d_max == 0)
				$bh = 0;
			else
				$bh = round($d_graph_height * $aDNs[$i]["total"] / $d_max);

			$d_cols .= '<div class="d_chart-col">
				<div class="d_bar" style="height:'.$bh.'px;"></div>
				<span class="d_lb" style="bottom:'.($bh+14).'px;">'.$aDNs[$i]["total"].'</span>
			</div>';
			
			$t = $aDNs[$i]["numero_dn"];
			if ($t == 232323) $t = "POOL PR";
			else if ($t == 242424) $t = "POOL RS";
			else if ($t == 252525) $t = "POOL DF";
			else if ($t == 262626) $t = "POOL MG";
			else if ($t == 272727) $t = "POOL SP";
			else $t = "DN: ".$t;

			$d_caption .= '<span class="d_txt" style="left:'.($j*$d_col_width+40).'px;"><a class="bold">'.($i+1).'</a><br><a class="bold">'.$t.'</a><br><a>'.get_regiao_name($aDNs[$i]["id_regiao"]).'</a></span>';
			$j += 1;			

			if (($i+1) % $d_bars_per_graph == 0 || ($i+1) == count($aDNs))
			{
				$d_output .= $d_cols.'<div class="d_mark-5"></div></div>
				<div class="holder" style="height:120px;margin-top:-10px;">
					'.$d_caption.'
				</div></div>';
		
				$d_cols = '';
				$d_caption = '';
				$j = 0;

				if (($i+1) < count($aDNs))
					$d_output .= '<div class="block"><div class="holder">
					<div class="d_left-scale">
						<span class="d_m1">'.$d_tick_4.'</span>
						<span class="d_m2">'.$d_tick_3.'</span>
						<span class="d_m3">'.$d_tick_2.'</span>
						<span class="d_m4">'.$d_tick_1.'</span>
						<span class="d_m5">0</span>
					</div>
					<div class="d_mark-1"></div>
					<div class="d_mark-2"></div>
					<div class="d_mark-3"></div>
					<div class="d_mark-4"></div>';
			}
		}
		//*********************************************
		//*********************************************
		//*********************************************



		$tHtml = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Volume de Acessos por Região</title>
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

		.r_chart-col {
			position: relative;
			width: '.$r_col_width.'px;
			height: '.($r_graph_height+40).'px;
			float: left;
			}

		.d_chart-col {
			position: relative;
			width: '.$d_col_width.'px;
			height: '.($d_graph_height+40).'px;
			float: left;
			}

		.r_bar {
			position: absolute;
			left: '.$r_bar_left.'px;
			bottom: 10px;
			width: '.$r_bar_width.'px;
			}

		.r_bar-1 { background-color: #4285f4; }
		.r_bar-3 { background-color: #f4c70f; }
		.r_bar-4 { background-color: #5bb95c; }
		.r_bar-5 { background-color: #aa67b2; }
		.r_bar-6 { background-color: #fd430b; }
		.r_bar-7 { background-color: #202020; }

		.d_bar {
			position: absolute;
			left: '.$d_bar_left.'px;
			bottom: 10px;
			width: '.$r_bar_width.'px;
			background-color: #4285f4;
			}
	
		.r_lb {	
			position: absolute;
			left: 0;
			bottom: 0;
			width: '.$r_col_width.'px;
			text-align: center;
			font-size: 11px;
			}

		.r_lb-1 { color: #4285f4; }
		.r_lb-3 { color: #f4c70f; }
		.r_lb-4 { color: #5bb95c; }
		.r_lb-5 { color: #aa67b2; }
		.r_lb-6 { color: #fd430b; }
		.r_lb-7 { color: #202020; }

		.d_lb {	
			position: absolute;
			left: 0;
			bottom: 0;
			width: '.$d_col_width.'px;
			text-align: center;
			font-size: 11px;
			color: #4285f4;
			}

		.r_txt {
			position: absolute;
			top: 6px;
			left: 0;
			width: '.$r_col_width.'px;
			font-size: 11px;
			text-align: center;
			}

		.d_txt {
			position: absolute;
			top: 6px;
			left: 0;
			width: '.$d_col_width.'px;
			font-size: 11px;
			text-align: center;
			}

		.bold { font-weight: bold; font-size: 12px; }

		.r_left-scale {
			position: relative;
			float: left;
			width: 40px;
			height: '.($r_graph_height+40).'px;
			}

		.d_left-scale {
			position: relative;
			float: left;
			width: 40px;
			height: '.($d_graph_height+40).'px;
			}

		.r_mark-1 {
			position: absolute;
			right: 0;
			bottom: '.$r_mark_1_bottom.'px;
			width: '.$r_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.r_mark-2 {
			position: absolute;
			right: 0;
			bottom: '.$r_mark_2_bottom.'px;
			width: '.$r_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.r_mark-3 {
			position: absolute;
			right: 0;
			bottom: '.$r_mark_3_bottom.'px;
			width: '.$r_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.r_mark-4 {
			position: absolute;
			right: 0;
			bottom: '.$r_mark_4_bottom.'px;
			width: '.$r_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.r_mark-5 {
			position: absolute;
			right: 0;
			bottom: '.$r_mark_5_bottom.'px;
			width: '.$r_graph_width.'px;
			height: 1px;
			background-color: #000000;
			}

		.r_m1 {
			position: absolute;
			right: 8px;
			bottom: '.$r_m1_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.r_m2 {
			position: absolute;
			right: 8px;
			bottom: '.$r_m2_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.r_m3 {
			position: absolute;
			right: 8px;
			bottom: '.$r_m3_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.r_m4 {
			position: absolute;
			right: 8px;
			bottom: '.$r_m4_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.r_m5 {
			position: absolute;
			right: 8px;
			bottom: 0;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.d_mark-1 {
			position: absolute;
			right: 0;
			bottom: '.$d_mark_1_bottom.'px;
			width: '.$d_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.d_mark-2 {
			position: absolute;
			right: 0;
			bottom: '.$d_mark_2_bottom.'px;
			width: '.$d_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.d_mark-3 {
			position: absolute;
			right: 0;
			bottom: '.$d_mark_3_bottom.'px;
			width: '.$d_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.d_mark-4 {
			position: absolute;
			right: 0;
			bottom: '.$d_mark_4_bottom.'px;
			width: '.$d_graph_width.'px;
			height: 1px;
			background-color: #cccccc;
			}

		.d_mark-5 {
			position: absolute;
			right: 0;
			bottom: '.$d_mark_5_bottom.'px;
			width: '.$d_graph_width.'px;
			height: 1px;
			background-color: #000000;
			}

		.d_m1 {
			position: absolute;
			right: 8px;
			bottom: '.$d_m1_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.d_m2 {
			position: absolute;
			right: 8px;
			bottom: '.$d_m2_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.d_m3 {
			position: absolute;
			right: 8px;
			bottom: '.$d_m3_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.d_m4 {
			position: absolute;
			right: 8px;
			bottom: '.$d_m4_bottom.'px;
			line-height: 21px;
			color: #888888;
			font-size: 11px;
			}

		.d_m5 {
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
		Volume de Acessos por Região
	</div>
	<div class="sub-title">
		Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')
	</div>
	'.$r_output.'
	<div class="title" style="margin-top:80px;">
		Maiores Acessos por DN
	</div>
	<div class="sub-title">
		Período escolhido ('.$data_selecionada_fr.' - '.$data_selecionada_to.')
	</div>
	'.$d_output.'
</div>
</body>
</html>';

		$oFile = fopen(UPLOAD_DIR."~volume_regional_".$sInside_id.".html", "w");
		fwrite($oFile, $tHtml);
		fclose($oFile);

		exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~volume_regional_".$sInside_id.".html ".UPLOAD_DIR."~volume_regional_".$sInside_id.".pdf");
		@unlink(UPLOAD_DIR."~volume_regional_".$sInside_id.".html");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
