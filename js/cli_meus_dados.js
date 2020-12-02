$(document).ready(function(){
	$("#i-comercial,#i-celular").inputmask("(99) 9999-9999[9]");
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

﻿function salvarMeusDados()
{
	var allow_senha = /^[0-9a-zA-Z\_\-\.\s]{4,15}$/;
	var allow_login = /^[0-9a-zA-Z\_\-\.]{3,50}$/;

	if ($("#i-nome").length)
	{
		$("#i-nome").val($.trim($("#i-nome").val()));
		if ($("#i-nome").val().length == 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome deste acesso.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
			return false;
		}
	}


	$("#i-email").val($.trim($("#i-email").val()));
	if (!isEmail($("#i-email").val()))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'O email precisa ser válido.',buttons:{'ok':{is_default:1,onclick:"foc('i-email');ultimateClose();"}}});
		return false;
	}


	if ($("#i-login").length)
	{
		$("#i-login").val($.trim($("#i-login").val()));
		if ($("#i-login").val().length < 3)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O nome do login precisa ter entre 3 a 50 caracteres.',buttons:{'ok':{is_default:1,onclick:"foc('i-login');ultimateClose();"}}});
			return false;
		}

		if (!$("#i-login").val().match(allow_login))
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O nome do login só pode conter letras não acentuadas, números e os caracteres<br>( - )( _ ) ou ( . )',buttons:{'ok':{is_default:1,onclick:"foc('i-login');ultimateClose();"}}});
			return false;
		}
	}

	
	if ($("#i-nova-senha").val().length > 0)
	{
		if ($("#i-nova-senha").val().length < 4)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'A nova senha precisa ter entre 4 a 15 caracteres.',buttons:{'ok':{is_default:1,onclick:"foc('i-nova-senha');ultimateClose();"}}});
			return false;
		}

		if (!$("#i-nova-senha").val().match(allow_senha))
		{
			ultimateDialog({title:'Erro.',color:'red',content:'A nova senha só pode conter letras não acentuadas, números e os caracteres<br>( - )( _ ) ( espaço ) ou ( . )',buttons:{'ok':{is_default:1,onclick:"foc('i-nova-senha');ultimateClose();"}}});
			return false;
		}
	}

	if ($("#i-comercial").inputmask("unmaskedvalue").length > 0 && $("#i-comercial").inputmask("unmaskedvalue").length < 10)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o telefone fixo corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-comercial');ultimateClose();"}}});
		return false;
	}

	if ($("#i-celular").inputmask("unmaskedvalue").length > 0 && $("#i-celular").inputmask("unmaskedvalue").length < 10)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o telefone celular corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-celular');ultimateClose();"}}});
		return false;
	}

	if ($("#i-senha-atual").val().length < 4)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Senha atual inválida.',buttons:{'ok':{is_default:1,onclick:"foc('i-senha-atual');ultimateClose();"}}});
		return false;
	}

	$.ajax({
		type: "POST",
		url: "c.meus_dados_salvar.php",
		data: $("#meus-dados-form").serialize(),
		dataType: "json",
		success: function(data)
		{
			if (data[0] == 9)
				ultimateDialog({title:'Erro.',color:'red',content:'Senha atual inválida.',buttons:{'Ok':{is_default:1,onclick:"foc('i-senha-atual');ultimateClose();"}}});
			else if (data[0] == 8)
				ultimateDialog({title:'Erro.',color:'red',content:'Já existe um usuário com este e-mail no sistema.',buttons:{'Ok':{is_default:1,onclick:"foc('i-email');ultimateClose();"}}});
			else if (data[0] == 7)
				ultimateDialog({title:'Erro.',color:'red',content:'Já existe um usuário com este login.',buttons:{'Ok':{is_default:1,onclick:"foc('i-login');ultimateClose();"}}});
			else if (data[0] == 1)
			{
				ultimateDialog({title:'Sucesso!',color:'green',content:data[1],buttons:{'Ok':{is_default:1,onclick:'ultimateClose();window.scrollTo(0,0);'}}});
				$("#i-nova-senha").val("");
				$("#i-senha-atual").val("");
				$(".top_emp").html($("#i-nome").val());
			}
			else
				ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
		}
	});
}

function regioes(ob)
{
	var id_obj = $(ob).attr("id");
	var msg = $(ob).parent().children('span').html();

	$.ajax({
		type:"post",
		url:"c.meus_dados_regioes.php",
		data:"id-obj="+id_obj+"&msg="+msg+"&eml-reg="+$('input[name="f-eml-reg"]').val()+"&sms-reg="+$('input[name="f-sms-reg"]').val(),
		dataType:"json",
		success:function(data)
		{
			ultimateDialog({
				title:'Selecione as Regiões',
				width:600,
				color:'gray',
				content:data[1],
				buttons:{'Ok':{onclick:'aplicarRegioes();ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
			});
		}
	});
}

function ckONLY(ob)
{
	if ($(ob).hasClass("cb0"))
		$(ob).attr("class","cb1");
	else
		$(ob).attr("class","cb0");
}

function rsel(ob, tipo, vl)
{
	if ($(ob).hasClass("rsel0"))
	{
		$(ob).attr("class","rsel1");

		v = $('input[name="tmp-'+tipo+'"]').val();
		if (v.indexOf(vl) < 0)
		{
			v += vl;
			$('input[name="tmp-'+tipo+'"]').val(v);
		}
	}
	else
	{
		$(ob).attr("class","rsel0");

		v = $('input[name="tmp-'+tipo+'"]').val();
		if (v.indexOf(vl) > -1)
		{
			v = v.replace(vl, "");
			$('input[name="tmp-'+tipo+'"]').val(v);
		}
	}
}

function aplicarRegioes()
{
	var id_obj = $('input[name="tmp-id-obj"]').val();
	var letter = id_obj.charAt(3);
	var tipo = "";
	if (id_obj.charAt(4) == "e")
		tipo = "eml";
	else
		tipo = "sms";

	$('input[name="f-'+tipo+'-reg"]').val($('input[name="tmp-'+tipo+'-reg"]').val());
		
	var aStr = [];
	var aFrm = [];
	var reg = $('input[name="f-'+tipo+'-reg"]').val();
	
	if (reg.indexOf(letter+'12') > -1) { aStr.push("1/2"); aFrm.push("12"); }
	if (reg.indexOf(letter+'3') > -1) { aStr.push("3"); aFrm.push("3"); }
	if (reg.indexOf(letter+'4') > -1) { aStr.push("4"); aFrm.push("4"); }
	if (reg.indexOf(letter+'5') > -1) { aStr.push("5"); aFrm.push("5"); }
	if (reg.indexOf(letter+'6') > -1) { aStr.push("6"); aFrm.push("6"); }

	//verificar global
	if ($("#ck-global").hasClass("cb1"))
	{
		var abc = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
		var novo_frm = '';
		for (i=0; i<abc.length; i++)
		{
			for (j=0; j<aFrm.length; j++)
				novo_frm += abc[i] + aFrm[j];
		}

		$('input[name="f-eml-reg"]').val(novo_frm);
		$('input[name="f-sms-reg"]').val(novo_frm);
		if (aStr.length > 0)
			$(".sel-reg").html(aStr.join(","));
		else
			$(".sel-reg").html('- - -');
	}
	else
	{
		if (aStr.length > 0)
			$("#"+id_obj).children(".sel-reg").html(aStr.join(","));
		else
			$("#"+id_obj).children(".sel-reg").html('- - -');
	}
}

