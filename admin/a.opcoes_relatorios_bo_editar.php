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

	$db = new Mysql();
	$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = 0 AND config = 'relatorios'");
	if ($db->nextRecord())
	{
		$dAcesso = explode(" ", $db->f("valor")); //string de acessos
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = array();
		$aAcessos = array("rel_1_1","rel_1_2","rel_1_3","rel_1_4","rel_2_1","rel_2_2","rel_2_3","rel_2_4","rel_3_1","rel_4_1","rel_4_2","rel_4_3","rel_4_4","rel_5_1","rel_6_1","rel_6_2","rel_6_3");
		for ($i=0; $i<count($aAcessos); $i++)
			$aReturn[1][] = array("acesso"=>$aAcessos[$i], "valor"=>(int)in_array($aAcessos[$i],$dAcesso));
	}
} 
echo json_encode($aReturn);

?>
