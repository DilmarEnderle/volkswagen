<?php

require_once "include/config.php";
require_once "include/essential.php";

$r_array = array(0);
if (isInside())
{
	$gYear = intval($_GET["y"]);
	$gMonth = intval($_GET["m"]);
	$gDay = intval($_GET["d"]);
	$gYear_min = 2014;
	$gYear_max = intval($_GET["mxy"]);
	
	//ajust day
	$days_in_month = cal_days_in_month(CAL_GREGORIAN, $gMonth, $gYear);
	if ($gDay > $days_in_month) $gDay = $days_in_month;

	//verificar se a data é valida
	if ($gYear >= $gYear_min && $gYear <= $gYear_max && checkdate($gMonth,$gDay,$gYear))
	{
		$r_array[0] = 1; //ok
		$tCalendar = ""; //return this
		$today_day = intval(date("j"));
		$today_month = intval(date("n"));
		$today_year = intval(date("Y"));

		$days_in_month = cal_days_in_month(CAL_GREGORIAN, $gMonth, $gYear);
		if ($gMonth == 1) 
			$days_pr_month = 31;
		else
			$days_pr_month = cal_days_in_month(CAL_GREGORIAN, $gMonth-1, $gYear);

		$day_of_week = date('w', strtotime($gMonth.'/1/'.$gYear)); //day of the week of 1st of the month selected 0..6 sun..sat

		//desenhar calendario
		if ($day_of_week > 0) for ($i=$days_pr_month-$day_of_week+1; $i<=$days_pr_month; $i++) $tCalendar .= '<a id="no_'.$i.'" class="dis">'.$i.'</a>';
		for ($i=1; $i<=$days_in_month; $i++)
		{
			$tClass = "wd";
			$dow = date("w", mktime(0,0,0,$gMonth,$i,$gYear));
			if ($dow == 0 || $dow == 6) $tClass = "wk";
			if ($i == $today_day && $gMonth == $today_month && $gYear == $today_year) $tClass = "wt";
			if ($i == $gDay) 
			{
				$r_array[1] = $tClass; //salvar classe para poder recuperar depois
				$tClass = "dsl";
			}
			$tCalendar .= '<a id="day_'.$i.'" class="'.$tClass.'" href="javascript:void(0);">'.$i.'</a>';
		}
		for ($i=1; $i<=42-$days_in_month-$day_of_week; $i++) $tCalendar .= '<a id="no_'.$i.'" class="dis">'.$i.'</a>';

		$r_array[2] = $gDay; //return day if there was an ajustment
		$r_array[3] = $tCalendar;
	}
}
echo json_encode($r_array);
?>
