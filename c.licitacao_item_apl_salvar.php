<?php

require_once "include/config.php";
require_once "include/essential.php";

/*********************************************************************************/
function salvarAPLemPDF($id_apl)
{
	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();

	$tApl = '';
	$tAcessorios = '';

	$db->query("
SELECT 
	apl.*,
	itm.item,
	IF (cli.id_parent > 0, clip.nome, cli.nome) AS dn_nome
FROM
	gelic_licitacoes_apl AS apl
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	apl.id = $id_apl");
	$db->nextRecord();

	$dDn_nome = utf8_encode($db->f("dn_nome"));
	$dItem = utf8_encode($db->f("item"));
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
	$dLimite_km_km = intval($db->f("limite_km_km"));
	$dPreco_publico_vw = ($db->f("preco_publico_vw") == '0.00') ? "" : "R$ ".number_format($db->f("preco_publico_vw"), 2, ",", ".");
	$dPreco_ref_edital = ($db->f("preco_ref_edital") == '0.00') ? "" : "R$ ".number_format($db->f("preco_ref_edital"), 2, ",", ".");
	$dQuantidade_veiculos = ($db->f("quantidade_veiculos") == 0) ? "" : $db->f("quantidade_veiculos");
	$dDn_venda = utf8_encode($db->f("dn_venda"));
	$dDn_venda_estado = $db->f("dn_venda_estado");
	$dRepasse_concessionario = ($db->f("repasse_concessionario") == 0) ? "" : $db->f("repasse_concessionario");
	$dDn_entrega = utf8_encode($db->f("dn_entrega"));
	$dDn_entrega_estado = $db->f("dn_entrega_estado");
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

	$amarok_upload_anexo = '';

	if ($dVersao == 2)
	{
		if ($dTransformacao_amarok_arquivo != '' && file_exists(UPLOAD_DIR."apl/".$dTransformacao_amarok_arquivo))
		{
			$pShort_file_name = $db->f("transformacao_amarok_nome_arquivo");
			if (strlen($pShort_file_name) > 84)
				$pShort_file_name = substr($pShort_file_name, 0, 73)."...".substr($pShort_file_name, -8);

			$file_size = formatSizeUnits(filesize(UPLOAD_DIR."apl/".$dTransformacao_amarok_arquivo));
			$amarok_upload_anexo = 'Anexo: <span>'.utf8_encode($pShort_file_name).'</span> ('.$file_size.')';
		}
	}

	$db->query("SELECT acessorio, valor FROM gelic_licitacoes_apl_acessorios WHERE id_apl = $id_apl ORDER BY id");
	while ($db->nextRecord())
	{
		$dAcessorio = utf8_encode($db->f("acessorio"));
		$dValor_ace = ($db->f("valor") == '0.00') ? "" : "R$ ".number_format($db->f("valor"), 2, ",", ".");

		$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
			<span class="apl-lb w-576 bg-2 white lpad">'.$dAcessorio.'</span>
			<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
			<span class="apl-lb w-160 bg-2 white rpad right">'.$dValor_ace.'</span>
		</div>';
	}


	if ($dVersao == 2)
	{
		if ($dAcessorios == 2)
			$tAcessorios = '';
		else
			$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>';

		$aPrazo = array();
		$aPrazo[] = '';
		$aPrazo[] = '12 meses';
		$aPrazo[] = '24 meses';
		$aPrazo[] = '36 meses';

		if ($dGarantia_prazo < 4)
			$dGarantia_prazo_str = $aPrazo[$dGarantia_prazo];
		else
			$dGarantia_prazo_str = $dGarantia_prazo_outro;

		$kmkm = '<span class="apl-lb bg-2" style="width:226px;">&nbsp;</span>';
		if ($dLimite_km == 1)
		$kmkm = '<span class="apl-lb bg-2 center" style="width:40px;color:#f1f1f1;border-left:1px solid #cccccc;">KM:</span>
			<span class="apl-lb bg-2 white lpad" style="width:179px;">'.$dLimite_km_km.'</span>';
	}

	if ($dVersao == 1)
		$tApl = '
			<div class="apl-row" style="margin-bottom:4px;text-align:center;">
				<span><a style="font-size: 24px;">APL - AUTORIZAÇÃO DE PARTICIPAÇÃO EM LICITAÇÕES</a><br><a class="bold">DN:</a> '.$dDn_nome.'&nbsp;&nbsp;&nbsp;<a class="bold">Licitação:</a> '.$dId_licitacao.'&nbsp;&nbsp;&nbsp;<a class="bold">Item:</a> '.$dItem.'</span>
			</div>
			<div class="apl-row apl-bt apl-br apl-bb apl-bl">
				<span class="apl-lb w-238 bg-1 white center">Nome do Órgão</span>
				<span class="apl-lb w-388 bg-2 white lpad">'.$dNome_orgao.'</span>
				<span class="apl-lb w-180 bg-1 white center">Data da Licitação</span>
				<span class="apl-lb w-130 bg-2 white lpad">'.$dData_licitacao.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-238 bg-1 white center">ID Licitação</span>
				<span class="apl-lb w-388 bg-2 white lpad">'.$dId_licitacao.'</span>
				<span class="apl-lb w-180 bg-imp center">Registro de Preços</span>
				<div class="apl-lb w-136 bg-2">
					<a class="rbw'.(int)in_array($dRegistro_precos,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dRegistro_precos,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Modalidade da Venda</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:76px;">
				<a class="rb'.(int)in_array($dModalidade_venda, array(1)).'" style="left:40px;top:14px;">Compra Direta</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(2)).'" style="left:250px;top:14px;">Tomada de Preços</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(3)).'" style="left:460px;top:14px;">Pregão Presencial</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(4)).'" style="left:670px;top:14px;">Adesão a Registro de Preços</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(5)).'" style="left:40px;bottom:13px;">Carta Convite</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(6)).'" style="left:250px;bottom:13px;">Concorrência</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(7)).'" style="left:460px;bottom:13px;">Pregão Eletrônico</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(8)).'" style="left:670px;bottom:13px;">Aditivo Contratual</a>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-238 bg-1 white center">Site Pregão Eletrônico</span>
				<span class="apl-lb w-388 bg-3 lpad">'.$dSite_pregao_eletronico.'</span>
				<span class="apl-lb w-180 bg-1 white center">Nº da Licitação</span>
				<span class="apl-lb w-130 bg-3 lpad">'.$dNumero_licitacao.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Documentação: Documentos Solicitados pelo Órgão (Licitações/Compra Direta)</span>
				<span class="apl-lb w-100p bg-1 white center italic">Dados do Participante pela VW (Concessionário / Pool)</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100 bg-1 white center">Nome</span>
				<span class="apl-lb w-836 bg-3 lpad">'.$dParticipante_nome.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100 bg-1 white center">RG</span>
				<span class="apl-lb w-368 bg-3 lpad">'.$dParticipante_rg.'</span>
				<span class="apl-lb w-100 bg-1 white center">CPF</span>
				<span class="apl-lb w-368 bg-3 lpad">'.$dParticipante_cpf.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Relacione os Documentos</span>
			</div>
			<div class="apl-row apl-br apl-bl bg-3" style="height: 165px;">
				<a class="cb'.(int)in_array(1, $dV1_documentacao_selecionados).'" style="left:40px;top:14px;">Atestado de Capacidade Técnica</a>
				<a class="cb'.(int)in_array(2, $dV1_documentacao_selecionados).'" style="left:40px;top:34px;">Ato Constitutivo</a>
				<a class="cb'.(int)in_array(3, $dV1_documentacao_selecionados).'" style="left:40px;top:54px;">Balanço Patrimonial</a>
				<a class="cb'.(int)in_array(4, $dV1_documentacao_selecionados).'" style="left:40px;top:74px;">Certidão de Tributos Estaduais</a>
				<a class="cb'.(int)in_array(5, $dV1_documentacao_selecionados).'" style="left:40px;top:94px;">Certidão de Tributos Federais / Divida Ativa da União (Internet)</a>
				<a class="cb'.(int)in_array(6, $dV1_documentacao_selecionados).'" style="left:40px;top:114px;">Certidão de Tributos Municipais</a>
				<a class="cb'.(int)in_array(7, $dV1_documentacao_selecionados).'" style="left:40px;top:134px;">CND INSS (Internet)</a>
				<a class="cb'.(int)in_array(8, $dV1_documentacao_selecionados).'" style="left:490px;top:14px;">CNDT - Certidão Negativa de Débitos Trabalhistas (Internet)</a>
				<a class="cb'.(int)in_array(9, $dV1_documentacao_selecionados).'" style="left:490px;top:34px;">CNPJ (Internet)</a>
				<a class="cb'.(int)in_array(10, $dV1_documentacao_selecionados).'" style="left:490px;top:54px;">Falência e Concordata</a>
				<a class="cb'.(int)in_array(11, $dV1_documentacao_selecionados).'" style="left:490px;top:74px;">FGTS (Internet)</a>
				<a class="cb'.(int)in_array(12, $dV1_documentacao_selecionados).'" style="left:490px;top:94px;">Ficha de Inscrição Estadual)</a>
				<a class="cb'.(int)in_array(13, $dV1_documentacao_selecionados).'" style="left:490px;top:114px;">Ficha de Inscrição Municipal</a>
				<a class="cb'.(int)in_array(14, $dV1_documentacao_selecionados).'" style="left:490px;top:134px;">Procuração</a>
			</div>
			<div class="apl-row apl-br apl-bl bg-3">
				<span class="apl-lb w-100p italic" style="margin-left: 40px;">Relacione outros documentos que não constam entre os citados acima:</span>
			</div>
			<div class="apl-row apl-br apl-bl">
				<span class="apl-textarea" style="margin-top:0;">'.nl2br($dV1_documentacao_outros).'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100 bg-3 center">Model Code</span>
				<span class="apl-lb w-100 bg-2 white lpad">'.$dModel_code.'</span>
				<span class="apl-lb w-100 bg-3 center">Cor</span>
				<span class="apl-lb w-100 bg-2 white lpad">'.$dCor.'</span>
				<span class="apl-lb w-128 bg-3 center">Opcionais (PR\'s)</span>
				<span class="apl-lb w-402 bg-2 white lpad">'.$dOpcionais_pr.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100 bg-3 center">Motorização</span>
				<span class="apl-lb w-100 bg-2 white lpad">'.$dMotorizacao.'</span>
				<span class="apl-lb w-100 bg-3 center">Potência</span>
				<span class="apl-lb w-100 bg-2 white lpad">'.$dPotencia.'</span>
				<span class="apl-lb w-128 bg-3 center">Combustível</span>
				<span class="apl-lb w-142 bg-2 white lpad">'.$dCombustivel.'</span>
				<span class="apl-lb w-130 bg-imp center">Transformação</span>
				<div class="apl-lb w-130 bg-2">
					<a class="rbw'.(int)in_array($dTransformacao, array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dTransformacao, array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Detalhar Transformação</span>
			</div>
			<div class="apl-row apl-br apl-bl apl-bb">
				<span class="apl-textarea">'.nl2br($dTransformacao_detalhar).'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-300 bg-imp center">Garantia do(s) Veículo(s):</span>
				<div class="apl-lb w-636 bg-3">
					<a class="rb'.(int)in_array($dGarantia, array(1)).'" style="left:40px;top:7px;">Garantia Padrão</a>
					<a class="rb'.(int)in_array($dGarantia, array(2)).'" style="left:300px;top:7px;">Garantia Diferenciada</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Acessórios</span>
			</div>
			'.$tAcessorios.'
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Informações da Proposta</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-130 bg-3 center">Preço Público</span>
				<span class="apl-lb w-160 bg-2 white lpad">'.$dPreco_publico_vw.'</span>
				<span class="apl-lb w-100 bg-3 center">Desconto</span>
				<span class="apl-lb w-118 bg-2 white lpad">'.$dV1_desconto.'</span>
				<span class="apl-lb w-128 bg-3 center">Repasse (%)</span>
				<span class="apl-lb w-60 bg-2 white lpad">'.$dRepasse_concessionario.'</span>
				<span class="apl-lb w-130 bg-3 center">DN de Venda</span>
				<span class="apl-lb w-98 bg-2 white lpad">'.$dDn_venda.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-130 bg-3 center">Preço Máximo</span>
				<span class="apl-lb w-160 bg-2 white lpad">'.$dV1_preco_maximo.'</span>
				<span class="apl-lb w-160 bg-3 center">Validade da Proposta</span>
				<span class="apl-lb w-252 bg-2 white lpad">'.$dValidade_proposta.'</span>
				<span class="apl-lb w-130 bg-3 center">DN de Entrega</span>
				<span class="apl-lb w-98 bg-2 white lpad">'.$dDn_entrega.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-130 bg-3 center">Prazo de Entrega</span>
				<span class="apl-lb w-160 bg-2 white lpad">'.$dPrazo_entrega.'</span>
				<span class="apl-lb w-160 bg-3 center">Prazo de Pagamento</span>
				<span class="apl-lb w-252 bg-2 white lpad">'.$dV1_prazo_pagamento.'</span>
				<span class="apl-lb w-130 bg-3 center">Quantidade</span>
				<span class="apl-lb w-98 bg-2 white lpad">'.$dQuantidade_veiculos.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-130 bg-3 center">AVE</span>
				<span class="apl-lb w-160 bg-2 white lpad">'.$dAve.'</span>
				<span class="apl-lb w-100 bg-3 center">Nº do Pool</span>
				<span class="apl-lb w-546 bg-2 white lpad">'.$dV1_numero_pool.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Origem da Verba</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 41px;">
				<a class="rb'.(int)in_array($dOrigem_verba, array(1)).'" style="left:40px;top:12px;">Federal</a>
				<a class="rb'.(int)in_array($dOrigem_verba, array(2)).'" style="left:250px;top:12px;">Estadual</a>
				<a class="rb'.(int)in_array($dOrigem_verba, array(3)).'" style="left:460px;top:12px;">Municipal</a>
				<a class="rb'.(int)in_array($dOrigem_verba, array(4)).'" style="left:670px;top:12px;">Convenio</a>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Isenções de Impostos do Órgão</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:106px;">
				<a class="rb'.(int)in_array($dIsencao_impostos, array(1)).'" style="left:40px;top:14px;">Não possui isenção</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(2)).'" style="left:250px;top:14px;">IPI</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(3)).'" style="left:460px;top:14px;">ICMS Substituto</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(4)).'" style="left:40px;bottom:43px;">IPI + ICMS</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(5)).'" style="left:250px;bottom:43px;">ICMS</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(6)).'" style="left:460px;bottom:43px;">IPI + ICMS Substituto</a>
				<span class="apl-lb italic" style="position: absolute; bottom: 6px; left: 40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via e-mail Lei/Decreto para que seja confirmado pelo Tributário.</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Observações Gerais</span>
			</div>
			<div class="apl-row apl-br apl-bl apl-bb">
				<span class="apl-textarea">'.nl2br($dObservacoes).'</span>
			</div>';
	else if ($dVersao == 2)
		$tApl = '
			<div class="apl-row" style="margin-bottom:4px;text-align:center;">
				<span><a style="font-size: 24px;">APL - AUTORIZAÇÃO DE PARTICIPAÇÃO EM LICITAÇÕES</a><br><a class="bold">DN:</a> '.$dDn_nome.'&nbsp;&nbsp;&nbsp;<a class="bold">Licitação:</a> '.$dId_licitacao.'&nbsp;&nbsp;&nbsp;<a class="bold">Item:</a> '.$dItem.'</span>
			</div>
			<div class="apl-row apl-bt apl-br apl-bb apl-bl">
				<span class="apl-lb bg-1 white center" style="width:240px;">Nome do Órgão</span>
				<span class="apl-lb bg-2 white lpad" style="width:388px;">'.$dNome_orgao.'</span>
				<span class="apl-lb bg-1 white center" style="width:182px;">Data da Licitação</span>
				<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dData_licitacao.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-1 white center" style="width:240px;">Licitação Nro.</span>
				<span class="apl-lb bg-2 white lpad" style="width:388px;">'.$dId_licitacao.'</span>
				<span class="apl-lb bg-imp center" style="width:182px;">Registro de Preços</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dRegistro_precos,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dRegistro_precos,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-1 white center" style="width:240px;">CNPJ de Faturamento</span>
				<span class="apl-lb bg-2 white lpad" style="width:388px;">'.$dCnpj_faturamento.'</span>
				<span class="apl-lb bg-1 white center" style="width:182px;">Estado</span>
				<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dEstado.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Modalidade da Venda</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height:76px;">
				<a class="rb'.(int)in_array($dModalidade_venda, array(1)).'" style="left:40px;top:14px;">Compra Direta</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(2)).'" style="left:250px;top:14px;">Tomada de Preços</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(3)).'" style="left:460px;top:14px;">Pregão Presencial</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(4)).'" style="left:670px;top:14px;">Adesão a Registro de Preços</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(5)).'" style="left:40px;bottom:13px;">Carta Convite</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(6)).'" style="left:250px;bottom:13px;">Concorrência</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(7)).'" style="left:460px;bottom:13px;">Pregão Eletrônico</a>
				<a class="rb'.(int)in_array($dModalidade_venda, array(8)).'" style="left:670px;bottom:13px;">Aditivo Contratual</a>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-1 white center" style="width:240px;">Site Pregão Eletrônico</span>
				<span class="apl-lb bg-3 lpad" style="width:388px;">'.$dSite_pregao_eletronico.'</span>
				<span class="apl-lb bg-1 white center" style="width:182px;">Nº da Licitação</span>
				<span class="apl-lb bg-3 lpad" style="width:126px;">'.$dNumero_licitacao.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center italic">Dados do Participante</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-1 white center" style="width:274px;">Nome</span>
				<span class="apl-lb bg-3 lpad" style="width:434px;">'.$dParticipante_nome.'</span>
				<span class="apl-lb bg-1 white center" style="width:102px;">CPF</span>
				<span class="apl-lb bg-3 lpad" style="width:126px;">'.$dParticipante_cpf.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-1 white center" style="width:274px;">RG</span>
				<span class="apl-lb bg-3 lpad" style="width:434px;">'.$dParticipante_rg.'</span>
				<span class="apl-lb bg-1 white center" style="width:102px;">Telefone</span>
				<span class="apl-lb bg-3 lpad" style="width:126px;">'.$dParticipante_telefone.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-1 white center" style="width:274px;">Endereço p/ Envio da Documentação</span>
				<span class="apl-lb bg-3 lpad" style="width:668px;">'.$dParticipante_endereco.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl" style="height:6px;"></div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:132px;">Model Code</span>
				<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dModel_code.'</span>
				<span class="apl-lb bg-3 center" style="width:102px;">Cor</span>
				<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dCor.'</span>
				<span class="apl-lb bg-3 center" style="width:130px;">Ano/Modelo</span>
				<span class="apl-lb bg-2 white lpad" style="width:314px;">'.$dAno_modelo.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:132px;">Motorização</span>
				<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dMotorizacao.'</span>
				<span class="apl-lb bg-3 center" style="width:102px;">Potência</span>
				<span class="apl-lb bg-2 white lpad" style="width:126px;">'.$dPotencia.'</span>
				<span class="apl-lb bg-3 center" style="width:130px;">Combustível</span>
				<span class="apl-lb bg-2 white lpad" style="width:314px;">'.$dCombustivel.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:132px;">Opcionais (PR\'s)</span>
				<span class="apl-lb bg-2 white lpad" style="width:460px;">'.$dOpcionais_pr.'</span>
				<span class="apl-lb bg-3 center" style="width:218px;">Eficiência Energética CONPET</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dEficiencia_energetica,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dEficiencia_energetica,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Transformação (Custo VW)</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-imp center" style="width:132px;">Transformação</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dTransformacao,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dTransformacao,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
				<span class="apl-lb bg-3 center" style="width:102px;">Tipo</span>
				<span class="apl-lb bg-2 white lpad" style="width:312px;">'.$dTransformacao_tipo.'</span>
				<span class="apl-lb bg-3 center" style="width:132px;">Protótipo</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dTransformacao_prototipo,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dTransformacao_prototipo,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bl bg-3">
				<span class="apl-lb w-100p italic" style="margin-left:40px;">Detalhar transformação</span>
			</div>
			<div class="apl-row apl-br apl-bl bg-3">
				<span class="apl-textarea" style="margin-top:0;">'.nl2br($dTransformacao_detalhar).'</span>
			</div>
			<div class="apl-row apl-br apl-bl bg-3" style="padding-top:10px;padding-bottom:20px;">
				<span class="apl-cl-label">Check List Transformação AMAROK</span>
				<div class="apl-upl-ready">
					<span>'.str_replace(' ','&nbsp;',$amarok_upload_anexo).'</span>
				</div>
			</div>
			<div class="apl-row apl-bt apl-br apl-bb apl-bl bg-1 white center">
				<div style="display:inline-block;overflow:hidden;">
					<span class="apl-lb">Acessórios (Custo pago pelo DN)</span>
					<a class="rbw'.(int)in_array($dAcessorios,array(1)).'" style="position:relative;float:left;margin:8px 0 0 40px;">Sim</a>
					<a class="rbw'.(int)in_array($dAcessorios,array(2)).'" style="position:relative;float:left;margin:8px 0 0 14px;">Não</a>
				</div>
			</div>
			'.$tAcessorios.'
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:184px;">Emplacamento</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dEmplacamento,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dEmplacamento,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
				<span class="apl-lb bg-3 center" style="width:184px;">Licenciamento</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dLicenciamento,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dLicenciamento,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
				<span class="apl-lb bg-3 center" style="width:184px;">IPVA</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dIpva,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dIpva,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Garantia e Revisões</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-imp center" style="width:302px;">Garantia</span>
				<div class="apl-lb bg-2" style="width:646px;">
					<a class="rbw'.(int)in_array($dGarantia,array(1)).'" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Padrão</a>
					<a class="rbw'.(int)in_array($dGarantia,array(2)).'" style="position:relative;float:left;margin-left:40px;margin-top:7px;">Garantia Diferenciada</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:302px;">Prazo Garantia <a class="italic">(12/24/36 Meses)</a></span>
				<span class="apl-lb bg-2 white lpad" style="width:324px;">'.$dGarantia_prazo_str.'</span>
				<span class="apl-lb bg-3 center" style="width:184px;">Revisão Embarcada</span>
				<div class="apl-lb bg-2" style="width:132px;">
					<a class="rbw'.(int)in_array($dRevisao_embarcada,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dRevisao_embarcada,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:302px;">Quantidade de Revisões Inclusas</span>
				<span class="apl-lb bg-2 white lpad" style="width:78px;">'.$dQuantidade_revisoes_inclusas.'</span>
				<span class="apl-lb bg-3 center" style="width:184px;">Limite de KM</span>
				<div class="apl-lb bg-2" style="width:152px;">
					<a class="rbw'.(int)in_array($dLimite_km,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dLimite_km,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
				'.$kmkm.'
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-3 center" style="color:#666666;"><a style="font-weight:bold;color:#ff0000;">Atenção:</a> AMAROK LIMITE 100 MIL KM&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GARANTIA PADRÃO 3 ANOS</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Informações da Proposta</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:152px;">Preço Público VW</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dPreco_publico_vw.'</span>
				<span class="apl-lb bg-3 center" style="width:152px;">Preço Ref. Edital</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dPreco_ref_edital.'</span>
				<span class="apl-lb bg-3 center" style="width:194px;">Quantidade de Veículos</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dQuantidade_veiculos.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:152px;">DN de Venda</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_venda.'</span>
				<span class="apl-lb bg-3 center" style="width:152px;">Estado DN de Venda</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_venda_estado.'</span>
				<span class="apl-lb bg-3 center" style="width:194px;">Repasse Concessionário (%)</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dRepasse_concessionario.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:152px;">DN de Entrega</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_entrega.'</span>
				<span class="apl-lb bg-3 center" style="width:152px;">Estado DN de Venda</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dDn_entrega_estado.'</span>
				<span class="apl-lb bg-3 center" style="width:194px;">Prazo Entrega</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dPrazo_entrega.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:152px;">Validade da Proposta</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dValidade_proposta.'</span>
				<span class="apl-lb bg-3 center" style="width:152px;">Vigência do Contrato</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dVigencia_contrato.'</span>
				<span class="apl-lb bg-3 center" style="width:194px;">Prazo Pagamento</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dPrazo_pagamento.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:244px;">AVE (enviar eletronic. no Sivolks)</span>
				<span class="apl-lb bg-2 white lpad" style="width:264px;">'.$dAve.'</span>
				<span class="apl-lb bg-3 center" style="width:284px;">Multas e Sanções – Indicar ítem do Edital</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dMultas_sansoes.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb bg-3 center" style="width:244px;">Garantia de Contrato</span>
				<div class="apl-lb bg-2" style="width:270px;">
					<a class="rbw'.(int)in_array($dGarantia_contrato,array(1)).'" style="left:14px;top:7px;">Sim</a>
					<a class="rbw'.(int)in_array($dGarantia_contrato,array(2)).'" style="left:70px;top:7px;">Não</a>
				</div>
				<span class="apl-lb bg-3 center" style="width:72px;">Prazo</span>
				<span class="apl-lb bg-2 white lpad" style="width:134px;">'.$dPrazo.'</span>
				<span class="apl-lb bg-3 center" style="width:72px;">Valor</span>
				<span class="apl-lb bg-2 white lpad" style="width:144px;">'.$dValor.'</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Origem da Verba</span>
			</div>
			<div class="apl-row apl-br apl-bl bg-3" style="height:41px;">
				<a class="rb'.(int)in_array($dOrigem_verba, array(1)).'" style="left:40px;top:12px;">Federal</a>
				<a class="rb'.(int)in_array($dOrigem_verba, array(2)).'" style="left:250px;top:12px;">Estadual</a>
				<a class="rb'.(int)in_array($dOrigem_verba, array(3)).'" style="left:460px;top:12px;">Municipal</a>
				<a class="rb'.(int)in_array($dOrigem_verba, array(4)).'" style="left:670px;top:12px;">Convenio</a>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl bg-2" style="height:41px;">
				<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(1)).'" style="left:40px;top:12px;">A Vista</a>
				<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(2)).'" style="left:250px;top:12px;">A Prazo</a>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Isenções de Impostos do Órgão</span>
			</div>
			<div class="apl-row apl-br apl-bl bg-3" style="height:80px;">
				<a class="rb'.(int)in_array($dIsencao_impostos, array(1)).'" style="left:40px;top:14px;">Não possui isenção</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(2)).'" style="left:250px;top:14px;">IPI</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(3)).'" style="left:460px;top:14px;">ICMS Substituto</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(4)).'" style="left:40px;top:46px;">IPI + ICMS</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(5)).'" style="left:250px;top:46px;">ICMS</a>
				<a class="rb'.(int)in_array($dIsencao_impostos, array(6)).'" style="left:460px;top:46px;">IPI + ICMS Substituto</a>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl bg-3">
				<span class="apl-lb w-100p italic" style="margin-left:40px;">Indicar item do edital ou Lei que confirme a marcação acima</span>
				<span class="apl-textarea" style="margin-top:0;">'.nl2br($dImposto_indicar).'</span>
				<span class="apl-lb w-100p italic" style="margin-left:40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via chat GELIC Lei/Decreto para que seja confirmado pelo Tributário.</span>
			</div>
			<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100p bg-1 white center">Observações Gerais</span>
			</div>
			<div class="apl-row apl-br apl-bl apl-bb">
				<span class="apl-textarea">'.nl2br($dObservacoes).'</span>
			</div>';


	$tHtml = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>APL</title>
	<style>
		body { font-family: \'Trebuchet MS\',\'Tahoma\'; font-size: 14px; }

		.apl-row {
			position: relative;
			width: 100%;
			overflow: hidden;
			box-sizing: border-box;
			}

		.apl-bt { border-top: 1px solid #666666; }
		.apl-br { border-right: 1px solid #666666; }
		.apl-bb { border-bottom: 1px solid #666666; }
		.apl-bl { border-left: 1px solid #666666; }

		.apl-lb {
			position: relative;
			display: inline-block;
			float: left;
			line-height: 30px;
			height: 30px;
			}

		.apl-cl-label {
			clear: both;
			float: left;
			margin-left: 40px;
			background-color: #386eb1;
			color: #ffffff;
			line-height: 26px;
			padding: 0 10px;
			}

		.apl-upl-ready {
			position: relative;
			clear: both;
			margin-left: 40px;
			width: 868px;
			height: 38px;
			border: 1px solid #dddddd;
			box-sizing: border-box;
			}

		.apl-upl-ready > span {
			float: left;
			line-height: 36px;
			color: #888888;
			font-style: italic;
			margin-left: 10px;
			}

		.apl-upl-ready > span > span {
			color: #ff0000;
			}

		.w-60 { width: 60px; }
		.w-98 { width: 98px; }
		.w-100 { width: 100px; }
		.w-118 { width: 118px; }
		.w-128 { width: 128px; }
		.w-130 { width: 130px; }
		.w-136 { width: 136px; }
		.w-142 { width: 142px; }
		.w-160 { width: 160px; }
		.w-180 { width: 180px; }
		.w-238 { width: 238px; }
		.w-252 { width: 252px; }
		.w-300 { width: 300px; }
		.w-368 { width: 368px; }
		.w-388 { width: 388px; }
		.w-402 { width: 402px; }
		.w-546 { width: 546px; }
		.w-576 { width: 576px; }
		.w-636 { width: 636px; }
		.w-836 { width: 836px; }
		.w-100p { width: 100%; }

		.bg-1 { background-color: #386eb1; }
		.bg-2 { background-color: #aaaaaa; }
		.bg-3 { background-color: #ffffff; }
		.bg-imp { background-color: #ee0000; color: #ffffff; font-weight: bold; }

		.white { color: #ffffff; }
		.center { text-align: center; }
		.right { text-align: right; }
		.lpad { padding-left: 6px; }
		.rpad { padding-right: 6px; }
		.italic { font-style: italic; }
		.bold { font-weight: bold; }

		.cb0 {
			position: absolute;
			height: 17px;
			background-image: url(\'../../img/cb0.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.cb1 {
			position: absolute;
			height: 17px;
			background-image: url(\'../../img/cb1.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rbw0 {
			position: absolute;
			height: 17px;
			background-image: url(\'../../img/rbw0.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #f1f1f1;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rbw1 {
			position: absolute;
			height: 17px;
			background-image: url(\'../../img/rbw1.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #f1f1f1;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rb0 {
			position: absolute;
			height: 17px;
			background-image: url(\'../../img/rb0.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.rb1 {
			position: absolute;
			height: 17px;
			background-image: url(\'../../img/rb1.png\');
			background-position: left top;
			background-repeat: no-repeat;
			color: #000000;
			line-height: 17px;
			text-align: left;
			text-decoration: none;
			padding-left: 22px;
			font-size: 13px;
			}

		.apl-textarea {
			float: left;
			clear: both;
			margin: 10px 0 10px 40px;
			box-sizing: border-box;
			width: 868px;
			border: 1px dotted #cccccc;
			padding: 6px;
			}

	</style>
</head>
<body>
<div style="width:950px;overflow:hidden;">
'.$tApl.'
</div>
</body>
</html>';

	$arquivo_md5 = md5($id_apl.time().$sInside_id);
	$oFile = fopen(UPLOAD_DIR."anexo/".$arquivo_md5.".html", "w");
	fwrite($oFile, $tHtml);
	fclose($oFile);

	exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."anexo/".$arquivo_md5.".html ".UPLOAD_DIR."anexo/".$arquivo_md5.".pdf");
	@unlink(UPLOAD_DIR."anexo/".$arquivo_md5.".html");

	return $arquivo_md5;
}
/*********************************************************************************/


$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	//--- Ajustar inside_id se for Representante ---
	if ($sInside_tipo == 4) //REP
	{
		$sInside_id = $_SESSION[SESSION_ID_DN];
		$sInside_parent = $sInside_id;
	}
	//----------------------------------------------


	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];


	$xAccess = explode(" ",getAccess());

	if ($sInside_tipo == 1 || ($sInside_tipo == 2 && !in_array($cliente_parent, array(599,600,601,602,603))) || !in_array("lic_apl_enviar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pId_item = intval($_POST["f-id-item"]);

	$pVersao = intval($_POST["f-versao-item"]);

	$pNome_orgao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-nome-orgao-item"])))));
	$pData_licitacao = trim($_POST["f-data-licitacao-item"]); // 'dd/mm/yyyy'
	$pRegistro_precos = intval($_POST["f-registro-precos-item"]);
	$pModalidade_venda = intval($_POST["f-modalidade-venda-item"]);
	$pSite_pregao_eletronico = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-site-pregao-eletronico-item"])))));
	$pNumero_licitacao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-numero-licitacao-item"])))));
	$pParticipante_nome = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-participante-nome-item"])))));
	$pParticipante_rg = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-participante-rg-item"])))));
	$pParticipante_cpf = trim($_POST["f-participante-cpf-item"]);
	$pModel_code = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-model-code-item"])))));
	$pCor = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-cor-item"])))));
	$pMotorizacao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-motorizacao-item"])))));
	$pPotencia = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-potencia-item"])))));
	$pCombustivel = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-combustivel-item"])))));
	$pOpcionais_pr = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-opcionais-pr-item"])))));
	$pTransformacao = intval($_POST["f-transformacao-item"]);
	$pTransformacao_detalhar = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-transformacao-detalhar-item"])))));
	$pGarantia = intval($_POST["f-garantia-item"]);
	$pAcessorio = $_POST["f-acessorio"];
	$pValor_ace = $_POST["f-valor"];
	$pPreco_publico_vw = trim($_POST["f-preco-publico-vw-item"]);
	$pQuantidade_veiculos = intval($_POST["f-quantidade-veiculos-item"]);
	$pDn_venda = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-dn-venda-item"])))));
	$pDn_entrega = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-dn-entrega-item"])))));
	$pRepasse_concessionario = intval($_POST["f-repasse-concessionario-item"]);
	$pValidade_proposta = intval($_POST["f-validade-proposta-item"]);
	$pPrazo_entrega = intval($_POST["f-prazo-entrega-item"]);
	$pAve = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-ave-item"])))));
	$pOrigem_verba = intval($_POST["f-origem-verba-item"]);
	$pIsencao_impostos = intval($_POST["f-isensao-impostos-item"]);
	$pObservacoes = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-observacoes-item"])))));

	//ajustar valores
	if (isValidBrDate($pData_licitacao))
		$pData_licitacao = brToMysql($pData_licitacao);
	else
		$pData_licitacao = "0000-00-00";

	$pPreco_publico_vw = str_replace(array(".","R","$"," "), "", $pPreco_publico_vw);
	$pPreco_publico_vw = str_replace(",", ".", $pPreco_publico_vw);
	if (strlen($pPreco_publico_vw) == 0) $pPreco_publico_vw = '0.00';


	if ($pVersao == 1)
	{
		$pV1_documentacao_selecionados = trim($_POST["f-v1-documentacao-selecionados-item"]);
		$pV1_documentacao_outros = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-v1-documentacao-outros-item"])))));
		$pV1_desconto = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-v1-desconto-item"])))));
		$pV1_preco_maximo = trim($_POST["f-v1-preco-maximo-item"]);
		$pV1_prazo_pagamento = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-v1-prazo-pagamento-item"])))));
		$pV1_numero_pool = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-v1-numero-pool-item"])))));

		//ajustar valores
		$pV1_preco_maximo = str_replace(array(".","R","$"," "), "", $pV1_preco_maximo);
		$pV1_preco_maximo = str_replace(",", ".", $pV1_preco_maximo);
		if (strlen($pV1_preco_maximo) == 0) $pV1_preco_maximo = '0.00';
	}
	else if ($pVersao == 2)
	{
		$pCnpj_faturamento = trim($_POST["f-cnpj-faturamento-item"]);
		$pEstado = trim($_POST["f-estado-item"]);
		$pParticipante_endereco = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-participante-endereco-item"])))));
		$pParticipante_cep = $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-participante-endereco-cep"]))));
		$pParticipante_telefone = trim($_POST["f-participante-telefone-item"]);
		$pAno_modelo = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-ano-modelo-item"])))));
		$pEficiencia_energetica = intval($_POST["f-eficiencia-energetica-item"]);
		$pTransformacao_tipo = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-transformacao-tipo-item"])))));
		$pTransformacao_prototipo = intval($_POST["f-transformacao-prototipo-item"]);

		$pTransformacao_amarok_status = intval($_POST["f-amarok-status"]);
		$pTransformacao_amarok_anexo = utf8_decode(trim($_POST["f-amarok-anexo"]));
		$pTransformacao_amarok_arquivo = utf8_decode(trim($_POST["f-amarok-arquivo"]));

		$pAcessorios = intval($_POST["f-acessorios-item"]);
		$pEmplacamento = intval($_POST["f-emplacamento-item"]);
		$pLicenciamento = intval($_POST["f-licenciamento-item"]);
		$pIpva = intval($_POST["f-ipva-item"]);
		$pGarantia_prazo = intval($_POST["f-garantia-prazo-item"]);
		$pGarantia_prazo_outro = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-garantia-prazo-outro-item"])))));
		$pRevisao_embarcada = intval($_POST["f-revisao-embarcada-item"]);
		$pQuantidade_revisoes_inclusas = intval($_POST["f-quantidade-revisoes-inclusas-item"]);
		$pLimite_km = intval($_POST["f-limite-km-item"]);
		$pLimite_km_km = intval($_POST["f-limite-km-km-item"]);
		$pPreco_ref_edital = trim($_POST["f-preco-ref-edital-item"]);
		$pDn_venda_estado = trim($_POST["f-dn-venda-estado-item"]);
		$pDn_entrega_estado = trim($_POST["f-dn-entrega-estado-item"]);
		$pVigencia_contrato = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-vigencia-contrato-item"])))));
		$pPrazo_pagamento = intval($_POST["f-prazo-pagamento-item"]);
		$pMultas_sansoes = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-multas-sansoes-item"])))));
		$pGarantia_contrato = intval($_POST["f-garantia-contrato-item"]);
		$pPrazo = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-prazo-item"])))));
		$pValor = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-vlr-item"])))));
		$pOrigem_verba_tipo = intval($_POST["f-origem-verba-tipo-item"]);
		$pImposto_indicar = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-imposto-indicar-item"])))));

		//ajustar valores
		$pPreco_ref_edital = str_replace(array(".","R","$"," "), "", $pPreco_ref_edital);
		$pPreco_ref_edital = str_replace(",", ".", $pPreco_ref_edital);
		if (strlen($pPreco_ref_edital) == 0) $pPreco_ref_edital = '0.00';

		if ($pGarantia_prazo < 4)
			$pGarantia_prazo_outro = '';
	}


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
/*		$db->query("
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
		}
	}



	$nova = false;
	$now = date("Y-m-d H:i:s");


	// Verificar se esta APL eh nova
	$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = $pId_item AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
	if (!$db->nextRecord())
		$nova = true;


	//SEMPRE INSERIR
	if ($pVersao == 1)
		$query = "INSERT INTO gelic_licitacoes_apl VALUES (
NULL,
$pId_licitacao,
$sInside_id,
$pId_item,
$pVersao,
'$pNome_orgao',
'$pData_licitacao',
$pRegistro_precos,
'',
'',
$pModalidade_venda,
'$pSite_pregao_eletronico',
'$pNumero_licitacao',
'$pParticipante_nome',
'$pParticipante_cpf',
'$pParticipante_rg',
'',
'',
'$pV1_documentacao_selecionados',
'$pV1_documentacao_outros',
'$pModel_code',
'$pCor',
'',
'$pMotorizacao',
'$pPotencia',
'$pCombustivel',
'$pOpcionais_pr',
0,
$pTransformacao,
'',
0,
'$pTransformacao_detalhar',
'',
'',
0,
0,
0,
0,
$pGarantia,
0,
'',
0,
0,
0,
0,
$pPreco_publico_vw,
0.00,
$pQuantidade_veiculos,
'$pDn_venda',
'',
$pRepasse_concessionario,
'$pDn_entrega',
'',
$pPrazo_entrega,
$pValidade_proposta,
'',
0,
'$pV1_prazo_pagamento',
'$pV1_desconto',
$pV1_preco_maximo,
'$pV1_numero_pool',
'$pAve',
'',
0,
'',
'',
$pOrigem_verba,
0,
$pIsencao_impostos,
'',
'$pObservacoes',
'',
'')";
	else if ($pVersao == 2)
		$query = "INSERT INTO gelic_licitacoes_apl VALUES (
					NULL,
					$pId_licitacao,
					$sInside_id,
					$pId_item,
					$pVersao,
					'$pNome_orgao',
					'$pData_licitacao',
					$pRegistro_precos,
					'$pCnpj_faturamento',
					'$pEstado',
					$pModalidade_venda,
					'$pSite_pregao_eletronico',
					'$pNumero_licitacao',
					'$pParticipante_nome',
					'$pParticipante_cpf',
					'$pParticipante_rg',
					'$pParticipante_telefone',
					'$pParticipante_endereco',
					'[]',
					'',
					'$pModel_code',
					'$pCor',
					'$pAno_modelo',
					'$pMotorizacao',
					'$pPotencia',
					'$pCombustivel',
					'$pOpcionais_pr',
					$pEficiencia_energetica,
					$pTransformacao,
					'$pTransformacao_tipo',
					$pTransformacao_prototipo,
					'$pTransformacao_detalhar',
					'',
					'',
					$pAcessorios,
					$pEmplacamento,
					$pLicenciamento,
					$pIpva,
					$pGarantia,
					$pGarantia_prazo,
					'$pGarantia_prazo_outro',
					$pRevisao_embarcada,
					$pQuantidade_revisoes_inclusas,
					$pLimite_km,
					$pLimite_km_km,
					$pPreco_publico_vw,
					$pPreco_ref_edital,
					$pQuantidade_veiculos,
					'$pDn_venda',
					'$pDn_venda_estado',
					$pRepasse_concessionario,
					'$pDn_entrega',
					'$pDn_entrega_estado',
					$pPrazo_entrega,
					$pValidade_proposta,
					'$pVigencia_contrato',
					$pPrazo_pagamento,
					'',
					'',
					0.00,
					'',
					'$pAve',
					'$pMultas_sansoes',
					$pGarantia_contrato,
					'$pPrazo',
					'$pValor',
					$pOrigem_verba,
					$pOrigem_verba_tipo,
					$pIsencao_impostos,
					'$pImposto_indicar',
					'$pObservacoes',
					'',
					'$pParticipante_cep')";

	$db->query($query);
	if (!$db->Errno[0] == 0)
	{
		logThis('ERROR INSERTING APL...');
		logThis('=======================');
		logThis('IP: '.$_SERVER['REMOTE_ADDR']);
		logThis('FROM: '.basename(__FILE__));
		logThis('ACESSORIOS: '.print_r($pAcessorio, true));
		logThis('VALORES: '.print_r($pValor_ace, true));
		if ($pVersao == 2)
		{
			logThis('=========== arquivos checklist ============');
			logThis('TRANSF. AMAROK STATUS: '.$pTransformacao_amarok_status);
			logThis('TRANSF. AMAROK ANEXO: '.$pTransformacao_amarok_anexo);
			logThis('TRANSF. AMAROK ARQUIVO: '.$pTransformacao_amarok_arquivo);
		}
		logThis('=========== query ============');
		logThis($query);

		$aReturn[0] = 0;
		echo json_encode($aReturn);
		exit;
	}

	$dId_apl = $db->li();


	if ($pVersao == 2)
	{
		//processar arquivos checklist
		if ($pTransformacao_amarok_status == 0 || $pTransformacao_amarok_status == 2)
		{
			//atualizar campos com os valores atuais
			$db->query("UPDATE gelic_licitacoes_apl SET transformacao_amarok_nome_arquivo = '".$pTransformacao_amarok_anexo."', transformacao_amarok_arquivo = '".$pTransformacao_amarok_arquivo."' WHERE id = $dId_apl");
		}
		else if ($pTransformacao_amarok_status == 1 || $pTransformacao_amarok_status == 4)
		{
			if (!is_dir(UPLOAD_DIR."apl")) mkdir(UPLOAD_DIR."apl");

			//create hash
			$amarok_arquivo_md5 = strtolower(getFilename($dId_apl, $pTransformacao_amarok_anexo, 'cli'.time().$sInside_id));

			//save file
			rename(UPLOAD_DIR."~upload_apl_amarok_".$sInside_id.".tmp", UPLOAD_DIR."apl/".$amarok_arquivo_md5);

			//save hash, save filename
			$db->query("UPDATE gelic_licitacoes_apl SET transformacao_amarok_nome_arquivo = '".$pTransformacao_amarok_anexo."', transformacao_amarok_arquivo = '".$amarok_arquivo_md5."' WHERE id = $dId_apl");
		}
		else if ($pTransformacao_amarok_status == 3)
		{
			//remove hash, remove filename
			$db->query("UPDATE gelic_licitacoes_apl SET transformacao_amarok_nome_arquivo = '', transformacao_amarok_arquivo = '' WHERE id = $dId_apl");
		}
	}


	//inserir acessorios
	for ($i=0; $i<count($pAcessorio); $i++)
	{
		$pAcessorio[$i] = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($pAcessorio[$i])))));
		$pValor_ace[$i] = trim($pValor_ace[$i]);
		$pValor_ace[$i] = str_replace(array(".","R","$"," "), "", $pValor_ace[$i]);
		$pValor_ace[$i] = str_replace(",", ".", $pValor_ace[$i]);
		if (strlen($pValor_ace[$i]) == 0) $pValor_ace[$i] = '0.00';
		$db->query("INSERT INTO gelic_licitacoes_apl_acessorios VALUES (NULL, $dId_apl, '".$pAcessorio[$i]."', ".$pValor_ace[$i].")");
	}


	$db->query("SELECT texto FROM gelic_texto WHERE id = 16");
	$db->nextRecord();
	$dTexto = $db->f("texto");

	$db->query("SELECT texto FROM gelic_texto WHERE id = 1");
	$db->nextRecord();
	$dTexto .= ' | '.$db->f("texto");

	$ip = $_SERVER['REMOTE_ADDR'];

	//adicionar APL historico tipo 1
	$db->query("INSERT INTO gelic_licitacoes_apl_historico VALUES (NULL, $dId_apl, $sInside_id, 1, '$ip', 0, 0, '$now', '$dTexto')");
	$dId_apl_historico = $db->li();

	//adicionar historico tipo 41 (APL Enviada)
	$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, $dId_apl, $sInside_id, 0, 41, $dId_apl_historico, 0, '$now', '', '', '')");
	$dId_historico = $db->li();



	//==========================
	// Atualizar tabela EAR
	//==========================
	$db->query("SELECT 
		(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = itm.id AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))) HAVING tipo IN (1,5,6)) AS t) AS enviadas,
		(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = itm.id AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))) HAVING tipo = 2) AS t) AS aprovadas,
		(SELECT COUNT(*) FROM (SELECT (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo FROM gelic_licitacoes_itens AS itm INNER JOIN gelic_licitacoes_apl AS apl ON apl.id_item = itm.id AND apl.id = (SELECT MAX(id) FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = itm.id AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))) HAVING tipo = 4) AS t) AS reprovadas");
	$db->nextRecord();
	$enviadas = $db->f("enviadas");
	$aprovadas = $db->f("aprovadas");
	$reprovadas = $db->f("reprovadas");
	
	$db->query("SELECT id FROM gelic_licitacoes_apl_ear WHERE id_licitacao = $pId_licitacao AND id_cliente = $cliente_parent");
	if ($db->nextRecord())
		$db->query("UPDATE gelic_licitacoes_apl_ear SET enviadas = $enviadas, aprovadas = $aprovadas, reprovadas = $reprovadas WHERE id_licitacao = $pId_licitacao AND id_cliente = $cliente_parent");
	else
		$db->query("INSERT INTO gelic_licitacoes_apl_ear VALUES (NULL, $pId_licitacao, $cliente_parent, $enviadas, $aprovadas, $reprovadas)");
	//==========================



	//STATUS E ABAS ANTES DA ALTERACAO
	$aLic_aba_status = array("fr"=>array(),"to"=>array());
	$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao ORDER BY grupo");
	while ($db->nextRecord())
		$aLic_aba_status["fr"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));


	//*************************************************************************************
	//****************************** ALTERACAO DE ABA/STATUS ******************************
	//*************************************************************************************
	if ($dFase == 1)
	{
		//alterar aba (Grupos 1,2,3)
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 14 WHERE id_licitacao = $pId_licitacao AND id_status NOT IN (8,19) AND grupo IN (1,2,3)");

		//alterar status (Grupos 1,2,3)
		$db->query("UPDATE gelic_licitacoes_abas SET id_status = 3 WHERE id_licitacao = $pId_licitacao AND id_status NOT IN (8,19) AND status_fixo = 0 AND grupo IN (1,2,3)");

		//alterar aba (Grupo 4)
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 8 WHERE id_licitacao = $pId_licitacao AND id_status NOT IN (27,28) AND grupo = 4");

		//alterar status (Grupo 4)
		$db->query("UPDATE gelic_licitacoes_abas SET id_status = 26 WHERE id_licitacao = $pId_licitacao AND id_status NOT IN (27,28) AND status_fixo = 0 AND grupo = 4");

		//se existir na aba em participacao entao adicionar na aba APL tambem para o ADMIN e BO se ainda nao estiver la
		$db->query("SELECT id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo = 2 AND id_aba = 9");
		if ($db->nextRecord())
		{
			$dId_status = $db->f("id_status");
			$dStatus_fixo = $db->f("status_fixo");

			$db->query("SELECT id FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo = 2 AND id_aba = 14");
			if (!$db->nextRecord())
			{
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 1, 14, $dId_status, $dStatus_fixo)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 2, 14, $dId_status, $dStatus_fixo)"); //BO
			}
		}
	}
	else
	{
		//alterar aba (Grupos 1,2,3)
		$db->query("UPDATE gelic_licitacoes_abas SET id_aba = 14 WHERE id_licitacao = $pId_licitacao AND id_status NOT IN (8,19) AND grupo IN (1,2,3)");

		//alterar status (Grupos 1,2,3)
		$db->query("UPDATE gelic_licitacoes_abas SET id_status = 30 WHERE id_licitacao = $pId_licitacao AND id_status NOT IN (8,19) AND status_fixo = 0 AND grupo IN (1,2,3)");

		//se existir na aba em participacao entao adicionar na aba APL tambem para o ADMIN e BO se ainda nao estiver la
		$db->query("SELECT id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo = 2 AND id_aba = 9");
		if ($db->nextRecord())
		{
			$dId_status = $db->f("id_status");
			$dStatus_fixo = $db->f("status_fixo");

			$db->query("SELECT id FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao AND grupo = 2 AND id_aba = 14");
			if (!$db->nextRecord())
			{
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 1, 14, $dId_status, $dStatus_fixo)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $pId_licitacao, 2, 14, $dId_status, $dStatus_fixo)"); //BO
			}
		}
	}
	//*************************************************************************************
	//********************************         END         ********************************
	//*************************************************************************************


	//STATUS E ABAS DEPOIS DA ALTERACAO
	$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $pId_licitacao ORDER BY grupo");
	while ($db->nextRecord())
		$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));

	$db->query("UPDATE gelic_historico SET texto = '".json_encode($aLic_aba_status)."' WHERE id = $dId_historico");



	$arquivo_md5 = salvarAPLemPDF($dId_apl);
	$tAnexos = '[{"nome_arquivo":"apl.pdf","arquivo":"'.$arquivo_md5.'.pdf","id_licitacao":'.$pId_licitacao.',"id_apl":'.$dId_apl.'}]';

	//atualizar APL com o nome do arquivo PDF
	$db->query("UPDATE gelic_licitacoes_apl SET arquivo = '".$arquivo_md5.".pdf' WHERE id = $dId_apl");

	//buscar DN
	$db->query("SELECT dn FROM gelic_clientes WHERE id = $cliente_parent");
	$db->nextRecord();
	$dDn = $db->f("dn");


	//buscar informacao da licitacao
	$db->query("
SELECT 
	lic.orgao,
    lic.datahora_abertura,
	lic.numero,
	cid.nome AS nome_cidade,
	uf.uf,
    uf.regiao_abv,
	mdl.abv
FROM 
	gelic_licitacoes AS lic
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE 
	lic.id = $pId_licitacao");
	$db->nextRecord();

	$dUf = $db->f("uf");
	$dNome_cidade = $db->f("nome_cidade");
	$dDatahora_abertura = $db->f("datahora_abertura");


	//-----------------------------------------
	//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
	//-----------------------------------------
	$tEmail_assunto = $db->f("regiao_abv").'('.$dUf.') - DN '.$dDn.' - (APL) '.utf8_encode($db->f("orgao")).' '.$db->f("abv").' '.utf8_encode($db->f("numero")).' ABERT '.mysqlToBr(substr($db->f("datahora_abertura"),0,10));
	$tEmail_assunto_admin = $db->f("regiao_abv").'('.$dUf.') - DN '.$dDn.' - APL - LIC '.$pId_licitacao;

	
	//buscar informacao de quem enviou
	$db->query("
SELECT
	his.ip,
    his.data_hora,
	cli.id_parent,
    cli.nome,
	clidn.nome AS dn_nome,
	item.item,
	lote.lote
FROM
	gelic_licitacoes_apl AS apl
	LEFT JOIN gelic_licitacoes_itens AS item ON item.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lote ON lote.id = item.id_lote
    INNER JOIN gelic_licitacoes AS lic ON lic.id = apl.id_licitacao
    INNER JOIN gelic_licitacoes_apl_historico AS his ON his.id_apl = apl.id AND his.tipo = 1
    LEFT JOIN gelic_clientes AS cli ON cli.id = his.id_cliente
    LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	apl.id = $dId_apl
ORDER BY
	his.id DESC LIMIT 1");
	$db->nextRecord();

	$por = utf8_encode($db->f("nome"));
	if ($db->f("id_parent") > 0)
		$por .= ' (DN: '.utf8_encode($db->f("dn_nome")).')';
	
	if ($nova)
		$inicio = 'Uma nova APL foi enviada.';
	else
		$inicio = 'APL alterada.';

	$dab_h = substr($dDatahora_abertura,11,5);
	if ($dab_h == "00:00") $dab_h = "--:--";

	$tEmail_mensagem = $inicio.'<br><br>
<span style="font-weight: bold;">Licitação:</span> '.$pId_licitacao.'<br>
<span style="font-weight: bold;">Data de Abertura:</span> '.mysqlToBr(substr($dDatahora_abertura,0,10)).' '.$dab_h.'<br>
<span style="font-weight: bold;">Localização:</span> '.utf8_encode($dNome_cidade).' - '.$dUf.'<br>
<span style="font-weight: bold;">Lote:</span> '.utf8_encode($db->f("lote")).'<br>
<span style="font-weight: bold;">Item:</span> '.utf8_encode($db->f("item")).'<br>
<span style="font-weight: bold;">Enviada Por:</span> '.$por.'<br>
<span style="font-weight: bold;">Origem:</span> '.$db->f("ip").'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
	$tTexto_sms = 'GELIC - '.$inicio. ' LIC '.$pId_licitacao.' LOTE: '.utf8_encode($db->f("lote")).' ITEM: '.utf8_encode($db->f("item")).' DN: '.$dDn;


	// Notificar ADMINs
	$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
	while ($db->nextRecord())
	{
		if (in_array("D", str_split($db->f("nt_email"))))
			queueMessage(4, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto_admin, $tEmail_mensagem, $tAnexos, '');

		if (in_array("D", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
			queueMessage(4, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
	}


	// Notificar BOs
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
	cli.tipo = 1 AND
	cli.notificacoes = 1 AND 
	cli.ativo = 1 AND
	cli.deletado = 0");
	while ($db->nextRecord())
	{
		$dNt_email = json_decode($db->f("nt_email"), true);
		$dNt_sms = json_decode($db->f("nt_celular"), true);

		if (in_array("E", str_split($dNt_email["ntf"])) && in_region("E", $dUf, $dNt_email["reg"]))
			queueMessage(21, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_BOF, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, $tAnexos, '');

		if (in_array("E", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0 && in_region("E", $dUf, $dNt_sms["reg"]))
			queueMessage(21, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_BOF, $db->f("id"), $db->f("celular"), '', $tTexto_sms, '', '');
	}
	//-----------------------------------------
	//-----------------------------------------
	//-----------------------------------------


	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
