<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_visualizar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		$aReturn[1] = 'Acesso restrito';
		$aReturn[2] = 0;
		echo json_encode($aReturn);
		exit;
	}

	$pId_pc_mensagem = intval($_POST["id"]);

	$db = new Mysql();
	$db->query("
SELECT
	msg.metodo,
	msg.destino,
	msg.data_hora_status,
	usr.nome,
	usr.login
FROM
	gelic_mensagens_log AS msg 
	INNER JOIN gelic_admin_usuarios AS usr ON usr.id = msg.id_destino
WHERE
	msg.id_pc_mensagem = $pId_pc_mensagem
ORDER BY
	msg.id DESC");

	if ($db->nf() > 0)
	{
		$oEmail = '';
		$oSms = '';

		while ($db->nextRecord())
		{
			if ($db->f("metodo") == M_EMAIL)
				$oEmail .= '<div class="pc-envio-detalhe">
					<span>'.mysqlToBr(substr($db->f("data_hora_status"), 0, 10)).' '.substr($db->f("data_hora_status"), 11).'</span>
					<span>'.utf8_encode($db->f("nome")).'</span>
					<span>('.utf8_encode($db->f("login")).')</span>
					<span>'.$db->f("destino").'</span>
				</div>';
			else if ($db->f("metodo") == M_SMS)
				$oSms .= '<div class="pc-envio-detalhe">
					<span>'.mysqlToBr(substr($db->f("data_hora_status"), 0, 10)).' '.substr($db->f("data_hora_status"), 11).'</span>
					<span>'.utf8_encode($db->f("nome")).'</span>
					<span>('.utf8_encode($db->f("login")).')</span>
					<span>'.$db->f("destino").'</span>
				</div>';
		}

		$oOutput = '';
		if (strlen($oEmail) > 0)
			$oOutput .= '<div style="text-align:left;"><img src="img/mon-icon2.png"></div>'.$oEmail;

		if (strlen($oSms) > 0)
		{
			if (strlen($oEmail) > 0)
				$oOutput .= '<div style="height: 20px;"></div>';

			$oOutput .= '<div style="text-align: left;"><img src="img/mon-icon3.png"></div>'.$oSms;
		}
		
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $oOutput;
	}
	else
	{
		$aReturn[0] = 9;
	}
}

echo json_encode($aReturn);

?>
