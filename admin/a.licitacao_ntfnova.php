<?php

require_once "include/config.php";
require_once "include/essential.php";

function ntfNovaLicitacao($id_licitacao)
{
	$db = new Mysql();
	$db->query("SELECT fase, notif_nova, DATE(datahora_abertura) AS data_abertura FROM gelic_licitacoes WHERE id = $id_licitacao AND deletado = 0");
	if ($db->nextRecord())
	{
		$dFase = $db->f("fase");
		$dNotif_nova = $db->f("notif_nova");

		$data_abertura_int = intval(str_replace("-","",$db->f("data_abertura")));
		$data_hoje_int = intval(date("Ymd"));

		if ($dFase == 1 && $dNotif_nova == 0 && $data_abertura_int >= $data_hoje_int)
		{
			// Verificar se existe algum item valido para os DNs
			$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_itens WHERE id_licitacao = $id_licitacao AND id_tipo_veiculo > 0 AND acompanhamento = 0");
			$db->nextRecord();
			if ($db->f("total") > 0)
			{
				// Verificar se a notificacao ja esta na lista para envio
				$db->query("SELECT id FROM gelic_mensagens WHERE id_notificacao = 27 AND ((metodo = 1 AND assunto LIKE '%(LIC $id_licitacao)%') OR (metodo = 2 AND mensagem LIKE '%(LIC $id_licitacao)%')) LIMIT 0,1");
				if (!$db->nextRecord())
				{
					$db->query("
SELECT
	lic.orgao,
	lic.objeto,
	lic.importante,
	lic.datahora_abertura,
	lic.datahora_entrega,
	lic.datahora_limite,
	lic.valor,
	mdl.nome AS modalidade,
	CONCAT(cid.nome,' - ',cid.uf) AS localizacao,
	his.data_hora
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 11
WHERE
	lic.id = $id_licitacao");
					$db->nextRecord();

					$dab_h = substr($db->f("datahora_abertura"),11,5);
					$den_h = substr($db->f("datahora_entrega"),11,5);
					if ($dab_h == "00:00") $dab_h = "--:--";
					if ($den_h == "00:00") $den_h = "--:--";

					if ($db->f("valor") == '0.00')
						$dValor = '<span style="font-style: italic;">não informado</span>';
					else
						$dValor = 'R$ '.number_format($db->f("valor"), 2, ",", ".");

					//-----------------------------------------
					//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
					//-----------------------------------------
					$tEmail_assunto = 'GELIC - Nova Licitação (LIC '.$id_licitacao.')';
					$tEmail_mensagem = 'Nova licitação disponível. Para responder ou acompanhar com mais detalhes entre na sua área restrita GELIC com o seu usuário e senha.<br><br>
<span style="font-weight: bold;">Número de Identificação:</span> '.$id_licitacao.'<br>
<span style="font-weight: bold;">Data/Hora de Abertura:</span> '.mysqlToBr(substr($db->f("datahora_abertura"),0,10)).' '.$dab_h.'<br>
<span style="font-weight: bold;">Data/Hora de Entrega:</span> '.mysqlToBr(substr($db->f("datahora_entrega"),0,10)).' '.$den_h.'<br>
<span style="font-weight: bold;">Prazo Limite:</span> '.mysqlToBr(substr($db->f("datahora_limite"),0,10)).' '.substr($db->f("datahora_limite"),11,5).'<br>
<span style="font-weight: bold;">Modalidade:</span> '.utf8_encode($db->f("modalidade")).'<br>
<span style="font-weight: bold;">Órgão Público:</span> '.utf8_encode($db->f("orgao")).'<br>
<span style="font-weight: bold;">Localização:</span> '.utf8_encode($db->f("localizacao")).'<br>
<span style="font-weight: bold;">Valor Estimado:</span> '.$dValor.'<br>
<span style="font-weight: bold;">Objeto:</span> '.clipString(strip_tags(utf8_encode($db->f("objeto"))),800).'<br>
<span style="font-weight: bold;">Importante:</span> '.clipString(strip_tags(utf8_encode($db->f("importante"))),800).'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($db->f("data_hora"),0,10)).' - '.substr($db->f("data_hora"),11).'<br><br>
'.rodapeEmail();
					$tTexto_sms = 'GELIC - Nova Licitacao (LIC '.$id_licitacao.') Localizacao: '.utf8_encode($db->f("localizacao"));

					//enviar daqui a 5 minutos (isso da tempo para o preenchimento de mais itens antes de notificar o DN)
					$enviar_em = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + 300);

					$sInside_id = $_SESSION[SESSION_ID];

					// Notificar DNs
					$db->query("
SELECT
	cli.id,
	cli.email,
	cli.celular,
	cli.nt_email,
	cli.nt_celular
FROM
	gelic_clientes AS cli
WHERE
	(
		(cli.tipo = 2 AND cli.id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $id_licitacao)) OR
		(cli.tipo = 3 AND cli.id_parent IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $id_licitacao)) OR
		(cli.tipo = 4 AND cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = $id_licitacao)))
	) AND
    cli.notificacoes = 1 AND
    cli.ativo = 1 AND
	cli.deletado = 0");
					while ($db->nextRecord())
					{
						$dNt_email = json_decode($db->f("nt_email"), true);
						$dNt_sms = json_decode($db->f("nt_celular"), true);

						if (in_array("A",str_split($dNt_email["ntf"])))
							queueMessage(27, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, ADM_DLR, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, '', $enviar_em);
	
						if (in_array("A",str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0)
							queueMessage(27, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, ADM_DLR, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', $enviar_em);
					}
					//-----------------------------------------
					//-----------------------------------------
					//-----------------------------------------
				}
			}
			else
			{
				// Nenhum item valido (remover envio atual)
				$db->query("DELETE FROM gelic_mensagens WHERE id_notificacao = 27 AND ((metodo = 1 AND assunto LIKE '%(LIC $id_licitacao)%') OR (metodo = 2 AND mensagem LIKE '%(LIC $id_licitacao)%'))");
			}
		}
	}
}

?>
