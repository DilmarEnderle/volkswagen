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
		$aDealers = array("dealers"=>0,"not_used"=>0,"inactive"=>0,"active"=>0);
	
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

		$pPeriodo_fr_mo = substr($pPeriodo_fr,0,2);
		$pPeriodo_fr_yr = substr($pPeriodo_fr,6,4);
		$pPeriodo_to_mo = substr($pPeriodo_to,0,2);
		$pPeriodo_to_yr = substr($pPeriodo_to,6,4);


		//AJUSTAR DATA PERIODO DE PARA O INICIO DO MES
		$pPeriodo_fr = $pPeriodo_fr_mo."/01/".$pPeriodo_fr_yr;
	
		//AJUSTAR DATA PERIODO ATE PARA O FINAL DO MES
		$pPeriodo_to = $pPeriodo_to_mo."/".cal_days_in_month(CAL_GREGORIAN, $pPeriodo_to_mo, $pPeriodo_to_yr)."/".$pPeriodo_to_yr;


		$data_selecionada_fr = usToBr($pPeriodo_fr);
		$data_selecionada_to = usToBr($pPeriodo_to);

		$mes_fr = intval($pPeriodo_fr_mo);
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

	
		$aMonth = array("","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");


		//=================================
		// DEALERS
		//=================================
		$db->query("SELECT COUNT(*) AS total FROM gelic_clientes WHERE tipo = 2");
		$db->nextRecord();
		$aDealers["dealers"] = $db->f("total");



		//=================================
		// NOT USED
		//=================================
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
				$aDealers["not_used"] += 1;
		}



		//=================================
		// INACTIVE
		//=================================
		$aSemanas = array();
		$aUnique = array();
		$aInativos = array();

		//AJUSTAR DATA PERIODO DE PARA O INICIO DA SEMANA
		$day_of_week = date('w', strtotime('12/01/2015')); //day of the week selected 0..6 sun..sat
		if ($day_of_week > 0)
			$pPeriodo_fr = date("m/d/Y", strtotime("12/01/2015 -".$day_of_week." day"));

		//AJUSTAR DATA PERIODO ATE PARA O FINAL DA SEMANA
		$day_of_week = date('w', strtotime(date('m/d/Y'))); //day of the week selected 0..6 sun..sat
		if ($day_of_week < 6)
			$pPeriodo_to = date("m/d/Y", strtotime(date('m/d/Y')." +".(6-$day_of_week)." day"));

		$semana_fr = usToMysql($pPeriodo_fr);
		$semana_to = date("Y-m-d", strtotime($semana_fr." +6 day")); 	
		$semana_to_int = intval(str_replace("-","",$semana_to));
		$stop_int = intval(date("Ymd", strtotime($pPeriodo_to)));
		$at = 1;
		while ($semana_to_int <= $stop_int)
		{
			$a = array();
			$db->query("
SELECT 
	IF (clip.id > 0, clip.id, log.id_clienteusuario) AS dn
FROM 
	gelic_log_login AS log
    INNER JOIN gelic_clientes AS cli ON cli.id = log.id_clienteusuario
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	log.tipo IN (2,3) AND
	log.id_clienteusuario NOT IN (1,591,8,84,88,260,405,426,454,535,552,9,76,102,531,557,21,50,112,154,202,314,352,387,480,49,190,220,262,357,365,5,7,66,72,77,92,100,114,127,161,197,205,211,213,254,280,282,306,307,334,337,350,361,362,406,409,413,474,588) AND
	log.data_hora BETWEEN '".$semana_fr." 00:00:00' AND '".$semana_to." 23:59:59'
GROUP BY
	dn");
			while ($db->nextRecord())
			{
				$a[] = $db->f("dn");
				if (!in_array($db->f("dn"), $aUnique))
					$aUnique[] = $db->f("dn");
			}

			$aSemanas[] = array("semana"=>$at, "semana_fr"=>$semana_fr." 00:00:00", "semana_to"=>$semana_to." 23:59:59", "dns"=>$a);

			$semana_fr = date("Y-m-d", strtotime($semana_fr." +7 day"));
			$semana_to = date("Y-m-d", strtotime($semana_fr." +6 day"));
			$semana_to_int = intval(str_replace("-","",$semana_to));
			$at += 1;
		}

		for ($i=0; $i<count($aUnique); $i++)
		{
			$a = array();
			$dn = $aUnique[$i];

			for ($j=0; $j<count($aSemanas); $j++)
			{
				if (in_array($dn, $aSemanas[$j]["dns"]))
					$a[] = $aSemanas[$j]["semana"];
			}
	
			$aUnique[$i] = array("dn"=>$dn, "seq"=>$a);
		
			if (count($aSemanas) - $a[count($a)-1] > 1)
				$aInativos[] = $dn;
		}

		$aDealers["inactive"] = count($aInativos);



		//=================================
		// ACTIVE
		//=================================
		$aDealers["active"] = $aDealers["dealers"] - $aDealers["not_used"] - $aDealers["inactive"];



		if ($pFormato == "xlsx")
		{
			require_once "../Phpexcel-1.8.0/PHPExcel.php";
	
			$phpexcel = new PHPExcel();
			$phpexcel->getProperties()->setCreator("GELIC")
				->setLastModifiedBy("GELIC")
				->setTitle("Dealers Access")
				->setSubject("Acessos")
				->setDescription("Acessos ao Sistema GELIC")
				->setKeywords("office 2007 openxml php gelic")
				->setCategory("Acessos");
								 
			$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
			$phpexcel->getDefaultStyle()->getFont()->setSize(10);

			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A1", 'Reporting period ('.$data_selecionada_fr.' - '.$data_selecionada_to.')');

			$row = 2;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", 'Month')
				->setCellValue("B$row", 'Access');

			for ($i=0; $i<count($aMes_total); $i++)
			{
				$row += 1;

				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", $aMonth[$aMes_total[$i]["mes"]]."/".substr($aMes_total[$i]["ano"], -2))
					->setCellValue("B$row", $aMes_total[$i]["cumulativo"]);
			}

			$phpexcel->getActiveSheet()->mergeCells("A1:B1");
			$phpexcel->getActiveSheet()->setTitle('Dealers Access');
			$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A2:B2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A2:B2")->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
			$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);

			$vrow = $row + 2;

			$vrow += 1;
			$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$vrow", "Dealers")
					->setCellValue("B$vrow", $aDealers["dealers"]);

			$vrow += 1;
			$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$vrow", "Not Used")
					->setCellValue("B$vrow", $aDealers["not_used"]);

			$vrow += 1;
			$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$vrow", "Inactive")
					->setCellValue("B$vrow", $aDealers["inactive"]);

			$vrow += 1;
			$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$vrow", "Active")
					->setCellValue("B$vrow", $aDealers["active"]);

			$phpexcel->getActiveSheet()->getStyle("A".($row+2).":B".($row+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
			$phpexcel->getActiveSheet()->getStyle("A".($row+2).":B".($row+2))->getFont()->setBold(true);
			$phpexcel->getActiveSheet()->getStyle("A2:A$vrow")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$phpexcel->getActiveSheet()->getStyle("B2:B$vrow")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$phpexcel->getActiveSheet()->getStyle("A1:B$vrow")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

			if (file_exists(UPLOAD_DIR."~vw_access_".$sInside_id.".xlsx"))
				unlink(UPLOAD_DIR."~vw_access_".$sInside_id.".xlsx");

			$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
			$obwriter->save(UPLOAD_DIR."~vw_access_".$sInside_id.".xlsx");

			$aReturn[0] = 1; //sucesso
		}
		else if ($pFormato == "pdf")
		{
			$max = 0;
			for ($i=0; $i<count($aMes_total); $i++)
				if ($max < $aMes_total[$i]["cumulativo"]) { $max = $aMes_total[$i]["cumulativo"]; }

			$bar_w = floor(1408 / count($aMes_total));
			if ($bar_w > 352) $bar_w = 352;

			$reporting_period = 'Reporting period – '.$data_selecionada_fr.' to '.$data_selecionada_to;

			$bars = '';
			$labels = '';
			for ($i=0; $i<count($aMes_total); $i++)
			{
				$h = ceil(252 * $aMes_total[$i]["cumulativo"] / $max);
				$t = 276 - $h;

				$bars .= '<div class="bar">
					<div class="bar-box" style="top: '.$t.'px; height: '.$h.'px; background-color: #003c65;"></div>
					<div class="bar-txt" style="top: '.($t+20).'px;"><span style="color: #ffffff;">'.$aMes_total[$i]["cumulativo"].'</span></div>
				</div>';

				$labels .= '<span>'.$aMonth[$aMes_total[$i]["mes"]]."/".substr($aMes_total[$i]["ano"], -2).'</span>';
			}


			//bar 0
			$bar0_top = 24;
			$bar0_height = 324;
			$bar0_top_txt = round($bar0_height / 2) - 16 + 24;


			//bar 1
			$bar1_top = 24;
			$bar1_height = round(324 * $aDealers["not_used"] / $aDealers["dealers"]);
			if ($bar1_height == 0) $bar1_height = 1;
			$bar1_top_txt = round($bar1_height / 2) - 16 + 24;
			$gap1_top = $bar1_top + $bar1_height - 1;


			//bar 2
			$bar2_top = $gap1_top + 1;
			$bar2_height = round(324 * $aDealers["inactive"] / $aDealers["dealers"]);
			if ($bar2_height == 0) $bar2_height = 1;
			$bar2_top_txt = round($bar2_height / 2) - 16 + $bar2_top;
			$gap2_top = $bar2_top + $bar2_height - 1;


			//bar 3
			$bar3_top = $gap2_top + 1;
			$bar3_height = round(324 * $aDealers["active"] / $aDealers["dealers"]);
			if ($bar3_height == 0) $bar3_height = 1;
			$bar3_top_txt = round($bar3_height / 2) - 16 + $bar3_top;



			$tHtml = '
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title>Acessos ao Sistema</title>
		<style>
			body { font-family: \'Arial\',\'Trebuchet MS\',\'Tahoma\'; font-size: 14px; color: #000000; }

			.header { 
				position: relative;
				height: 92px;
			}

			.header span { 
				position: absolute;
				left: 0;
				top: 0;
				font-size: 60px;
				font-weight: bold;
				color: #000000;
			}

			.header img {
				position: absolute;
				right: 0;
				top: 0;
				border: 0;
				width: 131px;
				height: 92px;
			}

			.gap { height: 32px; }

			.info-holder { float: left; width: 1462px; }
			.info-left {
				float: left;
				width: 60px;
				height: 930px;
			}

			.info-left span {
				float: left;
				background-color: #003c65;
				color: #ffffff;
				font-size: 32px;
				font-weight: bold;
				line-height: 60px;
				text-align: center;
				width: 930px;
				margin-top: 24px;
				-webkit-transform: rotate(-90deg);
				-webkit-transform-origin: 465px 465px;
			}

			.info { position: relative; height: 300px; }
			.dinfo { position: relative; height: 372px; }
	
			.bottom-line {
				position: absolute;
				right: 0;
				bottom: 22px;
				width: 1416px;
				height: 2px;
				background-color: #003c65;
			}

			.sub-title {
				position: relative;
				height: 72px;
				text-align: center;
				line-height: 72px;
				font-size: 24px;
				font-weight: bold;
			}

			.vgap { float: left; width: 54px; height: 300px; }
			.vgap-24 { position: relative; float: left; width: 24px; height: 372px; }
			.vgap-24 div { position: absolute; left: 0; top: 23px; width: 24px; border-bottom: 1px dotted #666666; }
			.bar { position: relative; float: left; width: '.$bar_w.'px; height: 300px; }
			.bar-box { position: absolute; left: '.round(($bar_w / 2) - (($bar_w * 66 / 100) / 2)).'px; top: 24px; width: '.round($bar_w * 66 / 100).'px; height: 324px; }
			.bar-txt { position: absolute; left: 0; top: 24px; width: '.$bar_w.'px; text-align: center; }
			.bar-txt span { display: inline-block; line-height: 32px; font-size: 20px; font-weight: bold; padding: 0 6px; }

			.dbar { position: relative; float: left; width: 154px; height: 372px; }
			.dbar-box { position: absolute; left: 0; top: 24px; width: 154px; height: 324px; }
			.dbar-txt { position: absolute; left: 0; top: 24px; width: 154px; text-align: center; }
			.dbar-txt span { display: inline-block; line-height: 32px; font-size: 28px; font-weight: bold; padding: 0 6px; }


			.labels { height: 62px; }

			.labels span {
				float: left;
				line-height: 30px;
				text-align: center;
				width: '.$bar_w.'px;
				font-size: 24px;
				font-weight: bold;
				color: #444444;
			}

			.dlabels { height: 62px; }

			.dlabels span {
				float: left;
				line-height: 30px;
				text-align: center;
				width: 178px;
				font-size: 24px;
				font-weight: bold;
				color: #444444;
			}

			.divider { height: 62px; }

			.footer {
				position: relative;
				height: 92px;
				clear: both;
			}

			.footer span {
				position: absolute;
				left: 0;
				bottom: 0;
				font-size: 28px;
				font-weight: bold;
				color: #444444;
			}

			.footer img {
				position: absolute;
				right: 0;
				bottom: 0;
				width: 92px;
				height: 92px;
			}

		</style>
	</head>
	<body>
	<div style="width: 1522px; overflow: hidden;">
		<div class="header">
			<span>DEALERS ACCESS</span>
			<img src="../admin/img/brflag.png">
		</div>
		<div class="gap"></div>
		<div class="info-left"><span>GELIC - SYSTEM ACCESS</span></div>
		<div class="info-holder">
			<div class="sub-title">SYSTEM ACCESS</div>
			<div class="info">
				<div class="bottom-line"></div>
				<div class="vgap"></div>
				'.$bars.'
			</div>
			<div class="labels">
				<div class="vgap" style="height:62px;"></div>
				'.$labels.'
			</div>
			<div class="divider"></div>
			<div class="dinfo">
				<div class="bottom-line"></div>
				<div class="vgap"></div>
				<div class="dbar">
					<div class="dbar-box" style="top: '.$bar0_top.'px; height: '.$bar0_height.'px; background-color: #4285f4;"></div>
					<div class="dbar-txt" style="top: '.$bar0_top_txt.'px;"><span style="color: #ffffff;">'.$aDealers["dealers"].'</span></div>
				</div>
				<div class="vgap-24"><div></div></div>
				<div class="dbar">
					<div class="dbar-box" style="top: '.$bar1_top.'px; height: '.$bar1_height.'px; background-color: #b2b2b2;"></div>
					<div class="dbar-txt" style="top: '.$bar1_top_txt.'px;"><span style="color: #33434c; background-color: #b2b2b2;">'.$aDealers["not_used"].'</span></div>
				</div>
				<div class="vgap-24"><div style="top: '.$gap1_top.'px;"></div></div>
				<div class="dbar">
					<div class="dbar-box" style="top: '.$bar2_top.'px; height: '.$bar2_height.'px; background-color: #b2b2b2;"></div>
					<div class="dbar-txt" style="top: '.$bar2_top_txt.'px;"><span style="color: #33434c; background-color: #b2b2b2;">'.$aDealers["inactive"].'</span></div>
				</div>
				<div class="vgap-24"><div style="top: '.$gap2_top.'px;"></div></div>
				<div class="dbar">
					<div class="dbar-box" style="top: '.$bar3_top.'px; height: '.$bar3_height.'px; background-color: #b2b2b2;"></div>
					<div class="dbar-txt" style="top: '.$bar3_top_txt.'px;"><span style="color: #33434c; background-color: #b2b2b2;">'.$aDealers["active"].'</span></div>
				</div>
			</div>
			<div class="dlabels">
				<span style="margin-left: 42px;">Dealers</span>
				<span>Not Used</span>
				<span>Inactive</span>
				<span>Active</span>
			</div>
		</div>
		<div class="footer">
			<span>'.$reporting_period.'</span>
			<img src="../admin/img/vwlogo.png">
		</div>
	</div>
	</body>
	</html>';

			$oFile = fopen(UPLOAD_DIR."~vw_access_".$sInside_id.".html", "w");
			fwrite($oFile, $tHtml);
			fclose($oFile);

			exec(PATH_HTMTOPDF." --orientation landscape --page-size Letter --image-quality 100 ".UPLOAD_DIR."~vw_access_".$sInside_id.".html ".UPLOAD_DIR."~vw_access_".$sInside_id.".pdf");
			@unlink(UPLOAD_DIR."~vw_access_".$sInside_id.".html");

			$aReturn[0] = 1; //sucesso
		}
	}
}
echo json_encode($aReturn);

?>
