<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'');
if (isInside())
{
	$pId_licitacao = intval($_POST["f-id-licitacao"]);

	$db = new Mysql();

	$oOutput = '';
	$db->query("SELECT 
					his.id,
					his.tipo,
					his.id_sender,
					his.id_valor_1,
					his.id_valor_2,
					his.data_hora,
					his.texto,
					his.nome_arquivo,
					his.arquivo,
					his.id_apl,
					usr.nome AS usuario_admin,
					cli.tipo AS tipo_cliente,
					cli.id_parent,
					cli.nome AS nome_cliente,
					clidn.nome AS nome_dn,
					vcli.id_parent AS vid_parent,
					vcli.nome AS vnome_cliente,
					vclidn.nome AS vnome_dn,
					mot.descricao AS motivo,
					smot.descricao AS submotivo,
					(SELECT id FROM gelic_licitacoes_apl_historico WHERE id = his.id_valor_1) AS id_ahis
				FROM 
					gelic_historico AS his 
					LEFT JOIN gelic_admin_usuarios AS usr ON usr.id = his.id_sender

					LEFT JOIN gelic_clientes AS cli ON cli.id = his.id_sender
					LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent

					LEFT JOIN gelic_clientes AS vcli ON vcli.id = his.id_valor_1
					LEFT JOIN gelic_clientes AS vclidn ON vclidn.id = vcli.id_parent

					LEFT JOIN gelic_motivos AS mot ON mot.id = his.id_valor_1
					LEFT JOIN gelic_motivos AS smot ON smot.id = his.id_valor_2

				    LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id = his.id_apl
				WHERE 
					his.id_licitacao = $pId_licitacao AND
				 	his.tipo IN (1,2,13,22,23,31,32,33,34,36,39,41,42,43,44,45)
				ORDER BY 
					his.id");
	while ($db->nextRecord())
	{
		if ($db->f("tipo") == 1) //1: Mensagem admin para cliente
		{
			$oOutput .= '<div class="content-inside h-msg-enviada">
				<img class="fl" src="img/reply.png" style="margin-top: 8px;">
				<span class="t13 fl lh-30 ml-10">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">'.utf8_encode($db->f("texto")).'</div>';
			
			if (strlen($db->f("arquivo")) > 0)
			{
				$oOutput .= '<div style="overflow:hidden;margin-bottom:8px;">
					<span class="t13 gray-88 fl lh-24 italic">Arquivo:</span>
					<span class="t13 red fl ml-8" style="line-height: 22px; border: 1px solid #999999; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					<a class="bt-style-2 fl" href="'.linkFileBucket("vw/licchat/".$db->f("arquivo")).'" target="_blank" style="height: 24px; line-height: 22px;">Ver Anexo</a>
				</div>';
			}

			$oOutput .= '</div>';
		}
		else if ($db->f("tipo") == 2) //2: Mensagem cliente para admin
		{
			if ($db->f("tipo_cliente") == 1)
				$dNome = 'BO: '.$db->f("nome_cliente");
			else
				$dNome = $db->f("nome_cliente");

			if ($db->f("id_parent") > 0)
				$dNome .= ' / '.$db->f("nome_dn");

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($dNome).')</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">'.utf8_encode($db->f("texto")).'</div>';
			
			if (strlen($db->f("arquivo")) > 0)
			{
				$oOutput .= '<div style="overflow:hidden;margin-bottom:8px;">
					<span class="t13 gray-88 fl lh-24 italic">Arquivo:</span>
					<span class="t13 red fl ml-8" style="line-height: 22px; border: 1px solid #999999; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					<a class="bt-style-2 fl" href="'.linkFileBucket("vw/licchat/".$db->f("arquivo")).'" target="_blank" style="height: 24px; line-height: 22px;">Ver Anexo</a>
				</div>';
			}

			$oOutput .= '</div>';
		}
		else if ($db->f("tipo") == 13) //13: Troca de fase
		{
			if ($db->f("id_valor_1") == 0 && $db->f("id_valor_2") == 1)
			{
				$oOutput .= '<div class="content-inside h-msg-recebida">
					<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
					<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
					<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
					<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;"><span class="bold cor-inicio">Início do prazo de 24hs para envio da APL exclusivo para ADVE/POOL</span></div>
				</div>';
			}
			else if ($db->f("id_valor_1") == 0 && $db->f("id_valor_2") == 2)
			{
				//pegar regiao
				$db->query("
SELECT
	uf.regiao
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
    INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	lic.id = $pId_licitacao",1);
				$db->nextRecord(1);

				$oOutput .= '<div class="content-inside h-msg-recebida">
					<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
					<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
					<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
					<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;"><span class="bold cor-inicio">Início do prazo de 24hs para envio da '.utf8_encode($db->f("regiao",1)).'</span></div>
				</div>';
			}
			else if ($db->f("id_valor_1") == 1 && $db->f("id_valor_2") == 2)
			{
				//pegar regiao
				$db->query("
SELECT
	uf.regiao
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
    INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	lic.id = $pId_licitacao",1);
				$db->nextRecord(1);

				$oOutput .= '<div class="content-inside h-msg-recebida">
					<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
					<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
					<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
					<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;"><span class="bold cor-fim">Fim do prazo de envio ADVE/POOL</span> => <span class="bold cor-inicio">Início do prazo de 24hs para envio da '.utf8_encode($db->f("regiao",1)).'</span></div>
				</div>';
			}
			else if ($db->f("id_valor_1") == 2 && $db->f("id_valor_2") == 3)
			{
				//pegar regiao
				$db->query("
SELECT
	uf.regiao
FROM
	gelic_licitacoes AS lic
    INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
    INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	lic.id = $pId_licitacao",1);
				$db->nextRecord(1);

				$oOutput .= '<div class="content-inside h-msg-recebida">
					<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
					<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
					<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
					<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;"><span class="bold cor-fim">Fim do prazo de envio da '.utf8_encode($db->f("regiao",1)).'</span> => <span class="bold cor-inicio">Início do prazo de envio (Brasil)</span></div>
				</div>';
			}
		}
		else if ($db->f("tipo") == 22) //22: Cliente não tem interesse
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <a class="gray-88">('.utf8_encode($db->f("submotivo")).')</a>';

			$msg = '';
			if (strlen($db->f("texto")) > 0)
				$msg = '<br><span class="normal">'.nl2br(utf8_encode($db->f("texto"))).'</span>';

			$dNome = $db->f("nome_cliente");
			if ($db->f("id_parent") > 0)
				$dNome .= ' / '.$db->f("nome_dn");

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($dNome).')</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">
					<span class="bold">Cliente não tem interesse em participar desta licitação</span><br>
					<span class="italic"><a class="bold">Motivo:</a> '.utf8_encode($db->f("motivo")).$subm.$msg.'</span>
				</div>
				<div style="overflow:hidden;margin-bottom:8px;">
					<a class="bt-style-2 fl" href="javascript:void(0);" onclick="reverterDeclinio('.$db->f("id_sender").',false);" style="height: 24px; line-height: 22px;">Reverter</a>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 23) //23: Cliente não tem interesse (revertido)
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <a class="gray-88">('.utf8_encode($db->f("submotivo")).')</a>';

			$msg = '';
			if (strlen($db->f("texto")) > 0)
				$msg = '<br><span class="normal">'.nl2br(utf8_encode($db->f("texto"))).'</span>';

			$dNome = $db->f("nome_cliente");
			if ($db->f("id_parent") > 0)
				$dNome .= ' / '.$db->f("nome_dn");

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($dNome).')</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">
					<span class="bold">Cliente não tem interesse em participar desta licitação <a class="italic normal">(revertido)</a></span><br>
					<span class="italic"><a class="bold">Motivo:</a> '.utf8_encode($db->f("motivo")).$subm.$msg.'</span>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 31) //31: Admin encerrou licitação
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <a class="gray-88">('.utf8_encode($db->f("submotivo")).')</a>';

			$oOutput .= '<div class="content-inside h-msg-enviada">
				<img class="fl" src="img/reply.png" style="margin-top: 8px;">
				<span class="t13 fl lh-30 ml-10">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">
					<span class="bold">Licitação encerrada.</span><br>
					<span class="italic"><a class="bold">Motivo:</a> '.utf8_encode($db->f("motivo")).$subm.'</span>
				</div>';

			if ($db->Row[0] == $db->nf())
			{
				$oOutput .= '<div style="overflow:hidden;margin-bottom:8px;">
					<a class="bt-style-2 fl" href="javascript:void(0);" onclick="reverterEncerramento(false);" style="height: 24px; line-height: 22px;">Reverter</a>
				</div>';
			}

			$oOutput .= '</div>';
		}
		else if ($db->f("tipo") == 32) //32: Admin reverteu o encerramento da licitação
		{
			$oOutput .= '<div class="content-inside h-msg-enviada">
				<img class="fl" src="img/reply.png" style="margin-top: 8px;">
				<span class="t13 fl lh-30 ml-10">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">
					<span class="bold">Encerramento revertido.</span>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 33) //33: Admin reverteu o desinteresse do cliente
		{
			$dNome = $db->f("vnome_cliente");
			if ($db->f("vid_parent") > 0)
				$dNome .= ' / '.$db->f("vnome_dn");

			$oOutput .= '<div class="content-inside h-msg-enviada">
				<img class="fl" src="img/reply.png" style="margin-top: 8px;">
				<span class="t13 fl lh-30 ml-10">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">
					<span class="bold">Declínio revertido.</span><br>
					<span class="gray-88">'.utf8_encode($dNome).'</span>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 34) //34: Admin prorrogou a abertura da licitação
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <a class="gray-88">('.utf8_encode($db->f("submotivo")).')</a>';

			$oOutput .= '<div class="content-inside h-msg-enviada">
				<img class="fl" src="img/reply.png" style="margin-top: 8px;">
				<span class="t13 fl lh-30 ml-10">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">
					<span class="bold">Licitação prorrogada.</span><br>
					'.utf8_encode($db->f("texto")).'<br>
					<span class="italic"><a class="bold">Motivo:</a> '.utf8_encode($db->f("motivo")).$subm.'</span>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 36) //36: Admin alterou o status manualmente
		{
			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;"><span class="bold gray-60">Status alterado(s) manualmente.</span> [ <a class="ablue" href="javascript:void(0);" onclick="tipo36('.$db->f("id").');">Ver detalhes...</a> ]</div>
			</div>';
		}
		else if ($db->f("tipo") == 39) //39: Admin encerrou licitação (revertido)
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <a class="gray-88">('.utf8_encode($db->f("submotivo")).')</a>';

			$oOutput .= '<div class="content-inside h-msg-enviada">
				<img class="fl" src="img/reply.png" style="margin-top: 8px;">
				<span class="t13 fl lh-30 ml-10">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span>

				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora"),11).'</span>
				<span class="t13 fl lh-30 ml-20 gray-88 italic bold">('.utf8_encode($db->f("usuario_admin")).')</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;">
					<span class="bold">Licitação encerrada. <a class="italic normal">(revertido)</a></span><br>
					<span class="italic"><a class="bold">Motivo:</a> '.utf8_encode($db->f("motivo")).$subm.'</span>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 41) //41: APL Enviada
		{
			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora",1),11).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>
				<div class="t13 lh-17 clear" style="padding: 4px 0 12px 0;"><a class="bold orange">APL enviada</a> por <a class="bold italic">'.$por.'</a> de <a class="red">'.$db->f("ip",1).'</a></div>
			</div>';
		}
		else if ($db->f("tipo") == 42) //42: APL Aprovada
		{
			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora",1),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>';

			$bo = '<span class="t13"><a class="bold green">APL aprovada</a> por <a class="bold italic">'.$por.'</a> de <a class="red">'.$db->f("ip",1).'</a></span><br>';

			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 41 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$oOutput .= '<div class="t12 lh-17 clear" style="padding: 4px 0 12px 0;">
'.$bo.'Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL enviada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip",1).'</span></div></div>';
		}
		else if ($db->f("tipo") == 43) //43: APL Reprovada
		{
			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora",1),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>';

			$bo = '<span class="t13"><a class="bold red">APL reprovada</a> por <a class="bold italic">'.$por.'</a> de <a class="red">'.$db->f("ip",1).'</a></span><br>';

			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 41 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$oOutput .= '<div class="t12 lh-17 clear" style="padding: 4px 0 12px 0;">
'.$bo.'Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL enviada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip",1).'</span></div></div>';

		}
		else if ($db->f("tipo") == 44) //44: APL Aprovação Revertida
		{
			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	ahis.texto,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$dTexto = '';
			if (strlen($db->f("texto",1)) > 0)
				$dTexto = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($db->f("texto",1)));

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora",1),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>';

			$bo = '<span class="t13"><a class="bold blue">Aprovação da APL revertida</a> por <a class="bold italic">'.$por.'</a> de <a class="red">'.$db->f("ip",1).'</a></span>'.$dTexto.'<br>';

			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 42 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$oOutput .= '<div class="t12 lh-17 clear" style="padding: 4px 0 12px 0;">
'.$bo.'Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL aprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip",1).'</span></div></div>';

		}
		else if ($db->f("tipo") == 45) //45: APL Reprovação Revertida
		{
			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	ahis.texto,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = ".$db->f("id_ahis"),1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$dTexto = '';
			if (strlen($db->f("texto",1)) > 0)
				$dTexto = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($db->f("texto",1)));

			$oOutput .= '<div class="content-inside h-msg-recebida">
				<span class="t13 fl lh-30">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span>
				<span class="t13 fl lh-30 ml-10 gray-88">'.substr($db->f("data_hora",1),11).'</span>
				<span class="t13 fr lh-30 gray-88">'.timeAgo($db->f("data_hora")).'</span>';

			$bo = '<span class="t13"><a class="bold blue">Reprovação da APL revertida</a> por <a class="bold italic">'.$por.'</a> de <a class="red">'.$db->f("ip",1).'</a></span>'.$dTexto.'<br>';

			$db->query("
SELECT 
	ahis.ip,
	ahis.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome,
	lot.lote,
	itm.item
FROM
	gelic_licitacoes_apl_historico AS ahis
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = ahis.id_apl
    INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
    INNER JOIN gelic_clientes AS cli ON cli.id = ahis.id_cliente
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	ahis.id = (
		SELECT 
			id_valor_1 
		FROM 
			gelic_historico 
		WHERE 
			id < ".$db->f("id")." AND 
			id_apl = ".$db->f("id_apl")." AND 
			tipo = 43 
		ORDER BY 
			id DESC 
		LIMIT 1)",1);
			$db->nextRecord(1);
			$por = utf8_encode($db->f("nome",1));
			if ($db->f("id_parent",1) > 0)
				$por .= ' (DN: '.utf8_encode($db->f("dn_nome",1)).')';

			$oOutput .= '<div class="t12 lh-17 clear" style="padding: 4px 0 12px 0;">
'.$bo.'Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL reprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="red">'.$db->f("ip",1).'</span></div></div>';

		}

	}

	if (strlen($oOutput) > 0)
		$aReturn[1] = '<div style="position: absolute; left: 0; top: 20px; width: 100%; height: 1px; border-top: 1px solid #cccccc;"></div>'.$oOutput;
	else
		$aReturn[1] = '';
}
echo json_encode($aReturn);

?>
