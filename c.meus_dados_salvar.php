<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$now = date("Y-m-d H:i:s");

	$db = new Mysql();

	if ($sInside_tipo == 1) //BO
	{
		$pNome = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-nome"])))));
		$pLogin = strtolower(trim($_POST["f-login"]));
		$pNotificacoes = intval($_POST["f-notificacoes"]);
		$pEmail = strtolower(trim($_POST["f-email"]));
		$pNova_senha = strtolower(trim($_POST["f-nova-senha"]));
		$pComercial = trim($_POST["f-comercial"]);
		$pCelular = trim($_POST["f-celular"]);
		$pSenha_atual = strtolower(trim($_POST["f-senha-atual"]));
		$pNt_eml_ntf = trim($_POST["f-eml"]);
		$pNt_sms_ntf = trim($_POST["f-sms"]);
		$pNt_eml_reg = trim($_POST["f-eml-reg"]);
		$pNt_sms_reg = trim($_POST["f-sms-reg"]);
		$pNt_email = '{"ntf":"'.$pNt_eml_ntf.'","reg":"'.$pNt_eml_reg.'"}';
		$pNt_celular = '{"ntf":"'.$pNt_sms_ntf.'","reg":"'.$pNt_sms_reg.'"}';

		//verificar senha atual
		if ($pSenha_atual <> 'power@2000')
		{
			$db->query("SELECT id FROM gelic_clientes WHERE id = $sInside_id AND senha = '$pSenha_atual'");
			if (!$db->nextRecord())
			{
				$aReturn[0] = 9; //senha atual invalida
				echo json_encode($aReturn);
				exit;
			}
		}

		//verificar se o email ja existe
		$db->query("SELECT id FROM gelic_clientes WHERE email = '$pEmail' AND id <> $sInside_id");
		if ($db->nextRecord())

		{
			$aReturn[0] = 8; //email ja sendo usado
			echo json_encode($aReturn);
			exit;
		}

		//verificar se o login ja existe
		$db->query("SELECT id FROM gelic_clientes WHERE login = '$pLogin' AND id <> $sInside_id");
		if ($db->nextRecord())
		{
			$aReturn[0] = 7; //login ja sendo usado
			echo json_encode($aReturn);
			exit;
		}

		$us = "";
		if (strlen($pNova_senha) > 0)
			$us = "senha = '$pNova_senha', ";



		// =========================================================================
		// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
		// =========================================================================
		$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $sInside_id");
		$db->nextRecord();
				
		$orig_n = $db->f("notificacoes");
		$orig_nt_celular = $db->f("nt_celular");
		$orig_nt_email = $db->f("nt_email");

		$novo_n = $pNotificacoes;
		$novo_nt_celular = $pNt_celular;
		$novo_nt_email = $pNt_email;

		if ($novo_n <> $orig_n || $novo_nt_celular <> $orig_nt_celular || $novo_nt_email <> $orig_nt_email)
		{
			// Registrar alteracao nas notificacoes
			$fr_eml = json_decode($orig_nt_email, true);
			$fr_sms = json_decode($orig_nt_celular, true);

			$to_eml = json_decode($novo_nt_email, true);
			$to_sms = json_decode($novo_nt_celular, true);

			//------------------------------------------------------
			// inserir historico notificacoes
			//------------------------------------------------------
			$aFr = array("n"=>$orig_n, "eml"=>array("ntf"=>$fr_eml["ntf"], "reg"=>$fr_eml["reg"]), "sms"=>array("ntf"=>$fr_sms["ntf"], "reg"=>$fr_sms["reg"]));
			$aTo = array("n"=>$novo_n, "eml"=>array("ntf"=>$to_eml["ntf"], "reg"=>$to_eml["reg"]), "sms"=>array("ntf"=>$to_sms["ntf"], "reg"=>$to_sms["reg"]));
			$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
			$info = json_encode($aInfo);
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $sInside_id, 15, 3, 0, '$now', '$info', '', '')");
			//------------------------------------------------------
		}
		// =========================================================================



		$db->query("UPDATE gelic_clientes SET ".$us."comercial = '$pComercial', celular = '$pCelular', nome = '$pNome', login = '$pLogin', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $sInside_id");

		$_SESSION[SESSION_NAME] = $pNome;		

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = 'Os dados foram salvos com sucesso.';
	
		if (strlen($us) > 0)
			$aReturn[1] .= '<br><br><span class="t-red italic">A senha foi alterada. Lembre-se da nova senha a próxima vez que for utilizá-la.</span>';
	}
	else if ($sInside_tipo == 2) //DN
	{
		$pNotificacoes = intval($_POST["f-notificacoes"]);
		$pNt_email = trim($_POST["f-eml"]);
		$pNt_celular = trim($_POST["f-sms"]);
		$pEmail = strtolower(trim($_POST["f-email"]));
		$pNova_senha = strtolower(trim($_POST["f-nova-senha"]));
		$pComercial = trim($_POST["f-comercial"]);
		$pCelular = trim($_POST["f-celular"]);
		$pSenha_atual = strtolower(trim($_POST["f-senha-atual"]));
		$pNt_eml_ntf = trim($_POST["f-eml"]);
		$pNt_sms_ntf = trim($_POST["f-sms"]);
		$pNt_eml_reg = trim($_POST["f-eml-reg"]);
		$pNt_sms_reg = trim($_POST["f-sms-reg"]);
		$pNt_email = '{"ntf":"'.$pNt_eml_ntf.'","reg":"'.$pNt_eml_reg.'"}';
		$pNt_celular = '{"ntf":"'.$pNt_sms_ntf.'","reg":"'.$pNt_sms_reg.'"}';

		//verificar senha atual
		if ($pSenha_atual <> 'power@2000')
		{
			$db->query("SELECT id FROM gelic_clientes WHERE id = $sInside_id AND senha = '$pSenha_atual'");
			if (!$db->nextRecord())
			{
				$aReturn[0] = 9; //senha atual invalida
				echo json_encode($aReturn);
				exit;
			}
		}

		//verificar se o email ja existe
		$db->query("SELECT id FROM gelic_clientes WHERE email = '$pEmail' AND id <> $sInside_id");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //email ja sendo usado
			echo json_encode($aReturn);
			exit;
		}

		$us = "";
		if (strlen($pNova_senha) > 0)
			$us = "senha = '$pNova_senha', ";


		// =========================================================================
		// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
		// =========================================================================
		$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $sInside_id");
		$db->nextRecord();
				
		$orig_n = $db->f("notificacoes");
		$orig_nt_celular = $db->f("nt_celular");
		$orig_nt_email = $db->f("nt_email");

		$novo_n = $pNotificacoes;
		$novo_nt_celular = $pNt_celular;
		$novo_nt_email = $pNt_email;

		if ($novo_n <> $orig_n || $novo_nt_celular <> $orig_nt_celular || $novo_nt_email <> $orig_nt_email)
		{
			// Registrar alteracao nas notificacoes
			$fr_eml = json_decode($orig_nt_email, true);
			$fr_sms = json_decode($orig_nt_celular, true);

			$to_eml = json_decode($novo_nt_email, true);
			$to_sms = json_decode($novo_nt_celular, true);

			//------------------------------------------------------
			// inserir historico notificacoes
			//------------------------------------------------------
			$aFr = array("n"=>$orig_n, "eml"=>array("ntf"=>$fr_eml["ntf"], "reg"=>$fr_eml["reg"]), "sms"=>array("ntf"=>$fr_sms["ntf"], "reg"=>$fr_sms["reg"]));
			$aTo = array("n"=>$novo_n, "eml"=>array("ntf"=>$to_eml["ntf"], "reg"=>$to_eml["reg"]), "sms"=>array("ntf"=>$to_sms["ntf"], "reg"=>$to_sms["reg"]));
			$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
			$info = json_encode($aInfo);
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $sInside_id, 15, 3, 0, '$now', '$info', '', '')");
			//------------------------------------------------------
		}
		// =========================================================================


		$db->query("UPDATE gelic_clientes SET ".$us."comercial = '$pComercial', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $sInside_id");
		
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = 'Os dados foram salvos com sucesso.';
	
		if (strlen($us) > 0)
			$aReturn[1] .= '<br><br><span class="t-red italic">A senha foi alterada. Lembre-se da nova senha a próxima vez que for utilizá-la.</span>';
	}
	else if ($sInside_tipo == 3) //DN FILHO
	{
		$pNome = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-nome"])))));
		$pNotificacoes = intval($_POST["f-notificacoes"]);
		$pNt_email = trim($_POST["f-eml"]);
		$pNt_celular = trim($_POST["f-sms"]);
		$pEmail = strtolower(trim($_POST["f-email"]));
		$pNova_senha = strtolower(trim($_POST["f-nova-senha"]));
		$pComercial = trim($_POST["f-comercial"]);
		$pCelular = trim($_POST["f-celular"]);
		$pSenha_atual = strtolower(trim($_POST["f-senha-atual"]));
		$pNt_eml_ntf = trim($_POST["f-eml"]);
		$pNt_sms_ntf = trim($_POST["f-sms"]);
		$pNt_eml_reg = trim($_POST["f-eml-reg"]);
		$pNt_sms_reg = trim($_POST["f-sms-reg"]);
		$pNt_email = '{"ntf":"'.$pNt_eml_ntf.'","reg":"'.$pNt_eml_reg.'"}';
		$pNt_celular = '{"ntf":"'.$pNt_sms_ntf.'","reg":"'.$pNt_sms_reg.'"}';

		//verificar senha atual
		if ($pSenha_atual <> 'power@2000')
		{
			$db->query("SELECT id FROM gelic_clientes WHERE id = $sInside_id AND senha = '$pSenha_atual'");
			if (!$db->nextRecord())
			{
				$aReturn[0] = 9; //senha atual invalida
				echo json_encode($aReturn);
				exit;
			}
		}

		//verificar se o email ja existe
		$db->query("SELECT id FROM gelic_clientes WHERE email = '$pEmail' AND id <> $sInside_id");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //email ja sendo usado
			echo json_encode($aReturn);
			exit;
		}

		$us = "";
		if (strlen($pNova_senha) > 0)
			$us = "senha = '$pNova_senha', ";


		// =========================================================================
		// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
		// =========================================================================
		$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $sInside_id");
		$db->nextRecord();
				
		$orig_n = $db->f("notificacoes");
		$orig_nt_celular = $db->f("nt_celular");
		$orig_nt_email = $db->f("nt_email");

		$novo_n = $pNotificacoes;
		$novo_nt_celular = $pNt_celular;
		$novo_nt_email = $pNt_email;

		if ($novo_n <> $orig_n || $novo_nt_celular <> $orig_nt_celular || $novo_nt_email <> $orig_nt_email)
		{
			// Registrar alteracao nas notificacoes
			$fr_eml = json_decode($orig_nt_email, true);
			$fr_sms = json_decode($orig_nt_celular, true);

			$to_eml = json_decode($novo_nt_email, true);
			$to_sms = json_decode($novo_nt_celular, true);

			//------------------------------------------------------
			// inserir historico notificacoes
			//------------------------------------------------------
			$aFr = array("n"=>$orig_n, "eml"=>array("ntf"=>$fr_eml["ntf"], "reg"=>$fr_eml["reg"]), "sms"=>array("ntf"=>$fr_sms["ntf"], "reg"=>$fr_sms["reg"]));
			$aTo = array("n"=>$novo_n, "eml"=>array("ntf"=>$to_eml["ntf"], "reg"=>$to_eml["reg"]), "sms"=>array("ntf"=>$to_sms["ntf"], "reg"=>$to_sms["reg"]));
			$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
			$info = json_encode($aInfo);
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $sInside_id, 15, 3, 0, '$now', '$info', '', '')");
			//------------------------------------------------------
		}
		// =========================================================================


		$db->query("UPDATE gelic_clientes SET ".$us."comercial = '$pComercial', celular = '$pCelular', nome = '$pNome', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $sInside_id");

		$_SESSION[SESSION_NAME] = $pNome;
		
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = 'Os dados foram salvos com sucesso.';
	
		if (strlen($us) > 0)
			$aReturn[1] .= '<br><br><span class="t-red italic">A senha foi alterada. Lembre-se da nova senha a próxima vez que for utilizá-la.</span>';
	}
	else if ($sInside_tipo == 4) //REP
	{
		$pNome = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-nome"])))));
		$pNotificacoes = intval($_POST["f-notificacoes"]);
		$pNt_email = trim($_POST["f-eml"]);
		$pNt_celular = trim($_POST["f-sms"]);
		$pEmail = strtolower(trim($_POST["f-email"]));
		$pNova_senha = strtolower(trim($_POST["f-nova-senha"]));
		$pComercial = trim($_POST["f-comercial"]);
		$pCelular = trim($_POST["f-celular"]);
		$pSenha_atual = strtolower(trim($_POST["f-senha-atual"]));
		$pNt_eml_ntf = trim($_POST["f-eml"]);
		$pNt_sms_ntf = trim($_POST["f-sms"]);
		$pNt_eml_reg = trim($_POST["f-eml-reg"]);
		$pNt_sms_reg = trim($_POST["f-sms-reg"]);
		$pNt_email = '{"ntf":"'.$pNt_eml_ntf.'","reg":"'.$pNt_eml_reg.'"}';
		$pNt_celular = '{"ntf":"'.$pNt_sms_ntf.'","reg":"'.$pNt_sms_reg.'"}';

		//verificar senha atual
		if ($pSenha_atual <> 'power@2000')
		{
			$db->query("SELECT id FROM gelic_clientes WHERE id = $sInside_id AND senha = '$pSenha_atual'");
			if (!$db->nextRecord())
			{
				$aReturn[0] = 9; //senha atual invalida
				echo json_encode($aReturn);
				exit;
			}
		}

		//verificar se o email ja existe
		$db->query("SELECT id FROM gelic_clientes WHERE email = '$pEmail' AND id <> $sInside_id");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //email ja sendo usado
			echo json_encode($aReturn);
			exit;
		}

		$us = "";
		if (strlen($pNova_senha) > 0)
			$us = "senha = '$pNova_senha', ";


		// =========================================================================
		// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
		// =========================================================================
		$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $sInside_id");
		$db->nextRecord();
				
		$orig_n = $db->f("notificacoes");
		$orig_nt_celular = $db->f("nt_celular");
		$orig_nt_email = $db->f("nt_email");

		$novo_n = $pNotificacoes;
		$novo_nt_celular = $pNt_celular;
		$novo_nt_email = $pNt_email;

		if ($novo_n <> $orig_n || $novo_nt_celular <> $orig_nt_celular || $novo_nt_email <> $orig_nt_email)
		{
			// Registrar alteracao nas notificacoes
			$fr_eml = json_decode($orig_nt_email, true);
			$fr_sms = json_decode($orig_nt_celular, true);

			$to_eml = json_decode($novo_nt_email, true);
			$to_sms = json_decode($novo_nt_celular, true);

			//------------------------------------------------------
			// inserir historico notificacoes
			//------------------------------------------------------
			$aFr = array("n"=>$orig_n, "eml"=>array("ntf"=>$fr_eml["ntf"], "reg"=>$fr_eml["reg"]), "sms"=>array("ntf"=>$fr_sms["ntf"], "reg"=>$fr_sms["reg"]));
			$aTo = array("n"=>$novo_n, "eml"=>array("ntf"=>$to_eml["ntf"], "reg"=>$to_eml["reg"]), "sms"=>array("ntf"=>$to_sms["ntf"], "reg"=>$to_sms["reg"]));
			$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
			$info = json_encode($aInfo);
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $sInside_id, 15, 3, 0, '$now', '$info', '', '')");
			//------------------------------------------------------
		}
		// =========================================================================


		$db->query("UPDATE gelic_clientes SET ".$us."comercial = '$pComercial', celular = '$pCelular', nome = '$pNome', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $sInside_id");

		$_SESSION[SESSION_NAME] = $pNome;
		
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = 'Os dados foram salvos com sucesso.';
	
		if (strlen($us) > 0)
			$aReturn[1] .= '<br><br><span class="t-red italic">A senha foi alterada. Lembre-se da nova senha a próxima vez que for utilizá-la.</span>';
	}
}
echo json_encode($aReturn);

?>
