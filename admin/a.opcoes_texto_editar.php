<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cnf_visualizar", $xAccess))
	{
		$aReturn[0] = 9; //accesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$aTexto = array();
	$aTexto[1] = 'LICITAÇÃO: Termos de envio da APL (DEPOIS DE CLICAR EM ENVIAR)';
	$aTexto[2] = 'LICITAÇÃO: Aprovar APL - Condições para Participação';
	$aTexto[3] = 'LICITAÇÃO: Reprovar APL - Observações Gerais';
	$aTexto[4] = 'LICITAÇÃO: Reverter Aprovação da APL - Observações Gerais';
	$aTexto[5] = 'LICITAÇÃO: Reverter Reprovação da APL - Observações Gerais';
	$aTexto[11] = 'COMPRA DIRETA/SRP: Termos de envio da APL';
	$aTexto[12] = 'COMPRA DIRETA/SRP: Aprovar APL - Condições para Participação';
	$aTexto[13] = 'COMPRA DIRETA/SRP: Reprovar APL - Observações Gerais';
	$aTexto[14] = 'COMPRA DIRETA/SRP: Reverter Aprovação da APL - Observações Gerais';
	$aTexto[15] = 'COMPRA DIRETA/SRP: Reverter Reprovação da APL - Observações Gerais';
	$aTexto[16] = 'LICITAÇÃO: Termos de envio da APL (ANTES DE CLICAR EM ENVIAR)';

	$db = new Mysql();
	$pId = intval($_POST["id"]);
	$db->query("SELECT texto FROM gelic_texto WHERE id = $pId");
	if ($db->nextRecord())
	{
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $aTexto[$pId];
		$aReturn[2] = utf8_encode($db->f("texto"));
	}
} 
echo json_encode($aReturn);

?>
