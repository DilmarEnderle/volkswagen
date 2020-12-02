<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	$sInside_id = $_SESSION[SESSION_ID];

	$pId = intval($_POST["f-id"]);
	$pNome = utf8_decode(trim($_POST["f-nome"]));
	$pId_perfil = intval($_POST["f-perfil"]);
	$pLogin = strtolower(trim($_POST["f-login"]));
	$pSenha = strtolower(trim($_POST["f-senha"]));
	$pAtivo = intval($_POST["f-ativo"]);
	$pEmail = strtolower(trim($_POST["f-email"]));
	$pCelular = trim($_POST["f-celular"]);
	$pNotificacoes = intval($_POST["f-notificacoes"]);
	$pNt_email = trim($_POST["f-eml"]);
	$pNt_celular = trim($_POST["f-sms"]);
	$now = date("Y-m-d H:i:s");

	$db = new Mysql();
	if ($pId == 0)
	{
		//************ NOVO REGISTRO ***************
		if (!in_array("usr_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}
		
		//verificar se ja existe este usuario
		$db->query("SELECT id FROM gelic_admin_usuarios WHERE login = '$pLogin'");
		if ($db->nextRecord())
		{
			$aReturn[0] = 2; //login ja existente
			echo json_encode($aReturn);
			exit;
		}
		
		$db->query("INSERT INTO gelic_admin_usuarios VALUES (NULL, $pId_perfil, $pAtivo, '$pNome', '$pLogin', '$pSenha', '$pCelular', '$pEmail', $pNotificacoes, '$pNt_celular', '$pNt_email', 0)");
		$dId_admin_usuario = $db->li();


		//------------------------------------------------------
		// inserir historico notificacoes
		//------------------------------------------------------
		$aFr = array("n"=>0, "eml"=>"", "sms"=>"");
		$aTo = array("n"=>$pNotificacoes, "eml"=>$pNt_email, "sms"=>$pNt_celular);
		$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
		$info = json_encode($aInfo);
		$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $dId_admin_usuario, 16, 1, 0, '$now', '$info', '', '')");
		//------------------------------------------------------


		$db->query("SELECT id FROM gelic_abas WHERE tipo = 'F'");
		$db->nextRecord();
		$dTab = $db->f("id");

		$db->query("INSERT INTO gelic_admin_usuarios_config VALUES (NULL, $dId_admin_usuario, 'tab', $dTab)");

		$aReturn[0] = 1; //sucesso
	}
	else
	{
		//************ SALVAR ALTERACOES ***************
		if (!in_array("usr_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}
		
		//verificar se ja existe outro usuario com o mesmo login
		$db->query("SELECT id FROM gelic_admin_usuarios WHERE login = '$pLogin' AND id <> $pId");
		if ($db->nextRecord())
		{
			$aReturn[0] = 2; //login ja existente
			echo json_encode($aReturn);
			exit;
		}


		// =========================================================================
		// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
		// =========================================================================
		$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_admin_usuarios WHERE id = $pId");
		$db->nextRecord();
				
		$orig_n = $db->f("notificacoes");
		$orig_nt_celular = $db->f("nt_celular");
		$orig_nt_email = $db->f("nt_email");

		$novo_n = $pNotificacoes;
		$novo_nt_celular = $pNt_celular;
		$novo_nt_email = $pNt_email;

		if ($novo_n <> $orig_n || $novo_nt_celular <> $orig_nt_celular || $novo_nt_email <> $orig_nt_email)
		{
			//------------------------------------------------------
			// inserir historico notificacoes
			//------------------------------------------------------
			$aFr = array("n"=>$orig_n, "eml"=>$orig_nt_email, "sms"=>$orig_nt_celular);
			$aTo = array("n"=>$novo_n, "eml"=>$novo_nt_email, "sms"=>$novo_nt_celular);
			$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
			$info = json_encode($aInfo);
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $pId, 16, 1, 0, '$now', '$info', '', '')");
			//------------------------------------------------------
		}
		// =========================================================================


		if (strlen($pSenha) > 0)
			$db->query("UPDATE gelic_admin_usuarios SET id_perfil = $pId_perfil, ativo = $pAtivo, nome = '$pNome', login = '$pLogin', senha = '$pSenha', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $pId");
		else
			$db->query("UPDATE gelic_admin_usuarios SET id_perfil = $pId_perfil, ativo = $pAtivo, nome = '$pNome', login = '$pLogin', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $pId");

		$aReturn[0] = 1; //sucesso
	}
} 
echo json_encode($aReturn);

?>
