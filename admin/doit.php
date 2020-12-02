<?php

require_once "include/config.php";
require_once "include/essential.php";

$db = new Mysql();
$db->query("SELECT id,dn,nome FROM gelic_clientes WHERE tipo = 2 AND id IN (SELECT id_cliente FROM gelic_licitacoes_apl_ear)");
while ($db->nextRecord())
{
	$dId_cliente = $db->f("id");
	$db->query("
SELECT
	SUM(enviadas) + SUM(aprovadas) + SUM(reprovadas) AS total_apl
FROM
	gelic_licitacoes_apl_ear
WHERE
	id_cliente = $dId_cliente AND
	id_licitacao IN (
SELECT 
	lic.id
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
WHERE
	lic.deletado = 0 AND
	lic.instancia = 1 AND
	lic.datahora_abertura >= '2017-01-01 00:00:00' AND
    lic.datahora_abertura <= '2017-08-18 23:59:59'
GROUP BY
	lic.id    
)",1);
	$db->nextRecord(1);
	if ($db->f("total_apl",1) > 0)
		echo '<br>'.$db->f("dn").',"'.$db->f("nome").'",'.$db->f("total_apl",1);
}



?>
