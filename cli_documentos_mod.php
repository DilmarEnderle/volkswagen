<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	$db = new Mysql();

	$tRow_doc = '<tr>
		<td class="doc_name"><span class="t14 bold gray-48">{{NOME}}</span><br><span class="t12 italic">{{DESC}}</span></td>
		<td class="doc_validade">{{EXP}}</td>
		<td class="doc_status">{{STA}}</td>
		<td class="doc_btn">{{BTN}}</td>
	</tr>';

	$oRows = '';

	if ($sInside_tipo == 1) //BO
	{
		$db->query("
SELECT 
	doc.id,
	doc.nome, 
	doc.descricao, 
	doc.validade,
	doc.validade < CURRENT_DATE() AS expired,
	his.id AS hid,
	his.arquivo
FROM 
	gelic_documentos AS doc
	LEFT JOIN gelic_documentos_historico AS his ON his.id_documento = doc.id AND his.id = (SELECT MAX(id) FROM gelic_documentos_historico WHERE id_documento = doc.id)");
		while ($db->nextRecord())
		{
			$tTmp = $tRow_doc;
			$tTmp = str_replace("{{NOME}}", clipString(utf8_encode($db->f("nome")),85), $tTmp);

			$dDescricao = utf8_encode($db->f("descricao"));
			$dDescricao = str_replace("<br />", "", $dDescricao);
			$tTmp = str_replace("{{DESC}}", clipString($dDescricao,85), $tTmp);

			if ($db->f("expired") > 0)
			{
				if ($db->f("validade") == "1111-11-11")
				{
					$tTmp = str_replace("{{EXP}}", "Sem", $tTmp);
					$tTmp = str_replace("{{STA}}", "Ok", $tTmp);
				}
				else if ($db->f("validade") == "0000-00-00")
				{
					$tTmp = str_replace("{{EXP}}", "---", $tTmp);
					$tTmp = str_replace("{{STA}}", "Ok", $tTmp);
				}
				else
				{
					$tTmp = str_replace("{{EXP}}", '<span class="vencido_sim">'.mysqlToBr($db->f("validade")).'</span>', $tTmp);
					$tTmp = str_replace("{{STA}}", '<span class="t-black">Vencido</span>', $tTmp);
				}
			}
			else
			{
				$tTmp = str_replace("{{EXP}}", '<span class="vencido_nao">'.mysqlToBr($db->f("validade")).'</span>', $tTmp);
				$tTmp = str_replace("{{STA}}", "Ok", $tTmp);
			}

			if (strlen($db->f("arquivo")) > 0)
				$tTmp = str_replace("{{BTN}}", '<a class="bt-style-2" href="'.linkFileBucket("vw/doc/".$db->f("arquivo")).'" target="_blank" style="height:25px;line-height:25px;">Abrir Anexo</a>', $tTmp);
	 		else
				$tTmp = str_replace("{{BTN}}", "", $tTmp);
	
			$oRows .= $tTmp;
		}
	}
	else
	{
		$oRows = '<tr><td colspan="4" class="no_recs">Acesso Restrito!</td></tr>';
	}


	if (strlen($oRows) == 0)
		$oRows = '<tr><td colspan="4" class="no_recs">Nenhum Documento!</td></tr>';

	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(2,0); ?>

			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo">Documentos</h4>
				<table cellpading="0" cellspacing="0" class="lic_list">
					<thead>
						<tr>
							<th style="text-align: left; width: 460px; border-bottom: 1px solid #bebebe;">Nome/Descrição</th>
							<th style="text-align: left; width: 120px; border-bottom: 1px solid #bebebe;">Validade</th>
							<th style="text-align: left; width: 180px; border-bottom: 1px solid #bebebe;">Status</th>
							<th style="text-align: left; border-bottom: 1px solid #bebebe;"></th>
						</tr>
					</thead>
					<tbody id="row-container">
						<?php echo $oRows; ?>
						<tr>
							<td colspan="4" style="height: 100px;"></td>
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
				<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;float:none;">Acesso Restrito!</h4>
				<p style="color: #a6a6a6;">Se você é cliente GELIC utilize o seu login e senha para ter acesso nesta área.</p>
			</div>
		</div>
	</section>
	<?php
}

?>
