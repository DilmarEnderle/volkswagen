<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'',0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("his_visualizar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		$aReturn[1] = '<span style="display:block;text-align:center;font-size:15px;font-style:italic;color:#777777;line-height:100px;">- acesso restrito -</span>';
		$aReturn[2] = 0; //nao mostrar msg
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];
	$db = new Mysql();
	$pId_cliente = intval($_POST["id-cliente"]);

	//check if cliente exists
	$db->query("SELECT id FROM gelic_clientes WHERE id = $pId_cliente AND tipo IN (1,2)");
	if (!$db->nextRecord())
	{
		$aReturn[0] = 9; //acesso restrito
		$aReturn[1] = '<span style="display:block;text-align:center;font-size:15px;font-style:italic;color:#777777;line-height:100px;">- selecione um cliente -</span>';
		$aReturn[2] = 0; //nao mostrar msg
		echo json_encode($aReturn);
		exit;
	}


	$oRows = '';

	$db->query("
SELECT
	his.data_hora,
	his.texto,
	his.nome_arquivo,
	his.arquivo,
	his.id_sender,
	adm.nome AS usuario_admin
FROM
	gelic_clientes_historico AS his
	INNER JOIN gelic_admin_usuarios AS adm ON adm.id = his.id_sender
WHERE
	his.id_cliente = $pId_cliente
ORDER BY
	his.id");
	while ($db->nextRecord())
	{
		if ($db->f("id_sender") == $sInside_id)
			$c = 'h-box-minha';
		else
			$c = 'h-box-outros';

		$oRows .= '<div class="content-inside '.$c.'">
			<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
			<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
			<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
			<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
			<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">'.utf8_encode($db->f("texto")).'</div>';

		if ($db->f("arquivo") <> '')
		{
			$oRows .= '<div style="overflow:hidden;margin-bottom:8px;">
				<span class="t13 gray-88 fl lh-24 italic">Arquivo:</span>
				<span class="t13 red fl ml-8" style="line-height: 22px; border: 1px solid #999999; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
				<a class="bt-style-2 fl" href="'.linkFileBucket("vw/chis/".$db->f("arquivo")).'" target="_blank" style="height: 24px; line-height: 22px;">Ver Anexo</a>
			</div>';
		}

		$oRows .= '</div>';
	}

	if ($oRows == '')
		$oRows = '<span style="display:block;text-align:center;font-size:15px;font-style:italic;color:#777777;line-height:40px;">- nenhum registro -</span>';

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oRows;
	$aReturn[2] = 1;
}
echo json_encode($aReturn);

?>
