<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_status", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pId_licitacao = intval($_POST["f-id-licitacao"]);

	$pId_status_admin = intval($_POST["f-id-status-admin"]);
	$pId_status_bo = intval($_POST["f-id-status-bo"]);
	$pId_status_dn_apl = intval($_POST["f-id-status-dn-apl"]);
	$pId_status_dn_outros = intval($_POST["f-id-status-dn-outros"]);

	$pFixo_admin = intval($_POST["f-fixo-admin"]);
	$pFixo_bo = intval($_POST["f-fixo-bo"]);
	$pFixo_dn_apl = intval($_POST["f-fixo-dn-apl"]);
	$pFixo_dn_outros = intval($_POST["f-fixo-dn-outros"]);

	$pId_aba_admin = intval($_POST["f-id-aba-admin"]);
	$pId_aba_bo = intval($_POST["f-id-aba-bo"]);
	$pId_aba_dn_apl = intval($_POST["f-id-aba-dn-apl"]);
	$pId_aba_dn_outros = intval($_POST["f-id-aba-dn-outros"]);

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

	//Buscar valores originais
	$aLic_aba_status = array("fr"=>array(),"to"=>array());
	$db->query("
SELECT 
	grupo,
	id_aba,
	id_status,
	status_fixo
FROM
	gelic_licitacoes_abas
WHERE
	id_licitacao = $pId_licitacao
ORDER BY
	grupo");
	while ($db->nextRecord())
		$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));


	if ($pId_status_admin == $aLic_aba_status["fr"][0]["status"] && 
		$pFixo_admin == $aLic_aba_status["fr"][0]["fixo"] &&
		$pId_aba_admin == $aLic_aba_status["fr"][0]["aba"] &&
		$pId_status_bo == $aLic_aba_status["fr"][1]["status"] && 
		$pFixo_bo == $aLic_aba_status["fr"][1]["fixo"] &&
		$pId_aba_bo == $aLic_aba_status["fr"][1]["aba"] &&
		$pId_status_dn_apl == $aLic_aba_status["fr"][2]["status"] && 
		$pFixo_dn_apl == $aLic_aba_status["fr"][2]["fixo"] &&
		$pId_aba_dn_apl == $aLic_aba_status["fr"][2]["aba"] &&
		$pId_status_dn_outros == $aLic_aba_status["fr"][3]["status"] && 
		$pFixo_dn_outros == $aLic_aba_status["fr"][3]["fixo"] &&
		$pId_aba_dn_outros == $aLic_aba_status["fr"][3]["aba"])
	{
		$aReturn[0] = 7; //no change
	}
	else
	{
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_admin, id_status = $pId_status_admin, status_fixo = $pFixo_admin WHERE id_licitacao = $pId_licitacao AND grupo = 1");
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_bo, id_status = $pId_status_bo, status_fixo = $pFixo_bo WHERE id_licitacao = $pId_licitacao AND grupo = 2");
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_dn_apl, id_status = $pId_status_dn_apl, status_fixo = $pFixo_dn_apl WHERE id_licitacao = $pId_licitacao AND grupo = 3");
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_dn_outros, id_status = $pId_status_dn_outros, status_fixo = $pFixo_dn_outros WHERE id_licitacao = $pId_licitacao AND grupo = 4");

		$aLic_aba_status["to"][] = array("grupo"=>1, "aba"=>$pId_aba_admin, "status"=>$pId_status_admin, "fixo"=>$pFixo_admin);
		$aLic_aba_status["to"][] = array("grupo"=>2, "aba"=>$pId_aba_bo, "status"=>$pId_status_bo, "fixo"=>$pFixo_bo);
		$aLic_aba_status["to"][] = array("grupo"=>3, "aba"=>$pId_aba_dn_apl, "status"=>$pId_status_dn_apl, "fixo"=>$pFixo_dn_apl);
		$aLic_aba_status["to"][] = array("grupo"=>4, "aba"=>$pId_aba_dn_outros, "status"=>$pId_status_dn_outros, "fixo"=>$pFixo_dn_outros);

		//INSERIR NO HISTORICO
		$now = date("Y-m-d H:i:s");
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 36, 0, 0, '$now', '".json_encode($aLic_aba_status)."', '', '')");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
