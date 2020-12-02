var rtext_apl_amarok = {};
var rtext_apl_amarok_upload = {};
var rtext_apl_spacefox = {};
var rtext_apl_spacefox_upload = {};

$(document).ready(function(){


	$(document).click(function(e){
		if (!$(e.target).hasClass("drp") && !$(e.target).parent().hasClass("drp"))
			closeDropBox();
	});

	$(document).keydown(function(e) {
		if (e.keyCode == 27 && $("#drop-box").is(":visible"))
			closeDropBox();
	});

	$("#i-data-licitacao-item").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#i-id-licitacao-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
	$("#i-cnpj-faturamento-item").inputmask("99.999.999/9999-99");
	$("#i-participante-cpf-item").inputmask("999.999.999-99");
	$("#i-participante-telefone-item").inputmask("(99) 9999-9999[9]");
	$("#i-participante-telefone-item").keyup(function(){
	if ($(this).inputmask("unmaskedvalue").length > 10)
		$(this).inputmask("(99) 99999-9999");
	else
		$(this).inputmask("(99) 9999-9999[9]");
	});
	$("#i-quantidade-revisoes-inclusas-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
	$("#i-limite-km-km-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
	$("#i-preco-ref-edital-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
	$("#i-prazo-pagamento-item").inputmask({"mask":"9", "repeat":5, "greedy":false});

	rtext_apl_amarok.status = 0;
	rtext_apl_amarok.long_filename = "";
	rtext_apl_amarok.short_filename = "";
	rtext_apl_amarok.file_size = "";
	rtext_apl_amarok.file = "";
	rtext_apl_amarok_upload = {};

	rtext_apl_spacefox.status = 0;
	rtext_apl_spacefox.long_filename = "";
	rtext_apl_spacefox.short_filename = "";
	rtext_apl_spacefox.file_size = "";
	rtext_apl_spacefox.file = "";
	rtext_apl_spacefox_upload = {};

	$('#form-apl-item input[name^="f-valor"]').each(function(){
		$(this).maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
	});

	$("#i-preco-publico-vw-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
	$("#i-quantidade-veiculos-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
	$("#i-repasse-concessionario-item").inputmask({"mask":"9", "repeat":3, "greedy":false});
	$("#i-prazo-entrega-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
	$("#i-validade-proposta-item").inputmask({"mask":"9", "repeat":5, "greedy":false});

	$("#i-nome-orgao-item").focus();

	autosize($("#i-transformacao-detalhar-item"));
	autosize($("#i-imposto-indicar-item"));
	autosize($("#i-observacoes-item"));

	$.fn.goTo = function() {
		$('html, body').animate({
			scrollTop: ($(this).offset().top - 16) + 'px'
		}, 'fast');
		return this; // for chaining...
	}
});

function ckSingle(ob, cl, vl)
{
	if ($(ob).attr("data-mode") == "readonly")
		return false;
	
	if ($(ob).hasClass("rb0") || $(ob).hasClass("rb1"))
	{
		$(".cl-"+cl).attr("class","rb0 cl-"+cl);
		$(ob).attr("class", "rb1 cl-"+cl);
		$('input[name="f-'+cl+'"]').val(vl);
	}
	else
	{
		$(".cl-"+cl).attr("class","rbw0 cl-"+cl);
		$(ob).attr("class", "rbw1 cl-"+cl);
		$('input[name="f-'+cl+'"]').val(vl);

		if (vl == 1 && cl == "acessorios-item")
			$("#acessorios").show();
		else if (vl == 2 && cl == "acessorios-item")
			$("#acessorios").hide();

		if (cl == "limite-km-item")
		{
			if (vl == 1)
			{
				$("#i-limite-kmed-item-fake").hide();
				$("#i-limite-kmed-item-lb").show();
				$("#i-limite-km-km-item").show();
				$("#i-limite-km-km-item").focus();
			}
			else
			{
				$("#i-limite-km-km-item").hide();
				$("#i-limite-kmed-item-lb").hide();
				$("#i-limite-kmed-item-fake").show();
			}
		}
	}
}

function ckSelfish(ob, cl, vl)
{
	if ($(ob).attr("data-mode") == "readonly")
		return false;

	if ($(ob).hasClass("cb0"))
	{
		$(ob).attr("class", "cb1");
		//add
		j = JSON.parse($('input[name="f-'+cl+'"]').val());
		if ($.inArray(vl, j) == -1)
			j.push(vl);
		$('input[name="f-'+cl+'"]').val("["+j.toString()+"]");
	}
	else
	{
		$(ob).attr("class", "cb0");
		//remove
		j = JSON.parse($('input[name="f-'+cl+'"]').val());
		idx = $.inArray(vl, j);
		if (idx > -1)
			j.splice(idx, 1);
		$('input[name="f-'+cl+'"]').val("["+j.toString()+"]");
	}
}

function ckSelfishONLY(ob)
{
	if ($(ob).hasClass("cb0"))
		$(ob).attr("class", "cb1");
	else
		$(ob).attr("class", "cb0");
}

function isValidCPF(v)
{
	if (v.length < 14) return false;
	var i;
	exp = /\.|\-|\//g
 	var cpf = v.toString().replace(exp,"");
	var c = cpf.substr(0,9);
	var dv = cpf.substr(9,2);
	var d1 = 0;
	for (i=0; i<9; i++)
	{
		d1 += c.charAt(i)*(10-i);
	}
	if (d1 == 0) return false;
 
	d1 = 11 - (d1 % 11);
	if (d1 > 9) d1 = 0;
	if (dv.charAt(0) != d1) return false;
 
	d1 *= 2;
	for (i=0; i<9; i++)
	{
		d1 += c.charAt(i)*(11-i);
	}
	d1 = 11 - (d1 % 11);
	if (d1 > 9) d1 = 0;
	if (dv.charAt(1) != d1) return false;
	
	return true;	
}

function isValidCNPJ(v)
{
	if (v.length<18) return false;
	var cnpj = v;
	var valida = new Array(6,5,4,3,2,9,8,7,6,5,4,3,2);
	var dig1= new Number;
	var dig2= new Number;
        
	exp = /\.|\-|\//g
	cnpj = cnpj.toString().replace( exp, "" ); 
	var digito = new Number(eval(cnpj.charAt(12)+cnpj.charAt(13)));
                
	for(i = 0; i<valida.length; i++)
	{
		dig1 += (i>0? (cnpj.charAt(i-1)*valida[i]):0);  
		dig2 += cnpj.charAt(i)*valida[i];       
	}
	dig1 = (((dig1%11)<2)? 0:(11-(dig1%11)));
	dig2 = (((dig2%11)<2)? 0:(11-(dig2%11)));
        
	if(((dig1*10)+dig2) != digito)
		return false;  
	else
		return true;
}

function scrollToElement(elm, focus)
{
	$(elm).goTo();
	if (focus)
		$(elm).focus();
}

function salvarAPL_item(id_item, now)
{
	$("#i-nome-orgao-item").val($.trim($("#i-nome-orgao-item").val()));
	$("#i-endereco-orgao-item").val($.trim($("#i-endereco-orgao-item").val()));
	$("#i-site-pregao-eletronico-item").val($.trim($("#i-site-pregao-eletronico-item").val()));
	$("#i-numero-licitacao-item").val($.trim($("#i-numero-licitacao-item").val()));
	$("#i-participante-nome-item").val($.trim($("#i-participante-nome-item").val()));
	$("#i-participante-rg-item").val($.trim($("#i-participante-rg-item").val()));
	$("#i-participante-endereco-item").val($.trim($("#i-participante-endereco-item").val()));
	$("#i-model-code-item").val($.trim($("#i-model-code-item").val()));
	$("#i-cor-item").val($.trim($("#i-cor-item").val()));
	$("#i-ano-modelo-item").val($.trim($("#i-ano-modelo-item").val()));
	$("#i-motorizacao-item").val($.trim($("#i-motorizacao-item").val()));
	$("#i-potencia-item").val($.trim($("#i-potencia-item").val()));
	$("#i-combustivel-item").val($.trim($("#i-combustivel-item").val()));
	$("#i-opcionais-pr-item").val($.trim($("#i-opcionais-pr-item").val()));
	$("#i-transformacao-tipo-item").val($.trim($("#i-transformacao-tipo-item").val()));
	$("#i-transformacao-detalhar-item").val($.trim($("#i-transformacao-detalhar-item").val()));
	$('#form-apl-item input[name^="f-acessorio"]').each(function(){ $(this).val($.trim($(this).val())); });
	$("#i-garantia-prazo-outro-item").val($.trim($("#i-garantia-prazo-outro-item").val()));
	$("#i-dn-venda-estado-item").val($.trim($("#i-dn-venda-estado-item").val()));
	$("#i-dn-entrega-estado-item").val($.trim($("#i-dn-entrega-estado-item").val()));
	$("#i-vigencia-contrato-item").val($.trim($("#i-vigencia-contrato-item").val()));
	$("#i-ave-item").val($.trim($("#i-ave-item").val()));
	$("#i-multas-sansoes-item").val($.trim($("#i-multas-sansoes-item").val()));
	$("#i-prazo-item").val($.trim($("#i-prazo-item").val()));
	$("#i-vlr-item").val($.trim($("#i-vlr-item").val()));
	$("#i-imposto-indicar-item").val($.trim($("#i-imposto-indicar-item").val()));
	$("#i-observacoes-item").val($.trim($("#i-observacoes-item").val()));

	if ($("#i-nome-orgao-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do órgão.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-nome-orgao-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-data-licitacao-item").inputmask("unmaskedvalue").length < 8)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a data da licitação.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-data-licitacao-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-id-licitacao-item").val().length == 0 || $("#i-id-licitacao-item").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a Licitação Nro.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-id-licitacao-item',true);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-registro-precos-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione registro de preços.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-id-licitacao-item',false);ultimateClose();"}}});
		return false;
	}

	if ($("#i-cnpj-faturamento-item").inputmask("unmaskedvalue").length < 14)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o CNPJ corretamente.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-cnpj-faturamento-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#drop-estado-1").children("span").html().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o estado.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-cnpj-faturamento-item',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-modalidade-venda-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione modalidade da venda.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-modalidade',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-modalidade-venda-item"]').val() == 7 && $("#i-site-pregao-eletronico-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o site pregão eletrônico.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-site-pregao-eletronico-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-numero-licitacao-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o número da licitação.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-numero-licitacao-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-participante-nome-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do participante.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-nome-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-participante-cpf-item").inputmask("unmaskedvalue").length < 11)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'O número CPF do participante está incorreto.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-cpf-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-participante-rg-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o RG do participante.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-rg-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-participante-telefone-item").inputmask("unmaskedvalue").length < 10)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o telefone do participante corretamente.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-telefone-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-participante-endereco-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o endereço completo do participante.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-endereco-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-model-code-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o model code.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-model-code-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-cor-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a cor.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-cor-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-ano-modelo-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o ano e modelo.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-ano-modelo-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-motorizacao-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a motorização.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-motorizacao-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-potencia-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a potência.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-potencia-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-combustivel-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o combustível.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-combustivel-item',true);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-eficiencia-energetica-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione eficiência energética.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-opcionais-pr-item',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-transformacao-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione transformação.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-transformacao',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-transformacao-prototipo-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione protótipo.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-transformacao',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-acessorios-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione acessórios.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-acessorios',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-emplacamento-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o emplacamento.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-emplacamento',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-licenciamento-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o licenciamento.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-emplacamento',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-ipva-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o IPVA.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-emplacamento',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-garantia-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a garantia.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-garantia',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-garantia-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o prazo da garantia.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-garantia-prazo',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-garantia-item"]').val() == 4 && $("#i-garantia-prazo-outro-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o prazo da garantia.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-garantia-prazo-outro-item',true);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-revisao-embarcada-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione revisão embarcada.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-revisao-embarcada',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-limite-km-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione limite de KM.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-revisao-embarcada',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-limite-km-item"]').val() == 1 && ($("#i-limite-km-km-item").val().length == 0 || $("#i-limite-km-km-item").val() == 0))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o limite em KM.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-limite-km-km-item',true);ultimateClose();"}}});
		return false;
	}






	if ($("#i-preco-publico-vw-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o preço público VW.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-preco-publico-vw-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-quantidade-veiculos-item").val().length == 0 || $("#i-quantidade-veiculos-item").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a quantidade de veículos.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-quantidade-veiculos-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-dn-venda-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o DN de venda.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-dn-venda-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#drop-estado-2").children("span").html().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o estado do DN de venda.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-dn-venda-item',false);ultimateClose();"}}});
		return false;
	}

	if ($("#i-dn-entrega-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o DN de entrega.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-dn-entrega-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#drop-estado-3").children("span").html().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o estado do DN de entrega.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-dn-entrega-item',false);ultimateClose();"}}});
		return false;
	}

	if ($("#i-prazo-entrega-item").val().length == 0 || $("#i-prazo-entrega-item").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o prazo de entrega.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-prazo-entrega-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-validade-proposta-item").val().length < 2)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a validade da proposta.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-validade-proposta-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-vigencia-contrato-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a vigência do contrato.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-vigencia-contrato-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-ave-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a AVE.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-ave-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-multas-sansoes-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe multas e sansões.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-multas-sansoes-item',true);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-garantia-contrato-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a garantia de contrato.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-garantia-contrato',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-origem-verba-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a origem da verba.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-origem-verba',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-origem-verba-tipo-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a origem da verba (A Vista ou A Prazo).',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-origem-verba',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-isensao-impostos-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione isenção de impostos do órgão.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-isencao-impostos',false);ultimateClose();"}}});
		return false;
	}

	if ($("#cl-amarok").children(".apl-upl-loading").is(":visible"))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Aguarde o término do carregamento do arquivo.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#cl-amarok',false);ultimateClose();"}}});
		return false;
	}

	if ($("#i-aceite").hasClass("cb0"))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'É necessário aceitar as condições antes do envio da APL.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-aceite',false);ultimateClose();"}}});
		return false;
	}

	if (now)
	{
		$("#ultimate-error").hide();
		if ($("#i-aceitar").hasClass("cb0"))
		{
			ajax = true;
			ultimateError('<p><span class="bold">Erro: </span>É necessário aceitar as condições antes do envio da APL.</p>');
			return false;
		}

		if (ajax)
		{
			ultimateClose();
			ultimateDialog({title:'Sucesso!',color:'green',content:'A APL é enviada neste momento.',buttons:{'Ok':{is_default:1}}});
		}
	}
	else
	{
		ajax = false;
		ultimateLoader(true, '');
		$.ajax({
			type:"post",
			url:"nova_apl_termos.php",
			dataType:"text",
			success:function(data)
			{
				ultimateLoader(false, '');
				ajax = true;
				ultimateDialog({
					title:'Condições para envio da APL',
					color:'gray',
					content:data,
					width:600,
					buttons:{'Continuar':{onclick:'salvarAPL_item('+id_item+',true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
				});
			}
		});
	}
}

function adicionarAcessorio()
{
	$("#aaBtn-row").before('<div class="apl-row apl-br apl-bb apl-bl"><span class="apl-lb w-100 bg-3 center">Acessório ?</span><input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="65535"><span class="apl-lb w-100 bg-3 center">Valor ? (R$)</span><input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" maxlength="30" style="text-align: right;"><a class="rm-acess" href="javascript:void(0);" onclick="removerAcessorio(this);" title="Remover Acessório"></a></div>');
	$('#form-apl-item input[name^="f-valor"]').each(function(){
		$(this).maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
	});
	reSequenciarAcessorios();
}

function removerAcessorio(ob)
{
	$(ob).parent().remove();
	reSequenciarAcessorios();
}

function reSequenciarAcessorios()
{
	var seq = 1;
	$("#acessorios").children("div").each(function(){
		if (typeof $(this).attr("id") === "undefined")
		{
			$(this).children("span:nth-of-type(1)").html('Acessório '+seq);
			$(this).children("span:nth-of-type(2)").html('Valor '+seq+' (R$)');
			seq++;
		}
	});
}

function dropEstado(n)
{
	if (!ajax || $("#drop-estado-"+n).attr("data-mode") == "readonly")
		return false;

	if ($("#drop-estado-"+n).hasClass("drop"))
	{
		closeDropBox();
	}
	else
	{
		for (i=1; i<5; i++)
		{
			if (i != n)
			{
				$("#drop-estado-"+i).removeClass("drop");
				$("#drop-estado-"+i).children("img").attr("src", "img/a-down-w.png");
			}
		}

		$("#drop-estado-"+n).addClass("drop");
		$("#drop-estado-"+n).children("img").attr("src", "img/a-up-w.png");
		$("#drop-box").css("top", $("#drop-estado-"+n).offset().top + 30);
		$("#drop-box").html('<span class="lh-20 italic" style="padding:0 10px;">Carregando...</span>');

		var lf = Math.round($("#drop-estado-"+n).offset().left + $("#drop-estado-"+n).outerWidth() - $("#drop-box").outerWidth());
		$("#drop-box").css("left", lf+"px");

		$("#drop-box").show();


		if (n < 4)
			var dataString = "d="+n+"&v="+$("#drop-estado-"+n).children("span").html();
		else
			var dataString = "d="+n+"&v="+$('input[name="f-garantia-prazo-item"]').val();

		ajax = false;
		$.ajax({
			type: "get",
			url: "nova_apl_drop_box.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				$("#drop-box").html(data[1]);
				var lf = Math.round($("#drop-estado-"+n).offset().left + $("#drop-estado-"+n).outerWidth() - $("#drop-box").outerWidth());
				$("#drop-box").css("left", lf+"px");
				ajax = true;
				if (data[2] > 14)
					$("#drop-box").scrollTop(5000);
			}
		});
	}
}

function closeDropBox()
{
	$("#drop-box").hide();
	for (i=1; i<5; i++)
	{
		$("#drop-estado-"+i).removeClass("drop");
		$("#drop-estado-"+i).children("img").attr("src", "img/a-down-w.png");
	}
}

function selDbItem(ob, v, n)
{
	$(".dbx").attr("class", "drop-box-item0 dbx");
	$(ob).removeClass("drop-box-item0").addClass("drop-box-item1");
	$("#drop-estado-"+n).children("span").html(v);

	if (n == 4) //prazo garantia
	{
		$('input[name="f-garantia-prazo-item"]').val($(ob).attr("data-value"));
		if ($(ob).attr("data-value") == 4) //outro
		{
			$("#i-garantia-prazo-drop-item").hide();
			$("#i-garantia-prazo-outro-item").show();
			$("#i-garantia-prazo-x-item").show();
			$("#i-garantia-prazo-outro-item").focus();
		}
		else
		{
			$("#i-garantia-prazo-outro-item").hide();
			$("#i-garantia-prazo-x-item").hide();
			$("#i-garantia-prazo-drop-item").show();
		}
	}

	$("#drop-estado-"+n).removeClass("drop");
	$("#drop-estado-"+n).children("img").attr("src", "img/a-down-w.png");
	$("#drop-box").hide();
}

function hidePGed()
{
	$("#i-garantia-prazo-outro-item").hide();
	$("#i-garantia-prazo-x-item").hide();
	$("#i-garantia-prazo-drop-item").show();
	$('input[name="f-garantia-prazo-item"]').val(0);
	$("#drop-estado-4").children("span").html("");
}

//-------- file upload APL Check List Transformacao AMAROK ----------
function selectFileAPL_AMAROK()
{
	if (!$("#upload-form-apl-amarok").length)
		$('body').append('<form id="upload-form-apl-amarok" enctype="multipart/form-data"></form>');

	$("#upload-form-apl-amarok").html('<input id="upload-form-button-apl-amarok" class="file-upload" type="file" name="f-upload" onchange="uploadFileAPL_AMAROK();">');
	$("#upload-form-button-apl-amarok").click();
}

function uploadFileAPL_AMAROK()
{
	var file = $("#upload-form-button-apl-amarok")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload", $("#upload-form-button-apl-amarok")[0].files[0]);

			xhr.upload.addEventListener("progress", uploadProgressAPL_AMAROK, false);
			xhr.addEventListener("load", uploadCompleteAPL_AMAROK, false);
			xhr.addEventListener('readystatechange', function()
			{
				if (this.readyState == 4)
					rtext_apl_amarok_upload = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "nova_apl_upload_amarok.php");
			xhr.send(fd);

			$("#cl-amarok").children(".apl-upl-loading").children(".bar").css("width", 0);
			$("#cl-amarok").children(".apl-upl-loading").children(".text").html("Carregando...");
			$("#cl-amarok").children(".apl-upl-box").hide();
			$("#cl-amarok").children(".apl-upl-loading").show();
		}
		else
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O arquivo selecionado é muito grande. Tamanho máximo permitido: 100 MB.',buttons:{'ok':{is_default:1}}});
		}
	}
}

function uploadProgressAPL_AMAROK(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var w = Math.round(854 * percentComplete / 100);
		$("#cl-amarok").children(".apl-upl-loading").children(".bar").css("width", w+'px');
		$("#cl-amarok").children(".apl-upl-loading").children(".text").html('Carregando... '+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#cl-amarok").children(".apl-upl-loading").children(".bar").css("width", '854px');
	}
}

function uploadCompleteAPL_AMAROK()
{
	if (rtext_apl_amarok_upload.status == 0)
	{
		//upload error
		$("#cl-amarok").children(".apl-upl-ready").hide();
		$("#cl-amarok").children(".apl-upl-loading").hide();
		$("#cl-amarok").children(".apl-upl-box").show();
	}
	else
	{
		//upload success
		if (rtext_apl_amarok.status == 0)
			rtext_apl_amarok.status = 1;
		else if (rtext_apl_amarok.status == 3)
			rtext_apl_amarok.status = 4;

		rtext_apl_amarok.long_filename = rtext_apl_amarok_upload.long_filename;
		rtext_apl_amarok.short_filename = rtext_apl_amarok_upload.short_filename;
		rtext_apl_amarok.file_size = rtext_apl_amarok_upload.file_size;
		rtext_apl_amarok.file = rtext_apl_amarok_upload.file;
		rtext_apl_amarok_upload = {};

		$("#cl-amarok").children(".apl-upl-loading").hide();
		$("#cl-amarok").children(".apl-upl-ready").children("span").html('Anexo: <span>'+rtext_apl_amarok.short_filename+'</span> ('+rtext_apl_amarok.file_size+')');
		$("#cl-amarok").children(".apl-upl-ready").show();
	}
}

function cancelUploadAPL_AMAROK()
{
	if (rtext_apl_amarok.status == 1)
		rtext_apl_amarok.status = 0;
	else if (rtext_apl_amarok.status == 2 || rtext_apl_amarok.status == 4)
		rtext_apl_amarok.status = 3;

	rtext_apl_amarok.long_filename = "";
	rtext_apl_amarok.short_filename = "";
	rtext_apl_amarok.file_size = "";
	rtext_apl_amarok.file = "";
	rtext_apl_amarok_upload = {};

	$("#cl-amarok").children(".apl-upl-ready").hide();
	$("#cl-amarok").children(".apl-upl-loading").hide();
	$("#cl-amarok").children(".apl-upl-box").show();
}
//-------- END file upload APL Check List Transformacao AMAROK ----------


//-------- file upload APL Check List Transformacao SPACEFOX ----------
function selectFileAPL_SPACEFOX()
{
	if (!$("#upload-form-apl-spacefox").length)
		$('body').append('<form id="upload-form-apl-spacefox" enctype="multipart/form-data"></form>');

	$("#upload-form-apl-spacefox").html('<input id="upload-form-button-apl-spacefox" class="file-upload" type="file" name="f-upload" onchange="uploadFileAPL_SPACEFOX();">');
	$("#upload-form-button-apl-spacefox").click();
}

function uploadFileAPL_SPACEFOX()
{
	var file = $("#upload-form-button-apl-spacefox")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload", $("#upload-form-button-apl-spacefox")[0].files[0]);

			xhr.upload.addEventListener("progress", uploadProgressAPL_SPACEFOX, false);
			xhr.addEventListener("load", uploadCompleteAPL_SPACEFOX, false);
			xhr.addEventListener('readystatechange', function()
			{
				if (this.readyState == 4)
					rtext_apl_spacefox_upload = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "nova_apl_upload_spacefox.php");
			xhr.send(fd);

			$("#cl-spacefox").children(".apl-upl-loading").children(".bar").css("width", 0);
			$("#cl-spacefox").children(".apl-upl-loading").children(".text").html("Carregando...");
			$("#cl-spacefox").children(".apl-upl-box").hide();
			$("#cl-spacefox").children(".apl-upl-loading").show();
		}
		else
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O arquivo selecionado é muito grande. Tamanho máximo permitido: 100 MB.',buttons:{'ok':{is_default:1}}});
		}
	}
}

function uploadProgressAPL_SPACEFOX(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var w = Math.round(854 * percentComplete / 100);
		$("#cl-spacefox").children(".apl-upl-loading").children(".bar").css("width", w+'px');
		$("#cl-spacefox").children(".apl-upl-loading").children(".text").html('Carregando... '+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#cl-spacefox").children(".apl-upl-loading").children(".bar").css("width", '854px');
	}
}

function uploadCompleteAPL_SPACEFOX()
{
	if (rtext_apl_spacefox_upload.status == 0)
	{
		//upload error
		$("#cl-spacefox").children(".apl-upl-ready").hide();
		$("#cl-spacefox").children(".apl-upl-loading").hide();
		$("#cl-spacefox").children(".apl-upl-box").show();
	}
	else
	{
		//upload success
		if (rtext_apl_spacefox.status == 0)
			rtext_apl_spacefox.status = 1;
		else if (rtext_apl_spacefox.status == 3)
			rtext_apl_spacefox.status = 4;

		rtext_apl_spacefox.long_filename = rtext_apl_spacefox_upload.long_filename;
		rtext_apl_spacefox.short_filename = rtext_apl_spacefox_upload.short_filename;
		rtext_apl_spacefox.file_size = rtext_apl_spacefox_upload.file_size;
		rtext_apl_spacefox.file = rtext_apl_spacefox_upload.file;
		rtext_apl_spacefox_upload = {};

		$("#cl-spacefox").children(".apl-upl-loading").hide();
		$("#cl-spacefox").children(".apl-upl-ready").children("span").html('Anexo: <span>'+rtext_apl_spacefox.short_filename+'</span> ('+rtext_apl_spacefox.file_size+')');
		$("#cl-spacefox").children(".apl-upl-ready").show();
	}
}

function cancelUploadAPL_SPACEFOX()
{
	if (rtext_apl_spacefox.status == 1)
		rtext_apl_spacefox.status = 0;
	else if (rtext_apl_spacefox.status == 2 || rtext_apl_spacefox.status == 4)
		rtext_apl_spacefox.status = 3;

	rtext_apl_spacefox.long_filename = "";
	rtext_apl_spacefox.short_filename = "";
	rtext_apl_spacefox.file_size = "";
	rtext_apl_spacefox.file = "";
	rtext_apl_spacefox_upload = {};

	$("#cl-spacefox").children(".apl-upl-ready").hide();
	$("#cl-spacefox").children(".apl-upl-loading").hide();
	$("#cl-spacefox").children(".apl-upl-box").show();
}
//-------- END file upload APL Check List Transformacao SPACEFOX ----------

