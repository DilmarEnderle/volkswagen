$(document).ready(function(){
	$("#i-adve").inputmask({ "mask": "9", "repeat": 10, "greedy": false });
});

function salvarCidade()
{
	$("#i-nome").val($.trim($("#i-nome").val()));
	
	if ($("#i-nome").val().length<3)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome da cidade.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
		return false;
	}

	if ($("#i-estado").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o estado.',buttons:{'ok':{is_default:1,onclick:"foc('i-estado');ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		$.ajax({
			type: "POST",
			url: "a.cidade_salvar.php",
			data: $("#cidade-form").serialize(),
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
				else if (data[0] == 2)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Já existe uma cidade com este nome no estado selecionado.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 1)
				{
					window.location.href = data[1];
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

