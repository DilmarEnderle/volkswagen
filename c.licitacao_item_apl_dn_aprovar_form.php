<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1) //BO
	{
		$pId_licitacao = intval($_POST["id-licitacao"]);
		$pId_item = intval($_POST["id-item"]);
		$pId_cliente = intval($_POST["id-cliente"]);

		$dAve = "";
		$dQuantidade_veiculos = "";
		$dModel_code = "";
		$dCor = "";
		$dOpcionais_pr = "";
		$dPreco_publico_vw = "";
		$dPrazo_entrega = "";
		$dTransformacao = 0;
		$dD_transf = 'none';

		$db = new Mysql();
		$db->query("SELECT ave, quantidade_veiculos, model_code, cor, opcionais_pr, preco_publico_vw, prazo_entrega, transformacao FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = $pId_item AND id_cliente = $pId_cliente ORDER BY id DESC LIMIT 1");
		if ($db->nextRecord())
		{
			$dAve = utf8_encode($db->f("ave"));
			$dQuantidade_veiculos = $db->f("quantidade_veiculos");
			$dModel_code = utf8_encode($db->f("model_code"));
			$dCor = utf8_encode($db->f("cor"));
			$dOpcionais = utf8_encode($db->f("opcionais_pr"));
			$dPreco_publico_vw = ($db->f("preco_publico_vw") == '0.00') ? "" : "R$ ".number_format($db->f("preco_publico_vw"), 2, ",", ".");
			$dPrazo_entrega = $db->f("prazo_entrega");
			$dTransformacao = $db->f("transformacao");
		}

		if ($dTransformacao == 1)
			$dD_transf = 'block';


		$db->query("SELECT texto FROM gelic_texto WHERE id = 2");
		$db->nextRecord();
		$dCondicoes = utf8_encode($db->f("texto"));

		$aReturn[1] = '
		<form id="apr-form" enctype="multipart/form-data">
		<input type="hidden" name="f-apr-transformacao" value="'.$dTransformacao.'">
		<div class="ultimate-row" style="background-color:#dedede;">
			<div style="position: relative; float:left;width:462px;overflow:hidden;padding-top:10px;">
				<span class="fl lh-30" style="width:100px;padding-left:10px;">AVE <span class="t-red">*</span></span>
				<input id="i-apr-ave" class="iText fl" type="text" name="f-apr-ave" maxlength="20" value="'.$dAve.'" style="width:260px;height:30px;line-height:28px;font-size:13px;color:#000000;">

				<span class="fl lh-30 clear" style="width:100px;padding-left:10px;margin-top:3px;">Quantidade <span class="t-red">*</span></span>
				<input id="i-apr-quantidade" class="iText fl" type="text" name="f-apr-quantidade" maxlength="4" value="'.$dQuantidade_veiculos.'"style="width:100px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">

				<span class="fl lh-30 clear" style="width:100px;padding-left:10px;margin-top:3px;">Model Code <span class="t-red">*</span></span>
				<input id="i-apr-model" class="iText fl" type="text" name="f-apr-model" maxlength="6" value="'.$dModel_code.'" style="width:100px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">

				<span class="fl lh-30 clear" style="width:100px;padding-left:10px;margin-top:3px;">Cor <span class="t-red">*</span></span>
				<input id="i-apr-cor" class="iText fl" type="text" name="f-apr-cor" maxlength="4" value="'.$dCor.'" style="width:100px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">

				<span class="fl lh-30 clear" style="width:100px;padding-left:10px;margin-top:3px;">Opcionais</span>
				<input id="i-apr-opcionais" class="iText fl" type="text" name="f-apr-opcionais" value="'.$dOpcionais.'" style="width:320px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">
			</div>
			<div style="float:left;width:330px;overflow:hidden;padding-top:10px;">
				<span class="fl lh-30" style="width:120px;">Preço Público <span class="t-red">*</span></span>
				<input id="i-apr-preco-publico" class="iText fl" type="text" name="f-apr-preco-publico" value="'.$dPreco_publico_vw.'"style="width:180px;height:30px;line-height:28px;font-size:13px;color:#000000;">

				<span class="fl lh-30 clear" style="width:120px;margin-top:3px;">Desconto VW</span>
				<input id="i-apr-desconto-vw" class="iText fl" type="text" name="f-apr-desconto-vw" maxlength="7" style="width:100px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">

				<span class="fl lh-30 clear" style="width:120px;margin-top:3px;">Comissão DN</span>
				<input id="i-apr-comissao-dn" class="iText fl" type="text" name="f-apr-comissao-dn" maxlength="7" style="width:100px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">

				<span class="fl lh-30 clear" style="width:120px;margin-top:3px;">Planta <span class="t-red">*</span></span>
				<select id="i-apr-planta" class="iText fl" name="f-apr-planta" style="width:180px;height:30px;line-height:28px;font-size:13px;color:#000000;background-color:#ffffff;padding:0 0 0 6px;margin-top:3px;">
					<option value="0"></option>
					<option value="1">TBT/SP (0024-46)</option>
					<option value="2">SBC/SP</option>
					<option value="3">SJP/PR (0103-84)</option>
				</select>

				<span class="fl lh-30 clear" style="width:120px;margin-top:3px;">Prazo de Entrega <span class="t-red">*</span></span>
				<input id="i-apr-prazo-entrega" class="iText fl" type="text" name="f-apr-prazo-entrega" maxlength="3" value="'.$dPrazo_entrega.'" style="width:180px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">

				<span class="fl lh-30 clear" style="display:'.$dD_transf.';width:120px;margin-top:3px;">Valor da Transf. <span class="t-red">*</span></span>
				<input id="i-apr-valor-transf" class="iText fl" type="text" name="f-apr-valor-transf" style="display:'.$dD_transf.';width:180px;height:30px;line-height:28px;font-size:13px;color:#000000;margin-top:3px;">
			</div>
			<span class="fl lh-30" style="width:100px;padding-left:10px;margin-top:10px;">Arquivo Anexo</span>
			<div style="float: left; height: 28px; width: 650px; background-color: #ffffff; border: 1px solid #d1d1d1;margin-top:10px;font-size:13px;">
				<div id="apr-upl-btn" style="display:block;">
					<a class="bt-style-2 fl" href="javascript:void(0);" onclick="selectFileAPR();" style="height:26px; line-height: 26px; margin: 1px 0 0 1px;">Anexar Arquivo</a>
					<span class="fl ml-10 italic gray-88" style="line-height:28px;">Tamanho Máximo (100MB)</span>
				</div>
				<div id="apr-upl-loading" style="display:none;">
					<div class="bar"></div>
					<span class="per"></span>
				</div>
				<div id="apr-upl-ready" style="display:none;">
					<span class="fname fl t-red italic ml-10" style="line-height:28px;"></span>
					<span class="fsize fl gray-88 italic ml-10" style="line-height:28px;"></span>
					<a class="bt-style-2 fr" href="javascript:void(0);" onclick="cancelUploadAPR();" style="height:26px; line-height: 26px; margin: 1px 1px 0 0; padding: 0; width: 32px;">x</a>
				</div>
			</div>
			<div style="height:10px;clear:both;float:left;"></div>
		</div>
		<div id="ultimate-error" class="ultimate-row"></div>
		<div class="ultimate-row" style="margin-top: 30px;">Condições para Participação</div>
		<div class="ultimate-row">
			<textarea id="i-condicoes" class="apl-textarea" name="f-apr-condicoes" style="width: 100%; height: 200px;">'.$dCondicoes.'</textarea>
		</div>
		</form>';

		$aReturn[2] = 820;
		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
