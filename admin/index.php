<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
	header("Location: a.licitacao.php");
else 
{
	// --- Plataformas ---
	$db = new Mysql();
	$db->selectDB("gelic_gelic");

	$host = $_SERVER["HTTP_HOST"];
	if ($host == '127.0.0.1')
		$host .= 'Gelic';

	$a = array();
	$a[] = array("n"=>"Gelic Padrão","sid"=>"---","lnk"=>"http://".$host."/admin");
	$a[] = array("n"=>"Volkswagen (vw)","sid"=>"vw","lnk"=>"http://".$host."/vw/admin");

	$db->query("SELECT nome, id_sistema FROM gelic_plataformas WHERE status = 'Ativa' ORDER BY nome");
	while ($db->nextRecord())
		$a[] = array("n"=>utf8_encode($db->f("nome"))." (".$db->f("id_sistema").")","sid"=>$db->f("id_sistema"),"lnk"=>"http://".$host."/".$db->f("id_sistema")."/admin");

	$dAdm_plat = '';
	$dPlat = '';
	for ($i=0; $i<count($a); $i++)
	{
		if ($i == 2)
			$dPlat .= '<div style="height:1px;border-bottom:1px dotted #888888;margin:2px 0 3px 0;"></div>';

		$dPlat .= '<a class="adm-drop-item-L" href="'.$a[$i]["lnk"].'">'.utf8_decode($a[$i]["n"]).'</a>';

		if (SYSTEM_ID == $a[$i]["sid"])
			$dAdm_plat = utf8_decode($a[$i]["n"]);
	}
	// --- end Plataformas ---


	$tPage = new Template("a.login.html");
	$tPage->replace("{{ADMPLAT}}", utf8_encode($dAdm_plat));
	$tPage->replace("{{PLAT}}", utf8_encode($dPlat));
	$tPage->replace("{{VERSION}}", VERSION);
	echo $tPage->body;
}

?>
