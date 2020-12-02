$(document).ready(function(){
	$(document).on("mouseenter", ".hgl", function(){ $(this).css("background-color", "#ffffb4"); });
	$(document).on("mouseleave", ".hgl", function(){ $(this).css("background-color", "#ffffff"); });
});

function removerFeriado(id, now)
{
	if (ajax)
	{
		if (now)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "post",
				url: "a.feriado_remover.php",
				data: "id="+id,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 1)
						window.location.href = "a.feriado.php";
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
				content:'Este feriado será removido do sistema.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
				buttons:{'Sim':{onclick:'removerFeriado('+id+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
			});
		}
	}
}
