<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_item = intval($_POST["id-item"]);

	$oHtml = '<div class="ultimate-row" style="max-height:280px;overflow-x:hidden;overflow-y:auto;">';
	$total = 0;
	$inb = array();

	$db = new Mysql();
	$db->query("
SELECT
	mtv.id,
	mtv.descricao,
    inb.id AS id_inb
FROM
	gelic_motivos AS mtv
    LEFT JOIN gelic_licitacoes_itens_check_inabilitado AS inb ON inb.id_motivo = mtv.id AND id_item = $pId_item
WHERE
	mtv.tipo = 21
ORDER BY
	mtv.descricao");
	while ($db->nextRecord())
	{
		if ($db->f("id_inb") <> '')
		{
			$total++;
			$inb[] = intval($db->f("id"));
			$oHtml .= '<a class="drop-item1" href="javascript:void(0);" onclick="inabilitadoSelect(this, '.$db->f("id").');">'.utf8_encode($db->f("descricao")).'</a>';
		}
		else
		{
			$oHtml .= '<a class="drop-item0" href="javascript:void(0);" onclick="inabilitadoSelect(this, '.$db->f("id").');">'.utf8_encode($db->f("descricao")).'</a>';
		}
	}

	$oHtml .= '</div>';

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oHtml;
	$aReturn[2] = '<span id="inb-count">'.$total.'</span>';
	$aReturn[3] = json_encode($inb);
} 
echo json_encode($aReturn);
?>
