<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	$sInside_id = $_SESSION[SESSION_ID];

	$pId = intval($_POST["f-id"]);
	$pTipo = intval($_POST["f-tipo"]);
	$pNome = utf8_decode(trim($_POST["f-nome"]));
	$pPessoa = intval($_POST["f-pessoa"]);
	if ($pPessoa == 1)
		$pCpfcnpj = trim($_POST["f-cpf"]);
	else
		$pCpfcnpj = trim($_POST["f-cnpj"]);

	$pDepartamento = utf8_decode(trim($_POST["f-dep"]));
	$pDn = intval($_POST["f-dn"]);
	$pAdve = intval($_POST["f-adve"]);
	$pRua = utf8_decode(trim($_POST["f-rua"]));
	$pNumero = trim($_POST["f-numero"]);
	$pComplemento = utf8_decode(trim($_POST["f-complemento"]));
	$pBairro = utf8_decode(trim($_POST["f-bairro"]));
	$pId_cidade = intval($_POST["f-cidade"]);
	$pCep = trim($_POST["f-cep"]);
	$pAtivo = intval($_POST["f-ativo"]);
	$pLogin = strtolower(trim($_POST["f-login"]));
	$pEmail = strtolower(trim($_POST["f-email"]));
	$pComercial = trim($_POST["f-comercial"]);
	$pCelular = trim($_POST["f-celular"]);
	$pNotificacoes = intval($_POST["f-notificacoes"]);
	$pNt_email_dn = trim($_POST["f-eml-dn"]);
	$pNt_celular_dn = trim($_POST["f-sms-dn"]);
	$pNt_email_bo = trim($_POST["f-eml-bo"]);
	$pNt_celular_bo = trim($_POST["f-sms-bo"]);
	$pNt_eml_reg = trim($_POST["f-eml-reg"]);
	$pNt_sms_reg = trim($_POST["f-sms-reg"]);
	$pObservacoes = utf8_decode(trim($_POST["f-observacoes"]));
	$pObservacoes = strip_tags($pObservacoes);
	$pObservacoes = nl2br($pObservacoes);
	$pObservacoes = preg_replace("/\s+/", " ", $pObservacoes);
	$now = date("Y-m-d H:i:s");


	if ($pTipo == 1) //BO
	{
		$pNt_email = '{"ntf":"'.$pNt_email_bo.'","reg":"'.$pNt_eml_reg.'"}';
		$pNt_celular = '{"ntf":"'.$pNt_celular_bo.'","reg":"'.$pNt_sms_reg.'"}';
	}
	else
	{
		$pNt_email = '{"ntf":"'.$pNt_email_dn.'","reg":"'.$pNt_eml_reg.'"}';
		$pNt_celular = '{"ntf":"'.$pNt_celular_dn.'","reg":"'.$pNt_sms_reg.'"}';
	}


	$db = new Mysql();
	if ($pId == 0)
	{
		//************ NOVO REGISTRO ***************
		if (!in_array("cli_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		if ($pTipo == 2)
		{
			//DN

			//verificar se o DN (Dealer Number) ja existe
			$db->query("SELECT id FROM gelic_clientes WHERE dn = $pDn");
			if ($db->nextRecord())
			{
				$aReturn[0] = 6; //DN ja existente
				echo json_encode($aReturn);
				exit;
			}

			//verificar se ja existe este login
			$db->query("SELECT id FROM gelic_clientes WHERE login = '$pLogin'");
			if ($db->nextRecord())
			{
				$aReturn[0] = 8; //login ja existente
				echo json_encode($aReturn);
				exit;
			}

			//verificar se ja existe este email
			$db->query("SELECT id FROM gelic_clientes WHERE email = '$pEmail'");
			if ($db->nextRecord())
			{
				$aReturn[0] = 7; //email ja existente
				echo json_encode($aReturn);
				exit;
			}

			//gerar nova senha randomica
			$xSenha = "";
			for ($i=0; $i<6; $i++)
				$xSenha .= mt_rand(1,9);

			$db->query("INSERT INTO gelic_clientes VALUES (NULL, 2, 2, 0, $pAtivo, $pPessoa, '$pCpfcnpj', $pDn, $pAdve, '$pNome', '', '$pRua', '$pNumero', '$pComplemento', '$pBairro', $pId_cidade, '$pCep', '$pComercial', '$pCelular', '$pEmail', '$pLogin', '$xSenha', 0, $pNotificacoes, '$pNt_celular', '$pNt_email', 0, '$pObservacoes', '$now')");
			$dId_cliente = $db->li();


			//------------------------------------------------------
			// inserir historico notificacoes
			//------------------------------------------------------
			$nt_eml = json_decode($pNt_email, true);
			$nt_sms = json_decode($pNt_celular, true);
			$aFr = array("n"=>0, "eml"=>array("ntf"=>"", "reg"=>""), "sms"=>array("ntf"=>"", "reg"=>""));
			$aTo = array("n"=>$pNotificacoes, "eml"=>array("ntf"=>$nt_eml["ntf"], "reg"=>$nt_eml["reg"]), "sms"=>array("ntf"=>$nt_sms["ntf"], "reg"=>$nt_sms["reg"]));
			$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
			$info = json_encode($aInfo);
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $dId_cliente, 15, 1, 0, '$now', '$info', '', '')");
			//------------------------------------------------------


			$db->query("SELECT id FROM gelic_abas WHERE tipo = 'F'");
			$db->nextRecord();
			$dTab = $db->f("id");

			$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $dId_cliente, 'tab', $dTab)");

			$aReturn[0] = 1; //sucesso
		}
		else
		{
			//BO

			//verificar se ja existe este login
			$db->query("SELECT id FROM gelic_clientes WHERE login = '$pLogin'");
			if ($db->nextRecord())
			{
				$aReturn[0] = 8; //login ja existente
				echo json_encode($aReturn);
				exit;
			}

			//verificar se ja existe este email
			$db->query("SELECT id FROM gelic_clientes WHERE email = '$pEmail'");
			if ($db->nextRecord())
			{
				$aReturn[0] = 7; //email ja existente
				echo json_encode($aReturn);
				exit;
			}

			//gerar nova senha randomica
			$xSenha = "";
			for ($i=0; $i<6; $i++)
				$xSenha .= mt_rand(1,9);

			$db->query("INSERT INTO gelic_clientes VALUES (NULL, 1, 4, 0, $pAtivo, $pPessoa, '$pCpfcnpj', 0, 0, '$pNome', '', '$pRua', '$pNumero', '$pComplemento', '$pBairro', $pId_cidade, '$pCep', '$pComercial', '$pCelular', '$pEmail', '$pLogin', '$xSenha', 0, $pNotificacoes, '', '', 0, '$pObservacoes', '$now')");
			$dId_cliente = $db->li();

			//------------------------------------------------------
			// inserir historico notificacoes
			//------------------------------------------------------
			$nt_eml = json_decode($pNt_email, true);
			$nt_sms = json_decode($pNt_celular, true);
			$aFr = array("n"=>0, "eml"=>array("ntf"=>"", "reg"=>""), "sms"=>array("ntf"=>"", "reg"=>""));
			$aTo = array("n"=>$pNotificacoes, "eml"=>array("ntf"=>$nt_eml["ntf"], "reg"=>$nt_eml["reg"]), "sms"=>array("ntf"=>$nt_sms["ntf"], "reg"=>$nt_sms["reg"]));
			$aInfo = array("fr"=>$aFr, "to"=>$aTo, "ip"=>$_SERVER["REMOTE_ADDR"]);
			$info = json_encode($aInfo);
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $dId_cliente, 15, 1, 0, '$now', '$info', '', '')");
			//------------------------------------------------------

			$db->query("SELECT id FROM gelic_abas WHERE tipo = 'F'");
			$db->nextRecord();
			$dTab = $db->f("id");

			$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $dId_cliente, 'tab', $dTab)");

			$aReturn[0] = 1; //sucesso
		}
	}
	else
	{
		//************ SALVAR ALTERACOES ***************
		if (!in_array("cli_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		//verificar se o tipo e o id conferem
		$db->query("SELECT id FROM gelic_clientes WHERE id = $pId AND tipo = $pTipo");
		if ($db->nextRecord())
		{
			if ($pTipo == 1) //BO
			{
				//verificar se ja existe este login
				$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND login = '$pLogin'");
				if ($db->nextRecord())
				{
					$aReturn[0] = 8; //login ja existente
					echo json_encode($aReturn);
					exit;
				}

				//verificar se ja existe este email
				$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND email = '$pEmail'");
				if ($db->nextRecord())
				{
					$aReturn[0] = 7; //email ja existente
					echo json_encode($aReturn);
					exit;
				}


				// =========================================================================
				// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
				// =========================================================================
				$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $pId");
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
					$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $pId, 15, 1, 0, '$now', '$info', '', '')");
					//------------------------------------------------------
				}
				// =========================================================================


				$db->query("UPDATE gelic_clientes SET ativo = $pAtivo, pessoa = $pPessoa, cpfcnpj = '$pCpfcnpj', nome = '$pNome', rua = '$pRua', numero = '$pNumero', complemento = '$pComplemento', bairro = '$pBairro', id_cidade = $pId_cidade, cep = '$pCep', comercial = '$pComercial', celular = '$pCelular', email = '$pEmail', login = '$pLogin', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email', observacoes = '$pObservacoes' WHERE id = $pId");
				$aReturn[0] = 1; //sucesso
			}
			else if ($pTipo == 2) //DN
			{
				//verificar se o DN (Dealer Number) ja existe
				$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND dn = $pDn");
				if ($db->nextRecord())
				{
					$aReturn[0] = 6; //DN ja existente
					echo json_encode($aReturn);
					exit;
				}

				//verificar se ja existe este login
				$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND login = '$pLogin'");
				if ($db->nextRecord())
				{
					$aReturn[0] = 8; //login ja existente
					echo json_encode($aReturn);
					exit;
				}

				//verificar se ja existe este email
				$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND email = '$pEmail'");
				if ($db->nextRecord())
				{
					$aReturn[0] = 7; //email ja existente
					echo json_encode($aReturn);
					exit;
				}


				// =========================================================================
				// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
				// =========================================================================
				$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $pId");
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
					$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $pId, 15, 1, 0, '$now', '$info', '', '')");
					//------------------------------------------------------
				}
				// =========================================================================


				$db->query("UPDATE gelic_clientes SET ativo = $pAtivo, pessoa = $pPessoa, cpfcnpj = '$pCpfcnpj', dn = $pDn, adve = $pAdve, nome = '$pNome', rua = '$pRua', numero = '$pNumero', complemento = '$pComplemento', bairro = '$pBairro', id_cidade = $pId_cidade, cep = '$pCep', comercial = '$pComercial', celular = '$pCelular', email = '$pEmail', login = '$pLogin', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email', observacoes = '$pObservacoes' WHERE id = $pId");
				$aReturn[0] = 1; //sucesso
			}
			else if ($pTipo == 3) //DN FILHO
			{
				//verificar se ja existe este email
				$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND email = '$pEmail'");
				if ($db->nextRecord())
				{
					$aReturn[0] = 7; //email ja existente
					echo json_encode($aReturn);
					exit;
				}


				// =========================================================================
				// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
				// =========================================================================
				$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $pId");
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
					$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $pId, 15, 1, 0, '$now', '$info', '', '')");
					//------------------------------------------------------
				}
				// =========================================================================


				$db->query("UPDATE gelic_clientes SET ativo = $pAtivo, pessoa = $pPessoa, cpfcnpj = '$pCpfcnpj', nome = '$pNome', departamento = '$pDepartamento', rua = '$pRua', numero = '$pNumero', complemento = '$pComplemento', bairro = '$pBairro', id_cidade = $pId_cidade, cep = '$pCep', comercial = '$pComercial', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email', observacoes = '$pObservacoes' WHERE id = $pId");
				$aReturn[0] = 1; //sucesso
			}
			else if ($pTipo == 4) //REP
			{
				//verificar se ja existe este email
				$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND email = '$pEmail'");
				if ($db->nextRecord())
				{
					$aReturn[0] = 7; //email ja existente
					echo json_encode($aReturn);
					exit;
				}


				// =========================================================================
				// VERIFICAR SE HOUVE ALTERACAO NAS NOTIFICACOES
				// =========================================================================
				$db->query("SELECT notificacoes, nt_celular, nt_email FROM gelic_clientes WHERE id = $pId");
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
					$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $pId, 15, 1, 0, '$now', '$info', '', '')");
					//------------------------------------------------------
				}
				// =========================================================================


				$db->query("UPDATE gelic_clientes SET ativo = $pAtivo, pessoa = $pPessoa, cpfcnpj = '$pCpfcnpj', nome = '$pNome', departamento = '$pDepartamento', rua = '$pRua', numero = '$pNumero', complemento = '$pComplemento', bairro = '$pBairro', id_cidade = $pId_cidade, cep = '$pCep', comercial = '$pComercial', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email', observacoes = '$pObservacoes' WHERE id = $pId");
				$aReturn[0] = 1; //sucesso
			}
		}
		else
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}
	}
}
echo json_encode($aReturn);

?>
