var tab = 1;
var srch = 1;
var filtro_status = [];
var filtro_estados = [];
var filtro_cidades = [];
var filtro_regioes = [];
var filtro_ultimas = [];
var filtro_dn = [];
var cl = "" //classe normal
var view = 1; //visualizaçao (1=mes,2=semana,3=dia)
var f1a = new Array();
var f1s = "";
var start = 0;
var sel = [];
var tex = [];

$(document).ready(function(){

	$(document).on("mouseenter", ".hgl", function(){ $(this).css("background-color", "#ffffb4"); });
	$(document).on("mouseleave", ".hgl", function(){ $(this).css("background-color", "#ffffff"); });

	$(document).click(function(e){
		if (!$(e.target).hasClass("drp") && !$(e.target).parent().hasClass("drp"))
		{
			$("#mais-filtros").hide();
			srch = 1;
			closeDropFiltro();
		}
		else if ($(e.target).hasClass("hdf"))
		{
			closeDropFiltro();
		}
	});

	$(document).keydown(function(e) {
		if (e.keyCode == 27)
		{
			if ($("#drop-filtro").is(":visible"))
				closeDropFiltro();
			else
			{
				$("#mais-filtros").hide();
				srch = 1;
			}
		}
	});

	$(document).on("keyup blur", "#filtro-op,#filtro-adve,#filtro-numero", function(e){
		if ($(this).val().length > 0)
		{
			$(this).css("color", "#ff0000");
			$(this).css("border-color", "#ee0000");
		}
		else
		{
			$(this).css("color", "#282828");
			$(this).css("border-color", "#cccccc");
		}
	});

	$(document).on("keyup blur", "#filtro-data-de,#filtro-data-ate", function(e){
		if ($(this).inputmask("unmaskedvalue").length == 8)
		{
			$(this).css("color", "#ff0000");
			$(this).css("border-color", "#ee0000");
		}
		else
		{
			$(this).css("color", "#282828");
			$(this).css("border-color", "#cccccc");
		}
	});


	$(window).scroll(function(){
		if ($(window).scrollTop() >= $(document).height() - $(window).height() - 150)
			listarLicitacoes(true);
	});
	
	$(document).on("keyup", "#i-search", function(e){
		if (e.keyCode == 13)
			buscar();
	});

	$(document).on('click','.wd,.wk,.wt,.dsl',function(){
		$("#day_"+$("#v_day").val()).removeClass("dsl").addClass(cl);
		cl = $(this).attr("class");
		$("#v_day").val(parseInt($(this).attr("id").split("_")[1]));
		$(this).removeClass("wd wk wt").addClass('dsl');
		paintSelection();
	});

	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});
	sel.push({a:[]});

	tex[0] = ["- status -", "Status"];
	tex[1] = ["- estado -", "Estado(s)"];
	tex[2] = ["- cidade -", "Cidade(s)"];
	tex[3] = ["- região -", "Região(ões)"];
	tex[4] = ["- últimas -", "Últimas"];
	tex[5] = ["- dn -", "DN(s)"];

	sel[0].a = filtro_status;
	atualizaFiltro(0, false);

	sel[1].a = filtro_estados;
	atualizaFiltro(1, false);

	sel[2].a = filtro_cidades;
	atualizaFiltro(2, false);

	sel[3].a = filtro_regioes;
	atualizaFiltro(3, false);

	sel[4].a = filtro_ultimas;
	atualizaFiltro(4, false);

	sel[5].a = filtro_dn;
	atualizaFiltro(5, false);

	$("#filtro-data-de").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#filtro-data-ate").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#filtro-adve").inputmask({"mask":"9","repeat":10,"greedy":false});

	goTab(tab);
});


function goTab(n)
{
	if (ajax)
	{
		start = 0;
		tab = n;

		if (n > 0)
		{
			var ob = $("#tab-"+n);
			$(".tb").removeClass("aba0 aba1").addClass("aba0");
			$(ob).addClass("aba1");

			if ($(ob).attr("data-tipo") == "C")
				calendario();
			else
				listarLicitacoes(false);
		}
		else
		{
			buscar();
		}
	}
}

function maisFiltros(ob)
{
	if ($("#mais-filtros").is(":visible"))
	{
		$("#mais-filtros").hide();
		srch = 1;
	}
	else
	{
		srch = 2;
		$("#mais-filtros").css("left", $(ob).offset().left-418);
		$("#mais-filtros").css("top", $(ob).offset().top-10);
		$("#filtro-erro").hide();
		$("#mais-filtros").show();
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

function filtroTitle(n)
{
	r = "";
	for (i=0; i<sel[n].a.length; i++)
		r += "\n"+sel[n].a[i].lb;

	if (r.length > 0)
		r = r.substr(1);

	return r;
}

function buscaFiltro(n)
{
	if (!ajax) { return false; }

	$("#filtro-erro").hide();

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
		for (i=0; i<sel.length; i++)
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

		if (n == 2)
			$("#drop-filtro").load("a.drop-filtro.php?d="+n+"&v="+filtroJoin(n)+"&e="+filtroJoin(1), function(){ ajax = true; });
		else
			$("#drop-filtro").load("a.drop-filtro.php?d="+n+"&v="+filtroJoin(n), function(){ ajax = true; });
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

function atualizaFiltro(n, open)
{
	ud = 'down';
	if (open) ud = 'up';

	if (sel[n].a.length > 0)
	{
		$("#filtro-"+n).removeClass("filtro0").addClass("filtro1");
		$("#filtro-"+n).attr("title",filtroTitle(n));
		if (n == 4)
			$("#filtro-"+n).html(sel[n].a[0].lb+'<img src="img/a-'+ud+'-r.png">');
		else
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

function selItem(ob, v, n, lb, uf)
{
	idx = isSelected(n, v);
	if (idx > -1)
	{
		sel[n].a.splice(idx, 1);
		$(ob).removeClass("drop-item1").addClass("drop-item0");
		if (n == 1) //estado
			atualizarCidadesFiltro();
	}
	else
	{
		if (n == 4)
		{
			sel[n].a = [{v:v,lb:lb,uf:uf}];
			$(".ult").removeClass("drop-item1").addClass("drop-item0");
		}
		else
			sel[n].a.push({v:v,lb:lb,uf:uf});

		$(ob).removeClass("drop-item0").addClass("drop-item1");
	}

	atualizaFiltro(n, true);
}

function atualizarCidadesFiltro()
{
	estados = [];
	cidades_new_array = [];

	for (i=0; i<sel[1].a.length; i++)
		estados.push(sel[1].a[i].v); 

	for (i=0; i<sel[2].a.length; i++)
		if (estados.indexOf(sel[2].a[i].uf) > -1)
			cidades_new_array.push(sel[2].a[i]);

	sel[2].a = cidades_new_array;

	if (sel[2].a.length > 0)
	{
		$("#filtro-2").removeClass("filtro0").addClass("filtro1");
		$("#filtro-2").attr("title",filtroTitle(2));
		$("#filtro-2").html(sel[2].a.length+" "+tex[2][1]+'<img src="img/a-down-r.png">');
		$("#limpa-2").show();
	}
	else
	{
		$("#filtro-2").removeClass("filtro1").addClass("filtro0");
		$("#filtro-2").attr("title","");
		$("#filtro-2").html(tex[2][0]+'<img src="img/a-down-g.png">');
		$("#limpa-2").hide();
	}
}

function limpaFiltro(n)
{
	sel[n].a = [];
	$("#filtro-"+n).removeClass("filtro1").addClass("filtro0");
	$("#filtro-"+n).attr("title","");
	$("#filtro-"+n).html(tex[n][0]+'<img src="img/a-down-g.png">');
	$("#limpa-"+n).hide();
	closeDropFiltro();
	if (n == 1)
		atualizarCidadesFiltro();
}

function closeDropFiltro()
{
	$("#drop-filtro").hide();
	for (i=0; i<sel.length; i++)
	{
		$("#filtro-"+i).removeClass("drop");
		if (sel[i].a.length > 0)
			$("#filtro-"+i).children("img").attr("src", "img/a-down-r.png");
		else
			$("#filtro-"+i).children("img").attr("src", "img/a-down-g.png");
	}
}

function listarLicitacoes(mais)
{
	if (mais)
	{
		if (ajax && start > 0 && tab != 12)
		{
			ajax = false;
			$("#mais").append('<div id="mais-carregando" class="content-inside" style="padding:20px 0;text-align:center;"><img src="img/spinner.gif"></div>');
			$.ajax({
				type: "POST",
				url: "a.licitacao_lista.php",
				data: "id_aba="+tab+"&start="+start+"&mais=1",
				dataType: "json",
				success: function(data)
				{
					$("#mais-carregando").remove();
					ajax = true;
					$("#mais").append(data[1]);
					start = data[2];
				}
			});
		}
	}
	else
	{
		if (ajax)
		{
			start = 0;
			$("#search-bar").hide();
			$("#conteudo").html('<div class="content-inside t16 bold italic gray-88" style="padding-bottom:100px;">Carregando... aguarde...</div>');
			ajax = false;
			$.ajax({
				type: "POST",
				url: "a.licitacao_lista.php",
				data: "id_aba="+tab+"&start="+start+"&mais=0",
				dataType: "json",
				success: function(data)
				{
					ajax = true;
					if (data[4] > 0)
						$("#search-bar").show();
					else
						$("#search-bar").hide();

					if (data[0] == 1)
					{
						$("#conteudo").html(data[1]);
						start = data[2];
						$("#p-title").html(data[3]);
					
						if (data[5] > 0)
						{
							if ($("#tab-"+tab).children("span").length)
								$("#tab-"+tab).children("span").html(data[5]);
							else
								$("#tab-"+tab).html($("#tab-"+tab).html()+'<span>'+data[5]+'</span>');
						}
						else
						{
							$("#tab-"+tab).children("span").remove();
						}
					}
					else
						$("#conteudo").html('<div class="content-inside t16 bold italic red" style="padding-bottom:100px;">Ocorreu um erro.</div>');
				}
			});
		}
	}
}

function calendario()
{
	if (ajax)
	{
		$("#search-bar").hide();
		$("#conteudo").html('<div class="content-inside t16 bold italic gray-88" style="padding-bottom:100px;">Carregando... aguarde...</div>');
		ajax = false;
		$.ajax({
			type:"post",
			url:"a.calendario_top.php",
			data: "",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#conteudo").html(data[1]);

				if (data[0] != 9)
					atualizarCalendario();
			}
		});
	}
}

function atualizarCalendario()
{
	$.ajax({
		type: "GET",
		url: "a.calendario_gen.php",
		data: "y="+$("#i_year").val()+"&m="+$("#i_month").val()+"&d="+$("#v_day").val()+"&mxy="+$("#v_mxy").val(),
		dataType: "json",
		success: function(data)
		{
			if (data[0] == 1)
			{
				cl = data[1];
				$("#v_day").val(parseInt(data[2]));
				$("#calendar_holder").html(data[3]);
				paintSelection();
			}
		}
	});
}

function paintSelection()
{
	$("#calendar_holder").children(".sp,.sps").remove();
	if (view == 1)
	{
		var st = false;
		var sp = false;
		var at = 0;
		var row = 1;
		var col = 0;
		var id = "no_0";
		$("#calendar_holder").children("a").each(function(){
			at++;
			col++;
			if (at == 8 || at == 15 || at == 22 || at == 29 || at == 36)
			{
				row++;
				col = 1;
			}
			id = $(this).attr("id");

			if (id.charAt(0) == "d" && st == false)
			{
				st = true;
				$("#calendar_holder").prepend('<div class="sp" style="top: 0; left: '+((col*27)-27)+'px; height: 28px;"></div><div class="sp" style="top: 27px; left: 0; width: '+((col*27)-27)+'px;"></div><div class="sp" style="top: 0; left: '+((col*27)-27)+'px; width: '+(((7-col+1)*27)+1)+'px;"></div>');
			}

			if (col == 7 && id.charAt(0) == "d")
				$("#calendar_holder").prepend('<div class="sp" style="top: '+((row*27)-27)+'px; right: 0; height: 28px;"></div>');

			if (col == 1 && id.charAt(0) == "d")
				$("#calendar_holder").prepend('<div class="sp" style="top: '+((row*27)-27)+'px; left: 0; height: 28px;"></div>');

			if (id.charAt(0) == "n" && st == true && sp == false)
			{
				sp = true;
				$("#calendar_holder").prepend('<div class="sp" style="top: '+((row*27)-27)+'px; left: '+((col*27)-27)+'px; width: '+((7-col+1)*27)+'px;"></div>');
				if (col > 1)
					$("#calendar_holder").prepend('<div class="sp" style="top: '+((row*27)-27)+'px; left: '+((col*27)-27)+'px; height: 28px;"></div><div class="sp" style="left: 0; top: '+(row*27)+'px; width: '+((col*27)-27)+'px;"></div>');
			}
		});
	}
	else if (view == 2 || view == 3)
	{
		var at = 0;
		var row = 1;
		var col = 0;
		var id = "no_0";
		$("#calendar_holder").children("a").each(function(){
			at++;
			col++;
			if (at == 8 || at == 15 || at == 22 || at == 29 || at == 36)
			{
				row++;
				col = 1;
			}
			id = $(this).attr("id");
			
			if (id.charAt(0) == "d" && parseInt(id.split("_")[1]) == $("#v_day").val())
				if (view == 2)
					$("#calendar_holder").prepend('<div class="sps" style="left: 0; width: 188px; top: '+((row*27)-27)+'px;"></div>');
				else
					$("#calendar_holder").prepend('<div class="sps" style="left: '+((col*27)-27)+'px; top: '+((row*27)-27)+'px;"></div>');
		});
	}
	atualizarCalendarioDados();
}

function atualizarCalendarioDados()
{
	$("#cal_data_holder").html('<img src="img/loader64.gif" style="margin: 40px 0 0 518px;">');
	$.ajax({
		type: "post",
		url: "a.calendario_dados.php",
		data: "y="+$("#i_year").val()+"&m="+$("#i_month").val()+"&d="+$("#v_day").val()+"&t="+view+"&mxy="+$("#v_mxy").val()+"&ord="+$("#i_ordenar").val(),
		dataType: "text",
		success: function(data)
		{
			
			$("#cal_data_holder").html(data);

			var mx = 0; //row 1
			$("#cal_data_holder").find(".d_row_1").each(function(){ if ($(this).height() > mx) mx = $(this).height(); });
			$(".d_row_1").height(mx);

			var mx = 0; //row 2
			$("#cal_data_holder").find(".d_row_2").each(function(){ if ($(this).height() > mx) mx = $(this).height(); });
			$(".d_row_2").height(mx);

			var mx = 0; //row 3
			$("#cal_data_holder").find(".d_row_3").each(function(){ if ($(this).height() > mx) mx = $(this).height(); });
			$(".d_row_3").height(mx);

			var mx = 0; //row 4
			$("#cal_data_holder").find(".d_row_4").each(function(){ if ($(this).height() > mx) mx = $(this).height(); });
			$(".d_row_4").height(mx);

			var mx = 0; //row 5
			$("#cal_data_holder").find(".d_row_5").each(function(){ if ($(this).height() > mx) mx = $(this).height(); });
			$(".d_row_5").height(mx);

			var mx = 0; //row 6
			$("#cal_data_holder").find(".d_row_6").each(function(){ if ($(this).height() > mx) mx = $(this).height(); });
			$(".d_row_6").height(mx);

		}
	});
}

function doPrint()
{
	var d = new Date();

	pw = window.open("","Imprimir","width=1160,height=600,left=20,top=20");
	pw.document.write('<!DOCTYPE html><html lang="pt"><head><meta charset="utf-8"><title>GELIC - Imprimir</title><link rel="stylesheet" type="text/css" href="css/a.licitacao.css?'+d.getTime()+'"><style type="text/css">body { background: none; }</style></head><body><div id="cal_data_holder" style="margin: 0 0 20px 0; width: 1100px; overflow: hidden;">'+$("#cal_data_holder").html()+'</div></body></html>');

	printer = pw.document.getElementById("printer");
	$(printer).remove();
	
	content = pw.document.getElementById("cal_data_holder");
	$(content).find("a").attr("href", "javascript:void(0);");
}

function setView(v,ob)
{
	$(".vi").removeClass("vis1").addClass("vis0");
	$(ob).removeClass("vis0").addClass("vis1");
	view = v;
	paintSelection();
}

function dropFilter()
{
	if (!ajax) { return false; }
	if ($("#filter_1").hasClass("drop"))
	{
		$("#filter_1").removeClass("drop");
		$("#filter_1_img").attr("src", "img/drop0.png");
		$("#drop_box").hide();
	}
	else
	{
		$("#filter_1").addClass("drop");
		$("#filter_1_img").attr("src", "img/drop1.png");
		
		$("#drop_box").css("top", $("#filter_1").offset().top + 30);
		$("#drop_box").css("left", $("#filter_1").offset().left);
		$("#drop_box").css("height", "20px");
		$("#drop_box").html("Carregando...");
		$("#drop_box").show();
		$("#drop_box").load("util_drop.php?d=1&v="+f1s, function(){
			$("#drop_box").css("height", "476px");
		});
	}
}

function cf1(ob,id)
{
	if (id == 0)
	{
		f1a.length = 0;
		$(".clitem").removeClass("drop_item1").addClass("drop_item");
	}
	else
	{
		if (f1a.indexOf(id) > -1)
		{
			f1a.splice(f1a.indexOf(id),1);
			$(ob).removeClass("drop_item1").addClass("drop_item");
		}
		else
		{
			f1a.push(id);
			$(ob).removeClass("drop_item").addClass("drop_item1");
		}
	}
	
	f1s = "";
	if (f1a.length == 0)
	{
		$("#todos_1").removeClass("drop_item").addClass("drop_item1");
		$("#filter_1_text").html("Todos");
	}
	else
	{
		$("#todos_1").removeClass("drop_item1").addClass("drop_item");
		$("#filter_1_text").html(f1a.length + " Cliente(s)");
		for ($i=0; $i<f1a.length; $i++)
		{
			if ($i==0)
			{
				f1s = f1a[$i];
			}
			else
			{
				f1s += "," + f1a[$i];
			}
		}
	}
	atualizarCalendarioDados();
}

function upDropAll()
{
	$("#filter_1").removeClass("drop");
	$("#filter_1_img").attr("src", "img/drop0.png");
	$("#drop_box").hide();
}

function buscar()
{
	$("#filtro-erro").hide();

	$("#filtro-op").val($.trim($("#filtro-op").val()));
	$("#filtro-adve").val($.trim($("#filtro-adve").val()));
	$("#filtro-numero").val($.trim($("#filtro-numero").val()));
	if ($("#filtro-adve").val() == 0)
		$("#filtro-adve").val("");

	var a = [];
	a.push($("#filtro-op"));
	a.push($("#filtro-adve"));
	a.push($("#filtro-numero"));

	for (i=0; i<a.length; i++)
	{
		if (a[i].val().length > 0)
		{
			$(a[i]).css("color", "#ff0000");
			$(a[i]).css("border-color", "#ee0000");
		}
		else
		{
			$(a[i]).css("color", "#282828");
			$(a[i]).css("border-color", "#cccccc");
		}
	}

	if (srch == 2)
	{
		if (sel[0].a.length == 0 && sel[1].a.length == 0 && sel[2].a.length == 0 && sel[3].a.length == 0 && sel[4].a.length == 0 && sel[5].a.length == 0 && $("#filtro-op").val().length == 0 && $("#filtro-adve").val().length == 0 && $("#filtro-numero").val().length == 0 && $("#filtro-data-de").inputmask("unmaskedvalue").length < 8 && $("#filtro-data-ate").inputmask("unmaskedvalue").length < 8)
		{
			$("#filtro-erro").fadeIn(260).fadeOut(220).fadeIn(180).fadeOut(140).fadeIn(100);
			return false;
		}
		var dataString = "f-tipo=2&f-dn="+filtroJoin(5)+"&f-status="+filtroJoin(0)+"&f-estados="+filtroJoin(1)+"&f-cidades="+filtroJoin(2)+"&f-regioes="+filtroJoin(3)+"&f-ultimas="+filtroJoin(4)+"&f-orgao="+encodeURIComponent($("#filtro-op").val())+"&f-adve="+$("#filtro-adve").val()+"&f-numero="+encodeURIComponent($("#filtro-numero").val())+"&f-data-de="+$("#filtro-data-de").val()+"&f-data-ate="+$("#filtro-data-ate").val();
	}
	else
	{
		$("#i-search").val($.trim($("#i-search").val()));
		if ($("#i-search").val().length == 0)
			return false;

		var dataString = "f-tipo=1&f-busca="+$("#i-search").val();
	}

	if (ajax)
	{
		$("#mais-filtros").hide();
		$(".tb").removeClass("aba0 aba1").addClass("aba0");

		start = 0;

		$("#search-bar").hide();
		$("#conteudo").html('<div class="content-inside t16 bold italic gray-88" style="padding-bottom:100px;">Procurando...</div>');
		ajax = false;
		$.ajax({
			type: "POST",
			url: "a.licitacao_busca.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				$("#search-bar").show();

				if (data[0] == 1)
				{
					$("#conteudo").html(data[1]);
					$("#p-title").html(data[2]);
				}
				else
					$("#conteudo").html('<div class="content-inside t16 bold italic red" style="padding-bottom:100px;">Ocorreu um erro.</div>');
			}
		});
	}
}

