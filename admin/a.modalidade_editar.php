<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$gId = 0;
	if (isset($_GET["id"]))
	{
		$gId = intval($_GET["id"]);
		$db = new Mysql();
		$db->query("SELECT * FROM gelic_modalidades WHERE id = $gId AND antigo = 0");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dNome = utf8_encode($db->f("nome"));
			$dAbv = utf8_encode($db->f("abv"));

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
		if (!in_array("mod_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">MODALIDADES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dNome = "";
		$dAbv = "";

		$vTitle = "Adicionar Modalidade";
		$vSave = "Salvar Nova Modalidade";
	}
	else
	{
		if (!in_array("mod_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">MODALIDADES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}
	}
	
	$tPage = new Template("a.modalidade_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);
	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{ABV}}", $dAbv);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
