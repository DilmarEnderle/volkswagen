<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_excluir", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$pId_licitacao = intval($_POST["id_licitacao"]);
	if ($pId_licitacao > 0)
	{
		$db = new Mysql();
		$now = date("Y-m-d H:i:s");

		//verificar se a licitacao pode ser removida
		$db->query("SELECT id, cod_pregao, cod_uasg FROM gelic_licitacoes WHERE id = $pId_licitacao");
		if ($db->nextRecord())
		{
			$dCod_pregao = $db->f("cod_pregao");
			$dCod_uasg = $db->f("cod_uasg");

			//remover licitacao
			$db->query("UPDATE gelic_licitacoes SET deletado = 1 WHERE id = $pId_licitacao");

			//inserir no historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 35, 0, 0, '$now', '', '', '')");

			// Remover registros da tabela "gelic_pc_usuarios"
			$db->query("DELETE FROM gelic_pc_usuarios WHERE id_licitacao = $pId_licitacao");

			// Desabilitar pc local
			$db->query("UPDATE gelic_licitacoes SET pc_ativo = 0 WHERE id = $pId_licitacao");

			//Remover notificacoes (remove da fila de envio)
			$db->query("DELETE FROM gelic_mensagens WHERE id_notificacao = 27 AND ((metodo = 1 AND assunto LIKE '%(LIC $pId_licitacao)%') OR (metodo = 2 AND mensagem LIKE '%(LIC $pId_licitacao)%'))");

			$aReturn[0] = 1; //success
		}
	}
} 
echo json_encode($aReturn);

?>
