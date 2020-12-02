<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$db = new Mysql();

	$pId = intval($_POST["f-id"]);
	$pNome = $db->escapeString(utf8_decode(trim($_POST["f-nome"])));
	$pDia = intval($_POST["f-dia"]);
	$pMes = intval($_POST["f-mes"]);
	$pFixo = intval($_POST["f-fixo"]);
	
	if ($pId == 0)
	{
		//************ NOVO REGISTRO ***************
		if (!in_array("fer_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("INSERT INTO gelic_feriados VALUES (NULL, $pMes, $pDia, '$pNome', $pFixo)");
	}
	else
	{
		//************ SALVAR ALTERACOES ***************
		if (!in_array("fer_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_feriados SET mes = $pMes, dia = $pDia, nome = '$pNome', fixo = $pFixo WHERE id = $pId");
	}

	$aReturn[0] = 1; //sucesso
} 
echo json_encode($aReturn);

?>
