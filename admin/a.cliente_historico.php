<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("his_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">HISTÓRICO DE REGISTROS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);

		echo $tPage->body;
		exit;
	}

	$gId_cliente = 0;
	if (isset($_GET["id"]))
		$gId_cliente = intval($_GET["id"]);

	$db = new Mysql();

	$dClientes = "";
	$db->query("SELECT id, tipo, nome FROM gelic_clientes WHERE tipo IN (1,2) ORDER BY tipo DESC, dn");
	while ($db->nextRecord())
	{
		if ($db->f("tipo") == 1)
			$dNome = 'BO - '.utf8_encode($db->f("nome"));
		else
			$dNome = utf8_encode($db->f("nome"));

		if ($gId_cliente == $db->f("id"))
			$dClientes .= '<option value="'.$db->f("id").'" selected="selected">'.$dNome.'</option>';
		else
			$dClientes .= '<option value="'.$db->f("id").'">'.$dNome.'</option>';
	}

	$tPage = new Template("a.cliente_historico.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{ID_CLIENTE}}", $gId_cliente);
	$tPage->replace("{{CLIENTES}}", $dClientes);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
