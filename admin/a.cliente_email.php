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

	$pId = intval($_POST["id"]);
	$db = new Mysql();

	$db->query("SELECT email FROM gelic_clientes WHERE id = $pId");
	if ($db->nextRecord())
	{
		if (strlen($db->f("email")) > 0)
		{
			$aReturn[0] = 1; //sucesso
			$aReturn[1] = 'Os dados de login serão enviados para:<br><span class="bold">'.$db->f("email").'</span><br><br><a id="i-reset" class="check-chk0" href="javascript:void(0);" onclick="ckSelf(this);" style="display: inline-block; font-size: 13px;">Resetar Senha</a><br><br><br><span class="bold">Confirmar envio?</span>';
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
