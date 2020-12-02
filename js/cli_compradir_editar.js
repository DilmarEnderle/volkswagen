var rtext = '';

$(document).ready(function(){
	$("#i-data-srp").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#i-valor").maskMoney({prefix:'R$  ', allowNegative: false, thousands:'.', decimal:',', affixesStay: true});
	$("#i-quantidade").inputmask({"mask":"9","repeat":10,"greedy":false});
	rtext = rtext_tmp;
});

function ckTipo(ob, cl, vl)
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

	if (vl == 1)
	{
		$("#n-srp").hide();
		$("#d-srp").hide();
	}
	else
	{
		$("#n-srp").show();
		$("#d-srp").show();
	}
}


//-------- file upload ----------
function selectFile()
{
	$("#upload-form").html('<input id="upload-button" class="file-upload" type="file" name="f-upload" onchange="uploadFile();">');
	$("#upload-button").click();
}

function uploadFile()
{
	var file = $("#upload-button")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload", $("#upload-button")[0].files[0]);
			xhr.upload.addEventListener("progress", uploadProgress, false);
			xhr.addEventListener("load", uploadComplete, false);
			xhr.addEventListener('readystatechange', function()
			{
				if (this.readyState == 4)
					rtext = xhr.responseText;
			});
			xhr.open("POST", "c.upload_compradir.php");
			xhr.send(fd);
			$("#anexo-btn").hide();
			$("#anexo-loading-bar").css("width", 0);
			$("#anexo-loading-per").html('Carregando...');
			$("#anexo-loading").show();
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
		var b = Math.round(536 * percentComplete / 100);
		$("#anexo-loading-bar").css("width", b+'px');
		$("#anexo-loading-per").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#anexo-loading-bar").css("width", "536px");
	}
}

function uploadComplete()
{
	if (rtext.split('{^=?!?=^}')[0] == 'ERRO')
		cancelUpload();
	else
	{
		$("#anexo-ready-filename").html(rtext.split('{^=?!?=^}')[0]);
		$("#anexo-ready-filesize").html(' '+rtext.split('{^=?!?=^}')[1]);
		$("#anexo-loading").hide();
		$("#anexo-ready").show();
	}
}

function cancelUpload()
{
	rtext = '';
	$("#anexo-ready").hide();
	$("#anexo-loading").hide();
	$("#anexo-btn").show();
}
//------- end file upload -----------


function salvarSolicitacao()
{
	$("#i-orgao").val($.trim($("#i-orgao").val()));
	$("#i-numero-srp").val($.trim($("#i-numero-srp").val()));
	$("#i-descritivo").val($.trim($("#i-descritivo").val()));
	
	if ($("#i-orgao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do órgão público.',buttons:{'ok':{is_default:1,onclick:"foc('i-orgao');ultimateClose();"}}});
		return false;
	}

	if ($("#i-descritivo").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o descritivo do veículo.',buttons:{'ok':{is_default:1,onclick:"foc('i-descritivo');ultimateClose();"}}});
		return false;
	}

	if ($("input[name='f-tipo']").val() == 2 && $("#i-numero-srp").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o número do SRP.',buttons:{'ok':{is_default:1,onclick:"foc('i-numero-srp');ultimateClose();"}}});
		return false;
	}

	if ($("input[name='f-tipo']").val() == 2 && $("#i-data-srp").inputmask("unmaskedvalue").length < 8)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a data do SRP corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-data-srp');ultimateClose();"}}});
		return false;
	}
	
	if ($("#i-quantidade").val().length == 0 || $("#i-quantidade").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a quantidade. <span class="italic">(Não pode ser zero)</span>',buttons:{'ok':{is_default:1,onclick:"foc('i-quantidade');ultimateClose();"}}});
		return false;
	}

	if ($("#i-valor").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o valor.</span>',buttons:{'ok':{is_default:1,onclick:"foc('i-valor');ultimateClose();"}}});
		return false;
	}

	if (rtext.length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Anexe o ofício de solicitação de compra.',buttons:{'ok':{is_default:1,onclick:"ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		ultimateLoader(true, '');
		ajax = false;
		$.ajax({
			type: "POST",
			url: "c.compradir_salvar.php",
			data: $("#compradir-form").serialize()+"&f-anexo=" + encodeURIComponent(rtext.split('{^=?!?=^}')[0]),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Acesso restrito.',buttons:{'Ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					window.location.href = "index.php?p=cli_compradir";
				}
				else
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			}
		});
	}
}

