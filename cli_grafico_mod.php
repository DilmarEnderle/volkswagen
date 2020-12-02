<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1) //BO
	{
		$aAcesso = array();

		$db = new Mysql();
		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = 0 AND config = 'relatorios'");
		if ($db->nextRecord())
			$aAcesso = explode(" ", $db->f("valor"));


		$aRel_option_1 = array();
		$aRel_option_1["rel_1_1"] = '<option value="r1_1">Total de DNs</option>';
		$aRel_option_1["rel_1_2"] = '<option value="r1_2">Quantos já acessaram</option>';
		$aRel_option_1["rel_1_3"] = '<option value="r1_3">Novos acessos</option>';
		$aRel_option_1["rel_1_4"] = '<option value="r1_4">Por mês e acumulado do ano</option>';
		$rel_1 = '';
		$rel_1_options = '';
		for ($i=0; $i<count($aAcesso); $i++)
			if (isset($aRel_option_1[$aAcesso[$i]]))
				$rel_1_options .= $aRel_option_1[$aAcesso[$i]];

		if ($rel_1_options != '')
			$rel_1 .= '<optgroup label="ADESÃO">'.$rel_1_options.'</optgroup>';



		$aRel_option_2 = array();
		$aRel_option_2["rel_2_1"] = '<option value="r2_1">Quantidade de acessos por mês</option>';
		$aRel_option_2["rel_2_2"] = '<option value="r2_2">Quantidade de acessos por ano</option>';
		$aRel_option_2["rel_2_3"] = '<option value="r2_3">Volume de acessos por DN</option>';
		$aRel_option_2["rel_2_4"] = '<option value="r2_4">Volume de acessos por Região</option>';
		$rel_2 = '';
		$rel_2_options = '';
		for ($i=0; $i<count($aAcesso); $i++)
			if (isset($aRel_option_2[$aAcesso[$i]]))
				$rel_2_options .= $aRel_option_2[$aAcesso[$i]];

		if ($rel_2_options != '')
			$rel_2 .= '<optgroup label="VOLUME DE ACESSOS">'.$rel_2_options.'</optgroup>';




		$aRel_option_3 = array();
		$aRel_option_3["rel_3_1"] = '<option value="r3_1">APLs enviadas por mês</option>';
		$rel_3 = '';
		$rel_3_options = '';
		for ($i=0; $i<count($aAcesso); $i++)
			if (isset($aRel_option_3[$aAcesso[$i]]))
				$rel_3_options .= $aRel_option_3[$aAcesso[$i]];

		if ($rel_3_options != '')
			$rel_3 .= '<optgroup label="APLs">'.$rel_3_options.'</optgroup>';




		$aRel_option_4 = array();
		$aRel_option_4["rel_4_1"] = '<option value="r4_1">Quantitativo total de processos Estaduais, Federais e Municipais</option>';
		$aRel_option_4["rel_4_2"] = '<option value="r4_2">Quantitativo de licitações recusadas pela GELIC e pela Fábrica</option>';
		$aRel_option_4["rel_4_3"] = '<option value="r4_3">Quantitativo de licitações em andamento, derrota, vitória e sem envio de APL</option>';
		$aRel_option_4["rel_4_4"] = '<option value="r4_4">Licitações e Veículos - Totais</option>';
		$rel_4 = '';
		$rel_4_options = '';
		for ($i=0; $i<count($aAcesso); $i++)
			if (isset($aRel_option_4[$aAcesso[$i]]))
				$rel_4_options .= $aRel_option_4[$aAcesso[$i]];

		if ($rel_4_options != '')
			$rel_4 .= '<optgroup label="LICITAÇÕES">'.$rel_4_options.'</optgroup>';



		$aRel_option_5 = array();
		$aRel_option_5["rel_5_1"] = '<option value="r5_1">Relatório por status - Licitações e Veículos</option>';
		$rel_5 = '';
		$rel_5_options = '';
		for ($i=0; $i<count($aAcesso); $i++)
			if (isset($aRel_option_5[$aAcesso[$i]]))
				$rel_5_options .= $aRel_option_5[$aAcesso[$i]];

		if ($rel_5_options != '')
			$rel_5 .= '<optgroup label="STATUS">'.$rel_5_options.'</optgroup>';



		$aRel_option_6 = array();
		$aRel_option_6["rel_6_1"] = '<option value="r6_1">Lista de licitações por DN</option>';
		$aRel_option_6["rel_6_2"] = '<option value="r6_2">Lista de licitações por status</option>';
		$aRel_option_6["rel_6_3"] = '<option value="r6_3">Lista de licitações por prazo</option>';
		$rel_6 = '';
		$rel_6_options = '';
		for ($i=0; $i<count($aAcesso); $i++)
			if (isset($aRel_option_6[$aAcesso[$i]]))
				$rel_6_options .= $aRel_option_6[$aAcesso[$i]];

		if ($rel_6_options != '')
			$rel_6 .= '<optgroup label="DNs">'.$rel_6_options.'</optgroup>';


	
		$rel = '<div style="border: 1px solid #999999; overflow: hidden;">
					<span style="float: left; margin: 20px 0 0 60px;">Relatório</span>
					<select id="rel-selected" style="clear: both; float: left; width: 680px; margin-left: 60px; padding: 0 0 0 4px; height: 32px; line-height: 30px;" onchange="relOpcoes();">
						<option value="">- selecione o relatório -</option>
						'.$rel_1.$rel_2.$rel_3.$rel_4.$rel_5.$rel_6.'
						<optgroup label="Outros">
							<option value="motd">Motivos (Sem Interesse)</option>
						</optgroup>						
					</select>

					<span id="descricao" style="display:none;clear:both;float:left;margin-left:60px;margin-top:4px;width:680px;background-color:#eeeeee;padding:4px 10px;box-sizing:border-box;line-height:18px;color:#666666;"></span>

					<span class="sta" style="display:none;clear:both;float:left;margin-top:20px;margin-left:60px;">Selecione o status</span>
					<a id="status" class="filtro1 sta drp" href="javascript:void(0);" onclick="dropBox();" style="display:none;clear:both;float:left;margin-left:60px;">Status (<span>0</span>)<img src="img/a-down-g.png"></a>

					<span class="dt" style="display:none;clear: both; float: left; line-height: 28px; margin: 10px 0 0 60px; color: #888888; font-style: italic;">(opcional - deixe em branco para todos)</span>
					<span class="dt" style="display:none;clear: both; float: left; line-height: 38px; margin-left: 60px;">Período de</span>
					<input id="rel-periodo-fr" class="iText dt" style="display:none;float: left; margin-left: 10px; width: 120px;">
					<span class="dt" style="display:none;float: left; margin-left: 10px; line-height: 38px;">até</span>
					<input id="rel-periodo-to" class="iText dt" style="display:none;float: left; margin-left: 10px; width: 120px;">

					<a id="rel-detalhamento" class="cb0 detalhamento" href="javascript:void(0);" onclick="justCheck(this);" style="clear:both;float:left;margin-top:20px;margin-left:60px;display:none;">Incluir detalhamento</a>

					<div style="clear: both; float: left; height: 28px;"></div>
					<a class="bt-style-2 rel-btn-xlsx" href="javascript:void(0);" onclick="relatorio(\'xlsx\');" style="display:none;clear: both; float: left; margin-left: 60px; padding: 0 18px 0 40px; height: 50px; line-height: 50px; background-image:url(\'img/xlsx-icon.png\'); background-repeat: no-repeat; background-position: 5px 5px;">Gerar Relatório</a>
					<a class="bt-style-2 rel-btn-pdf" href="javascript:void(0);" onclick="relatorio(\'pdf\');" style="display:none;float: left; margin-left: 40px; padding: 0 18px 0 40px; height: 50px; line-height: 50px; background-image:url(\'img/pdf-icon.png\'); background-repeat: no-repeat; background-position: 5px 5px;">Gerar Relatório</a>
					<a class="bt-style-2 btg" href="javascript:void(0);" onclick="relatorio(\'\');" style="display:none;clear: both; float: left; margin-left: 60px; height: 50px; line-height: 50px; display: none;">Atualizar Gráfico</a>
					<div style="clear: both; float: left; height: 20px;"></div>
    			</div>';
	}
	else
	{
		$rel = '<div style="border: 1px solid #999999; overflow: hidden;">
					<span style="float: left; margin: 20px 0 0 60px;">Relatório</span>
					<select id="rel-selected" style="clear: both; float: left; width: 480px; margin-left: 60px; padding: 0 0 0 4px; height: 32px; line-height: 30px;" onchange="relOpcoes();">
						<option value="motd">Motivos (Sem Interesse)</option>
					</select>

					<div style="clear: both; float: left; height: 28px;"></div>
					<a class="bt-style-2 btg" href="javascript:void(0);" onclick="relatorio(\'\');" style="clear: both; float: left; margin-left: 60px; height: 50px; line-height: 50px;">Atualizar Gráfico</a>
					<div style="clear: both; float: left; height: 20px;"></div>
    			</div>';
	}
	?>

	<!--Load the AJAX API-->
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">google.load("visualization", "1", {packages:["corechart"]});</script>

	<section>
		<div class="middle">
			<?php echo getTop(); ?>
			<?php echo getMenu(3,0); ?>
			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo">Relatórios</h4>
			</div>
			<div class="lic" style="margin: 0;">
				<?php echo $rel; ?>
				<div id="chart_div" style="height: 600px;"></div>
			</div>
			<div style="height: 100px;"><!-- gap --></div>
		</div>
	</section>

	<?php
}
else
{
	?>
	<section>
		<div class="middle">
			<div class="lic" style="height: 340px;">
				<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
				<p style="color: #a6a6a6;">Se você é cliente GELIC utilize o seu login e senha para ter acesso nesta área.</p>
			</div>
		</div>
	</section>
	<?php
}

?>
