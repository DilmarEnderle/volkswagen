<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cd_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">COMPRA DIRETA/SRP</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);
		
		echo $tPage->body;
		exit;
	}

	$gId_parent = 0;
	if (isset($_GET["id"]))
		$gId_parent = intval($_GET["id"]);

	if ($gId_parent == 0)
	{
		header("location: a.compradir.php");
		exit;
	}

	$aTipo = array();
	$aTipo[1] = 'sol-cd.png';
	$aTipo[2] = 'sol-srp.png';

	$aStatus = array();
	$aStatus[1] = '<span class="orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[2] = '<span class="green bold">APL Aprovada.</span>';
	$aStatus[4] = '<span class="red bold">APL Reprovada.</span>';
	$aStatus[5] = '<span class="orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[6] = '<span class="orange bold">APL Enviada. Aguardando aprovação.</span>';

	$db = new Mysql();
	$oRows = '';

	$db->query("
SELECT 
	comp.id,
	comp.tipo,
    comp.orgao,
    comp.quantidade,
    comp.valor,
	(SELECT arquivo FROM gelic_comprasrp_historico WHERE id_comprasrp = comp.id AND tipo = 1 AND arquivo <> '' ORDER BY id DESC LIMIT 1) AS arquivo,
	(SELECT data_hora FROM gelic_comprasrp_historico WHERE id_comprasrp = comp.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS data_hora,
	(SELECT id FROM gelic_comprasrp_historico WHERE id_comprasrp = comp.id AND tipo = 5 LIMIT 1) AS autorizado,
	(SELECT id FROM gelic_comprasrp_historico WHERE id_comprasrp = comp.id AND tipo = 6 LIMIT 1) AS nautorizado,
	(SELECT tipo FROM gelic_comprasrp_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS apl_tipo
FROM 
	gelic_comprasrp AS comp
	LEFT JOIN gelic_comprasrp_apl AS apl ON apl.id_comprasrp = comp.id AND apl.id = (SELECT MAX(id) FROM gelic_comprasrp_apl WHERE id_comprasrp = comp.id)
WHERE
	comp.deletado = 0 AND 
	(comp.id_cliente = $gId_parent OR comp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $gId_parent))
ORDER BY
	comp.id DESC");
	while ($db->nextRecord())
	{
		if ($db->f("autorizado") != '')
			$auth = '<span class="green bold">O envio da APL foi autorizado.</span>';
		else if ($db->f("nautorizado") != '')
			$auth = '<span class="red bold">O envio da APL não foi autorizado.</span>';
		else
			$auth = '<span class="italic gray-88">Aguardando autorização...</span>';

		//se tiver APL, mostrar status da APL
		if (strlen($db->f("apl_tipo")) > 0)
			$auth = $aStatus[$db->f("apl_tipo")];

		$oRows .= '<div class="cd-row">
			<img class="cd-img" src="img/'.$aTipo[$db->f("tipo")].'">
			<div class="cd-info">
				<p class="cd-lbs t13 bold">Data/Hora da Solicitação:<br>Órgão Público:<br>Quantidade:<br>Valor:<br>Status:</p>
				<p class="cd-vals t13">'.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.substr($db->f("data_hora"),11,5).'<br>'.utf8_encode($db->f("orgao")).'<br>'.$db->f("quantidade").'<br>R$ '.number_format($db->f("valor"),2,",",".").'<br>'.$auth.'</p>
			</div>
			<a class="cd-btn" href="a.compradir_abrir.php?id='.$db->f("id").'"></a>
			<a href="'.linkFileBucket("vw/comp/".$db->f("arquivo")).'" title="Anexo" target="_blank"><img class="cd-btn-anexo" src="img/btn-anexo.png"></a>
		</div>';
	}

	$db->query("SELECT nome FROM gelic_clientes WHERE id = $gId_parent");
	$db->nextRecord();
	$dDn = utf8_encode($db->f("nome"));

	$tPage = new Template("a.compradir_lista.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{DN}}", $dDn);
	$tPage->replace("{{ROWS}}", $oRows);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
