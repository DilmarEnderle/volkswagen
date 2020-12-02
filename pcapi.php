<?php

require_once "include/config.php";

$aReturn = array();
if (isset($_GET["action"]))
{
	if ($_GET["action"] == "ativos")
	{
		$db = new Mysql();
		$db->query("SELECT cod_pregao, cod_uasg FROM gelic_licitacoes WHERE pc_ativo = 1");
		while ($db->nextRecord())
			$aReturn[] = array("cod_pregao"=>intval($db->f("cod_pregao")),"cod_uasg"=>intval($db->f("cod_uasg")));
	}
}
else if (isset($_POST["action"]))
{
	if ($_POST["action"] == "desativar")
	{
		$aDesativar = json_decode($_POST["values"], true);
		$db = new Mysql();
		$now = date("Y-m-d H:i:s");

		for ($i=0; $i<count($aDesativar); $i++)
		{
			$db->query("SELECT id FROM gelic_licitacoes WHERE cod_pregao = ".$aDesativar[$i]["cod_pregao"]." AND cod_uasg = ".$aDesativar[$i]["cod_uasg"]);
			while ($db->nextRecord())
			{
				$db->query("UPDATE gelic_licitacoes SET pc_ativo = 0 WHERE id = ".$db->f("id"),1);
				$db->query("DELETE FROM gelic_pc_usuarios WHERE id_licitacao = ".$db->f("id"),1);

				$aHis = array();
				$aHis["source"] = "pcapi.php";
				$aHis["cod_pregao"] = $aDesativar[$i]["cod_pregao"];
				$aHis["cod_uasg"] = $aDesativar[$i]["cod_uasg"];

				$db->query("INSERT INTO gelic_historico VALUES (NULL, ".$db->f("id").", 0, 0, 0, 0, 52, 0, 0, '$now', '".json_encode($aHis)."', '', '')",1);
			}
		}
	}
}
header('Content-Type: application/json');
echo json_encode($aReturn);

?>
