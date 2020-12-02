var return_upload = {status:0,long_filename:""};

$(document).ready(function(){

	tinymce.init({
		selector: "#i-mensagem",
		height: "200",
		menubar: false,
		statusbar: false,
		resize: false,
		element_format: "html",
		language: 'pt_BR',
		plugins: ["textcolor"],
		skin: 'gelicmsg',
		toolbar: "bold italic underline forecolor removeformat"
	});

	atualizarHistorico(false);
});

function postar()
{
	var txt = $.trim(tinymce.activeEditor.getContent());
	if (txt.length < 1 && return_upload.status == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o conteudo ou anexe um arquivo antes de postar.',buttons:{'Ok':{is_default:1,onclick:"foc('i-message');ultimateClose();"}}});
		return false;
	}
	
	if (ajax)
	{
		ajax = false;
		$("#postar-box").hide();
		$("#processando-box").show();
		$.ajax({
			type: "post",
			url: "a.cliente_historico_postar.php",
			data: "id-cliente="+$("#id-cliente").val()+"&texto="+encodeURIComponent(txt)+"&anexo="+encodeURIComponent(return_upload.long_filename),
			dataType: "json",
			success: function(data)
			{
				ajax = true;

				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					$("#postar-box").show();
					$("#processando-box").hide();
				}
				else if (data[0] == 1)
				{
					tinymce.activeEditor.setContent('');
					cancelUpload();
					atualizarHistorico(true);
					$("#postar-box").show();
					$("#processando-box").hide();
				}
				else if (data[0] == 0)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
					$("#postar-box").show();
					$("#processando-box").hide();
				}
			}
		});
	}
}

function selectFile()
{
	if (!ajax) return false;

	if (!$("#upload-form").length)
		$("body").prepend('<form id="upload-form" enctype="multipart/form-data"><input id="upload-button" class="file-upload" type="file" name="f-upload" onchange="uploadFile();"></form>');

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
			xhr.open("POST", "a.cliente_historico_upload.php");
			xhr.send(fd);
			$("#upl-btn").hide();
			$("#upl-bar").css("width", 0);
			$("#upl-loading").show();
		}
		else
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O arquivo selecionado é muito grande.<br><br>Tamanho máximo permitido: <span class="bold">100 MB</span>',buttons:{'Ok':{is_default:1}}});
		}
	}
}

function uploadProgress(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var b = Math.round(798 * percentComplete / 100);
		$("#upl-bar").css("width", b+'px');
		$("#upl-per").html("Carregando..."+percentComplete+"%");
	}
	else
	{
		$("#upl-bar").css("width", "798px");
	}
}

function uploadComplete()
{
	if (return_upload.status == 0)
		cancelUpload();
	else
	{
		$("#upl-filename").html(return_upload.short_filename);
		$("#upl-filesize").html(return_upload.file_size);
		$("#upl-loading").hide();
		$("#upl-ready").show();
	}
}

function cancelUpload()
{
	return_upload = {status:0,long_filename:""};
	$("#upl-btn").show();
	$("#upl-loading").hide();
	$("#upl-ready").hide();
}

function atualizarHistorico(fromPost)
{
	if (fromPost)
	{
		if ($("#historico").children(".content-inside").length > 0)
			$("#historico").append('<span style="display:block;text-align:center;padding-top:24px;"><img src="img/spinner.gif"></span>');
		else
			$("#historico").html('<span style="display:block;text-align:center;padding-top:24px;"><img src="img/spinner.gif"></span>');
	}
	else
	{
		$("#historico").html('<span style="display:block;text-align:center;padding-top:24px;"><img src="img/spinner.gif"></span>');
	}

	$.ajax({
		type:"post",
		url:"a.cliente_historico_load.php",
		data:"id-cliente="+$("#id-cliente").val(),
		dataType:"json",
		success:function(data)
		{
			if (data[2] == true)
				$("#msg").show();
			else
				$("#msg").hide();

			$("#historico").html(data[1]);
		}
	});
}

function goCliente(id)
{
	$("#id-cliente").val(id);
	$("#msg").hide();
	atualizarHistorico(false);
}

