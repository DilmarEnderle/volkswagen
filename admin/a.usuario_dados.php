<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("usr_info_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">MEUS DADOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);
		
		echo $tPage->body;
		exit;
	}


	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();
	$db->query("
SELECT
	usr.nome,
	usr.login,
	usr.email,
	usr.celular,
	usr.nt_email,
	usr.nt_celular,
	usr.notificacoes,
	pfl.nome AS perfil
FROM
	gelic_admin_usuarios AS usr
	INNER JOIN gelic_admin_usuarios_perfis AS pfl ON pfl.id = usr.id_perfil
WHERE
	usr.id = $sInside_id");
	$db->nextRecord();

	$dNome = utf8_encode($db->f("nome"));
	$dPerfil = utf8_encode($db->f("perfil"));
	$dLogin = $db->f("login");
	$dSenha = "(manter a mesma)";
	$dCelular = $db->f("celular");
	$dEmail = $db->f("email");
	$dNotificacoes = $db->f("notificacoes");
	if (strlen($db->f("nt_celular"))>0)
		$dNt_celular = str_split($db->f("nt_celular"));
	else
		$dNt_celular = array();

	if (strlen($db->f("nt_email"))>0)
		$dNt_email = str_split($db->f("nt_email"));
	else
		$dNt_email = array();

	$tPage = new Template("a.usuario_dados.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));

	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{PERFIL}}", $dPerfil);
	$tPage->replace("{{LOGIN}}", $dLogin);
	$tPage->replace("{{SENHA}}", $dSenha);
	$tPage->replace("{{EMAIL}}", $dEmail);
	$tPage->replace("{{CELULAR}}", $dCelular);
	$tPage->replace("{{NOTIF_SIM}}", (int)in_array($dNotificacoes,array(1)));
	$tPage->replace("{{NOTIF_NAO}}", (int)in_array($dNotificacoes,array(0)));
	$tPage->replace("{{EML}}", implode("",$dNt_email));
	$tPage->replace("{{SMS}}", implode("",$dNt_celular));

	foreach(range('A','R') as $letter) 
		$tPage->replace("{{EML_".$letter."}}", (int)in_array($letter, $dNt_email));

	foreach(range('A','R') as $letter) 
		$tPage->replace("{{SMS_".$letter."}}", (int)in_array($letter, $dNt_celular));

	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
