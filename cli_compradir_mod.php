<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$db = new Mysql();

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$add_to_where = " AND (comp.id_cliente = $cliente_parent OR comp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))";
		$top_btn = '<a class="fr bt-style-2" href="index.php?p=cli_compradir_editar" style="height:26px;line-height:26px;font-weight:normal;">Nova Solicitação</a>';
	}
	else if ($sInside_tipo == 1) //BO
	{
		$gId_cliente = 0;
		if (isset($_GET["idc"]))
			$gId_cliente = intval($_GET["idc"]);
	
		//verificar se o cliente tem comprasrp
		$db->query("
SELECT 
	comp.id,
    IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome
FROM 
	gelic_comprasrp AS comp
    INNER JOIN gelic_clientes AS cli ON cli.id = comp.id_cliente
    LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE 
	comp.id_cliente = $gId_cliente OR 
	comp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $gId_cliente)");
		if ($db->nextRecord())
		{
			$dDn_nome = utf8_encode($db->f("nome"));
		}
		else
		{
			echo '<script>
			window.location = "index.php?p=cli_compradirbo";
			</script>';
			exit;
		}

		$add_to_where = " AND (comp.id_cliente = $gId_cliente OR comp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $gId_cliente))";
		$top_btn = '<a class="fr bt-style-2" href="index.php?p=cli_compradirbo" style="width: 26px; height: 26px; line-height: 26px; padding: 0;" title="Voltar">x</a>
		<span class="fr t-red" style="font-size:16px;height:26px;line-height:26px;font-weight:normal;margin-right:20px;">'.$dDn_nome.'</span>';
	}

	$aTipo = array();
	$aTipo[1] = 'sol-cd.png';
	$aTipo[2] = 'sol-srp.png';

	$aStatus = array();
	$aStatus[1] = '<span class="t-orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[2] = '<span class="t-green bold">APL Aprovada.</span>';
	$aStatus[4] = '<span class="t-red bold">APL Reprovada.</span>';
	$aStatus[5] = '<span class="t-orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[6] = '<span class="t-orange bold">APL Enviada. Aguardando aprovação.</span>';


	$oRows = '';

	//carregar solicitacoes
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
	comp.deletado = 0$add_to_where
ORDER BY
	comp.id DESC");

	while ($db->nextRecord())
	{
		if ($db->f("autorizado") != '')
			$auth = '<span class="t-green bold">O envio da APL foi autorizado.</span>';
		else if ($db->f("nautorizado") != '')
			$auth = '<span class="t-red bold">O envio da APL não foi autorizado.</span>';
		else
			$auth = '<span class="italic gray-88">Solicitação enviada. Aguardando autorização...</span>';


		//se tiver APL, mostrar status da APL
		if (strlen($db->f("apl_tipo")) > 0)
			$auth = $aStatus[$db->f("apl_tipo")];


		$oRows .= '<div class="cd-row">
			<img class="cd-img" src="img/'.$aTipo[$db->f("tipo")].'">
			<div class="cd-info">
				<p class="cd-lbs t12 bold">Data/Hora da Solicitação:<br>Órgão Público:<br>Quantidade:<br>Valor:<br>Status:</p>
				<p class="cd-vals t12">'.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.substr($db->f("data_hora"),11,5).'<br>'.utf8_encode($db->f("orgao")).'<br>'.$db->f("quantidade").'<br>R$ '.number_format($db->f("valor"),2,",",".").'<br>'.$auth.'</p>
			</div>';

		if ($sInside_tipo == 1) //BO
			$oRows .= '<a class="cd-btn" href="index.php?p=cli_compradir_abrir&id='.$db->f("id").'&idc='.$gId_cliente.'"></a>
				<img class="cd-btn-remover" src="img/btn-remover.png" style="opacity:0.3;">
				<img class="cd-btn-editar" src="img/btn-editar.png" style="opacity:0.3;">';
		else
			$oRows .= '<a class="cd-btn" href="index.php?p=cli_compradir_abrir&id='.$db->f("id").'"></a>
				<a href="javascript:void(0);" onclick="removerSolicitacao('.$db->f("id").',false);" title="Excluir"><img class="cd-btn-remover" src="img/btn-remover.png"></a>
				<a href="index.php?p=cli_compradir_editar&id='.$db->f("id").'" title="Editar"><img class="cd-btn-editar" src="img/btn-editar.png"></a>';

		$oRows .= '
			<a href="'.linkFileBucket("vw/comp/".$db->f("arquivo")).'" title="Anexo" target="_blank"><img class="cd-btn-anexo" src="img/btn-anexo.png"></a>
		</div>';
	}

	if (strlen($oRows) == 0)
		$oRows = '<div style="border: 1px solid #bebebe; text-align: center; padding: 40px 0;">Nenhuma solicitação!<br><br>[<a href="index.php?p=cli_compradir_editar" style="color: #0000ff;display:inline;">Nova Solicitação</a>]</div>';

	?>

	<section>
		<div class="middle">
			<?php echo getTop(); ?>
			<?php echo getMenu(5,5); ?>
			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo" style="display:block;width:100%;float:none;margin-bottom:40px;">Compra Direta/SRP (Solicitações)<?php echo $top_btn; ?></h4>
				<?php echo $oRows; ?>
			</div>
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
