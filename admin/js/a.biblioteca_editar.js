var rtext = '';

$(document).ready(function(){
	tinymce.init({
		selector: "textarea#i_observacao",
		menubar: false,
		statusbar: false,
		resize: false,
		element_format: "html",
		language: 'pt_BR',
		width: 572,
		height: 140,
		plugins: ["textcolor"],
		skin: 'geliclib',
		toolbar: "bold italic underline forecolor removeformat"
	});
});

function ckSingle(ob, vl, cl)
{
	$(".cl_"+cl).removeClass("check_dot1").addClass("check0");
	$("#arquivo-pen").hide();
	$("#link-pen").hide();
	if ( $(ob).hasClass("check0") )
	{
		$(ob).removeClass("check0").addClass("check_dot1");
		$('input[name="f-'+cl+'"]').val(vl);
		if (vl == 0)
			$("#arquivo-pen").show();
		else
			$("#link-pen").show();
	}
}

function selectFile()
{
	$("#form_upload").html('<input id="upload_button" class="file_upload" type="file" name="f-upload" onchange="uploadFile();">');
	$("#upload_button").click();	
}

function uploadFile()
{
	var file = $("#upload_button")[0].files[0];
	if (file && file.size > 0)
	{
		if (file.size <= 100000000) //100 MB
		{
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append("f-upload", $("#upload_button")[0].files[0]);
			xhr.upload.addEventListener("progress", uploadProgress, false);
			xhr.addEventListener("load", uploadComplete, false);
			xhr.addEventListener('readystatechange', function()
			{
				if (this.readyState == 4)
					rtext = xhr.responseText;
			});
			xhr.open("POST", "a.biblioteca_upload.php");
			xhr.send(fd);
			showProgress();
		}
		else
		{
			alert("O arquivo selecionado é muito grande.\nTamanho máximo permitido: 100 MB");
		}
	}
}

function uploadProgress(evt)
{
	if (evt.lengthComputable)
	{
		var percentComplete = Math.round(evt.loaded * 100 / evt.total);
		var b = Math.round(564 * percentComplete / 100);
		$("#upl-bar").css("width", b+'px');
		$("#upl-per").html('Carregando...'+parseInt(percentComplete)+'%');
	}
	else
	{
		$("#upl-bar").css("width", 564+'px');
	}
}

function showProgress()
{
	$("#upl-btn").hide();
	$("#upl-bar").css("width", 0);
	$("#upl-per").html('Carregando...');
	$("#upl-load").show();
}
   
function uploadComplete()
{
	$("#upl-load").hide();
	$("#upl-filename").html(rtext.split('_')[0]);
	$("#upl-filesize").html(rtext.split('_')[1]);
	$("#upl-ready").show();
}

function cancelUpload()
{
	rtext = '';
	$("#upl-ready").hide();
	$("#upl-load").hide();
	$("#upl-btn").show();
}

function saveLibrary()
{
	$("#i_nome").val($.trim($("#i_nome").val()));
	var obs = $.trim(tinymce.activeEditor.getContent());
	$("#i_link").val($.trim($("#i_link").val()));

	if ($("#i_tipo").val() == 1 && $("#i_link").val().length < 11)
	{
		alert("Verifique se o link foi informado corretamente.");
		$("#i_link").focus();
		return false;
	}

	if ($("#i_tipo").val() == 0 && rtext.length == 0)
	{
		alert("Adicione o arquivo antes de continuar.");
		return false;
	}

	if ($("#i_nome").val().length<3)
	{
		alert("Informe uma descrição para este item.");
		$("#i_nome").focus();
		return false;
	}

	if ($("#i_estado").val().length == 0)
	{
		alert("Selecione um estado.");
		$("#i_estado").focus();
		return false;
	}

	if (ajax)
	{
		$("#save_btn").hide();
		$("#im_loader").show();
		ajax = false;
		var dataString = $("#library_form").serialize()+"&f-obs="+encodeURIComponent(obs)+"&f-arquivo="+rtext.split('_')[0];
		$.ajax({
			type: "POST",
			url: "a.biblioteca_salvar.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					$("#save_btn").show();
					$("#im_loader").hide();					
				}
				else
				{
					window.location.href = "a.biblioteca.php";
				}
			}
		});
	}
}


