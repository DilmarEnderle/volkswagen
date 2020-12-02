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
		$db->query("SELECT * FROM gelic_biblioteca WHERE id = $gId");
		if ($db->nextRecord())
		{
			$vTitle = "Editar...";
			$vSave = "Salvar Alterações";
			$dId = $db->f("id");
			$dTipo = intval($db->f("tipo")); //0 = arquivo, 1 = link
			if ($dTipo > 0)
			{
				$dLink = $db->f("item");
				$dUp_btn = "block";
				$dUp_rdy = "none";
				$dUp_file = "---";
				$dUp_bytes = formatSizeUnits(0);
				$rtext = "";
			}
			else
			{
				$dLink = "http://";
				$dUp_btn = "none";
				$dUp_rdy = "block";
				$dUp_file = $db->f("item");
				$dUp_bytes = formatSizeUnits(intval($db->f("bytes")));
				$rtext = $dUp_file."_".$dUp_bytes;
			}

			$dNome = utf8_encode($db->f("nome"));
			$dUf = $db->f("uf");
			$dObservacao = utf8_encode($db->f("observacao"));
		}
		else
		{
			$gId = 0;
		}
	}
	
	if ($gId == 0)
	{
		if (!in_array("bib_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">BIBLIOTECA</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}

		$vTitle = "Inserir Novo Item";
		$vSave = "Salvar na Biblioteca";
		$dId = 0;
		$dTipo = 0;
		$dLink = "http://";
		$dUp_btn = "block";
		$dUp_rdy = "none";
		$dUp_file = "---";
		$dUp_bytes = formatSizeUnits(0);
		$dNome = "";
		$dUf = "";
		$dObservacao = "";
		$rtext = "";
	}
	else
	{
		if (!in_array("bib_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">BIBLIOTECA</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}
	}

	if ($dUf == "00")
		$dEstados = '<option value="00" selected>TODOS</option>';
	else
		$dEstados = '<option value="00">TODOS</option>';

	$db->query("SELECT * FROM gelic_uf ORDER BY estado");
	while ($db->nextRecord())
	{
		if ($db->f("uf") == $dUf)
			$dEstados .= '<option value="'.$db->f("uf").'" selected>'.utf8_encode($db->f("estado")).'</option>';
		else
			$dEstados .= '<option value="'.$db->f("uf").'">'.utf8_encode($db->f("estado")).'</option>';
	}
	
	$tPage = new Template("a.biblioteca_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{SAVE}}", $vSave);
	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{TIPO}}", $dTipo);
	$tPage->replace("{{T_ARQ}}", (int)in_array($dTipo,array(0)));
	$tPage->replace("{{T_LNK}}", (int)in_array($dTipo,array(1)));
	$tPage->replace("{{VERSION}}", VERSION);

	if ($dTipo == 0)
	{
		$tPage->replace("{{ARQ-P}}", "block");
		$tPage->replace("{{LNK-P}}", "none");
	}
	else
	{
		$tPage->replace("{{ARQ-P}}", "none");
		$tPage->replace("{{LNK-P}}", "block");
	}
	$tPage->replace("{{LINK}}", $dLink);
	$tPage->replace("{{UPBTN}}", $dUp_btn);
	$tPage->replace("{{UPRDY}}", $dUp_rdy);
	$tPage->replace("{{UPFILE}}", $dUp_file);
	$tPage->replace("{{UPBYTES}}", $dUp_bytes);
	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{OBS}}", $dObservacao);
	$tPage->replace("{{ESTADOS}}", $dEstados);
	$tPage->replace("{{RTEXT}}", $rtext);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
