<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	//--- Ajustar inside_id se for Representante ---
	if ($sInside_tipo == 4) //REP
		$sInside_id = $_SESSION[SESSION_ID_DN];
	//----------------------------------------------

	$pId_licitacao = intval($_POST["id-licitacao"]);
	$db = new Mysql();

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		//***********************************************************************
		$grupo = 4;
		$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
		if ($db->nextRecord())
			$grupo = 3;

		//pegar status
		$db->query("SELECT id_status FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo = $grupo ORDER BY id_aba DESC LIMIT 1");
		$db->nextRecord();
		$dId_status = $db->f("id_status");
		//***********************************************************************
	}
	else if ($sInside_tipo == 1) //BO
	{
		//***********************************************************************
		//pegar status
		$db->query("SELECT id_status FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo = 2 ORDER BY id_aba DESC LIMIT 1");
		$db->nextRecord();
		$dId_status = $db->f("id_status");
		//***********************************************************************
	}


	$oOutput = '';
	$db->query("
SELECT
	lic.fase,
	hdec.id AS declinou
FROM
	gelic_licitacoes AS lic
	LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
	LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
WHERE
	lic.id = $pId_licitacao AND
	lic.deletado = 0 AND
	his.id IS NULL");
	if ($db->nextRecord())
	{
		$declinou = false;
		if (($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) && $db->f("declinou") > 0)
			$declinou = true;

		$dFase = $db->f("fase");

		if ($declinou)
		{
			$status = '<span style="color: #ffffff; background-color: #ff0000; padding: 2px 6px; float: left;">SEM INTERESSE</span>';
		}
		else
		{
			if (in_array($dId_status, array(8,19))) // APL Aprovada, APL Reprovada
			{
				if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
					$db->query("SELECT enviadas, aprovadas, reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $pId_licitacao AND id_cliente = $cliente_parent");
				else
					$db->query("SELECT	SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $pId_licitacao");

				$db->nextRecord();

				$status = '';
				if ($dFase == 1)
				{
					if ($db->f("enviadas") > 0)
						$status .= '<span style="color: #ffffff; background-color: #827c7c; padding: 2px 6px; float: left;">APL em Análise - GELIC ('.$db->f("enviadas").')</span>';
				}
				else
				{
					if ($db->f("enviadas") > 0)
						$status .= '<span style="color: #050000; background-color: #ffe600; padding: 2px 6px; float: left;">APL Aguardando Aprovação ('.$db->f("enviadas").')</span>';
				}

				if ($db->f("aprovadas") > 0)
					$status .= '<span style="color: #ffffff; background-color: #00b318; padding: 2px 6px; float: left;">APL Aprovada ('.$db->f("aprovadas").')</span>';

				if ($db->f("reprovadas") > 0)
					$status .= '<span style="color: #ffffff; background-color: #ed0000; padding: 2px 6px; float: left;">APL Reprovada ('.$db->f("reprovadas").')</span>';

			}
			else
			{
				$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = $dId_status");
				$db->nextRecord();
				$status = '<span style="color: #'.$db->f("cor_texto").'; background-color: #'.$db->f("cor_fundo").'; padding: 2px 6px; float: left;">'.utf8_encode($db->f("descricao")).'</span>';
			}
		}

		$aReturn[1] = $status;
	}
	else
	{
		$aReturn[1] = 'Erro.';
	}
}
echo json_encode($aReturn);

?>
