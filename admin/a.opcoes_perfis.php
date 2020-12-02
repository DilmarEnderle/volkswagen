<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$db = new Mysql();
	
	$tRow = '<div class="row-item hgl">
		<a class="alnk t14 abs lh-30 pl-10" href="javascript:void(0);" onclick="editarPerfil({{ID}});" style="display: inline-block; width: 100%; box-sizing: border-box;">{{NOME}}</a>
		<a href="javascript:void(0);" onclick="removerPerfil({{ID}},false);" title="Remover Perfil"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
		<a class="abs t{{TN}} tp-5 lh-20" style="right:60px;">{{T}}</a>
	</div>';

	$oRows = '';
	$db->query("SELECT usrp.id, usrp.nome, (SELECT COUNT(*) FROM gelic_admin_usuarios WHERE id_perfil = usrp.id) AS total FROM gelic_admin_usuarios_perfis AS usrp ORDER BY usrp.nome");
	while ($db->nextRecord())
	{
		$tTmp = $tRow;
		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{NOME}}", utf8_encode($db->f("nome")), $tTmp);
		$tTmp = str_replace("{{T}}", $db->f("total"), $tTmp);
		if ($db->f("total") > 0)
			$tTmp = str_replace("{{TN}}", "1", $tTmp);
		else
			$tTmp = str_replace("{{TN}}", "0", $tTmp);
		$oRows .= $tTmp;
	}
	echo $oRows;
}

?>
