var return_upload_msg = {status:0,long_filename:""};
var return_upload_ata = {status:0,long_filename:""};
var lot,row,col;
var sBusy = false;
var scroll_width = 0;
var filtro_status = [];
var filtro_estados = [];
var filtro_cidades = [];
var filtro_regioes = [];
var filtro_ultimas = [];
var filtro_dn = [];
var sel = [];
var tex = [];
var mon_users = [];
var pcmsg = true;
var inb = [];

$(document).ready(function(){

	$(document).on("mouseenter", ".hgl", function(){ $(this).css("background-color", "#ffffb4"); });
	$(document).on("mouseleave", ".hgl", function(){ $(this).css("background-color", "#ffffff"); });

	$(document).on("mouseenter", ".mon-user-row", function(){ $(this).css("background-color", "#ffffb4"); });
	$(document).on("mouseleave", ".mon-user-row", function(){ $(this).css("background-color", "#efefef"); });

	$(document).on("click", ".sbl", function(){ scrollLeft(); });
	$(document).on("click", ".sbr", function(){ scrollRight(); });

	$(document).on("click", ".tab", function()
	{
		if (ajax)
		{
			var tb = $(this);
			$(".tab").removeClass("tab-active");
			$(this).addClass("tab-active");
			$("#cell-lote").val(parseInt($(this).parent().attr("id").split("-")[1]));
			$("#cell-item").val(parseInt($(this).attr("id").split("-")[1]));
			$("#itens-info").html('<div style="height:215px; text-align: center;"><img src="img/loader_32.gif" style="margin-top:90px"></div>');
			$("#itens-info").show();
			ajax = false;
			$.ajax({
				type: "post",
				url: "a.licitacao_item_item.php",
				data: "id-licitacao="+$("#id-licitacao").val()+"&id-lote="+$("#cell-lote").val()+"&id-item="+$("#cell-item").val(),
				dataType: "json",
				success: function(data){
					$("#itens-info").html(data[1]);
					ajax = true;
					if (data[2] == 1 && !$(tb).children(".comapl").length)
						$(tb).append('<span class="comapl"></span>');
				}
			});
		}
	});

	$(document).on("dblclick", ".lot-nome", function(){
		if (ajax)
		{
			ajax = false;
			ultimateLoader(true,'');
			id_lote = parseInt($(this).parent().attr("id").split("-")[1]);
			$.ajax({
				type: "post",
				url: "a.licitacao_item_lote.php",
				data: "id-licitacao="+$("#id-licitacao").val()+"&id-lote="+id_lote,
				dataType: "json",
				success: function(data){
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else
						ultimateDialog({
							title:'Lote',
							content:data[1],
							buttons:{'Ok':{is_default:1,onclick:"atualizarLote("+id_lote+");",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}},
						}, function(){ $("#i-lote").focus().val($("#i-lote").val()); });
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

	tinymce.init({
		selector: "#i-mensagem",
		menubar: false,
		statusbar: false,
		resize: false,
		element_format: "html",
		language: 'pt_BR',
		plugins: ["textcolor"],
		skin: 'gelicmsg',
		toolbar: "bold italic underline forecolor removeformat"
	});

	//carregarTodosItens();
	itemAbas();
	atualizarHistorico(true);
	atualizarSemInteresse();

	$(document).on("click",".cell",function()
	{
		cell = $(this).attr("id");
		$("#cell-edit").remove();
		$("#cell-campo").val("");
		fromKey = false;

		if (cell == "cell-1")
		{
			//ITEM
			$("#itens-info").append('<input id="cell-edit" class="cell-text" type="text">');
			$("#cell-edit").css("left", ($(this).position().left)+"px");
			$("#cell-edit").css("top", ($(this).position().top)+"px");
			$("#cell-edit").css("width","294px");
			$("#cell-edit").attr("maxlength","40");
			$("#cell-campo").val("item");
		}
		else if (cell == "cell-2")
		{
			//MARCA
			$("#itens-info").append('<input id="cell-edit" class="cell-text" type="text">');
			$("#cell-edit").css("left", ($(this).position().left)+"px");
			$("#cell-edit").css("top", ($(this).position().top)+"px");
			$("#cell-edit").css("width","294px");
			$("#cell-edit").attr("maxlength","100");
			$("#cell-campo").val("marca");
		}
		else if (cell == "cell-3")
		{
			//MODELO
			$("#itens-info").append('<select id="cell-edit" class="cell-text" style="padding-left:2px;"><option value="0"></option><option value="1">Up! 1.0</option><option value="2">Gol 1.0</option><option value="3">Gol 1.6</option><option value="4">Fox 1.0</option><option value="5">Fox 1.6</option><option value="6">Golf 1.0</option><option value="7">Golf 1.4</option><option value="8">Golf 1.6</option><option value="9">Golf 2.0</option><option value="10">Voyage 1.0</option><option value="11">Voyage 1.6</option><option value="12">Jetta 1.4</option><option value="13">Jetta 2.0</option><option value="14">Saveiro 1.6</option><option value="15">Amarok 2.0</option><option value="23">Amarok 3.0</option><option value="16">CrossFox 1.6</option><option value="17">SpaceFox 1.6</option><option value="20">Polo 1.0</option><option value="21">Polo 1.6</option><option value="22">Polo 200</option><option value="24">Virtus 1.0</option><option value="25">Virtus 1.6</option><option value="18">Incompatível</option><option value="19">Não disponível</option></select>');
			$("#cell-edit").css("left", ($(this).position().left)+"px");
			$("#cell-edit").css("top", ($(this).position().top)+"px");
			$("#cell-edit").css("width","294px");
			$("#cell-campo").val("id_modelo");
		}
		else if (cell == "cell-4")
		{
			//DESCRICAO
			$("#itens-info").append('<textarea id="cell-edit" class="cell-textarea" rows="1"></textarea>');
			$("#cell-edit").css("left", ($(this).position().left)+"px");
			$("#cell-edit").css("top", ($(this).position().top)+"px");
			$("#cell-edit").css("width", "823px");
			$("#cell-edit").css("height", ($(this).height()+14)+"px");
			$("#cell-campo").val("descricao");
			autosize($("#cell-edit")).on('autosize:resized', function(){ $("#cell-4").css("height", $("#cell-edit").height()+"px"); });
		}
		else if (cell == "cell-5")
		{
			//QUANTIDADE
			$("#itens-info").append('<input id="cell-edit" class="cell-text" type="text">');
			$("#cell-edit").css("left", ($(this).position().left)+"px");
			$("#cell-edit").css("top", ($(this).position().top)+"px");
			$("#cell-edit").css("width", "294px");
			$("#cell-edit").attr("maxlength","10");
			$("#cell-edit").inputmask({"mask":"9","repeat":10,"greedy":false});
			$("#cell-campo").val("quantidade");
		}
		else if (cell == "cell-6")
		{
			//VALOR
			$("#itens-info").append('<input id="cell-edit" class="cell-text" type="text">');
			$("#cell-edit").css("left", ($(this).position().left)+"px");
			$("#cell-edit").css("top", ($(this).position().top)+"px");
			$("#cell-edit").css("width", "294px");
			$("#cell-edit").attr("maxlength","18");
			$("#cell-edit").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
			$("#cell-campo").val("valor");
		}
		else if (cell == "cell-7")
		{
			//TIPO DO VEICULO
			$("#itens-info").append('<select id="cell-edit" class="cell-text" style="padding-left:2px;"><option value="0"></option><option value="1">Hatch Popular</option><option value="2">Hatch Premium</option><option value="3">Sedan Popular</option><option value="4">Sedan Premium</option><option value="5">Pick-up Popular</option><option value="6">Pick-up Premium</option><option value="7">Station Wagon</option><option value="8">Não pertinente</option><option value="9">Não disponível</option></select>');
			$("#cell-edit").css("left", ($(this).position().left)+"px");
			$("#cell-edit").css("top", ($(this).position().top)+"px");
			$("#cell-edit").css("width", "294px");
			$("#cell-campo").val("id_tipo_veiculo");
		}

		$.ajax({
			type: "GET",
			url: "a.licitacao_item_cel.php",
			data: "id-lote="+$("#cell-lote").val()+"&id-item="+$("#cell-item").val()+"&campo="+$("#cell-campo").val(),
			dataType: "text",
			success: function(data)
			{
				$("#cell-cur-val").val(data);
				$("#cell-edit").val(data);
				$("#cell-edit").show();
				$("#cell-edit").focus();
			}
		});
	});

	$(document).on("blur", "#cell-edit", function()
	{
		if (fromKey) return false;
		saveCell($(this));
	});

	$(document).on("keyup", "#i-search", function(e){
		if (e.keyCode == 13)
			buscar();
	});

	$(document).on("keydown","#cell-edit",function(e)
	{
		fromKey = false;
		if (e.keyCode == 27) //esc
		{
			fromKey = true;
			e.preventDefault();
			e.stopPropagation();
			$("#cell-val").val("");
			$("#cell-cur-val").val("");
			$("#cell-edit").remove();
		}
		else if (e.keyCode == 13) //enter
		{
			if ($(this).hasClass("cell-text"))
			{
				fromKey = true;
				e.preventDefault();
				e.stopPropagation();
				saveCell($(this));
			}
		}
		else if (e.keyCode == 9) //tab
		{
			fromKey = true;
			e.preventDefault();
			e.stopPropagation();
			saveCell($(this));
			
			if ($("#cell-campo").val() == "item")
				$("#cell-2").click();
			else if ($("#cell-campo").val() == "marca")
				$("#cell-3").click();
			else if ($("#cell-campo").val() == "id_modelo")
				$("#cell-4").click();
			else if ($("#cell-campo").val() == "descricao")
				$("#cell-6").click();
			else if ($("#cell-campo").val() == "quantidade")
				$("#cell-1").click();
			else if ($("#cell-campo").val() == "valor")
				$("#cell-7").click();
			else if ($("#cell-campo").val() == "id_tipo_veiculo")
				$("#cell-5").click();
		}
	});


	$(document).click(function(e){
		if (!$(e.target).hasClass("drp") && !$(e.target).parent().hasClass("drp"))
		{
			$("#mais-filtros").hide();
			srch = 1;
			closeDropFiltro();
			dropEditaisClose();
		}
		else if ($(e.target).hasClass("hdf"))
		{
			closeDropFiltro();
		}
	});


	$(document).keydown(function(e) {
		if (e.keyCode == 27)
		{
			dropEditaisClose();
			if ($("#drop-filtro").is(":visible"))
			{
				closeDropFiltro();
			}
			else
			{
				$("#mais-filtros").hide();
				srch = 1;
			}
		}
	});

	$(document).on("keyup blur", "#filtro-op,#filtro-adve,#filtro-numero", function(e){
		if ($(this).val().length > 0)
		{
			$(this).css("color", "#ff0000");
			$(this).css("border-color", "#ee0000");
		}
		else
		{
			$(this).css("color", "#282828");
			$(this).css("border-color", "#cccccc");
		}
	});

	$(document).on("keyup blur", "#filtro-data-de,#filtro-data-ate", function(e){
		if ($(this).inputmask("unmaskedvalue").length == 8)
		{
			$(this).css("color", "#ff0000");
			$(this).css("border-color", "#ee0000");
		}
		else
		{
			$(this).css("color", "#282828");
			$(this).css("border-color", "#cccccc");
		}
	});

	$(document).on("keyup", "#i-date", function(e){
		if (e.keyCode == 13)
			insertDate();
	});


	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});

	tex[0] = ["- status -", "Status"];
	tex[1] = ["- estado -", "Estado(s)"];
	tex[2] = ["- cidade -", "Cidade(s)"];
	tex[3] = ["- região -", "Região(ões)"];
	tex[4] = ["- últimas -", "Últimas"];
	tex[5] = ["- dn -", "DN(s)"];

	sel[0].a = filtro_status;
	atualizaFiltro(0, false);

	sel[1].a = filtro_estados;
	atualizaFiltro(1, false);

	sel[2].a = filtro_cidades;
	atualizaFiltro(2, false);

	sel[3].a = filtro_regioes;
	atualizaFiltro(3, false);

	sel[4].a = filtro_ultimas;
	atualizaFiltro(4, false);

	sel[5].a = filtro_dn;
	atualizaFiltro(5, false);

	$("#filtro-data-de").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#filtro-data-ate").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#filtro-adve").inputmask({"mask":"9","repeat":10,"greedy":false});

	pregoeiroChamaMensagens();
	setInterval(pregoeiroChamaMensagens,60000); //atualizar a cada 1 minuto
});

function ckSelf(ob)
{
	if ($(ob).hasClass("check-chk0"))
		$(ob).removeClass("check-chk0").addClass("check-chk1");
	else
		$(ob).removeClass("check-chk1").addClass("check-chk0");
}


function saveCell(ob)
{
	if ($("#cell-cur-val").val() != $("#cell-edit").val())
	{
		$("#cell-val").val($("#cell-edit").val()); //ler conteudo digitado
		$("#cell-edit").remove(); //remover caixa de texto
		$.ajax({
			type: "post",
			url: "a.licitacao_item_salvar.php",
			data: $("#cell-form").serialize()+"&id-licitacao="+$("#id-licitacao").val(),
			dataType: "json",
			success: function(data)
			{
				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1) //sucesso
				{
					//ITEM
					if (data[1].length == 0)
					{
						$("#cell-1").html('&lt;não informado&gt;');
						$("#cell-1").attr("class","item-vl gray-88 cell italic");
						$("#item-"+$("#cell-item").val()).children("span:first-child").html("ITEM: n/d");
						calcularScrollWidth();
					}
					else
					{
						$("#cell-1").html(data[1]);
						$("#cell-1").attr("class","item-vl cell");
						$("#item-"+$("#cell-item").val()).children("span:first-child").html("ITEM: "+data[1]);
						calcularScrollWidth();
					}

					//MARCA
					if (data[2].length == 0)
					{
						$("#cell-2").html('&lt;não informado&gt;');
						$("#cell-2").attr("class","item-vl cell gray-88 italic");
					}
					else
					{
						$("#cell-2").html(data[2]);
						$("#cell-2").attr("class","item-vl cell");
					}

					//MODELO
					if (data[10] == 0)
					{
						$("#cell-3").html(data[3]);
						$("#cell-3").attr("class","item-vl cell gray-88 italic");
					}
					else
					{
						$("#cell-3").html(data[3]);
						$("#cell-3").attr("class","item-vl cell");
					}

					if (data[10] != 18)
						$("#modelo-antigo").remove();

					//DESCRICAO
					if (data[4].length == 0)
					{
						$("#cell-4").html('&lt;não informado&gt;');
						$("#cell-4").attr("class","item-vl-desc cell gray-88 italic");
					}
					else
					{
						$("#cell-4").html(data[4]);
						$("#cell-4").attr("class","item-vl-desc cell");
					}

					//QUANTIDADE
					$("#cell-5").html(data[5]);

					//VALOR
					if (data[6] == "R$ 0,00")
					{
						$("#cell-6").html('&lt;não informado&gt;');
						$("#cell-6").attr("class","item-vl cell gray-88 italic");
					}
					else
					{
						$("#cell-6").html(data[6]);
						$("#cell-6").attr("class","item-vl cell");
					}

					//TIPO VEICULO
					if (data[8] == 0)
					{
						$("#cell-7").html(data[7]);
						$("#cell-7").attr("class","item-vl cell gray-88 italic");
					}
					else
					{
						$("#cell-7").html(data[7]);
						$("#cell-7").attr("class","item-vl cell");
					}

					//TOTAL
					$("#total-item").html(data[9]);

					$("#cell-cur-val").val($("#cell-val").val());
				}
				else if (data[0] == 8)
				{
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			}
		});
	}
	else
		$("#cell-edit").remove(); //remover caixa de texto
}

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
		$(ob).attr("class","exp-btn0");
		$(".imp-conteudo").hide();
	}
	else
	{
		$(ob).attr("class","exp-btn1");
		$(".imp-conteudo").show();
	}
}

function pcToggle(ob)
{
	if ($(".pc-conteudo").is(":visible"))
	{
		$(ob).attr("class","exp-btn0");
		$(".pc-conteudo").hide();
	}
	else
	{
		$(ob).attr("class","exp-btn1");
		$(".pc-conteudo").show();
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

function semInteresseToggle(ob)
{
	if ($(".si-conteudo").is(":visible"))
	{
		$(ob).attr("class","exp-btn0");
		$(".si-conteudo").hide();
	}
	else
	{
		$(ob).attr("class","exp-btn1");
		$(".si-conteudo").show();
	}
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
			xhr.open("POST", "a.upload_mensagem.php");
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
		var b = Math.round(794 * percentComplete / 100);
		$("#upl-bar").css("width", b+'px');
		$("#upl-per").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upl-bar").css("width", "794px");
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
		$("#upl-filesize").html(return_upload_msg.file_size);
		$("#upl-ready").show();
	}
}

function cancelUploadMSG()
{
	return_upload_msg = {status:0,long_filename:""};
	$("#upl-ready").hide();
	$("#upl-loading").hide();
	$("#upl-btn").show();
}
//------- end upload MSG -----------


function enviarMensagem()
{
	var msg = $.trim(tinymce.activeEditor.getContent());
	$("#i-doc-desc").val($.trim($("#i-doc-desc").val()));

	if (msg.length == 0 && return_upload_msg.status == 0 && $("#req-box").is(":visible") == false)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite a mensagem ou anexe um arquivo antes de enviar.',buttons:{'Ok':{is_default:1,onclick:"tinymce.execCommand('mceFocus',false,'i-mensagem');ultimateClose();"}}});
		return false;
	}
	
	if (ajax)
	{
		ajax = false;
		$("#enviar-box").hide();
		$("#processando-box").show();
		var dataString = "f-id_licitacao="+$("#id-licitacao").val()+"&f-mensagem="+encodeURIComponent(msg)+"&f-anexo="+encodeURIComponent(return_upload_msg.long_filename);			
		$.ajax({
			type: "POST",
			url: "a.licitacao_envia_mensagem.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					$("#enviar-box").show();
					$("#processando-box").hide();
					ajax = true;
				}
				else
					atualizarHistorico(false);
			}
		});
	}
}

function atualizarHistorico(inicial)
{
	if (inicial)
		$("#historico").html('<div class="content-inside"><span class="processando">Carregando mensagens...</span></div>');

	$.ajax({
		type:"post",
		url:"a.licitacao_historico.php",
		data:"f-id-licitacao="+$("#id-licitacao").val(),
		dataType:"json",
		success:function(data)
		{
			$("#historico").html(data[1]);
			if (!inicial)
			{
				tinyMCE.activeEditor.setContent('');
				cancelUploadMSG();
				$("#enviar-box").show();
				$("#processando-box").hide();
			}
			ajax = true;
		}
	});
}

function atualizarSemInteresse()
{
	$.ajax({
		type:"post",
		url:"a.licitacao_sem_interesse.php",
		data:"f-id-licitacao="+$("#id-licitacao").val(),
		dataType:"json",
		success:function(data)
		{
			$("#a-si").html(data[1]);
			$(".si-conteudo").html(data[2]);
		}
	});
}

function prorrogarLicitacao(now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			$("#i-observacao").val($.trim($("#i-observacao").val()));

			if ($("#i-data-abertura").inputmask("unmaskedvalue").length < 8)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe a Data de Abertura corretamente.</p>');
				$("#i-data-abertura").focus();
				return false;
			}
	
			if ($("#i-hora-abertura").inputmask("unmaskedvalue").length > 0 && $("#i-hora-abertura").inputmask("unmaskedvalue").length < 4)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe a Hora de Abertura corretamente.</p>');
				$("#i-hora-abertura").focus();
				return false;
			}		

			if ($("#i-data-entrega").inputmask("unmaskedvalue").length < 8)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe a Data de Entrega das Propostas corretamente.</p>');
				$("#i-data-entrega").focus();
				return false;
			}

			if ($("#i-hora-entrega").inputmask("unmaskedvalue").length > 0 && $("#i-hora-entrega").inputmask("unmaskedvalue").length < 4)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe a Hora de Entrega das Propostas corretamente.</p>');
				$("#i-hora-entrega").focus();
				return false;
			}		

			if ($("#i-motivo").val() == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Escolha um motivo.</p>');
				$("#i-motivo").focus();
				return false;
			}

			ajax = false;
			$.ajax({
				type: "post",
				url: "a.licitacao_prorrogar.php",
				data: "f-id_licitacao="+$("#id-licitacao").val()+"&f-data_abertura="+$("#i-data-abertura").val()+"&f-hora_abertura="+$("#i-hora-abertura").val()+"&f-data_entrega="+$("#i-data-entrega").val()+"&f-hora_entrega="+$("#i-hora-entrega").val()+"&f-motivo="+$("#i-motivo").val()+"&f-submotivo="+$("#i-submotivo").val()+"&f-observacao="+encodeURIComponent($("#i-observacao").val()),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					else if (data[0] == 3)
						ultimateError('<p><span class="bold">Erro:</span> A Data de Abertura está incorreta.<br>A Data de Abertura precisa ser atual ou uma data futura.</p>');
					else if (data[0] == 2)
						ultimateError('<p><span class="bold">Erro:</span> A Hora de Abertura está incorreta.<br><br>Verifique o formato e teste novamente.<br><a class="italic">hh:mm 24 horas</a></p>');
					else if (data[0] == 4)
						ultimateError('<p><span class="bold">Erro:</span> A Data da Entrega das Propostas está incorreta.<br><br>A Data da Entrega das Propostas precisa ser uma data futura menor ou igual à Data de Abertura.</p>');
					else if (data[0] == 8)
						ultimateError('<p><span class="bold">Erro:</span> A Hora de Entrega está incorreta.<br><br>Verifique o formato e teste novamente.<br><a class="italic">hh:mm 24 horas</a></p>');
					else if (data[0] == 5)
						ultimateError('<p><span class="bold">Erro:</span> Não existem '+$("#i-prazo").val()+' dias úteis disponíveis antes da Data de Entrega das Propostas.</p>');
					else if (data[0] == 6)
						ultimateError('<p><span class="bold">Erro:</span> Não houve alteração na Data/Hora de Abertura.</p>');
					else if (data[0] == 1)
						window.location.href = "a.licitacao_abrir.php?id="+$("#id-licitacao").val()+"&t="+new Date().getTime();
					else
						ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
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
				url: "a.licitacao_prorrogar_dados.php",
				data: "id_licitacao="+$("#id-licitacao").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');

					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 8)
						ultimateDialog({title:'Licitação Encerrada',color:'red',content:'É necessário reverter o encerramento antes de prorrogar.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({
						title:'Prorrogar Data de Abertura',
						width:500,
						content:data[1],
						buttons:{'Ok':{is_default:1,onclick:"prorrogarLicitacao(true);",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}},
						function(){
							$("#i-data-abertura").inputmask("d/m/y", {"placeholder":"__/__/____"});
							$("#i-hora-abertura").inputmask("hh:mm", {"placeholder":"__:__"});
							$("#i-data-entrega").inputmask("d/m/y", {"placeholder":"__/__/____"});
							$("#i-hora-entrega").inputmask("hh:mm", {"placeholder":"__:__"});
						});
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function listarSubmotivos()
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.licitacao_prorrogar_submotivos.php",
			data: "id="+$("#i-motivo").val(),
			dataType: "text",
			success: function(data){
				ajax = true;
				if (data.length > 0)
				{
					$("#i-submotivo").html(data);
					$(".submotivo").show();
				}
				else
				{
					$(".submotivo").hide();
				}
			}
		});
	}
}

function removerLicitacao(now)
{
	if (now)
	{
		if (ajax)
		{
			ajax = false;
			$.ajax({
				type: "POST",
				url: "a.licitacao_remover.php",
				data: "id_licitacao="+$("#id-licitacao").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 1)
						window.location.href = "a.licitacao.php";
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});						
				}
			});
		}
	}
	else
	{
		ultimateDialog({
			title:'Excluir Licitação',
			color:'red',
			content:'Esta licitação será removida do sistema mas os dados NÃO serão excluídos permanentemente.<br><br><span class="bold">Confirmar ?</span>',
			buttons:{'Sim':{onclick:'removerLicitacao(true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
		});
	}
}

function encerrarLicitacao(now)
{
	if (now)
	{
		$("#ultimate-error").hide();
		if (ajax)
		{
			if ($("#i-motivo-encerrar").val() == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Escolha um motivo de encerramento.</p>');
				$("#i-motivo-encerrar").focus();
				return false;
			}

			ajax = false;
			$.ajax({
				type: "post",
				url: "a.licitacao_encerrar.php",
				data: "f-id_licitacao="+$("#id-licitacao").val()+"&f-arquivo_ata="+encodeURIComponent(return_upload_ata.long_filename)+"&f-motivo="+$("#i-motivo-encerrar").val()+"&f-submotivo="+$("#i-submotivo-encerrar").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					else if (data[0] == 1)
						window.location.href = "a.licitacao_abrir.php?id="+$("#id-licitacao").val()+"&t="+new Date().getTime();
					else
						ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
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
				url: "a.licitacao_encerrar_dados.php",
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					return_upload_ata = {status:0,long_filename:""};

					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({
						title:'Encerrar Licitação',
						width:500,
						content:data[1],
						buttons:{'Ok':{is_default:1,onclick:"encerrarLicitacao(true);",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}});
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function listarSubmotivosEncerramento()
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.licitacao_encerrar_submotivos.php",
			data: "id="+$("#i-motivo-encerrar").val(),
			dataType: "text",
			success: function(data){
				ajax = true;
				if (data.length > 0)
				{
					$("#i-submotivo-encerrar").html(data);
					$(".submotivo-encerrar").show();
				}
				else
				{
					$(".submotivo-encerrar").hide();
				}
			}
		});
	}
}

function reverterDeclinio(id_cliente,now)
{
	if (now)
	{
		if (ajax)
		{
			ajax = false;
			$.ajax({
				type: "POST",
				url: "a.licitacao_reverter_declinio.php",
				data: "f-id-licitacao="+$("#id-licitacao").val()+"&f-id-cliente="+id_cliente,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
					{
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					}
					else if (data[0] == 1)
					{
						atualizarHistorico(false);
						atualizarSemInteresse();
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
		ultimateDialog({
			title:'Reverter Declínio do Cliente',
			color:'gray',
			content:'Você tem certeza que deseja reverter o declínio de participação deste cliente?',
			buttons:{'Sim':{onclick:'reverterDeclinio('+id_cliente+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
		});
	}
}

function reverterEncerramento(now)
{
	if (now)
	{
		if (ajax)
		{
			ajax = false;
			$.ajax({
				type: "POST",
				url: "a.licitacao_reverter_encerramento.php",
				data: "f-id-licitacao="+$("#id-licitacao").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 1)
						window.location.href = "a.licitacao_abrir.php?id="+$("#id-licitacao").val()+"&t="+new Date().getTime();
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});						
				}
			});
		}
	}
	else
	{
		ultimateDialog({
			title:'Reverter Encerramento',
			color:'gray',
			content:'ATENÇÃO: Se existir uma ATA anexada ao encerramento, esta será removida.<br><br><span class="bold">Você tem certeza que deseja reverter esta ação?</span>',
			buttons:{'Sim':{onclick:'reverterEncerramento(true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
		});
	}
}

//-------- file upload ATA ----------
function selectFileATA()
{
	$("#upload-form").html('<input id="upload-button" class="file-upload" type="file" name="f-upload" onchange="uploadFileATA();">');
	$("#upload-button").click();
}

function uploadFileATA()
{
	var file = $("#upload-button")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload", $("#upload-button")[0].files[0]);
			xhr.upload.addEventListener("progress", uploadProgressATA, false);
			xhr.addEventListener("load", uploadCompleteATA, false);
			xhr.addEventListener('readystatechange', function()

			{
				if (this.readyState == 4)
					return_upload_ata = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "a.upload_ata.php");
			xhr.send(fd);
			$("#upl-btn-ata").hide();
			$("#upl-bar-ata").css("width", 0);
			$("#upl-per-ata").html('Carregando...');
			$("#upl-loading-ata").show();
		}
		else
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O arquivo selecionado é muito grande.<br><br>Tamanho máximo permitido: <span class="bold">100 MB</span>',buttons:{'ok':{is_default:1}}});
		}
	}
}

function uploadProgressATA(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var b = Math.round(466 * percentComplete / 100);
		$("#upl-bar-ata").css("width", b+'px');
		$("#upl-per-ata").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upl-bar-ata").css("width", "466px");
	}
}

function uploadCompleteATA()
{
	if (return_upload_ata.status == 0)
		cancelUploadATA();
	else
	{
		$("#upl-loading-ata").hide();
		$("#upl-filename-ata").html(return_upload_ata.short_filename);
		$("#upl-filesize-ata").html(return_upload_ata.file_size);
		$("#upl-ready-ata").show();
	}
}

function cancelUploadATA()
{
	return_upload_ata = {status:0,long_filename:""};
	$("#upl-ready-ata").hide();
	$("#upl-loading-ata").hide();
	$("#upl-btn-ata").show();
}
//------- end upload ATA -----------

function alterarStatus(now)
{
	if (now)
	{
		if (ajax)
		{
			ajax = false;
			$.ajax({
				type: "POST",
				url: "a.licitacao_status.php",
				data: "f-id-licitacao="+$("#id-licitacao").val()+"&f-id-status-admin="+$("#i-status-admin").val()+"&f-id-aba-admin="+$("#i-aba-admin").val()+"&f-id-status-bo="+$("#i-status-bo").val()+"&f-id-aba-bo="+$("#i-aba-bo").val()+"&f-id-status-dn-apl="+$("#i-status-dn-apl").val()+"&f-id-aba-dn-apl="+$("#i-aba-dn-apl").val()+"&f-id-status-dn-outros="+$("#i-status-dn-outros").val()+"&f-id-aba-dn-outros="+$("#i-aba-dn-outros").val()+"&f-fixo-admin="+($("#i-fixo-admin").hasClass("check-chk1") << 0)+"&f-fixo-bo="+($("#i-fixo-bo").hasClass("check-chk1") << 0)+"&f-fixo-dn-apl="+($("#i-fixo-dn-apl").hasClass("check-chk1") << 0)+"&f-fixo-dn-outros="+($("#i-fixo-dn-outros").hasClass("check-chk1") << 0),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
						ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito!</p>');
					else if (data[0] == 8)
						ultimateError('<p><span class="bold">Erro:</span> É necessário reverter o encerramento antes de alterar o status.</p>');
					else if (data[0] == 1)
					{
						ultimateClose();
						window.location.href = "a.licitacao_abrir.php?id="+$("#id-licitacao").val()+"&t="+new Date().getTime();
					}
					else if (data[0] == 7)
					{
						//do nothing
						ultimateClose();
					}
					else
						ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
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
				url: "a.licitacao_status_dados.php",
				data: "id_licitacao="+$("#id-licitacao").val(),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 8)
						ultimateDialog({title:'Licitação Encerrada',color:'red',content:'É necessário reverter o encerramento antes de alterar o status.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						ultimateDialog({title:'Status da Licitação',width:700,content:data[1],buttons:{'Ok':{is_default:1,onclick:"alterarStatus(true);",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}});
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}





//-------- ITENS ----------
function itemAbas()
{
	$.ajax({
		type: "POST",
		url: "a.licitacao_item_abas.php",
		data: "f-id-licitacao="+$("#id-licitacao").val(),
		dataType: 'json',
		success: function(data)
		{
			$("#itens-abas").html(data[1]);
			$("#itens-abas").show();
			$(".total-itens").html(data[2]);
			itensToggle($("#a-itens"));
			calcularScrollWidth();
		}
	});
}

function itemAdicionar(id_licitacao, id_lote)
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.licitacao_item_adicionar.php",
			data: "f-id-licitacao="+id_licitacao+"&f-id-lote="+id_lote,
			dataType: "json",
			success: function(data)
			{
				ajax = true;

				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
				{
					if (data[3] == 1) //novo lote
					{
						$(".lot-add").parent().before(data[4]);
						$(".total-itens").html(data[5]);

						calcularScrollWidth();
						scrollToEnd();
						$("#item-"+data[2]).click();
					}
					else
					{
						$("#lote-"+id_lote).children(".tab-add").before(data[4]);
						$(".total-itens").html(data[5]);

						calcularScrollWidth();
						scrollToViewItem(data[2]);
						$("#item-"+data[2]).click();
					}
				}
				else if (data[0] == 0)
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function itemRemover(id_licitacao, id_lote, id_item, now)
{
	event.stopPropagation();
	if (ajax)
	{
		if (!now)
		{
			if (id_item > 0)
				ultimateDialog({
					title:'Remover ?',
					color:'red',
					content:'<span class="red">'+$("#item-"+id_item).children("span:first-child").html()+'</span><br><br>Este item será removido.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
					buttons:{'Sim':{onclick:'itemRemover('+id_licitacao+','+id_lote+','+id_item+',true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
				});
			else
				ultimateDialog({
					title:'Remover ?',
					color:'red',
					content:'<span class="red">'+$("#lote-"+id_lote).children("span").html()+'</span><br><br>ATENÇÃO!!!.<br>Todos os itens deste lote serão removidos.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
					buttons:{'Sim':{onclick:'itemRemover('+id_licitacao+','+id_lote+','+id_item+',true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
				});
		}
		else
		{
			ultimateClose();
			ajax = false;
			$.ajax({
				type: "post",
				url: "a.licitacao_item_remover.php",
				data: "f-id-licitacao="+id_licitacao+"&f-id-lote="+id_lote+"&f-id-item="+id_item,
				dataType: "json",
			    success: function(data)
				{
					ajax = true;
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 8)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 7)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Existe 1 ou mais APLs associadas ao item.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
					{
						if (data[1] == 0) //lote removed NO
						{
							$(".total-itens").html(data[2]);
							$("#item-"+id_item).remove();
							if ($("#cell-item").val() == id_item)
							{
								$("#itens-info").html("");
								$("#itens-info").hide();
							}
							calcularScrollWidth();
							if ($(".lot-add").position().left < 1028)
								scrollToEnd();
						}
						else if (data[1] == 1) //lote removed YES
						{
							$(".total-itens").html(data[2]);
							$("#lote-"+id_lote).remove();
							if ($("#cell-lote").val() == id_lote)
							{
								$("#itens-info").html("");
								$("#itens-info").hide();
							}
							calcularScrollWidth();
							if ($(".lot-add").position().left < 1028)
								scrollToEnd();
						}
					}
					else if (data[0] == 0)
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			});
		}
	}
}

function atualizarSingle(ob, chk)
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.acesso.php",
			data: "a=lic_editar",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 1)
				{
					ckSelf(ob);
					var dataString = "id-licitacao="+$("#id-licitacao").val()+"&id-item="+$("#cell-item").val()+"&valor="+($(ob).hasClass("check-chk1") << 0)+"&chk="+chk;
					$.ajax({
						type: "post",
						url: "a.licitacao_item_single.php",
						data: dataString,
						dataType: "json",
						success: function(data)
						{
							ajax = true;
							if (data[0] == 9)
								ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
							else if (data[0] == 8)
								ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
							else if (data[0] == 0)
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
				else
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
			}
		});
	}
}

function atualizarLote(id_lote)
{
	if (ajax)
	{
		ajax = false;
		var lote = $.trim($("#i-lote").val());
		ultimateClose();
		ultimateLoader(true, '');
		$.ajax({
			type: "post",
			url: "a.licitacao_item_lote_salvar.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id-lote="+id_lote+"&lote="+encodeURIComponent(lote),
			dataType: "json",
			success: function(data)
			{
				$("#lote-"+id_lote).children(".lot-nome").html("LOTE: "+data[1]);
				calcularScrollWidth();
				ajax = true;
				ultimateLoader(false, '');
			}
		});
	}
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
		var max_left = -(scroll_width - 1058);
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

function scrollToEnd()
{
	if (scroll_width > 1058)
		$(".scroll-container").animate({"left":-(scroll_width-1058)}, 140, function(){ scrollBtns(); });
	else
		$(".scroll-container").animate({"left":0}, 140, function(){ scrollBtns(); });
}

function scrollToViewItem(id_item)
{
	var cl = $(".scroll-container").position().left;
	var il = $("#item-"+id_item).position().left + $("#item-"+id_item).parent().position().left + 2;
	var iw = $("#item-"+id_item).outerWidth(true);
	if (il - Math.abs(cl) + iw + 67 > 1050)
	{
		var nl = 1058 - il - iw - 67;
		$(".scroll-container").animate({"left":nl}, 140, function(){ scrollBtns(); });
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

	if (($(".scroll-container").position().left + scroll_width) > 1058)
		$(".sbr").show();
}

function atualizarCheck(ob)
{
	if (ajax)
	{
		ajax = false;
		$(ob).children("img").show();
		var dataString = "id-licitacao="+$("#id-licitacao").val()+"&id-item="+$("#cell-item").val()+"&field="+$(ob).attr("data-field")+"&checked="+($(ob).hasClass("itm-cb1") << 0);
		$.ajax({
			type: "post",
			url: "a.licitacao_item_check.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$(ob).children("img").hide();
				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					$(ob).attr("class",data[1]);
				else if (data[0] == 0)
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function inabilitadoClick()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true, '');
		$.ajax({
			type: "post",
			url: "a.licitacao_item_inb.php",
			data: "id-item="+$("#cell-item").val(),
			dataType: "json",
			success: function(data)
			{
				inb = JSON.parse(data[3]);
				ajax = true;
				ultimateLoader(false,'');
				ultimateDialog({
					title:'Inabilitações ('+data[2]+')',
					width:700,
					color:'gray',
					content:data[1],
					buttons:{'Ok':{is_default:1,onclick:"inabilitadoSalvar();",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
				});
			}
		});
	}
}

function inabilitadoSelect(ob, id)
{
	idx = inb.indexOf(id);
	if (idx > -1)
	{
		$(ob).removeClass("drop-item1").addClass("drop-item0");
		inb.splice(idx, 1);
	}
	else
	{
		$(ob).removeClass("drop-item0").addClass("drop-item1");
		inb.push(id);
	}

	$("#inb-count").html(inb.length);
}

function inabilitadoSalvar()
{
	if (ajax)
	{
		ajax = false;
		ultimateClose();
		$("#inab").children("img").show();
		$.ajax({
			type: "post",
			url: "a.licitacao_item_inb_salvar.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id-item="+$("#cell-item").val()+"&selected="+inb.join(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#inab").children("img").hide();

				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					$("#inab").attr("class",data[1]);
				else if (data[0] == 0)
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function semInteresseClick()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true, '');
		$.ajax({
			type: "post",
			url: "a.licitacao_item_sint.php",
			data: "id-item="+$("#cell-item").val(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				ultimateDialog({
					title:'Sem interesse DN - Aberto',
					width:500,
					color:'gray',
					content:data[1],
					buttons:{'Ok':{is_default:1,onclick:"semInteresseSalvar();",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
				});
			}
		});
	}
}

function semInteresseSalvar()
{
	if (ajax)
	{
		ajax = false;
		id_motivo = $("#i-sint-motivo").val();
		ultimateClose();
		$("#sint").children("img").show();
		$.ajax({
			type: "post",
			url: "a.licitacao_item_sint_salvar.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id-item="+$("#cell-item").val()+"&id-motivo="+id_motivo,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#sint").children("img").hide();

				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					$("#sint").attr("class",data[1]);
				else if (data[0] == 0)
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function semParticipacaoClick()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true, '');
		$.ajax({
			type: "post",
			url: "a.licitacao_item_spart.php",
			data: "id-item="+$("#cell-item").val(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				ultimateDialog({
					title:'Sem participação (com APL)',
					width:500,
					color:'gray',
					content:data[1],
					buttons:{'Ok':{is_default:1,onclick:"semParticipacaoSalvar();",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
				});
			}
		});
	}
}

function semParticipacaoSalvar()
{
	if (ajax)
	{
		ajax = false;
		id_motivo = $("#i-spart-motivo").val();
		ultimateClose();
		$("#spart").children("img").show();
		$.ajax({
			type: "post",
			url: "a.licitacao_item_spart_salvar.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id-item="+$("#cell-item").val()+"&id-motivo="+id_motivo,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#spart").children("img").hide();

				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					$("#spart").attr("class",data[1]);
				else if (data[0] == 0)
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function addDate(evento)
{
	if (evento == 1) t = 'Empenho Recebido';
	else if (evento == 2) t = 'Faturamento';
	else if (evento == 3) t = 'Contrato Assinado';
	else if (evento == 4) t = 'Objeto Entregue';
	else if (evento == 5) t = 'Pagamento';
	ultimateDialog({
		title:t,
		color:'gray',
		content:'<div class="ultimate-row">Inserir Data:</div><div class="ultimate-row"><input id="i-date" class="iText" type="text" placeholder="dd/mm/aaaa" maxlength="10" data-event="'+evento+'" style="width:120px;"></div><div id="ultimate-error"></div>',
		buttons:{'Inserir':{is_default:1,onclick:"insertDate();",css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
	}, function(){ $("#i-date").inputmask("d/m/y", {"placeholder":"__/__/____"}); $("#i-date").focus(); });
}

function insertDate()
{
	if (ajax)
	{
		if ($("#i-date").inputmask("unmaskedvalue").length < 8)
		{
			ultimateError('<p><span class="bold">Erro:</span> Data inválida.</p>');
			return false;
		}

		ajax = false;
		$.ajax({
			type: "post",
			url: "a.licitacao_item_datains.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id-item="+$("#cell-item").val()+"&evento="+$("#i-date").attr("data-event")+"&data="+$("#i-date").val(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;

				if (data[0] == 9)
					ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito.</p>');
				else if (data[0] == 8)
					ultimateError('<p><span class="bold">Erro:</span> Esta licitação já foi encerrada.</p>');
				else if (data[0] == 7)
					ultimateError('<p><span class="bold">Erro:</span> Data inválida.</p>');
				else if (data[0] == 6)
					ultimateError('<p><span class="bold">Erro:</span> Data já inserida.</p>');
				else if (data[0] == 1)
				{
					reloadDates($("#i-date").attr("data-event"));
					ultimateClose();
				}
				else if (data[0] == 0)
					ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
			}
		});
	}
}

function reloadDates(evento)
{
	$.ajax({
		type: "post",
		url: "a.licitacao_item_datas.php",
		data: "id-item="+$("#cell-item").val()+"&evento="+evento,
		dataType: "json",
		success: function(data)
		{
			$("#pen-date-"+evento).html(data[1]);
		}
	});
}

function remDate(id, now)
{
	if (now)
	{
		$.ajax({
			type: "post",
			url: "a.licitacao_item_datarem.php",
			data: "id-licitacao="+$("#id-licitacao").val()+"&id="+id,
			dataType: "json",
			success: function(data)
			{
				if (data[0] == 9)
					ultimateError('<p><span class="bold">Erro:</span> Acesso Restrito.</p>');
				else if (data[0] == 8)
					ultimateError('<p><span class="bold">Erro:</span> Esta licitação já foi encerrada.</p>');
				else if (data[0] == 1)
				{
					reloadDates(data[1]);
					ultimateClose();
				}
				else if (data[0] == 0)
					ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
			}
		});
	}
	else
	{
		ultimateDialog({
			title:'Remover ?',
			color:'red',
			content:'<div class="ultimate-row">A data será removida.<br><br><span class="bold">Você tem certeza que deseja continuar?</span></div><div id="ultimate-error"></div>',
			buttons:{'Sim':{onclick:'remDate('+id+',true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
		});
	}
}

//-------- end ITENS ----------





function participantes(id_item)
{
	ultimateDialog({
		title:'<span id="pt-caption">Participantes...</span>',
		width:1020,
		color:'gray',
		content:'<div id="participantes">...</div>'
	}, function(){ atualizarParticipantes(id_item); });
}

function atualizarParticipantes(id_item)
{
	$("#participantes").html('<div class="ultimate-row pt-loader"><img src="img/loader24.gif"></div>');
	$.ajax({
		type: "post",
		url: "a.participantes.php",
		data: "id-item="+id_item,
		dataType: "json",
		success: function(data)
		{
			$("#pt-caption").html(data[1]);
			$("#participantes").html(data[2]);
			if (data[3] > 0)
			{
				$("#item-"+id_item).children(".chart-btn").attr("class","chart2 chart-btn");
				$("#item-"+id_item).children(".chart-btn").attr("title","Participantes");
			}
			else
			{
				$("#item-"+id_item).children(".chart-btn").attr("class","chart1 chart-btn");
				$("#item-"+id_item).children(".chart-btn").attr("title","Inserir Resultado");
			}
		}
	});
}

function editarParticipante(id, id_item)
{
	$("#participantes").html('<div class="ultimate-row pt-loader"><img src="img/loader24.gif"></div>');
	$.ajax({
		type: "post",
		url: "a.participantes_editar.php",
		data: "id="+id+"&id-item="+id_item,
		dataType: "json",
		success: function(data)
		{
			$("#participantes").html(data[1]);
			$("#i-cnpj").inputmask("99.999.999/9999-99");
			$("#i-valor-final").maskMoney({prefix:'R$  ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
			$("#i-dn-venda").inputmask({"mask":"9","repeat":4,"greedy":false});
		}
	});
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

function salvarParticipante()
{
	$("#i-razao").val($.trim($("#i-razao").val()));
	$("#i-fabricante").val($.trim($("#i-fabricante").val()));
	$("#i-modelo").val($.trim($("#i-modelo").val()));
	$("#ultimate-error").hide();

	if ($("#i-razao").val().length == 0)
	{
		ultimateError('<p><span class="bold">Erro:</span> Informe a razão social do participante.</p>');
		$("#i-razao").focus();
		return false;
	}

	if ($("#i-cnpj").val() == "00.000.000/0000-00" || !isValidCNPJ($("#i-cnpj").val()))
	{
		ultimateError('<p><span class="bold">Erro:</span> O número CNPJ está incorreto.</p>');
		$("#i-cnpj").focus();
		return false;
	}

	if ($("#i-valor-final").inputmask("unmaskedvalue").length == 0)
	{
		ultimateError('<p><span class="bold">Erro:</span> O valor final não pode ser zero.</p>');
		$("#i-valor-final").focus();
		return false;
	}

	$("#i-salvar-box").hide();
	$("#i-processando-box").show();

	var dataString = $("#form-participante").serialize()+"&f-vencedor="+($("#i-vencedor").hasClass("check-chk1") << 0)+"&f-inabilitado="+($("#i-inabilitado").hasClass("check-chk1") << 0);
	$.ajax({
		type: "post",
		url: "a.participantes_salvar.php",
		data: dataString,
		dataType: "json",
		success: function(data)
		{
			if (data[0] == 9)
			{
				ultimateError('<p><span class="bold">Erro:</span> Acesso restrito.</p>');
				$("#i-salvar-box").show();
				$("#i-processando-box").hide();
			}
			else if (data[0] == 1)
			{
				atualizarParticipantes(data[1]);
			}
			else
			{
				ultimateError('<p><span class="bold">Erro:</span> Ocorreu um erro. Entre em contato com o administrador do sistema.</p>');
				$("#i-salvar-box").show();
				$("#i-processando-box").hide();
			}
		}
	});
}

function removerParticipante(id, id_item)
{
	$.ajax({
		type: "post",
		url: "a.participantes_remover.php",
		data: "id="+id,
		dataType: "json",
		success: function(data)
		{
			atualizarParticipantes(id_item);
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
		$("#mul-"+id_item+'-'+id_cliente).html('<div id="item-apl" style="text-align: center; line-height:30px;background-color:#ffffff;width:100%;"><span class="gray-88 italic">Carregando APL...aguarde...</span></div>');

		$.ajax({
			type:"post",
			url:"a.licitacao_item_apl_dn.php",
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

function tipo36(id)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type:"post",
			url:"a.licitacao_status_manual.php",
			data:"f-id-historico="+id,
			dataType:"json",
			success:function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				
				if (data[0] == 1)
				{
					ultimateDialog({
						title:'Detalhamento da alteração do status.',
						width:800,
						content:data[1],
						buttons:{'Ok':{is_default:1}}
					});
				}
			}
		});
	}
}

function licAPL_historico(id_item, id_cliente)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "a.licitacao_item_apl_historico.php",
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
	$("#i-search").val($.trim($("#i-search").val()));
	if ($("#i-search").val().length == 0)
		return false;

	if (ajax)
	{
		ajax = false;
		$("#search-options").hide();
		$("#search-processing").show();

		$.ajax({
			type: "POST",
			url: "a.licitacao_abrir_busca.php",
			data: "id="+$("#i-search").val(),
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

function maisFiltros(ob)
{
	if ($("#mais-filtros").is(":visible"))
	{
		$("#mais-filtros").hide();
	}
	else
	{
		$("#mais-filtros").css("left", $(ob).offset().left-418);
		$("#mais-filtros").css("top", $(ob).offset().top-10);
		$("#filtro-erro").hide();
		$("#mais-filtros").show();
	}
}

function closeDropFiltro()
{
	$("#drop-filtro").hide();
	for (i=0; i<sel.length; i++)
	{
		$("#filtro-"+i).removeClass("drop");
		if (sel[i].a.length > 0)
			$("#filtro-"+i).children("img").attr("src", "img/a-down-r.png");
		else
			$("#filtro-"+i).children("img").attr("src", "img/a-down-g.png");
	}
}

function filtroJoin(n)
{
	r = "";
	for (i=0; i<sel[n].a.length; i++)
		r += ","+sel[n].a[i].v;

	if (r.length > 0)
		r = r.substr(1);

	return r;
}

function filtroTitle(n)
{
	r = "";
	for (i=0; i<sel[n].a.length; i++)
		r += "\n"+sel[n].a[i].lb;

	if (r.length > 0)
		r = r.substr(1);

	return r;
}

function buscaFiltro(n)
{
	if (!ajax) { return false; }

	$("#filtro-erro").hide();

	if ($("#filtro-"+n).hasClass("drop"))
	{
		$("#filtro-"+n).removeClass("drop");
		if (sel[n].a.length > 0)
			$("#filtro-"+n).children("img").attr("src", "img/a-down-r.png");
		else
			$("#filtro-"+n).children("img").attr("src", "img/a-down-g.png");
		$("#drop-filtro").hide();
	}
	else
	{
		for (i=0; i<sel.length; i++)
		{
			$("#filtro-"+i).removeClass("drop");
			if (sel[i].a.length > 0)
				$("#filtro-"+i).children("img").attr("src", "img/a-down-r.png");
			else
				$("#filtro-"+i).children("img").attr("src", "img/a-down-g.png");
		}

		$("#filtro-"+n).addClass("drop");
		if (sel[n].a.length > 0)
			$("#filtro-"+n).children("img").attr("src", "img/a-up-r.png");
		else
			$("#filtro-"+n).children("img").attr("src", "img/a-up-g.png");

		$("#drop-filtro").css("top", $("#filtro-"+n).offset().top + 31);
		$("#drop-filtro").css("left", $("#filtro-"+n).offset().left);
		$("#drop-filtro").html('<span class="lh-20 italic" style="padding: 0 10px;">Carregando...</span>');
		$("#drop-filtro").show();
		ajax = false;

		if (n == 2)
			$("#drop-filtro").load("a.drop-filtro.php?d="+n+"&v="+filtroJoin(n)+"&e="+filtroJoin(1), function(){ ajax = true; });
		else
			$("#drop-filtro").load("a.drop-filtro.php?d="+n+"&v="+filtroJoin(n), function(){ ajax = true; });
	}
}

function isSelected(n, v)
{
	for (i=0; i<sel[n].a.length; i++)
	{
		if (sel[n].a[i].v == v)
		{
			return i;
			break;
		}
	}
	return -1;
}

function atualizaFiltro(n, open)
{
	ud = 'down';
	if (open) ud = 'up';

	if (sel[n].a.length > 0)
	{
		$("#filtro-"+n).removeClass("filtro0").addClass("filtro1");
		$("#filtro-"+n).attr("title",filtroTitle(n));
		if (n == 4)
			$("#filtro-"+n).html(sel[n].a[0].lb+'<img src="img/a-'+ud+'-r.png">');
		else
			$("#filtro-"+n).html(sel[n].a.length+" "+tex[n][1]+'<img src="img/a-'+ud+'-r.png">');

		$("#limpa-"+n).show();
	}
	else
	{
		$("#filtro-"+n).removeClass("filtro1").addClass("filtro0");
		$("#filtro-"+n).attr("title","");
		$("#filtro-"+n).html(tex[n][0]+'<img src="img/a-'+ud+'-g.png">');
		$("#limpa-"+n).hide();
	}
}

function selItem(ob, v, n, lb, uf)
{
	idx = isSelected(n, v);
	if (idx > -1)
	{
		sel[n].a.splice(idx, 1);
		$(ob).removeClass("drop-item1").addClass("drop-item0");
		if (n == 1) //estado
			atualizarCidadesFiltro();
	}
	else
	{
		if (n == 4)
		{
			sel[n].a = [{v:v,lb:lb,uf:uf}];
			$(".ult").removeClass("drop-item1").addClass("drop-item0");
		}
		else
			sel[n].a.push({v:v,lb:lb,uf:uf});

		$(ob).removeClass("drop-item0").addClass("drop-item1");
	}

	atualizaFiltro(n, true);
}

function atualizarCidadesFiltro()
{
	estados = [];
	cidades_new_array = [];

	for (i=0; i<sel[1].a.length; i++)
		estados.push(sel[1].a[i].v); 

	for (i=0; i<sel[2].a.length; i++)
		if (estados.indexOf(sel[2].a[i].uf) > -1)
			cidades_new_array.push(sel[2].a[i]);

	sel[2].a = cidades_new_array;

	if (sel[2].a.length > 0)
	{
		$("#filtro-2").removeClass("filtro0").addClass("filtro1");
		$("#filtro-2").attr("title",filtroTitle(2));
		$("#filtro-2").html(sel[2].a.length+" "+tex[2][1]+'<img src="img/a-down-r.png">');
		$("#limpa-2").show();
	}
	else
	{
		$("#filtro-2").removeClass("filtro1").addClass("filtro0");
		$("#filtro-2").attr("title","");
		$("#filtro-2").html(tex[2][0]+'<img src="img/a-down-g.png">');
		$("#limpa-2").hide();
	}
}

function limpaFiltro(n)
{
	sel[n].a = [];
	$("#filtro-"+n).removeClass("filtro1").addClass("filtro0");
	$("#filtro-"+n).attr("title","");
	$("#filtro-"+n).html(tex[n][0]+'<img src="img/a-down-g.png">');
	$("#limpa-"+n).hide();
	closeDropFiltro();
	if (n == 1)
		atualizarCidadesFiltro();
}

function buscarF()
{
	$("#filtro-erro").hide();

	$("#filtro-op").val($.trim($("#filtro-op").val()));
	$("#filtro-adve").val($.trim($("#filtro-adve").val()));
	$("#filtro-numero").val($.trim($("#filtro-numero").val()));
	if ($("#filtro-adve").val() == 0)
		$("#filtro-adve").val("");

	var a = [];
	a.push($("#filtro-op"));
	a.push($("#filtro-adve"));
	a.push($("#filtro-numero"));

	for (i=0; i<a.length; i++)
	{
		if (a[i].val().length > 0)
		{
			$(a[i]).css("color", "#ff0000");
			$(a[i]).css("border-color", "#ee0000");
		}
		else
		{
			$(a[i]).css("color", "#282828");
			$(a[i]).css("border-color", "#cccccc");
		}
	}

	if (sel[0].a.length == 0 && sel[1].a.length == 0 && sel[2].a.length == 0 && sel[3].a.length == 0 && sel[4].a.length == 0 && sel[5].a.length == 0 && $("#filtro-op").val().length == 0 && $("#filtro-adve").val().length == 0 && $("#filtro-numero").val().length == 0 && $("#filtro-data-de").inputmask("unmaskedvalue").length < 8 && $("#filtro-data-ate").inputmask("unmaskedvalue").length < 8)
	{
		$("#filtro-erro").fadeIn(260).fadeOut(220).fadeIn(180).fadeOut(140).fadeIn(100);
		return false;
	}

	if (ajax)
	{
		$("#mais-filtros").hide();
		ajax = false;
		$("#search-options").hide();
		$("#search-processing").show();

		$.ajax({
			type: "POST",
			url: "a.licitacao_abrir_buscaf.php",
			data: "f-dn="+filtroJoin(5)+"&f-status="+filtroJoin(0)+"&f-estados="+filtroJoin(1)+"&f-cidades="+filtroJoin(2)+"&f-regioes="+filtroJoin(3)+"&f-ultimas="+filtroJoin(4)+"&f-orgao="+encodeURIComponent($("#filtro-op").val())+"&f-adve="+$("#filtro-adve").val()+"&f-numero="+encodeURIComponent($("#filtro-numero").val())+"&f-data-de="+$("#filtro-data-de").val()+"&f-data-ate="+$("#filtro-data-ate").val(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				window.location.href = "a.licitacao.php";
			}
		});
	}
}

function pregoeiroChama()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type:"post",
			url:"a.licitacao_abrir_pc.php",
			data:"action=read&id-licitacao="+$("#id-licitacao").val(),
			dataType:"json",
			success:function(data)
			{
				ajax = true;
				ultimateLoader(false, '');

				if (data[0] == 9)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
				{
					mon_users = JSON.parse(data[2]);
					ultimateDialog({
						title:data[5],
						color:data[4],
						width:900,
						content:data[1],
						buttons:JSON.parse(data[3])
					});
				}
				else if (data[0] == 2)
					ultimateDialog({title:'Erro.',color:'red',content:data[1],buttons:{'Ok':{is_default:1}}});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function monREMOVE(id, ntf)
{
	for (i=0; i<mon_users.length; i++)
	{
		if (mon_users[i].id == id)
		{
			if (mon_users[i].ntf.length == 1 && mon_users[i].ntf.indexOf(ntf) > -1)
			{
				mon_users.splice(i,1);
			}
			else
			{
				if (ntf == 1)
					mon_users[i].ntf = [2];
				else
					mon_users[i].ntf = [1];
			}
			break;
		}
	}
}

function monADD(id, ntf)
{
	var idx = -1;
	for (i=0; i<mon_users.length; i++)
		if (mon_users[i].id == id)
			idx = i;

	if (idx > -1)
	{
		if (mon_users[idx].ntf.indexOf(ntf) < 0)
			mon_users[idx].ntf.push(ntf);
	}
	else
	{
		mon_users.push({id:id,ntf:[ntf]});	
	}
}

function monUser(ob, id, ntf)
{
	if ($(ob).hasClass("mon-cb0"))
	{
		$(ob).removeClass("mon-cb0").addClass("mon-cb1");
		monADD(id, ntf);
	}
	else
	{
		$(ob).removeClass("mon-cb1").addClass("mon-cb0");
		monREMOVE(id, ntf);
	}
}

function pregoeiroChamaATIVAR()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type:"post",
			url:"a.licitacao_abrir_pc.php",
			data:"action=ativar&id-licitacao="+$("#id-licitacao").val()+"&users="+JSON.stringify(mon_users),
			dataType:"json",
			success:function(data)
			{
				ajax = true;
				ultimateLoader(false, '');

				if (data[0] == 9)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
				{
					$("#radar").attr("class","radar-on");
					$("#radar").children("img").attr("src","img/radar-on.gif");
				}
				else if (data[0] == 2)
					ultimateDialog({title:'Erro.',color:'red',content:data[1],buttons:{'Ok':{is_default:1}}});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function pregoeiroChamaDESATIVAR()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type:"post",
			url:"a.licitacao_abrir_pc.php",
			data:"action=desativar&id-licitacao="+$("#id-licitacao").val(),
			dataType:"json",
			success:function(data)
			{
				ajax = true;
				ultimateLoader(false, '');

				if (data[0] == 9)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
				{
					$("#radar").attr("class","radar-off");
					$("#radar").children("img").attr("src","img/radar-off.png");
				}
				else if (data[0] == 2)
				{
					ultimateDialog({title:'Erro.',color:'red',content:data[1],buttons:{'Ok':{is_default:1}}});
					$("#radar").attr("class","radar-off");
					$("#radar").children("img").attr("src","img/radar-off.png");
				}
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function pregoeiroChamaMensagens()
{
	if (pcmsg)
	{
		pcmsg = false;
		$.ajax({
			type: "post",
			url: "a.licitacao_abrir_pc_mensagens.php",
			data: "id-licitacao="+$("#id-licitacao").val(),
			dataType: "json",
			success: function(data)
			{
				pcmsg = true;
				$("#pc-count").html(data[2]);
				$("#pc-conteudo").html(data[1]);
			}
		});
	}
}

function pregoeiroChamaDetalhesEnvio(id)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true, '');
		$.ajax({
			type:"post",
			url:"a.licitacao_abrir_pc_detalhes.php",
			data:"id="+id,
			dataType:"json",
			success:function(data)
			{
				ajax = true;
				ultimateLoader(false, '');
				if (data[0] == 9)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					ultimateDialog({title:'Detalhamento de envios',width:900,color:"gray",content:data[1],buttons:{'Ok':{is_default:1}}});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

function Switch(ob,on)
{
	if (!on)
	{
		$(ob).attr("class", "switch s-on");
		$(ob).children(".disc").animate({left:'2px'}, 200, function(){
			$(ob).removeClass("s-on").addClass("s-off");
		});
	}
	else
	{
		$(ob).attr("class", "switch s-off");
		$(ob).children(".disc").animate({left:'32px'}, 200, function(){
			$(ob).removeClass("s-off").addClass("s-on");
		});
	}
}

function SwitchProcess(ob)
{
	if ($(ob).hasClass("s-loader"))
		return false;

	if (ajax)
	{
		ajax = false;
		var saved_class = $(ob).attr("class");
		$(ob).attr("class","switch s-loader");

		$.ajax({
			type: "post",
			url: "a.licitacao_abrir_switch.php",
			data: "id-licitacao="+$("#id-licitacao").val(),
			dataType: "json",
			success: function(data)
			{
				if (data[0] == 9)
				{
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					$(ob).attr("class",saved_class);
				}
				else if (data[0] == 2)
				{
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Nenhuma APL encontrada.',buttons:{'Ok':{is_default:1}}});
					$(ob).attr("class",saved_class);
				}
				else if (data[0] == 1)
				{
					Switch(ob,data[1]);
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
					$(ob).attr("class",saved_class);
				}

				ajax = true;
			}
		});
	}
}

function atualizarCheckLic(ob)
{
	if (ajax)
	{
		ajax = false;
		$(ob).children("img").show();
		var dataString = "id-licitacao="+$("#id-licitacao").val()+"&field="+$(ob).attr("data-field")+"&checked="+($(ob).hasClass("itm-cb1") << 0);
		$.ajax({
			type: "post",
			url: "a.licitacao_check.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$(ob).children("img").hide();
				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta licitação já foi encerrada.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					$(ob).attr("class",data[1]);
				else if (data[0] == 0)
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
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
			url: "a.licitacao_abrir_editais.php",
			data: "id="+$("#id-licitacao").val(),
			dataType: "json",
			success: function(data)
			{
				$("#drop-editais-content").html(data[1]);
				var lf = Math.round($("#drop-editais").offset().left + $("#drop-editais").outerWidth() - 685);
				$("#drop-editais-content").css("left", lf+"px");
				ajax = true;
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

