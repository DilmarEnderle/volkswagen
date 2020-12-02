<?php

require_once "include/config.php";
require_once "include/essential.php";

$db = new Mysql();
$db->query("SELECT * FROM gelic_licitacoes_apl");
while ($db->nextRecord())
{
	$id = $db->f("id");
	$id_licitacao = $db->f("id_licitacao");
	$id_cliente = $db->f("id_cliente");
	$id_item = $db->f("id_item");
	$nome_orgao = $db->escapeString($db->f("nome_orgao"));
	$data_licitacao = $db->f("data_licitacao");
	$numero_id_licitacao = $db->f("numero_id_licitacao");
	$registro_de_precos = $db->f("registro_de_precos");
	$modalidade_da_venda = $db->f("modalidade_da_venda");
	$site_pregao_eletronico = $db->f("site_pregao_eletronico");
	$numero_licitacao = $db->f("numero_licitacao");
	$documentacao_nome = $db->escapeString($db->f("documentacao_nome"));
	$documentacao_rg = $db->escapeString($db->f("documentacao_rg"));
	$documentacao_cpf = $db->f("documentacao_cpf");
	$documentacao_selecionados = $db->f("documentacao_selecionados");
	$documentacao_outros = $db->escapeString($db->f("documentacao_outros"));
	$model_code = $db->f("model_code");
	$cor = $db->f("cor");
	$opcionais_pr = $db->f("opcionais_pr");
	$motorizacao = $db->f("motorizacao");
	$potencia = $db->f("potencia");
	$combustivel = $db->f("combustivel");
	$transformacao = $db->f("transformacao");
	$detalhamento_transformacao = $db->escapeString($db->f("detalhamento_transformacao"));
	$garantia = $db->f("garantia");
	$preco_publico = $db->f("preco_publico");
	$desconto = $db->f("desconto");
	$repasse = $db->f("repasse");
	$dn_venda = $db->f("dn_venda");
	$preco_maximo = $db->f("preco_maximo");
	$validade_da_proposta = $db->f("validade_da_proposta");
	$dn_entrega = $db->f("dn_entrega");
	$prazo_de_entrega = $db->f("prazo_de_entrega");
	$prazo_de_pagamento = $db->escapeString($db->f("prazo_de_pagamento"));
	$quantidade = $db->f("quantidade");
	$ave = $db->f("ave");
	$no_pool = $db->f("no_pool");
	$origem_da_verba = $db->f("origem_da_verba");
	$isencao_de_impostos = $db->f("isencao_de_impostos");
	$observacoes_gerais = $db->escapeString($db->f("observacoes_gerais"));
	$arquivo = $db->f("arquivo");



	$acessorios = 2;
	$db->query("SELECT id FROM gelic_licitacoes_apl_acessorios WHERE id_apl = $id AND acessorio <> ''",1);
	if ($db->nextRecord(1))
		$acessorios = 1;



	$db->query("
INSERT INTO gelic_licitacoes_apl_new VALUES (
$id,
$id_licitacao,
$id_cliente,
$id_item,
1,
'$nome_orgao',
'$data_licitacao',
$registro_de_precos,
'',
'',
$modalidade_da_venda,
'$site_pregao_eletronico',
'$numero_licitacao',
'$documentacao_nome',
'$documentacao_cpf',
'$documentacao_rg',
'',
'',
'$documentacao_selecionados',
'$documentacao_outros',
'$model_code',
'$cor',
'',
'$motorizacao',
'$potencia',
'$combustivel',
'$opcionais_pr',
0,
$transformacao,
'',
0,
'$detalhamento_transformacao',
'',
'',
$acessorios,
0,
0,
0,
$garantia,
0,
'',
0,
0,
0,
0,
$preco_publico,
0.00,
$quantidade,
'$dn_venda',
'',
$repasse,
'$dn_entrega',
'',
$prazo_de_entrega,
$validade_da_proposta,
'',
0,
'$prazo_de_pagamento',
'$desconto',
$preco_maximo,
'$no_pool',
'$ave',
'',
0,
'',
'',
$origem_da_verba,
0,
$isencao_de_impostos,
'',
'$observacoes_gerais',
'$arquivo')",1);
}

echo 'done';

?>
