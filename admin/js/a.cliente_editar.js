$(document).ready(function(){
	$("#i-cnpj").inputmask("99.999.999/9999-99");
	$("#i-cpf").inputmask("999.999.999-99");
	$("#i-dn").inputmask({ "mask": "9", "repeat": 8, "greedy": false });
	$("#i-adve").inputmask({ "mask": "9", "repeat": 8, "greedy": false });
	$("#i-numero").inputmask({ "mask": "9", "repeat": 8, "greedy": false });
	$("#i-cep").inputmask("99999-999");
	$("#i-residencial").inputmask("(99) 9999-9999[9]");
	$("#i-comercial").inputmask("(99) 9999-9999[9]");
	$("#i-celular").inputmask("(99) 9999-9999[9]");

	$("#i-residencial,#i-comercial,#i-celular").keyup(function(){
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

	if (cl == "pessoa")
	{
		if (vl == 1)
		{
			$("#cnpj-box").hide();
			$("#cpf-box").show();
		}
		else
		{
			$("#cpf-box").hide();
			$("#cnpj-box").show();
		}
	}
	else if (cl == "tipo")
	{
		if (vl == 2)
		{
			$(".div-dn").show();
			$(".div-bo").hide();
			$("#i-tipo").val(2);
			$("#ntf-dn").show();
			$("#ntf-bo").hide();
		}
		else
		{
			$(".div-dn").hide();
			$(".div-bo").show();
			$("#i-tipo").val(1);
			$("#ntf-dn").hide();
			$("#ntf-bo").show();
		}
	}
}

function listarCidades()
{
	$("#i-cidade").hide();
	$("#loader").show();

	$.ajax({
		type: "GET",
		url: "a.cidades.php",
		data: "f-estado="+$("#i-estado").val(),
		dataType: "text",
		success: function(data)
		{
			$("#i-cidade").html(data);
			$("#i-cidade").show();
			$("#loader").hide();
		}
	});
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

function ckONLY(ob)
{
	if ($(ob).hasClass("check-chk0"))
		$(ob).attr("class","check-chk1");
	else
		$(ob).attr("class","check-chk0");
}

function isValidCPF(v)
{
	if (v.length < 14) return false;
	var i;
	exp = /\.|\-|\//g
 	var cpf = v.toString().replace(exp,"");
	var c = cpf.substr(0,9);
	var dv = cpf.substr(9,2);
	var d1 = 0;
	for (i=0; i<9; i++)
	{
		d1 += c.charAt(i)*(10-i);
	}
	if (d1 == 0) return false;
 
	d1 = 11 - (d1 % 11);
	if (d1 > 9) d1 = 0;
	if (dv.charAt(0) != d1) return false;
 
	d1 *= 2;
	for (i=0; i<9; i++)
	{
		d1 += c.charAt(i)*(11-i);
	}
	d1 = 11 - (d1 % 11);
	if (d1 > 9) d1 = 0;
	if (dv.charAt(1) != d1) return false;
	
	return true;	
}

function isValidCNPJ(v)
{
	if (v.length<18) return false;
	var cnpj = v;
	var valida = new Array(6,5,4,3,2,9,8,7,6,5,4,3,2);
	var dig1= new Number;
	var dig2= new Number;
        
	exp = /\.|\-|\//g
	cnpj = cnpj.toString().replace( exp, "" ); 
	var digito = new Number(eval(cnpj.charAt(12)+cnpj.charAt(13)));
                
	for(i = 0; i<valida.length; i++)
	{
		dig1 += (i>0? (cnpj.charAt(i-1)*valida[i]):0);  
		dig2 += cnpj.charAt(i)*valida[i];       
	}
	dig1 = (((dig1%11)<2)? 0:(11-(dig1%11)));
	dig2 = (((dig2%11)<2)? 0:(11-(dig2%11)));
        
	if(((dig1*10)+dig2) != digito)
		return false;  
	else
		return true;
}

function salvarCliente()
{
	$("#i-nome").val($.trim($("#i-nome").val()));
	$("#i-dep").val($.trim($("#i-dep").val()));
	$("#i-rua").val($.trim($("#i-rua").val()));
	$("#i-complemento").val($.trim($("#i-complemento").val()));
	$("#i-bairro").val($.trim($("#i-bairro").val()));
	$("#i-email").val($.trim($("#i-email").val()));
	$("#i-observacoes").val($.trim($("#i-observacoes").val()));

	if ($("#i-nome").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do DN, BO ou Rep.',buttons:{'ok':{is_default:1,onclick:"foc('i-nome');ultimateClose();"}}});
		return false;
	}

	//if ($("#i-pessoa-f").hasClass("check-dot1"))
	//{
		//if ($("#i-cpf").inputmask("unmaskedvalue").length > 0 && !isValidCPF($("#i-cpf").inputmask("unmaskedvalue")))
		//{
		//	ultimateDialog({title:'Erro.',color:'red',content:'O número CPF está incorreto.',buttons:{'ok':{is_default:1,onclick:"foc('i-cpf');ultimateClose();"}}});
		//	return false;
		//}
	//}
	//else if ($("#i-pessoa-j").hasClass("check-dot1"))
	//{
		//if ($("#i-cnpj").inputmask("unmaskedvalue").length > 0 && !isValidCNPJ($("#i-cnpj").inputmask("unmaskedvalue")))
		//{
		//	ultimateDialog({title:'Erro.',color:'red',content:'O número CNPJ está incorreto.',buttons:{'ok':{is_default:1,onclick:"foc('i-cnpj');ultimateClose();"}}});
		//	return false;
		//}
	//}

	if (($("#i-tipo").val() == 3 || $("#i-tipo").val() == 4) && $("#i-dep").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o departamento.',buttons:{'ok':{is_default:1,onclick:"foc('i-dep');ultimateClose();"}}});
		return false;
	}

	if ($("#i-tipo").val() == 2 && $("#i-dn").inputmask("unmaskedvalue").length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o DN (Dealer Number).',buttons:{'ok':{is_default:1,onclick:"foc('i-dn');ultimateClose();"}}});
		return false;
	}

	if ($("#i-estado").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o estado.',buttons:{'ok':{is_default:1,onclick:"foc('i-estado');ultimateClose();"}}});
		return false;
	}

	if ($("#i-cidade").val() == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione a cidade.',buttons:{'ok':{is_default:1,onclick:"foc('i-cidade');ultimateClose();"}}});
		return false;
	}

	if ($("#i-tipo").val() == 1 || $("#i-tipo").val() == 2)
	{
		if ($("#i-login").val().length < 3)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'Digite um nome de login.',buttons:{'ok':{is_default:1,onclick:"foc('i-login');ultimateClose();"}}});
			return false;
		}

		if ($("#i-login").val().indexOf(' ') > 0)
		{
			ultimateDialog({title:'Erro.',color:'red',content:'O login não pode conter espaços.',buttons:{'ok':{is_default:1,onclick:"foc('i-login');ultimateClose();"}}});
			return false;
		}
	}
	
	if (!isEmail($("#i-email").val()))
	{
		ultimateDialog({title:'Erro.',color:'red',content:'O email precisa ser válido.',buttons:{'ok':{is_default:1,onclick:"foc('i-email');ultimateClose();"}}});
		return false;
	}
	
	if ($("#i-comercial").inputmask("unmaskedvalue").length > 0 && $("#i-comercial").inputmask("unmaskedvalue").length < 10)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Informe o número comercial corretamente.',buttons:{'ok':{is_default:1,onclick:"foc('i-comercial');ultimateClose();"}}});
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

		var pessoa = 2;
		if ($("#i-pessoa-f").hasClass("check-dot1"))
			pessoa = 1;
		var dataString = $("#cliente-form").serialize()+"&f-ativo="+($("#i-ativo-sim").hasClass("check-dot1") << 0)+"&f-pessoa="+pessoa+"&f-notificacoes="+($("#i-notif-sim").hasClass("check-dot1") << 0);
		$.ajax({
			type: "POST",
			url: "a.cliente_salvar.php",
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
				else if (data[0] == 8)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'O nome do login já está sendo usado por outro usuário.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 7)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'O email já está sendo usado por outro usuário.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 6)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Este DN (Dealer Number) já está cadastrado.',buttons:{'Ok':{is_default:1}}});
					$("#i-salvar-box").show();
					$("#i-processando-box").hide();
				}
				else if (data[0] == 1)
				{
					window.location.href = "a.cliente.php";
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

function clienteSenha(now)
{
	if (now)
	{
		ultimateLoader(true,'');

		var reset = 0;
		if ($("#i-reset").hasClass("check-chk1"))
			reset = 1;

		$.ajax({
			type: "post",
			url: "a.cliente_email_enviar.php",
			data: "id="+$("#i-id").val()+"&reset="+reset,
			dataType: "json",
			success: function(data)
			{
				ultimateLoader(false, '');
				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Erro.',color:'red',content:'E-mail não cadastrado.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					ultimateDialog({title:'Sucesso!',color:'green',content:'E-mail enviado com sucesso.',buttons:{'Ok':{is_default:1}}});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});					
			}
		});
	}
	else
	{
		ultimateLoader(true, '');
		$.ajax({
			type: "post",
			url: "a.cliente_email.php",
			data: "id="+$("#i-id").val(),
			dataType: "json",
			success: function(data)
			{
				ultimateLoader(false, '');
				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Erro.',color:'red',content:'E-mail não cadastrado.',buttons:{'Ok':{is_default:1}}});
				else if (data[0] == 1)
					ultimateDialog({
						title:'Re-enviar',
						color:'gray',
						content:data[1],
						buttons:{'Sim':{onclick:'clienteSenha(true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
					});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});					
			}
		});
	}
}

function regioes(ob)
{
	var id_obj = $(ob).attr("id");
	var msg = $(ob).parent().children('span').html();
	var cat = $(ob).parent().parent().parent().children('div').children('span').html();
	$.ajax({
		type:"post",
		url:"a.cliente_notif_regioes.php",
		data:"id-obj="+id_obj+"&cat="+cat+"&msg="+msg+"&eml-reg="+$('input[name="f-eml-reg"]').val()+"&sms-reg="+$('input[name="f-sms-reg"]').val(),
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
	if ($("#ck-global").hasClass("check-chk1"))
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

