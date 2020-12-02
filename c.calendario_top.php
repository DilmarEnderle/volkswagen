<?php

require_once "include/config.php";
require_once "include/essential.php";

$oOutput = '';
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$a = array();
	$a["year"] = intval(date("Y"));
	$a["month"] = intval(date("n"));
	$a["day"] = intval(date("j"));
	$a["order"] = 0;
	$a["view"] = 1;

	$db = new Mysql();
	$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'calendar'");
	if ($db->nextRecord())
		$a = json_decode($db->f("valor"), true);



	//anos disponiveis: De 2014 até ano atual + 2 anos	
	$year_to = intval(date("Y")) + 2;
	$oYears = '';
	for ($i = 2014; $i <= $year_to; $i++)
	{
		if ($i == $a["year"])
			$oYears .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		else
			$oYears .= '<option value="'.$i.'">'.$i.'</option>';
	}

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
	$oMonths = '';
	for ($i = 1; $i <= 12; $i++)
	{
		if ($i == $a["month"])
			$oMonths .= '<option value="'.$i.'" selected="selected">'.$aMonth[$i].'</option>';
		else
			$oMonths .= '<option value="'.$i.'">'.$aMonth[$i].'</option>';
	}


	$aOrdem = array();
	$aOrdem[0] = "Todas";
	$aOrdem[1] = "Com APL Enviada";
	$aOrdem[2] = "Com APL Aprovada";
	$aOrdem[3] = "Aguardando Aprovação de APL";
	$aOrdem[4] = "Sem APL Enviada";
	$oOrdem = '';
	for ($i=0; $i<=4; $i++)
	{
		if ($i == $a["order"])
			$oOrdem .= '<option value="'.$i.'" selected="selected">'.$aOrdem[$i].'</option>';
		else
			$oOrdem .= '<option value="'.$i.'">'.$aOrdem[$i].'</option>';
	}

	$oOutput = '<div style="position: relative; height: 240px; border: 1px solid #999999; border-top: none; background-color: #f5f5f5;">
			<input id="v_day" type="hidden" value="'.$a["day"].'">
			<input id="v_mxy" type="hidden" value="'.$year_to.'">
			<input id="v_view" type="hidden" value="'.$a["view"].'">
			<select id="i_month" style="position: absolute; left: 40px; top: 12px; padding: 0 0 0 8px; width: 119px; height: 28px; line-height: 26px;" onchange="atualizarCalendario();">
				'.$oMonths.'
			</select>
			<select id="i_year" style="position: absolute; left: 160px; top: 12px; padding: 0 0 0 8px; width: 70px; height: 28px; line-height: 26px;" onchange="atualizarCalendario();">
				'.$oYears.'
			</select>
			<span style="position: absolute; left: 41px; top: 48px; text-align: center; width: 26px;">D</span>
			<span style="position: absolute; left: 68px; top: 48px; text-align: center; width: 26px;">S</span>
			<span style="position: absolute; left: 95px; top: 48px; text-align: center; width: 26px;">T</span>
			<span style="position: absolute; left: 122px; top: 48px; text-align: center; width: 26px;">Q</span>
			<span style="position: absolute; left: 149px; top: 48px; text-align: center; width: 26px;">Q</span>
			<span style="position: absolute; left: 176px; top: 48px; text-align: center; width: 26px;">S</span>
			<span style="position: absolute; left: 203px; top: 48px; text-align: center; width: 26px;">S</span>
			<div id="calendar_holder"></div>
			<div style="position: absolute; left: 270px; top: 12px; width: 1px; height: 216px; background-color: #cccccc;"><!-- --></div>
			<span style="position: absolute; right: 540px; top: 64px; line-height: 28px;">Ordenação</span>
			<select id="i_ordenar" style="position: absolute; left: 440px; top: 64px; padding: 0 0 0 4px; width: 400px; height: 28px; line-height: 26px;" onchange="atualizarCalendarioDados();">
				'.$oOrdem.'
    	    </select>
			<span style="position: absolute; right: 540px; top: 114px; line-height: 62px;">Visualização</span>
			<a class="vis'.(int)($a["view"] == 1).' vi" href="javascript:void(0);" onclick="setView(1,this);" style="left: 440px; top: 114px;">Mês</a>
			<a class="vis'.(int)($a["view"] == 2).' vi" href="javascript:void(0);" onclick="setView(2,this);" style="left: 550px; top: 114px;">Semana</a>
			<a class="vis'.(int)($a["view"] == 3).' vi" href="javascript:void(0);" onclick="setView(3,this);" style="left: 660px; top: 114px;">Dia</a>
    	</div>
	<div id="cal_data_holder" style="overflow: hidden;text-align:center;"></div>
	<div style="height: 100px;"><!-- gap --></div>';
}
echo $oOutput;

?>
