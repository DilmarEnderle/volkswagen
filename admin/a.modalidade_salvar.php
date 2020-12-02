<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'');
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$db = new Mysql();

	$pId = intval($_POST["f-id"]);
	$pNome = $db->escapeString(utf8_decode(trim($_POST["f-nome"])));
	$pAbv = $db->escapeString(utf8_decode(trim($_POST["f-abv"])));
	$pAbv = mb_strtoupper($pAbv, 'UTF-8');
	
	if ($pId == 0)
	{
		//************ NOVO REGISTRO ***************
		if (!in_array("mod_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_modalidades WHERE abv = '$pAbv' AND antigo = 0");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //abv repetido
			echo json_encode($aReturn);
			exit;
		}

		$db->query("INSERT INTO gelic_modalidades VALUES (NULL, '$pNome', '$pAbv', 0)");
		$last_id = $db->li();
		
		if (isset($_POST["gl"]))
		{
			$aReturn[1] .= '<option value="0">- modalidade -</option>';
			$db->query("SELECT id, nome FROM gelic_modalidades WHERE antigo = 0 ORDER BY nome");
			while ($db->nextRecord())
			{
				if ($db->f("id") == $last_id)
					$aReturn[1] .= '<option value="'.$db->f("id").'" selected="selected">'.utf8_encode($db->f("nome")).'</option>';
				else
					$aReturn[1] .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
			}
		}
	}
	else
	{
		//************ SALVAR ALTERACOES ***************
		if (!in_array("mod_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("SELECT id FROM gelic_modalidades WHERE id <> $pId AND abv = '$pAbv' AND antigo = 0");
		if ($db->nextRecord())
		{
			$aReturn[0] = 8; //abv repetido
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_modalidades SET nome = '$pNome', abv = '$pAbv' WHERE id = $pId");
	}

	$aReturn[0] = 1; //sucesso
} 
echo json_encode($aReturn);

?>
