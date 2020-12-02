<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$db = new Mysql();

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		//DN
		$oRows = '<div class="lic" style="height: 340px;">
			<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4>
		</div>';
	}
	else if ($sInside_tipo == 1) //BO
	{
		//BO
		$oRows = '<div class="lic">
					<h4 class="lic_tit" id="lic_titulo" style="display:block;width:100%;float:none;">Compra Direta/SRP (Solicitações)</h4>
					<table cellpading="0" cellspacing="0" style="width:100%; margin-top: 30px;">
						<thead>
							<tr>
								<th style="text-align: left; width: 810px; border-bottom: 1px solid #dedede; padding-left: 10px; line-height: 28px; background-color: #eeeeee;">DN</th>
								<th style="text-align: left; border-bottom: 1px solid #dedede; line-height: 28px; background-color: #eeeeee;">APL Aguardando Aprovação</th>
							</tr>
						</thead>
						<tbody>';

		$db->query("
SELECT 
	IF (cli.id_parent > 0, clip.id, cli.id) AS id_parent,
    IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome
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
		
			//contar solicitacoes aguardando aprovacao da APL
			$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT 
	comp.id,
	(SELECT tipo FROM gelic_comprasrp_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS apl_tipo
FROM 
	gelic_comprasrp AS comp
    INNER JOIN gelic_clientes AS cli ON cli.id = comp.id_cliente
    INNER JOIN gelic_comprasrp_apl AS apl ON apl.id_comprasrp = comp.id AND apl.id = (SELECT MAX(id) FROM gelic_comprasrp_apl WHERE id_comprasrp = comp.id)
WHERE
	comp.deletado = 0 AND 
	(cli.id = $dId_parent OR cli.id_parent = $dId_parent)
GROUP BY
	comp.id
HAVING
	apl_tipo IN (1,5,6)
) AS t",1);
			$db->nextRecord(1);
			$dTotal = $db->f("total",1);

			$oRows .= '<tr>
				<td colspan="2">
					<div class="hgl" style="position: relative;width: 100%; height: 34px; border-bottom: 1px solid #dedede;">
						<a class="apv'.(int)($dTotal > 0).'" style="position: absolute; left: 820px; top: 6px; line-height: 22px;">'.$dTotal.'</a>
						<a class="alnk" href="index.php?p=cli_compradir&idc='.$dId_parent.'">'.utf8_encode($db->f("nome")).'</a>
					</div>
				</td>
			</tr>';
		}

		$oRows .= '</tbody>
				</table>
			</div>';
	}

	?>
	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(5,5); ?>
			<?php echo $oRows; ?>

			<div style="height: 100px;"><!-- gap --></div>
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
				<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
				<p style="color: #a6a6a6;">Se você é cliente GELIC utilize o seu login e senha para ter acesso nesta área.</p>
			</div>
		</div>
	</section>
	<?php
}

?>
