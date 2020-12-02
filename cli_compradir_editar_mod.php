<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$db = new Mysql();

	if ($sInside_tipo == 1) //BO
	{
		echo '<section>
			<div class="middle">
				<div class="lic" style="height: 340px;">
					<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
				</div>
			</div>
		</section>';
		exit;
	}

	$xAccess = explode(" ",getAccess());

	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];

	$gId = 0;
	if (isset($_GET["id"]))
	{
		$gId = intval($_GET["id"]);
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
	(SELECT nome_arquivo FROM gelic_comprasrp_historico WHERE id_comprasrp = comp.id AND tipo = 1 AND arquivo <> '' ORDER BY id DESC LIMIT 1) AS nome_arquivo,
	(SELECT arquivo FROM gelic_comprasrp_historico WHERE id_comprasrp = comp.id AND tipo = 1 AND arquivo <> '' ORDER BY id DESC LIMIT 1) AS arquivo
FROM 
	gelic_comprasrp AS comp
WHERE 
	comp.id = $gId AND 
	(comp.id_cliente = $cliente_parent OR comp.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dTipo = $db->f("tipo");
			$dOrgao = utf8_encode($db->f("orgao"));
			$dNumero_srp = utf8_encode($db->f("numero_srp"));
			$dData_srp = mysqlToBr($db->f("data_srp"));
			$dDescritivo_veiculo = utf8_encode($db->f("descritivo_veiculo"));
			$dQuantidade = $db->f("quantidade");
			$dValor = $db->f("valor");
			$dValor = "R$ ".number_format($db->f("valor"), 2, ",", ".");
			if ($dValor == "R$ 0,00") $dValor = "";

			$dAnexo_btn = "none";
			$dAnexo_loading = "none";
			$dAnexo_ready = "block";
			$dAnexo_filename = utf8_encode($db->f("nome_arquivo"));
			$dAnexo_filesize = formatSizeUnits(sizeFileBucket("vw/comp/".$db->f("arquivo")));
			$dRtext = "KEEP{^=?!?=^}0";

			$vTitle = '(Editar...) - Solicitação de Compra Direta ou Adesão à ata de Registro de Preços';
			$vSave = "Enviar Alterações";
		}
		else
		{
			$gId = 0;
		}
	}


	$gId_atarp = 0;
	if (isset($_GET["a"]))
		$gId_atarp = intval($_GET["a"]);


	if ($gId == 0)
	{
		if (!in_array("cd_solicitar", $xAccess))
		{
			echo '<section>
				<div class="middle">
					<div class="lic" style="height: 340px;">
						<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
					</div>
				</div>
			</section>';
			exit;
		}

		$dId = 0;
		$dTipo = 1;
		$dOrgao = "";
		$dNumero_srp = "";
		$dData_srp = "";
		$dDescritivo_veiculo = "";
		$dQuantidade = "";
		$dValor = "";

		$dAnexo_btn = "block";
		$dAnexo_loading = "none";
		$dAnexo_ready = "none";
		$dAnexo_filename = "";
		$dAnexo_filesize = "";
		$dRtext = "";

		$vTitle = "(Nova!) - Solicitação de Compra Direta ou Adesão à ata de Registro de Preços";
		$vSave = "Enviar Solicitação";

		if ($gId_atarp > 0)
			$dTipo = 2;
	}

	$dTipo_v1 = (int)in_array($dTipo,array(1));
	$dTipo_v2 = (int)in_array($dTipo,array(2));


	if ($dTipo == 1)
		$atad = 'none';
	else
		$atad = 'block';

	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(5,4); ?>

			<div class="lic">
				<a class="bt-style-2 fr" href="index.php?p=cli_compradir">x</a>
				<h4 class="lic_tit" id="lic_titulo"><?php echo $vTitle; ?></h4>
			</div>


			<div class="lic">
				<?php echo '<script>rtext_tmp = "'.$dRtext.'";</script>'; ?>
				<form id="upload-form" enctype="multipart/form-data"></form>
				<form id="compradir-form">
					<input type="hidden" name="f-id" value="<?php echo $dId; ?>">
					<input type="hidden" name="f-tipo" value="<?php echo $dTipo; ?>">
					<input type="hidden" name="f-id_atarp" value="<?php echo $gId_atarp; ?>">

					<div class="rw">
						<span class="fl lh-38 w-240">Tipo</span>
						<a class="rb<?php echo $dTipo_v1; ?> cl-tipo" href="javascript:void(0);" onclick="ckTipo(this,'tipo',1);" style="margin-top: 10px;">Solicitação de Compra Direta</a>
						<a class="rb<?php echo $dTipo_v2; ?> cl-tipo" href="javascript:void(0);" onclick="ckTipo(this,'tipo',2);" style="clear:left;margin-bottom: 10px; margin-left:240px;">Adesão à ata de Registro de Preços</a>
					</div>

					<div class="rw">
						<span class="fl lh-38 w-240">Nome do Órgão Público</span>
						<input id="i-orgao" class="iText fl" type="text" name="f-orgao" placeholder="- órgão público -" maxlength="255" value="<?php echo $dOrgao; ?>" style="width:540px;">
					</div>
					<div class="rw">
						<span class="fl lh-38 w-240">Descritivo do Veículo</span>
						<input id="i-descritivo" class="iText fl" type="text" name="f-descritivo" placeholder="- descritivo do veículo -" maxlength="255" value="<?php echo $dDescritivo_veiculo; ?>" style="width:540px;">
					</div>
					<div class="rw" id="n-srp" style="display:<?php echo $atad; ?>;">
						<span class="fl lh-38 w-240">Número do SRP</span>
						<input id="i-numero-srp" class="iText fl" type="text" name="f-numero-srp" placeholder="- número SRP -" maxlength="20" value="<?php echo $dNumero_srp; ?>" style="width:180px;">
					</div>
					<div class="rw" id="d-srp" style="display:<?php echo $atad; ?>;">
						<span class="fl lh-38 w-240">Data do SRP</span>
						<input id="i-data-srp" class="iText fl" type="text" name="f-data-srp" placeholder="dd/mm/aaaa" maxlength="10" value="<?php echo $dData_srp; ?>" style="width:180px;">
					</div>
					<div class="rw">
						<span class="fl lh-38 w-240">Quantidade</span>
						<input id="i-quantidade" class="iText fl" type="text" name="f-quantidade" placeholder="- quantidade -" maxlength="10" value="<?php echo $dQuantidade; ?>" style="width:180px;">
					</div>
					<div class="rw">
						<span class="fl lh-38 w-240">Valor</span>
						<input id="i-valor" class="iText fl" type="text" name="f-valor" placeholder="R$  0,00" maxlength="24" value="<?php echo $dValor; ?>" style="width:180px;">
					</div>
					<div class="rw" style="margin-top:20px;">
						<span class="fl w-240" style="line-height:normal;">Ofício de solicitação de compra<br><span class="italic t-red">(anexo obrigatório)</span></span>
						<span class="fl t12 t-red" style="width:540px;">Para que a solicitação de compra direta ou adesão à ata de registro de preços seja enviada é necessário o envio do documento escaneado onde consta assinatura de autoridade competente solicitando a compra.</span>
						<div id="anexo-box">
							<div id="anexo-btn" style="display:<?php echo $dAnexo_btn; ?>;overflow:hidden;">
								<a class="bt-style-2 fl" href="javascript:void(0);" onclick="selectFile();" style="margin: 2px 0 0 2px;">Anexar Arquivo</a>
								<span class="fl italic t13 lh-34 ml-10 gray-88">Tamanho máximo (100MB)</span>
							</div>
							<div id="anexo-loading" style="display:<?php echo $dAnexo_loading; ?>;overflow:hidden;">
								<div id="anexo-loading-bar"></div>
								<span id="anexo-loading-per" class="t13">Carregando...</span>
							</div>
							<div id="anexo-ready" style="display:<?php echo $dAnexo_ready; ?>;overflow:hidden;background-color:#f1f1f1;">
								<img class="fl" src="img/btn-anexo.png">
								<span id="anexo-ready-filename" class="fl t13 italic lh-34"><?php echo $dAnexo_filename; ?></span>
								<span id="anexo-ready-filesize" class="fl t11 gray-88 italic lh-34 ml-10"><?php echo $dAnexo_filesize; ?></span>
								<a class="bt-style-2 fr" href="javascript:void(0);" onclick="cancelUpload();" style="padding: 0 10px; margin: 2px; font-size: 11px;" title="Cancelar">x</a>
							</div>
						</div>
					</div>
				</form>

				<div class="rw" style="margin-top: 30px;">
					<a class="bt-style-1 fl" href="javascript:void(0);" onclick="salvarSolicitacao();" style="margin-left: 240px;"><?php echo $vSave; ?></a>
				</div>
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
