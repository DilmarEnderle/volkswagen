<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$db = new Mysql();

	$pId = intval($_POST["f-id"]);
	$pNome = utf8_decode(trim($_POST["f-nome"]));
	$pNome = $db->escapeString($pNome);
	$pUf = $_POST["f-estado"];
	$pAdve = intval($_POST["f-adve"]);

	$db = new Mysql();
	if ($pId == 0)
	{
		//************ NOVO REGISTRO ***************

		if (!in_array("cid_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		//verificar se ja existe esta cidade
		$db->query("SELECT id FROM gelic_cidades WHERE nome = '$pNome' AND uf = '$pUf'");
		if ($db->nextRecord())
		{
			$aReturn[0] = 2; //cidade ja existente
			echo json_encode($aReturn);
			exit;
		}

		$db->query("INSERT INTO gelic_cidades VALUES (NULL, '$pNome', '$pUf', 0, '', '', $pAdve)");

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = "a.cidade.php?uf=".$pUf;
	}
	else
	{
		//************ SALVAR ALTERACOES ***************

		if (!in_array("cid_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		//verificar se ja existe outra cidade com este nome
		$db->query("SELECT id FROM gelic_cidades WHERE nome = '$pNome' AND uf = '$pUf' AND id <> $pId");
		if ($db->nextRecord())
		{
			$aReturn[0] = 2; //cidade ja existente
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_cidades SET nome = '$pNome', uf = '$pUf', adve = '$pAdve' WHERE id = $pId");

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = "a.cidade.php?uf=".$pUf;
	}
}
echo json_encode($aReturn);

?>
