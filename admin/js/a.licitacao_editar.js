var pasted = false;
var iRepeat = 0;
var pfn = "";
var selection = 0;
var vi = "";
var insert_ob = "";
var jcrop;
var verif_dup = 1;

var uploads = [];
var return_upload = {};

$(document).ready(function(){

	$(document).keydown(function(e){
		if (e.keyCode == 27 && !$("#loader_box").is(":visible"))
			pasteAreaOff();
	});

	$(document).on("blur", ".data-publicacao", function()
	{
		idx = parseInt($(this).parent().attr("id").split("-")[1]);
		uploads[idx].publish_date = $(this).val();
	});

	$("#i-data-abertura").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#i-hora-abertura").inputmask("hh:mm", {"placeholder":"__:__"});
	$("#i-data-entrega").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#i-hora-entrega").inputmask("hh:mm", {"placeholder":"__:__"});
	$("#i-valor").maskMoney({prefix:'R$  ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
	$("#i-prazo-entrega").inputmask({"mask":"9","repeat":4,"greedy":false});
	$("#i-uasg").inputmask({"mask":"9","repeat":10,"greedy":false});
	$(".data-publicacao").inputmask("d/m/y", {"placeholder":"__/__/____"});

	$("#receiver").on("paste", function(){
		pasted = true;
		iRepeat = setInterval(function(){waitforpastedata()},20);

		if (event.clipboardData && event.clipboardData.getData)
		{
			var items = event.clipboardData.items;
			if (items)
			{
				clearInterval(iRepeat);
				pasted = false;
				$("#receiver").html("");
			
				for (var i = 0; i < items.length; i++)
				{
					if (items[i].type.indexOf("image") !== -1)
					{
						var blob = items[i].getAsFile();
						var URLObj = window.URL || window.webkitURL;
						var source = URLObj.createObjectURL(blob);
   	             				var xhr = new XMLHttpRequest;
						xhr.responseType = 'blob';
						xhr.onload = function(){
							var recoveredBlob = xhr.response;
							var reader = new FileReader;
							reader.onload = function(event){
								$("#paste_box").hide();
								$("#loader_box").show();

								$.ajax({
									type: "POST",
									url: "a.colar_imagem.php",
									data: "pfn="+pfn+"&imgdata="+reader.result,
									dataType: "json",
									success: function(data)
									{
										pfn = data[0];
										if (vi.indexOf(data[0]) < 0) vi += ","+data[0];
										
										if (data[1] > 300)
										{
											$("#preview_box").css("width",data[1]+10);
										}
										else
										{
											$("#preview_box").css("width",310);
										}
										$("#preview_box").css("height",data[2]+60);
										$("#preview_box").css("top",data[3]);
										$("#preview_img").attr("src","../arquivos/"+data[0]);
										$("#preview_img").css("width",data[1]);
										$("#preview_img").css("height",data[2]);
										$("#loader_box").hide();
										$("#paste_box").hide();
										$("#preview_box").show();
										$("#preview_img").Jcrop({},function(){ jcrop = this; });
										$("#ok_btn").focus();
									}
								});
							};
							reader.readAsDataURL(recoveredBlob);
						};
						xhr.open('GET', source);
						xhr.send();
					}
				}
			}
		}	
	});

	if ($("#i-id").val() > 0)
		listarDNs();
});


function ckSingle(ob, cl)
{
	$(".cl-"+cl).attr("class","check-dot0 cl-"+cl);
	if ($(ob).hasClass("check-dot0"))
		$(ob).removeClass("check-dot0").addClass("check-dot1");
}


function waitforpastedata()
{
	if (pasted && $("#receiver").html().length > 0)
	{
		clearInterval(iRepeat);
		pasted = false;
		var dataString = $("#receiver").html();
		$("#receiver").html("");
		$("#paste_box").hide();
		$("#loader_box").show();

		alert("pfn="+pfn+"&imgdata="+dataString);

		$.ajax({
			type: "POST",
			url: "a.colar_imagem.php",
			data: "pfn="+pfn+"&imgdata="+dataString,
			dataType: "json",
			success: function(data)
			{
				pfn = data[0];
				if (vi.indexOf(data[0]) < 0) vi += ","+data[0];

				if (data[1] > 300)
				{
					$("#preview_box").css("width",data[1]+10);
				}
				else
				{
					$("#preview_box").css("width",310);
				}
				$("#preview_box").css("height",data[2]+60);
				$("#preview_box").css("top",data[3]);
				$("#preview_img").attr("src","../arquivos/"+data[0]);
				$("#preview_img").css("width",data[1]);
				$("#preview_img").css("height",data[2]);
				$("#loader_box").hide();
				$("#paste_box").hide();
				$("#preview_box").show();
				$("#preview_img").Jcrop({},function(){ jcrop = this; });
				$("#ok_btn").focus();
			}
		});
    }
}

function salvarLicitacao()
{
	$("#i-orgao").val($.trim($("#i-orgao").val()));
	$("#i-link").val($.trim($("#i-link").val()));
	$("#i-numero").val($.trim($("#i-numero").val()));
	$("#i-fonte").val($.trim($("#i-fonte").val()));
	$("#i-numero-rastreamento").val($.trim($("#i-numero-rastreamento").val()));
	$("#i-vigencia-contrato").val($.trim($("#i-vigencia-contrato").val()));
	
	if ($("#i-orgao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do órgão público.',buttons:{'ok':{is_default:1,onclick:"foc('i-orgao');ultimateClose();"}}});
		return false;
	}

	if ($("#i-objeto").html().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o objeto da licitação.',buttons:{'ok':{is_default:1,onclick:"foc('i-objeto');ultimateClose();"}}});
		return false;
	}

	if ($("#i-modalidade").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a modalidade.',buttons:{'ok':{is_default:1,onclick:"foc('i-modalidade');ultimateClose();"}}});
		return false;
	}

	if ($("#i-instancia").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a instância.',buttons:{'ok':{is_default:1,onclick:"foc('i-instancia');ultimateClose();"}}});
		return false;
	}

	if ($("#i-estado").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o estado.',buttons:{'ok':{is_default:1,onclick:"foc('i-estado');ultimateClose();"}}});
		return false;
	}

	if ($("#i-cidade").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a cidade.',buttons:{'ok':{is_default:1,onclick:"foc('i-cidade');ultimateClose();"}}});
		return false;
	}

	if ($("#i-data-abertura").inputmask("unmaskedvalue").length < 8)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a Data de Abertura corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-data-abertura');ultimateClose();"}}});
		return false;
	}

	if ($("#i-hora-abertura").inputmask("unmaskedvalue").length > 0 && $("#i-hora-abertura").inputmask("unmaskedvalue").length < 4)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a Hora de Abertura corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-hora-abertura');ultimateClose();"}}});
		return false;
	}

	if ($("#i-data-entrega").inputmask("unmaskedvalue").length < 8)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a Data de Entrega das Propostas corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-data-entrega');ultimateClose();"}}});
		return false;
	}

	if ($("#i-hora-entrega").inputmask("unmaskedvalue").length > 0 && $("#i-hora-entrega").inputmask("unmaskedvalue").length < 4)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a Hora de Entrega corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-hora-entrega');ultimateClose();"}}});
		return false;
	}

	if ($("#i-numero").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o número da licitação.',buttons:{'ok':{is_default:1,onclick:"foc('i-numero');ultimateClose();"}}});
		return false;
	}

	if ($("#i-srp").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione É SRP.',buttons:{'ok':{is_default:1,onclick:"foc('i-srp');ultimateClose();"}}});
		return false;
	}

	if ($("#i-prazo-entrega").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o Prazo de Entrega (em dias).',buttons:{'ok':{is_default:1,onclick:"foc('i-prazo-entrega');ultimateClose();"}}});
		return false;
	}

	if ($("#i-prazo-entrega-uteis").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe no Prazo de Entrega. Dias úteis ou Dias Corrigos.',buttons:{'ok':{is_default:1,onclick:"foc('i-prazo-entrega-uteis');ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		var dataString = $("#licitacao-form").serialize()+ "&f-objeto=" + encodeURIComponent($("#i-objeto").html()) + "&f-importante=" + encodeURIComponent($("#i-importante").html()) + "&editais=" + encodeURIComponent(JSON.stringify(uploads)) + "&vi=" + vi + "&verif_dup="+verif_dup;
		$.ajax({
			type: "POST",
			url: "a.licitacao_salvar.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				ajax = true;

				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 1)
				{
					window.location.href = "a.licitacao_editar.php?id="+data[1]+"&fr="+$("#i-back-to-id").val();
				}
				else if (data[0] == 2)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'A Hora de Abertura está incorreta.<br><br>Verifique o formato e tente novamente.<br><span class="italic">hh:mm 24 horas</span>',buttons:{'ok':{is_default:1,onclick:"foc('i-hora-abertura');ultimateClose();"}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 3)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'A Data de Abertura está incorreta.<br>A Data de Abertura precisa ser atual ou uma data futura.',buttons:{'ok':{is_default:1,onclick:"foc('i-data-abertura');ultimateClose();"}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 4)
				{
					//ultimateDialog({title:'Erro.',color:'red',content:'A Data da Entrega das Propostas está incorreta.<br><br>A Data da Entrega das Propostas precisa ser uma data futura menor ou igual à Data de Abertura.',buttons:{'ok':{is_default:1,onclick:"foc('i-data-entrega');ultimateClose();"}}});
					ultimateDialog({title:'Erro.',color:'red',content:'A Data da Entrega das Propostas está incorreta.<br><br>A Data da Entrega das Propostas precisa ser uma data menor ou igual à Data de Abertura.',buttons:{'ok':{is_default:1,onclick:"foc('i-data-entrega');ultimateClose();"}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 5)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Não existem '+$("#i-prazo-limite").val()+' dias úteis disponíveis antes da Data de Entrega das Propostas.',buttons:{'ok':{is_default:1,onclick:"foc('i-prazo-limite');ultimateClose();"}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 6)
				{
					ultimateDialog({
						title: 'Aviso!',
						width: 800,
						color: 'red',
						content: data[1],
						buttons: {
							'Ignorar':{onclick:"salvarLicitacaoIgn();",css_class:'ultimate-btn-red ultimate-btn-left'},
							'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}
						}
					});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 7)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Dados incorretos.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 8)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'A Hora de Entrega está incorreta.<br><br>Verifique o formato e tente novamente.<br><span class="italic">hh:mm 24 horas</span>',buttons:{'ok':{is_default:1,onclick:"foc('i-hora-abertura');ultimateClose();"}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 20)
				{
					ultimateDialog({title:'Erro.',color:'red',content:data[1],buttons:{'ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
			}
		});
	}
}

function salvarLicitacaoIgn()
{
	verif_dup = 0;
	salvarLicitacao();
}

function listarCidades()
{
	$("#i-cidade").hide();
	$("#loader").show();

	$.ajax({
		type: "GET",
		url: "a.cidades.php",
		data: "f-estado="+$("#i-estado").val(),
		dataType: "text",
		success: function(data)
		{
			$("#i-cidade").html(data);
			$("#i-cidade").show();
			$("#loader").hide();
		}
	});
}

function listarDNs()
{
	$("#dn").html('<span class="gray-88 italic" style="line-height:24px;">Carregando DN\'s...</span>');
	$.ajax({
		type:"post",
		url:"a.dn.php",
		data:"f-cidade="+$("#i-cidade").val(),
		dataType:"text",
		success:function(data)
		{
			$("#dn").html(data);
		}
	});
}

//-------- file upload edital ----------
function selectFile()
{
	if (!$("#upload-form").length)
		$('body').append('<form id="upload-form" enctype="multipart/form-data"></form>');

	$("#upload-form").html('<input id="upload-form-button" class="file-upload" type="file" name="f-upload" onchange="uploadFile();">');
	$("#upload-form-button").click();
}

function uploadFile()
{
	var file = $("#upload-form-button")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload", $("#upload-form-button")[0].files[0]);
			xhr.upload.addEventListener("progress", uploadProgress, false);
			xhr.addEventListener("load", uploadComplete, false);
			xhr.addEventListener('readystatechange', function()
			{
				if (this.readyState == 4)
					return_upload = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "a.upload_edital.php");
			xhr.send(fd);

			$("#upload-box").hide();
			$("#upload-loading").children("div").css("width", 0);
			$("#upload-loading").children("span").html('Carregando...');
			$("#upload-loading").show();
		}
		else
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O arquivo selecionado é muito grande.<br><br>Tamanho máximo permitido: <span class="bold">100 MB</span>',buttons:{'ok':{is_default:1}}});
		}
	}
}

function uploadProgress(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var b = Math.round(664 * percentComplete / 100);
		$("#upload-loading").children("div").css("width", b+'px');
		$("#upload-loading").children("span").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upload-loading").children("div").css("width", "664px");
	}
}

function uploadComplete()
{
	if (return_upload.status == 0)
	{
		//upload error
		$("#upload-loading").hide();
		$("#upload-box").show();
	}
	else
	{
		//upload success
		uploads.push(return_upload);
		return_upload = {};
		current_upload = uploads.length - 1;

		$("#upload-ready-header").show();
		$("#upload-box").before('<div id="edital-'+current_upload+'" class="upload-ready" style="display:block;">'+
			'<div class="file">'+
				'<img src="img/file.png" style="float:left;margin-left:6px;margin-top:4px;border:0;">'+
				'<span class="filename t13 red italic fl" style="margin-left:4px;line-height:32px;">'+uploads[current_upload].short_filename+'</span>'+
				'<span class="filesize gray-4c italic t11 fr" style="line-height:32px;margin-right:6px;">'+uploads[current_upload].file_size+'</span>'+
			'</div>'+
			'<input class="data-publicacao iText fl" type="text" placeholder="dd/mm/aaaa" style="width:150px;margin-left:4px;">'+
			'<div style="float:left;width:36px;height:34px;">'+
				'<a class="btn-x24" href="javascript:void(0);" onclick="removeUpload('+current_upload+');" style="right: 4px; top: 4px;" title="Remover"></a>'+
			'</div>'+
		'</div>');

		$(".data-publicacao").inputmask("d/m/y", {"placeholder":"__/__/____"});
		$("#upload-loading").hide();
		$("#upload-box").show();
	}
}

function removeUpload(idx)
{
	if (uploads[idx].status == 1)
		uploads[idx].status = 2; //remove file only
	else if (uploads[idx].status == 3)
		uploads[idx].status = 4; //remove file plus db reference

	$("#edital-"+idx).remove();

	found = false;
	for (i=0; i<uploads.length; i++)
	{
		if (uploads[i].status == 1 || uploads[i].status == 3)
		{
			found = true;
			break;
		}
	}

	if (!found)
		$("#upload-ready-header").hide();
}
//------- end upload edital -----------


function pasteAreaOn(ob)
{
	insert_ob = ob;
	$("#i_"+insert_ob).focus();
	selection = saveSelection();
	clearInterval(iRepeat);
	pasted = false;
	$("#dim_dialog_1").css("height", $(document).height());
	var new_top = $(window).scrollTop() + 200;
	$("#paste_box").css("top", new_top);
	$("#dim_dialog_1").show();
	$("#loader_box").hide();
	$("#preview_box").hide();
	$("#paste_box").show();
	$("#receiver").focus();
}

function pasteAreaOff()
{
	clearInterval(iRepeat);
	pasted = false;
	if (typeof(jcrop) == 'object')
		jcrop.destroy();
	$("#dim_dialog_1").hide();
}

function toReceiver()
{
	$("#receiver").focus();
}

function pasteOk()
{
	$("#preview_box").hide();
	$("#loader_box").show();
	var c = jcrop.tellSelect();
	$.ajax({
		type: "GET",
		url: "a.crop_imagem.php?im="+pfn+"&x1="+c.x+"&y1="+c.y+"&x2="+c.x2+"&y2="+c.y2+"&sw="+$("#preview_img").width()+"&sh="+$("#preview_img").height(),
		dataType: "text",
		success: function(data)
		{
			pasteAreaOff();
			restoreSelection(selection);
			insertAtCursor();
		}
	});
}

function saveSelection()
{
	var sel = window.getSelection();
	if (sel.getRangeAt && sel.rangeCount)
	{
		return sel.getRangeAt(0);
	}
	return null;
}

function restoreSelection(range)
{
	if (range)
	{
		var sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange(range);
    }
}

function insertAtCursor()
{
	$("#i_"+insert_ob).focus();
	var sel = window.getSelection();
	var range = sel.getRangeAt(0);
	range.deleteContents();
	var im = document.createElement("img");
	$(im).attr("src","../arquivos/"+pfn+"?"+new Date().getTime());
	range.insertNode(im);
	pfn = "";
}

function inserirModalidade(now)
{
	if (!ajax) return false;

	if (now)
	{
		$("#i-nome").val($.trim($("#i-nome").val()));
		$("#i-abv").val($.trim($("#i-abv").val()));
		$("#ultimate-error").html('');

		if ($("#i-nome").val().length<3)
		{
			ultimateError('<p><span class="bold">Erro:</span> Digite o nome da modalidade corretamente.</p>');
			$("#i-nome").focus();
			return false;
		}

		if ($("#i-abv").val() == 0)
		{
			ultimateError('<p><span class="bold">Erro:</span> Digite a abreviação.</p>');
			$("#i-abv").focus();
			return false;
		}

		ajax = false;
		$.ajax({
			type: "POST",
			url: "a.modalidade_salvar.php",
			data: "f-id=0&f-nome="+encodeURIComponent($("#i-nome").val())+"&f-abv="+encodeURIComponent($("#i-abv").val())+"&gl=1",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					ultimateError('<p><img src="img/padlock.png" style="display:inline;width:9px;margin-right:4px;"> Acesso restrito.</p>');
				}
				else if (data[0] == 8)
				{
					ultimateError('<p><span class="bold">Erro:</span> Abreviação já utilizada por outra modalidade.</p>');
				}
				else if (data[0] == 1)
				{
					$("#i-modalidade").html(data[1]);
					ultimateClose();
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			}
		});
	}
	else
	{
		ultimateDialog({
			title: 'Inserir Modalidade',
			width: 540,
			color: 'gray',
			content: '<div class="ultimate-row">Nome</div><div class="ultimate-row"><input id="i-nome" class="iText" type="text" placeholder="- nome da modalidade -" name="f-nome" maxlength="50" style="width:100%;"></div><div class="ultimate-row" style="margin-top:14px;">Abreviação</div><div class="ultimate-row"><input id="i-abv" class="iText" type="text" placeholder="- abv -" name="f-abv" maxlength="6" style="width: 200px;"></div><div id="ultimate-error"></div>',
			buttons: {
				'Inserir':{onclick:"inserirModalidade(true);",css_class:'ultimate-btn-red ultimate-btn-left'},
				'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}
			}
		}, function(){ $("#i-nome").focus(); });
	}
}
