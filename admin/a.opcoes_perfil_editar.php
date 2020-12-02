<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cnf_visualizar", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();
	$pId = intval($_POST["id"]);
	$db->query("SELECT * FROM gelic_admin_usuarios_perfis WHERE id = $pId");
	if ($db->nextRecord())
	{
		$dAcesso = explode(" ", $db->f("acesso")); //string de acessos
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = array();
		$aAcessos = array("lic_visualizar","lic_inserir","lic_editar","lic_excluir","lic_encerrar","lic_reverter_encerramento","lic_prorrogar","lic_reverter_desinteresse","lic_status","lic_mensagem","lic_check","cli_visualizar","cli_inserir","cli_editar","cli_excluir","cli_acessar_ambiente","cli_enviar_senha","his_visualizar","his_inserir","doc_visualizar","doc_inserir","doc_editar","doc_excluir","usr_visualizar","usr_inserir","usr_editar","usr_excluir","usr_info_visualizar","usr_info_editar","fer_visualizar","fer_inserir","fer_editar","fer_excluir","mod_visualizar","mod_inserir","mod_editar","mod_excluir","cid_visualizar","cid_inserir","cid_editar","cid_excluir","mot_visualizar","mot_inserir","mot_editar","mot_excluir","sta_visualizar","sta_inserir","sta_editar","sta_excluir","cd_visualizar","cd_autorizar","cd_mensagem","atarp_visualizar","atarp_inserir","atarp_editar","atarp_excluir","bib_visualizar","bib_inserir","bib_editar","bib_excluir","rel_1_1","rel_1_2","rel_1_3","rel_1_4","rel_2_1","rel_2_2","rel_2_3","rel_2_4","rel_3_1","rel_4_1","rel_4_2","rel_4_3","rel_4_4","rel_4_5","rel_5_1","rel_6_1","rel_6_2","rel_6_3","msg_visualizar","msg_reenviar","msg_excluir","cnf_visualizar","cnf_editar");
		for ($i=0; $i<count($aAcessos); $i++)
			$aReturn[1][] = array("acesso"=>$aAcessos[$i], "valor"=>(int)in_array($aAcessos[$i],$dAcesso));

		$aReturn[2] = utf8_encode($db->f("nome"));
		$aReturn[3] = $db->f("ip");
	}
} 
echo json_encode($aReturn);

?>
