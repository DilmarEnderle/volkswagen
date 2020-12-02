function ckSingle(ob, vl, cl)
{
	$(".cl-"+cl).attr("class","check-dot0 cl-"+cl);
	if ($(ob).hasClass("check-dot0"))
		$(ob).removeClass("check-dot0").addClass("check-dot1");
}

function ckSelfish(ob, cl)
{
	if ($(ob).hasClass("check-chk0"))
	{
		$(ob).removeClass("check-chk0").addClass("check-chk1");
		$('input[name="f-'+cl+'"]').val(1);
	}
	else
	{
		$(ob).removeClass("check-chk1").addClass("check-chk0");
		$('input[name="f-'+cl+'"]').val(0);
	}
}

function salvarMotivo()
{
	$("#i-descricao").val($.trim($("#i-descricao").val()));

	if ($("#i-tipo").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o tipo.',buttons:{'ok':{is_default:1,onclick:"foc('i-tipo');ultimateClose();"}}});
		return false;
	}

	if ($("#i-descricao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Descreva o motivo.',buttons:{'ok':{is_default:1,onclick:"foc('i-descricao');ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		var dataString = $("#motivo-form").serialize();
		$.ajax({
			type: "POST",
			url: "a.motivo_salvar.php",
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
					window.location.href = "a.motivo.php";
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

function listarSubmotivos()
{
	if ($("#i-tipo").val() > 0 && $("#i-tipo").val() != 21 && $("#i-tipo").val() != 22 && $("#i-tipo").val() != 23)
	{
		$("#sub").show();
		if (ajax)
		{
			ajax = false;
			$.ajax({
				type: "post",
				url: "a.motivo_submotivos.php",
				data: "id="+$("#i-id").val()+"&tipo="+$("#i-tipo").val(),
				dataType: "text",
				success: function(data){
					ajax = true;
					$("#i-submotivo").html(data);
				}
			});
		}
	}
	else
	{
		$("#sub").hide();
	}
}
