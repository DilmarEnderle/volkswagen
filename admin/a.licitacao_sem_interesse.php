<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'Sem Interesse','');
if (isInside())
{
	$pId_licitacao = intval($_POST["f-id-licitacao"]);

	$db = new Mysql();

	//BUSCAR DNs SEM INTERESSE EM PARTICIPAR
	$db->query("
SELECT
	cli.id_parent,
	cli.nome,
    clidn.nome AS nome_dn
FROM
	gelic_clientes AS cli
    LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	cli.id IN (
SELECT 
id_sender
FROM
gelic_historico
WHERE
id_licitacao = $pId_licitacao AND
tipo = 22
GROUP BY
id_sender
)");
	if ($db->nf() > 0)
		$aReturn[1] = "Sem Interesse (".$db->nf().")";
	else
		$aReturn[1] = "Sem Interesse";

	while ($db->nextRecord())
	{
		if ($db->f("id_parent") > 0)
			$aReturn[2] .= '<span style="display:block;line-height:21px;">'.utf8_encode($db->f("nome_dn")).'</span>';
		else
			$aReturn[2] .= '<span style="display:block;line-height:21px;">'.utf8_encode($db->f("nome")).'</span>';
	}
}
echo json_encode($aReturn);

?>
