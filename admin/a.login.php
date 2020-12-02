<?php

require_once "include/config.php";

$aReturn = array(0);

$pLogin = trim(strtolower($_POST["f-login"]));
$pPassw = trim(strtolower($_POST["f-passw"]));

$now = date("Y-m-d H:i:s");

$db = new Mysql();
$db->query("SELECT 
	usr.id, 
    usr.ativo, 
    usr.nome,
    pfl.ip
FROM 
	gelic_admin_usuarios AS usr
	INNER JOIN gelic_admin_usuarios_perfis AS pfl ON pfl.id = usr.id_perfil
WHERE 
	usr.login = '$pLogin' AND 
    usr.senha = '$pPassw'");
if ($db->nextRecord())
{
	if ($db->f("ativo") == 0)
	{
		$aReturn[0] = 9; //acesso bloqueado
		echo json_encode($aReturn);
		exit;
	}

	$dId = $db->f("id");
	$dNome = utf8_encode($db->f("nome"));
	$dIp_access = $db->f("ip");

	//verificar restrição via IP
	if (strlen($dIp_access) == 0)
	{
		$aReturn[0] = 7; //IP
		echo json_encode($aReturn);
		exit;
	}

	if ($dIp_access != "*")
	{
		$remote = $_SERVER['REMOTE_ADDR'];
		$ipa = explode(",", $dIp_access);
		if (!in_array($remote, $ipa))
		{
			$aReturn[0] = 7; //IP
			echo json_encode($aReturn);	
			exit;
		}
	}

	session_start();
	$_SESSION[SESSION_ID] = $dId;
	$_SESSION[SESSION_NAME] = $dNome;
	if (isset($_SESSION[SESSION_ID]))
	{
		setcookie(COOKIE_NAME, $pLogin."!=|=!".md5($pPassw), time()+50400); //14 horas
		//log
		$db->query("INSERT INTO gelic_log_login VALUES (NULL, '$now', 0, $dId, '".basename(__FILE__)." (".__LINE__.")', '".$_SERVER['REMOTE_ADDR']."')");
		$aReturn[0] = 1; //sucesso
	}
}
else
{
	$aReturn[0] = 8; //usuario - senha invalidos
}

echo json_encode($aReturn);

?>
