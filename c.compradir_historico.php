<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'',0);
if (isInside())
{
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	$pId_cd = intval($_POST["id-cd"]);
	$db = new Mysql();
	$oOutput = '';
	$autorizado = false;

	$db->query("
SELECT 
	his.tipo, 
	his.data_hora,
	his.texto AS htexto,
	his.nome_arquivo,
	his.arquivo,
	usr.nome AS nome_admin,
    ahis.ip,
	ahis.texto AS atexto,
    cli.id_parent,
	cli.nome AS nome_cliente,
	clidn.nome AS nome_dn
FROM 
	gelic_comprasrp_historico AS his
	LEFT JOIN gelic_admin_usuarios AS usr ON usr.id = his.id_sender
	LEFT JOIN gelic_clientes AS cli ON cli.id = his.id_sender
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
	LEFT JOIN gelic_comprasrp_apl_historico AS ahis ON ahis.id = his.id_valor_1
WHERE 
	his.id_comprasrp = $pId_cd 
ORDER BY 
	his.id");
	while ($db->nextRecord())
	{
		if ($db->f("tipo") == 1 && !$autorizado) //(DN) 1: Solicitação enviada. Aguardando autorização...
		{
			$nome = $db->f("nome_cliente");
			if ($db->f("id_parent") > 0)
				$nome .= ' / '.$db->f("nome_dn");

			$oOutput .= '<div class="m-row">
				<div class="m-row-send">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fl ml-30 italic gray-99 bold lh-27">('.utf8_encode($nome).')</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="italic gray-88">Solicitação enviada. Aguardando autorização...</span></p>
					</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 2) //(GL) 2: Mensagem enviada pelo ADMIN
		{
			$oOutput .= '<div class="m-row">
				<div class="m-row-receive">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fl ml-30 italic gray-99 bold lh-27">('.utf8_encode($db->f("nome_admin")).')</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p>'.utf8_encode($db->f("htexto")).'</p>';

			if (strlen($db->f("arquivo")) > 0)
				$oOutput .= '<p>
					<span class="t13 gray-88 fl lh-22 italic">Arquivo:</span>
					<span class="t13 t-orange fl ml-8 lh-20" style="border: 1px solid #bebebe; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					<a class="bt-style-2 fl" href="'.linkFileBucket("vw/cdchat/".$db->f("arquivo")).'" target="_blank" style="font-size: 12px; height: 22px; line-height:22px;">Abrir Anexo</a>
				</p>';

			$oOutput .= '
					</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 3) //(DN) 3: Mensagem enviada pelo DN
		{
			$nome = $db->f("nome_cliente");
			if ($db->f("id_parent") > 0)
				$nome .= ' / '.$db->f("nome_dn");

			$oOutput .= '<div class="m-row">
				<div class="m-row-send">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fl ml-30 italic gray-99 bold lh-27">('.utf8_encode($nome).')</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p>'.utf8_encode($db->f("htexto")).'</p>';

			if (strlen($db->f("arquivo")) > 0)
				$oOutput .= '<p>
					<span class="t13 gray-88 fl lh-22 italic">Arquivo:</span>
					<span class="t13 t-orange fl ml-8 lh-20" style="border: 1px solid #bebebe; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					<a class="bt-style-2 fl" href="'.linkFileBucket("vw/cdchat/".$db->f("arquivo")).'" target="_blank" style="font-size: 12px; height: 22px; line-height:22px;">Abrir Anexo</a>
				</p>';

			$oOutput .= '</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 4) //(BK) 4: Mensagem enviada pelo BACK OFFICE
		{
			$nome = $db->f("nome_cliente");
			$oOutput .= '<div class="m-row">
				<div class="m-row-send">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fl ml-30 italic gray-99 bold lh-27">'.utf8_encode($nome).' (BO)</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p>'.utf8_encode($db->f("htexto")).'</p>';

			if (strlen($db->f("arquivo")) > 0)
				$oOutput .= '<p>
					<span class="t13 gray-88 fl lh-22 italic">Arquivo:</span>
					<span class="t13 t-orange fl ml-8 lh-20" style="border: 1px solid #bebebe; padding: 0 10px; box-sizing: border-box; border-right: 0;background-color:#ffffff;">'.utf8_encode($db->f("nome_arquivo")).'</span>
					<a class="bt-style-2 fl" href="'.linkFileBucket("vw/cdchat/".$db->f("arquivo")).'" target="_blank" style="font-size: 12px; height: 22px; line-height:22px;">Abrir Anexo</a>
				</p>';

			$oOutput .= '</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 5) //(GL) 5: O envio da APL foi autorizado.
		{
			$oOutput .= '<div class="m-row">
				<div class="m-row-receive">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fl ml-30 italic gray-99 bold lh-27">('.utf8_encode($db->f("nome_admin")).')</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="t-green bold">O envio da APL foi autorizado.</span></p>
					</div>
				</div>
			</div>';

			$autorizado = true;
		}
		else if ($db->f("tipo") == 6) //(GL) 6: O envio da APL não foi autorizado.
		{
			$oOutput .= '<div class="m-row">
				<div class="m-row-receive">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fl ml-30 italic gray-99 bold lh-27">('.utf8_encode($db->f("nome_admin")).')</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="t-red bold">O envio da APL não foi autorizado.</span></p>
						<p class="italic"><span class="bold">Motivo:</span> '.utf8_encode($db->f("htexto")).'</p>
					</div>
				</div>
			</div>';

			$autorizado = true;
		}
		else if ($db->f("tipo") == 41) //(DN) 41: APL Enviada
		{
			$por = utf8_encode($db->f("nome_cliente"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			$oOutput .= '<div class="m-row">
				<div class="m-row-send">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="bold t-orange">APL enviada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip").'</span></p>
					</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 42) //(BO) 42: APL Aprovada
		{
			$por = utf8_encode($db->f("nome_cliente"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			if ($sInside_tipo == 1)
				$rs = 'send';
			else
				$rs = 'receive';

			$oOutput .= '<div class="m-row">
				<div class="m-row-'.$rs.'">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="bold t-green">APL aprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip").'</span></p>
					</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 43) //(BO) 43: APL Reprovada
		{
			$por = utf8_encode($db->f("nome_cliente"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			if ($sInside_tipo == 1)
				$rs = 'send';
			else
				$rs = 'receive';

			$oOutput .= '<div class="m-row">
				<div class="m-row-'.$rs.'">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="bold t-red">APL reprovada</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip").'</span></p>
					</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 44) //(BO) 44: APL Aprovação Revertida
		{
			$por = utf8_encode($db->f("nome_cliente"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			$obs = '';
			if (strlen($db->f("atexto")) > 0)
				$obs = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($db->f("atexto")));

			if ($sInside_tipo == 1)
				$rs = 'send';
			else
				$rs = 'receive';

			$oOutput .= '<div class="m-row">
				<div class="m-row-'.$rs.'">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>

						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="bold t-blue">Aprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip").'</span>'.$obs.'</p>
					</div>
				</div>
			</div>';
		}
		else if ($db->f("tipo") == 45) //(BO) 45: APL Reprovação Revertida
		{
			$por = utf8_encode($db->f("nome_cliente"));
			if ($db->f("id_parent") > 0)
				$por .= ' (DN: '.utf8_encode($db->f("nome_dn")).')';

			$obs = '';
			if (strlen($db->f("atexto")) > 0)
				$obs = '<br><span class="bold">Observações:</span><br>'.nl2br(utf8_encode($db->f("atexto")));

			if ($sInside_tipo == 1)
				$rs = 'send';
			else
				$rs = 'receive';

			$oOutput .= '<div class="m-row">
				<div class="m-row-'.$rs.'">
					<div class="m-first">
						<span class="fl ml-36 lh-27">'.mysqlToBr(substr($db->f("data_hora"), 0, 10)).'</span>
						<span class="fl gray-99 ml-10 lh-27">'.substr($db->f("data_hora"), 11).'</span>
						<span class="fr bold gray-88 lh-27">'.timeAgo($db->f("data_hora")).'</span>
					</div>
					<div class="m-txt">
						<p><span class="bold t-blue">Reprovação da APL revertida</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip").'</span>'.$obs.'</p>
					</div>
				</div>
			</div>';
		}
	}
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
