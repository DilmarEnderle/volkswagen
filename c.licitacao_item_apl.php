<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'');
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pId_item = intval($_POST["f-id-item"]);
	$pPergunta = (bool)intval($_POST["f-pergunta"]);

	$xAccess = explode(" ",getAccess());

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$db = new Mysql();

		// Pegar fase e abertura
		$db->query("SELECT fase, (datahora_abertura >= NOW()) AS abertura FROM gelic_licitacoes WHERE id = $pId_licitacao");
		$db->nextRecord();
		$dFase = $db->f("fase");
		$dAntes_abertura = (bool)$db->f("abertura");


		// Verificar se este cliente declinou o interesse na licitacao
		$sem_interesse = false;
		$db->query("SELECT id FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND tipo = 22 AND (id_sender = $cliente_parent OR id_sender IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
		if ($db->nextRecord())
			$sem_interesse = true;


		if ($dFase == 1)
		{
			// 1. Verificar se outro DN ja enviou uma APL para esta licitacao
			// Retornar informacao da primeira APL enviada pelo outro DN
			/*$db->query("
SELECT 
	his.ip,
	his.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl AS apl
	INNER JOIN gelic_licitacoes_apl_historico AS his ON his.id_apl = apl.id,
	gelic_clientes AS cli
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	apl.id_licitacao = $pId_licitacao AND 
	his.id = (SELECT MIN(id) FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id AND id_cliente <> $cliente_parent AND id_cliente NOT IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent) AND tipo = 1) AND
    apl.id_cliente = cli.id
ORDER BY 
	his.id LIMIT 1");
			if ($db->nextRecord())
			{
				$dHistory_ip = $db->f("ip");
				$dHistory_datahora = $db->f("data_hora");
				$dHistory_quem = utf8_encode($db->f("nome"));

				if ($db->f("id_parent") > 0)
					$dHistory_quem .= ' (DN: '.utf8_encode($db->f("dn_nome")).')';

				$aReturn[0] = 20; //custom
				$aReturn[1] = 'APL enviada por outro concessionário'; //title
				$aReturn[2] = 600; //width
				$aReturn[3] = 'red'; //color
				$aReturn[4] = 'APL preenchida e enviada em <span class="bold">'.mysqlToBr(substr($dHistory_datahora,0,10)).'</span> às <span class="bold gray-88">'.substr($dHistory_datahora,11).'</span> por <span class="bold italic">'.$dHistory_quem.'</span> de <span class="t-red">'.$dHistory_ip.'</span>';
				echo json_encode($aReturn);
				exit;
			}
*/


			// Verificar se este DN ja enviou uma APL para este item
			$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = $pId_item AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
			if (!$db->nextRecord())
			{
				// SEM APL

				// 0. Prosseguir so se for representante
				if ($sInside_tipo == 2 && !in_array($cliente_parent, array(599,600,601,602,603)))
				{
					// Verificar se ja existe algum representante cadastrado
					$db->query("SELECT id FROM gelic_clientes WHERE deletado = 0 AND ((id_parent = $sInside_id AND tipo = 3) OR id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = $sInside_id))");
					if ($db->nextRecord())
					{
						$aReturn[0] = 20;
						$aReturn[1] = 'Restrição'; //title
						$aReturn[2] = 500; //width
						$aReturn[3] = 'red'; //color
						$aReturn[4] = 'O envio da APL deve ser feito pelo representante.';
						echo json_encode($aReturn);
						exit;
					}
					else
					{
						$aReturn[0] = 21;
						$aReturn[1] = 'Restrição'; //title
						$aReturn[2] = 500; //width
						$aReturn[3] = 'red'; //color
						$aReturn[4] = 'O envio da APL deve ser feito pelo representante.<br><br>Você gostaria de adicionar um novo usuário?';
						echo json_encode($aReturn);
						exit;
					}
				}

				// 1. Sem interesse ?
				if ($sem_interesse)
				{
					$aReturn[0] = 20; //custom
					$aReturn[1] = 'Sem interesse'; //title
					$aReturn[2] = 500; //width
					$aReturn[3] = 'red'; //color
					$aReturn[4] = 'Sem interesse em participar.';
					echo json_encode($aReturn);
					exit;
				}

				// 2. Se ja passou da data de abertura nao permitir enviar uma nova APL
				if (!$dAntes_abertura)
				{
					$aReturn[0] = 20; //custom
					$aReturn[1] = 'Prazo de envio'; //title
					$aReturn[2] = 500; //width
					$aReturn[3] = 'red'; //color
					$aReturn[4] = 'O prazo para envio da APL expirou.';
					echo json_encode($aReturn);
					exit;
				}

				// 3. Se faltar menos de 24 horas uteis para a data/hora de abertura da licitacao entao bloquear envio da APL
				$now = time();
				$t_24horas = $now;
				$aFeriados = array();
				$db->query("SELECT CONCAT(LPAD(mes,2,'0'),LPAD(dia,2,'0')) AS feriado FROM gelic_feriados");
				while ($db->nextRecord()) { $aFeriados[] = $db->f("feriado"); } //array com valores em (mmdd)
				$pPointer = date("Ymdw", $t_24horas);
				$pDia_util = 0;
				while ($pDia_util == 0)
				{
					$t_24horas += 86400;
					$pPointer = date("Ymdw", $t_24horas);
					if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
						$pDia_util = 1;
				}
				$target_date_time = date("Y-m-d H:i:s", $t_24horas);
				$db->query("SELECT id FROM gelic_licitacoes WHERE id = $pId_licitacao AND datahora_abertura >= '$target_date_time'");
				if (!$db->nextRecord())
				{
					$aReturn[0] = 20; //custom
					$aReturn[1] = 'Prazo de envio'; //title
					$aReturn[2] = 500; //width
					$aReturn[3] = 'red'; //color
					$aReturn[4] = 'Sem prazo para análise da APL.';
					echo json_encode($aReturn);
					exit;
				}
			}
		}
		else
		{
			// FASES 2 e 3

			// Verificar se este DN ja enviou uma APL para este item
			$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = $pId_item AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
			if (!$db->nextRecord())
			{
				// SEM APL


				// 0. Prosseguir so se for representante
				if ($sInside_tipo == 2 && !in_array($cliente_parent, array(599,600,601,602,603)))
				{
					// Verificar se ja existe algum representante cadastrado
					$db->query("SELECT id FROM gelic_clientes WHERE deletado = 0 AND ((id_parent = $sInside_id AND tipo = 3) OR id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = $sInside_id))");
					if ($db->nextRecord())
					{
						$aReturn[0] = 20;
						$aReturn[1] = 'Restrição'; //title
						$aReturn[2] = 500; //width
						$aReturn[3] = 'red'; //color
						$aReturn[4] = 'O envio da APL deve ser feito pelo representante.';
						echo json_encode($aReturn);
						exit;
					}
					else
					{
						$aReturn[0] = 21;
						$aReturn[1] = 'Restrição'; //title
						$aReturn[2] = 500; //width
						$aReturn[3] = 'red'; //color
						$aReturn[4] = 'O envio da APL deve ser feito pelo representante.<br><br><span class="bold">Você gostaria de adicionar um novo usuário?</span>';
						echo json_encode($aReturn);
						exit;
					}
				}



				// 1. Sem interesse ?
				if ($sem_interesse)
				{
					$aReturn[0] = 20; //custom
					$aReturn[1] = 'Sem interesse'; //title
					$aReturn[2] = 500; //width
					$aReturn[3] = 'red'; //color
					$aReturn[4] = 'Sem interesse em participar.';
					echo json_encode($aReturn);
					exit;
				}


				// 2. Verificar se uma APL ja foi APROVADA ou REPROVADA para outro DN para esta LICITACAO
				$db->query("
SELECT
	id
FROM
	gelic_licitacoes_apl_ear
WHERE
	id_licitacao = $pId_licitacao AND
	(id_cliente <> $cliente_parent AND id_cliente NOT IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent)) AND
	(aprovadas > 0 OR reprovadas > 0)");
				if ($db->nextRecord())
				{
					$aReturn[0] = 20; //custom
					$aReturn[1] = 'APL Processada'; //title
					$aReturn[2] = 500; //width
					$aReturn[3] = 'red'; //color
					$aReturn[4] = 'Uma APL já foi processada para outro concessionário.';
					echo json_encode($aReturn);
					exit;
				}


				// 3. Se ja passou da data de abertura nao permitir enviar uma nova APL
				$aExceptions = array(15215);
				if (!$dAntes_abertura && !in_array($pId_licitacao, $aExceptions))
				{
					$aReturn[0] = 20; //custom
					$aReturn[1] = 'Prazo de envio'; //title
					$aReturn[2] = 500; //width
					$aReturn[3] = 'red'; //color
					$aReturn[4] = 'O prazo para envio da APL expirou.';
					echo json_encode($aReturn);
					exit;
				}



				// 5. Se faltar menos de 24 horas uteis para a data/hora de abertura da licitacao entao bloquear envio da APL
				$now = time();
				$t_24horas = $now;
				$aFeriados = array();
				$db->query("SELECT CONCAT(LPAD(mes,2,'0'),LPAD(dia,2,'0')) AS feriado FROM gelic_feriados");
				while ($db->nextRecord()) { $aFeriados[] = $db->f("feriado"); } //array com valores em (mmdd)
				$pPointer = date("Ymdw", $t_24horas);
				$pDia_util = 0;
				while ($pDia_util == 0)
				{
					$t_24horas += 86400;
					$pPointer = date("Ymdw", $t_24horas);
					if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
						$pDia_util = 1;
				}
				$target_date_time = date("Y-m-d H:i:s", $t_24horas);
				$db->query("SELECT id FROM gelic_licitacoes WHERE id = $pId_licitacao AND datahora_abertura >= '$target_date_time'");
				if (!$db->nextRecord())
				{
					$aReturn[0] = 20; //custom
					$aReturn[1] = 'Prazo de envio'; //title
					$aReturn[2] = 500; //width
					$aReturn[3] = 'red'; //color
					$aReturn[4] = 'Sem prazo para análise da APL.';
					echo json_encode($aReturn);
					exit;
				}




				// 4. Alertar o DN se ja existe APL enviada por outro DN e perguntar se deseja enviar a dele mesmo assim

				// Se este DN ja tiver uma APL aprovada para esta licitacao entao ignorar a pergunta
				$db->query("
SELECT 
	(SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM
	gelic_licitacoes_apl AS apl
WHERE
	apl.id = (
		SELECT 
			MAX(id) 
		FROM 
			gelic_licitacoes_apl 
		WHERE 
			id_licitacao = $pId_licitacao AND 
			(id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
		)
HAVING
	tipo = 2");
				if ($db->nextRecord())
					$pPergunta = false;

				if ($pPergunta)
				{
					$db->query("
SELECT COUNT(*) AS total FROM 
(
	SELECT
    	IF (cli.id_parent > 0, cli.id_parent, cli.id) AS global_parent
	FROM
		gelic_licitacoes_apl AS apl
		INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
	WHERE
		apl.id_licitacao = $pId_licitacao AND 
		apl.id_cliente <> $cliente_parent AND
		apl.id_cliente NOT IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent)
	GROUP BY
		global_parent
) AS t");
					$db->nextRecord();
					if ($db->f("total") > 0)
					{
						$aReturn[0] = 2; //pergunta
						$aReturn[1] = 'Já existe(m) <span class="bold t-red">'.$db->f("total").'</span> DN(s) com APL enviada(s).<br><br><span class="italic">Gostaria de enviar a sua mesmo assim ?</span>';
						echo json_encode($aReturn);
						exit;
					}
				}
			}
		}



		// Preencher valores padrao para uma APL vazia
		$tApl = '';
		$dVersao = 2;
		$dNome_orgao = "";
		$dData_licitacao = "";
		$dId_licitacao = "";
		$dRegistro_precos = 0;
		$dCnpj_faturamento = "";
		$dEstado = "";
		$dModalidade_venda = 0;
		$dSite_pregao_eletronico = "";
		$dNumero_licitacao = "";
		$dParticipante_nome = "";
		$dParticipante_cpf = "";
		$dParticipante_rg = "";
		$dParticipante_telefone = "";
		$dParticipante_endereco = "";
		$dV1_documentacao_selecionados = json_decode("[]"); //versao 1
		$dV1_documentacao_outros = "";                      //versao 1
		$dModel_code = "";
		$dCor = "";
		$dAno_modelo = "";
		$dMotorizacao = "";
		$dPotencia = "";
		$dCombustivel = "";
		$dOpcionais_pr = "";
		$dEficiencia_energetica = 0;
		$dTransformacao = 0;
		$dTransformacao_tipo = "";
		$dTransformacao_prototipo = 0;
		$dTransformacao_detalhar = "";
		$dTransformacao_amarok_nome_arquivo	= "";
		$dTransformacao_amarok_arquivo = "";
		$dAcessorios = 0;
		$dEmplacamento = 0;
		$dLicenciamento = 0;
		$dIpva = 0;
		$dGarantia = 0;
		$dGarantia_prazo = 0;
		$dGarantia_prazo_outro = "";
		$dRevisao_embarcada = 0;
		$dQuantidade_revisoes_inclusas = "";
		$dLimite_km = 0;
		$dLimite_km_km = "";
		$dPreco_publico_vw = "";
		$dPreco_ref_edital = "";
		$dQuantidade_veiculos = "";
		$dDn_venda = "";
		$dDn_venda_estado = "";
		$dRepasse_concessionario = "";
		$dDn_entrega = "";
		$dDn_entrega_estado = "";
		$dPrazo_entrega = "";
		$dValidade_proposta = "";
		$dVigencia_contrato = "";
		$dPrazo_pagamento = "";
		$dV1_prazo_pagamento = "";      //versao 1
		$dV1_desconto = "";             //versao 1
		$dV1_preco_maximo = "";         //versao 1
		$dAve = "";
		$dMultas_sansoes = "";
		$dV1_numero_pool = "";          //versao 1
		$dGarantia_contrato = 0;
		$dPrazo = "";
		$dValor = "";
		$dOrigem_verba = 0;
		$dOrigem_verba_tipo = 0;
		$dIsencao_impostos = 0;
		$dImposto_indicar = "";
		$dObservacoes = "";

		$amarok_upload_box = '';
		$amarok_upload_ready = ' style="display:none;"';
		$amarok_upload_anexo = '';
		$amarok_object = new stdClass();
		$amarok_object->status = 0;
		$amarok_object->long_filename = "";
		$amarok_object->short_filename = "";
		$amarok_object->file_size = "";
		$amarok_object->file = "";


		$tAcessorios = '<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 1</span>
			<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000">
			<span class="apl-lb w-100 bg-3 center">Valor 1 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 2</span>
			<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000">
			<span class="apl-lb w-100 bg-3 center">Valor 2 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 3</span>
			<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000">
			<span class="apl-lb w-100 bg-3 center">Valor 3 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 4</span>
			<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000">
			<span class="apl-lb w-100 bg-3 center">Valor 4 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div id="aaBtn-row" class="apl-row apl-br apl-bb apl-bl" style="background-color:#efefef;">
			<a class="bt-apl-style-1 fl" href="javascript:void(0);" onclick="adicionarAcessorio();" title="Adicionar Acessório" style="margin-bottom: 6px; border-bottom-right-radius:5px;">+</a>
		</div>';


		// -------------------------------------------------------------------------------------
		// Preencher campos automaticamente com valores da licitacao
		// -------------------------------------------------------------------------------------
		$db->query("SELECT id, orgao, datahora_abertura, numero, link, srp, id_modalidade FROM gelic_licitacoes WHERE id = $pId_licitacao");
		$db->nextRecord();
		$dId_licitacao = $db->f("id");
		$dNome_orgao = utf8_encode($db->f("orgao"));
		$dData_licitacao = mysqlToBr(substr($db->f("datahora_abertura"),0,10));
		$dRegistro_precos = intval($db->f("srp"));
		$dNumero_licitacao = utf8_encode($db->f("numero"));

		if ($db->f("id_modalidade") == 3) $dModalidade_da_venda = 2;
		else if ($db->f("id_modalidade") == 7) $dModalidade_da_venda = 3;
		else if ($db->f("id_modalidade") == 2) $dModalidade_da_venda = 5;
		else if ($db->f("id_modalidade") == 4) $dModalidade_da_venda = 6;
		else if ($db->f("id_modalidade") == 1) $dModalidade_da_venda = 7;
	
		if (strlen($db->f("link")) > 0 && $db->f("id_modalidade") == 1)
			$dSite_pregao_eletronico = utf8_encode($db->f("link"));
		// -------------------------------------------------------------------------------------


		$tipo = 0;
		$dados = false;
		$readonly = false;


		// Buscar dados da ultima APL enviada (licitacao, item, cliente)
		$db->query("
SELECT 
	apl.*,
	(SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM
	gelic_licitacoes_apl AS apl
WHERE
	apl.id = (
		SELECT 
			MAX(id) 
		FROM 
			gelic_licitacoes_apl 
		WHERE 
			id_licitacao = $pId_licitacao AND 
			id_item = $pId_item AND 
			(id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent)))");
		if ($db->nextRecord())
		{
			// APL ENCONTRADA
			$tipo = $db->f("tipo");

			// Verificar se o DN tem acesso para editar
			if (!in_array("lic_apl_enviar", $xAccess))
				$readonly = true;

			// Verificar se ja foi aprovada ou reprovada
			if ($tipo == 2 || $tipo == 4) //aprovada, reprovada
				$readonly = true;

			$dados = true;
		}

		if ($sem_interesse || ($sInside_tipo == 2 && !in_array($cliente_parent, array(599,600,601,602,603))))
			$readonly = true;



		$input_readonly = '';
		$check_readonly = '';
		if ($readonly)
		{
			$input_readonly = ' readonly';
			$check_readonly = ' data-mode="readonly"';
		}


		if ($dados)
		{
			// Preencher os dados conforme informacoes da APL encontrada
			$dId_apl = $db->f("id");
			$dVersao = intval($db->f("versao"));
			$dNome_orgao = utf8_encode($db->f("nome_orgao"));
			$dData_licitacao = mysqlToBr($db->f("data_licitacao"));
			$dId_licitacao = $db->f("id_licitacao");
			$dRegistro_precos = intval($db->f("registro_precos"));
			$dCnpj_faturamento = $db->f("cnpj_faturamento");
			$dEstado = $db->f("estado");
			$dModalidade_venda = intval($db->f("modalidade_venda"));
			$dSite_pregao_eletronico = utf8_encode($db->f("site_pregao_eletronico"));
			$dNumero_licitacao = utf8_encode($db->f("numero_licitacao"));
			$dParticipante_nome = utf8_encode($db->f("participante_nome"));
			$dParticipante_cpf = $db->f("participante_cpf");
			$dParticipante_rg = utf8_encode($db->f("participante_rg"));
			$dParticipante_telefone = $db->f("participante_telefone");
			$dParticipante_endereco = utf8_encode($db->f("participante_endereco"));
			$dV1_documentacao_selecionados = json_decode($db->f("v1_documentacao_selecionados"));
			$dV1_documentacao_outros = utf8_encode($db->f("v1_documentacao_outros"));
			$dModel_code = utf8_encode($db->f("model_code"));
			$dCor = utf8_encode($db->f("cor"));
			$dAno_modelo = utf8_encode($db->f("ano_modelo"));
			$dMotorizacao = utf8_encode($db->f("motorizacao"));
			$dPotencia = utf8_encode($db->f("potencia"));
			$dCombustivel = utf8_encode($db->f("combustivel"));
			$dOpcionais_pr = utf8_encode($db->f("opcionais_pr"));
			$dEficiencia_energetica = intval($db->f("eficiencia_energetica"));
			$dTransformacao = intval($db->f("transformacao"));
			$dTransformacao_tipo = utf8_encode($db->f("transformacao_tipo"));
			$dTransformacao_prototipo = intval($db->f("transformacao_prototipo"));
			$dTransformacao_detalhar = utf8_encode($db->f("transformacao_detalhar"));
			$dTransformacao_amarok_nome_arquivo	= utf8_encode($db->f("transformacao_amarok_nome_arquivo"));
			$dTransformacao_amarok_arquivo = $db->f("transformacao_amarok_arquivo");
			$dAcessorios = intval($db->f("acessorios"));
			$dEmplacamento = intval($db->f("emplacamento"));
			$dLicenciamento = intval($db->f("licenciamento"));
			$dIpva = intval($db->f("ipva"));
			$dGarantia = intval($db->f("garantia"));
			$dGarantia_prazo = intval($db->f("garantia_prazo"));
			$dGarantia_prazo_outro = utf8_encode($db->f("garantia_prazo_outro"));
			$dRevisao_embarcada = intval($db->f("revisao_embarcada"));
			$dQuantidade_revisoes_inclusas = ($db->f("quantidade_revisoes_inclusas") == 0) ? "" : $db->f("quantidade_revisoes_inclusas");
			$dLimite_km = intval($db->f("limite_km"));
			$dLimite_km_km = ($db->f("limite_km_km") == 0) ? "" : $db->f("limite_km_km");
			$dPreco_publico_vw = ($db->f("preco_publico_vw") == '0.00') ? "" : "R$ ".number_format($db->f("preco_publico_vw"), 2, ",", ".");
			$dPreco_ref_edital = ($db->f("preco_ref_edital") == '0.00') ? "" : "R$ ".number_format($db->f("preco_ref_edital"), 2, ",", ".");
			$dQuantidade_veiculos = ($db->f("quantidade_veiculos") == 0) ? "" : $db->f("quantidade_veiculos");
			$dDn_venda = utf8_encode($db->f("dn_venda"));
			$dDn_venda_estado = utf8_encode($db->f("dn_venda_estado"));
			$dRepasse_concessionario = ($db->f("repasse_concessionario") == 0) ? "" : $db->f("repasse_concessionario");
			$dDn_entrega = utf8_encode($db->f("dn_entrega"));
			$dDn_entrega_estado = utf8_encode($db->f("dn_entrega_estado"));
			$dPrazo_entrega = ($db->f("prazo_entrega") == 0) ? "" : $db->f("prazo_entrega");
			$dValidade_proposta = ($db->f("validade_proposta") == 0) ? "" : $db->f("validade_proposta");
			$dVigencia_contrato = utf8_encode($db->f("vigencia_contrato"));
			$dPrazo_pagamento = ($db->f("prazo_pagamento") == 0) ? "" : $db->f("prazo_pagamento");
			$dV1_prazo_pagamento = utf8_encode($db->f("v1_prazo_pagamento"));
			$dV1_desconto = utf8_encode($db->f("v1_desconto"));
			$dV1_preco_maximo = ($db->f("v1_preco_maximo") == '0.00') ? "" : "R$ ".number_format($db->f("v1_preco_maximo"), 2, ",", ".");
			$dV1_numero_pool = utf8_encode($db->f("v1_numero_pool"));
			$dAve = utf8_encode($db->f("ave"));
			$dMultas_sansoes = utf8_encode($db->f("multas_sansoes"));
			$dGarantia_contrato = intval($db->f("garantia_contrato"));
			$dPrazo = utf8_encode($db->f("prazo"));
			$dValor = utf8_encode($db->f("valor"));
			$dOrigem_verba = intval($db->f("origem_verba"));
			$dOrigem_verba_tipo = intval($db->f("origem_verba_tipo"));
			$dIsencao_impostos = intval($db->f("isencao_impostos"));
			$dImposto_indicar = utf8_encode($db->f("imposto_indicar"));
			$dObservacoes = utf8_encode($db->f("observacoes"));

			if ($dVersao == 2)
			{
				if ($dTransformacao_amarok_arquivo != '' && file_exists(UPLOAD_DIR."apl/".$dTransformacao_amarok_arquivo))
				{
					$pShort_file_name = $db->f("transformacao_amarok_nome_arquivo");
					if (strlen($pShort_file_name) > 84)
						$pShort_file_name = substr($pShort_file_name, 0, 73)."...".substr($pShort_file_name, -8);

					$file_size = formatSizeUnits(filesize(UPLOAD_DIR."apl/".$dTransformacao_amarok_arquivo));
					$amarok_upload_anexo = 'Anexo: <a class="clf" href="arquivos/apl/'.$dTransformacao_amarok_arquivo.'" target="_blank">'.utf8_encode($pShort_file_name).'</a> ('.$file_size.')';

					$amarok_object->status = 2;
					$amarok_object->long_filename = $dTransformacao_amarok_nome_arquivo;
					$amarok_object->short_filename = utf8_encode($pShort_file_name);
					$amarok_object->file_size = $file_size;
					$amarok_object->file = $dTransformacao_amarok_arquivo;

					$amarok_upload_box = ' style="display:none;"';
					$amarok_upload_ready = '';
				}
			}

			//acessorios
			$tAcessorios = '';
			$db->query("SELECT acessorio, valor FROM gelic_licitacoes_apl_acessorios WHERE id_apl = $dId_apl ORDER BY id");
			while ($db->nextRecord())
			{
				$dAcessorio = utf8_encode($db->f("acessorio"));
				$dValor_ace = ($db->f("valor") == '0.00') ? "" : "R$ ".number_format($db->f("valor"), 2, ",", ".");

				if ($readonly)
				{
					$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
						<input class="apl-input w-576 bg-2 white" type="text" maxlength="1000" value="'.$dAcessorio.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
						<input class="apl-input w-160 bg-2 white" type="text" style="text-align:right;" value="'.$dValor_ace.'"'.$input_readonly.'>
					</div>';
				}
				else
				{
					if ($db->Row[0] < 5)
						$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
							<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
							<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000" value="'.$dAcessorio.'"'.$input_readonly.'>
							<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
							<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align:right;" value="'.$dValor_ace.'"'.$input_readonly.'>
							<span class="rm-acess-dummy"></span>
						</div>';
					else
						$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
							<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
							<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000" value="'.$dAcessorio.'"'.$input_readonly.'>
							<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
							<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align:right;" value="'.$dValor_ace.'"'.$input_readonly.'>
							<a class="rm-acess" href="javascript:void(0);" onclick="removerAcessorio(this);" title="Remover Acessório"></a>
						</div>';
				}
			}

			if (!$readonly)
				$tAcessorios .= '<div id="aaBtn-row" class="apl-row apl-br apl-bb apl-bl" style="background-color:#efefef;">
					<a class="bt-apl-style-1 fl" href="javascript:void(0);" onclick="adicionarAcessorio();" title="Adicionar Acessório" style="margin-bottom: 6px; border-bottom-right-radius:5px;">+</a>
				</div>';
		}

	
		$acessorios_sim_nao = ' style="display:none;"';
		if ($dAcessorios == 1)
			$acessorios_sim_nao = '';

		$kmkm = 'none';
		$kmkmf = 'inline-block';
		if ($dLimite_km == 1)
		{
			$kmkm = 'inline-block';
			$kmkmf = 'none';
		}


		$aPrazo = array();
		$aPrazo[] = '';
		$aPrazo[] = '12 meses';
		$aPrazo[] = '24 meses';
		$aPrazo[] = '36 meses';

		if ($dGarantia_prazo < 4)
			$dGarantia_prazo_str = $aPrazo[$dGarantia_prazo];
		else
			$dGarantia_prazo_str = $dGarantia_prazo_outro;


		$db->query("SELECT texto FROM gelic_texto WHERE id = 16");
		$db->nextRecord();
		$termos = utf8_encode($db->f("texto"));

		$aceite = '';
		if ($sInside_tipo != 2 || in_array($cliente_parent, array(599,600,601,602,603)))
			$aceite = '<div id="hook-aceite" class="apl-row apl-br apl-bb apl-bl bg-3" style="overflow:hidden;padding:20px 40px;">
				<a id="i-aceite" class="cb0" href="javascript:void(0);"'.$check_readonly.' onclick="ckSelfishONLY(this);" style="position:relative;float:left;font-size:12px;height:auto;color:#565656;">'.$termos.'</a>
			</div>';


		if ($dVersao == 1)
			$tApl = '<div style="position:relative;overflow:hidden;width:100%;">
				<form id="form-apl-item">
					<input type="hidden" name="f-versao-item" value="'.$dVersao.'">
					<input type="hidden" name="f-registro-precos-item" value="'.$dRegistro_precos.'">
					<input type="hidden" name="f-modalidade-venda-item" value="'.$dModalidade_venda.'">
					<input type="hidden" name="f-v1-documentacao-selecionados-item" value="'.json_encode($dV1_documentacao_selecionados).'">
					<input type="hidden" name="f-transformacao-item" value="'.$dTransformacao.'">
					<input type="hidden" name="f-garantia-item" value="'.$dGarantia.'">
					<input type="hidden" name="f-origem-verba-item" value="'.$dOrigem_verba.'">
					<input type="hidden" name="f-isensao-impostos-item" value="'.$dIsencao_impostos.'">

					<div class="apl-row apl-bt apl-br apl-bb apl-bl">
						<span class="apl-lb w-238 bg-1 white center">Nome do Órgão</span>
						<input id="i-nome-orgao-item" class="apl-input w-388 bg-2 white" type="text" name="f-nome-orgao-item" maxlength="100" value="'.$dNome_orgao.'"'.$input_readonly.'>
						<span class="apl-lb w-180 bg-1 white center">Data da Licitação</span>
						<input id="i-data-licitacao-item" class="apl-input w-130 bg-2 white" type="text" name="f-data-licitacao-item" maxlength="10" value="'.$dData_licitacao.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-238 bg-1 white center">ID Licitação</span>
						<input id="i-id-licitacao-item" class="apl-input w-388 bg-2 white" type="text" name="f-id-licitacao-item" maxlength="10" value="'.$dId_licitacao.'" readonly>
						<span class="apl-lb w-180 bg-imp center">Registro de Preços</span>
						<div class="apl-lb w-130 bg-2">
							<a class="rbw'.(int)in_array($dRegistro_precos,array(1)).' cl-registro-precos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'registro-precos-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dRegistro_precos,array(2)).' cl-registro-precos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'registro-precos-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
					</div>
					<div id="hook-modalidade-venda" class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Modalidade da Venda</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:76px;">
						<a class="rb'.(int)in_array($dModalidade_venda, array(1)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',1);" style="left:40px;top:14px;">Compra Direta</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(2)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',2);" style="left:250px;top:14px;">Tomada de Preços</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(3)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',3);" style="left:460px;top:14px;">Pregão Presencial</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(4)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',4);" style="left:670px;top:14px;">Adesão a Registro de Preços</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(5)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',5);" style="left:40px;bottom:13px;">Carta Convite</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(6)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',6);" style="left:250px;bottom:13px;">Concorrência</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(7)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',7);" style="left:460px;bottom:13px;">Pregão Eletrônico</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(8)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',8);" style="left:670px;bottom:13px;">Aditivo Contratual</a>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-238 bg-1 white center">Site Pregão Eletrônico</span>
						<input id="i-site-pregao-eletronico-item" class="apl-input w-388 bg-3" type="text" name="f-site-pregao-eletronico-item" maxlength="100" value="'.$dSite_pregao_eletronico.'"'.$input_readonly.'>
						<span class="apl-lb w-180 bg-1 white center">Nº da Licitação</span>
						<input id="i-numero-licitacao-item" class="apl-input w-130 bg-3" type="text" name="f-numero-licitacao-item" maxlength="50" value="'.$dNumero_licitacao.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Documentação: Documentos Solicitados pelo Órgão (Licitações/Compra Direta)</span>
						<span class="apl-lb w-936 bg-1 white center italic">Dados do Participante pela VW (Concessionário / Pool)</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-100 bg-1 white center">Nome</span>
						<input id="i-participante-nome-item" class="apl-input w-836 bg-3" type="text" name="f-participante-nome-item" maxlength="100" value="'.$dParticipante_nome.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-100 bg-1 white center">RG</span>
						<input id="i-participante-rg-item" class="apl-input w-368 bg-3" type="text" name="f-participante-rg-item" maxlength="60" value="'.$dParticipante_rg.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-1 white center">CPF</span>
						<input id="i-participante-cpf-item" class="apl-input w-368 bg-3" type="text" name="f-participante-cpf-item" maxlength="14" value="'.$dParticipante_cpf.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Relacione os Documentos</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:165px;">
						<a class="cb'.(int)in_array(1, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',1);" style="left:40px;top:14px;">Atestado de Capacidade Técnica</a>
						<a class="cb'.(int)in_array(2, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',2);" style="left:40px;top:34px;">Ato Constitutivo</a>
						<a class="cb'.(int)in_array(3, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',3);" style="left:40px;top:54px;">Balanço Patrimonial</a>
						<a class="cb'.(int)in_array(4, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',4);" style="left:40px;top:74px;">Certidão de Tributos Estaduais</a>
						<a class="cb'.(int)in_array(5, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',5);" style="left:40px;top:94px;">Certidão de Tributos Federais / Divida Ativa da União (Internet)</a>
						<a class="cb'.(int)in_array(6, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',6);" style="left:40px;top:114px;">Certidão de Tributos Municipais</a>
						<a class="cb'.(int)in_array(7, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',7);" style="left:40px;top:134px;">CND INSS (Internet)</a>
						<a class="cb'.(int)in_array(8, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',8);" style="left:490px;top:14px;">CNDT - Certidão Negativa de Débitos Trabalhistas (Internet)</a>
						<a class="cb'.(int)in_array(9, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',9);" style="left:490px;top:34px;">CNPJ (Internet)</a>
						<a class="cb'.(int)in_array(10, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',10);" style="left:490px;top:54px;">Falência e Concordata</a>
						<a class="cb'.(int)in_array(11, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',11);" style="left:490px;top:74px;">FGTS (Internet)</a>
						<a class="cb'.(int)in_array(12, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',12);" style="left:490px;top:94px;">Ficha de Inscrição Estadual)</a>
						<a class="cb'.(int)in_array(13, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',13);" style="left:490px;top:114px;">Ficha de Inscrição Municipal</a>
						<a class="cb'.(int)in_array(14, $dV1_documentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'v1-documentacao-selecionados-item\',14);" style="left:490px;top:134px;">Procuração</a>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:112px;">
						<span class="apl-lb w-936 italic" style="margin-left:40px;">Relacione outros documentos que não constam entre os citados acima:</span>
						<textarea id="i-v1-documentacao-outros-item" class="apl-textarea" name="f-v1-documentacao-outros-item" maxlength="65535"'.$input_readonly.'>'.$dV1_documentacao_outros.'</textarea>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-100 bg-3 center">Model Code</span>
						<input id="i-model-code-item" class="apl-input w-100 bg-2 white" type="text" name="f-model-code-item" maxlength="6" value="'.$dModel_code.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-3 center">Cor</span>
						<input id="i-cor-item" class="apl-input w-100 bg-2 white" type="text" name="f-cor-item" maxlength="4" value="'.$dCor.'"'.$input_readonly.'>
						<span class="apl-lb w-128 bg-3 center">Opcionais (PR\'s)</span>
						<input id="i-opcionais-pr-item" class="apl-input w-408 bg-2 white" type="text" name="f-opcionais-pr-item" maxlength="65535" value="'.$dOpcionais_pr.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-100 bg-3 center">Motorização</span>
						<input id="i-motorizacao-item" class="apl-input w-100 bg-2 white" type="text" name="f-motorizacao-item" maxlength="3" value="'.$dMotorizacao.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-3 center">Potência</span>
						<input id="i-potencia-item" class="apl-input w-100 bg-2 white" type="text" name="f-potencia-item" maxlength="3" value="'.$dPotencia.'"'.$input_readonly.'>
						<span class="apl-lb w-128 bg-3 center">Combustível</span>
						<input id="i-combustivel-item" class="apl-input w-148 bg-2 white" type="text" name="f-combustivel-item" maxlength="10" value="'.$dCombustivel.'"'.$input_readonly.'>
						<span id="hook-transformacao" class="apl-lb w-130 bg-imp center">Transformação</span>
						<div class="apl-lb w-130 bg-2">
							<a class="rbw'.(int)in_array($dTransformacao, array(1)).' cl-transformacao-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'transformacao-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dTransformacao, array(2)).' cl-transformacao-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'transformacao-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Detalhar Transformação</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3">
						<textarea id="i-transformacao-detalhar-item" class="apl-textarea" name="f-transformacao-detalhar-item" maxlength="65535" style="margin-top:10px;"'.$input_readonly.'>'.$dTransformacao_detalhar.'</textarea>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-garantia" class="apl-lb w-300 bg-imp center">Garantia do(s) Veículo(s):</span>
						<div class="apl-lb w-636 bg-3">
							<a class="rb'.(int)in_array($dGarantia, array(1)).' cl-garantia-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-item\',1);" style="left:40px;top:7px;">Garantia Padrão</a>
							<a class="rb'.(int)in_array($dGarantia, array(2)).' cl-garantia-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-item\',2);" style="left:300px;top:7px;">Garantia Diferenciada</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Acessórios</span>
					</div>
					<div id="acessorios">
						'.$tAcessorios.'
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Informações da Proposta</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-130 bg-3 center">Preço Público</span>
						<input id="i-preco-publico-vw-item" class="apl-input w-160 bg-2 white" type="text" name="f-preco-publico-vw-item" maxlength="30" value="'.$dPreco_publico_vw.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-3 center">Desconto</span>
						<input id="i-v1-desconto-item" class="apl-input w-130 bg-2 white" type="text" name="f-v1-desconto-item" maxlength="20" value="'.$dV1_desconto.'"'.$input_readonly.'>
						<span class="apl-lb w-128 bg-3 center">Repasse (%)</span>
						<input id="i-repasse-concessionario-item" class="apl-input w-60 bg-2 white" type="text" name="f-repasse-concessionario-item" maxlength="3" value="'.$dRepasse_concessionario.'"'.$input_readonly.'>
						<span class="apl-lb w-130 bg-3 center">DN de Venda</span>
						<input id="i-dn-venda-item" class="apl-input w-98 bg-2 white" type="text" name="f-dn-venda-item" maxlength="4" value="'.$dDn_venda.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-130 bg-3 center">Preço Máximo</span>
						<input id="i-v1-preco-maximo-item" class="apl-input w-160 bg-2 white" type="text" name="f-v1-preco-maximo-item" maxlength="30" value="'.$dV1_preco_maximo.'"'.$input_readonly.'>
						<span class="apl-lb w-160 bg-3 center">Validade da Proposta</span>
						<input id="i-validade-proposta-item" class="apl-input w-258 bg-2 white" type="text" name="f-validade-proposta-item" maxlength="5" value="'.$dValidade_proposta.'"'.$input_readonly.'>
						<span class="apl-lb w-130 bg-3 center">DN de Entrega</span>
						<input id="i-dn-entrega-item" class="apl-input w-98 bg-2 white" type="text" maxlength="4" name="f-dn-entrega-item" maxlength="4" value="'.$dDn_entrega.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-130 bg-3 center">Prazo de Entrega</span>
						<input id="i-prazo-entrega-item" class="apl-input w-160 bg-2 white" type="text" name="f-prazo-entrega-item" maxlength="5" value="'.$dPrazo_entrega.'"'.$input_readonly.'>
						<span class="apl-lb w-160 bg-3 center">Prazo de Pagamento</span>
						<input id="i-v1-prazo-pagamento-item" class="apl-input w-258 bg-2 white" type="text" name="f-v1-prazo-pagamento-item" maxlength="60" value="'.$dV1_prazo_pagamento.'"'.$input_readonly.'>
						<span class="apl-lb w-130 bg-3 center">Quantidade</span>
						<input id="i-quantidade-veiculos-item" class="apl-input w-98 bg-2 white" type="text" name="f-quantidade-veiculos-item" maxlength="5" value="'.$dQuantidade_veiculos.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-130 bg-3 center">AVE</span>
						<input id="i-ave-item" class="apl-input w-160 bg-2 white" type="text" name="f-ave-item" maxlength="20" value="'.$dAve.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-3 center">Nº do Pool</span>
						<input id="i-v1-numero-pool-item" class="apl-input w-546 bg-2 white" type="text" name="f-v1-numero-pool-item" maxlength="65535" value="'.$dV1_numero_pool.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-origem-verba" class="apl-lb w-936 bg-1 white center">Origem da Verba</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:41px;">
						<a class="rb'.(int)in_array($dOrigem_verba, array(1)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',1);" style="left:40px;top:12px;">Federal</a>
						<a class="rb'.(int)in_array($dOrigem_verba, array(2)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',2);" style="left:250px;top:12px;">Estadual</a>
						<a class="rb'.(int)in_array($dOrigem_verba, array(3)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',3);" style="left:460px;top:12px;">Municipal</a>
						<a class="rb'.(int)in_array($dOrigem_verba, array(4)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',4);" style="left:670px;top:12px;">Convenio</a>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-isencao-impostos" class="apl-lb w-936 bg-1 white center">Isenções de Impostos do Órgão</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:106px;">
						<a class="rb'.(int)in_array($dIsencao_impostos, array(1)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',1);" style="left:40px;top:14px;">Não possui isenção</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(2)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',2);" style="left:250px;top:14px;">IPI</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(3)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',3);" style="left:460px;top:14px;">ICMS Substituto</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(4)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',4);" style="left:40px;bottom:43px;">IPI + ICMS</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(5)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',5);" style="left:250px;bottom:43px;">ICMS</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(6)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',6);" style="left:460px;bottom:43px;">IPI + ICMS Substituto</a>
						<span class="apl-lb italic" style="position: absolute; bottom: 6px; left: 40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via e-mail Lei/Decreto para que seja confirmado pelo Tributário.</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Observações Gerais</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3">
						<textarea id="i-observacoes-item" class="apl-textarea" name="f-observacoes-item" maxlength="65535" style="margin-top:10px;"'.$input_readonly.'>'.$dObservacoes.'</textarea>
					</div>
				</form>';

		else if ($dVersao == 2)
			$tApl = '<div style="position:relative;overflow:hidden;width:100%;">
				<form id="form-apl-item">
					<input type="hidden" name="f-versao-item" value="'.$dVersao.'">
					<input type="hidden" name="f-registro-precos-item" value="'.$dRegistro_precos.'">
					<input type="hidden" name="f-estado-item" value="'.$dEstado.'">
					<input type="hidden" name="f-modalidade-venda-item" value="'.$dModalidade_venda.'">
					<input type="hidden" name="f-eficiencia-energetica-item" value="'.$dEficiencia_energetica.'">
					<input type="hidden" name="f-transformacao-item" value="'.$dTransformacao.'">
					<input type="hidden" name="f-transformacao-prototipo-item" value="'.$dTransformacao_prototipo.'">
					<input type="hidden" name="f-acessorios-item" value="'.$dAcessorios.'">
					<input type="hidden" name="f-emplacamento-item" value="'.$dEmplacamento.'">
					<input type="hidden" name="f-licenciamento-item" value="'.$dLicenciamento.'">
					<input type="hidden" name="f-ipva-item" value="'.$dIpva.'">
					<input type="hidden" name="f-garantia-item" value="'.$dGarantia.'">
					<input type="hidden" name="f-garantia-prazo-item" value="'.$dGarantia_prazo.'">
					<input type="hidden" name="f-revisao-embarcada-item" value="'.$dRevisao_embarcada.'">
					<input type="hidden" name="f-limite-km-item" value="'.$dLimite_km.'">
					<input type="hidden" name="f-dn-venda-estado-item" value="'.$dDn_venda_estado.'">
					<input type="hidden" name="f-dn-entrega-estado-item" value="'.$dDn_entrega_estado.'">
					<input type="hidden" name="f-garantia-contrato-item" value="'.$dGarantia_contrato.'">
					<input type="hidden" name="f-origem-verba-item" value="'.$dOrigem_verba.'">
					<input type="hidden" name="f-origem-verba-tipo-item" value="'.$dOrigem_verba_tipo.'">
					<input type="hidden" name="f-isensao-impostos-item" value="'.$dIsencao_impostos.'">
					<input id="amarok-status" type="hidden" value="'.$amarok_object->status.'">
					<input id="amarok-long-filename" type="hidden" value="'.$amarok_object->long_filename.'">
					<input id="amarok-short-filename" type="hidden" value="'.$amarok_object->short_filename.'">
					<input id="amarok-file-size" type="hidden" value="'.$amarok_object->file_size.'">
					<input id="amarok-file" type="hidden" value="'.$amarok_object->file.'">

					<div class="apl-row apl-bt apl-br apl-bb apl-bl">
						<span class="apl-lb w-238 bg-1 white center">Nome do Órgão</span>
						<input id="i-nome-orgao-item" class="apl-input w-388 bg-2 white" type="text" name="f-nome-orgao-item" maxlength="100" value="'.$dNome_orgao.'"'.$input_readonly.'>
						<span class="apl-lb w-180 bg-1 white center">Data da Licitação</span>
						<input id="i-data-licitacao-item" class="apl-input w-130 bg-2 white" type="text" name="f-data-licitacao-item" maxlength="10" value="'.$dData_licitacao.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-238 bg-1 white center">Licitação Nro.</span>
						<input id="i-id-licitacao-item" class="apl-input w-388 bg-2 white" type="text" name="f-id-licitacao-item" maxlength="10" value="'.$dId_licitacao.'" readonly>
						<span class="apl-lb w-180 bg-imp center">Registro de Preços</span>
						<div class="apl-lb w-130 bg-2">
							<a class="rbw'.(int)in_array($dRegistro_precos,array(1)).' cl-registro-precos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'registro-precos-item\',1);" style="left: 14px; top: 7px;">Sim</a>
							<a class="rbw'.(int)in_array($dRegistro_precos,array(2)).' cl-registro-precos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'registro-precos-item\',2);" style="left: 70px; top: 7px;">Não</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-238 bg-1 white center">CNPJ de Faturamento</span>
						<input id="i-cnpj-faturamento-item" class="apl-input bg-2 white" style="width:388px;" type="text" name="f-cnpj-faturamento-item" maxlength="18" value="'.$dCnpj_faturamento.'"'.$input_readonly.'>
						<span class="apl-lb w-180 bg-1 white center">Estado</span>
						<div class="apl-lb w-130 bg-2">
							<a id="drop-estado-1" class="estado-drop drp"'.$check_readonly.' href="javascript:void(0);" onclick="dropEstado(1);" style="width:130px;padding-left:14px;"><span>'.$dEstado.'</span><img src="img/a-down-w.png"></a>
						</div>
					</div>
					<div id="hook-modalidade-venda" class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Modalidade da Venda</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:76px;">
						<a class="rb'.(int)in_array($dModalidade_venda, array(1)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',1);" style="left: 40px; top: 14px;">Compra Direta</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(2)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',2);" style="left: 250px; top: 14px;">Tomada de Preços</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(3)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',3);" style="left: 460px; top: 14px;">Pregão Presencial</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(4)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',4);" style="left: 670px; top: 14px;">Adesão a Registro de Preços</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(5)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',5);" style="left: 40px; bottom: 13px;">Carta Convite</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(6)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',6);" style="left: 250px; bottom: 13px;">Concorrência</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(7)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',7);" style="left: 460px; bottom: 13px;">Pregão Eletrônico</a>
						<a class="rb'.(int)in_array($dModalidade_venda, array(8)).' cl-modalidade-venda-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'modalidade-venda-item\',8);" style="left: 670px; bottom: 13px;">Aditivo Contratual</a>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-238 bg-1 white center">Site Pregão Eletrônico</span>
						<input id="i-site-pregao-eletronico-item" class="apl-input w-388 bg-3" type="text" name="f-site-pregao-eletronico-item" maxlength="100" value="'.$dSite_pregao_eletronico.'"'.$input_readonly.'>
						<span class="apl-lb w-180 bg-1 white center">Nº da Licitação</span>
						<input id="i-numero-licitacao-item" class="apl-input w-130 bg-3" type="text" name="f-numero-licitacao-item" maxlength="50" value="'.$dNumero_licitacao.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center italic">Dados do Participante</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-1 white center" style="width:270px;">Nome</span>
						<input id="i-participante-nome-item" class="apl-input bg-3" style="width:436px;" type="text" name="f-participante-nome-item" maxlength="100" value="'.$dParticipante_nome.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-1 white center">CPF</span>
						<input id="i-participante-cpf-item" class="apl-input w-368 bg-3" style="width:130px;" type="text" name="f-participante-cpf-item" maxlength="14" value="'.$dParticipante_cpf.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-1 white center" style="width:270px;">RG</span>
						<input id="i-participante-rg-item" class="apl-input bg-3" style="width:436px;" type="text" name="f-participante-rg-item" maxlength="60" value="'.$dParticipante_rg.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-1 white center">Telefone</span>
						<input id="i-participante-telefone-item" class="apl-input bg-3" style="width:130px;" type="text" name="f-participante-telefone-item" maxlength="20" value="'.$dParticipante_telefone.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-1 white center" style="width:270px;">Endereço p/ Envio da Documentação</span>
						<input id="i-participante-endereco-item" class="apl-input bg-3" style="width:436px;" type="text" name="f-participante-endereco-item" maxlength="140" value="'.$dParticipante_endereco.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-1 white center">CEP</span>
						<input id="i-participante-endereco-cep" class="apl-input bg-3" style="width:130px;" type="text" name="f-participante-endereco-cep" maxlength="10" value="'.$dParticipante_endereco_cep.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:130px;">Model Code</span>
						<input id="i-model-code-item" class="apl-input bg-2 white" style="width:130px;" type="text" name="f-model-code-item" maxlength="6" value="'.$dModel_code.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:100px;">Cor</span>
						<input id="i-cor-item" class="apl-input bg-2 white" style="width:130px;" type="text" name="f-cor-item" maxlength="4" value="'.$dCor.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:128px;">Ano/Modelo</span>
						<input id="i-ano-modelo-item" class="apl-input bg-2 white" style="width:318px;" type="text" name="f-ano-modelo-item" maxlength="60" value="'.$dAno_modelo.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:130px;">Motorização</span>
						<input id="i-motorizacao-item" class="apl-input bg-2 white" style="width:130px;" type="text" name="f-motorizacao-item" maxlength="3" value="'.$dMotorizacao.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:100px;">Potência</span>
						<input id="i-potencia-item" class="apl-input bg-2 white" style="width:130px;" type="text" name="f-potencia-item" maxlength="3" value="'.$dPotencia.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:128px;">Combustível</span>
						<input id="i-combustivel-item" class="apl-input bg-2 white" style="width:318px;" type="text" name="f-combustivel-item" maxlength="10" value="'.$dCombustivel.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:130px;">Opcionais (PR\'s)</span>
						<input id="i-opcionais-pr-item" class="apl-input bg-2 white" style="width:460px;" type="text" name="f-opcionais-pr-item" maxlength="65535" value="'.$dOpcionais_pr.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:216px;">Eficiência Energética CONPET</span>
						<div class="apl-lb w-130 bg-2">
							<a class="rbw'.(int)in_array($dEficiencia_energetica, array(1)).' cl-eficiencia-energetica-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'eficiencia-energetica-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dEficiencia_energetica, array(2)).' cl-eficiencia-energetica-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'eficiencia-energetica-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Transformação/Implementação Homologada (Custo VW)</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-transformacao" class="apl-lb w-130 bg-imp center">Transformação</span>
						<div class="apl-lb w-130 bg-2">
							<a class="rbw'.(int)in_array($dTransformacao, array(1)).' cl-transformacao-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'transformacao-item\',1);" style="left: 14px; top: 7px;">Sim</a>
							<a class="rbw'.(int)in_array($dTransformacao, array(2)).' cl-transformacao-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'transformacao-item\',2);" style="left: 70px; top: 7px;">Não</a>
						</div>
						<span class="apl-lb w-100 bg-3 center">Tipo</span>
						<input id="i-transformacao-tipo-item" class="apl-input bg-2 white" style="width:316px;" type="text" name="f-transformacao-tipo-item" maxlength="140" value="'.$dTransformacao_tipo.'"'.$input_readonly.'>
						<span class="apl-lb w-130 bg-3 center">Protótipo</span>
						<div class="apl-lb w-130 bg-2">
							<a class="rbw'.(int)in_array($dTransformacao_prototipo, array(1)).' cl-transformacao-prototipo-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'transformacao-prototipo-item\',1);" style="left: 14px; top: 7px;">Sim</a>
							<a class="rbw'.(int)in_array($dTransformacao_prototipo, array(2)).' cl-transformacao-prototipo-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'transformacao-prototipo-item\',2);" style="left: 70px; top: 7px;">Não</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bl bg-3">
						<span class="apl-lb w-936 italic" style="margin-left:40px;">Detalhar transformação</span>
						<textarea id="i-transformacao-detalhar-item" class="apl-textarea" name="f-transformacao-detalhar-item" maxlength="65535"'.$input_readonly.'>'.$dTransformacao_detalhar.'</textarea>
					</div>
					<div id="cl-amarok" class="apl-row apl-br apl-bl bg-3" style="padding-top:10px;padding-bottom:10px;"'.$check_readonly.'>
						<span class="apl-cl-label">Check List Transformação AMAROK</span>
						<div class="apl-upl-box"'.$amarok_upload_box.'>
							<a class="ubtn" href="javascript:void(0);" onclick="selectFileAPL_AMAROK();">Selecionar arquivo<img src="img/upload.png"></a>
							<a class="dlnk" href="c.download-apl.php" target="_blank">Baixar modelo aqui</a>
							<span>Obrigatório o preenchimento e envio do check list.</span>
						</div>
						<div class="apl-upl-loading" style="display:none;">
							<div class="bar"></div>
							<div class="text"></div>
						</div>
						<div class="apl-upl-ready"'.$amarok_upload_ready.'>
							<span>'.$amarok_upload_anexo.'</span>
							<a class="uplcancel" href="javascript:void(0);" onclick="cancelUploadAPL_AMAROK();" title="Cancelar"></a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-1 center white">
						<div style="display:inline-block;overflow:hidden;">
							<span id="hook-acessorios" class="apl-lb">Acessórios (Custo pago pelo DN)</span>
							<a class="rbw'.(int)in_array($dAcessorios, array(1)).' cl-acessorios-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'acessorios-item\',1);" style="position:relative;float:left;margin:8px 0 0 40px;">Sim</a>
							<a class="rbw'.(int)in_array($dAcessorios, array(2)).' cl-acessorios-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'acessorios-item\',2);" style="position:relative;float:left;margin:8px 0 0 14px;">Não</a>
						</div>
					</div>
					<div id="acessorios"'.$acessorios_sim_nao.'>
						'.$tAcessorios.'
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-emplacamento" class="apl-lb bg-3 center" style="width:182px;">Emplacamento</span>
						<div class="apl-lb bg-2" style="width:130px;">
							<a class="rbw'.(int)in_array($dEmplacamento, array(1)).' cl-emplacamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'emplacamento-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dEmplacamento, array(2)).' cl-emplacamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'emplacamento-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
						<span class="apl-lb bg-3 center" style="width:182px;">Licenciamento</span>
						<div class="apl-lb bg-2" style="width:130px;">
							<a class="rbw'.(int)in_array($dLicenciamento, array(1)).' cl-licenciamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'licenciamento-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dLicenciamento, array(2)).' cl-licenciamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'licenciamento-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
						<span class="apl-lb bg-3 center" style="width:182px;">IPVA</span>
						<div class="apl-lb bg-2" style="width:130px;">
							<a class="rbw'.(int)in_array($dIpva, array(1)).' cl-ipva-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'ipva-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dIpva, array(2)).' cl-ipva-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'ipva-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Garantia e Revisões</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-garantia" class="apl-lb w-300 bg-imp center">Garantia</span>
						<div class="apl-lb w-636 bg-2">
							<a class="rbw'.(int)in_array($dGarantia, array(1)).' cl-garantia-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-item\',1);" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Padrão</a>
							<a class="rbw'.(int)in_array($dGarantia, array(2)).' cl-garantia-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-item\',2);" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Diferenciada</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-garantia-prazo" class="apl-lb bg-3 center" style="width:300px;">Prazo de Garantia</span>
						<input id="i-garantia-prazo-outro-item" class="apl-input bg-2 white" style="display:none;width:324px;padding-right:32px;" type="text" name="f-garantia-prazo-outro-item" maxlength="40" value="'.$dGarantia_prazo_outro.'"'.$input_readonly.'>
						<div id="i-garantia-prazo-drop-item" class="apl-lb bg-2" style="width:324px;">
							<a id="drop-estado-4" class="estado-drop drp"'.$check_readonly.' href="javascript:void(0);" onclick="dropEstado(4);" style="width:324px;padding-left:14px;"><span>'.$dGarantia_prazo_str.'</span><img src="img/a-down-w.png"></a>
						</div>
						<a id="i-garantia-prazo-x-item" class="xed" href="javascript:void(0);" onclick="hidePGed();"></a>
						<span id="hook-revisao-embarcada" class="apl-lb bg-3 center" style="width:182px;">Revisão Embarcada</span>
						<div class="apl-lb bg-2" style="width:130px;">
							<a class="rbw'.(int)in_array($dRevisao_embarcada, array(1)).' cl-revisao-embarcada-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'revisao-embarcada-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dRevisao_embarcada, array(2)).' cl-revisao-embarcada-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'revisao-embarcada-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:300px;">Quantidade de Revisões Inclusas</span>
						<input id="i-quantidade-revisoes-inclusas-item" class="apl-input bg-2 white" style="width:78px;" type="text" name="f-quantidade-revisoes-inclusas-item" maxlength="5" value="'.$dQuantidade_revisoes_inclusas.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:182px;">Limite de KM</span>
						<div class="apl-lb bg-2" style="width:150px;">
							<a class="rbw'.(int)in_array($dLimite_km, array(1)).' cl-limite-km-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'limite-km-item\',1);" style="left: 14px; top: 7px;">Sim</a>
							<a class="rbw'.(int)in_array($dLimite_km, array(2)).' cl-limite-km-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'limite-km-item\',2);" style="left: 70px; top: 7px;">Não</a>
						</div>
						<span id="i-limite-kmed-item-lb" class="apl-lb bg-2 center" style="display:'.$kmkm.';width:39px;color:#f1f1f1;border-left:1px solid #cccccc;">KM:</span>
						<input id="i-limite-km-km-item" class="apl-input bg-2 white" style="display:'.$kmkm.';width:186px;" type="text" name="f-limite-km-km-item" maxlength="10" value="'.$dLimite_km_km.'"'.$input_readonly.'>
						<div id="i-limite-kmed-item-fake" class="apl-lb bg-2" style="display:'.$kmkmf.';width:226px;"></div>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-3 center" style="color:#666666;"><a style="display:inline;cursor:default;font-weight:bold;color:#ff0000;">Atenção:</a> AMAROK LIMITE 100 MIL KM&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GARANTIA PADRÃO 3 ANOS</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Informações da Proposta</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:150px;">Preço Público VW</span>
						<input id="i-preco-publico-vw-item" class="apl-input bg-2 white" style="width:148px;" type="text" name="f-preco-publico-vw-item" maxlength="30" value="'.$dPreco_publico_vw.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:150px;">Preço Ref. Edital</span>
						<input id="i-preco-ref-edital-item" class="apl-input bg-2 white" style="width:148px;" type="text" name="f-preco-ref-edital-item" maxlength="30" value="'.$dPreco_ref_edital.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:190px;">Quantidade de Veículos</span>
						<input id="i-quantidade-veiculos-item" class="apl-input bg-2 white" style="width:150px;" type="text" name="f-quantidade-veiculos-item" maxlength="5" value="'.$dQuantidade_veiculos.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:150px;">DN de Venda</span>
						<input id="i-dn-venda-item" class="apl-input bg-2 white" style="width:148px;" type="text" name="f-dn-venda-item" maxlength="4" value="'.$dDn_venda.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:150px;">Estado DN de Venda</span>
						<div class="apl-lb bg-2" style="width:148px;">
							<a id="drop-estado-2" class="estado-drop drp"'.$check_readonly.' href="javascript:void(0);" onclick="dropEstado(2);" style="width:148px;padding-left:14px;"><span>'.$dDn_venda_estado.'</span><img src="img/a-down-w.png"></a>
						</div>
						<span class="apl-lb bg-3 center" style="width:190px;">Repasse Concessionário (%)</span>
						<input id="i-repasse-concessionario-item" class="apl-input bg-2 white" style="width:150px;" type="text" name="f-repasse-concessionario-item" maxlength="3" value="'.$dRepasse_concessionario.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:150px;">DN de Entrega</span>
						<input id="i-dn-entrega-item" class="apl-input bg-2 white" style="width:148px;" type="text" name="f-dn-entrega-item" maxlength="4" value="'.$dDn_entrega.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:150px;">Estado DN de Entrega</span>
						<div class="apl-lb bg-2" style="width:148px;">
							<a id="drop-estado-3" class="estado-drop drp"'.$check_readonly.' href="javascript:void(0);" onclick="dropEstado(3);" style="width:148px;padding-left:14px;"><span>'.$dDn_entrega_estado.'</span><img src="img/a-down-w.png"></a>
						</div>
						<span class="apl-lb bg-3 center" style="width:190px;">Prazo Entrega</span>
						<input id="i-prazo-entrega-item" class="apl-input bg-2 white" style="width:150px;" type="text" name="f-prazo-entrega-item" maxlength="5" value="'.$dPrazo_entrega.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:150px;">Validade da Proposta</span>
						<input id="i-validade-proposta-item" class="apl-input bg-2 white" style="width:148px;" type="text" name="f-validade-proposta-item" maxlength="5" value="'.$dValidade_proposta.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:150px;">Vigência do Contrato</span>
						<input id="i-vigencia-contrato-item" class="apl-input bg-2 white" style="width:148px;" type="text" name="f-vigencia-contrato-item" maxlength="20" value="'.$dVigencia_contrato.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:190px;">Prazo Pagamento</span>
						<input id="i-prazo-pagamento-item" class="apl-input bg-2 white" style="width:150px;" type="text" name="f-prazo-pagamento-item" maxlength="5" value="'.$dPrazo_pagamento.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb bg-3 center" style="width:240px;">AVE (enviar eletronic. no Sivolks)</span>
						<input id="i-ave-item" class="apl-input bg-2 white" style="width:266px;" type="text" name="f-ave-item" maxlength="20" value="'.$dAve.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:280px;">Multas e Sanções – Indicar ítem do Edital</span>
						<input id="i-multas-sansoes-item" class="apl-input bg-2 white" style="width:150px;" type="text" name="f-multas-sansoes-item" maxlength="65535" value="'.$dMultas_sansoes.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-garantia-contrato" class="apl-lb bg-3 center" style="width:240px;">Garantia de Contrato</span>
						<div class="apl-lb bg-2" style="width:266px;">
							<a class="rbw'.(int)in_array($dGarantia_contrato, array(1)).' cl-garantia-contrato-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-contrato-item\',1);" style="left:14px;top:7px;">Sim</a>
							<a class="rbw'.(int)in_array($dGarantia_contrato, array(2)).' cl-garantia-contrato-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-contrato-item\',2);" style="left:70px;top:7px;">Não</a>
						</div>
						<span class="apl-lb bg-3 center" style="width:70px;">Prazo</span>
						<input id="i-prazo-item" class="apl-input bg-2 white" style="width:140px;" type="text" name="f-prazo-item" maxlength="20" value="'.$dPrazo.'"'.$input_readonly.'>
						<span class="apl-lb bg-3 center" style="width:70px;">Valor</span>
						<input id="i-vlr-item" class="apl-input bg-2 white" style="width:150px;" type="text" name="f-vlr-item" maxlength="20" value="'.$dValor.'"'.$input_readonly.'>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-origem-verba" class="apl-lb w-936 bg-1 white center">Origem da Verba</span>
					</div>
					<div class="apl-row apl-br apl-bl bg-3" style="height:41px;">
						<a class="rb'.(int)in_array($dOrigem_verba, array(1)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',1);" style="left:40px;top:12px;">Federal</a>
						<a class="rb'.(int)in_array($dOrigem_verba, array(2)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',2);" style="left:250px;top:12px;">Estadual</a>
						<a class="rb'.(int)in_array($dOrigem_verba, array(3)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',3);" style="left:460px;top:12px;">Municipal</a>
						<a class="rb'.(int)in_array($dOrigem_verba, array(4)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',4);" style="left:670px;top:12px;">Convenio</a>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-2" style="height:41px;">
						<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(1)).' cl-origem-verba-tipo-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-tipo-item\',1);" style="left: 40px; top: 12px;">A Vista</a>
						<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(2)).' cl-origem-verba-tipo-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-tipo-item\',2);" style="left: 250px; top: 12px;">A Prazo</a>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span id="hook-isencao-impostos" class="apl-lb w-936 bg-1 white center">Isenções de Impostos do Órgão</span>
					</div>
					<div class="apl-row apl-br apl-bl bg-3" style="height:80px;">
						<a class="rb'.(int)in_array($dIsencao_impostos, array(1)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',1);" style="left:40px;top:14px;">Não possui isenção</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(2)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',2);" style="left:250px;top:14px;">IPI</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(3)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',3);" style="left:460px;top:14px;">ICMS Substituto</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(4)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',4);" style="left:40px;top:46px;">IPI + ICMS</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(5)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',5);" style="left:250px;top:46px;">ICMS</a>
						<a class="rb'.(int)in_array($dIsencao_impostos, array(6)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',6);" style="left:460px;top:46px;">IPI + ICMS Substituto</a>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3">
						<span class="apl-lb w-936 italic" style="margin-left:40px;">Indicar item do edital ou Lei que confirme a marcação acima</span>
						<textarea id="i-imposto-indicar-item" class="apl-textarea" name="f-imposto-indicar-item" maxlength="65535"'.$input_readonly.'>'.$dImposto_indicar.'</textarea>
						<span class="apl-lb italic" style="margin-left:40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via chat GELIC Lei/Decreto para que seja confirmado pelo Tributário.</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-936 bg-1 white center">Observações Gerais</span>
					</div>
					<div class="apl-row apl-br apl-bb apl-bl bg-3">
						<textarea id="i-observacoes-item" class="apl-textarea" name="f-observacoes-item" maxlength="65535" style="margin-top:10px;"'.$input_readonly.'>'.$dObservacoes.'</textarea>
					</div>
					'.$aceite.'
				</form>';





		$info = '';
		$dTexto = '';
		$aPlanta = array("","TBT/SP (0024-46)","SBC/SP","SJP/PR (0103-84)");
		if ($tipo == 2 || $tipo == 4) //aprovada, reprovada
		{
			$db->query("
SELECT 
	his.texto,
	his.data_hora,
	his.ip, 
	cli.nome,
    apr.ave,
    apr.quantidade,
	apr.model_code,
	apr.cor,
	apr.opcionais_pr,
	apr.preco_publico,
	apr.prazo_de_entrega,
	apr.planta,
	apr.desconto_vw,
	apr.comissao_dn,
	apr.valor_da_transformacao,
	apr.nome_arquivo,
	apr.arquivo,
    item.item,
    lote.lote,
	(SELECT descricao FROM gelic_motivos WHERE id = his.id_valor_1) AS motivo, 
	(SELECT descricao FROM gelic_motivos WHERE id = his.id_valor_2) AS submotivo
FROM 
	gelic_licitacoes_apl_historico AS his
    INNER JOIN gelic_clientes AS cli ON cli.id = his.id_cliente
    INNER JOIN gelic_licitacoes_apl AS apl ON apl.id = $dId_apl
    INNER JOIN gelic_licitacoes_itens AS item ON item.id = apl.id_item
    INNER JOIN gelic_licitacoes_lotes AS lote ON lote.id = item.id_lote
    LEFT JOIN gelic_licitacoes_apl_aprovadas AS apr ON apr.id_apl_historico = his.id AND apr.ativo = 1
WHERE 
	his.id_apl = $dId_apl
ORDER BY 
	his.id 
DESC LIMIT 1");
			$db->nextRecord();
			if ($tipo == 2)
			{
				$apr_tbl = '';

				if (strlen($db->f("ave")) > 0)
				{
					if ($db->f("valor_da_transformacao") > 0)
						$valor_transf = '<tr><td class="apr-tbl-lb">VALOR DA TRANSFORMAÇÃO:</td><td class="apr-tbl-vl">R$ '.number_format($db->f("valor_da_transformacao"),2,',','.').'</td></tr>';
					else
						$valor_transf = '';

					if (strlen($db->f("arquivo")) > 0)
						$anexo = '<tr><td class="apr-tbl-lb">ANEXO:</td><td><a class="ablue" href="arquivos/apr/'.$db->f("arquivo").'" target="_blank">'.utf8_encode($db->f("nome_arquivo")).'</a></td></tr>';
					else
						$anexo = '';

					$apr_tbl = '<br><br><table class="apr-tbl">
	<tr>
		<td class="apr-tbl-lb">LOTE:</td>
		<td class="apr-tbl-vl">'.utf8_encode($db->f("lote")).'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">ITEM:</td>
		<td class="apr-tbl-vl">'.utf8_encode($db->f("item")).'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">AVE:</td>
		<td class="apr-tbl-vl">'.utf8_encode($db->f("ave")).'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">QUANTIDADE:</td>
		<td class="apr-tbl-vl">'.$db->f("quantidade").'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">MODEL CODE:</td>
		<td class="apr-tbl-vl">'.utf8_encode($db->f("model_code")).'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">COR:</td>
		<td class="apr-tbl-vl">'.utf8_encode($db->f("cor")).'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">OPCIONAIS (PR\'s):</td>
		<td class="apr-tbl-vl">'.utf8_encode($db->f("opcionais_pr")).'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">PREÇO PÚBLICO:</td>
		<td class="apr-tbl-vl">R$ '.number_format($db->f("preco_publico"),2,',','.').'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">PRAZO DE ENTREGA:</td>
		<td class="apr-tbl-vl">'.$db->f("prazo_de_entrega").'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">PLANTA:</td>
		<td class="apr-tbl-vl">'.$aPlanta[$db->f("planta")].'</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">DESCONTO VW:</td>
		<td class="apr-tbl-vl">'.number_format($db->f("desconto_vw"),2,',','.').' %</td>
	</tr>
	<tr>
		<td class="apr-tbl-lb">COMISSÃO DN:</td>
		<td class="apr-tbl-vl">'.number_format($db->f("comissao_dn"),2,',','.').' %</td>
	</tr>
	'.$valor_transf.$anexo.'
</table>';
				}

				$info .= '<div class="apl-row" style="text-align: left; font-size: 11px;">APL aprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora"),11).'</span> por <span class="bold italic">'.utf8_encode($db->f("nome")).'</span> de <span class="t-red">'.$db->f("ip").'</span></span>'.$apr_tbl.'</div>';
			}
			else if ($tipo == 4)
				$info .= '<div class="apl-row" style="text-align: left; font-size: 11px;">APL reprovada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora"),11).'</span> por <span class="bold italic">'.utf8_encode($db->f("nome")).'</span> de <span class="t-red">'.$db->f("ip").'</span></span></div>';

			if (strlen($db->f("texto")) > 0)
				$dTexto = utf8_encode($db->f("texto"));

			if ($tipo == 2 && strlen($dTexto) > 0)
				$info .= '<br><span class="bold">Observações:</span><br>'.nl2br($dTexto);
			else if ($tipo == 4)
			{
				$info .= '<br><span class="bold">Motivo:</span><br><span class="gray-88">'.utf8_encode($db->f("motivo")).'</span>';
				if (strlen($db->f("submotivo")))
					$info .= ' <span class="gray-88 italic">('.utf8_encode($db->f("submotivo")).')</span>';

				if (strlen($dTexto) > 0)
					$info .= '<br><br><span class="bold">Observações:</span><br>'.nl2br($dTexto);
			}
		}



		if ($dados)
		{
			$db->query("
SELECT 
	his.ip,
	his.data_hora,
	cli.id_parent,
	cli.nome,
	clidn.nome AS dn_nome
FROM
	gelic_licitacoes_apl_historico AS his,
	gelic_licitacoes_apl AS apl,
    gelic_clientes AS cli
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	his.id_apl = apl.id AND
	his.id = (SELECT MAX(id) FROM gelic_licitacoes_apl_historico WHERE id_apl = $dId_apl AND tipo = 1) AND
	his.id_cliente = cli.id");
			if ($db->nextRecord())
			{
				$por = utf8_encode($db->f("nome"));
				if ($db->f("id_parent") > 0)
					$por .= ' (DN: '.utf8_encode($db->f("dn_nome")).')';

				$tApl .= '<div class="apl-row" style="text-align: left; font-size: 11px; padding: 0 40px;">APL preenchida e enviada em <span class="bold">'.mysqlToBr(substr($db->f("data_hora"),0,10)).'</span> às <span class="bold gray-88">'.substr($db->f("data_hora"),11).'</span> por <span class="bold italic">'.$por.'</span> de <span class="t-red">'.$db->f("ip").'</span></span></div>';
			}
		}



		// Verificar se eh preciso mostrar o botao de historico de aprovacoes
		$historico_btn = '';
		if ($dados)
		{
			$db->query("
SELECT
	apl.id
FROM
	gelic_licitacoes_apl AS apl
    INNER JOIN gelic_licitacoes_apl_historico AS ahis ON ahis.id_apl = apl.id AND ahis.tipo IN (2,4,5,6)
WHERE
	apl.id_licitacao = $pId_licitacao AND
    apl.id_item = $pId_item AND
    (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
LIMIT 1");
			if ($db->nextRecord())
				$historico_btn = '<a class="bt-style-2 fr" href="javascript:void(0);" onclick="licAPL_historico('.$pId_item.','.$cliente_parent.');">Histórico de Aprovações/Reprovações</a>';
		}


		if (!$readonly)
			$tApl .= '<div class="apl-row" style="margin: 20px 0; padding: 0 40px;">
				<a class="bt-style-1 fl" href="javascript:void(0);" onclick="salvarAPL_item('.$pId_item.', false);">Enviar APL</a>
				'.$historico_btn.'
			</div></div>';

		if ($tipo == 2)
			$tApl .= '<div class="apl-row t13" style="margin: 20px 0; padding: 0 40px; text-align: left; line-height: normal;"><span class="bold t-green t20" style="font-size:20px;">Aprovada!</span>'.$info.'<br><br>'.$historico_btn.'</div></div>';
		else if ($tipo == 4)
			$tApl .= '<div class="apl-row t13" style="margin: 20px 0; padding: 0 40px; text-align: left; line-height: normal;"><span class="bold t-red t20" style="font-size:20px;">Reprovada.</span>'.$info.'<br><br>'.$historico_btn.'</div></div>';


		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $tApl;
	}
}
echo json_encode($aReturn);

?>
