var return_upload_msg = {status:0};
var rtext_apr = JSON.parse('["0","","",""]'); 
var rtext_apl_amarok = {};
var rtext_apl_amarok_upload = {};
var sBusy = false;
var scroll_width = 0;
var inv = [
'012345',
'123456',
'234567',
'345678',
'456789',
'567890',
'000000',
'111111',
'222222',
'333333',
'444444',
'555555',
'666666',
'777777',
'888888',
'999999',
'000001',
'000002',
'000003',
'000004',
'000005',
'000006',
'000007',
'000008',
'000009'
]

$(document).ready(function(){

	$(document).on("click", ".sbl", function(){ scrollLeft(); });
	$(document).on("click", ".sbr", function(){ scrollRight(); });

	$(document).on("click", ".tab", function()
	{
		if (ajax)
		{
			$(".tab").removeClass("tab-active");
			$(this).addClass("tab-active");
			id_lote = parseInt($(this).parent().attr("id").split("-")[1]);
			id_item = parseInt($(this).attr("id").split("-")[1]);
			$("#itens-info").html('<div style="height:215px; text-align: center;"><img src="img/loader_32.gif" style="margin-top:90px"></div>');
			$("#itens-info").show();
			ajax = false;
			$.ajax({
				type: "post",
				url: "c.licitacao_item_item.php",
				data: "id-licitacao="+$("#id-licitacao").val()+"&id-lote="+id_lote+"&id-item="+id_item,
				dataType: "json",
				success: function(data){
					$("#itens-info").html(data[1]);
					ajax = true;
				}
			});
		}
	});

	$(document).on('keydown', function(e){
		if (e.keyCode == 39 && e.target.tagName.toLowerCase() != 'input')
			scrollRight();
		else if (e.keyCode == 37 && e.target.tagName.toLowerCase() != 'input')
			scrollLeft();
	});

	$(document).on("keyup", "#i_search", function(e){
		if (e.keyCode == 13)
			buscar();
	});

	$(document).click(function(e){
		if (!$(e.target).hasClass("drp") && !$(e.target).parent().hasClass("drp"))
		{
			dropEditaisClose();
			closeAdvSearch();
			closeDropBox();
		}
	});

	$(document).keydown(function(e) {
		if (e.keyCode == 27)
		{
			dropEditaisClose();
			closeAdvSearch();
			closeDropBox();
		}
	});

	$("#i-da-fr").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#i-da-to").inputmask("d/m/y", {"placeholder":"__/__/____"});

	dhtmlXCalendarObject.prototype.langData["pt"] = {
		dateformat: '%d/%m/%Y',
		monthesFNames: ["Janeiro","Fereveiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
		monthesSNames: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
		daysFNames: ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
		daysSNames: ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],
		weekstart: 7
	};

	itemAbas();
	atualizarHistorico(true);
	atualizarStatus();

	var myCalendar = new dhtmlXCalendarObject(["i-da-fr","i-da-to"]);
	myCalendar.loadUserLanguage("pt");
	myCalendar.hideTime();
	myCalendar.showToday();
	myCalendar.setSkin("dhx_terrace");

	$.fn.goTo = function() {
		$('html, body').animate({
			scrollTop: ($(this).offset().top - 16) + 'px'
		}, 'fast');
		return this; // for chaining...
	}
});

function objetoToggle(ob)
{
	if ($(".obj-conteudo").is(":visible"))
	{
		$(ob).attr("class","exp-btn0");
		$(".obj-conteudo").hide();
	}
	else
	{
		$(ob).attr("class","exp-btn1");
		$(".obj-conteudo").show();
	}
}

function importanteToggle(ob)
{
	if ($(".imp-conteudo").is(":visible"))
	{
		$(ob).attr("class","exp-btnY0");
		$(".imp-conteudo").hide();
	}
	else
	{
		$(ob).attr("class","exp-btnY1");
		$(".imp-conteudo").show();
	}
}

function itensToggle(ob)
{
	if ($(".itens-conteudo").is(":visible"))
	{
		$(ob).attr("class","exp-btn0");
		$(".itens-conteudo").hide();
	}
	else
	{
		$(ob).attr("class","exp-btn1");
		$(".itens-conteudo").show();
	}
}

function aplToggle(ob)
{
	if ($(".apl-conteudo").is(":visible"))
	{
		$(ob).attr("class","exp-btn0");
		$(".apl-conteudo").hide();
	}
	else
	{
		$(ob).attr("class","exp-btn1");
		$(".apl-conteudo").show();
	}
}

function atualizarHistorico(inicial)
{
	if (inicial)
		$("#historico").html('<span class="italic">Carregando mensagens...</span>');

	$.ajax({
		type:"post",
		url:"c.licitacao_historico.php",
		data:"id_licitacao="+$("#id-licitacao").val(),
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
			if (data[2] > 0)
				$("#alerta-documento").show();
			else
				$("#alerta-documento").hide();

			ajax = true;
		}
	});
}

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
					return_upload_msg = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "c.upload_mensagem.php");
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
		var b = Math.round(918 * percentComplete / 100);
		$("#upl-bar").css("width", b+'px');
		$("#upl-per").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upl-bar").css("width", "918px");
	}
}

function uploadCompleteMSG()
{
	if (return_upload_msg.status == 0)
		cancelUploadMSG();
	else
	{
		$("#upl-loading").hide();
		$("#upl-filename").html(return_upload_msg.short_filename);
		$("#upl-filesize").html('('+return_upload_msg.file_size+')');
		$("#upl-ready").show();
	}
}

function cancelUploadMSG()
{
	return_upload_msg = {status:0};
	$("#upl-ready").hide();
	$("#upl-loading").hide();
	$("#upl-btn").show();
}
//------- end upload MSG -----------


function enviarMensagem()
{
	ultimateClose();
	$("#i-mensagem").val($.trim($("#i-mensagem").val()));	
	if ($("#i-mensagem").val().length == 0 && return_upload_msg.status == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite a mensagem ou anexe um arquivo antes de enviar.',buttons:{'Ok':{is_default:1,onclick:"foc('i-mensagem');ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		ajax = false;
		$("#enviar-box").hide();
		$("#processando-box").show();
		if (return_upload_msg.status > 0)
			var dataString = "f-id-licitacao="+$("#id-licitacao").val()+"&f-mensagem="+encodeURIComponent($("#i-mensagem").val())+"&f-anexo="+encodeURIComponent(return_upload_msg.long_filename);
		else
			var dataString = "f-id-licitacao="+$("#id-licitacao").val()+"&f-mensagem="+encodeURIComponent($("#i-mensagem").val())+"&f-anexo=";
		$.ajax({
			type: "POST",
			url: "c.licitacao_envia_mensagem.php",
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
	if ($(ob).attr("data-mode") == "readonly")
		return false;

	if ($(ob).hasClass("cb0"))
		$(ob).attr("class", "cb1");
	else
		$(ob).attr("class", "cb0");
}

function ckNovoPrazoLimite(ob)
{
	if ($(ob).hasClass("cb0"))
	{
		$(ob).attr("class", "cb1");
		$(".np").show();
	}
	else
	{
		$(ob).attr("class", "cb0");
		$(".np").hide();
	}
}

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

//-------- ITENS ----------
function itemAbas()
{
	$.ajax({
		type: "POST",
		url: "c.licitacao_item_abas.php",
		data: "id-licitacao="+$("#id-licitacao").val(),
		dataType: "json",
		success: function(data)
		{
			$("#itens-abas").html(data[1]);
			$("#itens-abas").show();
			$(".total-itens").html(data[2]);
			itensToggle($("#a-itens"));
			calcularScrollWidth();

			if (data[2] == 1)
				$("#item-"+data[3]).click();
		}
	});
}

function scrollLeft()
{
	if (!sBusy)
	{
		//verificar se ainda pode continuar scrolling
		var cur_left = $(".scroll-container").position().left;
		if (cur_left < 0)
		{
			var target = cur_left + 200;
			if (target > 0)
				inc = Math.abs(cur_left);
			else
				inc = 200;

			sBusy = true;
			$(".scroll-container").animate({"left":"+="+inc}, 140, function(){
				sBusy = false;
				scrollBtns();
			});
		}
	}
}

function scrollRight()
{
	if (!sBusy)
	{
		//verificar se ainda pode continuar scrolling
		var max_left = -(scroll_width - 938);
		var cur_left = $(".scroll-container").position().left;
		if (max_left < cur_left)
		{
			var target = cur_left - 200;
			if (target < max_left)
				inc = Math.abs(max_left) - Math.abs(cur_left);
			else
				inc = 200;

			sBusy = true;
			$(".scroll-container").animate({"left":"-="+inc}, 140, function(){
				sBusy = false;
				scrollBtns();
			});
		}
	}
}

function calcularScrollWidth()
{
	scroll_width = 0;
	$('.scroll-container').children(".lot").each(function(){ scroll_width += $(this).outerWidth(true); });
	$('.scroll-container').promise().done(function(){ $(".scroll-container").width(scroll_width); scrollBtns(); });
}

function scrollBtns()
{
	$(".sbr").hide();
	$(".sbl").hide();

	if ($(".scroll-container").position().left < 0)
		$(".sbl").show();

	if (($(".scroll-container").position().left + scroll_width) > 938)
		$(".sbr").show();
}

//-------- END ITENS ----------



function declinarParticipacao(now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			$("#i-obs-declinar").val($.trim($("#i-obs-declinar").val()));
			if ($("#i-motivo-declinar").val() == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Escolha um motivo.</p>');
				$("#i-motivo-declinar").focus();
				return false;
			}

			if ($("#i-obs-declinar").val() == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Descreva o motivo.</p>');
				$("#i-obs-declinar").focus();
				return false;
			}

			ajax = false;
			$.ajax({
				type: "post",
				url: "c.licitacao_declinar.php",
				data: "f-id-licitacao="+$("#id-licitacao").val()+"&f-id-motivo="+$("#i-motivo-declinar").val()+"&f-id-submotivo="+$("#i-submotivo-declinar").val()+"&f-observacoes="+encodeURIComponent($("#i-obs-declinar").val()),
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
						ultimateClose();
						window.location.href = data[1];
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
				url: "c.licitacao_declinar_dados.php",
				data: "f-id-licitacao="+$("#id-licitacao").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 1)
					{
						ultimateDialog({
							title:'Sem Interesse',
							color:'gray',
							width:540,
							content:data[1],
							buttons:{'Concluir':{is_default:1,onclick:"declinarParticipacao(true);",css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
						});
					}
				}
			});
		}
	}
}

function listarSubmotivosDeclinio()
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "c.licitacao_declinar_submotivos.php",
			data: "f-id-motivo="+$("#i-motivo-declinar").val(),
			dataType: "text",
			success: function(data){
				ajax = true;
				if (data.length > 0)
				{
					$("#i-submotivo-declinar").html(data);
					$(".submotivo-declinar").show();
				}
				else
				{
					$(".submotivo-declinar").hide();
				}
			}
		});
	}
}

function itemAPL(id_item)
{
	if ($("#mul-"+id_item).html().length > 0)
	{
		$("#mul-"+id_item).html("");
		rtext_apl_amarok = {};
		rtext_apl_amarok_upload = {};
	}
	else
	{
		$(".mul").html("");
		$("#mul-"+id_item).html('<div id="item-apl" style="text-align:center; line-height:30px; background-color:#ffffff; width:938px;"><span class="gray-88 italic">Carregando APL... Aguarde...</span></div>');
		$.ajax({
			type:"post",
			url:"c.licitacao_item_apl.php",
			data:"f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item+"&f-pergunta=1",
			dataType:"json",
			success:function(data)
			{
				if (data[0] == 20)
				{
					ultimateDialog({
						title:data[1],
						width:data[2],
						color:data[3],
						content:data[4],
						buttons:{'Ok':{is_default:1}}
					});
					$("#mul-"+id_item).html("");
				}
				else if (data[0] == 21)
				{
					ultimateDialog({
						title:data[1],
						width:data[2],
						color:data[3],
						content:data[4],
						buttons:{'Criar Usuário':{href:'index.php?p=cli_usuarioeditar',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
					});
					$("#mul-"+id_item).html("");
				}
				else if (data[0] == 2)
				{
					$("#mul-"+id_item).html("");
					ultimateDialog({
						title:'Enviar APL ?',
						color:'gray',
						content:'<div class="ultimate-row">'+data[1]+'</div>',
						width:500,
						buttons:{'Sim':{onclick:'itemAPL_skip('+id_item+');',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'},'Não':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
					});
				}
				else
				{
					$("#mul-"+id_item).html(data[1]);

					$("#i-data-licitacao-item").inputmask("d/m/y", {"placeholder":"__/__/____"});
					$("#i-id-licitacao-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
					$("#i-participante-cpf-item").inputmask("999.999.999-99");

					if ($('input[name="f-versao-item"]').val() == 2)
					{
						$("#i-cnpj-faturamento-item").inputmask("99.999.999/9999-99");
						$("#i-participante-telefone-item").inputmask("(99) 9999-9999[9]");
						$("#i-participante-telefone-item").keyup(function(){
						if ($(this).inputmask("unmaskedvalue").length > 10)
							$(this).inputmask("(99) 99999-9999");
						else
							$(this).inputmask("(99) 9999-9999[9]");
						});
						$("#i-limite-km-km-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
						$("#i-quantidade-revisoes-inclusas-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
						$("#i-preco-ref-edital-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
						$("#i-prazo-pagamento-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
                        $("#i-ave-item").inputmask({"mask":"9", "repeat":6, "greedy":true});

						rtext_apl_amarok.status = parseInt($("#amarok-status").val());
						rtext_apl_amarok.long_filename = $("#amarok-long-filename").val();
						rtext_apl_amarok.short_filename = $("#amarok-short-filename").val();
						rtext_apl_amarok.file_size = $("#amarok-file-size").val();
						rtext_apl_amarok.file = $("#amarok-file").val();
						rtext_apl_amarok_upload = {};
					}

					$('#form-apl-item input[name^="f-valor"]').each(function(){
						$(this).maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
					});

					$("#i-preco-publico-vw-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
					$("#i-quantidade-veiculos-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
					$("#i-repasse-concessionario-item").inputmask({"mask":"9", "repeat":3, "greedy":false});
					$("#i-prazo-entrega-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
					$("#i-validade-proposta-item").inputmask({"mask":"9", "repeat":5, "greedy":false});

					if ($('input[name="f-versao-item"]').val() == 1)
						$("#i-v1-preco-maximo-item").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});

					$("#i-nome-orgao-item").focus();

					autosize($("#i-transformacao-detalhar-item"));
					autosize($("#i-imposto-indicar-item"));
					autosize($("#i-observacoes-item"));
				}
			}
		});
	}
}

function itemAPL_skip(id_item)
{
	ultimateClose();
	$("#mul-"+id_item).html('<div id="item-apl" style="text-align:center; line-height:30px; background-color:#ffffff; width:938px;"><span class="gray-88 italic">Carregando APL... Aguarde...</span></div>');
	$.ajax({
		type:"post",
		url:"c.licitacao_item_apl.php",
		data:"f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item+"&f-pergunta=0",
		dataType:"json",
		success:function(data)
		{
			$("#mul-"+id_item).html(data[1]);

			$("#i-data-licitacao-item").inputmask("d/m/y", {"placeholder":"__/__/____"});
			$("#i-id-licitacao-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
			$("#i-participante-cpf-item").inputmask("999.999.999-99");

			if ($('input[name="f-versao-item"]').val() == 2)
			{
				$("#i-cnpj-faturamento-item").inputmask("99.999.999/9999-99");
				$("#i-participante-telefone-item").inputmask("(99) 9999-9999[9]");
				$("#i-participante-telefone-item").keyup(function(){
				if ($(this).inputmask("unmaskedvalue").length > 10)
					$(this).inputmask("(99) 99999-9999");
				else
					$(this).inputmask("(99) 9999-9999[9]");
				});
				$("#i-limite-km-km-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
				$("#i-quantidade-revisoes-inclusas-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
				$("#i-preco-ref-edital-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
				$("#i-prazo-pagamento-item").inputmask({"mask":"9", "repeat":5, "greedy":false});

				rtext_apl_amarok.status = parseInt($("#amarok-status").val());
				rtext_apl_amarok.long_filename = $("#amarok-long-filename").val();
				rtext_apl_amarok.short_filename = $("#amarok-short-filename").val();
				rtext_apl_amarok.file_size = $("#amarok-file-size").val();
				rtext_apl_amarok.file = $("#amarok-file").val();
				rtext_apl_amarok_upload = {};
			}

			$('#form-apl-item input[name^="f-valor"]').each(function(){
				$(this).maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
			});

			$("#i-preco-publico-vw-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
			$("#i-quantidade-veiculos-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
			$("#i-repasse-concessionario-item").inputmask({"mask":"9", "repeat":3, "greedy":false});
			$("#i-prazo-entrega-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
			$("#i-validade-proposta-item").inputmask({"mask":"9", "repeat":5, "greedy":false});

			if ($('input[name="f-versao-item"]').val() == 1)
				$("#i-v1-preco-maximo-item").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});

			$("#i-nome-orgao-item").focus();

			autosize($("#i-transformacao-detalhar-item"));
			autosize($("#i-imposto-indicar-item"));
			autosize($("#i-observacoes-item"));
		}
	});
}


function itemAPL_DN(id_item, id_cliente)
{
	if ($("#mul-"+id_item+'-'+id_cliente).html().length > 0)
	{
		$("#mul-"+id_item+'-'+id_cliente).html("");
	}
	else
	{
		$(".mul").html("");
		$("#mul-"+id_item+'-'+id_cliente).html('<div id="item-apl" style="text-align:center; line-height:30px; background-color:#ffffff; width:938px;"><span class="gray-88 italic">Carregando APL... Aguarde...</span></div>');
		$.ajax({
			type:"post",
			url:"c.licitacao_item_apl_dn.php",
			data:"f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item+"&f-id-cliente="+id_cliente,
			dataType:"json",
			success:function(data)
			{
				$("#mul-"+id_item+'-'+id_cliente).html(data[1]);

				$("#i-data-licitacao-item").inputmask("d/m/y", {"placeholder":"__/__/____"});
				$("#i-id-licitacao-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
				$("#i-participante-cpf-item").inputmask("999.999.999-99");

				if ($('input[name="f-versao-item"]').val() == 2)
				{
					$("#i-cnpj-faturamento-item").inputmask("99.999.999/9999-99");
					$("#i-participante-telefone-item").inputmask("(99) 9999-9999[9]");
					$("#i-participante-telefone-item").keyup(function(){
					if ($(this).inputmask("unmaskedvalue").length > 10)
						$(this).inputmask("(99) 99999-9999");
					else
						$(this).inputmask("(99) 9999-9999[9]");
					});
					$("#i-limite-km-km-item").inputmask({"mask":"9", "repeat":10, "greedy":false});
					$("#i-quantidade-revisoes-inclusas-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
					$("#i-preco-ref-edital-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
					$("#i-prazo-pagamento-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
				}

				$('#form-apl-item input[name^="f-valor"]').each(function(){
					$(this).maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
				});

				$("#i-preco-publico-vw-item").maskMoney({prefix:'R$ ', allowNegative:false, thousands:'.', decimal:',', affixesStay:true});
				$("#i-quantidade-veiculos-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
				$("#i-repasse-concessionario-item").inputmask({"mask":"9", "repeat":3, "greedy":false});
				$("#i-prazo-entrega-item").inputmask({"mask":"9", "repeat":5, "greedy":false});
				$("#i-validade-proposta-item").inputmask({"mask":"9", "repeat":5, "greedy":false});

				if ($('input[name="f-versao-item"]').val() == 1)
					$("#i-v1-preco-maximo-item").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});

				autosize($("#i-transformacao-detalhar-item"));
				autosize($("#i-imposto-indicar-item"));
				autosize($("#i-observacoes-item"));
			}
		});
	}
}

function scrollToElement(elm, focus)
{
	$(elm).goTo();
	if (focus)
		$(elm).focus();
}

function salvarAPL_item(id_item, now)
{
	var versao = $('input[name="f-versao-item"]').val();
	if (versao == 2)
	{
		$("#i-cnpj-faturamento-item").val($.trim($("#i-cnpj-faturamento-item").val()));
		$("#i-participante-endereco-item").val($.trim($("#i-participante-endereco-item").val()));
		$("#i-participante-endereco-cep").val($.trim($("#i-participante-endereco-cep").val()));
		$("#i-ano-modelo-item").val($.trim($("#i-ano-modelo-item").val()));
		$("#i-transformacao-tipo-item").val($.trim($("#i-transformacao-tipo-item").val()));
		$("#i-garantia-prazo-outro-item").val($.trim($("#i-garantia-prazo-outro-item").val()));
		$("#i-vigencia-contrato-item").val($.trim($("#i-vigencia-contrato-item").val()));
		$("#i-multas-sansoes-item").val($.trim($("#i-multas-sansoes-item").val()));
		$("#i-prazo-item").val($.trim($("#i-prazo-item").val()));
		$("#i-vlr-item").val($.trim($("#i-vlr-item").val()));
		$("#i-imposto-indicar-item").val($.trim($("#i-imposto-indicar-item").val()));
	}
	else if (versao == 1)
	{
		$("#i-v1-documentacao-outros-item").val($.trim($("#i-v1-documentacao-outros-item").val()));
		$("#i-v1-desconto-item").val($.trim($("#i-v1-desconto-item").val()));
		$("#i-v1-prazo-pagamento-item").val($.trim($("#i-v1-prazo-pagamento-item").val()));
		$("#i-v1-numero-pool-item").val($.trim($("#i-v1-numero-pool-item").val()));
	}

	$("#i-nome-orgao-item").val($.trim($("#i-nome-orgao-item").val()));
	$("#i-site-pregao-eletronico-item").val($.trim($("#i-site-pregao-eletronico-item").val()));
	$("#i-numero-licitacao-item").val($.trim($("#i-numero-licitacao-item").val()));
	$("#i-participante-nome-item").val($.trim($("#i-participante-nome-item").val()));
	$("#i-participante-rg-item").val($.trim($("#i-participante-rg-item").val()));
	$("#i-model-code-item").val($.trim($("#i-model-code-item").val()));
	$("#i-cor-item").val($.trim($("#i-cor-item").val()));
	$("#i-motorizacao-item").val($.trim($("#i-motorizacao-item").val()));
	$("#i-potencia-item").val($.trim($("#i-potencia-item").val()));
	$("#i-combustivel-item").val($.trim($("#i-combustivel-item").val()));
	$("#i-opcionais-pr-item").val($.trim($("#i-opcionais-pr-item").val()));
	$("#i-transformacao-detalhar-item").val($.trim($("#i-transformacao-detalhar-item").val()));
	$('#form-apl-item input[name^="f-acessorio"]').each(function(){ $(this).val($.trim($(this).val())); });
	$("#i-dn-venda-item").val($.trim($("#i-dn-venda-item").val()));
	$("#i-dn-entrega-item").val($.trim($("#i-dn-entrega-item").val()));
	$("#i-ave-item").val($.trim($("#i-ave-item").val()));
	$("#i-observacoes-item").val($.trim($("#i-observacoes-item").val()));


	if ($("#i-nome-orgao-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do orgão.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-nome-orgao-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-data-licitacao-item").inputmask("unmaskedvalue").length < 8)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a data da licitação.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-data-licitacao-item',true);ultimateClose();"}}});
		return false;
	}

	if ($("#i-id-licitacao-item").val().length == 0 || $("#i-id-licitacao-item").val() == 0)
	{
		if (versao == 1)
			ultimateDialog({title:'Erro.',color:'red',content:'Digite o ID da licitação.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-id-licitacao-item',false);ultimateClose();"}}});
		else
			ultimateDialog({title:'Erro.',color:'red',content:'Informe a Licitação Nro.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-id-licitacao-item',false);ultimateClose();"}}});

		return false;
	}

	if ($('input[name="f-registro-precos-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione registro de preços.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-id-licitacao-item',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $("#i-cnpj-faturamento-item").inputmask("unmaskedvalue").length < 14)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o CNPJ corretamente.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-cnpj-faturamento-item',true);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $("#drop-estado-1").children("span").html().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o estado.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-cnpj-faturamento-item',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-modalidade-venda-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione modalidade da venda.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-modalidade-venda',false);ultimateClose();"}}});
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

	if (versao == 2)
	{
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

		if ($("#i-participante-endereco-cep").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Digite o CEP completo do participante.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-endereco-cep',true);ultimateClose();"}}});
			return false;
		}
	}
	else
	{
		if ($("#i-participante-nome-item").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do participante.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-nome-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-participante-rg-item").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Digite o RG do participante.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-rg-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-participante-cpf-item").inputmask("unmaskedvalue").length < 11)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O número CPF do participante está incorreto.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-participante-cpf-item',true);ultimateClose();"}}});
			return false;
		}
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

	if (versao == 2 && $("#i-ano-modelo-item").val().length == 0)
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

	if (versao == 2 && $('input[name="f-eficiencia-energetica-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione eficiência energética.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-opcionais-pr-item',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-transformacao-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione transformação.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-transformacao',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-transformacao-prototipo-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione protótipo.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-transformacao',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-transformacao-item"]').val() == 1)
	{
		if ($("#i-transformacao-detalhar-item").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe o campo detalhar transformação.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-transformacao-detalhar-item',true);ultimateClose();"}}});
			return false;
		}

		if (typeof rtext_apl_amarok.status !== 'undefined')
		{
			if (rtext_apl_amarok.status == 0 || rtext_apl_amarok.status == 3)
			{
				ultimateDialog({title:'Erro.',color:'red',content:'Selecione o arquivo Check List Transformação AMAROK.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#cl-amarok',false);ultimateClose();"}}});
				return false;
			}
		}
		else
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Selecione o arquivo Check List Transformação AMAROK.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#cl-amarok',false);ultimateClose();"}}});
			return false;
		}
	}

	if (versao == 2 && $('input[name="f-acessorios-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione acessórios.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-acessorios',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-emplacamento-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o emplacamento.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-emplacamento',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-licenciamento-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o licenciamento.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-emplacamento',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-ipva-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o IPVA.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-emplacamento',false);ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-garantia-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a garantia.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-garantia',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-garantia-prazo-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o prazo da garantia.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-garantia-prazo',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-garantia-prazo-item"]').val() == 4 && $("#i-garantia-prazo-outro-item").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o prazo da garantia.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-garantia-prazo-outro-item',true);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-revisao-embarcada-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione revisão embarcada.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-revisao-embarcada',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-limite-km-item"]').val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione limite de KM.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-quantidade-revisoes-inclusas-item',false);ultimateClose();"}}});
		return false;
	}

	if (versao == 2 && $('input[name="f-limite-km-item"]').val() == 1 && ($("#i-limite-km-km-item").val().length == 0 || $("#i-limite-km-km-item").val() == 0))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o limite em KM.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-limite-km-km-item',true);ultimateClose();"}}});
		return false;
	}

	if (versao == 2)
	{
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

		if ($("#i-ave-item").inputmask("unmaskedvalue").length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe a AVE.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-ave-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-ave-item").inputmask("unmaskedvalue").length < 6)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe a AVE corretamente.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-ave-item',true);ultimateClose();"}}});
			return false;
		}

		if (inv.indexOf($("#i-ave-item").inputmask("unmaskedvalue")) > -1)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe a AVE corretamente.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-ave-item',true);ultimateClose();"}}});
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
	}
	else if (versao == 1)
	{
		if ($("#i-preco-publico-vw-item").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe o preço público.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-preco-publico-vw-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-dn-venda-item").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe o DN de venda.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-dn-venda-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-validade-proposta-item").val().length < 2)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe a validade da proposta.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-validade-proposta-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-dn-entrega-item").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe o DN de entrega.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-dn-entrega-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-prazo-entrega-item").val().length == 0 || $("#i-prazo-entrega-item").val() == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe o prazo de entrega.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-prazo-entrega-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-quantidade-veiculos-item").val().length == 0 || $("#i-quantidade-veiculos-item").val() == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe a quantidade.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-quantidade-veiculos-item',true);ultimateClose();"}}});
			return false;
		}

		if ($("#i-ave-item").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Informe a AVE.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#i-ave-item',true);ultimateClose();"}}});
			return false;
		}
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
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione isenção de impostos do orgão.',buttons:{'ok':{is_default:1,onclick:"scrollToElement('#hook-isencao-impostos',false);ultimateClose();"}}});
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
			ajax = false;
			ultimateLoader(true, 'Enviando APL...');

			if (versao == 1)
				var dataString = "f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item+"&"+$("#form-apl-item").serialize();
			else if (versao == 2)
				var dataString = "f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item+"&"+$("#form-apl-item").serialize()+"&f-amarok-status="+rtext_apl_amarok.status+"&f-amarok-anexo="+encodeURIComponent(rtext_apl_amarok.long_filename)+"&f-amarok-arquivo="+rtext_apl_amarok.file;

			$.ajax({
				type:"post",
				url:"c.licitacao_item_apl_salvar.php",
				data:dataString,
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
						if (!$("#item-"+id_item).children(".comapl").length)
							$("#item-"+id_item).append('<span class="comapl"></span>');

						$("#apl-btn").attr("class", "bt-apl-style-1");
						$("#apl-btn").attr("title", "Aguardando aprovação");
						$("#apl-btn").html("APL &#x21e3;");
						$("#apl-btn").css("width","100px");
						$(".mul").html("");
						rtext_apl_amarok = {};
						rtext_apl_amarok_upload = {};
						atualizarStatus(0);
						$('html, body').animate({scrollTop: $("#d-status").offset().top-30 + 'px'}, 'fast');
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
			url:"c.licitacao_item_apl_termos.php",
			data:"f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item,
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
	$("#aaBtn-row").before('<div class="apl-row apl-br apl-bb apl-bl"><span class="apl-lb w-100 bg-3 center">Acessório ?</span><input class="apl-input w-546 bg-2 white" type="text" name="f-acessorio[]" maxlength="1000"><span class="apl-lb w-100 bg-3 center">Valor ? (R$)</span><input class="apl-input w-160 bg-2 white" type="text" name="f-valor[]" style="text-align: right;"><a class="rm-acess" href="javascript:void(0);" onclick="removerAcessorio(this);" title="Remover Acessório"></a></div>');
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

function atualizarStatus(aba)
{
	$("#d-status").html("...");
	$.ajax({
		type: "post",
		url: "c.licitacao_status.php",
		data: "id-licitacao="+$("#id-licitacao").val(),
		dataType: "json",
		success: function(data)
		{
			$("#d-status").html(data[1]);
		}
	});
}

function aprovarAPL_item_DN(id_item, id_cliente, now)
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
			dataString = $("#apr-form").serialize()+"&f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item+"&f-id-cliente="+id_cliente+"&f-apr-anexo="+encodeURIComponent(rtext_apr[1]);


			$.ajax({
				type: "post",
				url: "c.licitacao_item_apl_dn_aprovar.php",
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
						$("#apl-btn-"+id_cliente).attr("class", "bt-apl-style-2 fr");
						$("#apl-btn-"+id_cliente).attr("title", "Aprovada");
						$(".mul").html("");
						atualizarStatus(0);
						$('html, body').animate({scrollTop: $("#d-status").offset().top-30 + 'px'}, 'fast');
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
		//verificar parametros Registro de Precos, Transformacao, Garantia Diferenciada
		if ($('input[name="f-registro-precos-item"]').val() == 1 || $('input[name="f-transformacao-item"]').val() == 1 || $('input[name="f-garantia-item"]').val() == 2)
		{
			if ($('input[name="f-registro-precos-item"]').val() == 1)
				rp = 'SIM';
			else
				rp = 'NÃO';

			if ($('input[name="f-transformacao-item"]').val() == 1)
				tr = 'SIM';
			else
				tr = 'NÃO';

			if ($('input[name="f-garantia-item"]').val() == 2)
				gr = 'SIM';
			else
				gr = 'NÃO';

			ultimateDialog({
				title:'Aprovar APL',
				color:'gray',
				content:'<div class="ultimate-row bold t-red" style="background-color:#ee0000;color:#ffffff;line-height:52px;text-align:center;">ATENÇÃO!</div><div class="ultimate-row" style="margin-top:20px;"><span style="display:inline-block;color:#444444;width:160px;font-weight:bold;">Registro de Preços:</span> <span class="t-red bold">'+rp+'</span><br><br><span style="display:inline-block;color:#444444;width:160px;font-weight:bold;">Transformação:</span> <span class="t-red bold">'+tr+'</span><br><br><span style="display:inline-block;color:#444444;width:160px;font-weight:bold;">Garantia Diferenciada:</span> <span class="t-red bold">'+gr+'</span></div>',
				x_btn:0,
				buttons:{'Ok':{onclick:'aprovarAPL_item_DN_form('+id_item+', '+id_cliente+');ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'}}
			});
		}
		else
			aprovarAPL_item_DN_form(id_item, id_cliente);
	}
}

function aprovarAPL_item_DN_form(id_item, id_cliente)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true, '');
		$.ajax({
			type: "post",
			url: "c.licitacao_item_apl_dn_aprovar_form.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id-item="+id_item+"&id-cliente="+id_cliente,
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
						buttons:{'Aprovar APL':{onclick:'aprovarAPL_item_DN('+id_item+','+id_cliente+',true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
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

function reprovarAPL_item_DN(id_item, id_cliente, now)
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
				url: "c.licitacao_item_apl_dn_reprovar.php",
				data: "f-id-licitacao="+$("#id-licitacao").val()+"&f-id-item="+id_item+"&f-motivo="+$("#i-motivo-declinar-apl").val()+"&f-submotivo="+$("#i-submotivo-declinar-apl").val()+"&f-observacoes="+encodeURIComponent($("#i-obs").val())+"&f-id-cliente="+id_cliente,
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
						$("#apl-btn-"+id_cliente).attr("class", "bt-apl-style-4 fr");
						$("#apl-btn-"+id_cliente).attr("title", "Reprovada");
						$(".mul").html("");
						atualizarStatus(0);
						$('html, body').animate({scrollTop: $("#d-status").offset().top-30 + 'px'}, 'fast');
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
				url: "c.licitacao_item_apl_dn_reprovar_dados.php",
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
						buttons:{'Reprovar APL':{is_default:1,onclick:'reprovarAPL_item_DN('+id_item+','+id_cliente+',true);',css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}});

					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function reverterAprovacao(id_apl, now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			if ($("#i-pl-data").inputmask("unmaskedvalue").length > 0 || $("#i-pl-hora").inputmask("unmaskedvalue").length > 0)
			{
				if ($("#i-pl-data").inputmask("unmaskedvalue").length < 8 || $("#i-pl-hora").inputmask("unmaskedvalue").length < 4)
				{
					ultimateError('<p><span class="bold">Erro:</span> Informe o novo prazo limite corretamente (Data e Hora).</p>');
					if ($("#i-pl-data").inputmask("unmaskedvalue").length < 8)
						$("#i-pl-data").focus();
					else
						$("#i-pl-hora").focus();
					return false;
				}
			}

			ajax = false;
			$.ajax({
				type: "post",
				url: "c.licitacao_item_apl_reverter_a.php",
				data: "f-id-apl="+id_apl+"&f-observacoes="+encodeURIComponent($("#i-pl-obs").val())+"&f-data-limite="+$("#i-pl-data").val()+"&f-hora-limite="+$("#i-pl-hora").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 20)
					{
						alert(data[1]);
					}
					else if (data[0] == 9)
					{
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					}
					else if (data[0] == 8)
					{
						ultimateError('<p><span class="bold">Erro:</span> O prazo limite atual expirou. Informe um novo prazo limite.</p>');
					}
					else if (data[0] == 7)
					{
						ultimateError('<p><span class="bold">Erro:</span> O novo prazo limite precisa ser maior ou igual a data/hora atual e menor ou igual a data/hora de abertura.</p>');
					}
					else if (data[0] == 1)
					{
						ultimateClose();
						window.location.href = data[1];
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
				url: "c.licitacao_item_apl_reverter_a_info.php",
				data: "f-id-apl="+id_apl,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');

					if (data[0] == 9)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({
							title:'Reverter Aprovação da APL',
							width:600,
							content:data[1],
							buttons:{'Reverter':{is_default:1,onclick:'reverterAprovacao('+id_apl+',true);',css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
						}, function(){
								$("#i-pl-data").inputmask("d/m/y", {"placeholder":"__/__/____"});
								$("#i-pl-hora").inputmask("hh:mm", {"placeholder":"__:__"});
							}
						);
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function reverterReprovacao(id_apl, now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			if ($("#i-pl-data").inputmask("unmaskedvalue").length > 0 || $("#i-pl-hora").inputmask("unmaskedvalue").length > 0)
			{
				if ($("#i-pl-data").inputmask("unmaskedvalue").length < 8 || $("#i-pl-hora").inputmask("unmaskedvalue").length < 4)
				{
					ultimateError('<p><span class="bold">Erro:</span> Informe o novo prazo limite corretamente (Data e Hora).</p>');
					if ($("#i-pl-data").inputmask("unmaskedvalue").length < 8)
						$("#i-pl-data").focus();
					else
						$("#i-pl-hora").focus();
					return false;
				}
			}

			ajax = false;
			$.ajax({
				type: "post",
				url: "c.licitacao_item_apl_reverter_r.php",
				data: "f-id-apl="+id_apl+"&f-observacoes="+encodeURIComponent($("#i-pl-obs").val())+"&f-data-limite="+$("#i-pl-data").val()+"&f-hora-limite="+$("#i-pl-hora").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 20)
					{
						alert(data[1]);
					}
					else if (data[0] == 9)
					{
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					}
					else if (data[0] == 8)
					{
						ultimateError('<p><span class="bold">Erro:</span> O prazo limite atual expirou. Informe um novo prazo limite.</p>');
					}
					else if (data[0] == 7)
					{
						ultimateError('<p><span class="bold">Erro:</span> O novo prazo limite precisa ser maior ou igual a data/hora atual e menor ou igual a data/hora de abertura.</p>');
					}
					else if (data[0] == 1)
					{
						ultimateClose();
						window.location.href = data[1];
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
				url: "c.licitacao_item_apl_reverter_r_info.php",
				data: "f-id-apl="+id_apl,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');

					if (data[0] == 9)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({
							title:'Reverter Reprovação da APL',
							width:600,
							content:data[1],
							buttons:{'Reverter':{is_default:1,onclick:'reverterReprovacao('+id_apl+',true);',css_class:'ultimate-btn-red ultimate-btn-left'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
						}, function(){
								$("#i-pl-data").inputmask("d/m/y", {"placeholder":"__/__/____"});
								$("#i-pl-hora").inputmask("hh:mm", {"placeholder":"__:__"});
							}
						);
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function exportarPDF()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'Exportando licitação...aguarde...');
		$.ajax({
			type:"post",
			url:"c.licitacao_exportar.php",
			data:"id-licitacao="+$("#id-licitacao").val(),
			dataType:"json",
			success:function(data)
			{
				ajax = true;
				ultimateLoader(false, '');
				if (data[0] == 1)
					window.location.href = "c.download.php?id="+data[1];
				else
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
			}
		});
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
			xhr.open("POST", "c.upload_apr.php");
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


function licAPL_historico(id_item, id_cliente)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "c.licitacao_item_apl_historico.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id-item="+id_item+"&id-cliente="+id_cliente,
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

function buscar()
{
	if ($("#adv-search-box").is(":visible"))
	{
		$("#erro").hide();
		$("#i-orgao").val($.trim($("#i-orgao").val()));

		if ($("#i-da-fr").inputmask("unmaskedvalue").length < 8 &&
			$("#i-da-to").inputmask("unmaskedvalue").length < 8 &&
			$("#i-estado").val().length == 0 &&
			$("#i-cidade").val() == 0 &&
			$("#i-orgao").val().length == 0 &&
			$("#i-modalidade").val() == 0)
		{
			$("#erro").fadeIn(260).fadeOut(220).fadeIn(180).fadeOut(140).fadeIn(100);
			return false;
		}

		var dataString = "f-da-fr="+$("#i-da-fr").val()+"&f-da-to="+$("#i-da-to").val()+"&f-estado="+$("#i-estado").val()+"&f-cidade="+$("#i-cidade").val()+"&f-orgao="+encodeURIComponent($("#i-orgao").val())+"&f-modalidade="+$("#i-modalidade").val();
		$("#adv-search-box").hide();

		if (ajax)
		{
			ajax = false;
			$("#search-options").hide();
			$("#search-processing").show();

			$.ajax({
				type: "POST",
				url: "c.licitacao_abrir_buscaf.php",
				data: dataString,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					window.location.href = "index.php?p=cli_index";
				}
			});
		}
	}
	else
	{	
		$("#i_search").val($.trim($("#i_search").val()));
		if ($("#i_search").val().length == 0)
			return false;

		if (ajax)
		{
			ajax = false;
			$("#search-options").hide();
			$("#search-processing").show();
			$.ajax({
				type: "POST",
				url: "c.licitacao_abrir_busca.php",
				data: "id="+$("#i_search").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 1)
					{
						window.location.href = data[1];
					}
					else
					{
						ultimateDialog({title:'Erro.',color:'red',content:'Licitação não encontrada.',buttons:{'Ok':{is_default:1}}});
						$("#search-processing").hide();
						$("#search-options").show();
					}
				}
			});
		}
	}
}

function advSearch(ob)
{
	if ($("#adv-search-box").is(":visible"))
	{
		closeAdvSearch();
	}
	else
	{
		var rt = ($(window).width() - ($(ob).offset().left + $(ob).outerWidth()));
		$("#adv-search-box").css("right", rt+"px");
		$("#adv-search-box").css("top", $(ob).offset().top+"px");
		$("#adv-search-box").show();
	}
}

function closeAdvSearch()
{
	$("#adv-search-box").hide();
}

function listarCidades()
{
	$("#i-cidade").val(0);
	$(".cid").hide();

	$.ajax({
		type: "GET",
		url: "c.cidades.php",
		data: "f-estado="+$("#i-estado").val(),
		dataType: "text",
		success: function(data)
		{
			$("#i-cidade").html(data);
			$(".cid").show();
		}
	});
}

//-------- file upload APL Check List Transformacao AMAROK ----------
function selectFileAPL_AMAROK()
{
	if ($("#cl-amarok").attr("data-mode") == "readonly")
		return false;

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
			xhr.open("POST", "c.upload_apl_amarok.php");
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
	if ($("#cl-amarok").attr("data-mode") == "readonly")
		return false;

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
			url: "c.drop-box-apl.php",
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

	if (n == 1) $('input[name="f-estado-item"]').val(v);
	else if (n == 2) $('input[name="f-dn-venda-estado-item"]').val(v);
	else if (n == 3) $('input[name="f-dn-entrega-estado-item"]').val(v);

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

function dropEditais()
{
	if (!ajax) return false;

	if ($("#drop-editais").hasClass("drop"))
	{
		dropEditaisClose();
	}
	else
	{
		if (!$("#drop-editais-content").length)
			$('body').append('<div id="drop-editais-content" class="drp"></div>');

		$("#drop-editais").addClass("drop");
		$("#drop-editais").children("img").attr("src", "img/a-up-g.png");
		$("#drop-editais-content").css("top", $("#drop-editais").offset().top + 30);
		$("#drop-editais-content").html('<span class="lh-20 italic" style="padding:0 10px;">Carregando...</span>');
		var lf = Math.round($("#drop-editais").offset().left + $("#drop-editais").outerWidth() - $("#drop-editais-content").outerWidth());
		$("#drop-editais-content").css("left", lf+"px");
		$("#drop-editais-content").show();

		ajax = false;
		$.ajax({
			type: "post",
			url: "c.licitacao_abrir_editais.php",
			data: "id="+$("#id-licitacao").val(),
			dataType: "json",
			success: function(data)
			{
				$("#drop-editais-content").html(data[1]);
				$("#total-editais").html(data[2]);
				var lf = Math.round($("#drop-editais").offset().left + $("#drop-editais").outerWidth() - 685);
				$("#drop-editais-content").css("left", lf+"px");
				ajax = true;
				if (data[2] == 0)
				{
					dropEditaisClose();
					$("#drop-editais").remove();
				}
			}
		});
	}
}

function dropEditaisClose()
{
	$("#drop-editais").removeClass("drop");
	$("#drop-editais").children("img").attr("src", "img/a-down-g.png");
	$("#drop-editais-content").remove();
}
