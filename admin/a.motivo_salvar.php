<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$pId = intval($_POST["f-id"]);
	$pTipo = intval($_POST["f-tipo"]);
	$pDescricao = utf8_decode(trim($_POST["f-descricao"]));
	$pId_parent = intval($_POST["f-id_parent"]);
	
	$db = new Mysql();
	if ($pId == 0)
	{
		//************ NOVO REGISTRO ***************

		if (!in_array("mot_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("INSERT INTO gelic_motivos VALUES (NULL, $pId_parent, $pTipo, '$pDescricao')");
	}
	else
	{
		//************ SALVAR ALTERACOES ***************

		if (!in_array("mot_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_motivos SET tipo = $pTipo, descricao = '$pDescricao', id_parent = $pId_parent WHERE id = $pId");
	}

	$aReturn[0] = 1; //sucesso
}

echo json_encode($aReturn);

?>
