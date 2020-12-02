function removerSolicitacao(id, now)
{
	if (ajax)
	{
		if (now)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "post",
				url: "c.compradir_remover.php",
				data: "id="+id,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Você não tem permissão para executar esta operação.',buttons:{'Ok':{is_default:1}}});
					if (data[0] == 8)
						ultimateDialog({title:'Acesso Restrito!',color:'red',content:'Esta solicitação não pode ser removida.',buttons:{'Ok':{is_default:1}}});
					else if (data[0] == 1)
						window.location.href = "index.php?p=cli_compradir";
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
				content:'Está solicitação será removida do sistema.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
				buttons:{'Sim':{onclick:'removerSolicitacao('+id+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
			});
		}
	}
}

