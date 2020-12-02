var uf = '';
$(document).ready(function(){
	$(document).on("mouseenter", ".hgl", function(){ $(this).css("background-color", "#ffffb4"); });
	$(document).on("mouseleave", ".hgl", function(){ $(this).css("background-color", "#ffffff"); });

	if (uf.length == 2)
		$("#i-estado").val(uf);

	listarCidades();
});

function listarCidades()
{
	if (ajax)
	{
		ajax = false;
		$("#i-estado").hide();
		$("#i-cidades").html('<div class="content-inside italic lh-30 t14 gray-88" style="height: 30px;">Carregando...</div>');
		$.ajax({
			type: "post",
			url: "a.cidade_listar.php",
			data: "uf="+$("#i-estado").val(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#i-cidades").html(data[1]);
				if (data[2] > 0)
					$(".dh").show();
				else
					$(".dh").hide();
				$("#i-estado").show();
			}
		});
	}
}

function removerCidade(id, now)
{
	if (ajax)
	{
		if (now)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "post",
				url: "a.cidade_remover.php",
				data: "id="+id,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 8)
						ultimateDialog({title:'Atenção!',color:'red',content:'Para manter a consistência dos dados, este registro não pode ser removido neste momento.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						listarCidades();
					else
						ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});					
				}
			});
		}
		else
		{
			ultimateDialog({
				title:'Remover ?',
				color:'red',
				content:'Esta cidade será removida do sistema.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
				buttons:{'Sim':{onclick:'removerCidade('+id+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
			});
		}
	}
}
