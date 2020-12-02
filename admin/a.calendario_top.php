<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_visualizar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		$aReturn[1] = '<div class="content-inside" style="padding-top: 20px;">
			<div class="full-row" style="padding: 0 0 2px 0; border-bottom: 1px solid #666666;">
				<span class="t18 bold lh-30 fl red">Acesso Restrito!</span>
			</div>
			<div class="t14" style="position: relative; margin: 40px auto; width: 500px; text-align: center; border: 1px solid #999999; padding: 20px 0;">
				<span class="bold">LICITAÇÕES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span><br><br><br>
				<a class="bt-style-1" href="javascript:window.history.back();" style="display: inline-block;">Ok</a>
			</div>
		</div>';
		echo json_encode($aReturn);
		exit;
	}

	//anos disponiveis: De 2014 até ano atual + 2 anos	
	$year = intval(date("Y")); //4 digitos
	$year_to = $year + 2;
	$oYears = '';
	for ($i = 2014; $i <= $year_to; $i++)
	{
		if ($i == $year)
			$oYears .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		else
			$oYears .= '<option value="'.$i.'">'.$i.'</option>';
	}

	$month = date('n');
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
		if ($i == $month)
			$oMonths .= '<option value="'.$i.'" selected="selected">'.$aMonth[$i].'</option>';
		else
			$oMonths .= '<option value="'.$i.'">'.$aMonth[$i].'</option>';
	}

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = '<div class="row_wide_content" style="height: 242px;">
		<div style="position: absolute; left: 22px; top: 0; width: 1056px; height: 238px; background-color: #ededed;">
			<input id="v_day" type="hidden" value="'.intval(date('j')).'">
			<input id="v_mxy" type="hidden" value="'.$year_to.'">
			<select id="i_month" class="iText_in" style="position: absolute; left: 40px; top: 10px; padding: 0 0 0 8px; width: 119px; height: 28px; line-height: 26px;" onchange="atualizarCalendario();">
				'.$oMonths.'
			</select>
			<select id="i_year" class="iText_in" style="position: absolute; left: 160px; top: 10px; padding: 0 0 0 8px; width: 70px; height: 28px; line-height: 26px;" onchange="atualizarCalendario();">
				'.$oYears.'
			</select>
			<span class="t12 bold dark_red abs" style="left: 41px; top: 48px; text-align: center; width: 26px;">D</span>
			<span class="t12 bold gray_4c abs" style="left: 68px; top: 48px; text-align: center; width: 26px;">S</span>
			<span class="t12 bold gray_4c abs" style="left: 95px; top: 48px; text-align: center; width: 26px;">T</span>
			<span class="t12 bold gray_4c abs" style="left: 122px; top: 48px; text-align: center; width: 26px;">Q</span>
			<span class="t12 bold gray_4c black abs" style="left: 149px; top: 48px; text-align: center; width: 26px;">Q</span>
			<span class="t12 bold gray_4c black abs" style="left: 176px; top: 48px; text-align: center; width: 26px;">S</span>
			<span class="t12 bold dark_red abs" style="left: 203px; top: 48px; text-align: center; width: 26px;">S</span>
			<div id="calendar_holder"></div>
			<div style="position: absolute; left: 270px; top: 10px; width: 1px; height: 218px; background-color: #b08706;"><!-- --></div>
			<span class="t14 abs" style="right: 674px; top: 84px; line-height: 28px;">Ordenação</span>
			<select id="i_ordenar" class="iText_in" style="position: absolute; left: 400px; top: 84px; padding: 0 0 0 4px; width: 616px; height: 28px; line-height: 26px;" onchange="atualizarCalendarioDados();">
				<option value="0">Todas</option>
				<option value="1">Com APL Enviada</option>
				<option value="2">Com APL Aprovada</option>
				<option value="3">Aguardando Aprovação de APL</option>
				<option value="4">Sem APL Enviada</option>
            </select>
			<span class="t14 abs" style="right: 674px; top: 134px; line-height: 62px;">Visualização</span>
			<a class="vis1 vi" href="javascript:void(0);" onclick="setView(1,this);" style="left: 400px; top: 134px;">Mês</a>
			<a class="vis0 vi" href="javascript:void(0);" onclick="setView(2,this);" style="left: 510px; top: 134px;">Semana</a>
			<a class="vis0 vi" href="javascript:void(0);" onclick="setView(3,this);" style="left: 620px; top: 134px;">Dia</a>
		</div>
		<div style="position: absolute; left: 22px; bottom: 1px; width: 1056px; height: 1px; background-color: #b08706;"><!-- --></div>
	</div>
	<div class="row_wide_content" style="height: 20px;"><!-- --></div>
	<div class="row_wide_content">
		<div id="cal_data_holder"></div>
	</div>        
	<div class="row_wide_content" style="height: 60px;"><!-- --></div>';
}
echo json_encode($aReturn);

?>
