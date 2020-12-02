function salvarCores()
{
	$.ajax({
		type: "POST",
		url: "c.custom_salvar.php",
		data: "cor-base="+$("#i-cor-base").val()+"&cor-menu="+$("#i-cor-menu").val()+"&cor-aba="+$("#i-cor-aba").val()+"&cor-botao-1="+$("#i-cor-btn1").val()+"&cor-botao-2="+$("#i-cor-btn2").val(),
		dataType: "json",
		success: function(data)
		{
			if (data[0] == 1)
				ultimateDialog({title:'Sucesso!',color:'green',content:'Alterações salvas com sucesso.',x_btn:0,buttons:{'ok':{onclick:'location.reload();',is_default:1}}});
			else
				ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
		}
	});
}

function padraoCores()
{
	document.getElementById("i-cor-base").jscolor.fromString("548ec7");
	document.getElementById("i-cor-menu").jscolor.fromString("ff6600");
	document.getElementById("i-cor-aba").jscolor.fromString("548ec7");
	document.getElementById("i-cor-btn1").jscolor.fromString("ff6600");
	document.getElementById("i-cor-btn2").jscolor.fromString("bebebe");
}
