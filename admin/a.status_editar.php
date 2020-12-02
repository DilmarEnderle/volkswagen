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
		$db->query("SELECT * FROM gelic_status WHERE id = $gId");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dDescricao = utf8_encode($db->f("descricao"));
			$dCor_texto = $db->f("cor_texto");
			$dCor_fundo = $db->f("cor_fundo");
			$dTipo = $db->f("tipo");

			$vTitle = 'Editar... (<a class="red">'.$dDescricao.'</a>)';
			$vSave = "Salvar Alterações";
		}
		else
		{
			$gId = 0;
		}
	}
	
	if ($gId == 0)
	{
		if (!in_array("sta_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">STATUS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dDescricao = "";
		$dCor_texto = "ffffff";
		$dCor_fundo = "000000";
		$dTipo = 0;

		$vTitle = "Novo Status";
		$vSave = "Salvar Novo Status";
	}
	else
	{
		if (!in_array("sta_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">STATUS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}
	}

	$tPage = new Template("a.status_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);

	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{DESCRICAO}}", $dDescricao);
	$tPage->replace("{{COR_TEXTO}}", $dCor_texto);
	$tPage->replace("{{COR_FUNDO}}", $dCor_fundo);
	$tPage->replace("{{INICIAL_SIM}}", (int)in_array($dTipo,array(1)));
	$tPage->replace("{{INICIAL_NAO}}", (int)in_array($dTipo,array(0)));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
