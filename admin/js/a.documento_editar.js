var return_upload = {};

$(document).ready(function(){
	$("#i-data-validade").inputmask("d/m/y", {"placeholder":"__/__/____"});
});

//-------- file upload doc ----------
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
					return_upload = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "a.upload_documento.php");
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

function uploadProgress(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var b = Math.round(512 * percentComplete / 100);
		$("#upl-bar").css("width", b+'px');
		$("#upl-per").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upl-bar").css("width", "512px");
	}
}

function uploadComplete()
{
	if (return_upload.status == 0)
		cancelUpload();
	else
	{
		$("#upl-loading").hide();
		$("#upl-filename").html(return_upload.short_filename);
		$("#upl-filesize").html(return_upload.file_size);
		$("#upl-ready").show();
	}
}

function cancelUpload()
{
	return_upload = {};
	$("#upl-ready").hide();
	$("#upl-loading").hide();
	$("#upl-btn").show();
}
//------- end upload doc -----------

function ckSingle(ob, vl, cl)
{
	$(".cl-"+cl).attr("class","check-dot0 cl-"+cl);
	if ($(ob).hasClass("check-dot0"))
	{
		$(ob).removeClass("check-dot0").addClass("check-dot1");
		$('input[name="f-notificar"]').val(vl);
	}
}

function salvarDocumento()
{
	$("#i-nome").val($.trim($("#i-nome").val()));
	$("#i-descricao").val($.trim($("#i-descricao").val()));
	$("#i-prazo-notificacao").val($.trim($("#i-prazo-notificacao").val()));

	if ($("#i-nome").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do documento.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
		return false;
	}

	if ($('input[name="f-notificar"]').val() > 0 && $("#i-prazo-notificacao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o número de dias.',buttons:{'ok':{is_default:1,onclick:"foc('i-prazo-notificacao');ultimateClose();"}}});
		return false;
	}

	if (typeof return_upload.status == 'undefined')
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Anexe o arquivo.',buttons:{'Ok':{is_default:1}}});
		return false;
	}


	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		var dataString = $("#documento-form").serialize()+"&f-arquivo="+encodeURIComponent(return_upload.long_filename)+"&f-novo="+return_upload.is_new;
		$.ajax({
			type: "POST",
			url: "a.documento_salvar.php",
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
					window.location.href = "a.documento.php";
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
			}
		});
	}
}

