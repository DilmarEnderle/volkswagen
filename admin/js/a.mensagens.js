var tab_selected = 0;
var sel = [{a:[]},{a:[]},{a:[]}];
var tex = [];
var start = 0;
var msg_selected = [];

$(document).ready(function(){

	$(document).click(function(e){
		if (!$(e.target).hasClass("drp") && !$(e.target).parent().hasClass("drp"))
		{
			closeDropFiltro();
		}
		else if ($(e.target).hasClass("hdf"))
		{
			closeDropFiltro();
		}
	});

	$(document).keydown(function(e) {
		if (e.keyCode == 27 && $("#drop-filtro").is(":visible"))
			closeDropFiltro();
	});

	$(window).scroll(function(){
		if ($(window).scrollTop() >= $(document).height() - $(window).height() - 150)
        	listarMensagens(true);
	});

	tex[0] = ["- recipiente -", "Recipiente(s)"];
	tex[1] = ["- notificação -", "Notificação(ões)"];
	tex[2] = ["- método -", "Método(s)"];

	autoTotals();
	setInterval(function(){autoTotals()}, 5000);

	tab(0);
});

function tab(t)
{
	if (!ajax) return false;

	tab_selected = t;

	if (t == 0)
	{
		$("#tab-underline").css("background-color","#0077ee");
		$("#tab-title").css("color","#0077ee");
		$("#tab-title").html("Novas");
		$("#tab0").attr("class","bt-novas1");
		$("#tab1").attr("class","bt-processando0");
		$("#tab2").attr("class","bt-erro0");
		$("#tab3").attr("class","bt-sucesso0");
	}
	else if (t == 1)
	{
		$("#tab-underline").css("background-color","#f0b400");
		$("#tab-title").css("color","#f0b400");
		$("#tab-title").html("Processando");
		$("#tab0").attr("class","bt-novas0");
		$("#tab1").attr("class","bt-processando1");
		$("#tab2").attr("class","bt-erro0");
		$("#tab3").attr("class","bt-sucesso0");
	}
	else if (t == 2)
	{
		$("#tab-underline").css("background-color","#ef0000");
		$("#tab-title").css("color","#ef0000");
		$("#tab-title").html("Erro");
		$("#tab0").attr("class","bt-novas0");
		$("#tab1").attr("class","bt-processando0");
		$("#tab2").attr("class","bt-erro1");
		$("#tab3").attr("class","bt-sucesso0");
	}
	else if (t == 3)
	{
		$("#tab-underline").css("background-color","#00c400");
		$("#tab-title").css("color","#00c400");
		$("#tab-title").html("Sucesso");
		$("#tab0").attr("class","bt-novas0");
		$("#tab1").attr("class","bt-processando0");
		$("#tab2").attr("class","bt-erro0");
		$("#tab3").attr("class","bt-sucesso1");
	}

	$("#m-holder").html('');
	listarMensagens(false);
}

function buscaFiltro(n)
{
	if (!ajax) { return false; }

	if ($("#filtro-"+n).hasClass("drop"))
	{
		$("#filtro-"+n).removeClass("drop");
		if (sel[n].a.length > 0)
			$("#filtro-"+n).children("img").attr("src", "img/a-down-r.png");
		else
			$("#filtro-"+n).children("img").attr("src", "img/a-down-g.png");
		$("#drop-filtro").hide();
	}
	else
	{
		for (i=0; i<3; i++)
		{
			$("#filtro-"+i).removeClass("drop");
			if (sel[i].a.length > 0)
				$("#filtro-"+i).children("img").attr("src", "img/a-down-r.png");
			else
				$("#filtro-"+i).children("img").attr("src", "img/a-down-g.png");
		}

		$("#filtro-"+n).addClass("drop");
		if (sel[n].a.length > 0)
			$("#filtro-"+n).children("img").attr("src", "img/a-up-r.png");
		else
			$("#filtro-"+n).children("img").attr("src", "img/a-up-g.png");

		$("#drop-filtro").css("top", $("#filtro-"+n).offset().top + 31);
		$("#drop-filtro").css("left", $("#filtro-"+n).offset().left);
		$("#drop-filtro").html('<span class="lh-20 italic" style="padding: 0 10px;">Carregando...</span>');
		$("#drop-filtro").show();

		ajax = false;
		$("#drop-filtro").load("a.mensagens_drop.php?d="+n+"&v="+filtroJoin(n), function(){ ajax = true; });
	}
}

function isSelected(n, v)
{
	for (i=0; i<sel[n].a.length; i++)
	{
		if (sel[n].a[i].v == v)
		{
			return i;
			break;
		}
	}
	return -1;
}

function filtroTitle(n)
{
	r = "";
	for (i=0; i<sel[n].a.length; i++)
		r += "\n"+sel[n].a[i].lb;

	if (r.length > 0)
		r = r.substr(1);

	return r;
}

function selItem(ob, v, n, lb, uf)
{
	idx = isSelected(n, v);
	if (idx > -1)
	{
		sel[n].a.splice(idx, 1);
		$(ob).removeClass("drop-item1").addClass("drop-item0");
	}
	else
	{
		sel[n].a.push({v:v,lb:lb,uf:uf});
		$(ob).removeClass("drop-item0").addClass("drop-item1");
	}

	atualizaFiltro(n, true);
	autoTotals();
}

function atualizaFiltro(n, open)
{
	ud = 'down';
	if (open) ud = 'up';

	if (sel[n].a.length > 0)
	{
		$("#filtro-"+n).removeClass("filtro0").addClass("filtro1");
		$("#filtro-"+n).attr("title",filtroTitle(n));
		$("#filtro-"+n).html(sel[n].a.length+" "+tex[n][1]+'<img src="img/a-'+ud+'-r.png">');
		$("#limpa-"+n).show();
	}
	else
	{
		$("#filtro-"+n).removeClass("filtro1").addClass("filtro0");
		$("#filtro-"+n).attr("title","");
		$("#filtro-"+n).html(tex[n][0]+'<img src="img/a-'+ud+'-g.png">');
		$("#limpa-"+n).hide();
	}
}

function filtroJoin(n)
{
	r = "";
	for (i=0; i<sel[n].a.length; i++)
		r += ","+sel[n].a[i].v;

	if (r.length > 0)
		r = r.substr(1);

	return r;
}

function limpaFiltro(n)
{
	sel[n].a = [];
	$("#filtro-"+n).removeClass("filtro1").addClass("filtro0");
	$("#filtro-"+n).attr("title","");
	$("#filtro-"+n).html(tex[n][0]+'<img src="img/a-down-g.png">');
	$("#limpa-"+n).hide();
	closeDropFiltro();
	autoTotals();
}

function closeDropFiltro()
{
	$("#drop-filtro").hide();
	for (i=0; i<3; i++)
	{
		$("#filtro-"+i).removeClass("drop");
		if (sel[i].a.length > 0)
			$("#filtro-"+i).children("img").attr("src", "img/a-down-r.png");
		else
			$("#filtro-"+i).children("img").attr("src", "img/a-down-g.png");
	}
}

function autoTotals()
{
	$.ajax({
		type: "POST",
		url: "a.mensagens_totais.php",
		data: "filtro-recip="+filtroJoin(0)+"&filtro-notif="+filtroJoin(1)+"&filtro-metod="+filtroJoin(2),
		dataType: "json",
		success: function(data)
		{
			$("#tab0").children("span").html(data[1]);
			$("#tab1").children("span").html(data[2]);
			$("#tab2").children("span").html(data[3]);
			$("#tab3").children("span").html(data[4]);
		}
	});
}

function listarMensagens(mais)
{
	if (mais)
	{
		if (ajax && start > 0)
		{
			$("#loader-more").show();
			ajax = false;
			$.ajax({
				type: "POST",
				url: "a.mensagens_listar.php",
				data: "tab="+tab_selected+"&filtro-recip="+filtroJoin(0)+"&filtro-notif="+filtroJoin(1)+"&filtro-metod="+filtroJoin(2)+"&start="+start,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					$("#loader-more").hide();
					$("#m-holder").append(data[1]);
					start = data[2];
				}
			});
		}
	}
	else
	{
		if (ajax)
		{
			$("#loader-more").show();
			msg_selected = [];
			$("#sel-all").attr("class","mch0 fr");
			atualizarBotoesAcao();
			start = 0;
			ajax = false;
			$.ajax({
				type: "POST",
				url: "a.mensagens_listar.php",
				data: "tab="+tab_selected+"&filtro-recip="+filtroJoin(0)+"&filtro-notif="+filtroJoin(1)+"&filtro-metod="+filtroJoin(2)+"&start="+start,
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					$("#loader-more").hide();
					$("#m-holder").html(data[1]);
					start = data[2];
				}
			});
		}
	}
}

function selecionarMensagem(ob, id)
{
	idx = msg_selected.indexOf(id);
	if (idx > -1)
	{
		msg_selected.splice(idx,1);
		$(ob).attr("class","mch0");
	}
	else
	{
		msg_selected.push(id);
		$(ob).attr("class","mch1");
	}

	atualizarBotoesAcao();
}

function atualizarBotoesAcao()
{
	if (msg_selected.length > 0)
	{
		$("#bm-sel").html('Selecionadas (<a class="bold">'+msg_selected.length+'</a>)');

		$("#bm-rem").attr("class", "sel-rem1");
		$("#bm-rem").attr("onclick", "removerMensagens();");

		if (tab_selected == 2) //com erro
		{
			$("#bm-ref").attr("class", "sel-ref1 mr-8");
			$("#bm-ref").attr("onclick", "reenviarMensagens();");
		}
		else
		{
			$("#bm-ref").attr("class", "sel-ref0 mr-8");
			$("#bm-ref").attr("onclick", "");
		}
	}
	else
	{
		$("#bm-sel").html('Selecionadas (0)');

		$("#bm-rem").attr("class", "sel-rem0");
		$("#bm-rem").attr("onclick", "");

		$("#bm-ref").attr("class", "sel-ref0 mr-8");
		$("#bm-ref").attr("onclick", "");
	}

	if (tab_selected == 0)
	{
		$("#bm-sel").show();
		$("#bm-ref").hide();
		$("#bm-rem").show();
		$("#sel-all").show();
	}
	else if (tab_selected == 2)
	{
		$("#bm-sel").show();
		$("#bm-ref").show();
		$("#bm-rem").show();
		$("#sel-all").show();
	}
	else
	{
		$("#bm-sel").hide();
		$("#bm-ref").hide();
		$("#bm-rem").hide();
		$("#sel-all").hide();
	}
}

function removerMensagens()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "POST",
			url: "a.mensagens_remover.php",
			data: "ids="+msg_selected.join(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');

				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					for (i=0; i<msg_selected.length; i++)
						$("#msg-"+msg_selected[i]).remove();

					msg_selected = [];
					atualizarBotoesAcao();
					autoTotals();
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			}
		});
	}
}

function reenviarMensagens()
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "POST",
			url: "a.mensagens_reenviar.php",
			data: "ids="+msg_selected.join(),
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');

				if (data[0] == 9)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'ok':{is_default:1}}});
				}
				else if (data[0] == 1)
				{
					for (i=0; i<msg_selected.length; i++)
						$("#msg-"+msg_selected[i]).remove();

					msg_selected = [];
					atualizarBotoesAcao();
					autoTotals();
				}
				else
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
				}
			}
		});
	}
}

function selecionarTodas(ob)
{
	if ($(ob).hasClass("mch0"))
	{
		$(ob).attr("class","mch1 fr");

		$("#m-holder").children().each(function(i, elm) {
			id = parseInt($(elm).attr("id").split("-")[1]);
			idx = msg_selected.indexOf(id);
			if (idx < 0)
			{
				msg_selected.push(id);
				$(elm).children(".cb-holder").children("a").attr("class","mch1");
			}
		});
	}
	else
	{
		$(ob).attr("class","mch0 fr");

		$("#m-holder").children().each(function(i, elm) {
			$(elm).children(".cb-holder").children("a").attr("class","mch0");
		});

		msg_selected = [];
	}

	atualizarBotoesAcao();
}

function abrirMensagem(id)
{
	if (ajax)
	{
		ajax = false;
		ultimateLoader(true,'');
		$.ajax({
			type: "POST",
			url: "a.mensagens_abrir.php",
			data: "id="+id+"&tab="+tab_selected,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');

				if (data[0] == 1)
					ultimateDialog({
						title:'Mensagem',
						width:900,
						content:data[1],
						buttons:{'Ok':{is_default:1}}
					});
				else
					ultimateDialog({title:'Erro.',color:'red',content:'Mensagem não encontrada.',buttons:{'Ok':{is_default:1}}});
			}
		});
	}
}

