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

	//POST VALUES
	$pId = intval($_POST["f-id"]);
	$pNome = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-perfil-nome"])))));
	$pIp = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-perfil-ip"])))));
	$pAcesso = "";

	if (intval($_POST["f-lic_visualizar"]) > 0) $pAcesso .= " lic_visualizar";
	if (intval($_POST["f-lic_inserir"]) > 0) $pAcesso .= " lic_inserir";
	if (intval($_POST["f-lic_editar"]) > 0) $pAcesso .= " lic_editar";
	if (intval($_POST["f-lic_excluir"]) > 0) $pAcesso .= " lic_excluir";
	if (intval($_POST["f-lic_encerrar"]) > 0) $pAcesso .= " lic_encerrar";
	if (intval($_POST["f-lic_reverter_encerramento"]) > 0) $pAcesso .= " lic_reverter_encerramento";
	if (intval($_POST["f-lic_prorrogar"]) > 0) $pAcesso .= " lic_prorrogar";
	if (intval($_POST["f-lic_reverter_desinteresse"]) > 0) $pAcesso .= " lic_reverter_desinteresse";
	if (intval($_POST["f-lic_status"]) > 0) $pAcesso .= " lic_status";
	if (intval($_POST["f-lic_mensagem"]) > 0) $pAcesso .= " lic_mensagem";
	if (intval($_POST["f-lic_check"]) > 0) $pAcesso .= " lic_check";

	if (intval($_POST["f-cli_visualizar"]) > 0) $pAcesso .= " cli_visualizar";
	if (intval($_POST["f-cli_inserir"]) > 0) $pAcesso .= " cli_inserir";
	if (intval($_POST["f-cli_editar"]) > 0) $pAcesso .= " cli_editar";
	if (intval($_POST["f-cli_excluir"]) > 0) $pAcesso .= " cli_excluir";
	if (intval($_POST["f-cli_acessar_ambiente"]) > 0) $pAcesso .= " cli_acessar_ambiente";
	if (intval($_POST["f-cli_enviar_senha"]) > 0) $pAcesso .= " cli_enviar_senha";

	if (intval($_POST["f-his_visualizar"]) > 0) $pAcesso .= " his_visualizar";
	if (intval($_POST["f-his_inserir"]) > 0) $pAcesso .= " his_inserir";

	if (intval($_POST["f-doc_visualizar"]) > 0) $pAcesso .= " doc_visualizar";
	if (intval($_POST["f-doc_inserir"]) > 0) $pAcesso .= " doc_inserir";
	if (intval($_POST["f-doc_editar"]) > 0) $pAcesso .= " doc_editar";
	if (intval($_POST["f-doc_excluir"]) > 0) $pAcesso .= " doc_excluir";

	if (intval($_POST["f-usr_visualizar"]) > 0) $pAcesso .= " usr_visualizar";
	if (intval($_POST["f-usr_inserir"]) > 0) $pAcesso .= " usr_inserir";
	if (intval($_POST["f-usr_editar"]) > 0) $pAcesso .= " usr_editar";
	if (intval($_POST["f-usr_excluir"]) > 0) $pAcesso .= " usr_excluir";

	if (intval($_POST["f-usr_info_visualizar"]) > 0) $pAcesso .= " usr_info_visualizar";
	if (intval($_POST["f-usr_info_editar"]) > 0) $pAcesso .= " usr_info_editar";

	if (intval($_POST["f-fer_visualizar"]) > 0) $pAcesso .= " fer_visualizar";
	if (intval($_POST["f-fer_inserir"]) > 0) $pAcesso .= " fer_inserir";
	if (intval($_POST["f-fer_editar"]) > 0) $pAcesso .= " fer_editar";
	if (intval($_POST["f-fer_excluir"]) > 0) $pAcesso .= " fer_excluir";

	if (intval($_POST["f-mod_visualizar"]) > 0) $pAcesso .= " mod_visualizar";
	if (intval($_POST["f-mod_inserir"]) > 0) $pAcesso .= " mod_inserir";
	if (intval($_POST["f-mod_editar"]) > 0) $pAcesso .= " mod_editar";
	if (intval($_POST["f-mod_excluir"]) > 0) $pAcesso .= " mod_excluir";

	if (intval($_POST["f-cid_visualizar"]) > 0) $pAcesso .= " cid_visualizar";
	if (intval($_POST["f-cid_inserir"]) > 0) $pAcesso .= " cid_inserir";
	if (intval($_POST["f-cid_editar"]) > 0) $pAcesso .= " cid_editar";
	if (intval($_POST["f-cid_excluir"]) > 0) $pAcesso .= " cid_excluir";

	if (intval($_POST["f-mot_visualizar"]) > 0) $pAcesso .= " mot_visualizar";
	if (intval($_POST["f-mot_inserir"]) > 0) $pAcesso .= " mot_inserir";
	if (intval($_POST["f-mot_editar"]) > 0) $pAcesso .= " mot_editar";
	if (intval($_POST["f-mot_excluir"]) > 0) $pAcesso .= " mot_excluir";

	if (intval($_POST["f-sta_visualizar"]) > 0) $pAcesso .= " sta_visualizar";
	if (intval($_POST["f-sta_inserir"]) > 0) $pAcesso .= " sta_inserir";
	if (intval($_POST["f-sta_editar"]) > 0) $pAcesso .= " sta_editar";
	if (intval($_POST["f-sta_excluir"]) > 0) $pAcesso .= " sta_excluir";

	if (intval($_POST["f-cd_visualizar"]) > 0) $pAcesso .= " cd_visualizar";
	if (intval($_POST["f-cd_autorizar"]) > 0) $pAcesso .= " cd_autorizar";
	if (intval($_POST["f-cd_mensagem"]) > 0) $pAcesso .= " cd_mensagem";

	if (intval($_POST["f-atarp_visualizar"]) > 0) $pAcesso .= " atarp_visualizar";
	if (intval($_POST["f-atarp_inserir"]) > 0) $pAcesso .= " atarp_inserir";
	if (intval($_POST["f-atarp_editar"]) > 0) $pAcesso .= " atarp_editar";
	if (intval($_POST["f-atarp_excluir"]) > 0) $pAcesso .= " atarp_excluir";

	if (intval($_POST["f-rel_1_1"]) > 0) $pAcesso .= " rel_1_1";
	if (intval($_POST["f-rel_1_2"]) > 0) $pAcesso .= " rel_1_2";
	if (intval($_POST["f-rel_1_3"]) > 0) $pAcesso .= " rel_1_3";
	if (intval($_POST["f-rel_1_4"]) > 0) $pAcesso .= " rel_1_4";
	if (intval($_POST["f-rel_2_1"]) > 0) $pAcesso .= " rel_2_1";
	if (intval($_POST["f-rel_2_2"]) > 0) $pAcesso .= " rel_2_2";
	if (intval($_POST["f-rel_2_3"]) > 0) $pAcesso .= " rel_2_3";
	if (intval($_POST["f-rel_2_4"]) > 0) $pAcesso .= " rel_2_4";
	if (intval($_POST["f-rel_3_1"]) > 0) $pAcesso .= " rel_3_1";
	if (intval($_POST["f-rel_4_1"]) > 0) $pAcesso .= " rel_4_1";
	if (intval($_POST["f-rel_4_2"]) > 0) $pAcesso .= " rel_4_2";
	if (intval($_POST["f-rel_4_3"]) > 0) $pAcesso .= " rel_4_3";
	if (intval($_POST["f-rel_4_4"]) > 0) $pAcesso .= " rel_4_4";
	if (intval($_POST["f-rel_4_5"]) > 0) $pAcesso .= " rel_4_5";
	if (intval($_POST["f-rel_5_1"]) > 0) $pAcesso .= " rel_5_1";
	if (intval($_POST["f-rel_6_1"]) > 0) $pAcesso .= " rel_6_1";
	if (intval($_POST["f-rel_6_2"]) > 0) $pAcesso .= " rel_6_2";
	if (intval($_POST["f-rel_6_3"]) > 0) $pAcesso .= " rel_6_3";

	if (intval($_POST["f-bib_visualizar"]) > 0) $pAcesso .= " bib_visualizar";
	if (intval($_POST["f-bib_inserir"]) > 0) $pAcesso .= " bib_inserir";
	if (intval($_POST["f-bib_editar"]) > 0) $pAcesso .= " bib_editar";
	if (intval($_POST["f-bib_excluir"]) > 0) $pAcesso .= " bib_excluir";

	if (intval($_POST["f-msg_visualizar"]) > 0) $pAcesso .= " msg_visualizar";
	if (intval($_POST["f-msg_reenviar"]) > 0) $pAcesso .= " msg_reenviar";
	if (intval($_POST["f-msg_excluir"]) > 0) $pAcesso .= " msg_excluir";

	if (intval($_POST["f-cnf_visualizar"]) > 0) $pAcesso .= " cnf_visualizar";
	if (intval($_POST["f-cnf_editar"]) > 0) $pAcesso .= " cnf_editar";

	$pAcesso = trim($pAcesso);

	if ($pId == 0)
	{
		//INSERIR

		//verificar se o nome ja existe
		$db->query("SELECT id FROM gelic_admin_usuarios_perfis WHERE nome = '$pNome'");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //nome ja existe
			echo json_encode($aReturn);
			exit;
		}

		$db->query("INSERT INTO gelic_admin_usuarios_perfis VALUES (NULL, '$pNome', '$pAcesso', '$pIp')");
		$aReturn[0] = 1; //sucesso
	}
	else
	{
		//SALVAR

		//verificar se o nome ja existe
		$db->query("SELECT id FROM gelic_admin_usuarios_perfis WHERE id <> $pId AND nome = '$pNome'");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //nome ja existe
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_admin_usuarios_perfis SET nome = '$pNome', acesso = '$pAcesso', ip = '$pIp' WHERE id = $pId");
		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
