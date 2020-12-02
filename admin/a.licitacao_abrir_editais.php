<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'');
if (isInside())
{
	$pId_licitacao = intval($_POST["id"]);

	$aReturn[1] = '<div class="drp" style="overflow:hidden;background-color:#e5e5e5;border-bottom:1px solid #aeaeae;">
		<span style="float:left;width:527px;text-align:left;padding-left:8px;font-size:12px;font-weight:bold;line-height:21px;color:#444444;box-sizing:border-box;">Arquivo</span>
		<span style="float:left;width:156px;text-align:left;padding-left:8px;font-size:12px;font-weight:bold;line-height:21px;color:#444444;box-sizing:border-box;">Data de Publicação</span>
	</div>
	<div class="editais-holder drp">';

	$db = new Mysql();
	$db->query("SELECT data_publicacao, nome_arquivo, arquivo FROM gelic_licitacoes_edital WHERE id_licitacao = $pId_licitacao ORDER BY id DESC");
	while ($db->nextRecord())
	{
		if ($db->f("data_publicacao") == "0000-00-00")
			$data_publicacao = '<span class="dpni">- não informado -</span>';
		else
			$data_publicacao = '<span class="dp">'.mysqlToBr($db->f("data_publicacao")).'</span>';

		$short_file_name = $db->f("nome_arquivo");
		if (strlen($short_file_name) > 56)
			$short_file_name = substr($short_file_name, 0, 45)."...".substr($short_file_name, -8);

		$aReturn[1] .= '<a class="edital-drop-item drp" href="'.linkFileBucket("vw/edital/".$db->f("arquivo")).'" target="_blank"><img src="img/file.png" style="float:left;margin-left:6px;margin-top:4px;width:20px;height:20px;border:0;"><span class="ar">'.utf8_encode($short_file_name).'</span>'.$data_publicacao.'</a>';
	}

	$aReturn[1] .= '</div>';
	$aReturn[0] = 1; //sucesso
}

echo json_encode($aReturn);

?>
