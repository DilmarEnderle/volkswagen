$(document).ready(function(){
	$("#i-comercial").inputmask("(99) 9999-9999[9]");
	$("#i-celular").inputmask("(99) 9999-9999[9]");

	$("#i-comercial,#i-celular").keyup(function(){
		if ($(this).inputmask("unmaskedvalue").length > 10)
			$(this).inputmask("(99) 99999-9999");
		else
			$(this).inputmask("(99) 9999-9999[9]");
	});
});

function ckSelfish(ob, cl)
{
	if ($(ob).attr("data-mode") == "readonly")
		return false;

	if ($(ob).hasClass("cb0"))
	{
		$(ob).attr("class", "cb1 fl");
		$('input[name="f-'+cl+'"]').val(1);
	}
	else
	{
		$(ob).attr("class", "cb0 fl");
		$('input[name="f-'+cl+'"]').val(0);
	}
}

function ckSelf(ob, tipo, code)
{
	if ($(ob).attr("data-mode") == "readonly")
		return false;

	if ($(ob).hasClass("cb0"))
	{
		$(ob).removeClass("cb0").addClass("cb1");

		v = $('input[name="f-'+tipo+'"]').val();
		if (v.indexOf(code) < 0)
		{
			v = v + code;
			$('input[name="f-'+tipo+'"]').val(v);
		}	
	}
	else
	{
		$(ob).removeClass("cb1").addClass("cb0");

		v = $('input[name="f-'+tipo+'"]').val();
		if (v.indexOf(code) > -1)
		{
			v = v.replace(code, "");
			$('input[name="f-'+tipo+'"]').val(v);
		}
	}
}

function salvarUsuario(p)
{
	$("#i-nome").val($.trim($("#i-nome").val()));
	$("#i-departamento").val($.trim($("#i-departamento").val()));
	$("#i-email").val($.trim($("#i-email").val()));
	
	if ($("#i-nome").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do usuário.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
		return false;
	}

	if ($("#i-departamento").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o departamento.',buttons:{'ok':{is_default:1,onclick:"foc('i-departamento');ultimateClose();"}}});
		return false;
	}

	if (!$("#i-perfil").val() > 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione um perfil de acesso.',buttons:{'ok':{is_default:1,onclick:"foc('i-perfil');ultimateClose();"}}});
		return false;
	}

	if (!isEmail($("#i-email").val()))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'O email precisa ser válido.',buttons:{'ok':{is_default:1,onclick:"foc('i-email');ultimateClose();"}}});
		return false;
	}

	if ($("#i-comercial").inputmask("unmaskedvalue").length < 10)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o telefone fixo corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-comercial');ultimateClose();"}}});
		return false;
	}

	if ($("#i-celular").inputmask("unmaskedvalue").length < 10)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o telefone celular corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-celular');ultimateClose();"}}});
		return false;
	}
	
	if (p)
		var dataString = $("#usuario-form").serialize()+"&f-q17pil69ai=1";
	else
		var dataString = $("#usuario-form").serialize();

	if (ajax)
	{
		ultimateLoader(true, '');
		ajax = false;
		$.ajax({
			type: "POST",
			url: "c.usuario_salvar.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Acesso restrito.',buttons:{'Ok':{is_default:1}}});
				}
				else if (data[0] == 8)
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Já existe um usuário com este e-mail no sistema (BO).',buttons:{'Ok':{is_default:1}}});
				}
				else if (data[0] == 7)
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Já existe um usuário com este e-mail no sistema (DN).',buttons:{'Ok':{is_default:1}}});
				}
				else if (data[0] == 6)
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Você já tem um usuário com este e-mail.',buttons:{'Ok':{is_default:1}}});
				}
				else if (data[0] == 5)
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Você já tem um usuário com este e-mail.',buttons:{'Ok':{is_default:1}}});
				}
				else if (data[0] == 4)
				{
					//perguntar
					ultimateLoader(false, '');
					ultimateDialog({
						title:'Liberar Acesso ?',
						color:'red',
						content:data[1],
						buttons:{'Sim':{onclick:'salvarUsuario(true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
					});
				}
				else if (data[0] == 3)
				{
					ultimateLoader(false, '');
					ultimateDialog({title:'Erro.',color:'red',content:'Já existe um usuário com este e-mail no sistema.',buttons:{'Ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					window.location.href = data[1];
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

