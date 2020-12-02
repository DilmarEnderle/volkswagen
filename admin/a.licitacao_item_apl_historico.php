<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_licitacao = intval($_POST["id-licitacao"]);
	$pId_item = intval($_POST["id-item"]);
	$pId_cliente = intval($_POST["id-cliente"]);

	$db = new Mysql();

	$rows = '';
	$enviada = false;

	$db->query("
SELECT
	his.id,
	his.data_hora,
	his.tipo,
	apl.arquivo,
	ahis.id AS id_ahis,
    ahis.ip,
	ahis.texto,
    cli.id_parent,
	cli.nome,
	clidn.nome AS nome_dn,
	(SELECT descricao FROM gelic_motivos WHERE id = ahis.id_valor_1) AS motivo,
	(SELECT descricao FROM gelic_motivos WHERE id = ahis.id_valor_2) AS submotivo
FROM
	gelic_historico AS his
	INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = his.id_apl
	INNER JOIN gelic_licitacoes_apl_historico AS ahis ON ahis.id = his.id_valor_1
	INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	his.tipo IN (41,42,43,44,45) AND
	apl.id_licitacao = $pId_licitacao AND
	apl.id_item = {$pId_item} AND
	(apl.id_cliente = $pId_cliente OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $pId_cliente))
ORDER BY 
	his.id");
	while ($db->nextRecord())
	{
		if ($db->Row[0] % 2)
			$bg = '';
		else
			$bg = ' style="background-color: #eeeeee;"';


		if ($db->f("tipo") == 41) //41: APL Enviada
		{
			$por = utf8_encode($db->f("nome"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			if ($enviada)
				$t = 'APL re-enviada';
			else
				$t = 'APL enviada';

			$rows .= '
		<div class="ultimate-row lh-27 t13"'.$bg.'>
			<div class="fl w-160">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).' <span class="gray-88">'.substr($db->f("data_hora"), 11).'</span></div>
			<div class="fl"><span class="bold orange">'.$t.'</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip").'</span><a class="his-btn ml-10" href="../arquivos/anexo/'.$db->f("arquivo").'" target="_blank">APL</a></div>
		</div>';

			$enviada = true;
		}
		else if ($db->f("tipo") == 42) //42: APL Aprovada
		{
			$por = utf8_encode($db->f("nome"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			$rows .= '<div class="ultimate-row lh-27 t13"'.$bg.'>
				<div class="fl w-160">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).' <span class="gray-88">'.substr($db->f("data_hora"), 11).'</span></div>
				<div class="fl"><span class="bold green">APL aprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip").'</span> [ <a class="his-btn-info" href="javascript:void(0);" onclick="verMais('.$db->f("id").');">Ver mais...</a> ]</div>
			</div>';


			// Verificar se existe informacao da aprovacao da APL
			$apr_tbl = '';
			$obs = '';
			$aPlanta = array("","TBT/SP (0024-46)","SBC/SP","SJP/PR (0103-84)");
			$db->query("
SELECT 
    apr.ave,
    apr.quantidade,
	apr.model_code,
	apr.cor,
	apr.opcionais_pr,
	apr.preco_publico,
	apr.prazo_de_entrega,
	apr.planta,
	apr.desconto_vw,
	apr.comissao_dn,
	apr.valor_da_transformacao,
	apr.nome_arquivo,
	apr.arquivo,
	ahis.texto
FROM 
	gelic_licitacoes_apl_historico AS ahis
	LEFT JOIN gelic_licitacoes_apl_aprovadas AS apr ON apr.id_apl_historico = ahis.id
WHERE 
	ahis.id = ".$db->f("id_ahis"),1);
			if ($db->nextRecord(1))
			{
				if (strlen($db->f("ave",1)) > 0)
				{
					if ($db->f("valor_da_transformacao",1) > 0)
						$valor_transf = '<tr><td class="apr-tbl-lb">VALOR DA TRANSFORMAÇÃO:</td><td class="apr-tbl-vl">R$ '.number_format($db->f("valor_da_transformacao",1),2,',','.').'</td></tr>';
					else
						$valor_transf = '';

					if (strlen($db->f("arquivo",1)) > 0)
						$anexo = '<tr><td class="apr-tbl-lb">ANEXO:</td><td><a class="ablue" href="../arquivos/apr/'.$db->f("arquivo",1).'" target="_blank">'.utf8_encode($db->f("nome_arquivo",1)).'</a></td></tr>';
					else
						$anexo = '';

					$apr_tbl = '<table class="apr-tbl">
<tr>
	<td class="apr-tbl-lb">AVE:</td>
	<td class="apr-tbl-vl">'.utf8_encode($db->f("ave",1)).'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">QUANTIDADE:</td>
	<td class="apr-tbl-vl">'.$db->f("quantidade",1).'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">MODEL CODE:</td>
	<td class="apr-tbl-vl">'.utf8_encode($db->f("model_code",1)).'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">COR:</td>
	<td class="apr-tbl-vl">'.utf8_encode($db->f("cor",1)).'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">OPCIONAIS (PR\'s):</td>
	<td class="apr-tbl-vl">'.utf8_encode($db->f("opcionais_pr",1)).'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">PREÇO PÚBLICO:</td>
	<td class="apr-tbl-vl">R$ '.number_format($db->f("preco_publico",1),2,',','.').'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">PRAZO DE ENTREGA:</td>
	<td class="apr-tbl-vl">'.$db->f("prazo_de_entrega",1).'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">PLANTA:</td>
	<td class="apr-tbl-vl">'.$aPlanta[$db->f("planta",1)].'</td>
</tr>
<tr>
	<td class="apr-tbl-lb">DESCONTO VW:</td>
	<td class="apr-tbl-vl">'.number_format($db->f("desconto_vw",1),2,',','.').' %</td>
</tr>
<tr>
	<td class="apr-tbl-lb">COMISSÃO DN:</td>
	<td class="apr-tbl-vl">'.number_format($db->f("comissao_dn",1),2,',','.').' %</td>
</tr>
'.$valor_transf.$anexo.'
</table>';
				}

				if (strlen($db->f("texto",1)) > 0)
					$obs = '<div class="bold mt-12">Observações:</div>
						<div>'.nl2br(utf8_encode($db->f("texto",1))).'</div>';
			}

			if (strlen($apr_tbl) > 0 || strlen($obs) > 0)
			{
				$rows .= '<div id="his-'.$db->f("id").'" class="ultimate-row lh-27 t13 pb-14 his-hidden"'.$bg.'>
					<div class="fl w-160">&nbsp;</div>
					<div class="fl w-600" style="line-height: normal;">';

				if (strlen($apr_tbl) > 0)
					$rows .= $apr_tbl;

				if (strlen($obs) > 0)
					$rows .= $obs;

				$rows .= '</div></div>';
			}
		}
		else if ($db->f("tipo") == 43) //43: APL Reprovada
		{
			$por = utf8_encode($db->f("nome"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			$rows .= '<div class="ultimate-row lh-27 t13"'.$bg.'>
				<div class="fl w-160">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).' <span class="gray-88">'.substr($db->f("data_hora"), 11).'</span></div>
				<div class="fl"><span class="bold red">APL reprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip").'</span> [ <a class="his-btn-info" href="javascript:void(0);" onclick="verMais('.$db->f("id").');">Ver mais...</a> ]</div>
			</div>';

			$ms = utf8_encode($db->f("motivo"));
			if (strlen($db->f("submotivo")) > 0)
				$ms .= ' <span class="gray-88 italic">('.utf8_encode($db->f("submotivo")).')</span>';
			
			$obs = '';
			if (strlen($db->f("texto")) > 0)
				$obs = '<div class="bold mt-12">Observações:</div>
					<div>'.nl2br(utf8_encode($db->f("texto"))).'</div>';

			$rows .= '<div id="his-'.$db->f("id").'" class="ultimate-row lh-27 t13 pb-14 his-hidden"'.$bg.'>
				<div class="fl w-160">&nbsp;</div>
				<div class="fl w-600" style="line-height: normal;">
					<div class="bold">Motivo:</div>
					<div>'.$ms.'</div>
					'.$obs.'
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 44) //44: APL Aprovação Revertida
		{
			$por = utf8_encode($db->f("nome"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';


			$mais = '';
			if (strlen($db->f("texto")) > 0)
				$mais = ' [ <a class="his-btn-info" href="javascript:void(0);" onclick="verMais('.$db->f("id").');">Ver mais...</a> ]';

			$rows .= '<div class="ultimate-row lh-27 t13"'.$bg.'>
				<div class="fl w-160">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).' <span class="gray-88">'.substr($db->f("data_hora"), 11).'</span></div>
				<div class="fl"><span class="bold blue">Aprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip").'</span>'.$mais.'</div>
			</div>';

			if (strlen($db->f("texto")) > 0)
			{
				$rows .= '<div id="his-'.$db->f("id").'" class="ultimate-row lh-27 t13 pb-14 his-hidden"'.$bg.'>
					<div class="fl w-160">&nbsp;</div>
					<div class="fl w-600" style="line-height: normal;">
						<div class="bold">Observações:</div>
						<div>'.nl2br(utf8_encode($db->f("texto"))).'</div>
					</div>
				</div>';
			}
		}
		else if ($db->f("tipo") == 45) //45: APL Reprovação Revertida
		{
			$por = utf8_encode($db->f("nome"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			$mais = '';
			if (strlen($db->f("texto")) > 0)
				$mais = ' [ <a class="his-btn-info" href="javascript:void(0);" onclick="verMais('.$db->f("id").');">Ver mais...</a> ]';

			$rows .= '<div class="ultimate-row lh-27 t13"'.$bg.'>
				<div class="fl w-160">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).' <span class="gray-88">'.substr($db->f("data_hora"), 11).'</span></div>
				<div class="fl"><span class="bold blue">Reprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip").'</span>'.$mais.'</div>
			</div>';

			if (strlen($db->f("texto")) > 0)
			{
				$rows .= '<div id="his-'.$db->f("id").'" class="ultimate-row lh-27 t13 pb-14 his-hidden"'.$bg.'>
					<div class="fl w-160">&nbsp;</div>
					<div class="fl w-600" style="line-height: normal;">
						<div class="bold">Observações:</div>
						<div>'.nl2br(utf8_encode($db->f("texto"))).'</div>
					</div>
				</div>';
			}
		}
	}


	$oOutput = '
		<div class="ultimate-row" style="border-bottom: 1px solid #cecece;">
			<div class="fl w-160 bold">Data/Hora</div>
			<div class="fl bold">Evento</div>
		</div>
		'.$rows;

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
