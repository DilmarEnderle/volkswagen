var rtext = '---{^=?!?=^}0';
var rtext_apr = JSON.parse('["0","","",""]');

$(document).ready(function(){
	atualizarHistorico(true);
});

//-------- file upload MSG ----------
function selectFileMSG()
{
	$("#upload-form").html('<input id="upload-button" class="file-upload" type="file" name="f-upload" onchange="uploadFileMSG();">');
	$("#upload-button").click();
}

function uploadFileMSG()
{
	var file = $("#upload-button")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload", $("#upload-button")[0].files[0]);
			xhr.upload.addEventListener("progress", uploadProgressMSG, false);
			xhr.addEventListener("load", uploadCompleteMSG, false);
			xhr.addEventListener('readystatechange', function()
			{
				if (this.readyState == 4)
					rtext = xhr.responseText;
			});
			xhr.open("POST", "c.upload_compradir_mensagem.php");
			xhr.send(fd);
			$("#upl-btn").hide();
			$("#upl-bar").css("width", 0);
			$("#upl-per").html('Carregando...');
			$("#upl-loading").show();
		}
		else
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O arquivo selecionado é muito grande.<br><br>Tamanho máximo permitido: <span class="bold">100 MB</span>',buttons:{'ok':{is_default:1}}});
		}
	}
}

function uploadProgressMSG(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var b = Math.round(1178 * percentComplete / 100);
		$("#upl-bar").css("width", b+'px');
		$("#upl-per").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upl-bar").css("width", "1178px");
	}
}

function uploadCompleteMSG()
{
	if (rtext.split('{^=?!?=^}')[0] == 'ERRO')
		cancelUploadMSG();
	else
	{
		$("#upl-loading").hide();
		$("#upl-filename").html(rtext.split('{^=?!?=^}')[0]);
		$("#upl-filesize").html('('+rtext.split('{^=?!?=^}')[1]+')');
		$("#upl-ready").show();
	}
}

function cancelUploadMSG()
{
	rtext = '---{^=?!?=^}0';
	$("#upl-ready").hide();
	$("#upl-loading").hide();
	$("#upl-btn").show();
}
//------- end upload MSG -----------

function enviarMensagem()
{
	$("#i-mensagem").val($.trim($("#i-mensagem").val()));	
	if ($("#i-mensagem").val().length == 0 && rtext.split('{^=?!?=^}')[0] == '---')
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite a mensagem ou anexe um arquivo antes de enviar.',buttons:{'Ok':{is_default:1,onclick:"foc('i-mensagem');ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		ajax = false;
		$("#enviar-box").hide();
		$("#processando-box").show();
		var dataString = "id-cd="+$("#id-cd").val()+"&mensagem="+encodeURIComponent($("#i-mensagem").val())+"&anexo="+rtext.split('{^=?!?=^}')[0];
		$.ajax({
			type: "POST",
			url: "c.compradir_envia_mensagem.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				if (data[0] == 9)
				{
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					$("#enviar-box").show();
					$("#processando-box").hide();
					ajax = true;
				}
				else
				{
					atualizarHistorico(false);
				}
			}
		});
	}
}

function atualizarHistorico(inicial)
{
	if (inicial)
		$("#historico").html('<span class="italic">Carregando mensagens...</span>');

	$.ajax({
		type:"post",
		url:"c.compradir_historico.php",
		data:"id-cd="+$("#id-cd").val(),
		dataType:"json",
		success:function(data)
		{
			$("#historico").html(data[1]);
			if (!inicial)
			{
				cancelUploadMSG();
				$("#i-mensagem").val("");
				$("#enviar-box").show();
				$("#processando-box").hide();
			}

			ajax = true;
		}
	});
}

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

function cdAPL(id_cd)
{
	if ($("#apl").html().length > 0)
	{
		$("#apl").html("");
	}
	else
	{
		$("#apl").html('<div class="gray-88 italic" style="text-align:center; line-height:30px;">Carregando APL... Aguarde...</div>');
		$.ajax({
			type:"post",
			url:"c.compradir_apl.php",
			data:"id-cd="+id_cd,
			dataType:"json",
			success:function(data)
			{
				$("#apl").html(data[1]);
				$("#i-doc-cpf").inputmask("999.999.999-99");

				$('#form-apl-cd input[name^="f-valor"]').each(function(){
					$(this).maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
				});

				$("#i-preco-publico").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
				$("#i-preco-maximo").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
				$("#i-repasse").inputmask({ "mask": "9", "repeat": 3, "greedy": false });
				$("#i-quantidade").inputmask({ "mask": "9", "repeat": 4, "greedy": false });
				$("#i-prazo-entrega").inputmask({ "mask": "9", "repeat": 3, "greedy": false });
				$("#i-nome-orgao").focus();
			}
		});
	}
}

function cdAPL_salvar(id_cd, now)
{
	$("#i-nome-orgao").val($.trim($("#i-nome-orgao").val()));
	$("#i-numero-pregao").val($.trim($("#i-numero-pregao").val()));
	$("#i-doc-nome").val($.trim($("#i-doc-nome").val()));
	$("#i-doc-rg").val($.trim($("#i-doc-rg").val()));
	$("#i-doc-outros").val($.trim($("#i-doc-outros").val()));
	$("#i-model-code").val($.trim($("#i-model-code").val()));
	$("#i-cor").val($.trim($("#i-cor").val()));
	$("#i-pr").val($.trim($("#i-pr").val()));
	$("#i-motorizacao").val($.trim($("#i-motorizacao").val()));
	$("#i-potencia").val($.trim($("#i-potencia").val()));
	$("#i-combustivel").val($.trim($("#i-combustivel").val()));
	$("#i-detalhamento-transformacao").val($.trim($("#i-detalhamento-transformacao").val()));

	$('#form-apl-cd input[name^="f-acessorio"]').each(function(){
		$(this).val($.trim($(this).val()));
	});

	$("#i-desconto").val($.trim($("#i-desconto").val()));
	$("#i-dnvenda").val($.trim($("#i-dnvenda").val()));
	$("#i-dnentrega").val($.trim($("#i-dnentrega").val()));
	$("#i-prazo-pagamento").val($.trim($("#i-prazo-pagamento").val()));
	$("#i-ave").val($.trim($("#i-ave").val()));
	$("#i-numero-pool").val($.trim($("#i-numero-pool").val()));
	$("#i-observacoes").val($.trim($("#i-observacoes").val()));

	if ($("#i-nome-orgao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do orgão.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome-orgao');ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-mdl"]').val() == 2 && $("#i-numero-pregao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o Nº do Pregão.',buttons:{'ok':{is_default:1,onclick:"foc('i-numero-pregao');ultimateClose();"}}});
		return false;
	}

	if ($("#i-doc-nome").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do participante.',buttons:{'ok':{is_default:1,onclick:"foc('i-doc-nome');ultimateClose();"}}});
		return false;
	}

	if ($("#i-doc-rg").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o RG do participante.',buttons:{'ok':{is_default:1,onclick:"foc('i-doc-rg');ultimateClose();"}}});
		return false;
	}

	if (!isValidCPF($("#i-doc-cpf").val()))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'O número CPF do participante está incorreto.',buttons:{'ok':{is_default:1,onclick:"foc('i-doc-cpf');ultimateClose();"}}});
		return false;
	}

	if ($("#i-model-code").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o model code.',buttons:{'ok':{is_default:1,onclick:"foc('i-model-code');ultimateClose();"}}});
		return false;
	}

	if ($("#i-cor").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a cor.',buttons:{'ok':{is_default:1,onclick:"foc('i-cor');ultimateClose();"}}});
		return false;
	}

	if ($("#i-motorizacao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a motorização.',buttons:{'ok':{is_default:1,onclick:"foc('i-motorizacao');ultimateClose();"}}});
		return false;
	}

	if ($("#i-potencia").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a potência.',buttons:{'ok':{is_default:1,onclick:"foc('i-potencia');ultimateClose();"}}});

		return false;
	}

	if ($("#i-combustivel").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o combustível.',buttons:{'ok':{is_default:1,onclick:"foc('i-combustivel');ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-trans"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione transformação.',buttons:{'ok':{is_default:1,onclick:"foc('i-combustivel');ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-gar"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a garantia.',buttons:{'ok':{is_default:1,onclick:"foc('i-combustivel');ultimateClose();"}}});
		return false;
	}

	if ($("#i-preco-publico").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o preço público.',buttons:{'ok':{is_default:1,onclick:"foc('i-preco-publico');ultimateClose();"}}});
		return false;
	}

	if ($("#i-dnvenda").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o DN de venda.',buttons:{'ok':{is_default:1,onclick:"foc('i-dnvenda');ultimateClose();"}}});
		return false;
	}

	if ($("#i-validade-proposta").val().length < 2)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a validade da proposta.',buttons:{'ok':{is_default:1,onclick:"foc('i-validade-proposta');ultimateClose();"}}});
		return false;
	}

	if ($("#i-dnentrega").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o DN de entrega.',buttons:{'ok':{is_default:1,onclick:"foc('i-dnentrega');ultimateClose();"}}});
		return false;
	}

	if ($("#i-prazo-entrega").val().length == 0 || $("#i-prazo-entrega").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o prazo de entrega.',buttons:{'ok':{is_default:1,onclick:"foc('i-prazo-entrega');ultimateClose();"}}});
		return false;
	}

	if ($("#i-quantidade").val().length == 0 || $("#i-quantidade").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a quantidade.',buttons:{'ok':{is_default:1,onclick:"foc('i-quantidade');ultimateClose();"}}});
		return false;
	}

	if ($("#i-ave").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a AVE.',buttons:{'ok':{is_default:1,onclick:"foc('i-ave');ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-verba"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a origem da verba.',buttons:{'ok':{is_default:1,onclick:"foc('i-numero-pool');ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-imp"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione isenção de impostos do orgão.',buttons:{'ok':{is_default:1,onclick:"foc('i-numero-pool');ultimateClose();"}}});
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
			ajax = false;
			ultimateLoader(true, 'Enviando APL...');
			$.ajax({
				type:"post",
				url:"c.compradir_apl_salvar.php",
				data:"id-cd="+id_cd+"&"+$("#form-apl-cd").serialize(),
				dataType:"json",
				success:function(data)
				{
					ultimateLoader(false, '');
					ajax = true;
					if (data[0] == 20)
					{
						ultimateDialog({
							title:data[1],
							width:data[2],
							color:data[3],
							content:data[4],
							buttons:{'Ok':{is_default:1}}
						});
					}
					else if (data[0] == 9)
					{
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					}
					else if (data[0] == 1)
					{
						$("#apl-btn").attr("class", "bt-apl-style-1");
						$("#apl-btn").attr("title", "Aguardando aprovação");
						$("#apl-btn").html("APL &#x21e3;");
						$("#apl-btn").css("width","100px");
						$("#apl").html("");
						$(".cd-info span").last().remove();
						$(".cd-info").append('<span class="fl t-orange bold">APL Enviada. Aguardando aprovação.</span>');
						atualizarHistorico(false);
					}
					else
					{
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
					}
				}
			});
		}
	}
	else
	{
		ajax = false;
		ultimateLoader(true, '');
		$.ajax({
			type:"post",
			url:"c.compradir_apl_termos.php",
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
					buttons:{'Continuar':{onclick:'cdAPL_salvar('+id_cd+',true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
				});
			}
		});
	}
}

function adicionarAcessorio()
{
	$("#aaBtn-row").before('<div class="apl-row apl-br apl-bb apl-bl"><span class="apl-lb w-100 bg-3 center">Acessório ?</span><input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000"><span class="apl-lb w-100 bg-3 center">Valor ? (R$)</span><input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;"><a class="rm-acess" href="javascript:void(0);" onclick="removerAcessorio(this);" title="Remover Acessório"></a></div>');
	$('#form-apl-cd input[name^="f-valor"]').each(function(){
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

function cdAPL_aprovar(id_cd, now)
{
	if (now)
	{
		if (ajax)
		{
			$("#ultimate-error").hide();
			$("#i-apr-ave").val($.trim($("#i-apr-ave").val()));
			$("#i-apr-model").val($.trim($("#i-apr-model").val()));
			$("#i-apr-cor").val($.trim($("#i-apr-cor").val()));
			$("#i-apr-opcionais").val($.trim($("#i-apr-opcionais").val()));

			if ($("#i-apr-ave").val().length == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe a AVE.</p>');
				$("#i-apr-ave").focus();
				return false;
			}

			if ($("#i-apr-quantidade").val().length == 0 || $("#i-apr-quantidade").val() == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe a quantidade.</p>');
				$("#i-apr-quantidade").focus();
				return false;
			}

			if ($("#i-apr-model").val().length == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe o model code.</p>');
				$("#i-apr-model").focus();
				return false;
			}

			if ($("#i-apr-cor").val().length == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe a cor.</p>');
				$("#i-apr-cor").focus();
				return false;
			}

			if ($("#i-apr-preco-publico").val().length == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe o preço público.</p>');
				$("#i-apr-preco-publico").focus();
				return false;
			}
			
			if ($("#i-apr-planta").val() < 1)
			{
				ultimateError('<p><span class="bold">Erro:</span> Selecione a planta de participação.</p>');
				$("#i-apr-planta").focus();
				return false;
			}

			if ($("#i-apr-prazo-entrega").val().length == 0 || $("#i-apr-prazo-entrega").val() == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe o prazo de entrega.</p>');
				$("#i-apr-prazo-entrega").focus();
				return false;
			}

			if ($("#i-apr-valor-transf").is(":visible") && $("#i-apr-valor-transf").val().length == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe o valor da transformação.</p>');
				$("#i-apr-valor-transf").focus();
				return false;
			}
		

			ajax = false;
			$("#ultimate-default-btn").hide();
			$(".ultimate-buttons").prepend('<span id="processing" class="italic t-red">Processando APL...</span>');
			dataString = $("#apr-form").serialize()+"&id-cd="+id_cd+"&anexo="+encodeURIComponent(rtext_apr[1]);

			$.ajax({
				type: "post",
				url: "c.compradir_apl_aprovar.php",
				data: dataString,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
					{
						ultimateError('<p><span class="bold">Acesso Restrito!</span> Você não tem permissão para executar esta operação.</p>');
						$("#ultimate-default-btn").show();
						$("#processing").remove();
					}
					else if (data[0] == 1)
					{
						$("#apl-btn").attr("class", "bt-apl-style-2");
						$("#apl-btn").attr("title", "Aprovada");
						$("#apl").html("");
						$(".cd-info span").last().remove();
						$(".cd-info").append('<span class="fl t-green bold">APL Aprovada.</span>');
						atualizarHistorico(false);
						ultimateClose();
					}
					else
					{
						ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
						$("#ultimate-default-btn").show();
						$("#processing").remove();
					}
				}
			});
		}
	}
	else
	{
		if (ajax)
		{
			ajax = false;
			ultimateLoader(true, '');
			$.ajax({
				type: "post",
				url: "c.compradir_apl_aprovar_form.php",
				data: "id-cd="+id_cd,
				dataType: "json",
				success: function(data)
				{
					ultimateLoader(false, '');
					ajax = true;
					if (data[0] == 1)
					{
						ultimateDialog({
							title:'Aprovar APL',
							color:'green',
							content:data[1],
							width:data[2],
							buttons:{'Aprovar APL':{onclick:'cdAPL_aprovar('+id_cd+', true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
						},
						function(){
							$("#i-apr-quantidade").inputmask({"mask":"9","repeat":4,"greedy":false});
							$("#i-apr-preco-publico").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
							$("#i-apr-desconto-vw").maskMoney({suffix:' %', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
							$("#i-apr-comissao-dn").maskMoney({suffix:' %', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
							$("#i-apr-prazo-entrega").inputmask({"mask":"9","repeat":3,"greedy":false});
							$("#i-apr-valor-transf").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
						});
					}
				}
			});
		}
	}
}

//-------- file upload APL apr ----------
function selectFileAPR()
{
	$("#ultimate-error").hide();
	$("#upload-form-apr").html('<input id="upload-button-apr" class="file-upload" type="file" name="f-upload-apr" onchange="uploadFileAPR();">');
	$("#upload-button-apr").click();
}

function uploadFileAPR()
{
	var file = $("#upload-button-apr")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload-apr", $("#upload-button-apr")[0].files[0]);

			xhr.upload.addEventListener("progress", uploadProgressAPR, false);
			xhr.addEventListener("load", uploadCompleteAPR, false);
			xhr.addEventListener('readystatechange', function()
			{
				if (this.readyState == 4)
					rtext_apr = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "c.upload_compradir_apr.php");
			xhr.send(fd);
			$("#apr-upl-btn").hide();
			$("#apr-upl-loading").children(".bar").css("width", 0);
			$("#apr-upl-loading").children(".per").html('Carregando...');
			$("#apr-upl-loading").show();
		}
		else
		{
			ultimateError('<p><span class="bold">Erro:</span> O arquivo selecionado é muito grande. Tamanho máximo permitido: 100 MB</p>');
		}
	}
}

function uploadProgressAPR(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var b = Math.round(($("#apl-upl-ready").width() - 2) * percentComplete / 100);
		$("#apr-upl-loading").children(".bar").css("width", b+'px');
		$("#apr-upl-loading").children(".per").html('Carregando...'+parseInt(percentComplete)+'%');

	}
	else
	{
		$("#apr-upl-loading").children(".bar").css("width", ($("#apl-upl-ready").width()-2)+'px');
	}
}

function uploadCompleteAPR()
{
	if (rtext_apr[0] == "0")
		cancelUploadAPR();
	else
	{
		$("#apr-upl-loading").hide();
		$("#apr-upl-ready").children(".fname").html(rtext_apr[2]);
		$("#apr-upl-ready").children(".fsize").html("("+rtext_apr[3]+")");
		$("#apr-upl-ready").show();
	}
}

function cancelUploadAPR()
{
	rtext = JSON.parse('["0","","",""]');
	$("#apr-upl-ready").hide();
	$("#apr-upl-loading").hide();
	$("#apr-upl-btn").show();
}
//------- end upload APL apr -----------

function listarSubmotivosAPL()
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "c.licitacao_item_apl_submotivos.php",
			data: "id="+$("#i-motivo-declinar-apl").val(),
			dataType: "text",
			success: function(data){
				ajax = true;
				if (data.length > 0)
				{
					$("#i-submotivo-declinar-apl").html(data);
					$(".submotivo-declinar-apl").show();
				}
				else
				{
					$(".submotivo-declinar-apl").hide();
				}
			}
		});
	}
}

function cdAPL_reprovar(id_cd, now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			if ($("#i-motivo-declinar-apl").val() == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Escolha um motivo de reprovação.</p>');
				$("#i-motivo-declinar-apl").focus();
				return false;
			}

			ajax = false;
			$.ajax({
				type: "post",
				url: "c.compradir_apl_reprovar.php",
				data: "id-cd="+id_cd+"&motivo="+$("#i-motivo-declinar-apl").val()+"&submotivo="+$("#i-submotivo-declinar-apl").val()+"&observacoes="+encodeURIComponent($("#i-obs").val()),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
					{
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					}
					else if (data[0] == 1)
					{
						$("#apl-btn").attr("class", "bt-apl-style-4");
						$("#apl-btn").attr("title", "Reprovada");
						$("#apl").html("");
						$(".cd-info span").last().remove();
						$(".cd-info").append('<span class="fl t-red bold">APL Reprovada.</span>');
						atualizarHistorico(false);
						ultimateClose();
					}
					else
					{
						ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
					}
				}
			});
		}
	}
	else
	{
		if (ajax)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "POST",
				url: "c.compradir_apl_reprovar_dados.php",
				data: "f-id-licitacao="+$("#id-licitacao").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');

					if (data[0] == 9)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({
						title:'Reprovar APL',
						width:600,
						content:data[1],
						buttons:{'Reprovar APL':{is_default:1,onclick:'cdAPL_reprovar('+id_cd+',true);',css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}});
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function cdAPL_reverterAprovacao(id_apl, now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			ajax = false;
			$.ajax({
				type: "post",
				url: "c.compradir_apl_reverter_a.php",
				data: "id-apl="+id_apl+"&observacoes="+encodeURIComponent($("#i-obs").val()),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
					{
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					}
					else if (data[0] == 1)
					{
						$("#apl-btn").attr("class", "bt-apl-style-1");
						$("#apl-btn").attr("title", "Aguardando aprovação");
						$("#apl").html("");
						$(".cd-info span").last().remove();
						$(".cd-info").append('<span class="fl t-orange bold">APL Enviada. Aguardando aprovação.</span>');
						atualizarHistorico(false);
						ultimateClose();
					}
					else
					{
						ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
					}
				}
			});
		}
	}
	else
	{
		if (ajax)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "POST",
				url: "c.compradir_apl_reverter_a_info.php",
				data: "id-apl="+id_apl,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');

					if (data[0] == 9)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({
							title:'Reverter Aprovação da APL ?',
							width:600,
							content:data[1],
							buttons:{'Reverter':{is_default:1,onclick:'cdAPL_reverterAprovacao('+id_apl+',true);',css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
						});
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function cdAPL_reverterReprovacao(id_apl, now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			ajax = false;
			$.ajax({
				type: "post",
				url: "c.compradir_apl_reverter_r.php",
				data: "id-apl="+id_apl+"&observacoes="+encodeURIComponent($("#i-obs").val()),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
					{
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					}
					else if (data[0] == 1)
					{
						$("#apl-btn").attr("class", "bt-apl-style-1");
						$("#apl-btn").attr("title", "Aguardando aprovação");
						$("#apl").html("");
						$(".cd-info span").last().remove();
						$(".cd-info").append('<span class="fl t-orange bold">APL Enviada. Aguardando aprovação.</span>');
						atualizarHistorico(false);
						ultimateClose();
					}
					else
					{
						ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
					}
				}
			});
		}
	}
	else
	{
		if (ajax)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "POST",
				url: "c.compradir_apl_reverter_r_info.php",
				data: "id-apl="+id_apl,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');

					if (data[0] == 9)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({
							title:'Reverter Reprovação da APL ?',
							width:600,
							content:data[1],
							buttons:{'Reverter':{is_default:1,onclick:'cdAPL_reverterReprovacao('+id_apl+',true);',css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
						});
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function cdAPL_historico(id_cd)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "c.compradir_apl_historico.php",
			data: "id-cd="+id_cd,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');

				if (data[0] == 9)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					ultimateDialog({
						title:'Histórico de Aprovações/Reprovações da APL',
						width:900,
						content:data[1],
						buttons:{'Fechar':{is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'}}
					});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function verMais(id_his)
{
	if ($("#his-"+id_his).is(":visible"))
		$("#his-"+id_his).hide();
	else
	{
		$(".his-hidden").hide();
		$("#his-"+id_his).show();
	}
}
