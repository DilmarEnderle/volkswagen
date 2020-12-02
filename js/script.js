var ajax = true;

$(document).ready(function()
{
	$(document).on("keyup", ".lg", function(e){
		if (e.keyCode == 13)
		{
			if ($("#iLogin").val() == "")
				$("#iLogin").focus();
			else if ($("#iSenha").val() == "")
				$("#iSenha").focus();
			else
				doLogin();
		}
	});
});

function foc(ob) { $("#"+ob).focus(); }

function doConn()
{
	if ($("#conn").hasClass("conn-box0"))
	{
		$("#conn").attr("class","conn-box1");
		$('input[name="conn"]').val(1);
	}
	else
	{
		$("#conn").attr("class","conn-box0");
		$('input[name="conn"]').val(0);
	}
}

function doLogin()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "cli_login.php",
			dataType: "json",
			data: $('#form_login').serialize(),
			success:function(data)
			{
				ajax = true;
				if (data[0] == 1)
					window.location.href = data[1];
				else
				{
					ultimateLoader(false,'');
					ultimateDialog({title:'Login Inválido.',color:'red',content:'O usuário e a senha fornecidos não conferem.',buttons:{'ok':{is_default:1}}});
				}
			}
		});
	}
}

function isEmail(strng)
{
	if (strng == "") return false;
	var emailFilter = /^.+@.+\..{2,}$/;
	var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;
	if (!(emailFilter.test(strng)) || strng.match(illegalChars))
		return false;
	return true;    
}

function selecionarDN()
{
	ultimateLoader(true,'');
	$.ajax({
		type:"post",
		url:"c.selecionar_dn.php",
		data:"f-dn="+$("#i-dn").val(),
		dataType:"json",
		success:function(data)
		{
			if (data[0] == 9)
			{
				ultimateLoader(false,'');
				ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
			}
			else if (data[0] == 1)
			{
				location.reload();
			}
			else
			{
				ultimateLoader(false,'');
				ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
			}
		}
	});
}
