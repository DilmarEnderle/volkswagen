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

	$gId = 0;
	if (isset($_GET["id"]))
		$gId = intval($_GET["id"]);

	$aTipo = array();
	$aTipo[1] = 'sol-cd.png';
	$aTipo[2] = 'sol-srp.png';

	$aStatus = array();
	$aStatus[1] = '<span class="fl orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[2] = '<span class="fl green bold">APL Aprovada.</span>';
	$aStatus[4] = '<span class="fl red bold">APL Reprovada.</span>';
	$aStatus[5] = '<span class="fl orange bold">APL Enviada. Aguardando aprovação.</span>';
	$aStatus[6] = '<span class="fl orange bold">APL Enviada. Aguardando aprovação.</span>';

	$db = new Mysql();

	$oRow = '';

	$autorizado = false;
	$db->query("
SELECT 
	comp.id,
	comp.id_cliente,
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
	comp.deletado = 0");
	if ($db->nextRecord())
	{
		$dId_cliente = $db->f("id_cliente");
		$auth_btn = '';

		if ($db->f("autorizado") != '')
		{
			$status = '<span class="fl green bold">O envio da APL foi autorizado.</span>';
			$autorizado = true;
		}
		else if ($db->f("nautorizado") != '')
		{
			$status = '<span class="fl red bold">O envio da APL não foi autorizado.</span>';
		}
		else
		{
			$status = '<span class="fl italic gray-88">Aguardando autorização...</span>';
			if (in_array("cd_autorizar", $xAccess))
				$auth_btn = '<a class="bt-autorizar" href="javascript:void(0);" onclick="autorizarAPL('.$gId.',false);">Autorizar envio da APL</a>
					<a class="bt-nautorizar" href="javascript:void(0);" onclick="nautorizarAPL('.$gId.',false);">Não autorizar envio da APL</a>';
		}

		//se tiver APL, mostrar status da APL
		if (strlen($db->f("apl_tipo")) > 0)
			$status = $aStatus[$db->f("apl_tipo")];

		$add_to_row = '';
		if ($db->f("tipo") == 2)
			$add_to_row = '<span class="clear bold fl w-170">Número do SRP:</span>
				<span class="fl w-600">'.utf8_encode($db->f("numero_srp")).'</span>

				<span class="clear bold fl w-170">Data do SRP:</span>
				<span class="fl">'.mysqlToBr($db->f("data_srp")).'</span>';


		$oRow = '<div class="cd-row">
			<img class="cd-img" src="img/'.$aTipo[$db->f("tipo")].'">
			<div class="cd-info">
				<span class="bold fl w-170">Data/Hora da Solicitação:</span>
				<span class="fl">'.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.substr($db->f("data_hora"),11,5).'</span>

				<span class="clear bold fl w-170">Órgão Público:</span>
				<span class="fl">'.utf8_encode($db->f("orgao")).'</span>

				<span class="clear bold fl w-170">Descritivo do Veículo:</span>
				<span class="fl w-600">'.utf8_encode($db->f("descritivo_veiculo")).'</span>
				'.$add_to_row.'
				<span class="clear bold fl w-170">Quantidade:</span>
				<span class="fl">'.$db->f("quantidade").'</span>

				<span class="clear bold fl w-170">Valor:</span>
				<span class="fl">R$ '.number_format($db->f("valor"),2,",",".").'</span>

				<span class="clear bold fl w-170">Status:</span>
				'.$status.'
			</div>
			<a href="'.linkFileBucket("vw/comp/".$db->f("arquivo")).'" title="Anexo" target="_blank"><img class="cd-btn-anexo" src="img/btn-anexo.png"></a>
			'.$auth_btn.'
		</div>';
	}
	else
	{
		header("location: a.compradir.php");
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
			<div id="apl"></div>';
		}
	}


	$oSnd = '<div class="content pt-40">
		<div class="content-inside mb-8 w-800">
			<img class="fl" src="img/mail.png">
			<span class="t18 gray-33 fl ml-10 mt-10 lh-26">Enviar Mensagem/Arquivo</span>
		</div>
		<div class="content-inside w-800">
			<textarea id="i-mensagem" name="msg" placeholder="- digite aqui a mensagem -"></textarea>
		</div>
		<div class="content-inside w-800" style="border: 1px solid #cccccc; border-top: 0; height: 37px;">
			<div id="upl-btn" style="display: block;">
				<span class="t11 red" style="position: absolute; right: 14px; top: 0; line-height: 37px;">Máx. 100 MB</span>
				<img src="img/pc-top.png" style="position: absolute; left: 12px; top: 1px;">
				<a class="upl-btn" href="javascript:void(0);" onclick="selectFileMSG();"><img src="img/pc-bot.png">Anexar Arquivo</a>
			</div>
			<div id="upl-loading" style="display: none;">
				<div id="upl-bar"></div>
				<span id="upl-per">Carregando...100%</span>
			</div>
			<div id="upl-ready" style="display: none;">
				<img src="img/file.png" style="position: absolute; left: 6px; top: 6px; border: 0;">
				<span id="upl-filename" class="t13 red italic" style="position: absolute; left: 40px; top: 6px; line-height: 24px;">---</span>
				<span id="upl-filesize" class="gray-4c italic t11" style="position: absolute; right: 40px; top: 6px; line-height: 24px;">0 bytes</span>
				<a class="btn-x24" href="javascript:void(0);" onclick="cancelUploadMSG();" style="right: 6px; top: 6px;" title="Cancelar"></a>
			</div>
		</div>
		<div id="enviar-box" class="content-inside mt-10 w-800">
			<a class="bt-style-1" href="javascript:void(0);" onclick="enviarMensagem();">Enviar</a>
		</div>
		<div id="processando-box" class="content-inside mt-10 w-800" style="display: none;">
			<div class="processando">Enviando...</div>
		</div>
		<div style="position: absolute; left: 0; top: 15px; width: 100%; height: 1px; border-top: 1px solid #cccccc;"></div>
	</div>';


	if (!in_array("cd_mensagem", $xAccess))
		$oSnd = '';


	$db->query("
SELECT
	IF (dn.nome IS NULL, cli.id, dn.id) AS id,
	IF (dn.nome IS NULL, cli.nome, dn.nome) AS nome
FROM
	gelic_clientes AS cli
	LEFT JOIN gelic_clientes AS dn ON dn.id = cli.id_parent
WHERE
	cli.id = $dId_cliente");
	$db->nextRecord();
	$dId_parent = $db->f("id");
	$dDn = utf8_encode($db->f("nome"));

	$tPage = new Template("a.compradir_abrir.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{DN}}", $dDn);
	$tPage->replace("{{ID}}", $gId);
	$tPage->replace("{{ID_CLIENTE}}", $dId_parent);
	$tPage->replace("{{ROW}}", $oRow);
	$tPage->replace("{{APL}}", $oApl);
	$tPage->replace("{{SND}}", $oSnd);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
