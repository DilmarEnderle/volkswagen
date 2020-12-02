var rtext = '---{^=?!?=^}0';

$(document).ready(function(){
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

	atualizarHistorico(true);
});

function atualizarHistorico(inicial)
{
	if (inicial)
		$("#historico").html('<span class="italic">Carregando mensagens...</span>');

	$.ajax({
		type:"post",
		url:"a.compradir_historico.php",
		data:"id-cd="+$("#id-cd").val(),
		dataType:"json",
		success:function(data)
		{
			$("#historico").html(data[1]);
			if (!inicial)
			{
				cancelUploadMSG();
				tinyMCE.activeEditor.setContent('');
				$("#enviar-box").show();
				$("#processando-box").hide();
			}
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
			xhr.open("POST", "a.upload_compradir_mensagem.php");
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
	if (rtext.split('{^=?!?=^}')[0] == 'ERRO')
		cancelUploadMSG();
	else
	{
		$("#upl-loading").hide();
		$("#upl-filename").html(rtext.split('{^=?!?=^}')[0]);
		$("#upl-filesize").html(rtext.split('{^=?!?=^}')[1]);
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
	var msg = $.trim(tinymce.activeEditor.getContent());

	if (msg.length == 0 && rtext.split('{^=?!?=^}')[0] == '---')
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite a mensagem ou anexe um arquivo antes de enviar.',buttons:{'Ok':{is_default:1,onclick:"tinymce.execCommand('mceFocus',false,'i-mensagem');ultimateClose();"}}});
		return false;
	}
	
	if (ajax)
	{
		ajax = false;
		$("#enviar-box").hide();
		$("#processando-box").show();
		var dataString = "id-cd="+$("#id-cd").val()+"&mensagem="+encodeURIComponent(msg)+"&anexo="+rtext.split('{^=?!?=^}')[0];
		$.ajax({
			type: "POST",
			url: "a.compradir_envia_mensagem.php",
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

function autorizarAPL(id, now)
{
	if (ajax)
	{
		if (now)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "post",
				url: "a.compradir_autorizar.php",
				data: "id-cd="+id,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 1)
					{
						$(".cd-info span").last().remove();
						$(".cd-info").append('<span class="fl green bold">O envio da APL foi autorizado.</span>');
						$(".bt-autorizar").remove();
						$(".bt-nautorizar").remove();
						atualizarHistorico(false);
					}
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});					
				}
			});
		}
		else
		{
			ultimateDialog({
				title:'Autorizar envio da APL',
				color:'green',
				content:'<span class="bold">Confirmar autorização?</span>',
				buttons:{'Sim':{onclick:'autorizarAPL('+id+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
			});
		}
	}
}

function nautorizarAPL(id, now)
{
	if (ajax)
	{
		if (now)
		{
			var m = $.trim($("#i-motivo").val());
			if (m.length == 0)
			{
				ultimateError('<p><span class="bold">Erro:</span> Informe o motivo.</p>');
				$("#i-motivo").focus();
				return false;
			}

			ajax = false;
			ultimateClose();
			ultimateLoader(true,'');
			$.ajax({
				type: "post",
				url: "a.compradir_nautorizar.php",
				data: "id-cd="+id+"&motivo="+encodeURIComponent(m),
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 1)
					{
						$(".cd-info span").last().remove();
						$(".cd-info").append('<span class="fl red bold">O envio da APL não foi autorizado.</span>');
						$(".bt-autorizar").remove();
						$(".bt-nautorizar").remove();
						atualizarHistorico(false);
					}
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});					
				}
			});
		}
		else
		{
			ultimateDialog({
				title:'Não autorizar envio da APL',
				color:'gray',
				content:'<div class="ultimate-row"><span style="line-height:21px;">Motivo</span></div><div class="ultimate-row"><textarea id="i-motivo" class="iText" placeholder="- descreva o motivo -" style="width:100%;height:100px;padding:2px 4px;resize:none;font-family:Arial;"></textarea></div><div id="ultimate-error"></div>',
				buttons:{'Continuar':{onclick:'nautorizarAPL('+id+',true);',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
			}, function(){ $("#i-motivo").focus(); });
		}
	}
}

function cdAPL(id)
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
			url:"a.compradir_apl.php",
			data:"id-cd="+id,
			dataType:"json",
			success:function(data)
			{
				$("#apl").html(data[1]);
				$("#i-doc-cpf").inputmask("999.999.999-99");
				$("#i-valor-1").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
				$("#i-valor-2").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
				$("#i-valor-3").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
				$("#i-valor-4").maskMoney({prefix:'R$ ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
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


