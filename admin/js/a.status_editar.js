function ckSingle(ob, cl)
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

function salvarStatus()
{
	$("#i-descricao").val($.trim($("#i-descricao").val()));

	if ($("#i-descricao").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Descreva o status.',buttons:{'ok':{is_default:1,onclick:"foc('i-descricao');ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		$("#i-salvar-box").hide();
		$("#i-processando-box").show();
		ajax = false;
		var dataString = $("#status-form").serialize()+"&f-inicial="+($("#i-inicial-sim").hasClass("check-dot1") << 0);
		$.ajax({
			type: "POST",
			url: "a.status_salvar.php",
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
					window.location.href = "a.status.php";
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

function atualizaExemplo()
{
	$(".exemplo").css("color","#"+$("#i-cor-texto").val());
	$(".exemplo").css("background-color","#"+$("#i-cor-fundo").val());
}
