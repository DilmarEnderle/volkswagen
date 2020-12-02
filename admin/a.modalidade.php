<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("mod_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">MODALIDADES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);

		echo $tPage->body;
		exit;
	}

	$tRow = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<a class="alnk t14 abs lh-30 pl-10" href="a.modalidade_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{N}}</a>
		<a class="alnk t14 abs lh-30 lf-500" href="a.modalidade_editar.php?id={{ID}}">{{ABV}}</a>
		<a href="javascript:void(0);" onclick="removerModalidade({{ID}},false);" title="Remover Modalidade"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
	</div>';

	$db = new Mysql();
	
	$oRows = "";
	
	$db->query("SELECT COUNT(*) AS total FROM gelic_modalidades WHERE antigo = 0");
	$db->nextRecord();
	$total_recs = $db->f("total");
	
	$query = "SELECT * FROM gelic_modalidades WHERE antigo = 0 ORDER BY nome";
	$db->query($query);
	while ($db->nextRecord())
	{
		$tTmp = $tRow;
		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{N}}", $db->f("nome"), $tTmp);
		$tTmp = str_replace("{{ABV}}", $db->f("abv"), $tTmp);
		$oRows .= $tTmp;
	}
	
	$tPage = new Template("a.modalidade.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TOTAL}}", $total_recs);
	$tPage->replace("{{ROWS}}", utf8_encode($oRows));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
