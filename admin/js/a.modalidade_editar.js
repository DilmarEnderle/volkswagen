function salvarModalidade()
{
	$("#i-nome").val($.trim($("#i-nome").val()));
	$("#i-abv").val($.trim($("#i-abv").val()));

	if ($("#i-nome").val().length<3)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome da modalidade corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
		return false;
	}

	if ($("#i-abv").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite a abreviação.',buttons:{'ok':{is_default:1,onclick:"foc('i-abv');ultimateClose();"}}});
		return false;
	}


	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		$.ajax({
			type: "POST",
			url: "a.modalidade_salvar.php",
			data: $("#modalidade-form").serialize(),
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
				else if (data[0] == 8)
				{
					ultimateDialog({title:'Erro!',color:'red',content:'Abreviação já utilizada por outra modalidade.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 1)
				{
					window.location.href = "a.modalidade.php";
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

