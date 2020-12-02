<?php

require_once "include/config.php";
require_once "include/essential.php";


/*********************************************************************************/
function salvarAPLemPDF($id_apl)
{
	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();

	$tApl = '';
	$dDn_nome = "";
	$dDescritivo_veiculo = "";
	$dNome_orgao = "";
	$dModalidade_da_venda = 0;
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
	$tAcessorios = '';

	$db->query("
SELECT 
	apl.*,
	comp.descritivo_veiculo,
	IF (cli.id_parent > 0, clip.nome, cli.nome) AS dn_nome
FROM
	gelic_comprasrp_apl AS apl
	INNER JOIN gelic_comprasrp AS comp ON comp.id = apl.id_comprasrp
	INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
WHERE
	apl.id = $id_apl");
	if ($db->nextRecord())
	{
		$dDn_nome = utf8_encode($db->f("dn_nome"));
		$dDescritivo_veiculo = utf8_encode($db->f("descritivo_veiculo"));
		$dNome_orgao = utf8_encode($db->f("nome_orgao"));
		$dModalidade_da_venda = intval($db->f("modalidade_da_venda"));
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

		$db->query("SELECT acessorio, valor FROM gelic_comprasrp_apl_acessorios WHERE id_apl = $id_apl ORDER BY id");
		while ($db->nextRecord())
		{
			$dAcessorio = utf8_encode($db->f("acessorio"));
			$dValor = "R$ ".number_format($db->f("valor"), 2, ",", ".");
			if ($dValor == "R$ 0,00") $dValor = "";

			$tAcessorios .= '<div class="apl-row apl-br apl-bb apl-bl">
				<span class="apl-lb w-100 bg-3 center">Acessório '.$db->Row[0].'</span>
				<span class="apl-lb w-576 bg-2 white lpad">'.$dAcessorio.'</span>
				<span class="apl-lb w-100 bg-3 center">Valor '.$db->Row[0].' (R$)</span>
				<span class="apl-lb w-160 bg-2 white rpad right">'.$dValor.'</span>
			</div>';
		}
	}

	$tApl = '
		<div class="apl-row" style="margin-bottom: 4px; text-align: center;">
			<span><a style="font-size: 24px;">APL - AUTORIZAÇÃO DE PARTICIPAÇÃO EM LICITAÇÕES</a><br><a class="bold">DN:</a> '.$dDn_nome.'&nbsp;&nbsp;&nbsp;<a class="bold">Descritivo do Veículo:</a> '.$dDescritivo_veiculo.'</span>
		</div>
		<div class="apl-row apl-bt apl-br apl-bb apl-bl">
			<span class="apl-lb w-238 bg-1 white center">Nome do Órgão</span>
			<span class="apl-lb w-704 bg-3 lpad">'.$dNome_orgao.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-238 bg-1 white center">Modalidade</span>
			<div class="apl-lb w-704 bg-4">
				<a class="rb'.(int)in_array($dModalidade_da_venda, array(1)).'" style="left: 140px; top: 7px;">Compra Direta</a>
				<a class="rb'.(int)in_array($dModalidade_da_venda, array(2)).'" style="left: 360px; top: 7px;">Adesão a Registro de Preços</a>
			</div>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl" style="height: 6px;"></div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100p bg-1 white center">Documentação: Documentos Solicitados pelo Órgão (Licitações/Compra Direta)</span>
			<span class="apl-lb w-100p bg-1 white center italic">Dados do Participante pela VW (Concessionário / Pool)</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-1 white center">Nome</span>
			<span class="apl-lb w-836 bg-3 lpad">'.$dDocumentacao_nome.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-1 white center">RG</span>
			<span class="apl-lb w-368 bg-3 lpad">'.$dDocumentacao_rg.'</span>
			<span class="apl-lb w-100 bg-1 white center">CPF</span>
			<span class="apl-lb w-368 bg-3 lpad">'.$dDocumentacao_cpf.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100p bg-1 white center">Relacione os Documentos</span>
		</div>
		<div class="apl-row apl-br apl-bl bg-3" style="height: 165px;">
			<a class="cb'.(int)in_array(1, $dDocumentacao_selecionados).'" style="left: 40px; top: 14px;">Atestado de Capacidade Técnica</a>
			<a class="cb'.(int)in_array(2, $dDocumentacao_selecionados).'" style="left: 40px; top: 34px;">Ato Constitutivo</a>
			<a class="cb'.(int)in_array(3, $dDocumentacao_selecionados).'" style="left: 40px; top: 54px;">Balanço Patrimonial</a>
			<a class="cb'.(int)in_array(4, $dDocumentacao_selecionados).'" style="left: 40px; top: 74px;">Certidão de Tributos Estaduais</a>
			<a class="cb'.(int)in_array(5, $dDocumentacao_selecionados).'" style="left: 40px; top: 94px;">Certidão de Tributos Federais / Divida Ativa da União (Internet)</a>
			<a class="cb'.(int)in_array(6, $dDocumentacao_selecionados).'" style="left: 40px; top: 114px;">Certidão de Tributos Municipais</a>
			<a class="cb'.(int)in_array(7, $dDocumentacao_selecionados).'" style="left: 40px; top: 134px;">CND INSS (Internet)</a>
			<a class="cb'.(int)in_array(8, $dDocumentacao_selecionados).'" style="left: 490px; top: 14px;">CNDT - Certidão Negativa de Débitos Trabalhistas (Internet)</a>
			<a class="cb'.(int)in_array(9, $dDocumentacao_selecionados).'" style="left: 490px; top: 34px;">CNPJ (Internet)</a>
			<a class="cb'.(int)in_array(10, $dDocumentacao_selecionados).'" style="left: 490px; top: 54px;">Falência e Concordata</a>
			<a class="cb'.(int)in_array(11, $dDocumentacao_selecionados).'" style="left: 490px; top: 74px;">FGTS (Internet)</a>
			<a class="cb'.(int)in_array(12, $dDocumentacao_selecionados).'" style="left: 490px; top: 94px;">Ficha de Inscrição Estadual)</a>
			<a class="cb'.(int)in_array(13, $dDocumentacao_selecionados).'" style="left: 490px; top: 114px;">Ficha de Inscrição Municipal</a>
			<a class="cb'.(int)in_array(14, $dDocumentacao_selecionados).'" style="left: 490px; top: 134px;">Procuração</a>
		</div>
		<div class="apl-row apl-br apl-bl bg-3">
			<span class="apl-lb w-100p italic" style="margin-left: 40px;">Relacione outros documentos que não constam entre os citados acima:</span>
		</div>
		<div class="apl-row apl-br apl-bl">
			<span class="apl-textarea">'.$dDocumentacao_outros.'</span>
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
			<span class="apl-lb w-130 bg-3 center">Transformação</span>
			<div class="apl-lb w-130 bg-2">
				<a class="rbw'.(int)in_array($dTransformacao, array(1)).'" style="left: 14px; top: 7px;">Sim</a>
				<a class="rbw'.(int)in_array($dTransformacao, array(2)).'" style="left: 70px; top: 7px;">Não</a>
			</div>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100p bg-1 white center">Detalhar Transformação</span>
		</div>
		<div class="apl-row apl-br apl-bl apl-bb">
			<span class="apl-textarea" style="margin-top: 10px;">'.$dDetalhamento_transformacao.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-300 bg-3 center">Garantia do(s) Veículo(s):</span>
			<div class="apl-lb w-636 bg-3">
				<a class="rb'.(int)in_array($dGarantia, array(1)).'" style="left: 40px; top: 7px;">Garantia Padrão</a>
				<a class="rb'.(int)in_array($dGarantia, array(2)).'" style="left: 300px; top: 7px;">Garantia Diferenciada</a>
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
			<span class="apl-lb w-160 bg-2 white lpad">'.$dPreco_publico.'</span>
			<span class="apl-lb w-100 bg-3 center">Desconto</span>
			<span class="apl-lb w-118 bg-2 white lpad">'.$dDesconto.'</span>
			<span class="apl-lb w-128 bg-3 center">Repasse (%)</span>
			<span class="apl-lb w-60 bg-2 white lpad">'.$dRepasse.'</span>
			<span class="apl-lb w-130 bg-3 center">DN de Venda</span>
			<span class="apl-lb w-98 bg-2 white lpad">'.$dDn_venda.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-130 bg-3 center">Preço Máximo</span>
			<span class="apl-lb w-160 bg-2 white lpad">'.$dPreco_maximo.'</span>
			<span class="apl-lb w-160 bg-3 center">Validade da Proposta</span>
			<span class="apl-lb w-252 bg-2 white lpad">'.$dValidade_da_proposta.'</span>
			<span class="apl-lb w-130 bg-3 center">DN de Entrega</span>
			<span class="apl-lb w-98 bg-2 white lpad">'.$dDn_entrega.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-130 bg-3 center">Prazo de Entrega</span>
			<span class="apl-lb w-160 bg-2 white lpad">'.$dPrazo_de_entrega.'</span>
			<span class="apl-lb w-160 bg-3 center">Prazo de Pagamento</span>
			<span class="apl-lb w-252 bg-2 white lpad">'.$dPrazo_de_pagamento.'</span>
			<span class="apl-lb w-130 bg-3 center">Quantidade</span>
			<span class="apl-lb w-98 bg-2 white lpad">'.$dQuantidade.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-130 bg-3 center">AVE</span>
			<span class="apl-lb w-160 bg-2 white lpad">'.$dAve.'</span>
			<span class="apl-lb w-100 bg-3 center">Nº do Pool</span>
			<span class="apl-lb w-546 bg-2 white lpad">'.$dNumero_pool.'</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100p bg-1 white center">Origem da Verba</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 41px;">
			<a class="rb'.(int)in_array($dOrigem_da_verba, array(1)).'" style="left: 40px; top: 12px;">Federal</a>
			<a class="rb'.(int)in_array($dOrigem_da_verba, array(2)).'" style="left: 250px; top: 12px;">Estadual</a>
			<a class="rb'.(int)in_array($dOrigem_da_verba, array(3)).'" style="left: 460px; top: 12px;">Municipal</a>
			<a class="rb'.(int)in_array($dOrigem_da_verba, array(4)).'" style="left: 670px; top: 12px;">Convenio</a>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100p bg-1 white center">Isenções de Impostos do Órgão</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 106px;">
			<a class="rb'.(int)in_array($dIsencao_de_impostos, array(1)).'" style="left: 40px; top: 14px;">Não possui isenção</a>
			<a class="rb'.(int)in_array($dIsencao_de_impostos, array(2)).'" style="left: 250px; top: 14px;">IPI</a>
			<a class="rb'.(int)in_array($dIsencao_de_impostos, array(3)).'" style="left: 460px; top: 14px;">ICMS Substituto</a>
			<a class="rb'.(int)in_array($dIsencao_de_impostos, array(4)).'" style="left: 40px; bottom: 43px;">IPI + ICMS</a>
			<a class="rb'.(int)in_array($dIsencao_de_impostos, array(5)).'" style="left: 250px; bottom: 43px;">ICMS</a>
			<a class="rb'.(int)in_array($dIsencao_de_impostos, array(6)).'" style="left: 460px; bottom: 43px;">IPI + ICMS Substituto</a>
			<span class="apl-lb italic" style="position: absolute; bottom: 6px; left: 40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via e-mail Lei/Decreto para que seja confirmado pelo Tributário.</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100p bg-1 white center">Observações Gerais</span>
		</div>
		<div class="apl-row apl-br apl-bl apl-bb">
			<span class="apl-textarea" style="margin-top: 10px;">'.$dObservacoes_gerais.'</span>
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

		.w-60 { width: 60px; }
		.w-98 { width: 98px; }
		.w-100 { width: 100px; }
		.w-118 { width: 118px; }
		.w-128 { width: 128px; }
		.w-130 { width: 130px; }
		.w-142 { width: 142px; }
		.w-160 { width: 160px; }
		.w-238 { width: 238px; }
		.w-252 { width: 252px; }
		.w-300 { width: 300px; }
		.w-368 { width: 368px; }
		.w-402 { width: 402px; }
		.w-546 { width: 546px; }
		.w-576 { width: 576px; }
		.w-636 { width: 636px; }
		.w-704 { width: 704px; }
		.w-836 { width: 836px; }
		.w-100p { width: 100%; }

		.bg-1 { background-color: #386eb1; }
		.bg-2 { background-color: #aaaaaa; }
		.bg-3 { background-color: #ffffff; }
		.bg-4 { background-color: #f1f1f1; }

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
			display: block;
			margin: 0 auto 10px auto;
			width: 868px;
			border: 1px dotted #cccccc;
			padding: 6px;
			}

	</style>
</head>
<body>
<div style="width: 950px; overflow: hidden;">
'.$tApl.'
</div>
</body>
</html>';

	$arquivo_md5 = md5($id_apl.time().$sInside_id);
	$oFile = fopen(UPLOAD_DIR."~".$arquivo_md5.".html", "w");
	fwrite($oFile, $tHtml);
	fclose($oFile);

	exec(PATH_HTMTOPDF." --image-quality 100 ".UPLOAD_DIR."~".$arquivo_md5.".html ".UPLOAD_DIR."~".$arquivo_md5.".pdf");
	
	@unlink(UPLOAD_DIR."~".$arquivo_md5.".html");

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
		$sInside_id = $_SESSION[SESSION_ID_DN];
	//----------------------------------------------


	$xAccess = explode(" ",getAccess());

	if ($sInside_tipo == 1 || !in_array("cd_apl_enviar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$db = new Mysql();

	$pId_cd = intval($_POST["id-cd"]);
	$pNome_orgao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-nome-orgao"])))));
	$pNumero_pregao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-numero-pregao"])))));
	$pDocumentacao_nome = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-doc-nome"])))));
	$pDocumentacao_rg = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-doc-rg"])))));
	$pDocumentacao_cpf = trim($_POST["f-doc-cpf"]);
	$pDocumentacao_selecionados = trim($_POST["f-docs"]);
	$pDocumentacao_outros = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-doc-outros"])))));
	$pModel_code = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-model-code"])))));
	$pCor = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-cor"])))));
	$pOpcionais_pr = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-pr"])))));
	$pMotorizacao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-motorizacao"])))));
	$pPotencia = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-potencia"])))));
	$pCombustivel = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-combustivel"])))));
	$pTransformacao = intval($_POST["f-trans"]);
	$pDetalhamento_transformacao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-detalhamento-transformacao"])))));
	$pGarantia = intval($_POST["f-gar"]);
	$pAcessorio = $_POST["f-acessorio"];
	$pValor = $_POST["f-valor"];
	$pPreco_publico = trim($_POST["f-preco-publico"]);
	$pDesconto = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-desconto"])))));
	$pRepasse = intval($_POST["f-repasse"]);
	$pDn_venda = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-dnvenda"])))));
	$pPreco_maximo = trim($_POST["f-preco-maximo"]);
	$pValidade_da_proposta = intval($_POST["f-validade-proposta"]);
	$pDn_entrega = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-dnentrega"])))));
	$pPrazo_de_entrega = intval($_POST["f-prazo-entrega"]);
	$pPrazo_de_pagamento = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-prazo-pagamento"])))));
	$pQuantidade = intval($_POST["f-quantidade"]);
	$pAve = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-ave"])))));
	$pNumero_pool = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-numero-pool"])))));
	$pOrigem_da_verba = intval($_POST["f-verba"]);
	$pIsencao_de_impostos = intval($_POST["f-imp"]);
	$pObservacoes_gerais = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-observacoes"])))));

	//corrigir valores
	$pPreco_publico = str_replace(array(".","R","$"," "), "", $pPreco_publico);
	$pPreco_publico = str_replace(",", ".", $pPreco_publico);
	if (strlen($pPreco_publico) == 0) $pPreco_publico = '0.00';

	$pPreco_maximo = str_replace(array(".","R","$"," "), "", $pPreco_maximo);
	$pPreco_maximo = str_replace(",", ".", $pPreco_maximo);
	if (strlen($pPreco_maximo) == 0) $pPreco_maximo = '0.00';


	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];


	$nova = false;
	$now = date("Y-m-d H:i:s");

	
	// Verificar se esta APL eh nova
	$db->query("SELECT id FROM gelic_comprasrp_apl WHERE id_comprasrp = $pId_cd");
	if (!$db->nextRecord())
		$nova = true;


	//SEMPRE INSERIR
	$db->query("SELECT tipo FROM gelic_comprasrp WHERE id = $pId_cd");
	$db->nextRecord();
	$pModalidade_da_venda = $db->f("tipo");


	$db->query("INSERT INTO gelic_comprasrp_apl VALUES (
NULL, 
$pId_cd, 
$sInside_id,
'$pNome_orgao', 
$pModalidade_da_venda, 
'$pNumero_pregao', 
'$pDocumentacao_nome', 
'$pDocumentacao_rg', 
'$pDocumentacao_cpf', 
'$pDocumentacao_selecionados', 
'$pDocumentacao_outros', 
'$pModel_code', 
'$pCor', 
'$pOpcionais_pr', 
'$pMotorizacao', 
'$pPotencia', 
'$pCombustivel', 
$pTransformacao,
'$pDetalhamento_transformacao',
$pGarantia, 
$pPreco_publico, 
'$pDesconto', 
$pRepasse, 
'$pDn_venda', 
$pPreco_maximo, 
$pValidade_da_proposta, 
'$pDn_entrega', 
$pPrazo_de_entrega, 
'$pPrazo_de_pagamento', 
$pQuantidade, 
'$pAve', 
'$pNumero_pool', 
$pOrigem_da_verba, 
$pIsencao_de_impostos, 
'$pObservacoes_gerais',
'')");
	if (!$db->Errno[0] == 0)
	{
		logThis('==== QUERY ERRO ===');
		logThis('IP: '.$_SERVER['REMOTE_ADDR']);
		logThis('FROM: '.basename(__FILE__));
		logThis('=========== query ============');
		logThis("INSERT INTO gelic_comprasrp_apl VALUES (
NULL, 
$pId_cd, 
$sInside_id,
'$pNome_orgao', 
$pModalidade_da_venda, 
'$pNumero_pregao', 
'$pDocumentacao_nome', 
'$pDocumentacao_rg', 
'$pDocumentacao_cpf', 
'$pDocumentacao_selecionados', 
'$pDocumentacao_outros', 
'$pModel_code', 
'$pCor', 
'$pOpcionais_pr', 
'$pMotorizacao', 
'$pPotencia', 
'$pCombustivel', 
$pTransformacao,
'$pDetalhamento_transformacao',
$pGarantia, 
$pPreco_publico, 
'$pDesconto', 
$pRepasse, 
'$pDn_venda', 
$pPreco_maximo, 
$pValidade_da_proposta, 
'$pDn_entrega', 
$pPrazo_de_entrega, 
'$pPrazo_de_pagamento', 
$pQuantidade, 
'$pAve', 
'$pNumero_pool', 
$pOrigem_da_verba, 
$pIsencao_de_impostos, 
'$pObservacoes_gerais',
'')");
		logThis('ACESSORIOS: '.print_r($pAcessorio, true));
		logThis('VALORES: '.print_r($pValor, true));

		$aReturn[0] = 0;
		echo json_encode($aReturn);
		exit;
	}

	$dId_apl = $db->li();

	//inserir acessorios
	for ($i=0; $i<count($pAcessorio); $i++)
	{
		$pAcessorio[$i] = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($pAcessorio[$i])))));
		$pValor[$i] = trim($pValor[$i]);
		$pValor[$i] = str_replace(array(".","R","$"," "), "", $pValor[$i]);
		$pValor[$i] = str_replace(",", ".", $pValor[$i]);
		if (strlen($pValor[$i]) == 0) $pValor[$i] = '0.00';
		$db->query("INSERT INTO gelic_comprasrp_apl_acessorios VALUES (NULL, $dId_apl, '".$pAcessorio[$i]."', ".$pValor[$i].")");
	}

	$db->query("SELECT texto FROM gelic_texto WHERE id = 11");
	$db->nextRecord();
	$dTexto = $db->f("texto");

	$ip = $_SERVER['REMOTE_ADDR'];

	//adicionar APL historico tipo 1
	$db->query("INSERT INTO gelic_comprasrp_apl_historico VALUES (NULL, $dId_apl, $sInside_id, 1, '$ip', 0, 0, '$now', '$dTexto')");
	$dId_apl_historico = $db->li();

	//adicionar historico tipo 41 (APL Enviada)
	$db->query("INSERT INTO gelic_comprasrp_historico VALUES (NULL, $pId_cd, $dId_apl, $sInside_id, 41, $dId_apl_historico, '$now', '', '', '')");

	


	//-----------------------------------------
	//--- ENVIO DE NOTIFICACOES EMAIL / SMS ---
	//-----------------------------------------
	$arquivo_md5 = salvarAPLemPDF($dId_apl);
	$tAnexos = '[{"nome_arquivo":"apl.pdf","arquivo":"'.$arquivo_md5.'.pdf","id_comprasrp":'.$pId_cd.',"id_apl":'.$dId_apl.'}]';

	//atualizar APL com o nome do arquivo PDF
	$db->query("UPDATE gelic_comprasrp_apl SET arquivo = '".$arquivo_md5.".pdf' WHERE id = $dId_apl");

	$db->query("
SELECT
	IF (cdsrp.tipo = 1, 'Solicitação de Compra Direta', 'Adesão à ata de Registro de Preços') AS tipo_desc,
	cdsrp.tipo,
    cdsrp.orgao,
    cdsrp.descritivo_veiculo,
    cdsrp.numero_srp,
	cdsrp.data_srp,
	cdsrp.quantidade,
	cdsrp.valor,
    IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome_dn,
	IF (cli.id_parent > 0, clip.dn, cli.dn) AS dn,
	cid.uf,
	(SELECT data_hora FROM gelic_comprasrp_historico WHERE id_comprasrp = cdsrp.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS data_hora
FROM
	gelic_comprasrp AS cdsrp
    INNER JOIN gelic_clientes AS cli ON cli.id = cdsrp.id_cliente
    LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
WHERE
	cdsrp.id = $pId_cd");
	$db->nextRecord();
	$dUf = $db->f("uf"); // para filtrar notif. enviadas para o BO
	

	$tEmail_srp = '';
	if ($db->f("tipo") == 2)
		$tEmail_srp = '<span style="font-weight: bold;">Número do SRP:</span> '.utf8_encode($db->f("numero_srp")).'<br>
<span style="font-weight: bold;">Data do SRP:</span> '.mysqlToBr($db->f("data_srp")).'<br>';


	if ($nova)
	{
		$tEmail_assunto = 'GELIC - Uma nova APL foi enviada (COMPRA DIRETA/SRP)';
		$tInicio = 'Nova APL';
	}
	else
	{
		$tEmail_assunto = 'GELIC - APL alterada (COMPRA DIRETA/SRP)';
		$tInicio = 'APL alterada';
	}

	$tEmail_mensagem = $tInicio.' referente a seguinte Compra Direta/SRP.<br><br>
<span style="font-weight: bold;">DN:</span> '.utf8_encode($db->f("nome_dn")).'<br><br>
<span style="font-weight: bold;">Tipo da Solicitação:</span> '.$db->f("tipo_desc").'<br>
<span style="font-weight: bold;">Data/Hora da Solicitação:</span> '.mysqlToBr(substr($db->f("data_hora"),0,10)).' '.substr($db->f("data_hora"),11).'<br>
<span style="font-weight: bold;">Nome do Órgão Público:</span> '.utf8_encode($db->f("orgao")).'<br>
<span style="font-weight: bold;">Descritivo do Veículo:</span> '.utf8_encode($db->f("descritivo_veiculo")).'<br>
'.$tEmail_srp.'<span style="font-weight: bold;">Quantidade:</span> '.$db->f("quantidade").'<br>
<span style="font-weight: bold;">Valor:</span> R$ '.number_format($db->f("valor"),2,",",".").'<br>
<span style="font-weight: bold;">Registro no Sistema:</span> '.mysqlToBr(substr($now,0,10)).' - '.substr($now,11).'<br><br>
'.rodapeEmail();
	$tTexto_sms = $tEmail_assunto. ' DN: '.$db->f("dn");


	// Notificar ADMINs
	$db->query("SELECT id, email, celular, nt_email, nt_celular FROM gelic_admin_usuarios WHERE notificacoes = 1 AND ativo = 1");
	while ($db->nextRecord())
	{
		if (in_array("L", str_split($db->f("nt_email"))))
			queueMessage(12, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_ADM, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, $tAnexos, '');

		if (in_array("L", str_split($db->f("nt_celular"))) && strlen($db->f("celular")) > 0)
			queueMessage(12, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_ADM, $db->f("id"), $db->f("celular"), '', $tTexto_sms, $tAnexos, '');
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

		if (in_array("J", str_split($dNt_email["ntf"])) && in_region("J", $dUf, $dNt_email["reg"]))
			queueMessage(26, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_EMAIL, DLR_BOF, $db->f("id"), $db->f("email"), $tEmail_assunto, $tEmail_mensagem, $tAnexos, '');

		if (in_array("J", str_split($dNt_sms["ntf"])) && strlen($db->f("celular")) > 0 && in_region("J", $dUf, $dNt_sms["reg"]))
			queueMessage(26, 0, basename(__FILE__).' ('.__LINE__.')', $sInside_id, M_SMS, DLR_BOF, $db->f("id"), $db->f("celular"), '', $tTexto_sms, $tAnexos, '');
	}
	//-----------------------------------------
	//-----------------------------------------
	//-----------------------------------------


	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
