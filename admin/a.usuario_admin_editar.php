<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$db = new Mysql();
	$gId = 0;
	if (isset($_GET["id"]))
	{
		$gId = intval($_GET["id"]);
		$db->query("SELECT * FROM gelic_admin_usuarios WHERE id = $gId");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dId_perfil = $db->f("id_perfil");
			$dAtivo = $db->f("ativo");
			$dNome = utf8_encode($db->f("nome"));
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

			$vTitle = 'Editar... (<a class="red">'.$dNome.'</a>)';
			$vSave = "Salvar Alterações";
		}
		else
		{
			$gId = 0;
		}
	}
	
	if ($gId == 0)
	{
		if (!in_array("usr_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">USUÁRIOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dId_perfil = 0;
		$dAtivo = 1;
		$dNome = "";
		$dLogin = "";
		$dSenha = "- senha -";
		$dEmail = "";
		$dCelular = "";
		$dNotificacoes = 1;
		$dNt_celular = array();
		$dNt_email = array();

		$vTitle = "Novo Usuário";
		$vSave = "Salvar Novo Usuário";
	}
	else
	{
		if (!in_array("usr_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">USUÁRIOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}
	}

	$dPerfis = '';
	$db->query("SELECT id, nome FROM gelic_admin_usuarios_perfis ORDER BY nome");
	while ($db->nextRecord())
	{
		if ($dId_perfil == $db->f("id"))
			$dPerfis .= '<option value="'.$db->f("id").'" selected>'.utf8_encode($db->f("nome")).'</option>';
		else
			$dPerfis .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
	}
	
	$tPage = new Template("a.usuario_admin_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);

	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{PERFIS}}", $dPerfis);
	$tPage->replace("{{LOGIN}}", $dLogin);
	$tPage->replace("{{SENHA_PH}}", $dSenha);
	$tPage->replace("{{ATIVO_SIM}}", (int)in_array($dAtivo,array(1)));
	$tPage->replace("{{ATIVO_NAO}}", (int)in_array($dAtivo,array(0)));
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
