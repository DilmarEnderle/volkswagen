<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_item = intval($_POST["id-item"]);
	$pEvento = intval($_POST["evento"]);

	$oOutput = '';
	$db = new Mysql();
	$db->query("SELECT id, data_evento FROM gelic_licitacoes_itens_eventos WHERE id_item = $pId_item AND evento = $pEvento ORDER BY data_evento");
	while ($db->nextRecord())
	{
		$oOutput .= '<div style="overflow:hidden;">
			<span class="disp-date">'.mysqlToBr($db->f("data_evento")).'</span>
			<a class="rem-date" href="javascript:void(0);" onclick="remDate('.$db->f("id").');" title="Remover">-</a>
		</div>';
	}

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
