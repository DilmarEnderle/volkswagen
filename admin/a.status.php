<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("sta_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">STATUS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);
		
		echo $tPage->body;
		exit;
	}

	$tRow = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<a class="alnk-status t14 abs lh-30 pl-10" href="a.status_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">&nbsp;</a>
		<a href="javascript:void(0);" onclick="removerStatus({{ID}},false);" title="Remover Status"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
		<a class="alnk-status-inside t14 abs" href="a.status_editar.php?id={{ID}}" style="left: 10px; top: 2px; color: #{{CT}}; background-color: #{{CF}};">{{D}}</a>
	</div>';

	$db = new Mysql();
	$oRows = "";

	$db->query("SELECT id, descricao, cor_texto, cor_fundo FROM gelic_status ORDER BY descricao");
	$dTotal_recs = $db->nf();
	while ($db->nextRecord())
	{
		$tTmp = $tRow;
		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{D}}", $db->f("descricao"), $tTmp);
		$tTmp = str_replace("{{CT}}", $db->f("cor_texto"), $tTmp);
		$tTmp = str_replace("{{CF}}", $db->f("cor_fundo"), $tTmp);
		$oRows .= $tTmp;
	}
	
	$tPage = new Template("a.status.html");
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
