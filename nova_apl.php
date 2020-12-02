<?php

require_once "include/config.php";
require_once "include/essential.php";


$pId_licitacao = 15411;
$pId_item = 12257;

$pId_licitacao = 2;
$pId_item = 1;

$db = new Mysql();

// Preencher valores padrao para uma APL vazia
$tApl = '';
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
$dAve = "";
$dMultas_sansoes = "";
$dGarantia_contrato = 0;
$dPrazo = "";
$dValor = "";
$dOrigem_verba = 0;
$dOrigem_verba_tipo = 0;
$dIsencao_impostos = 0;
$dImposto_indicar = "";
$dObservacoes = "";
$tAcessorios = '<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 1</span>
			<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="65535">
			<span class="apl-lb w-100 bg-3 center">Valor 1 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" maxlength="30" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 2</span>
			<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="65535">
			<span class="apl-lb w-100 bg-3 center">Valor 2 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" maxlength="30" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 3</span>
			<input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="65535">
			<span class="apl-lb w-100 bg-3 center">Valor 3 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" maxlength="30" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-100 bg-3 center">Acessório 4</span>
			<input id="i-acessorio-4" class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="65535">
			<span class="apl-lb w-100 bg-3 center">Valor 4 (R$)</span>
			<input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" maxlength="30" style="text-align: right;">
			<span class="rm-acess-dummy"></span>
		</div>
		<div id="aaBtn-row" class="apl-row apl-br apl-bb apl-bl" style="background-color:#efefef;">
			<a class="bt-apl-style-1 fl" href="javascript:void(0);" onclick="adicionarAcessorio();" title="Adicionar Acessório" style="margin-bottom: 6px; border-bottom-right-radius:5px;">+</a>
		</div>';

// Preencher campos automaticamente
$db->query("SELECT id, orgao, datahora_abertura, numero, link, srp, id_modalidade FROM gelic_licitacoes WHERE id = $pId_licitacao");
$db->nextRecord();
$dId_licitacao = $db->f("id");
$dNome_orgao = utf8_encode($db->f("orgao"));
$dData_licitacao = mysqlToBr(substr($db->f("datahora_abertura"),0,10));
$dRegistro_precos = intval($db->f("srp"));
$dNumero_licitacao = utf8_encode($db->f("numero"));

if ($db->f("id_modalidade") == 3) $dModalidade_venda = 2;
else if ($db->f("id_modalidade") == 7) $dModalidade_venda = 3;
else if ($db->f("id_modalidade") == 2) $dModalidade_venda = 5;
else if ($db->f("id_modalidade") == 4) $dModalidade_venda = 6;
else if ($db->f("id_modalidade") == 1) $dModalidade_venda = 7;
	
if (strlen($db->f("link")) > 0 && $db->f("id_modalidade") == 1)
	$dSite_pregao_eletronico = utf8_encode($db->f("link"));


$readonly = false;

$input_readonly = '';
$check_readonly = '';
if ($readonly)
{
	$input_readonly = ' readonly';
	$check_readonly = ' data-mode="readonly"';
}


$tApl = '<div style="position: relative; overflow: hidden; width: 100%;">
		<form id="form-apl-item">
		<input type="hidden" name="f-registro-precos-item" value="'.$dRegistro_precos.'">
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
		<input type="hidden" name="f-garantia-contrato-item" value="'.$dGarantia_contrato.'">
		<input type="hidden" name="f-origem-verba-item" value="'.$dOrigem_verba.'">
		<input type="hidden" name="f-origem-verba-tipo-item" value="'.$dOrigem_verba_tipo.'">
		<input type="hidden" name="f-isensao-impostos-item" value="'.$dIsencao_impostos.'">

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
		<div id="hook-modalidade" class="apl-row apl-br apl-bb apl-bl" style="height: 6px;"></div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-936 bg-1 white center">Modalidade da Venda</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl bg-3" style="height: 76px;">
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
		<div class="apl-row apl-br apl-bb apl-bl" style="height: 6px;"></div>
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
			<input id="i-participante-endereco-item" class="apl-input bg-3" style="width:666px;" type="text" name="f-participante-endereco-item" maxlength="140" value="'.$dParticipante_endereco.'"'.$input_readonly.'>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl" style="height: 6px;"></div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-936 bg-1 white center">Especificação do Veículo (Resumo do Edital)</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-128 bg-3 center">Model Code</span>
			<input id="i-model-code-item" class="apl-input bg-2 white" style="width:120px;" type="text" name="f-model-code-item" maxlength="6" value="'.$dModel_code.'"'.$input_readonly.'>
			<span class="apl-lb w-100 bg-3 center">Cor</span>
			<input id="i-cor-item" class="apl-input bg-2 white" style="width:120px;" type="text" name="f-cor-item" maxlength="4" value="'.$dCor.'"'.$input_readonly.'>
			<span class="apl-lb w-128 bg-3 center">Ano/Modelo</span>
			<input id="i-ano-modelo-item" class="apl-input bg-2 white" style="width:340px;" type="text" name="f-ano-modelo-item" maxlength="60" value="'.$dAno_modelo.'"'.$input_readonly.'>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-128 bg-3 center">Motorização</span>
			<input id="i-motorizacao-item" class="apl-input bg-2 white" style="width:120px;" type="text" name="f-motorizacao-item" maxlength="3" value="'.$dMotorizacao.'"'.$input_readonly.'>
			<span class="apl-lb w-100 bg-3 center">Potência</span>
			<input id="i-potencia-item" class="apl-input bg-2 white" style="width:120px;" type="text" name="f-potencia-item" maxlength="3" value="'.$dPotencia.'"'.$input_readonly.'>
			<span class="apl-lb w-128 bg-3 center">Combustível</span>
			<input id="i-combustivel-item" class="apl-input bg-2 white" style="width:340px;" type="text" name="f-combustivel-item" maxlength="10" value="'.$dCombustivel.'"'.$input_readonly.'>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-128 bg-3 center">Opcionais (PR\'s)</span>
			<input id="i-opcionais-pr-item" class="apl-input bg-2 white" style="width:462px;" type="text" name="f-opcionais-pr-item" maxlength="65535" value="'.$dOpcionais_pr.'"'.$input_readonly.'>
			<span class="apl-lb bg-3 center" style="width:216px;">Eficiência Energética CONPET</span>
			<div class="apl-lb w-130 bg-2">
				<a class="rbw'.(int)in_array($dEficiencia_energetica, array(1)).' cl-eficiencia-energetica-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'eficiencia-energetica-item\',1);" style="left: 14px; top: 7px;">Sim</a>
				<a class="rbw'.(int)in_array($dEficiencia_energetica, array(2)).' cl-eficiencia-energetica-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'eficiencia-energetica-item\',2);" style="left: 70px; top: 7px;">Não</a>
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
			<textarea id="i-transformacao-detalhar-item" class="apl-textarea" name="f-transformacao-detalhar-item" maxlength="65535" style="float:left;width:858px;height:109px;margin-left:40px;margin-bottom:10px;"'.$input_readonly.'>'.$dTransformacao_detalhar.'</textarea>
		</div>
		<div id="cl-amarok" class="apl-row apl-br apl-bl bg-3" style="padding-top:10px;padding-bottom:10px;">
			<span class="apl-cl-label">Check List Transformação AMAROK</span>
			<div class="apl-upl-box">
				<a class="ubtn" href="javascript:void(0);" onclick="selectFileAPL_AMAROK();">Selecionar arquivo<img src="img/upload.png"></a>
				<a class="dlnk" href="javascript:void(0);">Baixar modelo aqui</a>
				<span>Obrigatório o preenchimento e envio do check list.</span>
			</div>
			<div class="apl-upl-loading" style="display:none;">
				<div class="bar"></div>
				<div class="text"></div>
			</div>
			<div class="apl-upl-ready" style="display:none;">
				<span></span>
				<a href="javascript:void(0);" onclick="cancelUploadAPL_AMAROK();" title="Cancelar"></a>
			</div>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl bg-1 center white">
			<div style="display:inline-block;overflow:hidden;">
				<span id="hook-acessorios" class="apl-lb">Acessórios (Custo pago pelo DN)</span>
				<a class="rbw'.(int)in_array($dAcessorios, array(1)).' cl-acessorios-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'acessorios-item\',1);" style="position:relative;float:left;margin:8px 0 0 40px;">Sim</a>
				<a class="rbw'.(int)in_array($dAcessorios, array(2)).' cl-acessorios-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'acessorios-item\',2);" style="position:relative;float:left;margin:8px 0 0 14px;">Não</a>
			</div>
		</div>
		<div id="acessorios" style="display:none;">
			'.$tAcessorios.'
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span id="hook-emplacamento" class="apl-lb bg-3 center" style="width:182px;">Emplacamento</span>
			<div class="apl-lb bg-2" style="width:130px;">
				<a class="rbw'.(int)in_array($dEmplacamento, array(1)).' cl-emplacamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'emplacamento-item\',1);" style="left: 14px; top: 7px;">Sim</a>
				<a class="rbw'.(int)in_array($dEmplacamento, array(2)).' cl-emplacamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'emplacamento-item\',2);" style="left: 70px; top: 7px;">Não</a>
			</div>
			<span class="apl-lb bg-3 center" style="width:182px;">Licenciamento</span>
			<div class="apl-lb bg-2" style="width:130px;">
				<a class="rbw'.(int)in_array($dLicenciamento, array(1)).' cl-licenciamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'licenciamento-item\',1);" style="left: 14px; top: 7px;">Sim</a>
				<a class="rbw'.(int)in_array($dLicenciamento, array(2)).' cl-licenciamento-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'licenciamento-item\',2);" style="left: 70px; top: 7px;">Não</a>
			</div>
			<span class="apl-lb bg-3 center" style="width:182px;">IPVA</span>
			<div class="apl-lb bg-2" style="width:130px;">
				<a class="rbw'.(int)in_array($dIpva, array(1)).' cl-ipva-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'ipva-item\',1);" style="left: 14px; top: 7px;">Sim</a>
				<a class="rbw'.(int)in_array($dIpva, array(2)).' cl-ipva-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'ipva-item\',2);" style="left: 70px; top: 7px;">Não</a>
			</div>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-936 bg-1 white center">Garantia e Revisões</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span id="hook-garantia" class="apl-lb w-300 bg-imp center">Garantia</span>
			<div class="apl-lb w-636 bg-2">
				<a class="rbw'.(int)in_array($dGarantia, array(1)).' cl-garantia-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-item\',1);" style="left: 40px; top: 7px;">Garantia Padrão</a>
				<a class="rbw'.(int)in_array($dGarantia, array(2)).' cl-garantia-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-item\',2);" style="left: 200px; top: 7px;">Garantia Diferenciada</a>
			</div>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span id="hook-garantia-prazo" class="apl-lb bg-3 center" style="width:300px;">Prazo de Garantia</span>
			<input id="i-garantia-prazo-outro-item" class="apl-input bg-2 white" style="display:none;width:324px;padding-right:32px;" type="text" name="f-garantia-prazo-outro-item" maxlength="40" value="'.$dGarantia_prazo_outro.'"'.$input_readonly.'>
			<div id="i-garantia-prazo-drop-item" class="apl-lb bg-2" style="width:324px;">
				<a id="drop-estado-4" class="estado-drop drp"'.$check_readonly.' href="javascript:void(0);" onclick="dropEstado(4);" style="width:324px;padding-left:14px;"><span></span><img src="img/a-down-w.png"></a>
			</div>
			<a id="i-garantia-prazo-x-item" class="xed" href="javascript:void(0);" onclick="hidePGed();"></a>
			<span id="hook-revisao-embarcada" class="apl-lb bg-3 center" style="width:182px;">Revisão Embarcada</span>
			<div class="apl-lb bg-2" style="width:130px;">
				<a class="rbw'.(int)in_array($dRevisao_embarcada, array(1)).' cl-revisao-embarcada-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'revisao-embarcada-item\',1);" style="left: 14px; top: 7px;">Sim</a>
				<a class="rbw'.(int)in_array($dRevisao_embarcada, array(2)).' cl-revisao-embarcada-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'revisao-embarcada-item\',2);" style="left: 70px; top: 7px;">Não</a>
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
			<span id="i-limite-kmed-item-lb" class="apl-lb bg-2 center" style="display:none;width:39px;color:#f1f1f1;border-left:1px solid #cccccc;">KM:</span>
			<input id="i-limite-km-km-item" class="apl-input bg-2 white" style="display:none;width:186px;" type="text" name="f-limite-km-km-item" maxlength="10" value="'.$dLimite_km_km.'"'.$input_readonly.'>
			<div id="i-limite-kmed-item-fake" class="apl-lb bg-2" style="width:226px;"></div>
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
				<a class="rbw'.(int)in_array($dGarantia_contrato, array(1)).' cl-garantia-contrato-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-contrato-item\',1);" style="left: 14px; top: 7px;">Sim</a>
				<a class="rbw'.(int)in_array($dGarantia_contrato, array(2)).' cl-garantia-contrato-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'garantia-contrato-item\',2);" style="left: 70px; top: 7px;">Não</a>
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
			<a class="rb'.(int)in_array($dOrigem_verba, array(1)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',1);" style="left: 40px; top: 12px;">Federal</a>
			<a class="rb'.(int)in_array($dOrigem_verba, array(2)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',2);" style="left: 250px; top: 12px;">Estadual</a>
			<a class="rb'.(int)in_array($dOrigem_verba, array(3)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',3);" style="left: 460px; top: 12px;">Municipal</a>
			<a class="rb'.(int)in_array($dOrigem_verba, array(4)).' cl-origem-verba-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-item\',4);" style="left: 670px; top: 12px;">Convenio</a>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl bg-2" style="height:41px;">
			<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(1)).' cl-origem-verba-tipo-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-tipo-item\',1);" style="left: 40px; top: 12px;">A Vista</a>
			<a class="rbw'.(int)in_array($dOrigem_verba_tipo, array(2)).' cl-origem-verba-tipo-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'origem-verba-tipo-item\',2);" style="left: 250px; top: 12px;">A Prazo</a>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span id="hook-isencao-impostos" class="apl-lb w-936 bg-1 white center">Isenções de Impostos do Órgão</span>
		</div>
		<div class="apl-row apl-br apl-bl bg-3" style="height:80px;">
			<a class="rb'.(int)in_array($dIsencao_impostos, array(1)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',1);" style="left: 40px; top: 14px;">Não possui isenção</a>
			<a class="rb'.(int)in_array($dIsencao_impostos, array(2)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',2);" style="left: 250px; top: 14px;">IPI</a>
			<a class="rb'.(int)in_array($dIsencao_impostos, array(3)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',3);" style="left: 460px; top: 14px;">ICMS Substituto</a>
			<a class="rb'.(int)in_array($dIsencao_impostos, array(4)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',4);" style="left: 40px; top: 46px;">IPI + ICMS</a>
			<a class="rb'.(int)in_array($dIsencao_impostos, array(5)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',5);" style="left: 250px; top: 46px;">ICMS</a>
			<a class="rb'.(int)in_array($dIsencao_impostos, array(6)).' cl-isensao-impostos-item"'.$check_readonly.' href="javascript:void(0);" onclick="ckSingle(this,\'isensao-impostos-item\',6);" style="left: 460px; top: 46px;">IPI + ICMS Substituto</a>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl bg-3">
			<span class="apl-lb w-936 italic" style="margin-left:40px;">Indicar item do edital ou Lei que confirme a marcação acima</span>
			<textarea id="i-imposto-indicar-item" class="apl-textarea" name="f-imposto-indicar-item" maxlength="65535" style="float:left;width:858px;height:60px;margin-left:40px;margin-bottom:10px;"'.$input_readonly.'>'.$dImposto_indicar.'</textarea>
			<span class="apl-lb italic" style="margin-left:40px;">OBS: Em caso de isenção de ICMS Substituto, enviar via chat GELIC Lei/Decreto para que seja confirmado pelo Tributário.</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl">
			<span class="apl-lb w-936 bg-1 white center">Observações Gerais</span>
		</div>
		<div class="apl-row apl-br apl-bb apl-bl bg-3">
			<textarea id="i-observacoes-item" class="apl-textarea" name="f-observacoes-item" style="margin:10px 0 10px 40px;width:858px;height:109px;"'.$input_readonly.'>'.$dObservacoes.'</textarea>
		</div>
		<div id="hook-aceite" class="apl-row apl-br apl-bb apl-bl bg-3" style="overflow:hidden;padding:20px 40px;">
			<a id="i-aceite" class="cb0" href="javascript:void(0);" onclick="ckSelfish(this,\'aceite-item\',1);" style="position:relative;float:left;font-size:12px;height:auto;color:#565656;">Li e concordo com os termos constantes no Edital e na APL, assumindo todo e qualquer custo não coberto pela Volkswagen do Brasil, como por exemplo: emplacamento, IPVA, acessórios como película solar, rádio USB, alarme, revisões preventivas, adesivagem/plotagem, etc. Após aprovação da APL pela Volkswagen assumo compromisso de comparecer em data e horário estipulados para este referido pregão e representar a mesma na condição de credenciado. Comprometo-me também à enviar a ATA assim que emitida pelo órgão competente.</a>
		</div>
		</form>';



$tApl .= '<div class="apl-row" style="margin: 20px 0; padding: 0 40px;">
		<a class="bt-style-1 fl" href="javascript:void(0);" onclick="salvarAPL_item('.$pId_item.', false);" style="height:46px;line-height:46px;padding:0 26px;">Enviar APL</a>
	</div></div>';


$tPage = new Template("nova_apl.html");
$tPage->replace("{{APL}}", $tApl);
$tPage->replace("{{VERSION}}", VERSION);

echo $tPage->body;


?>
