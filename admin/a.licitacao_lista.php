<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	$sInside_id = $_SESSION[SESSION_ID];

	if (!in_array("lic_visualizar", $xAccess))
	{
		$oRows = '<div class="content-inside" style="padding-top: 20px;">
			<div class="full-row" style="padding: 0 0 2px 0; border-bottom: 1px solid #666666;">
				<span class="t18 bold lh-30 fl red">Acesso Restrito!</span>
			</div>
			<div class="t14" style="position: relative; margin: 40px auto; width: 500px; text-align: center; border: 1px solid #999999; padding: 20px 0;">
				<span class="bold">LICITAÇÕES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span><br><br><br>
				<a class="bt-style-1" href="javascript:window.history.back();" style="display: inline-block;">Ok</a>
			</div>
		</div>';

		$pStart = 0;
		$dTotal = 0;
		$dh = 'none';

		//salvar tab para este usuario
		$pId_aba = intval($_POST["id_aba"]);
		$pMais = intval($_POST["mais"]);
		
		if ($pMais == 0)
		{
			$db = new Mysql();
			$db->query("UPDATE gelic_admin_usuarios_config SET valor = '$pId_aba' WHERE id_admin_usuario = $sInside_id AND config = 'tab'");
		}
	}
	else
	{
		$pId_aba = intval($_POST["id_aba"]);
		$pStart = intval($_POST["start"]);
		$pMais = intval($_POST["mais"]);

		$aEstados = array();
		$aEstados["AC"] = utf8_decode("Região 6");
		$aEstados["AL"] = utf8_decode("Região 5");
		$aEstados["AP"] = utf8_decode("Região 6");
		$aEstados["AM"] = utf8_decode("Região 6");
		$aEstados["BA"] = utf8_decode("Região 5");
		$aEstados["CE"] = utf8_decode("Região 5");
		$aEstados["DF"] = utf8_decode("Região 6");
		$aEstados["ES"] = utf8_decode("Região 4");
		$aEstados["GO"] = utf8_decode("Região 6");
		$aEstados["MA"] = utf8_decode("Região 6");
		$aEstados["MT"] = utf8_decode("Região 6");
		$aEstados["MS"] = utf8_decode("Região 6");
		$aEstados["MG"] = utf8_decode("Região 4");
		$aEstados["PA"] = utf8_decode("Região 6");
		$aEstados["PB"] = utf8_decode("Região 5");
		$aEstados["PR"] = utf8_decode("Região 3");
		$aEstados["PE"] = utf8_decode("Região 5");
		$aEstados["PI"] = utf8_decode("Região 5");
		$aEstados["RJ"] = utf8_decode("Região 4");
		$aEstados["RN"] = utf8_decode("Região 5");
		$aEstados["RS"] = utf8_decode("Região 3");
		$aEstados["RO"] = utf8_decode("Região 6");
		$aEstados["RR"] = utf8_decode("Região 6");
		$aEstados["SC"] = utf8_decode("Região 3");
		$aEstados["SP"] = utf8_decode("Região 1/2");
		$aEstados["SE"] = utf8_decode("Região 5");
		$aEstados["TO"] = utf8_decode("Região 6");


		$tRow = '<div class="content-inside mt-2" style="border: 1px solid #aaaaaa; background-color: #ebebeb; min-height: 100px;">
			<div class="dep-box {{CLR}}">
				<span class="t12">{{DEP}}</span>
				<span class="t11 mt-10">{{DIAS}}</span>
				<a href="a.licitacao_abrir.php?id={{ID}}"></a>
			</div>
			<a class="row-item-conteudo" href="a.licitacao_abrir.php?id={{ID}}">
				<span class="row-item-cliente bold lh-24 t13">{{CLI}}</span>
				<span class="row-item-objeto t13 gray-33">{{OBJ}}</span>
				<span class="row-item-orgao t12 italic mt-10"><span>{{ORG}}, {{CID}} - {{UF}}</span></span>
				<span class="row-item-modalidade t12 gray-88">{{MOD}}</span>
				<span class="row-item-num t12">Nº da Licitação: {{NUM}}</span>
			</a>
			<a class="md-icon" href="a.licitacao_abrir.php?id={{ID}}" style="right: 79px;">{{MSG_D}}<img src="img/icon_{{MSG_I}}.png" title="{{MSG_T}}"></a>
			<span class="t11 verdana row-item-valor">{{VLR}}</span>
			<span class="row-item-numero dark-red">LIC {{ID}}</span>
			{{STA}}
		</div>';

		$tRow_todas = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede; font-size:13px;">
			<a class="ilnk abs lh-30" href="a.licitacao_abrir.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">&nbsp;</a>
			<span style="display:inline-block;line-height:30px;float:left;width:60px;text-align:center;border-right:1px solid #dedede;box-sizing:border-box;font-weight:bold;color:#c90000;">{{ID}}</span>
			<span style="display:inline-block;line-height:30px;float:left;width:300px;text-align:left;border-right:1px solid #dedede;padding-left:6px;box-sizing:border-box;">{{ORG}}</span>
			<span style="display:inline-block;line-height:30px;float:left;width:140px;text-align:left;border-right:1px solid #dedede;padding-left:6px;box-sizing:border-box;">{{DA}}&nbsp;&nbsp;<a style="color:#888888;">{{HA}}</a></span>
			<span style="display:inline-block;line-height:30px;float:left;width:160px;text-align:left;border-right:1px solid #dedede;padding-left:6px;box-sizing:border-box;">{{NUM}}</span>
			{{STA}}
		</div>';

		$oRows = "";
		$db = new Mysql();

		if ($pMais == 0)
		{
			//salvar tab para este usuario
			$db->query("UPDATE gelic_admin_usuarios_config SET valor = '$pId_aba' WHERE id_admin_usuario = $sInside_id AND config = 'tab'");

			// ----------------------------------------------------------------
			// CONTAR TOTAL DE LICITACOES NA ABA
			// ----------------------------------------------------------------
			if ($pId_aba == 16) //expiradas
				$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes WHERE deletado = 0 AND datahora_abertura < NOW()");
			else if ($pId_aba == 17) //todas
				$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		LEFT JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
	WHERE
		lic.deletado = 0 AND
	    (itm.id IS NULL OR itm.id_tipo_veiculo <> 8)
	GROUP BY
		lic.id
) AS t");
			else if ($pId_aba == 15) //nao pertinente
				$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
	WHERE
		lic.deletado = 0
	GROUP BY
		lic.id
	HAVING
		SUM(itm.id_tipo_veiculo = 8) = SUM(itm.id > 0)
) AS t");
			else //outras abas
				$db->query("
SELECT COUNT(*) AS total FROM (
	SELECT
		lic.id
	FROM
		gelic_licitacoes AS lic
		INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND grupo = 1 AND id_aba = $pId_aba
		LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
	WHERE
	    lic.deletado = 0 AND
		his.id IS NULL
	GROUP BY
		lic.id
) AS t");
			$db->nextRecord();
			$dTotal = intval($db->f("total"));
			// ----------------------------------------------------------------
		}


		$rows_per_load = LPP;

		$select = "lic.id, 
			lic.orgao, 
			lic.objeto, 
			lic.datahora_abertura, 
			lic.datahora_entrega, 
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			lic.numero,
			cid.nome AS cidade, 
			cid.uf AS uf,
			mdl.nome AS modalidade,
			labas.id_status,
			(SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo,
			(SELECT MAX(id) FROM gelic_historico WHERE id_licitacao = lic.id AND tipo IN (1,2)) AS mensagem_global";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 1
			INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
			INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade";

		$where = "lic.deletado = 0";
		$orderby = "lic.datahora_abertura DESC";
		$having = "";


		if ($pId_aba == 16) //expiradas
		{
			$where .= " AND datahora_abertura < NOW()";
		}
		else if ($pId_aba == 17) //todas
		{
			$rows_per_load = 50;
			$from .= " LEFT JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id";
			$where .= " AND (itm.id IS NULL OR itm.id_tipo_veiculo <> 8)";
			$orderby = "lic.id DESC";
		}
		else if ($pId_aba == 15) //nao pertinentes
		{
			$from .= " INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id";
			$having = " HAVING SUM(itm.id_tipo_veiculo = 8) = SUM(itm.id > 0)";
		}
		else //outras abas
		{
			$from .= " LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";
			$where .= " AND labas.id_aba = $pId_aba AND his.id IS NULL";
			$orderby = "lic.datahora_abertura";
		}

		//if ($pId_aba == 16 && $pMais == 0)
			//logThis("SELECT $select FROM $from WHERE $where GROUP BY lic.id$having ORDER BY $orderby LIMIT $pStart,$rows_per_load");

		$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id$having ORDER BY $orderby LIMIT $pStart,$rows_per_load");

		$pStart += $db->nf();
		if ($db->nf() == 0) $pStart = 0;
		while ($db->nextRecord())
		{
			if ($pId_aba == 17) //todas
				$tTmp = $tRow_todas;
			else
				$tTmp = $tRow;


			if (in_array($db->f("id_status"), array(8,19))) // APL Aprovada, APL Reprovada
			{
				$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = ".$db->f("id"),1);
				$db->nextRecord(1);

				if ($pId_aba == 17) //todas
				{
					$status = '<div style="display:inline-block;height:30px;line-height:30px;float:left;width:400px;text-align:center;background-color: #dedede;font-size:11px;">';

					if ($db->f("fase") == 1)
					{
						if ($db->f("enviadas",1) > 0)
							$status .= '<span style="display:inline-block; color: #ffffff; background-color:#827c7c; padding: 0 6px; line-height:30px;">APL em Análise - GELIC ('.$db->f("enviadas",1).')</span>';
					}
					else
					{
						if ($db->f("enviadas",1) > 0)
							$status .= '<span style="display:inline-block; color: #050000; background-color:#ffe600; padding: 0 6px; line-height:30px;">APL Aguardando Aprovação ('.$db->f("enviadas",1).')</span>';
					}

					if ($db->f("aprovadas",1) > 0)
						$status .= '<span style="display:inline-block; color: #ffffff; background-color:#00b318; padding: 0 6px; line-height:30px;">APL Aprovada ('.$db->f("aprovadas",1).')</span>';

					if ($db->f("reprovadas",1) > 0)
						$status .= '<span style="display:inline-block; color: #ffffff; background-color:#ed0000; padding: 0 6px; line-height:30px;">APL Reprovada ('.$db->f("reprovadas",1).')</span>';

					$status .= '</div>';
				}
				else
				{
					$status = '<div class="row-item-status" style="background-color:#dedede;font-size: 13px;">';

					if ($db->f("fase") == 1)
					{
						if ($db->f("enviadas",1) > 0)
							$status .= '<span style="background-color:#827c7c;color:#ffffff;line-height:26px;display:inline-block;padding:0 10px;">APL em Análise - GELIC ('.$db->f("enviadas",1).')</span>';
					}
					else
					{
						if ($db->f("enviadas",1) > 0)
							$status .= '<span style="background-color:#ffe600;color:#050000;line-height:26px;display:inline-block;padding:0 10px;">APL Aguardando Aprovação ('.$db->f("enviadas",1).')</span>';
					}

					if ($db->f("aprovadas",1) > 0)
						$status .= '<span style="background-color:#00b318;color:#ffffff;line-height:26px;display:inline-block;padding:0 10px;">APL Aprovada ('.$db->f("aprovadas",1).')</span>';
	
					if ($db->f("reprovadas",1) > 0)
						$status .= '<span style="background-color:#ed0000;color:#ffffff;line-height:26px;display:inline-block;padding:0 10px;">APL Reprovada ('.$db->f("reprovadas",1).')</span>';

					$status .= '</div>';
				}
			}
			else
			{
				$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = ".$db->f("id_status"),1);
				$db->nextRecord(1);

				if ($pId_aba == 17) //todas
					$status = '<div style="display:inline-block;height:30px;line-height:30px;float:left;width:400px;text-align:center;background-color: #'.$db->f("cor_fundo",1).';"><span style="color: #'.$db->f("cor_texto",1).';">'.utf8_encode($db->f("descricao",1)).'</span></div>';
				else
					$status = '<span class="row-item-status" style="color: #'.$db->f("cor_texto",1).'; background-color: #'.$db->f("cor_fundo",1).';">'.utf8_encode($db->f("descricao",1)).'</span>';
			}

			$tTmp = str_replace("{{STA}}", $status, $tTmp);
			$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
			if ($db->f("numero") == '')
				$tTmp = str_replace("{{NUM}}", '<span class="gray-88 italic">- não informado -</span>', $tTmp);
			else
				$tTmp = str_replace("{{NUM}}", utf8_encode($db->f("numero")), $tTmp);

			if ($pId_aba == 17)
			{
				$tTmp = str_replace("{{DA}}", mysqlToBr(substr($db->f("datahora_abertura"), 0, 10)), $tTmp);
				$tTmp = str_replace("{{HA}}", substr($db->f("datahora_abertura"), 11, 5), $tTmp);
				$tTmp = str_replace("{{ORG}}", utf8_encode(clipString($db->f("orgao"), 40)), $tTmp);
			}
			else
			{
				$dLimite = segundosConv($db->f("limite"));
				$tTmp = str_replace("{{DEP}}", mysqlToBr(substr($db->f("datahora_entrega"),0,10)), $tTmp);
				$tTmp = str_replace("{{ORG}}", utf8_encode($db->f("orgao")), $tTmp);
				$tTmp = str_replace("{{OBJ}}", utf8_encode(clipString(strip_tags(stripslashes($db->f("objeto"))), 220)), $tTmp);
				$tTmp = str_replace("{{CID}}", utf8_encode($db->f("cidade")), $tTmp);
				$tTmp = str_replace("{{UF}}", $db->f("uf"), $tTmp);
				$tTmp = str_replace("{{MOD}}", utf8_encode($db->f("modalidade")), $tTmp);
				if ($db->f("valor") == 0.00)
					$tTmp = str_replace("{{VLR}}", '<a class="gray-aa">Não Informado</a>', $tTmp);
				else
					$tTmp = str_replace("{{VLR}}", "R$ ".number_format($db->f("valor"),2,',','.'), $tTmp);
	
				$tTmp = str_replace("{{DIAS}}", '<br><br><span style="font-size: 11px;">Prazo Limite<br>'.$dLimite["h"].'h '.$dLimite["m"].'m</span>', $tTmp);

				if ($db->f("fase") == 1)
				{
					$DNs = '';
					$db->query("SELECT nome FROM gelic_clientes WHERE id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = ".$db->f("id").") ORDER BY dn",1);
					while ($db->nextRecord(1))
						$DNs .= '<br>'.utf8_encode($db->f("nome",1));

					$tTmp = str_replace("{{CLI}}", substr($DNs, 4), $tTmp);
				}
				else if ($db->f("fase") == 2)
				{
					$tTmp = str_replace("{{CLI}}", utf8_encode($aEstados[$db->f("uf")]), $tTmp);
				}
				else
				{
					$tTmp = str_replace("{{CLI}}", "Brasil", $tTmp);
				}


				// **** ICONE DE MENSAGEM ***
				if ($db->f("mensagem_global") > 0)
				{
					//buscar historico
					$db->query("SELECT data_hora FROM gelic_historico WHERE id = ".$db->f("mensagem_global"),1);
					$db->nextRecord(1);
					$tTmp = str_replace("{{MSG_D}}", timeAgo($db->f("data_hora",1)), $tTmp);
				}
				else
					$tTmp = str_replace("{{MSG_D}}", "---", $tTmp);

				$tTmp = str_replace("{{MSG_T}}", "Mensagens", $tTmp);
				$tTmp = str_replace("{{MSG_I}}", "msg0", $tTmp);
				// **** FIM ICONE DE MENSAGEM ***


				if ($db->f("ultimo_tipo") != 31) //nao encerrada
				{
					if ($db->f("data_abertura_dias") < 0)
						$tTmp = str_replace("{{CLR}}", "dep-box-black", $tTmp); //black
					else
					{
						if ($dLimite["h"] >= 2)
							$tTmp = str_replace("{{CLR}}", "dep-box-green", $tTmp); //green
						else if ($dLimite["h"] > 0 && $dLimite["h"] < 2)
							$tTmp = str_replace("{{CLR}}", "dep-box-bright-red", $tTmp); //bright red
						else if ($dLimite["h"] <= 0)
							$tTmp = str_replace("{{CLR}}", "dep-box-dark-red", $tTmp); //red
					}
				}
				else
					$tTmp = str_replace("{{CLR}}", "dep-box-gray", $tTmp); //gray
			}
			
			$oRows .= $tTmp;
		}

		$dh = 'block';
	}

	if ($pMais == 0)
	{
		$aReturn[0] = 1; //sucesso
		
		if ($pId_aba == 17) //todas
			$aReturn[1] = '
			<div class="content-inside" style="display: '.$dh.'; background-color: #ababab; height: 26px; margin-top: 2px;">
				<span class="bold abs lh-26" style="left: 0; width:59px; text-align:center;">LIC</span>
				<span class="bold abs lh-26" style="left: 66px;">Órgão</span>
				<span class="bold abs lh-26" style="left: 366px;">Data/Hora Abertura</span>
				<span class="bold abs lh-26" style="left: 506px;">Nº da Licitação</span>
				<span class="bold abs lh-26" style="right: 0; width:400px; text-align: center;">Status</span>
			</div>
			<div id="mais">'.$oRows.'</div>';
		else
			$aReturn[1] = '
			<div class="content-inside" style="display: '.$dh.'; background-color: #ababab; height: 26px; margin-top: 2px;">
				<span class="bold abs lh-26" style="left: 8px;">D.E.P.</span>
				<span class="bold abs lh-26" style="left: 96px;">Cliente/Objeto/Órgão/Localização/Modalidade</span>
				<span class="bold abs lh-26" style="right: 220px;">Valor Estimado</span>
			</div>
			<div id="mais">'.$oRows.'</div>';

		$aReturn[2] = $pStart;
		$aReturn[3] = 'Licitações (<a class="red">'.$dTotal.'</a>)';
		$aReturn[4] = (int)in_array("lic_visualizar", $xAccess);
		$aReturn[5] = $dTotal;
	}
	else
	{
		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $oRows;
		$aReturn[2] = $pStart;
	}
} 

echo json_encode($aReturn);

?>
