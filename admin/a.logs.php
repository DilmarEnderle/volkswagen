<?php

$gKey = "";
if (isset($_GET["k"]))
	$gKey = $_GET["k"];

if ($gKey <> "a32cbc5af5914dc4624e2d28cd6a139e")
{
	echo "Acesso Negado.";
	exit;
}

// ------------------------------------------------------
// Lista logins efetuados por clientes tipo 2 (DNs)
// por data decrescente e último login do cliente no dia
// ------------------------------------------------------

require_once "include/config.php";
require_once "include/essential.php";

$db = new Mysql();

$oOutput = utf8_decode('<table cellpadding="6" cellspacing="0" style="border: 1px solid #cecece; border-collapse: collapse; width: 960px;">
	<thead>
		<tr style="background-color: #cccccc;">
			<th style="border: 1px solid #cecece;">Data</th>
			<th style="border: 1px solid #cecece;">Hora</th>
			<th style="border: 1px solid #cecece;">Nome do DN</th>
			<th style="border: 1px solid #cecece;">N° do DN</th>
			<th style="border: 1px solid #cecece;">IP</th>
			<th style="border: 1px solid #cecece;">Região</th>
			<th style="border: 1px solid #cecece;">Online</th>
		</tr>
	</thead>
	<tbody>');

$time_20_seconds_ago = time() - 20;

$db->query("SELECT DATE(data_hora) AS data, DAYOFWEEK(data_hora) AS diasemana FROM gelic_log_login GROUP BY data ORDER BY data_hora DESC");
while ($db->nextRecord())
{
	if ($db->Row[0] % 2 == 0)
		$cd = "f0f0f0";
	else
		$cd = "e0e0e0";

	$dData = $db->f("data");

	$db->query("
SELECT 
	log.data_hora, 
	log.ip,
	log.tipo,
	cli.nome,
	cli.dn,
	cli.id_parent,
	clidn.dn AS parent_dn,
	uf.regiao,
	uf.uf,
	cli.online >= $time_20_seconds_ago AS online,
	(SELECT MAX(data_hora) FROM gelic_log_login WHERE DATE(data_hora) = '$dData' AND id_clienteusuario = cli.id AND tipo IN (2,3)) AS dh
FROM 
	gelic_log_login AS log,
	gelic_clientes AS cli
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE 
	log.tipo IN (2,3) AND
	cli.id = log.id_clienteusuario AND
	DATE(data_hora) = '$dData'
GROUP BY 
	log.id_clienteusuario 
ORDER BY 
	cli.nome",1);
	while ($db->nextRecord(1))
	{
		if ($db->f("tipo",1) == 3)
			$dn = $db->f("parent_dn",1);
		else
			$dn = $db->f("dn",1);

		if (in_array($dn, array(232323,242424,252525,262626,272727)))
			$dn = 'POOL '.$db->f("uf",1);

		if ($db->f("online",1) > 0)
			$online = '<td style="border: 1px solid #cecece; text-align: center;"><span class="online"></span></td>';
		else
			$online = utf8_decode('<td style="border: 1px solid #cecece; text-align: center;"><span class="offline"></span></td>');

		$oOutput .= '<tr>
			<td style="border: 1px solid #cecece; background-color:#'.$cd.'; text-align: center;">'.mysqlToBr(substr($db->f("dh",1), 0, 10)).'</td>
			<td style="border: 1px solid #cecece; text-align: center;">'.substr($db->f("dh",1), 11).'</td>
			<td style="border: 1px solid #cecece; text-align: left;">'.$db->f("nome",1).'</td>
			<td style="border: 1px solid #cecece; color:#ff0000; text-align: center;">'.$dn.'</td>
			<td style="border: 1px solid #cecece; text-align: right;">'.$db->f("ip",1).'</td>
			<td style="border: 1px solid #cecece; text-align: center;">'.$db->f("regiao",1).'</td>
			'.$online.'
		</tr>';
	}
}

$oOutput .= '</tbody></table>';

echo '<html>
<head>
	<style>
		body, table { 
			font-family: Arial;
			font-size: 1em;
			padding: 0;
			margin: 0;
		}

		.online {
			display: inline-block;
			width: 16px;
			height: 16px;
			border-radius: 8px;
			background-color: #00c800;
		}

		.offline {
			display: inline-block;
			width: 10px;
			height: 10px;
			border-radius: 5px;
			background-color: #ff3131;
		}

		a:link.dn, a:visited.dn {
			display: inline-block;
			width: 80px;
			height: 80px;
			background-image: url(\'img/xlsx.png\');
			background-repeat: no-repeat;
			background-position: center center;
			float: right;
			border: 1px solid #f0f0f0;
			background-color: #ffffff;
		}
		a:hover.dn {
			opacity: 0.8;
			background-color: #f0f0f0;
		}

		.header { width: 960px; height: 100px; box-sizing: border-box; padding: 10px 0; float: left; }

	</style>
</head>
<body>
<div class="header">
	<a class="dn" href="a.logs_download.php?k=9837d3f9cc7d39b8c83690de79ca0924" target="_blank"></a>
</div>
'.$oOutput.'
</body>
</html>';

?>
