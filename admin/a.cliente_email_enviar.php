<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cli_enviar_senha", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pId = intval($_POST["id"]);
	$pReset = intval($_POST["reset"]);

	$db = new Mysql();

	$db->query("SELECT tipo, login, email, senha FROM gelic_clientes WHERE id = $pId");
	if ($db->nextRecord())
	{
		if (strlen($db->f("email")) > 0)
		{
			$dTipo = $db->f("tipo");
			$dLogin = $db->f("login");
			$dEmail = $db->f("email");
			$dSenha = $db->f("senha");

			if ($dTipo == 3 || $dTipo == 4) //DN FILHO, REP
				$dLogin = $dEmail;

			$tEmail_assunto = 'GELIC - Senha de Acesso';

			if ($pReset > 0)
			{
				//gerar nova senha randomica
				$dSenha = "";
				for ($i=0; $i<6; $i++)
					$dSenha .= mt_rand(1,9);

				//atualizar senha no registro
				$db->query("UPDATE gelic_clientes SET senha = $dSenha WHERE id = $pId");
	
				$tEmail_assunto = 'GELIC - Nova Senha de Acesso';
			}
			

			if (strlen($dLogin) > 0)
				$dUsuario_login = $dLogin;
			else
				$dUsuario_login = $dEmail;

			//***************************************
			//******  ENVIAR SENHA DE ACESSO  *******
			//***************************************
			$tEmail_mensagem = 'Para entrar na sua área restrita GELIC utilize os seguintes dados.<br><br>
Serviço Gelic: <a href="https://gelicprime.com.br/" target="_blank">www.gelicprime.com.br</a><br>
<span style="font-weight: bold;">Login:</span> '.$dUsuario_login.'<br>
<span style="font-weight: bold;">Senha:</span> '.$dSenha.'<br><br>
'.rodapeEmail();

			queueMessage(0, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, 0, $pId, $dEmail, $tEmail_assunto, $tEmail_mensagem, '', '');
			//***************************************
			//***************************************
			//***************************************

			$aReturn[0] = 1; //sucesso
		}
		else
		{
			$aReturn[0] = 8; //email nao encontrado
		}
	}
	else
	{
		$aReturn[0] = 9; //acesso restrito
	}
} 
echo json_encode($aReturn);

?>
