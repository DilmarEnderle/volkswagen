<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$pId = intval($_POST["f-id"]);
	$pDescricao = utf8_decode(trim($_POST["f-descricao"]));
	$pCor_texto = $_POST["f-cor_texto"];
	$pCor_fundo = $_POST["f-cor_fundo"];
	$pTipo = intval($_POST["f-inicial"]);
	
	$db = new Mysql();
	if ($pId == 0)
	{
		if (!in_array("sta_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("INSERT INTO gelic_status VALUES (NULL, $pTipo, '$pDescricao', '$pCor_texto', '$pCor_fundo')");
	}
	else
	{
		if (!in_array("sta_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_status SET tipo = $pTipo, descricao = '$pDescricao', cor_texto = '$pCor_texto', cor_fundo = '$pCor_fundo' WHERE id = $pId");
	}

	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
