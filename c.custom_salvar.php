<?php 

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	if ($sInside_tipo == 2 || $sInside_tipo == 1)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];

	$pCor_base = $_POST["cor-base"];
	$pCor_menu = $_POST["cor-menu"];
	$pCor_aba = $_POST["cor-aba"];
	$pCor_botao_1 = $_POST["cor-botao-1"];
	$pCor_botao_2 = $_POST["cor-botao-2"];

	$db = new Mysql();

	$db->query("SELECT id FROM gelic_clientes_personalizacao WHERE id_cliente = $cliente_parent");
	if ($db->nextRecord())
		$db->query("UPDATE gelic_clientes_personalizacao SET cor_base = '$pCor_base', cor_menu = '$pCor_menu', cor_aba = '$pCor_aba', cor_botao_1 = '$pCor_botao_1', cor_botao_2 = '$pCor_botao_2' WHERE id_cliente = $cliente_parent");
	else
		$db->query("INSERT INTO gelic_clientes_personalizacao VALUES (NULL, $cliente_parent, '$pCor_base', '$pCor_menu', '$pCor_aba', '$pCor_botao_1', '$pCor_botao_2')");

	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
