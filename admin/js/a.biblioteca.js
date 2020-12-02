var fa = new Array();
var fs = "";
var first = 1;

$(document).keydown(function(e) {
	if (e.keyCode == 27)
		upDropAll();
});

$(document).ready(function(){
	$(document).click(function(e){
		if (!$(e.target).hasClass("drp"))
			upDropAll();
	});

	doSearch();
});

function ckSingle(ob, vl, cl)
{
	$(".cl_"+cl).removeClass("check_dot1").addClass("check0");
	$("#arquivo-pen").hide();
	$("#link-pen").hide();
	if ( $(ob).hasClass("check0") )
	{
		$(ob).removeClass("check0").addClass("check_dot1");
		$('input[name="f-'+cl+'"]').val(vl);
		if (vl == 0)
			$("#arquivo-pen").show();
		else
			$("#link-pen").show();
	}
}

function ckTipoArq()
{
	if ($("#arq-c").hasClass("check0"))
	{
		$("#arq-c").removeClass("check0").addClass("check1");
		$("#i-tipo-arq").val(1);
	}
	else
	{
		$("#arq-c").removeClass("check1").addClass("check0");
		$("#i-tipo-arq").val(0);
	}
}

function ckTipoLnk()
{
	if ($("#lnk-c").hasClass("check0"))
	{
		$("#lnk-c").removeClass("check0").addClass("check1");
		$("#i-tipo-lnk").val(1);
	}
	else
	{
		$("#lnk-c").removeClass("check1").addClass("check0");
		$("#i-tipo-lnk").val(0);
	}
}

function dropFilter()
{
	if (!ajax) { return false; }
	if ($("#filter").hasClass("drop"))
	{
		$("#filter").removeClass("drop");
		$("#filter_img").attr("src", "img/drop0.png");
		$("#drop_box").hide();
	}
	else
	{
		$("#filter").addClass("drop");
		$("#filter_img").attr("src", "img/drop1.png");

		$("#drop_box").css("top", $("#filter").offset().top + 30);
		$("#drop_box").css("left", $("#filter").offset().left);
		$("#drop_box").css("height", "20px");
		$("#drop_box").html("Carregando...");
		$("#drop_box").show();
		$("#drop_box").load("util_drop.php?d=3&v="+fs, function(){
			$("#drop_box").css("height", "190px");
		});
	}
}

function upDropAll()
{
	$("#filter").removeClass("drop");
	$("#filter_img").attr("src", "img/drop0.png");
	$("#drop_box").hide();
}

function cf(ob,id)
{
	if (id == '')
	{
		fa.length = 0;
		$(".esta").removeClass("drop_item1").addClass("drop_item");
	}
	else
	{
		if (fa.indexOf(id) > -1)
		{
			fa.splice(fa.indexOf(id),1);
			$(ob).removeClass("drop_item1").addClass("drop_item");
		}
		else
		{
			fa.push(id);
			$(ob).removeClass("drop_item").addClass("drop_item1");
		}
	}
	
	fs = "";
	if (fa.length == 0)
	{
		$("#todos").removeClass("drop_item").addClass("drop_item1");
		$("#filter_text").html("Todos");
	}
	else
	{
		$("#todos").removeClass("drop_item1").addClass("drop_item");
		for ($i=0; $i<fa.length; $i++)
		{
			if ($i == 0)
			{
				fs = fa[$i];
			}
			else
			{
				fs += "," + fa[$i];
			}
		}
		$("#filter_text").html(fs);
	}
}

function doSearch()
{
	if (ajax)
	{
		$("#i-busca").val($.trim($("#i-busca").val()));
		ajax = false;
		$("#results").html('<div class="row_wide_content" style="height: 200px;"><img src="img/loader64.gif" style="position: absolute; top: 68px; left: 518px;"></div>');
		var dataString = "f-tipo-arq="+$("#i-tipo-arq").val()+"&f-tipo-lnk="+$("#i-tipo-lnk").val()+"&f-busca="+$("#i-busca").val()+"&f-estados="+fs+"&f-first="+first;
		first = 0;
		$.ajax({
			type: "POST",
			url: "a.biblioteca_buscar.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				if (data[0] == 9)
				{
					alert("Acesso Restrito");
					$("#results").html('<div class="row_wide_content" style="height: 60px;"></div>');
				}
				else if (data[0] == 1)
				{
					$("#results").html(data[1]);
				}
				else
				{
					alert("Ocorreu um erro.");
					$("#results").html('<div class="row_wide_content" style="height: 60px;"></div>');
				}
			}
		});
	}
}

function removerItem(id, now)
{
	if (ajax)
	{
		if (now)
		{
			ajax = false;
			ultimateLoader(true,'');
			$.ajax({
				type: "post",
				url: "a.biblioteca_remover.php",
				data: "id="+id,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					ultimateLoader(false,'');
					if (data[0] == 9)
						ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
					else if (data[0] == 1)
						doSearch()
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
				content:'Este registro será removido do sistema.<br><br><span class="bold">Você tem certeza que deseja continuar?</span>',
				buttons:{'Sim':{onclick:'removerItem('+id+',true);ultimateClose();',is_default:1,css_class:'ultimate-btn-red ultimate-btn-left ultimate-btn-60'},'Cancelar':{css_class:'ultimate-btn-gray ultimate-btn-right'}}
			});
		}
	}
}

function expandLib(id,ob)
{
	$.post("a.biblioteca_expandir.php",{id:id},function(data){ $(ob).parent().html(data); });
}
