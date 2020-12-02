<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_item = intval($_POST["id-item"]);

	$db = new Mysql();

	//buscar informacao caption
	$db->query("
SELECT 
	itm.id_licitacao,
	itm.id_lote, 
    lot.lote,
	itm.item, 
    itm.valor 
FROM 
	gelic_licitacoes_itens AS itm
    INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
WHERE 
	itm.id = $pId_item");
	$db->nextRecord();
	$dCaption_id_licitacao = $db->f("id_licitacao");
	$dCaption_id_lote = $db->f("id_lote");
	$dCaption_lote = utf8_encode($db->f("lote"));
	$dCaption_item = utf8_encode($db->f("item"));
	$dCaption_valor = 'R$ '.number_format($db->f("valor"),2,",",".");

	$oCaption = '
		<a class="bold fl">Participantes</a>
		<a class="fr mr-80" style="font-weight:normal;">'.$dCaption_valor.'</a>
		<a class="bold fr mr-6">Valor Edital:</a>
		<a class="fr mr-20" style="font-weight:normal;">'.$dCaption_item.'</a>
		<a class="bold fr mr-6">Item:</a>
		<a class="fr mr-20" style="font-weight:normal;">'.$dCaption_lote.'</a>
		<a class="bold fr mr-6">Lote:</a>
		<a class="fr mr-20" style="font-weight:normal;">'.$dCaption_id_licitacao.'</a>
		<a class="bold fr mr-6">Licitação:</a>';

	$oOutput = '<div class="ultimate-row mt-10">
		<span class="pt-razao fl">Razão Social</span>
		<span class="pt-cnpj fl">CNPJ</span>
		<span class="pt-valor fl">Valor Final</span>
		<span class="pt-inabilitado fr">Inabilitado</span>
		<span class="pt-vencedor fr">Vencedor</span>
	</div>';

	$db->query("SELECT id, razao_social, cnpj, valor_final, vencedor, inabilitado FROM gelic_licitacoes_itens_participantes WHERE id_item = $pId_item AND deletado = 0 ORDER BY razao_social");
	$dTotal = $db->nf();
	while ($db->nextRecord())
	{
		$v = '';
		if ($db->f("vencedor") > 0)
			$v = '<img class="abs tp-9" src="img/check1010.png" style="right:150px;">';

		$i = '';
		if ($db->f("inabilitado") > 0)
			$i = '<img class="abs tp-9" src="img/check1010.png" style="right:60px;">';

		$oOutput .= '<div class="ultimate-row hgl" style="height: 30px; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(222, 222, 222); background-color: rgb(255, 255, 255);">
			'.$v.$i.'
			<a class="alnk t14 abs lh-30 pl-10" href="javascript:void(0);" onclick="editarParticipante('.$db->f("id").','.$pId_item.');" style="display: inline-block; width: 100%; box-sizing: border-box;">'.utf8_encode($db->f("razao_social")).'</a>
			<a class="alnk t14 abs lh-30 lf-440" href="javascript:void(0);" onclick="editarParticipante('.$db->f("id").','.$pId_item.');">'.$db->f("cnpj").'</a>
			<a class="alnk t14 abs lh-30" href="javascript:void(0);" onclick="editarParticipante('.$db->f("id").','.$pId_item.');" style="right:201px;">R$ '.number_format($db->f("valor_final"),2,",",".").'</a>
			<a href="javascript:void(0);" onclick="removerParticipante('.$db->f("id").','.$pId_item.');" title="Remover Participante"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
		</div>';
	}

	$oOutput .= '<div class="ultimate-row mt-30 mb-20"><a class="bt-style-1 fl" href="javascript:void(0);" onclick="editarParticipante(0, '.$pId_item.');">+ Adicionar Participante</a></div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oCaption;
	$aReturn[2] = $oOutput;
	$aReturn[3] = $dTotal;
}
echo json_encode($aReturn);

?>
