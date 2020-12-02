<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$db = new Mysql();

	$gId = 0;
	if (isset($_GET["id"]))
		$gId = intval($_GET["id"]);

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$add_to_where = " AND (comp.id_cliente = $cliente_parent OR comp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))";
		$voltar_params = "";
	}
	else
	{
		$gId_cliente = 0;
		if (isset($_GET["idc"]))
			$gId_cliente = intval($_GET["idc"]);

		$add_to_where = " AND (comp.id_cliente = $gId_cliente OR comp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $gId_cliente))";
		$voltar_params = "&idc=$gId_cliente";
	}

	$aTipo = array();
	$aTipo[1] = 'sol-cd.png';
	$aTipo[2] = 'sol-srp.png';

	$aStatus = array();
	$aStatus[1] = '<span class="fl t-orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[2] = '<span class="fl t-green bold">APL Aprovada.</span>';
	$aStatus[4] = '<span class="fl t-red bold">APL Reprovada.</span>';
	$aStatus[5] = '<span class="fl t-orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[6] = '<span class="fl t-orange bold">APL Enviada. Aguardando aprovação.</span>';

	$autorizado = false;
	$db->query("
SELECT 
	comp.id,
	comp.tipo,
    comp.orgao,
	comp.numero_srp,
    comp.data_srp,
	comp.descritivo_veiculo,
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
	comp.id = $gId AND
	comp.deletado = 0$add_to_where");
	if ($db->nextRecord())
	{
		if ($db->f("autorizado") != '')
		{
			$status = '<span class="fl t-green bold">O envio da APL foi autorizado.</span>';
			$autorizado = true;
		}
		else if ($db->f("nautorizado") != '')
		{
			$status = '<span class="fl t-red bold">O envio da APL não foi autorizado.</span>';
		}
		else
		{
			$status = '<span class="fl italic gray-88">Solicitação enviada. Aguardando autorização...</span>';
		}

		//se tiver APL, mostrar status da APL
		if (strlen($db->f("apl_tipo")) > 0)
			$status = $aStatus[$db->f("apl_tipo")];

		$add_to_row = '';
		if ($db->f("tipo") == 2) //Adesão à ata de Registro de Preços
			$add_to_row = '<span class="clear bold fl w-160">Número do SRP:</span>
				<span class="fl w-600">'.utf8_encode($db->f("numero_srp")).'</span>

				<span class="clear bold fl w-160">Data do SRP:</span>
				<span class="fl">'.mysqlToBr($db->f("data_srp")).'</span>';


		$das_h = substr($db->f("data_hora"),11,5);
		if ($das_h == "00:00") $das_h = "--:--";

		$oRow = '<div class="cd-row">
			<img class="cd-img" src="img/'.$aTipo[$db->f("tipo")].'">
			<div class="cd-info">
				<span class="bold fl w-160">Data/Hora da Solicitação:</span>
				<span class="fl">'.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.$das_h.'</span>

				<span class="clear bold fl w-160">Órgão Público:</span>
				<span class="fl">'.utf8_encode($db->f("orgao")).'</span>

				<span class="clear bold fl w-160">Descritivo do Veículo:</span>
				<span class="fl w-600">'.utf8_encode($db->f("descritivo_veiculo")).'</span>
				'.$add_to_row.'
				<span class="clear bold fl w-160">Quantidade:</span>
				<span class="fl">'.$db->f("quantidade").'</span>

				<span class="clear bold fl w-160">Valor:</span>
				<span class="fl">R$ '.number_format($db->f("valor"),2,",",".").'</span>

				<span class="clear bold fl w-160">Status:</span>
				'.$status.'
			</div>
			<a href="'.linkFileBucket("vw/comp/".$db->f("arquivo")).'" title="Anexo" target="_blank"><img class="cd-btn-anexo" src="img/btn-anexo.png"></a>
		</div>';
	}
	else
	{
		echo '<script>window.location = "index.php?p=cli_compradir";</script>';
		exit;
	}

	$oApl = '';
	if ($autorizado)
	{
		$aAPL_B_style = array();
		$aAPL_B_style[1] = 'Aguardando aprovação'; //preenchida pelo cliente (preto)
		$aAPL_B_style[2] = 'Aprovada';             //aprovada (verde)
		$aAPL_B_style[4] = 'Reprovada';            //reprovada (vermelho)

		//verificar status da APL e se foi enviada ou nao
		$db->query("SELECT 
			(SELECT tipo FROM gelic_comprasrp_apl_historico WHERE id_apl = apl.id AND tipo < 5 ORDER BY id DESC LIMIT 1) AS tipo
		FROM
			gelic_comprasrp_apl AS apl 
		WHERE 
			apl.id = (SELECT MAX(id) FROM gelic_comprasrp_apl WHERE id_comprasrp = $gId)");
		if ($db->nextRecord())
		{
			$dStatus_apl = $db->f("tipo");
			$oApl = '<div style="overflow: hidden; text-align: center; padding: 10px 0;">
				<a id="apl-btn" class="bt-apl-style-'.$dStatus_apl.'" href="javascript:void(0);" onclick="cdAPL('.$gId.');" style="height:34px;line-height:34px;font-size:13px;width:100px;" title="'.$aAPL_B_style[$dStatus_apl].'">APL &#x21e3;</a>
			</div>
			<div id="apl" style="width:940px;margin:0 auto;"></div>';
		}
		else
		{
			if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				$oApl = '<div style="overflow: hidden; text-align: center; padding: 10px 0">
					<a id="apl-btn" class="bt-style-2" href="javascript:void(0);" onclick="cdAPL('.$gId.');" style="height:34px;line-height:34px;font-size:13px;width:200px;" title="Não Preenchida">ENVIAR APL &#x21e3;</a>
				</div>
				<div id="apl" style="width:940px;margin:0 auto;"></div>';
		}
	}

	//$oSnd = '';
	//if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
		$oSnd = '<div class="send">
			<form id="upload-form" enctype="multipart/form-data"></form>
			<h4 class="send_tit">Enviar Mensagem/Arquivo</h4>
			<div class="send_box">
				<textarea id="i-mensagem" placeholder="Digite aqui a mensagem..." style="resize: none;"></textarea>
				<div id="upl-btn" class="send_box_bottom">
					<a class="bt-style-2 fl" href="javascript:void(0);" onclick="selectFileMSG();" style="height:25px;line-height:25px;margin:5px;">Anexar Arquivo</a>
					<span>Tamanho máximo (100 MB)</span>
				</div>
				<div id="upl-loading" class="send_box_bottom" style="display: none; position: relative; height: 35px;">
					<div id="upl-bar" style="position: absolute; left: 10px; top: 6px; height: 25px; background-color: #deddcc; width: 1178px;"></div>
					<span id="upl-per" style="left: 20px;position: absolute;">Carregando... Aguarde...</span>					
  				</div>
				<div id="upl-ready" class="send_box_bottom" style="display: none;">
					<span style="margin-left: 10px;">Arquivo Anexo:</span> <span id="upl-filename" style="display: inline; color: #ff0000; margin-left:8px;"></span> <span id="upl-filesize" style="margin-left:8px;">(0 bytes)</span>
					<a class="bt-style-2 fr" href="javascript:void(0);" onclick="cancelUploadMSG();" style="height:25px;line-height:25px;margin:5px;">Cancelar</a>
				</div>
			</div>
			<div id="enviar-box" style="overflow:hidden;">
				<a class="bt-style-1 fl" href="javascript:void(0);" onclick="enviarMensagem();" style="height:40px;line-height:40px;">Enviar</a>
			</div>
			<div id="processando-box" style="display: none;">
				<div class="processando">Enviando...</div>
			</div>
		</div>';


	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(5,5); ?>

			<div class="lic">
				<div class="topbts" style="position: relative;">
					<a class="menu-btn fl" href="index.php?p=cli_compradir<?php echo $voltar_params; ?>">← Voltar</a>
				</div>
				<input id="id-cd" type="hidden" value="<?php echo $gId; ?>">
				<form id="upload-form-apr" enctype="multipart/form-data"></form>

				<?php
					echo $oRow;
					echo $oApl;
					echo '<div id="historico"></div>';
					echo $oSnd;
				?>

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
