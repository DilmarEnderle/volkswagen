<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'');
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$pId_cd = intval($_POST["id-cd"]);

	$xAccess = explode(" ",getAccess());

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$add_to_where = "(apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent)) AND";
	}
	else
	{
		$add_to_where = "";
	}


	// Preencher valores padrao para uma APL vazia
	$tApl = '';
	$dNome_orgao = "";
	$dModalidade_da_venda = 0;
	$dNumero_pregao = "";
	$dDocumentacao_nome = "";
	$dDocumentacao_rg = "";
	$dDocumentacao_cpf = "";
	$dDocumentacao_selecionados = json_decode("[]");
	$dDocumentacao_outros = "";
	$dModel_code = "";
	$dCor = "";
	$dOpcionais_pr = "";
	$dMotorizacao = "";
	$dPotencia = "";
	$dCombustivel = "";
	$dTransformacao = 0;
	$dDetalhamento_transformacao = "";
	$dGarantia = 0;
	$dAcessorio_1 = "";
	$dValor_1 = "";
	$dAcessorio_2 = "";
	$dValor_2 = "";
	$dAcessorio_3 = "";
	$dValor_3 = "";
	$dAcessorio_4 = "";
	$dValor_4 = "";
	$dPreco_publico = "";
	$dDesconto = "";
	$dRepasse = "";
	$dDn_venda = "";
	$dPreco_maximo = "";
	$dValidade_da_proposta = "";
	$dDn_entrega = "";
	$dPrazo_de_entrega = "";
	$dPrazo_de_pagamento = "";
	$dQuantidade = "";
	$dAve = "";
	$dNumero_pool = "";
	$dOrigem_da_verba = 0;
	$dIsencao_de_impostos = 0;
	$dObservacoes_gerais = "";
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

	$db = new Mysql();

	// Preencher campos automaticamente
	$db->query("SELECT orgao, tipo FROM gelic_comprasrp WHERE id = $pId_cd");
	$db->nextRecord();
	$dNome_orgao = utf8_encode($db->f("orgao"));
	$dModalidade_da_venda = intval($db->f("tipo"));
	$dSrp_show = 'none';
	if ($dModalidade_da_venda == 2)
		$dSrp_show = 'block';


	$tipo = 0;
	$dados = false;
	$readonly = false;



	// Buscar dados da ultima APL enviada
	$db->query("
SELECT 
	apl.*,
	(SELECT tipo FROM gelic_comprasrp_apl_historico WHERE id_apl = apl.id AND tipo < 5 ORDER BY id DESC LIMIT 1) AS tipo
FROM
	gelic_comprasrp_apl AS apl
WHERE
	{$add_to_where}
	apl.id = (SELECT MAX(id) FROM gelic_comprasrp_apl WHERE id_comprasrp = $pId_cd)");
	if ($db->nextRecord())
	{
		// APL ENCONTRADA
		$tipo = $db->f("tipo");

		// Verificar se o DN tem acesso para editar
		if (!in_array("cd_apl_enviar", $xAccess))
			$readonly = true;

		// Verificar se ja foi aprovada ou reprovada
		if ($tipo == 2 || $tipo == 4) //aprovada, reprovada
			$readonly = true;

		$dados = true;
	}


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
		$dNome_orgao = utf8_encode($db->f("nome_orgao"));
		$dModalidade_da_venda = intval($db->f("modalidade_da_venda"));
		$dNumero_pregao = utf8_encode($db->f("numero_pregao"));
		$dDocumentacao_nome = utf8_encode($db->f("documentacao_nome"));
		$dDocumentacao_rg = utf8_encode($db->f("documentacao_rg"));
		$dDocumentacao_cpf = $db->f("documentacao_cpf");
		$dDocumentacao_selecionados = json_decode($db->f("documentacao_selecionados"));
		$dDocumentacao_outros = utf8_encode($db->f("documentacao_outros"));
		$dModel_code = utf8_encode($db->f("model_code"));
		$dCor = utf8_encode($db->f("cor"));
		$dOpcionais_pr = utf8_encode($db->f("opcionais_pr"));
		$dMotorizacao = utf8_encode($db->f("motorizacao"));
		$dPotencia = utf8_encode($db->f("potencia"));
		$dCombustivel = utf8_encode($db->f("combustivel"));
		$dTransformacao = intval($db->f("transformacao"));
		$dDetalhamento_transformacao = utf8_encode($db->f("detalhamento_transformacao"));
		$dGarantia = intval($db->f("garantia"));
		$dPreco_publico = "R$ ".number_format($db->f("preco_publico"), 2, ",", ".");
		if ($dPreco_publico == "R$ 0,00") $dPreco_publico = "";
		$dDesconto = utf8_encode($db->f("desconto"));
		$dRepasse = intval($db->f("repasse"));
		if ($dRepasse == 0) $dRepasse = "";
		$dDn_venda = utf8_encode($db->f("dn_venda"));
		$dPreco_maximo = "R$ ".number_format($db->f("preco_maximo"), 2, ",", ".");
		if ($dPreco_maximo == "R$ 0,00") $dPreco_maximo = "";
		$dValidade_da_proposta = intval($db->f("validade_da_proposta"));
		if ($dValidade_da_proposta == 0) $dValidade_da_proposta = "";
		$dDn_entrega = utf8_encode($db->f("dn_entrega"));
		$dPrazo_de_entrega = intval($db->f("prazo_de_entrega"));
		if ($dPrazo_de_entrega == 0) $dPrazo_de_entrega = "";
		$dPrazo_de_pagamento = utf8_encode($db->f("prazo_de_pagamento"));
		$dQuantidade = intval($db->f("quantidade"));
		if ($dQuantidade == 0) $dQuantidade = "";
		$dAve = utf8_encode($db->f("ave"));
		$dNumero_pool = utf8_encode($db->f("no_pool"));
		$dOrigem_da_verba = intval($db->f("origem_da_verba"));
		$dIsencao_de_impostos = intval($db->f("isencao_de_impostos"));
		$dObservacoes_gerais = utf8_encode($db->f("observacoes_gerais"));

		//acessorios
		$tAcessorios = '';
		$db->query("SELECT acessorio, valor FROM gelic_comprasrp_apl_acessorios WHERE id_apl = $dId_apl ORDER BY id");
		while ($db->nextRecord())
		{
			$dAcessorio = utf8_encode($db->f("acessorio"));
			$dValor = "R$ ".number_format($db->f("valor"), 2, ",", ".");
			if ($dValor == "R$ 0,00") $dValor = "";

			if ($readonly)
			{
				$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
					<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
					<input class="apl-input w-576 bg-2 white" type="text" maxlength="1000" value="'.$dAcessorio.'"'.$input_readonly.'>
					<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
					<input class="apl-input w-162 bg-2 white" type="text" style="text-align: right;" value="'.$dValor.'"'.$input_readonly.'>
				</div>';
			}
			else
			{
				if ($db->Row[0] < 5)
					$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
						<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000" value="'.$dAcessorio.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
						<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;" value="'.$dValor.'"'.$input_readonly.'>
						<span class="rm-acess-dummy"></span>
					</div>';
				else
					$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
						<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
						<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000" value="'.$dAcessorio.'"'.$input_readonly.'>
						<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
						<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;" value="'.$dValor.'"'.$input_readonly.'>
						<a class="rm-acess" href="javascript:void(0);" onclick="removerAcessorio(this);" title="Remover Acessório"></a>
					</div>';
			}
		}

		if (!$readonly)
			$tAcessorios .= '<div id="aaBtn-row" class="apl-row apl-br apl-bb apl-bl" style="background-color:#efefef;">
				<a class="bt-apl-style-1 fl" href="javascript:void(0);" onclick="adicionarAcessorio();" title="Adicionar Acessório" style="margin-bottom: 6px; border-bottom-right-radius:5px;">+</a>
			</div>';
	}


	$tApl = '<div style="position: relative; overflow: hidden; width: 100%;">
	<div class="apl-bl apl-br apl-bt" style="position: relative; text-align: center; height: 50px;">
		<h4 style="display:inline-block;position:absolute;left:0;top:5px;line-height:40px;width:938px;text-align:center;">APL - AUTORIZAÇÃO DE PARTICIPAÇÃO EM LICITAÇÕES</h4>
	</div>
	<form id="form-apl-cd">
	<input type="hidden" name="f-mdl" value="'.$dModalidade_da_venda.'">
	<input type="hidden" name="f-docs" value="'.json_encode($dDocumentacao_selecionados).'">
	<input type="hidden" name="f-trans" value="'.$dTransformacao.'">
	<input type="hidden" name="f-gar" value="'.$dGarantia.'">
	<input type="hidden" name="f-verba" value="'.$dOrigem_da_verba.'">
	<input type="hidden" name="f-imp" value="'.$dIsencao_de_impostos.'">

	<div class="apl-row apl-bt apl-br apl-bb apl-bl">
		<span class="apl-lb w-238 bg-1 white center">Nome do Órgão</span>
		<input id="i-nome-orgao" class="apl-input w-700 bg-3" type="text" name="f-nome-orgao" maxlength="100" value="'.$dNome_orgao.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-238 bg-1 white center">Modalidade</span>
		<div class="apl-lb w-700 bg-4">
			<a class="rb'.(int)in_array($dModalidade_da_venda, array(1)).'" data-mode="readonly" href="javascript:void(0);" style="left: 140px; top: 7px;">Compra Direta</a>
			<a class="rb'.(int)in_array($dModalidade_da_venda, array(2)).'" data-mode="readonly" href="javascript:void(0);" style="left: 360px; top: 7px;">Adesão a Registro de Preços</a>
		</div>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl" style="display:'.$dSrp_show.';">
		<span class="apl-lb w-238 bg-1 white center">Nº do Pregão</span>
		<input id="i-numero-pregao" class="apl-input w-700 bg-3" type="text" name="f-numero-pregao" maxlength="20" value="'.$dNumero_pregao.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl" style="height: 6px;"></div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Documentação: Documentos Solicitados pelo Órgão (Licitações/Compra Direta)</span>
		<span class="apl-lb w-938 bg-1 white center italic">Dados do Participante pela VW (Concessionário / Pool)</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-100 bg-1 white center">Nome</span>
		<input id="i-doc-nome" class="apl-input w-838 bg-3" type="text" name="f-doc-nome" maxlength="100" value="'.$dDocumentacao_nome.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-100 bg-1 white center">RG</span>
		<input id="i-doc-rg" class="apl-input w-368 bg-3" type="text" name="f-doc-rg" maxlength="50" value="'.$dDocumentacao_rg.'"'.$input_readonly.'>
		<span class="apl-lb w-100 bg-1 white center">CPF</span>
		<input id="i-doc-cpf" class="apl-input w-370 bg-3" type="text" name="f-doc-cpf" value="'.$dDocumentacao_cpf.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Relacione os Documentos</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 165px;">
		<a class="cb'.(int)in_array(1, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',1);" style="left: 40px; top: 14px;">Atestado de Capacidade Técnica</a>
		<a class="cb'.(int)in_array(2, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',2);" style="left: 40px; top: 34px;">Ato Constitutivo</a>
		<a class="cb'.(int)in_array(3, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',3);" style="left: 40px; top: 54px;">Balanço Patrimonial</a>
		<a class="cb'.(int)in_array(4, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',4);" style="left: 40px; top: 74px;">Certidão de Tributos Estaduais</a>
		<a class="cb'.(int)in_array(5, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',5);" style="left: 40px; top: 94px;">Certidão de Tributos Federais / Divida Ativa da União (Internet)</a>
		<a class="cb'.(int)in_array(6, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',6);" style="left: 40px; top: 114px;">Certidão de Tributos Municipais</a>
		<a class="cb'.(int)in_array(7, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',7);" style="left: 40px; top: 134px;">CND INSS (Internet)</a>
		<a class="cb'.(int)in_array(8, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',8);" style="left: 490px; top: 14px;">CNDT - Certidão Negativa de Débitos Trabalhistas (Internet)</a>
		<a class="cb'.(int)in_array(9, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',9);" style="left: 490px; top: 34px;">CNPJ (Internet)</a>
		<a class="cb'.(int)in_array(10, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',10);" style="left: 490px; top: 54px;">Falência e Concordata</a>
		<a class="cb'.(int)in_array(11, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',11);" style="left: 490px; top: 74px;">FGTS (Internet)</a>
		<a class="cb'.(int)in_array(12, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',12);" style="left: 490px; top: 94px;">Ficha de Inscrição Estadual)</a>
		<a class="cb'.(int)in_array(13, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',13);" style="left: 490px; top: 114px;">Ficha de Inscrição Municipal</a>
		<a class="cb'.(int)in_array(14, $dDocumentacao_selecionados).'"'.$check_readonly.' href="javascript:void(0);" onclick="ckSelfish(this,\'docs\',14);" style="left: 490px; top: 134px;">Procuração</a>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 112px;">
		<span class="apl-lb w-936 italic" style="margin-left: 40px;">Relacione outros documentos que não constam entre os citados acima:</span>
		<textarea id="i-doc-outros" class="apl-textarea" name="f-doc-outros" style="position: absolute; left: 40px; top: 30px; width: 859px; height: 70px;"'.$input_readonly.'>'.$dDocumentacao_outros.'</textarea>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-100 bg-3 center">Model Code</span>
		<input id="i-model-code" class="apl-input w-100 bg-2 white" type="text" name="f-model-code" maxlength="6" value="'.$dModel_code.'"'.$input_readonly.'>
		<span class="apl-lb w-100 bg-3 center">Cor</span>
		<input id="i-cor" class="apl-input w-100 bg-2 white" type="text" maxlength="4" name="f-cor" value="'.$dCor.'"'.$input_readonly.'>
		<span class="apl-lb w-128 bg-3 center">Opcionais (PR\'s)</span>
		<input id="i-pr" class="apl-input w-410 bg-2 white" type="text" name="f-pr" value="'.$dOpcionais_pr.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-100 bg-3 center">Motorização</span>
		<input id="i-motorizacao" class="apl-input w-100 bg-2 white" type="text" name="f-motorizacao" maxlength="3" value="'.$dMotorizacao.'"'.$input_readonly.'>
		<span class="apl-lb w-100 bg-3 center">Potência</span>
		<input id="i-potencia" class="apl-input w-100 bg-2 white" type="text" name="f-potencia" maxlength="3" value="'.$dPotencia.'"'.$input_readonly.'>
		<span class="apl-lb w-128 bg-3 center">Combustível</span>
		<input id="i-combustivel" class="apl-input w-148 bg-2 white" type="text" name="f-combustivel" maxlength="10" value="'.$dCombustivel.'"'.$input_readonly.'>
		<span class="apl-lb w-130 bg-3 center">Transformação</span>
		<div class="apl-lb w-132 bg-2">
			<a class="rbw'.(int)in_array($dTransformacao, array(1)).' cl-trans"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'trans\',1);" style="left: 14px; top: 7px;">Sim</a>
			<a class="rbw'.(int)in_array($dTransformacao, array(2)).' cl-trans"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'trans\',2);" style="left: 70px; top: 7px;">Não</a>
		</div>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Detalhar Transformação</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 130px;">
		<textarea id="i-detalhamento-transformacao" class="apl-textarea" name="f-detalhamento-transformacao" style="position: absolute; left: 40px; top: 10px; width: 859px; height: 109px;"'.$input_readonly.'>'.$dDetalhamento_transformacao.'</textarea>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-300 bg-3 center">Garantia do(s) Veículo(s):</span>
		<div class="apl-lb w-638 bg-3">
			<a class="rb'.(int)in_array($dGarantia, array(1)).' cl-gar"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'gar\',1);" style="left: 40px; top: 7px;">Garantia Padrão</a>
			<a class="rb'.(int)in_array($dGarantia, array(2)).' cl-gar"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'gar\',2);" style="left: 300px; top: 7px;">Garantia Diferenciada</a>
		</div>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Acessórios</span>
	</div>
	<div id="acessorios">
		'.$tAcessorios.'
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Informações da Proposta</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-130 bg-3 center">Preço Público</span>
		<input id="i-preco-publico" class="apl-input w-160 bg-2 white" type="text" name="f-preco-publico" value="'.$dPreco_publico.'"'.$input_readonly.'>
		<span class="apl-lb w-100 bg-3 center">Desconto</span>
		<input id="i-desconto" class="apl-input w-130 bg-2 white" type="text" name="f-desconto" maxlength="20" value="'.$dDesconto.'"'.$input_readonly.'>
		<span class="apl-lb w-128 bg-3 center">Repasse (%)</span>
		<input id="i-repasse" class="apl-input w-60 bg-2 white" type="text" name="f-repasse" maxlength="3" value="'.$dRepasse.'"'.$input_readonly.'>
		<span class="apl-lb w-130 bg-3 center">DN de Venda</span>
		<input id="i-dnvenda" class="apl-input w-100 bg-2 white" type="text" name="f-dnvenda" maxlength="4" value="'.$dDn_venda.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-130 bg-3 center">Preço Máximo</span>
		<input id="i-preco-maximo" class="apl-input w-160 bg-2 white" type="text" name="f-preco-maximo" value="'.$dPreco_maximo.'"'.$input_readonly.'>
		<span class="apl-lb w-160 bg-3 center">Validade da Proposta</span>
		<input id="i-validade-proposta" class="apl-input w-258 bg-2 white" type="text" name="f-validade-proposta" maxlength="3" value="'.$dValidade_da_proposta.'"'.$input_readonly.'>
		<span class="apl-lb w-130 bg-3 center">DN de Entrega</span>
		<input id="i-dnentrega" class="apl-input w-100 bg-2 white" type="text" maxlength="4" name="f-dnentrega" value="'.$dDn_entrega.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-130 bg-3 center">Prazo de Entrega</span>
		<input id="i-prazo-entrega" class="apl-input w-160 bg-2 white" type="text" name="f-prazo-entrega" maxlength="3" value="'.$dPrazo_de_entrega.'"'.$input_readonly.'>
		<span class="apl-lb w-160 bg-3 center">Prazo de Pagamento</span>
		<input id="i-prazo-pagamento" class="apl-input w-258 bg-2 white" type="text" name="f-prazo-pagamento" maxlength="50" value="'.$dPrazo_de_pagamento.'"'.$input_readonly.'>
		<span class="apl-lb w-130 bg-3 center">Quantidade</span>
		<input id="i-quantidade" class="apl-input w-100 bg-2 white" type="text" name="f-quantidade" maxlength="4" value="'.$dQuantidade.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-130 bg-3 center">AVE</span>
		<input id="i-ave" class="apl-input w-160 bg-2 white" type="text" name="f-ave" maxlength="20" value="'.$dAve.'"'.$input_readonly.'>
		<span class="apl-lb w-100 bg-3 center">Nº do Pool</span>
		<input id="i-numero-pool" class="apl-input w-548 bg-2 white" type="text" name="f-numero-pool" value="'.$dNumero_pool.'"'.$input_readonly.'>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Origem da Verba</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 41px;">
		<a class="rb'.(int)in_array($dOrigem_da_verba, array(1)).' cl-verba"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'verba\',1);" style="left: 40px; top: 12px;">Federal</a>
		<a class="rb'.(int)in_array($dOrigem_da_verba, array(2)).' cl-verba"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'verba\',2);" style="left: 250px; top: 12px;">Estadual</a>
		<a class="rb'.(int)in_array($dOrigem_da_verba, array(3)).' cl-verba"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'verba\',3);" style="left: 460px; top: 12px;">Municipal</a>
		<a class="rb'.(int)in_array($dOrigem_da_verba, array(4)).' cl-verba"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'verba\',4);" style="left: 670px; top: 12px;">Convenio</a>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Isenções de Impostos do Órgão</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 106px;">
		<a class="rb'.(int)in_array($dIsencao_de_impostos, array(1)).' cl-imp"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'imp\',1);" style="left: 40px; top: 14px;">Não possui isenção</a>
		<a class="rb'.(int)in_array($dIsencao_de_impostos, array(2)).' cl-imp"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'imp\',2);" style="left: 250px; top: 14px;">IPI</a>
		<a class="rb'.(int)in_array($dIsencao_de_impostos, array(3)).' cl-imp"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'imp\',3);" style="left: 460px; top: 14px;">ICMS Substituto</a>
		<a class="rb'.(int)in_array($dIsencao_de_impostos, array(4)).' cl-imp"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'imp\',4);" style="left: 40px; bottom: 43px;">IPI + ICMS</a>
		<a class="rb'.(int)in_array($dIsencao_de_impostos, array(5)).' cl-imp"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'imp\',5);" style="left: 250px; bottom: 43px;">ICMS</a>
		<a class="rb'.(int)in_array($dIsencao_de_impostos, array(6)).' cl-imp"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'imp\',6);" style="left: 460px; bottom: 43px;">IPI + ICMS Substituto</a>
		<span class="apl-lb italic" style="position: absolute; bottom: 6px; left: 40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via e-mail Lei/Decreto para que seja confirmado pelo Tributário.</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl">
		<span class="apl-lb w-938 bg-1 white center">Observações Gerais</span>
	</div>
	<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 130px;">
		<textarea id="i-observacoes" class="apl-textarea" name="f-observacoes" style="position: absolute; left: 40px; top: 10px; width: 859px; height: 109px;"'.$input_readonly.'>'.$dObservacoes_gerais.'</textarea>
	</div>
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
	(SELECT descricao FROM gelic_motivos WHERE id = his.id_valor_1) AS motivo, 
	(SELECT descricao FROM gelic_motivos WHERE id = his.id_valor_2) AS submotivo
FROM 
	gelic_comprasrp_apl_historico AS his
    INNER JOIN gelic_clientes AS cli ON cli.id = his.id_cliente
    INNER JOIN gelic_comprasrp_apl AS apl ON apl.id = $dId_apl
    LEFT JOIN gelic_comprasrp_apl_aprovadas AS apr ON apr.id_apl_historico = his.id AND apr.ativo = 1
WHERE 
	his.id_apl = $dId_apl AND
	his.tipo < 5
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
					$anexo = '<tr><td class="apr-tbl-lb">ANEXO:</td><td><a class="ablue" href="'.linkFileBucket("vw/cdapr/".$db->f("arquivo")).'" target="_blank">'.utf8_encode($db->f("nome_arquivo")).'</a></td></tr>';
				else
					$anexo = '';

				$apr_tbl = '<br><br><table class="apr-tbl">
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



	// Verificar se eh preciso mostrar o botao de historico de aprovacoes
	$historico_btn = '';
	if ($dados)
	{
		$db->query("SELECT id FROM gelic_comprasrp_apl_historico WHERE id_apl = $dId_apl AND tipo IN (2,4,5,6,7,8)");
		if ($db->nextRecord())
			$historico_btn = '<a class="bt-style-2 fr" href="javascript:void(0);" onclick="cdAPL_historico('.$pId_cd.');">Histórico de Aprovações/Reprovações</a>';
	}


	if (!$readonly)
		$tApl .= '<div class="apl-row" style="padding: 20px 40px; border: 1px solid #cecece; border-top: none;">
			<a class="bt-style-1 fl" href="javascript:void(0);" onclick="cdAPL_salvar('.$pId_cd.', false);">Enviar APL</a>
		</div></div>';

	if ($tipo == 2)
	{
		$reverter_btn = '';
		if ($sInside_tipo == 1)
			$reverter_btn = '<br><br><a class="bt-style-2 fl" href="javascript:void(0);" onclick="cdAPL_reverterAprovacao('.$dId_apl.', false);">Reverter Aprovação da APL</a>';

		$tApl .= '<div class="apl-row t13" style="padding: 20px 40px; border: 1px solid #cecece; border-top: none; text-align: left; line-height: normal;"><span class="bold t-green t20" style="font-size:20px;">Aprovada!</span>'.$info.$reverter_btn.$historico_btn.'</div></div>';
	}
	else if ($tipo == 4)
	{
		$reverter_btn = '';
		if ($sInside_tipo == 1)
			$reverter_btn = '<br><br><a class="bt-style-2 fl" href="javascript:void(0);" onclick="cdAPL_reverterReprovacao('.$dId_apl.', false);">Reverter Reprovação da APL</a>';

		$tApl .= '<div class="apl-row t13" style="padding: 20px 40px; border: 1px solid #cecece; border-top: none; text-align: left; line-height: normal;"><span class="bold t-red t20" style="font-size:20px;">Reprovada.</span>'.$info.$reverter_btn.$historico_btn.'</div></div>';
	}

	if ($tipo == 1 && $sInside_tipo == 1)
	{
		$tApl .= '<div class="apl-row" style="padding: 20px 40px; border: 1px solid #cecece; border-top: none;">
			<a class="bt-style-1 fl" href="javascript:void(0);" onclick="cdAPL_aprovar('.$pId_cd.',false);">Aprovar APL</a>
			<a class="bt-style-2 fl" href="javascript:void(0);" onclick="cdAPL_reprovar('.$pId_cd.',false);" style="margin-left: 10px;">Reprovar APL</a>
			'.$historico_btn.'
		</div>';
	}


	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $tApl;
}
echo json_encode($aReturn);

?>
