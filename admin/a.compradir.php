<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cd_visualizar", $xAccess))
	{
		$oRows = utf8_decode('<div class="content-inside" style="padding-top: 20px;">
			<div class="full-row" style="padding: 0 0 2px 0; border-bottom: 1px solid #666666;">
				<span class="t18 bold lh-30 fl red">Acesso Restrito!</span>
			</div>
			<div class="t14" style="position: relative; margin: 40px auto; width: 500px; text-align: center; border: 1px solid #999999; padding: 20px 0;">
				<span class="bold">COMPRA DIRETA/SRP</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span><br><br><br>
				<a class="bt-style-1" href="javascript:window.history.back();" style="display: inline-block;">Ok</a>
			</div>
		</div>');

		$dh = "none";
	}
	else
	{
		$db = new Mysql();
		$oRows = '';

		$db->query("
SELECT 
	if (cli.id_parent > 0, clip.id, cli.id) AS id_parent,
    if (cli.id_parent > 0, clip.nome, cli.nome) AS nome
FROM 
	gelic_comprasrp AS comp
	INNER JOIN gelic_clientes AS cli ON cli.id = comp.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	comp.deletado = 0
GROUP BY
	id_parent");
		while ($db->nextRecord())
		{
			$dId_parent = $db->f("id_parent");
		
			//contar solicitacoes aguardando autorizacao de envio APL
			$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT 
	    (SELECT id FROM gelic_comprasrp_historico WHERE id_comprasrp = comp.id AND tipo IN (5,6) LIMIT 1) AS autorizado
	FROM 
		gelic_comprasrp AS comp
	    INNER JOIN gelic_clientes AS cli ON cli.id = comp.id_cliente
	WHERE
		comp.deletado = 0 AND 
		(cli.id = $dId_parent OR cli.id_parent = $dId_parent)
	HAVING
		autorizado IS NULL
) AS t",1);
			$db->nextRecord(1);
			$dTotal = $db->f("total",1);

			$oRows .= '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
				<a class="alnk t14 abs lh-30 pl-10" href="a.compradir_lista.php?id='.$dId_parent.'" style="display: inline-block; width: 100%; box-sizing: border-box;">'.utf8_encode($db->f("nome")).'</a>
				<a class="abs aut'.(int)($dTotal > 0).' lf-800 tp-5 lh-20">'.$dTotal.'</a>
			</div>';
		}
		
		$dh = "block";
	}

	$tPage = new Template("a.compradir.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{DH}}", $dh);
	$tPage->replace("{{ROWS}}", utf8_encode($oRows));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
