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
		$db->query("SELECT * FROM gelic_feriados WHERE id = $gId");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dFixo = $db->f("fixo");
			$dNome = utf8_encode($db->f("nome"));
			$dDia = $db->f("dia");
			$dMes = $db->f("mes");

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
		if (!in_array("fer_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">FERIADOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dFixo = 9;
		$dNome = "";
		$dDia = 0;
		$dMes = 0;

		$vTitle = "Adicionar Feriado";
		$vSave = "Salvar Novo Feriado";
	}
	else
	{
		if (!in_array("fer_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">FERIADOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}
	}
	
	$aMes = array();
	$aMes[1] = "Janeiro";
	$aMes[2] = "Fevereiro";
	$aMes[3] = utf8_encode("Março");
	$aMes[4] = "Abril";
	$aMes[5] = "Maio";
	$aMes[6] = "Junho";
	$aMes[7] = "Julho";
	$aMes[8] = "Agosto";
	$aMes[9] = "Setembro";
	$aMes[10] = "Outubro";
	$aMes[11] = "Novembro";
	$aMes[12] = "Dezembro";	
	
	$dDays = "";
	$dMonths = "";
	for ($i=1; $i<=31; $i++)
	{
		if ($i == $dDia)
			$dDays .= '<option value="'.$i.'" selected="selected">Dia '.$i.'</option>';
		else
			$dDays .= '<option value="'.$i.'">Dia '.$i.'</option>';
	}
	
	for ($i=1; $i<=12; $i++)
	{
		if ($i == $dMes)
			$dMonths .= '<option value="'.$i.'" selected="selected">'.$i.' - '.$aMes[$i].'</option>';
		else
			$dMonths .= '<option value="'.$i.'">'.$i.' - '.$aMes[$i].'</option>';
	}

	$tPage = new Template("a.feriado_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);
	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{FIXO_SIM}}", (int)in_array($dFixo,array(1)));
	$tPage->replace("{{FIXO_NAO}}", (int)in_array($dFixo,array(0)));
	$tPage->replace("{{FIXO}}", $dFixo);
	$tPage->replace("{{DIAS}}", $dDays);
	$tPage->replace("{{MESES}}", utf8_decode($dMonths));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
