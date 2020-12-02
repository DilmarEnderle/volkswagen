var rtext = JSON.parse('["0","","","",""]');
var uplds = [];     //uploads

$(document).ready(function(){
	$("#i-celular").inputmask("(99) 9999-9999[9]");

	$("#i-celular").keyup(function(){
		if ($(this).inputmask("unmaskedvalue").length > 10)
			$(this).inputmask("(99) 99999-9999");
		else
			$(this).inputmask("(99) 9999-9999[9]");
	});
});

function ckSingle(ob, cl)
{
	$(".cl-"+cl).attr("class","check-dot0 cl-"+cl);
	if ($(ob).hasClass("check-dot0"))
		$(ob).removeClass("check-dot0").addClass("check-dot1");
}

function salvarAtarp()
{
	$("#i-modelo").val($.trim($("#i-modelo").val()));
	$("#i-orgao").val($.trim($("#i-orgao").val()));
	$("#i-licitacao").val($.trim($("#i-licitacao").val()));
	$("#i-vigencia").val($.trim($("#i-vigencia").val()));

	if ($("#i-modelo").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o modelo.',buttons:{'ok':{is_default:1,onclick:"foc('i-modelo');ultimateClose();"}}});
		return false;
	}

	if ($("#i-orgao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o nome do órgão.',buttons:{'ok':{is_default:1,onclick:"foc('i-orgao');ultimateClose();"}}});
		return false;
	}

	if ($("#i-licitacao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a licitação.',buttons:{'ok':{is_default:1,onclick:"foc('i-licitacao');ultimateClose();"}}});
		return false;
	}

	if ($("#i-vigencia").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe a vigencia.',buttons:{'ok':{is_default:1,onclick:"foc('i-vigencia');ultimateClose();"}}});
		return false;
	}
	
	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		var dataString = $("#atarp-form").serialize()+"&f-adesao="+($("#i-adesao-sim").hasClass("check-dot1") << 0)+"&uplds="+JSON.stringify(uplds);
		$.ajax({
			type: "POST",
			url: "a.atarp_salvar.php",
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
					window.location.href = "a.atarp.php";
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

function selectFile()
{
	$("#ultimate-error").hide();
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
					rtext = JSON.parse(xhr.responseText);
			});
			xhr.open("POST", "a.upload_atarp.php");
			xhr.send(fd);
			$("#upl-btn").hide();
			$("#upl-bar").css("width", 0);
			$("#upl-per").html('Carregando...');
			$("#upl-load").show();
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
		var b = Math.round(636 * percentComplete / 100);
		$("#upl-bar").css("width", b+'px');
		$("#upl-per").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upl-bar").css("width", 636+'px');
	}
}

function uploadComplete()
{
	$("#upl-load").hide();
	$("#upl-btn").show();
	uplds.push({filename:rtext[1], shortfilename:rtext[2], filesize:rtext[3], filemd5:rtext[4], id:0, action:'a'});
	$("#upload-holder").before('<div id="upl-'+(uplds.length-1)+'" class="file-box"><img src="img/file.png" style="position: absolute; left: 4px; top: 4px; border: 0;"><span style="position: absolute; left: 34px; top: 0; line-height: 32px; font-size: 13px;">'+rtext[2]+'</span><span class="gray-4c italic t11" style="position: absolute; right: 40px; top: 0; line-height: 32px;">'+rtext[3]+'</span><a class="btn-x24" href="javascript:void(0);" onclick="removeUpload('+(uplds.length-1)+');" style="right: 4px; top: 4px;" title="Remover"></a></div>');
}

function removeUpload(idx)
{
	uplds[idx].action = 'r';
	$("#upl-"+idx).remove();
}

