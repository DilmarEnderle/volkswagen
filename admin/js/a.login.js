$(document).ready(function(){
	$("#iLogin").focus();
	$(".iText").keydown(function(e){
		if (e.keyCode == 13)
			loginGo();
	});

	$(window).resize(function() {
		if ($("#adm-drop-contents-L").is(":visible"))
		{
			var lf = Math.round($("#adm-drop-L").offset().left + $("#adm-drop-L").outerWidth() - $("#adm-drop-contents-L").outerWidth());
			$("#adm-drop-contents-L").css("left", lf+"px");
		}
	});

	$(document).click(function(e){
		if (!$(e.target).hasClass("adr") && !$(e.target).parent().hasClass("adr"))
			admDropCloseL();
	});
});

function loginGo()
{
	ajax = false;
	ultimateLoader(true,'');
	$.ajax({
		type: "POST",
		url: "a.login.php",
		data: "f-login=" + $("#iLogin").val() + "&f-passw=" + $("#iPassw").val(),
		dataType: "json",
		success: function(data)
		{
			ajax = true;
			if (data[0] == 9)
			{
				ultimateLoader(false,'');
				ultimateDialog({title:'Login Inválido.',color:'red',content:'Acesso bloqueado.<br>Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1,onclick:"ultimateClose();foc('iLogin');"}}});
			}
			else if (data[0] == 8)
			{
				ultimateLoader(false,'');
				ultimateDialog({title:'Login Inválido.',color:'red',content:'O usuário e a senha fornecidos não conferem.',buttons:{'ok':{is_default:1,onclick:"ultimateClose();foc('iLogin');"}}});
			}
			else if (data[0] == 7)
			{
				ultimateLoader(false,'');
				ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem autorização para entrar deste local.',buttons:{'ok':{is_default:1,onclick:"ultimateClose();foc('iLogin');"}}});
			}
			else if (data[0] == 1)
				window.location.href = 'a.licitacao.php';
			else
			{
				ultimateLoader(false,'');
				ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1,onclick:"ultimateClose();foc('iLogin');"}}});
			}
		}
	});
}

function admDropClickL()
{
	if ($("#adm-drop-L").hasClass("drop"))
	{
		admDropCloseL();
	}
	else
	{
		$("#adm-drop-L").addClass("drop");
		$("#adm-drop-L").children("img").attr("src", "img/a-up-g.png");

		$("#adm-drop-contents-L").css("top", $("#adm-drop-L").offset().top + 32);
		var lf = Math.round($("#adm-drop-L").offset().left + $("#adm-drop-L").outerWidth() - $("#adm-drop-contents-L").outerWidth());
		$("#adm-drop-contents-L").css("left", lf+"px");
		$("#adm-drop-contents-L").show();
	}
}

function admDropCloseL()
{
	$("#adm-drop-L").removeClass("drop");
	$("#adm-drop-L").children("img").attr("src", "img/a-down-g.png");
	$("#adm-drop-contents-L").hide();
}


