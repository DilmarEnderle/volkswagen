<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$db = new Mysql();

	$tRow_fer = '<tr>
		<td class="fer_nome"><span class="t14 bold gray-48">{{D}}</span></td>
		<td class="fer_dia">{{DIA}}</td>
		<td class="fer_fixo">{{F}}</td>
	</tr>';


	$aFixo = array();
	$aFixo[0] = "Não";
	$aFixo[1] = "Sim";

	$aMes = array();
	$aMes[1] = "Janeiro";
	$aMes[2] = "Fevereiro";
	$aMes[3] = "Março";
	$aMes[4] = "Abril";
	$aMes[5] = "Maio";
	$aMes[6] = "Junho";
	$aMes[7] = "Julho";
	$aMes[8] = "Agosto";
	$aMes[9] = "Setembro";
	$aMes[10] = "Outubro";
	$aMes[11] = "Novembro";
	$aMes[12] = "Dezembro";


	$oRows = '';
	//carregar feriados
	$db->query("SELECT nome, dia, mes, fixo FROM gelic_feriados ORDER BY mes,dia");
	while ($db->nextRecord())
	{
		$tTmp = $tRow_fer;
		$tTmp = str_replace("{{D}}", utf8_encode($db->f("nome")), $tTmp);
		$tTmp = str_replace("{{DIA}}", $db->f("dia")." - ".$aMes[$db->f("mes")], $tTmp);
		$tTmp = str_replace("{{F}}", $aFixo[$db->f("fixo")], $tTmp);
		$oRows .= $tTmp;
	}

	if (strlen($oRows) == 0)
		$oRows = '<tr><td colspan="3" class="no_recs">Nenhum Feriado!</td></tr>';

	?>

	<section>
		<div class="middle">
			<?php echo getTop(); ?>
			<?php echo getMenu(4,1); ?>

			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo">Feriados</h4>
				<table cellpading="0" cellspacing="0" class="lic_list">
					<thead>
						<tr>
							<th style="text-align: left; width: 500px; border-bottom: 1px solid #bebebe;">Descrição</th>
							<th style="text-align: left; width: 200px; border-bottom: 1px solid #bebebe;">Dia/Mês</th>
							<th style="text-align: left; border-bottom: 1px solid #bebebe;">Fixo</th>
						</tr>
					</thead>
					<tbody id="row-container">
						<?php echo $oRows; ?>
						<tr>
							<td colspan="3" style="height: 100px;"></td>
						</tr>
					</tbody>
				</table>
			</div>
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
