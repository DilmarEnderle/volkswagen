function ckSingle(ob, cl)
{
	$(".cl-"+cl).attr("class","check-dot0 cl-"+cl);
	if ($(ob).hasClass("check-dot0"))
		$(ob).removeClass("check-dot0").addClass("check-dot1");
}

function salvarFeriado()
{
	$("#i-nome").val($.trim($("#i-nome").val()));

	if ($("#i-nome").val().length<3)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do feriado corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
		return false;
	}

	if ($("#i-dia").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o dia.',buttons:{'ok':{is_default:1,onclick:"foc('i-dia');ultimateClose();"}}});
		return false;
	}

	if ($("#i-mes").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o mês.',buttons:{'ok':{is_default:1,onclick:"foc('i-mes');ultimateClose();"}}});
		return false;
	}

	if ($("#i-fixo-sim").hasClass("check-dot0") && $("#i-fixo-nao").hasClass("check-dot0"))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe se o feriado é fixo.',buttons:{'ok':{is_default:1,onclick:"ultimateClose();"}}});
		return false;
	}
	
	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		var dataString = $("#feriado-form").serialize()+"&f-fixo="+($("#i-fixo-sim").hasClass("check-dot1") << 0);
		$.ajax({
			type: "POST",
			url: "a.feriado_salvar.php",
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
					window.location.href = "a.feriado.php";
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

