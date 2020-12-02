<?php

require_once "include/config.php";
require_once "include/essential.php";

$r_array = array();
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pDe = preg_replace('/[^0-9]/','',trim($_POST["de"]));
	$pAte = preg_replace('/[^0-9]/','',trim($_POST["ate"]));
	$pId_tipo = intval($_POST["id_tipo"]);

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$add_to_where = " AND cli.id = $cliente_parent AND lic.deletado = 0";
	}
	else
		$add_to_where = " AND lic.deletado = 0";

	if (strlen($pDe) == 8)
	{
		$pDe = substr($pDe,-4) . "-" . substr($pDe,2,2) . "-" . substr($pDe,0,2);
		$add_to_where .= " AND lic.data_hora >= '$pDe'";
	}

	if (strlen($pAte) == 8)
	{
		$pAte = substr($pAte,-4) . "-" . substr($pAte,2,2) . "-" . substr($pAte,0,2);
		$add_to_where .= " AND lic.data_hora <= '$pAte'";
	}


	$db = new Mysql();

	if ($pId_tipo == 0) //motivos
	{
		$r_array[0] = array('Motivo','Total');
		$db->query("SELECT id,descricao FROM gelic_motivos WHERE tipo = 30 ORDER BY descricao");
		while ($db->nextRecord())
		{
			$dId_motivo = $db->f("id");
			$db->query("SELECT 
	COUNT(DISTINCT(lic.id)) AS total 
FROM 
	gelic_licitacoes AS lic,
    gelic_licitacoes_clientes AS licc,
	gelic_clientes AS cli,
    gelic_historico as his
WHERE 
	lic.id = licc.id_licitacao AND
    licc.id_cliente = cli.id$add_to_where AND 
    his.id_licitacao = lic.id AND
	his.tipo = 22 AND 
    his.id_valor_1 = $dId_motivo",1);
			$db->nextRecord(1);
			$r_array[$db->Row[0]] = array(utf8_encode($db->f("descricao")),intval($db->f("total",1)));
		}
	} 
}
echo json_encode($r_array);
?>
