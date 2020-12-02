<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("mot_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">MOTIVOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);
		
		echo $tPage->body;
		exit;
	}

	$tRow = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<a class="alnk t14 abs lh-30 pl-10" href="a.motivo_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{T}}</a>
		<a class="alnk t14 abs lh-30 lf-300" href="a.motivo_editar.php?id={{ID}}">{{D}}</a>
		<a href="javascript:void(0);" onclick="removerMotivo({{ID}},false);" title="Remover Motivo"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
		{{ARR}}
	</div>';

	$db = new Mysql();
	$oRows = "";

	$aTipos = array();
	$aTipos[10] = '<span style="display:inline-block;background-color:#444444;color:#dddddd;line-height:25px;padding:0 6px;border-radius:4px;">Admin (Encerramento)</span>';
	$aTipos[20] = '<span style="display:inline-block;background-color:#001fb8;color:#ffffff;line-height:25px;padding:0 6px;border-radius:4px;">Admin (Prorrogação)</span>';
	$aTipos[21] = '<span style="display:inline-block;background-color:#827c7c;color:#ffffff;line-height:25px;padding:0 6px;border-radius:4px;">Admin Itens (Inabilitado)</span>';
	$aTipos[22] = '<span style="display:inline-block;background-color:#81b388;color:#ffffff;line-height:25px;padding:0 6px;border-radius:4px;">Admin Itens (Sem Interesse DN - Aberto)</span>';
	$aTipos[23] = '<span style="display:inline-block;background-color:#ffe600;color:#000000;line-height:25px;padding:0 6px;border-radius:4px;">Admin Itens (Sem Participação - com APL)</span>';
	$aTipos[30] = '<span style="display:inline-block;background-color:#ff0000;color:#ffffff;line-height:25px;padding:0 6px;border-radius:4px;">Cliente (Sem Interesse Licitação)</span>';
	$aTipos[40] = '<span style="display:inline-block;background-color:#fc6500;color:#000000;line-height:25px;padding:0 6px;border-radius:4px;">Cliente (Reprovação APL)</span>';

	$db->query("SELECT id, tipo, descricao FROM gelic_motivos WHERE id_parent = 0 ORDER BY tipo, descricao");
	$dTotal_recs = $db->nf();
	while ($db->nextRecord())
	{
		$tTmp = $tRow;
		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{T}}", utf8_decode($aTipos[$db->f("tipo")]), $tTmp);
		$tTmp = str_replace("{{D}}", $db->f("descricao"), $tTmp);
		$tTmp = str_replace("{{ARR}}", "", $tTmp);
		$oRows .= $tTmp;

		$db->query("SELECT id, descricao FROM gelic_motivos WHERE id_parent = ".$db->f("id")." ORDER BY descricao",1);
		while ($db->nextRecord(1))
		{
			$tTmp = $tRow;
			$tTmp = str_replace("{{ID}}", $db->f("id",1), $tTmp);
			$tTmp = str_replace("{{T}}", "&nbsp;", $tTmp);
			$tTmp = str_replace("{{D}}", $db->f("descricao",1), $tTmp);
			$tTmp = str_replace("{{ARR}}", '<a href="a.motivo_editar.php?id={{ID}}"><img class="subarrow" src="img/subarrow.png"></a>', $tTmp);
			$oRows .= $tTmp;
			$dTotal_recs++;
		}
	}
	
	$tPage = new Template("a.motivo.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TOTAL}}", $dTotal_recs);
	$tPage->replace("{{ROWS}}", utf8_encode($oRows));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
