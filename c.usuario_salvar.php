<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1 || $sInside_tipo == 3 || $sInside_tipo == 4) //BO, DN FILHO, REP
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();


	//verificar se o cliente tem acesso
	$db->query("SELECT nome, id_cidade FROM gelic_clientes WHERE id = $sInside_id");
	$db->nextRecord();
	$dId_cidade = $db->f("id_cidade");
	$dDn_nome = utf8_encode($db->f("nome"));

	$pId = intval($_POST["f-id"]);
	$pNome = utf8_decode(trim($_POST["f-nome"]));
	$pDepartamento = utf8_decode(trim($_POST["f-departamento"]));
	$pId_perfil = intval($_POST["f-perfil"]);
	$pEmail = strtolower(trim($_POST["f-email"]));
	$pComercial = trim($_POST["f-comercial"]);
	$pCelular = trim($_POST["f-celular"]);
	$pNotificacoes = intval($_POST["f-notificacoes"]);
	$pNt_eml_ntf = trim($_POST["f-eml"]);
	$pNt_sms_ntf = trim($_POST["f-sms"]);
	$pNt_email = '{"ntf":"'.$pNt_eml_ntf.'","reg":""}';
	$pNt_celular = '{"ntf":"'.$pNt_sms_ntf.'","reg":""}';

	$now = date("Y-m-d H:i:s");
	$ip = $_SERVER['REMOTE_ADDR'];

	//informacao pronta
	if ($pId == 0)
	{
		//************ NOVO USUARIO ***************

		//verificar duplicidade de email
		$db->query("SELECT id, tipo, id_parent, id_perfil FROM gelic_clientes WHERE email = '$pEmail'");
		if ($db->nextRecord())
		{
			$removido = false;
			$db->query("SELECT id FROM gelic_clientes_acesso_historico WHERE acao = 2 AND id_cliente_acao = ".$db->f("id_parent"),1);
			if ($db->nextRecord(1))
				$removido = true;

			if ($db->f("tipo") == 1)
			{
				$aReturn[0] = 8; //o usuario ja esta cadastrado como BO
				echo json_encode($aReturn);
				exit;
			}
			else if ($db->f("tipo") == 2)
			{
				$aReturn[0] = 7; //o usuario ja esta cadastrado como DN
				echo json_encode($aReturn);
				exit;
			}
			else if ($db->f("id_parent") == $sInside_id && $removido == false)
			{
				$aReturn[0] = 6; //usuario ja existe neste DN (nao deixar prosseguir)
				echo json_encode($aReturn);
				exit;
			}
			else
			{
				$dId_cliente = $db->f("id");
				$dId_parent = $db->f("id_parent");
				$dId_perfil_parent = $db->f("id_perfil");

				//verificar se o acesso ja foi dado para este usuario
				$db->query("SELECT id FROM gelic_clientes_acesso WHERE id_cliente = $dId_cliente AND id_cliente_acesso = $sInside_id");
				if ($db->nextRecord())
				{
					$aReturn[0] = 5; //DN ja deu acesso para este usuario
					echo json_encode($aReturn);
					exit;
				}

				if (isset($_POST["f-q17pil69ai"]))
				{
					//se nao existir ainda ninguem na tabela gelic_clientes_acesso
					//adicionar para os 2 DNs ao mesmo tempo
					$db->query("SELECT COUNT(*) AS total FROM gelic_clientes_acesso WHERE id_cliente = $dId_cliente");
					$db->nextRecord();
					if ($db->f("total") == 0)
					{
						$db->query("SELECT id FROM gelic_clientes_acesso_historico WHERE acao = 2 AND id_cliente_acao = $dId_parent");
						if (!$db->nextRecord())
						{
							//inserir acesso
							$db->query("INSERT INTO gelic_clientes_acesso VALUES (NULL, $dId_cliente, $dId_parent, $dId_perfil_parent)");

							//inserir no historico
							$db->query("INSERT INTO gelic_clientes_acesso_historico VALUES (NULL, 3, '$now', '$ip', $dId_parent, $dId_cliente, $dId_perfil_parent)");
						}
					}

					//liberar acesso para este usuario acessar meus dados tambem
					$db->query("INSERT INTO gelic_clientes_acesso VALUES (NULL, $dId_cliente, $sInside_id, $pId_perfil)");

					//inserir no historico de liberacao de acessos
					$db->query("INSERT INTO gelic_clientes_acesso_historico VALUES (NULL, 1, '$now', '$ip', $sInside_id, $dId_cliente, $pId_perfil)");

					//atualizar tipo
					$db->query("UPDATE gelic_clientes SET tipo = 4 WHERE id = $dId_cliente");


					//***************************************
					//*****  NOTIFICAR REP DO ACESSO  *******
					//***************************************
					$tEmail_assunto = 'GELIC - Novo Acesso';
					$tEmail_mensagem = 'O DN ('.$dDn_nome.') liberou para que você tenha acesso nos dados dele.<br><br>
Serviço Gelic: <a href="https://gelicprime.com.br/" target="_blank">www.gelicprime.com.br</a><br>
<span style="color:#ff0000;">Utilize o seu login atual para entrar no sistema.</span><br><br>'.rodapeEmail();

					queueMessage(0, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, 0, $dId_cliente, $pEmail, $tEmail_assunto, $tEmail_mensagem, '', '');
					//***************************************
					//***************************************
					//***************************************

					$aReturn[0] = 1; //sucesso
					$aReturn[1] = "index.php?p=cli_usuarios";
				}
				else
				{
					//usuario existe em outro DN (perguntar se devo liberar acesso para este usuario acessar meus dados)
					$aReturn[0] = 4; //pergunta
					$aReturn[1] = 'Este usuário já está cadastrado para outro DN.<br><br><span class="bold">Permitir acesso nos meus dados também?</span><br><br><span class="italic t-red">ATENÇÃO: Como este usuário já existe, as informações fornecidas e opções de notificação não serão salvas.<br>Apenas o perfil de acesso será utilizado.</span>';
					echo json_encode($aReturn);
					exit;
				}
			}
		}
		else
		{
			//Novo email (cadastrar novo usuario tipo 3)
				
			//gerar nova senha randomica
			$xSenha = "";
			for ($i=0; $i<6; $i++)
				$xSenha .= mt_rand(1,9);

			$db->query("INSERT INTO gelic_clientes VALUES (NULL, 3, $pId_perfil, $sInside_id, 1, 1, '', 0, 0, '$pNome', '$pDepartamento', '', '', '', '', $dId_cidade, '', '$pComercial', '$pCelular', '$pEmail', '', '$xSenha', 0, $pNotificacoes, '$pNt_celular', '$pNt_email', 0, '', '$now')");
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
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $dId_cliente, 15, 2, 0, '$now', '$info', '', '')");
			//------------------------------------------------------


			$db->query("SELECT id FROM gelic_abas WHERE tipo = 'F'");
			$db->nextRecord();
			$dTab = $db->f("id");
			$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $dId_cliente, 'tab', $dTab)");
			$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $dId_cliente, 'dn', 0)");


			//***************************************
			//******  ENVIAR SENHA DE ACESSO  *******
			//***************************************
			$tEmail_assunto = 'GELIC - Senha de Acesso';
			$tEmail_mensagem = 'O DN ('.$dDn_nome.') liberou acesso para você utilizar o sistema.<br><br>
Serviço Gelic: <a href="https://gelicprime.com.br/" target="_blank">www.gelicprime.com.br</a><br>
<span style="font-weight: bold;">Login:</span> '.$pEmail.'<br>
<span style="font-weight: bold;">Senha:</span> '.$xSenha.'<br><br>'.rodapeEmail();

			queueMessage(0, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, 0, $dId_cliente, $pEmail, $tEmail_assunto, $tEmail_mensagem, '', '');
			//***************************************
			//***************************************
			//***************************************

			$aReturn[0] = 1; //sucesso
			$aReturn[1] = "index.php?p=cli_usuarios";
		}
	}
	else
	{
		//************ SALVAR ALTERACOES ***************

		//se o usuario for REP entao salvar somente o id_perfil
		$db->query("SELECT tipo FROM gelic_clientes WHERE id = $pId");
		$db->nextRecord();

		if ($db->f("tipo") == 4) //REP
		{
			//verificar se o usuario pertence a este DN
			$db->query("SELECT id FROM gelic_clientes_acesso WHERE id_cliente = $pId AND id_cliente_acesso = $sInside_id");
			if ($db->nextRecord())
			{
				$dId = $db->f("id");

				//alterar somente o id_perfil
				$db->query("UPDATE gelic_clientes_acesso SET id_perfil = $pId_perfil WHERE id = $dId");
	
				$aReturn[0] = 1; //sucesso
				$aReturn[1] = "index.php?p=cli_usuarios";
			}
			else
			{
				$aReturn[0] = 9; //acesso restrito
				echo json_encode($aReturn);
				exit;
			}
		}
		else if ($db->f("tipo") == 3) //DN FILHO
		{
			//verificar duplicidade de email
			$db->query("SELECT id FROM gelic_clientes WHERE id <> $pId AND email = '$pEmail'");
			if ($db->nextRecord())
			{
				$aReturn[0] = 7; //email ja existe
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
				$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, 0, 0, $sInside_id, $pId, 15, 2, 0, '$now', '$info', '', '')");
				//------------------------------------------------------
			}
			// =========================================================================


			$db->query("UPDATE gelic_clientes SET nome = '$pNome', departamento = '$pDepartamento', id_perfil = $pId_perfil, comercial = '$pComercial', celular = '$pCelular', email = '$pEmail', notificacoes = $pNotificacoes, nt_celular = '$pNt_celular', nt_email = '$pNt_email' WHERE id = $pId");

			$aReturn[0] = 1; //sucesso
			$aReturn[1] = "index.php?p=cli_usuarios";
		}
	}
}
echo json_encode($aReturn);

?>
