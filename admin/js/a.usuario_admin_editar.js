$(document).ready(function(){
	$("#i-celular").inputmask("(99) 9999-9999[9]");

	$("#i-celular").keyup(function(){
		if ($(this).inputmask("unmaskedvalue").length > 10)
			$(this).inputmask("(99) 99999-9999");
		else
			$(this).inputmask("(99) 9999-9999[9]");
	});
});

function ckSingle(ob, vl, cl)
{
	$(".cl-"+cl).attr("class","check-dot0 cl-"+cl);
	if ($(ob).hasClass("check-dot0"))
		$(ob).removeClass("check-dot0").addClass("check-dot1");
}

function ckSelf(ob, tipo, code)
{
	if ($(ob).hasClass("check-chk0"))
	{
		$(ob).removeClass("check-chk0").addClass("check-chk1");

		v = $('input[name="f-'+tipo+'"]').val();
		if (v.indexOf(code) < 0)
		{
			v = v + code;
			$('input[name="f-'+tipo+'"]').val(v);
		}	
	}
	else
	{
		$(ob).removeClass("check-chk1").addClass("check-chk0");

		v = $('input[name="f-'+tipo+'"]').val();
		if (v.indexOf(code) > -1)
		{
			v = v.replace(code, "");
			$('input[name="f-'+tipo+'"]').val(v);
		}
	}
}

function salvarUsuario()
{
	$("#i-nome").val($.trim($("#i-nome").val()));
	$("#i-login").val($.trim($("#i-login").val()));
	$("#i-senha").val($.trim($("#i-senha").val()));
	$("#i-email").val($.trim($("#i-email").val()));

	if ($("#i-nome").val().length<3)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do usuário corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
		return false;
	}

	if ($("#i-perfil").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o perfil deste usuário.',buttons:{'ok':{is_default:1,onclick:"foc('i-perfil');ultimateClose();"}}});
		return false;
	}

	if ($("#i-login").val().length<3)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite um nome de login.',buttons:{'ok':{is_default:1,onclick:"foc('i-login');ultimateClose();"}}});
		return false;
	}
	
	if ($("#i-login").val().indexOf(' ') > 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'O login não pode conter espaços.',buttons:{'ok':{is_default:1,onclick:"foc('i-login');ultimateClose();"}}});
		return false;
	}

	if ($("#i-senha").val().length == 0 && $("input[name='f-id']").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite uma senha.',buttons:{'ok':{is_default:1,onclick:"foc('i-senha');ultimateClose();"}}});
		return false;
	}

	if (!isEmail($("#i-email").val()))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'O email precisa ser válido.',buttons:{'ok':{is_default:1,onclick:"foc('i-email');ultimateClose();"}}});
		return false;
	}

	if ($("#i-celular").inputmask("unmaskedvalue").length > 0 && $("#i-celular").inputmask("unmaskedvalue").length < 10)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o número celular corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-celular');ultimateClose();"}}});
		return false;
	}
	
	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		var dataString = $("#usuario-form").serialize()+"&f-ativo="+($("#i-ativo-sim").hasClass("check-dot1") << 0)+"&f-notificacoes="+($("#i-notif-sim").hasClass("check-dot1") << 0);
		$.ajax({
			type: "POST",
			url: "a.usuario_admin_salvar.php",
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
				else if (data[0] == 2)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Já existe um usuário com este login.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 1)
				{
					window.location.href = "a.usuario_admin.php";
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
