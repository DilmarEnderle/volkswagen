<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$cliente_parent = 0;
	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];


	$db = new Mysql();

	$aAdesao = array();
	$aAdesao[0] = '';
	$aAdesao[1] = '<img src="img/check1010.png">';

	$oRows = '';

	$db->query("
SELECT 
	atarp.*,
	(SELECT id FROM gelic_atarp_anexos WHERE id_atarp = atarp.id LIMIT 1) AS anexo 
FROM 
	gelic_atarp AS atarp
ORDER BY 
	atarp.id DESC");
	while ($db->nextRecord())
	{
		$anx = '';
		if (strlen($db->f("anexo")) > 0)
			$anx = '<a href="javascript:void(0);" onclick="verAnexos('.$db->f("id").');" title="Anexo(s)"><img src="img/btn-anexo.png" style="border:0;"></a>';

		$obs = '';
		if ($db->f("observacoes") <> '')
			$obs = '<a href="javascript:void(0);" onclick="verObservacoes('.$db->f("id").');" title="Observações"><img src="img/btn-notes.png" style="border:0;margin-top:4px;"></a>';

		$da = '<a class="da" href="index.php?p=cli_compradir_editar&a='.$db->f("id").'">Desejo Aderir</a>';
		if ($sInside_tipo == 1)
			$da = '<a class="da-g" href="javascript:void(0);">Desejo Aderir</a>';
			

		$oRows .= '<tr>
			<td class="td_cell">'.utf8_encode($db->f("modelo")).'</td>
			<td class="td_cell">'.utf8_encode($db->f("orgao")).'</td>
			<td class="td_cell">'.utf8_encode($db->f("licitacao")).'</td>
			<td class="td_cell">'.utf8_encode($db->f("vigencia")).'</td>
			<td class="td_cell" style="width: 31px; padding: 0; text-align: center;">'.utf8_encode($aAdesao[$db->f("status")]).'</td>
			<td class="td_cell" style="width: 33px; padding: 0; text-align: center;">'.$anx.'</td>
			<td class="td_cell" style="width: 33px; padding: 0; text-align: center;">'.$obs.'</td>
			<td class="td_cell" style="width: 60px; padding: 0; text-align: center;">'.$da.'</td>
		</tr>';
	}


	if (strlen($oRows) == 0)
		$oRows = '<tr><td colspan="8" style="border: 1px solid #bebebe; text-align: center; padding: 40px 0;">---</td></tr>';

	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(5,6); ?>

			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo" style="display:block;width:100%;">Atas de Registro de Preços</h4>
				<table class="atarp-tbl" cellpading="0" cellspacing="0" style="border-collapse: collapse; margin: 28px 0 25px 0; float: left;">
					<thead>
						<tr>
							<th style="text-align: left; width: 140px; line-height: 36px; padding: 0 10px; box-sizing: border-box;">Modelo</th>
							<th style="text-align: left; width: 322px; line-height: 36px; padding: 0 10px; box-sizing: border-box;">Órgão</th>
							<th style="text-align: left; width: 160px; line-height: 36px; padding: 0 10px; box-sizing: border-box;">Licitação</th>
							<th style="text-align: left; width: 160px; line-height: 36px; padding: 0 10px; box-sizing: border-box;">Vigência</th>
							<th colspan="4" style="text-align: left; width: 139px; line-height: 36px; padding: 0; box-sizing: border-box;">Aceita Adesão</th>
						</tr>
					</thead>
					<tbody id="row-container">
						<?php echo $oRows; ?>
					</tbody>
				</table>
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
