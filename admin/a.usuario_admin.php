<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("usr_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">USUÁRIOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);

		echo $tPage->body;
		exit;
	}

	$tRow = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<a class="alnk t14 abs lh-30 pl-10" href="a.usuario_admin_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{N}} <span class="gray-88">({{L}})</span></a>
		<a class="alnk t14 abs lh-30 lf-600" href="a.usuario_admin_editar.php?id={{ID}}">{{P}}</a>
		<a class="abs ativo{{A}} lf-940 tp-5 lh-20">{{AT}}</a>
		<a href="javascript:void(0);" onclick="removerUsuario({{ID}},false);" title="'.utf8_decode("Remover Usuário").'"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
	</div>';

	$aActive = array();
	$aActive[0] = utf8_decode("Não");
	$aActive[1] = "Sim";
	
	$db = new Mysql();
	$oRows = "";

	$db->query("SELECT usr.id, usr.nome, usr.login, usr.ativo, pfl.nome AS perfil FROM gelic_admin_usuarios AS usr INNER JOIN gelic_admin_usuarios_perfis AS pfl ON pfl.id = usr.id_perfil ORDER BY usr.ativo DESC, usr.nome");
	$dTotal_recs = $db->nf();
	while ($db->nextRecord())
	{
		$tTmp = $tRow;
		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{N}}", $db->f("nome"), $tTmp);
		$tTmp = str_replace("{{L}}", $db->f("login"), $tTmp);
		$tTmp = str_replace("{{P}}", $db->f("perfil"), $tTmp);
		$tTmp = str_replace("{{A}}", $db->f("ativo"), $tTmp);
		$tTmp = str_replace("{{AT}}", $aActive[$db->f("ativo")], $tTmp);
		$oRows .= $tTmp;
	}
	
	$tPage = new Template("a.usuario_admin.html");
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
