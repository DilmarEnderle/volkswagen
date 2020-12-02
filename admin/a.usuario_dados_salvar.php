<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("usr_info_editar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pNome = utf8_decode(trim($_POST["f-nome"]));
	$pLogin = strtolower(trim($_POST["f-login"]));
	$pSenha = strtolower(trim($_POST["f-senha"]));
	$pEmail = strtolower(trim($_POST["f-email"]));
	$pCelular = trim($_POST["f-celular"]);
	$pNotificacoes = intval($_POST["f-notificacoes"]);
	$pNt_email = trim($_POST["f-eml"]);
	$pNt_celular = trim($_POST["f-sms"]);
	$now = date("Y-m-d H:i:s");

	$db = new Mysql();
		
	//verificar se ja existe outro usuario com o mesmo login
	$db->query("SELECT id FROM gelic_admin_usuarios WHERE login = '$pLogin' AND id <> $sInside_id");
	if ($db->nextRecord())
	{
		$aReturn[0] = 2; //login ja existente
		echo json_encode($aReturn);
		exit;
	}


	// =========================================================================
	// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
	// =========================================================================
	$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_admin_usuarios WHERE id = $sInside_id");
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
		$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $sInside_id, 16, 2, 0, '$now', '$info', '', '')");
		//------------------------------------------------------
	}
	// =========================================================================


	if (strlen($pSenha) > 0)
		$db->query("UPDATE gelic_admin_usuarios SET nome = '$pNome', login = '$pLogin', senha = '$pSenha', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $sInside_id");
	else
		$db->query("UPDATE gelic_admin_usuarios SET nome = '$pNome', login = '$pLogin', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $sInside_id");

	$aReturn[0] = 1; //sucesso
} 
echo json_encode($aReturn);

?>
