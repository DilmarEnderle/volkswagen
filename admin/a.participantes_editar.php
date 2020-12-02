<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId = intval($_POST["id"]);
	$pId_item = intval($_POST["id-item"]);

	$db = new Mysql();

	if ($pId > 0)
	{
		$db->query("SELECT * FROM gelic_licitacoes_itens_participantes WHERE id = $pId AND deletado = 0");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dRazao_social = utf8_encode($db->f("razao_social"));
			$dCnpj = $db->f("cnpj");
			$dFabricante = utf8_encode($db->f("fabricante"));
			$dModelo = utf8_encode($db->f("modelo"));
			$dDn_venda = $db->f("dn_venda");
			$dValor_final = "R$  ".number_format($db->f("valor_final"), 2, ",", ".");
			if ($dValor_final == "R$ 0,00") $dValor_final = '';
			$dVencedor = $db->f("vencedor");
			$dInabilitado = $db->f("inabilitado");

			$vTitle = 'Editar Participante';
			$vSave = "Salvar Alterações";
		}
		else
		{
			$pId = 0;
		}
	}

	if ($pId == 0)
	{
		$dId = 0;
		$dRazao_social = '';
		$dCnpj = '';
		$dFabricante = '';
		$dModelo = '';
		$dDn_venda = '';
		$dValor_final = '';
		$dInabilitado = '0';

		//buscar DN de Venda da APL aprovada
		$db->query("SELECT apl.dn_venda, (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS apl_status FROM gelic_licitacoes_apl AS apl WHERE apl.id_item = $pId_item HAVING apl_status = 2");
		if ($db->nextRecord())
			$dDn_venda = $db->f("dn_venda");

		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens_participantes WHERE id_item = $pId_item AND deletado = 0");
		$db->nextRecord();
		if ($db->f("total") == 0)
			$dVencedor = '1';
		else
			$dVencedor = '0';

		$vTitle = 'Adicionar Participante';
		$vSave = "Adicionar";
	}

	$oOutput = '<div class="ultimate-row mt-10">
		<a class="bt-style-4 fl ml-10" href="javascript:void(0);" onclick="atualizarParticipantes('.$pId_item.');" style="width:100px;padding:0;">&lt; Voltar</a>
		<span class="pt-edit-title">'.$vTitle.'</span>
	</div>
	<form id="form-participante">
		<input type="hidden" name="f-id" value="'.$dId.'">
		<input type="hidden" name="f-id-item" value="'.$pId_item.'">
		<div class="ultimate-row mt-40" style="height: 40px;">
			<span class="fl ml-140 w-140 lh-34">Razão Social <a class="t10 bold red">*</a></span>
			<input id="i-razao" class="iText fl" type="text" placeholder="- razão social -" name="f-razao" maxlength="100" value="'.$dRazao_social.'" style="width: 500px;">
		</div>
		<div class="ultimate-row" style="height: 40px;">
			<span class="fl ml-140 w-140 lh-34">CNPJ <a class="t10 bold red">*</a></span>
			<input id="i-cnpj" class="iText fl" type="text" placeholder="- CNPJ -" name="f-cnpj" maxlength="18" value="'.$dCnpj.'" style="width: 300px;">
		</div>
		<div class="ultimate-row" style="height: 40px;">
			<span class="fl ml-140 w-140 lh-34">Valor Final <a class="t10 bold red">*</a></span>
			<input id="i-valor-final" class="iText fl" type="text" placeholder="R$  0,00" name="f-valor-final" maxlength="24" value="'.$dValor_final.'" style="width: 300px;">
		</div>
		<div class="ultimate-row" style="height: 40px;">
			<span class="fl ml-140 w-140 lh-34">Fabricante</span>
			<input id="i-fabricante" class="iText fl" type="text" placeholder="- fabricante -" name="f-fabricante" maxlength="60" value="'.$dFabricante.'" style="width: 300px;">
		</div>
		<div class="ultimate-row" style="height: 40px;">
			<span class="fl ml-140 w-140 lh-34">Modelo</span>
			<input id="i-modelo" class="iText fl" type="text" placeholder="- modelo -" name="f-modelo" maxlength="60" value="'.$dModelo.'" style="width: 300px;">
		</div>
		<div class="ultimate-row" style="height: 40px;">
			<span class="fl ml-140 w-140 lh-34">DN Venda</span>
			<input id="i-dn-venda" class="iText fl" type="text" placeholder="- DN venda -" name="f-dn-venda" maxlength="4" value="'.$dDn_venda.'" style="width: 300px;">
		</div>
	</form>
	<div class="ultimate-row" style="height: 40px;">
		<a id="i-vencedor" class="t11 check-chk'.$dVencedor.' abs" href="javascript:void(0);" onclick="ckSelf(this);" style="display: inline-block; left: 280px; top: 10px;">Vencedor</a>
		<a id="i-inabilitado" class="t11 check-chk'.$dInabilitado.' abs" href="javascript:void(0);" onclick="ckSelf(this);" style="display: inline-block; left: 380px; top: 10px;">Inabilitado</a>
	</div>
	<div id="ultimate-error" class="ml-280" style="width: 500px;"></div>
	<div id="i-salvar-box" class="ultimate-row mt-20 mb-20"><a class="bt-style-1 fl ml-280" href="javascript:void(0);" onclick="salvarParticipante();">'.$vSave.'</a></div>
	<div id="i-processando-box" style="display: none; position: relative; height: 28px; margin-top: 20px; margin-bottom: 20px;">
		<div class="processando">Salvando...</div>
	</div>';

	$aReturn[0] = 1;
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
