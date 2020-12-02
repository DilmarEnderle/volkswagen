var sec = 1;

$(document).ready(function(){
	$("#opcao-"+sec).click();

	$(document).on("mouseenter", ".hgl", function(){ $(this).css("background-color", "#ffffb4"); });
	$(document).on("mouseleave", ".hgl", function(){ $(this).css("background-color", "#ffffff"); });
});

function showSec(ob,s)
{
	if (ajax)
	{
		ajax = false;
		$(".mm").removeClass("menu-item0 menu-item1").addClass("menu-item0");
		$(ob).removeClass("menu-item0 menu-item1").addClass("menu-item1");
		if (
			(s == "config-perfil" && $("#config-perfil-editar").is(":visible")) ||
			(s == "config-texto" && $("#config-texto-editar").is(":visible"))
		)
		{
			ajax = true; 
		}
		else
		{
			ajax = true;
			$(".conf-right-cnf-holder").hide();
			$("#"+s).show();
		}
	}
}

function ckSelfish(ob, cl)
{
	if ($(ob).hasClass("check_d0") || $(ob).hasClass("check_d1"))
		return false;

	if ($(ob).hasClass("check0"))
	{
		$(ob).removeClass("check0").addClass("check1");
		$('input[name="f-'+cl+'"]').val(1);
	}
	else
	{
		$(ob).removeClass("check1").addClass("check0");
		$('input[name="f-'+cl+'"]').val(0);
	}
}

function reloadPerfis()
{
	$.ajax({
		type: "post",
		url: "a.opcoes_perfis.php",
		dataType: "text",
		success: function(data)
		{
			$("#perfil-container").html(data);
		}
	});
}

function adicionarPerfil()
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.acesso.php",
			data: "a=cnf_editar",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 1)
				{
					$("#config-perfil").hide();
					$("#perfil-form").children("input:hidden").val(0);
					$("#acessos").find("a").attr("class", "check0 ml-36");
					$("#i-perfil-nome").val("");
					$("#i-perfil-ip").val("*");
					$("#perfil-title").html("Criar Novo Perfil");	
					$("#b-salvar").children("a").html("Salvar Novo Perfil");
					$("#config-perfil-editar").show();
					$("#i-perfil-nome").focus();
				}
				else
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
			}
		});
	}
}

function editarPerfil(id)
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.opcoes_perfil_editar.php",
			data: "id="+id,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					$('input[name="f-id"]').val(id);

					for (i=0; i<data[1].length; i++)
					{
						$('input[name="f-'+data[1][i].acesso+'"]').val(data[1][i].valor);
						$("#i-"+data[1][i].acesso).attr("class","check"+data[1][i].valor+" ml-36");
					}

					$("#i-perfil-nome").val(data[2]);
					$("#i-perfil-ip").val(data[3]);
					$("#perfil-title").html("Editar Perfil...");
					$("#b-salvar").children("a").html("Salvar Alterações");
	
					$("#config-perfil").hide();
					$("#config-perfil-editar").show();
				}
				else if (data[0] == 0)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
				}
			}
		});
	}
}

function salvarPerfil()
{
	$("#i-perfil-nome").val($.trim($("#i-perfil-nome").val()));
	if ($("#i-perfil-nome").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Digite o nome do perfil.',buttons:{'ok':{is_default:1,onclick:"foc('i-perfil-nome');ultimateClose();"}}});
		return false;
	}

	if (ajax)
	{
		ajax = false;
		$("#b-salvar").hide();
		$("#processando").show();
		$.ajax({
			type: "post",
			url: "a.opcoes_perfil_salvar.php",
			data: $("#perfil-form").serialize(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#processando").hide();
				$("#b-salvar").show();

				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 8)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Já existe um perfil com este nome.<br>Digite um nome diferente.',buttons:{'ok':{is_default:1,onclick:"foc('i-perfil-nome');ultimateClose();"}}});
				}
				else if (data[0] == 1)
				{
					//sucesso
					reloadPerfis();
					$("#config-perfil-editar").hide();
					$("#config-perfil").show();
					$(window).scrollTop(0);
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
				}
			}
		});
	}
}

function cancelarPerfil()
{
	$("#config-perfil-editar").hide();
	$("#config-perfil").show();
}

function removerPerfil(id,now)
{
	if (now)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "a.opcoes_perfil_remover.php",
			data: "id="+id,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 8)
					ultimateDialog({title:'Atenção!',color:'red',content:'Para manter a consistência dos dados, este registro não pode ser removido neste momento.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 1)
					reloadPerfis();
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
			}
		});
	}
	else
	{
		ultimateDialog({
			title:'Remover ?',
			color:'red',
			content:'Este perfil será removido do sistema.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
			buttons:{'Sim':{onclick:'removerPerfil('+id+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
		});
	}
}


function editarRelatoriosBO()
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.opcoes_relatorios_bo_editar.php",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					for (i=0; i<data[1].length; i++)
					{
						$('input[name="f-bo_'+data[1][i].acesso+'"]').val(data[1][i].valor);
						$("#i-bo_"+data[1][i].acesso).attr("class","check"+data[1][i].valor+" ml-36");
					}
				}
				else if (data[0] == 0)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
				}
			}
		});
	}
}

function salvarRelatoriosBO()
{
	if (ajax)
	{
		ajax = false;
		$("#b-bo-salvar").hide();
		$("#bo-processando").show();
		$.ajax({
			type: "post",
			url: "a.opcoes_relatorios_bo_salvar.php",
			data: $("#relatorios-form").serialize(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#bo-processando").hide();
				$("#b-bo-salvar").show();

				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					//sucesso
					$(window).scrollTop(0);
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
				}
			}
		});
	}
}

function editarTexto(id)
{
	if (ajax)
	{
		ajax = false;
		$.ajax({
			type: "post",
			url: "a.opcoes_texto_editar.php",
			data: "id="+id,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					$('input[name="f-id"]').val(id);
					$("#texto-title").html(data[1]);
					$("#i-texto").val(data[2]);

					$("#config-texto").hide();
					$("#config-texto-editar").show();
				}
				else if (data[0] == 0)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
				}
			}
		});
	}
}

function cancelarTexto()
{
	$("#config-texto-editar").hide();
	$("#config-texto").show();
}

function salvarTexto()
{
	$("#i-texto").val($.trim($("#i-texto").val()));
	if (ajax)
	{
		ajax = false;
		$("#t-salvar").hide();
		$("#t-processando").show();
		$.ajax({
			type: "post",
			url: "a.opcoes_texto_salvar.php",
			data: $("#texto-form").serialize(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#t-processando").hide();
				$("#t-salvar").show();

				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					//sucesso
					$("#config-texto-editar").hide();
					$("#config-texto").show();
					$(window).scrollTop(0);
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'ok':{is_default:1}}});
				}
			}
		});
	}
}

