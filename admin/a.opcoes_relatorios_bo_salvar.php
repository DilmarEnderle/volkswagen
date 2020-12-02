<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cnf_editar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pAcesso = "";
	if (intval($_POST["f-bo_rel_1_1"]) > 0) $pAcesso .= " rel_1_1";
	if (intval($_POST["f-bo_rel_1_2"]) > 0) $pAcesso .= " rel_1_2";
	if (intval($_POST["f-bo_rel_1_3"]) > 0) $pAcesso .= " rel_1_3";
	if (intval($_POST["f-bo_rel_1_4"]) > 0) $pAcesso .= " rel_1_4";
	if (intval($_POST["f-bo_rel_2_1"]) > 0) $pAcesso .= " rel_2_1";
	if (intval($_POST["f-bo_rel_2_2"]) > 0) $pAcesso .= " rel_2_2";
	if (intval($_POST["f-bo_rel_2_3"]) > 0) $pAcesso .= " rel_2_3";
	if (intval($_POST["f-bo_rel_2_4"]) > 0) $pAcesso .= " rel_2_4";
	if (intval($_POST["f-bo_rel_3_1"]) > 0) $pAcesso .= " rel_3_1";
	if (intval($_POST["f-bo_rel_4_1"]) > 0) $pAcesso .= " rel_4_1";
	if (intval($_POST["f-bo_rel_4_2"]) > 0) $pAcesso .= " rel_4_2";
	if (intval($_POST["f-bo_rel_4_3"]) > 0) $pAcesso .= " rel_4_3";
	if (intval($_POST["f-bo_rel_4_4"]) > 0) $pAcesso .= " rel_4_4";
	if (intval($_POST["f-bo_rel_5_1"]) > 0) $pAcesso .= " rel_5_1";
	if (intval($_POST["f-bo_rel_6_1"]) > 0) $pAcesso .= " rel_6_1";
	if (intval($_POST["f-bo_rel_6_2"]) > 0) $pAcesso .= " rel_6_2";
	if (intval($_POST["f-bo_rel_6_3"]) > 0) $pAcesso .= " rel_6_3";
	$pAcesso = trim($pAcesso);

	$db->query("UPDATE gelic_clientes_config SET valor = '$pAcesso' WHERE id_cliente = 0 AND config = 'relatorios'");
	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
