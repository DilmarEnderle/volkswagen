<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_licitacao = intval($_POST["id_licitacao"]);
	$db = new Mysql();

	//verificar se o status pode ser alterado para esta licitacao
	$db->query("SELECT lic.deletado, (SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo FROM gelic_licitacoes AS lic WHERE lic.id = $pId_licitacao");
	$db->nextRecord();
	if ($db->f("deletado") > 0)
	{
		$aReturn[0] = 9; //nao disponivel
		echo json_encode($aReturn);
		exit;
	}

	if ($db->f("ultimo_tipo") == 31)
	{
		$aReturn[0] = 8; //encerrada
		echo json_encode($aReturn);
		exit;
	}


	//--- grupo/aba/status da licitacao ----
	$aLic_aba_status = array();
	$db->query("
SELECT 
	grupo,
	id_aba,
	id_status,
	status_fixo
FROM
	gelic_licitacoes_abas
WHERE
	id_licitacao = $pId_licitacao"); 
	while ($db->nextRecord())
		$aLic_aba_status[$db->f("grupo")] = array("id_aba"=>$db->f("id_aba"),"id_status"=>$db->f("id_status"),"status_fixo"=>$db->f("status_fixo"));


	//--- situacoes de status ---
	$aStatus = array();
	$db->query("SELECT id, descricao FROM gelic_status ORDER BY descricao");
	while ($db->nextRecord())
		$aStatus[] = array("id"=>$db->f("id"),"descricao"=>utf8_encode($db->f("descricao")));


	//--- abas ---
	$aAbas = array();
	$db->query("SELECT id, nome, tipo FROM gelic_abas WHERE tipo <> 'C' ORDER BY ordem");
	while ($db->nextRecord())
		$aAbas[] = array("tipo"=>$db->f("tipo"),"id"=>$db->f("id"),"nome"=>utf8_encode($db->f("nome")));




	// ADMIN
	$tStatus_admin = '<select id="i-status-admin" class="iText fl" style="width: 400px; height: 34px;">';
	for ($i=0; $i<count($aStatus); $i++)
	{
		if ($aLic_aba_status[1]["id_status"] == $aStatus[$i]["id"])
			$tStatus_admin .= '<option value="'.$aStatus[$i]["id"].'" selected="selected">'.$aStatus[$i]["descricao"].'</option>';
		else
			$tStatus_admin .= '<option value="'.$aStatus[$i]["id"].'">'.$aStatus[$i]["descricao"].'</option>';
	}
	$tStatus_admin .= '</select>';

	$tAba_admin = '<select id="i-aba-admin" class="iText fr" style="width: 268px; height: 34px; margin-left: 4px;">';
	for ($i=0; $i<count($aAbas); $i++)
	{
		if ($aLic_aba_status[1]["id_aba"] == $aAbas[$i]["id"])
			$tAba_admin .= '<option value="'.$aAbas[$i]["id"].'" selected="selected">'.$aAbas[$i]["nome"].'</option>';
		else
			$tAba_admin .= '<option value="'.$aAbas[$i]["id"].'">'.$aAbas[$i]["nome"].'</option>';
	}
	$tAba_admin .= '</select>';


	// BO
	$tStatus_bo = '<select id="i-status-bo" class="iText fl" style="width: 400px; height: 34px;">';
	for ($i=0; $i<count($aStatus); $i++)
	{
		if ($aLic_aba_status[2]["id_status"] == $aStatus[$i]["id"])
			$tStatus_bo .= '<option value="'.$aStatus[$i]["id"].'" selected="selected">'.$aStatus[$i]["descricao"].'</option>';
		else
			$tStatus_bo .= '<option value="'.$aStatus[$i]["id"].'">'.$aStatus[$i]["descricao"].'</option>';
	}
	$tStatus_bo .= '</select>';

	$tAba_bo = '<select id="i-aba-bo" class="iText fr" style="width: 268px; height: 34px; margin-left: 4px;">';
	for ($i=0; $i<count($aAbas); $i++)
	{
		if ($aAbas[$i]["tipo"] == 'A') continue;
		if ($aLic_aba_status[2]["id_aba"] == $aAbas[$i]["id"])
			$tAba_bo .= '<option value="'.$aAbas[$i]["id"].'" selected="selected">'.$aAbas[$i]["nome"].'</option>';
		else
			$tAba_bo .= '<option value="'.$aAbas[$i]["id"].'">'.$aAbas[$i]["nome"].'</option>';
	}
	$tAba_bo .= '</select>';


	// DN COM APL
	$tStatus_dn_apl = '<select id="i-status-dn-apl" class="iText fl" style="width: 400px; height: 34px;">';
	for ($i=0; $i<count($aStatus); $i++)
	{
		if ($aLic_aba_status[3]["id_status"] == $aStatus[$i]["id"])
			$tStatus_dn_apl .= '<option value="'.$aStatus[$i]["id"].'" selected="selected">'.$aStatus[$i]["descricao"].'</option>';
		else
			$tStatus_dn_apl .= '<option value="'.$aStatus[$i]["id"].'">'.$aStatus[$i]["descricao"].'</option>';
	}
	$tStatus_dn_apl .= '</select>';

	$tAba_dn_apl = '<select id="i-aba-dn-apl" class="iText fr" style="width: 268px; height: 34px; margin-left: 4px;">';
	for ($i=0; $i<count($aAbas); $i++)
	{
		if ($aAbas[$i]["tipo"] == 'A') continue;
		if ($aLic_aba_status[3]["id_aba"] == $aAbas[$i]["id"])
			$tAba_dn_apl .= '<option value="'.$aAbas[$i]["id"].'" selected="selected">'.$aAbas[$i]["nome"].'</option>';
		else
			$tAba_dn_apl .= '<option value="'.$aAbas[$i]["id"].'">'.$aAbas[$i]["nome"].'</option>';
	}
	$tAba_dn_apl .= '</select>';


	// DN OUTROS
	$tStatus_dn_outros = '<select id="i-status-dn-outros" class="iText fl" style="width: 400px; height: 34px;">';
	for ($i=0; $i<count($aStatus); $i++)
	{
		if ($aLic_aba_status[4]["id_status"] == $aStatus[$i]["id"])
			$tStatus_dn_outros .= '<option value="'.$aStatus[$i]["id"].'" selected="selected">'.$aStatus[$i]["descricao"].'</option>';
		else
			$tStatus_dn_outros .= '<option value="'.$aStatus[$i]["id"].'">'.$aStatus[$i]["descricao"].'</option>';
	}
	$tStatus_dn_outros .= '</select>';

	$tAba_dn_outros = '<select id="i-aba-dn-outros" class="iText fr" style="width: 268px; height: 34px; margin-left: 4px;">';
	for ($i=0; $i<count($aAbas); $i++)
	{
		if ($aAbas[$i]["tipo"] == 'A') continue;
		if ($aLic_aba_status[4]["id_aba"] == $aAbas[$i]["id"])
			$tAba_dn_outros .= '<option value="'.$aAbas[$i]["id"].'" selected="selected">'.$aAbas[$i]["nome"].'</option>';
		else
			$tAba_dn_outros .= '<option value="'.$aAbas[$i]["id"].'">'.$aAbas[$i]["nome"].'</option>';
	}
	$tAba_dn_outros .= '</select>';




	$oOutput = '
		<div class="ultimate-row red" style="line-height: 23px;">Atenção: A alteração do status não terá efeito para DNs sem interesse em participar da licitação.</div>

		<div class="ultimate-row bold" style="margin-top: 14px; line-height: 23px;">Administração</div>
		<div class="ultimate-row">
			<span class="fl" style="width: 400px; line-height: 23px;">Status</span>
			<span class="fr" style="width: 268px; line-height: 23px;">Aba</span>
		</div>
		<div class="ultimate-row">'.$tStatus_admin.$tAba_admin.'</div>
		<div class="ultimate-row"><a id="i-fixo-admin" class="fl check-chk'.$aLic_aba_status[1]["status_fixo"].'" href="javascript:void(0);" onclick="ckSelf(this);">Não permitir o sistema alterar o status automaticamente.</a></div>

		<div class="ultimate-row bold" style="margin-top: 30px; line-height: 23px;">Back Office</div>
		<div class="ultimate-row">
			<span class="fl" style="width: 400px; line-height: 23px;">Status</span>
			<span class="fr" style="width: 268px; line-height: 23px;">Aba</span>
		</div>
		<div class="ultimate-row">'.$tStatus_bo.$tAba_bo.'</div>
		<div class="ultimate-row"><a id="i-fixo-bo" class="fl check-chk'.$aLic_aba_status[2]["status_fixo"].'" href="javascript:void(0);" onclick="ckSelf(this);">Não permitir o sistema alterar o status automaticamente.</a></div>

		<div class="ultimate-row bold" style="margin-top: 30px; line-height: 23px;">DNs com APL</div>
		<div class="ultimate-row">
			<span class="fl" style="width: 400px; line-height: 23px;">Status</span>
			<span class="fr" style="width: 268px; line-height: 23px;">Aba</span>
		</div>
		<div class="ultimate-row">'.$tStatus_dn_apl.$tAba_dn_apl.'</div>
		<div class="ultimate-row"><a id="i-fixo-dn-apl" class="fl check-chk'.$aLic_aba_status[3]["status_fixo"].'" href="javascript:void(0);" onclick="ckSelf(this);">Não permitir o sistema alterar o status automaticamente.</a></div>

		<div class="ultimate-row bold" style="margin-top: 30px; line-height: 23px;">Outros DNs</div>
		<div class="ultimate-row">
			<span class="fl" style="width: 400px; line-height: 23px;">Status</span>
			<span class="fr" style="width: 268px; line-height: 23px;">Aba</span>
		</div>
		<div class="ultimate-row">'.$tStatus_dn_outros.$tAba_dn_outros.'</div>
		<div class="ultimate-row"><a id="i-fixo-dn-outros" class="fl check-chk'.$aLic_aba_status[4]["status_fixo"].'" href="javascript:void(0);" onclick="ckSelf(this);">Não permitir o sistema alterar o status automaticamente.</a></div>

		<div id="ultimate-error"></div>';
		
	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
