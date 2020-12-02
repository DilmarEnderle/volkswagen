<?php

require_once "include/config.php";
require_once "include/essential.php";

$sInside_id = 1;
$db = new Mysql();

$filename = 'list.csv';
$contents = file($filename);
foreach($contents as $line)
{
	$pId_licitacao = intval($line);

	$s = $pId_licitacao;

	//verificar se o status pode ser alterado para esta licitacao
	$db->query("SELECT lic.deletado, (SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo FROM gelic_licitacoes AS lic WHERE lic.id = $pId_licitacao");
	$db->nextRecord();
	if ($db->f("deletado") > 0)
	{
		$s .= ' <span style="color:#ff0000;font-weight:bold;">- deletado -</span><br>';
		echo $s;
		continue;
	}

	if ($db->f("ultimo_tipo") == 31)
	{
		$s .= ' <span style="color:#ee0000;font-weight:bold;">- ENCERRADA -</span><br>';
		echo $s;
		continue;
	}


	//ler original
	$aLic_aba_status = array("fr"=>array(),"to"=>array());
	$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao ORDER BY grupo");
	while ($db->nextRecord())
		$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));


	$pId_status_admin = 32; //$aLic_aba_status["fr"][0]["id_status"];
	$pId_status_bo = 32; //$aLic_aba_status["fr"][1]["id_status"];
	$pId_status_dn_apl = 32; //$aLic_aba_status["fr"][2]["id_status"];
	$pId_status_dn_outros = 32; //$aLic_aba_status["fr"][3]["id_status"];

	$pFixo_admin = $aLic_aba_status["fr"][0]["fixo"];
	$pFixo_bo = $aLic_aba_status["fr"][1]["fixo"];
	$pFixo_dn_apl = $aLic_aba_status["fr"][2]["fixo"];
	$pFixo_dn_outros = $aLic_aba_status["fr"][3]["fixo"];

	$pId_aba_admin = $aLic_aba_status["fr"][0]["aba"];
	$pId_aba_bo = $aLic_aba_status["fr"][1]["aba"];
	$pId_aba_dn_apl = $aLic_aba_status["fr"][2]["aba"];
	$pId_aba_dn_outros = $aLic_aba_status["fr"][3]["aba"];

	//alterar status
	$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_admin, id_status = $pId_status_admin, status_fixo = $pFixo_admin WHERE id_licitacao = $pId_licitacao AND grupo = 1");
	$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_bo, id_status = $pId_status_bo, status_fixo = $pFixo_bo WHERE id_licitacao = $pId_licitacao AND grupo = 2");
	$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_dn_apl, id_status = $pId_status_dn_apl, status_fixo = $pFixo_dn_apl WHERE id_licitacao = $pId_licitacao AND grupo = 3");
	$db->query("UPDATE gelic_licitacoes_abas SET id_aba = $pId_aba_dn_outros, id_status = $pId_status_dn_outros, status_fixo = $pFixo_dn_outros WHERE id_licitacao = $pId_licitacao AND grupo = 4");

	$aLic_aba_status["to"][] = array("grupo"=>1, "aba"=>$pId_aba_admin, "status"=>$pId_status_admin, "fixo"=>$pFixo_admin);
	$aLic_aba_status["to"][] = array("grupo"=>2, "aba"=>$pId_aba_bo, "status"=>$pId_status_bo, "fixo"=>$pFixo_bo);
	$aLic_aba_status["to"][] = array("grupo"=>3, "aba"=>$pId_aba_dn_apl, "status"=>$pId_status_dn_apl, "fixo"=>$pFixo_dn_apl);
	$aLic_aba_status["to"][] = array("grupo"=>4, "aba"=>$pId_aba_dn_outros, "status"=>$pId_status_dn_outros, "fixo"=>$pFixo_dn_outros);

	//inserir no historico
	$now = date("Y-m-d H:i:s");
	$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 36, 0, 0, '$now', '".json_encode($aLic_aba_status)."', '', '')");


	$s .= ' <span style="color:#00c400;font-weight:bold;">Done.</span><br><span style="color:#0000ff;">'.json_encode($aLic_aba_status).'</span><br><br>';
	echo $s;
}

?>
