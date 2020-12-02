<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_item = intval($_POST["id-item"]);

	$oHtml = '<div class="ultimate-row" style="max-height:280px;overflow-x:hidden;overflow-y:auto;">
		<div class="ultimate-row">Selecione o motivo de não participação:</div>
		<select id="i-spart-motivo" style="height:30px;width:100%;">
			<option value="0"></option>';

	$db = new Mysql();
	$db->query("
SELECT
	mtv.id,
	mtv.descricao,
    icheck.id AS id_check
FROM
	gelic_motivos AS mtv
    LEFT JOIN gelic_licitacoes_itens_check AS icheck ON icheck.sem_participacao_apl_id_motivo = mtv.id AND id_item = $pId_item
WHERE
	mtv.tipo = 23
ORDER BY
	mtv.descricao");
	while ($db->nextRecord())
	{
		if ($db->f("id_check") <> '')
			$oHtml .= '<option value="'.$db->f("id").'" selected="selected">'.utf8_encode($db->f("descricao")).'</option>';
		else
			$oHtml .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';
	}

	$oHtml .= '</select></div>';

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oHtml;
} 
echo json_encode($aReturn);
?>
