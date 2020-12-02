<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pYear = intval($_POST["y"]);
	$pMonth = intval($_POST["m"]);
	$pDay = intval($_POST["d"]);
	$pType = intval($_POST["t"]);
	$pYear_min = 2014;
	$pYear_max = intval($_POST["mxy"]);

	$pOrder = intval($_POST["ord"]);
	$aOrder = array("Todas", "Com APL Enviada", "Com APL Aprovada", "Aguardando Aprovação de APL", "Sem APL Enviada");

	$aMonth = array();
	$aMonth[1] = "Janeiro";
	$aMonth[2] = "Fevereiro";
	$aMonth[3] = "Março";
	$aMonth[4] = "Abril";
	$aMonth[5] = "Maio";
	$aMonth[6] = "Junho";
	$aMonth[7] = "Julho";
	$aMonth[8] = "Agosto";
	$aMonth[9] = "Setembro";
	$aMonth[10] = "Outubro";
	$aMonth[11] = "Novembro";
	$aMonth[12] = "Dezembro";

	$aWeek = array();
	$aWeek[0] = "Domingo";
	$aWeek[1] = "Segunda-feira";
	$aWeek[2] = "Terça-feira";
	$aWeek[3] = "Quarta-feira";
	$aWeek[4] = "Quinta-feira";
	$aWeek[5] = "Sexta-feira";
	$aWeek[6] = "Sábado";

	$data_hoje = date("Y-m-d");

	$db = new Mysql();

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];
	}

	//verificar se a data é valida
	if ($pYear >= $pYear_min && $pYear <= $pYear_max && checkdate($pMonth,$pDay,$pYear))
	{
		saveConfig("tab", "12");
		saveConfig("is_search", "0");

		$a["year"] = $pYear;
		$a["month"] = $pMonth;
		$a["day"] = $pDay;
		$a["order"] = $pOrder;
		$a["view"] = $pType;
		$json_string = json_encode($a);
		saveConfig("calendar", $json_string);


		if ($pType == 1)
		{
			//***********************************
			//***            MES              ***
			//***********************************
			$tOutput = '<div class="t14 bold cal_title">'.$aMonth[$pMonth].', '.$pYear.'<br><a class="t12 normal">'.$aOrder[$pOrder].'</a> <a id="printer" href="javascript:doPrint();" title="Imprimir" style="position: absolute; right: 0; bottom: 0;"><img src="img/ico_print.png" style="border: 0;"></a></div><div class="cal_cap" style="border-left: 1px solid #000000;">Domingo</div><div class="cal_cap">Segunda</div><div class="cal_cap">Terça</div><div class="cal_cap">Quarta</div><div class="cal_cap">Quinta</div><div class="cal_cap">Sexta</div><div class="cal_cap">Sábado</div>';

			$days_in_month = cal_days_in_month(CAL_GREGORIAN, $pMonth, $pYear);
			if ($pMonth == 1)
				$days_pr_month = 31;
			else
				$days_pr_month = cal_days_in_month(CAL_GREGORIAN, $pMonth-1, $pYear);

			$day_of_week_first = date('w', strtotime($pMonth.'/1/'.$pYear)); //day of the week of 1st of the month selected 0..6 sun..sat
			$day_of_week_last = date('w', strtotime($pMonth.'/'.$days_in_month.'/'.$pYear)); //day of the week of last of the month 0..6 sun..sat

			$frDate = $pYear . "-" . str_pad($pMonth,2,"0",STR_PAD_LEFT) . "-01 00:00:00";
			$toDate = $pYear . "-" . str_pad($pMonth,2,"0",STR_PAD_LEFT) . "-" . $days_in_month . " 23:59:59";
			$data = array(); //holds the info from the database for faster processing

			if ($pOrder == 0)
			{
				// ---------- TODAS ----------

				$select = "lic.id, lic.valor, DAY(lic.datahora_abertura) AS dia";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 1)
			{
				// ---------- COM APL ENVIADA ----------

				$select = "lic.id, lic.valor, DAY(lic.datahora_abertura) AS dia";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 2)
			{
				// ---------- COM APL APROVADA ----------

				$select = "lic.id, lic.valor, DAY(lic.datahora_abertura) AS dia";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente AND ear.aprovadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.aprovadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 3)
			{
				// ---------- AGUARDANDO APROVACAO DE APL ----------

				$select = "lic.id, lic.valor, DAY(lic.datahora_abertura) AS dia";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente AND ear.enviadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.enviadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 4)
			{
				// ---------- SEM APL ENVIADA ----------

				$select = "lic.id, lic.valor, DAY(lic.datahora_abertura) AS dia";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						apl.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						apl.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}


			while ($db->nextRecord())
				$data[$db->f("dia")][] = array($db->f("id"), number_format($db->f("valor"),2,",","."));

			//desenhar calendario
			if ($day_of_week_first > 0)
				for ($i=$days_pr_month-$day_of_week_first+1; $i<=$days_pr_month; $i++)
					if ($i == $days_pr_month-$day_of_week_first+1)
						$tOutput .= '<div class="cal_data_mo d_row_1" style="border-left: 1px solid #000000;"><span class="t12 gray_aa dom">'.$i.'</span></div>';
					else
						$tOutput .= '<div class="cal_data_mo d_row_1"><span class="t12 gray_aa dom">'.$i.'</span></div>';

			$col = $day_of_week_first + 1; //1..7
			$row = 1;
			for ($i=1; $i<=$days_in_month; $i++)
			{
				$box_data = "";
				if (array_key_exists($i,$data))
				{
					$rows = "";
					for ($k=0; $k<count($data[$i]); $k++)
						$rows .= '<a class="cal-btn" href="index.php?p=cli_open&id='.$data[$i][$k][0].'">'.$data[$i][$k][0].' (R$ '.$data[$i][$k][1].')</a>';

					if (count($data[$i]) < 6)
						$box_data .= '<div class="cal_data_mo_bot" style="position: absolute; left: 0; bottom: 0;">'.$rows.'</div>';
					else
						$box_data .= '<div class="cal_data_mo_bot"><div style="height: 20px;"></div>'.$rows.'</div>';
				}

				if ($col == 1)
					$tOutput .= '<div class="cal_data_mo d_row_'.$row.'" style="border-left: 1px solid #000000;"><span class="t12 gray_28 dom">'.$i.'</span>'.$box_data.'</div>';
				else
					$tOutput .= '<div class="cal_data_mo d_row_'.$row.'"><span class="t12 gray_28 dom">'.$i.'</span>'.$box_data.'</div>';

				$col++;
				if ($col == 8)
				{
					$col = 1;
					$row++;
				}
			}
			if ($day_of_week_last < 6)
				for ($i=1; $i<=6-$day_of_week_last; $i++) $tOutput .= '<div class="cal_data_mo d_row_'.$row.'"><span class="t12 gray_aa dom">'.$i.'</span></div>';	
		}
		else if ($pType == 2)
		{
			//***********************************
			//***           SEMANA            ***
			//***********************************
			$days_in_month = cal_days_in_month(CAL_GREGORIAN, $pMonth, $pYear);
			if ($pMonth == 1)
				$days_pr_month = 31;
			else
				$days_pr_month = cal_days_in_month(CAL_GREGORIAN, $pMonth-1, $pYear);

			$date_unix = strtotime($pMonth.'/'.$pDay.'/'.$pYear);
			$day_of_week = date('w', $date_unix); //day of the week selected 0..6 sun..sat
			$frDate = date("Y-m-d", strtotime('-'.$day_of_week.' day', $date_unix))." 00:00:00";
			$frDate_unix = strtotime(substr($frDate,5,2).'/'.substr($frDate,8,2).'/'.substr($frDate,0,4));
			$toDate = date("Y-m-d", strtotime('+6 day', $frDate_unix))." 23:59:59";

			$tOutput = '<div class="t14 bold cal_title">Semana de '.mysqlToBr(substr($frDate,0,10)).' à '.mysqlToBr(substr($toDate,0,10)).'<br><a class="t12 normal">'.$aOrder[$pOrder].'</a> <a id="printer" href="javascript:doPrint();" title="Imprimir" style="position: absolute; right: 0; bottom: 0;"><img src="img/ico_print.png" style="border: 0;"></a></div><div class="cal_cap" style="border-left: 1px solid #000000;">Domingo</div><div class="cal_cap">Segunda</div><div class="cal_cap">Terça</div><div class="cal_cap">Quarta</div><div class="cal_cap">Quinta</div><div class="cal_cap">Sexta</div><div class="cal_cap">Sábado</div>';
			$data = array(); //holds the info from the database for faster processing

			if ($pOrder == 0)
			{
				// ---------- TODAS ----------

				$select = "lic.id, lic.valor, DATE(lic.datahora_abertura) AS data";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 1)
			{
				// ---------- COM APL ENVIADA ----------

				$select = "lic.id, lic.valor, DATE(lic.datahora_abertura) AS data";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 2)
			{
				// ---------- COM APL APROVADA ----------

				$select = "lic.id, lic.valor, DATE(lic.datahora_abertura) AS data";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente AND ear.aprovadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.aprovadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 3)
			{
				// ---------- AGUARDANDO APROVACAO DE APL ----------

				$select = "lic.id, lic.valor, DATE(lic.datahora_abertura) AS data";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente AND ear.enviadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.enviadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 4)
			{
				// ---------- SEM APL ENVIADA ----------

				$select = "lic.id, lic.valor, DATE(lic.datahora_abertura) AS data";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						apl.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						apl.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}

			while ($db->nextRecord())
				$data[$db->f("data")][] = array($db->f("id"), number_format($db->f("valor"),2,",","."));

			//desenhar calendario
			$this_date = strtotime(substr($frDate,5,2)."/".substr($frDate,8,2)."/".substr($frDate,0,4));
			for ($i=1; $i<8; $i++)
			{
				$this_date_db = date("Y-m-d",$this_date);
				$box_data = "";
				if (array_key_exists($this_date_db,$data))
				{
					$rows = "";
					for ($k=0; $k<count($data[$this_date_db]); $k++)
						$rows .= '<a class="cal-btn" href="index.php?p=cli_open&id='.$data[$this_date_db][$k][0].'">'.$data[$this_date_db][$k][0].' (R$ '.$data[$this_date_db][$k][1].')</a>';

					if (count($data[$this_date_db]) < 6)
						$box_data .= '<div class="cal_data_mo_bot" style="position: absolute; left: 0; bottom: 0;">'.$rows.'</div>';
					else
						$box_data .= '<div class="cal_data_mo_bot"><div style="height: 20px;"></div>'.$rows.'</div>';
				}

				if ($i == 1)
					$tOutput .= '<div class="cal_data_mo d_row_1" style="border-left: 1px solid #000000;"><span class="t12 gray_28 dom">'.date("j",$this_date).'</span>'.$box_data.'</div>';
				else
					$tOutput .= '<div class="cal_data_mo d_row_1"><span class="t12 gray_28 dom">'.date("j",$this_date).'</span>'.$box_data.'</div>';
				$this_date += 86400;
			}
		}
		else if ($pType == 3)
		{
			//***********************************
			//***             DIA             ***
			//***********************************
			$thisDate = $pYear . "-" . str_pad($pMonth,2,"0",STR_PAD_LEFT) . "-" . str_pad($pDay,2,"0",STR_PAD_LEFT);
			$frDate = $thisDate." 00:00:00";
			$toDate = $thisDate." 23:59:59";
			$data = array(); //holds the info from the database for faster processing

			if ($pOrder == 0)
			{
				// ---------- TODAS ----------

				$select = "lic.id, lic.valor";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 1)
			{
				// ---------- COM APL ENVIADA ----------

				$select = "lic.id, lic.valor";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 2)
			{
				// ---------- COM APL APROVADA ----------

				$select = "lic.id, lic.valor";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente AND ear.aprovadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.aprovadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 3)
			{
				// ---------- AGUARDANDO APROVACAO DE APL ----------

				$select = "lic.id, lic.valor";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.id_cliente = licc.id_cliente AND ear.enviadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_apl_ear AS ear ON ear.id_licitacao = lic.id AND ear.enviadas > 0
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}
			else if ($pOrder == 4)
			{
				// ---------- SEM APL ENVIADA ----------

				$select = "lic.id, lic.valor";

				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
						LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						apl.id IS NULL AND
						IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= CURRENT_DATE())";
				}
				else //BO
				{
					$from = "gelic_licitacoes AS lic
						INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
						LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id
						LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
						LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

					$where = "lic.deletado = 0 AND
						lic.datahora_abertura >= '$frDate' AND
						lic.datahora_abertura <= '$toDate' AND
						his.id IS NULL AND
						apl.id IS NULL";
				}

				$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
			}

			while ($db->nextRecord())
				$data[1][] = array($db->f("id"), number_format($db->f("valor"),2,",","."));

			$box_data = "";
			if (array_key_exists(1,$data))
			{
				$rows = "";
				for ($k=0; $k<count($data[1]); $k++)
					$rows .= '<a class="cal-btn" href="index.php?p=cli_open&id='.$data[1][$k][0].'">'.$data[1][$k][0].' (R$ '.$data[1][$k][1].')</a>';

				if (count($data[1]) < 6)
					$box_data .= '<div class="cal_data_mo_bot" style="position: absolute; left: 0; bottom: 0;">'.$rows.'</div>';
				else
					$box_data .= '<div class="cal_data_mo_bot">'.$rows.'</div>';
			}

			$day_of_week = date('w', strtotime($pMonth.'/'.$pDay.'/'.$pYear)); //day of the week selected 0..6 sun..sat

			$tOutput = '<div class="t14 bold cal_title">Dia '.str_pad($pDay,2,"0",STR_PAD_LEFT).'/'.str_pad($pMonth,2,"0",STR_PAD_LEFT).'/'.$pYear.'<br><a class="t12 normal">'.$aOrder[$pOrder].'</a> <a id="printer" href="javascript:doPrint();" title="Imprimir" style="position: absolute; right: 0; bottom: 0;"><img src="img/ico_print.png" style="border: 0;"></a></div><div class="cal_cap" style="border-left: 1px solid #000000; width: 938px;">'.$aWeek[$day_of_week].'</div>';
			$tOutput .= '<div class="cal_data_mo" style="border-left: 1px solid #000000; width: 938px;">'.$box_data.'</div>';
		}
	}
	else
	{
		echo "Data inválida!";
	}
	echo $tOutput;
}
?>
