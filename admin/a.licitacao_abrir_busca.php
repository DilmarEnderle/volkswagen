<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pId_licitacao = intval($_POST["id"]);
	$db = new Mysql();
	
	$db->query("SELECT id FROM gelic_licitacoes WHERE id = $pId_licitacao AND deletado = 0");
	if ($db->nextRecord())
	{
		//****** SALVAR BUSCA ********
		$db->query("SELECT valor FROM gelic_admin_usuarios_config WHERE id_admin_usuario = $sInside_id AND config = 'search'");
		if ($db->nextRecord())
		{
			$json_string = $db->f("valor");

			$a = json_decode($json_string, true);
			$a["search"] = 1;
			$a["search_1"] = $pId_licitacao;

			$json_string = json_encode($a);
			$db->query("UPDATE gelic_admin_usuarios_config SET valor = '$json_string' WHERE id_admin_usuario = $sInside_id AND config = 'search'");
		}
		else
		{
			$json_string = '{"search":1,"search_1":'.$pId_licitacao.',"search_2":{"dn":"","status":"","estados":"","cidades":"","regioes":"","ultimas":0,"orgao":"","adve":0,"numero":"","data_de":"","data_ate":""}}';
			$db->query("INSERT INTO gelic_admin_usuarios_config VALUES (NULL, $sInside_id, 'search', '$json_string')");
		}
		//**********************************************************************************

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = "a.licitacao_abrir.php?id=$pId_licitacao";
	}
}
echo json_encode($aReturn);

?>
