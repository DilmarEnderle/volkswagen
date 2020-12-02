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
		$db->query("SELECT * FROM gelic_motivos WHERE id = $gId");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dId_parent = $db->f("id_parent");
			$dDescricao = utf8_encode($db->f("descricao"));
			$dTipo = $db->f("tipo");

			$oSubmotivos = '<option value="0"></option>';
			$db->query("SELECT id, descricao FROM gelic_motivos WHERE id <> $dId AND tipo = $dTipo AND id_parent = 0 ORDER BY descricao");
			while ($db->nextRecord())
			{
				if ($db->f("id") == $dId_parent)
					$oSubmotivos .= '<option value="'.$db->f("id").'" selected>'.utf8_encode($db->f("descricao")).'</option>';
				else
					$oSubmotivos .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';
			}

			$disp_sub = "none";
			if ($dTipo > 0 && $dTipo != 21 && $dTipo != 22 && $dTipo != 23)
				$disp_sub = "block";

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
		if (!in_array("mot_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">MOTIVOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dDescricao = "";
		$dTipo = 0;
		$oSubmotivos = '<option value="0"></option>';
		$disp_sub = "none";

		$vTitle = "Novo Motivo";
		$vSave = "Salvar Novo Motivo";
	}
	else
	{
		if (!in_array("mot_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">MOTIVOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}
	}

	$aTipos = array();
	$aTipos[] = array(10,"Admin (Encerramento)");
	$aTipos[] = array(20,"Admin (Prorrogação)");
	$aTipos[] = array(21,"Admin Itens (Inabilitado)");
	$aTipos[] = array(22,"Admin Itens (Sem Interesse DN - Aberto)");
	$aTipos[] = array(23,"Admin Itens (Sem Participação - com APL)");
	$aTipos[] = array(30,"Cliente (Sem Interesse Licitação)");
	$aTipos[] = array(40,"Cliente (Reprovação APL)");

	$oTipos = '<option value="0"></option>';
	for ($i=0; $i<count($aTipos); $i++)
	{
		if ($aTipos[$i][0] == $dTipo)
			$oTipos .= '<option value="'.$aTipos[$i][0].'" selected>'.$aTipos[$i][1].'</option>';
		else
			$oTipos .= '<option value="'.$aTipos[$i][0].'">'.$aTipos[$i][1].'</option>';
	}

	$tPage = new Template("a.motivo_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);
	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{TIPOS}}", $oTipos);
	$tPage->replace("{{DESCRICAO}}", $dDescricao);
	$tPage->replace("{{SUBMOTIVOS}}", $oSubmotivos);
	$tPage->replace("{{DSUB}}", $disp_sub);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
