<?php
require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'',0);
if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$pId_licitacao = intval($_POST["id_licitacao"]);
	$db = new Mysql();

	$oOutput = '';
	$db->query("
SELECT 
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
 	his.tipo IN (1,2,13,22,23,31,32,33,34,39,41,42,43,44,45)
ORDER BY 
	his.id");
	while ($db->nextRecord())
	{
		if ($db->f("tipo") == 1) //1: Mensagem admin para cliente
		{
			$oOutput .= '<div class="fourcont">
				<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt-fa">
					<p>'.utf8_encode($db->f("texto")).'</p>';

			if (strlen($db->f("arquivo")) > 0)
			{
				$oOutput .= '<p style="overflow: hidden; margin-top: 17px;">
					<span class="t13 gray-88 fl lh-22 italic">Arquivo:</span>
					<span class="t13 t-orange fl ml-8 lh-20" style="border: 1px solid #bebebe; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					<a class="bt-style-2 fl" href="'.linkFileBucket("vw/licchat/".$db->f("arquivo")).'" target="_blank" style="font-size: 12px; height: 22px; line-height:22px;">Abrir Anexo</a>
				</p>';
			}

			$oOutput .= '</div></div>';
		}
		else if ($db->f("tipo") == 2) //2: Mensagem cliente para admin
		{
			if ($db->f("tipo_cliente") == 1)
				$dNome = 'BO: '.$db->f("nome_cliente");
			else
				$dNome = $db->f("nome_cliente");

			if ($db->f("id_parent") > 0)
				$dNome .= ' / '.$db->f("nome_dn");

			$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
				<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($dNome).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p>'.utf8_encode($db->f("texto")).'</p>';

			if ($db->f("arquivo") != '')
			{
				$oOutput .= '<p style="overflow: hidden;">
					<span class="t13 gray-88 fl lh-22 italic">Arquivo:</span>
					<span class="t13 t-orange fl ml-8 lh-20" style="border: 1px solid #bebebe; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					<a class="bt-style-2 fl" href="'.linkFileBucket("vw/licchat/".$db->f("arquivo")).'" target="_blank" style="font-size: 12px; height: 22px; line-height:22px;">Abrir Anexo</a>
				</p>';
			}

			$oOutput .= '</div></div>';
		}
		else if ($db->f("tipo") == 13) //13: Troca de fase
		{
			if ($db->f("id_valor_1") == 0 && $db->f("id_valor_2") == 1)
			{
				$oOutput .= '<div class="fourcont bg-troca">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p>
							<span class="bold cor-inicio">Início do prazo de 24hs para envio da APL exclusivo para ADVE/POOL</span>
						</p>
					</div>
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

				$oOutput .= '<div class="fourcont bg-troca">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p>
							<span class="bold cor-inicio">Início do prazo de 24hs para envio da '.utf8_encode($db->f("regiao",1)).'</span>
						</p>
					</div>
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

				$oOutput .= '<div class="fourcont bg-troca">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p>
							<span class="bold cor-fim">Fim do prazo de envio ADVE/POOL</span> => <span class="bold cor-inicio">Início do prazo de 24hs para envio da '.utf8_encode($db->f("regiao",1)).'</span>
						</p>
					</div>
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

				$oOutput .= '<div class="fourcont bg-troca">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
					<span class="day">'.timeAgo($db->f("data_hora")).'</span>
					<div class="txt">
						<p>
							<span class="bold cor-fim">Fim do prazo de envio da '.utf8_encode($db->f("regiao",1)).'</span> => <span class="bold cor-inicio">Início do prazo de envio (Brasil)</span>
						</p>
					</div>
				</div>';
			}
		}
		else if ($db->f("tipo") == 22) //22: Cliente não tem interesse
		{
			$dNome = $db->f("nome_cliente");

			if ($db->f("id_parent") > 0)
				$dNome .= ' / '.$db->f("nome_dn");

			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

			$msg = '';
			if (strlen($db->f("texto")) > 0)
				$msg = '<br><span class="normal">'.nl2br(utf8_encode($db->f("texto"))).'</span>';

			$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
				<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($dNome).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="bold italic">Não tenho interesse em participar desta licitação.<br>
					Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.$msg.'</p>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 23) //23: Cliente não tem interesse (revertido)
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

			$msg = '';
			if (strlen($db->f("texto")) > 0)
				$msg = '<br><span class="normal">'.nl2br(utf8_encode($db->f("texto"))).'</span>';

			$dNome = $db->f("nome_cliente");

			if ($db->f("id_parent") > 0)
				$dNome .= ' / '.$db->f("nome_dn");

			$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
				<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($dNome).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="bold italic">Não tenho interesse em participar desta licitação. <span style="font-weight:normal;color:#888888;">(revertido)</span><br>
					Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.$msg.'</p>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 31) //31: Admin encerrou licitação
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

			$oOutput .= '<div class="fourcont">
				<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>

				<div class="txt">
					<p class="bold italic">Licitação encerrada pela administração.<br>
					Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.'</p>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 32) //32: Admin reverteu o encerramento da licitação
		{
			$oOutput .= '<div class="fourcont">
				<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="bold italic">Encerramento revertido.</p>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 33) //33: Admin reverteu o desinteresse do cliente
		{
			$dNome = $db->f("vnome_cliente");
			if ($db->f("vid_parent") > 0)
				$dNome .= ' / '.$db->f("vnome_dn");			

			$oOutput .= '<div class="fourcont">
				<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="bold italic">Declínio de interesse revertido.<br>
					<span class="normal gray-88">'.utf8_encode($dNome).'</span></p>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 34) //34: Admin prorrogou a abertura da licitação
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

			$oOutput .= '<div class="fourcont">
				<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="bold italic" style="margin:0;">Licitação prorrogada.</p><p class="prrrr">'.utf8_encode(str_replace("{{MOT}}", '<span class="italic"><a class="bold">Motivo:</a> '.utf8_encode($db->f("motivo")).$subm.'</span>', $db->f("texto"))).'</p>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 39) //39: Admin encerrou licitação (revertido)
		{
			$subm = '';
			if (strlen($db->f("submotivo")) > 0)
				$subm = ' <span class="gray-88" style="display:inline;font-weight: normal;">('.utf8_encode($db->f("submotivo")).')</span>';

			$oOutput .= '<div class="fourcont">
				<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>
				<p class="usuario-admin italic gray-88">('.utf8_encode($db->f("usuario_admin")).')</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="bold italic">Licitação encerrada pela administração. <span style="font-weight:normal;color:#888888;">(revertido)</span><br>
					Motivo: <span style="font-weight: normal;">'.utf8_encode($db->f("motivo")).'</span>'.$subm.'</p>
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

			$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
				<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span>LOTE: '.utf8_encode($db->f("lote",1)).'&nbsp;&nbsp;ITEM: '.utf8_encode($db->f("item",1)).'</p>
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p><span class="bold t-orange">APL enviada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
				</div>
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

			if ($sInside_tipo == 1) //BO
				$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
			else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				$oOutput .= '<div class="fourcont" style="position: relative;">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

			$oOutput .= '
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="t13" style="margin:0;"><span class="bold t-green">APL aprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
				</div>';

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


			$oOutput .= '
				<div class="txt">
					<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL enviada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
				</div>
			</div>';
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

			if ($sInside_tipo == 1) //BO
				$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
			else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				$oOutput .= '<div class="fourcont" style="position: relative;">
					<p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

			$oOutput .= '
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="t13" style="margin:0;"><span class="bold t-red">APL reprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
				</div>';

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

			$oOutput .= '
				<div class="txt">
					<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL enviada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
				</div>
			</div>';
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
			{
				$dTexto = $db->f("texto",1);

				if ($dTexto{0} == "{")
					$dTexto = substr($dTexto, strpos($dTexto,"}") + 1);

				if (strlen($dTexto) > 0)
					$dTexto = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($dTexto));
			}

			if ($sInside_tipo == 1) //BO
				$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
			else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				$oOutput .= '<div class="fourcont" style="position: relative;"><p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

			$oOutput .= '
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="t13" style="margin:0;"><span class="bold t-blue">Aprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span>'.$dTexto.'</p>
				</div>';

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

			$oOutput .= '
				<div class="txt">
					<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL aprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
				</div>
			</div>';
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
			{
				$dTexto = $db->f("texto",1);

				if ($dTexto{0} == "{")
					$dTexto = substr($dTexto, strpos($dTexto,"}") + 1);

				if (strlen($dTexto) > 0)
					$dTexto = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($dTexto));
			}

			if ($sInside_tipo == 1) //BO
				$oOutput .= '<div class="fourcont" style="position: relative; background-color: #f4f4f4;">
					<p class="threecont_tit">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';
			else if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
				$oOutput .= '<div class="fourcont" style="position: relative;"><p class="threecont_tit" style="background: none; padding: 0 0 0 10px;">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'<span>'.substr($db->f("data_hora"),11).'</span></p>';

			$oOutput .= '
				<span class="day">'.timeAgo($db->f("data_hora")).'</span>
				<div class="txt">
					<p class="t13" style="margin:0;"><span class="bold t-blue">Reprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span>'.$dTexto.'</p>
				</div>';

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

			$oOutput .= '
				<div class="txt">
					<p class="t12" style="margin:0 0 17px 0;">Ref: <span class="gray-88">LOTE: '.utf8_encode($db->f("lote",1)).'</span>&nbsp;&nbsp;<span class="gray-88">ITEM: '.utf8_encode($db->f("item",1)).'</span><br>Da APL reprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora",1),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora",1),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip",1).'</span></p>
				</div>
			</div>';
		}

	}
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
