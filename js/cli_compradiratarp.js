function verAnexos(id)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "c.compradiratarp_anexos.php",
			data: "id="+id,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] == 1)
					ultimateDialog({title:'Anexo(s)',color:'green',width:700,content:data[1],buttons:{'Ok':{is_default:1}}});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});					
			}
		});
	}
}


function verObservacoes(id)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "c.compradiratarp_obs.php",
			data: "id="+id,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] == 1)
					ultimateDialog({title:'Observações',color:'green',width:700,content:data[1],buttons:{'Ok':{is_default:1}}});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});					
			}
		});
	}
}
