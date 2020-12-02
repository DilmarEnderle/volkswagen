var rtext = "";
var work_id = 0;

$(document).ready(function(){
	$(document).on("mouseenter", ".hgl", function(){ $(this).css("background-color", "#ffffb4"); });
	$(document).on("mouseleave", ".hgl", function(){ $(this).css("background-color", "#ffffff"); });
	listarDocumentos();
});

function toggleSec(sec)
{
	if ( $("#holder-"+sec).is(":visible") )
	{
		$("#holder-"+sec).hide();
		$(".drop").removeClass("bar-0 bar-0d").addClass("bar-0");
		$("#drop-"+sec).removeClass("bar-0 bar-0d").addClass("bar-0");
	}
	else
	{
		$(".holder").hide();
		$(".drop").removeClass("bar-0 bar-0d").addClass("bar-0");
		$("#holder-"+sec).show();
		$("#drop-"+sec).removeClass("bar-0 bar-0d").addClass("bar-0d");
	}
}

function listarDocumentos()
{
	$.ajax({
		type: "POST",
		url: "a.documento_lista.php",
		dataType: "json",
		success: function(data)
		{
			$("#doc-holder").html(data[1]);
		}
	});
}


function removerDocumento(id,now)
{
	if (now)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "post",
			url: "a.documento_remover.php",
			data: "f-id-documento="+id,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] == 9)
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				else if (data[0] == 1)
					window.location.href = "a.documento.php";
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
			content:'Este documento será removido do sistema.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
			buttons:{'Sim':{onclick:'removerDocumento('+id+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
		});
	}
}


