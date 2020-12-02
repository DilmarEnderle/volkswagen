var cl = "" //classe normal
var start = 0;
var map;
var idInfoBoxAberto;
var infoBox = [];
var markers = [];
var markerCluster;
var srch = 1;
var sel_regioes = [];
var sel_status = [];
var sel_estados = [];

var change_regioes = false;
var change_status = false;
var change_estados = false;

$(document).ready(function(){

	$(window).scroll(function(){
		if ($(window).scrollTop() >= $(document).height() - $(window).height() - 150)
			listarLicitacoes(true);
	});

	$(document).click(function(e){
		if (!$(e.target).hasClass("drp") && !$(e.target).parent().hasClass("drp"))
			closeAdvSearch();

		if (!$(e.target).hasClass("dbx") && !$(e.target).parent().hasClass("dbx"))
		{
			closeDropBox();
		}
	});

	$(document).keydown(function(e) {
		if (e.keyCode == 27)
		{
			closeAdvSearch();
			closeDropBox();
		}
	});

	$(document).on("keyup", "#i_search", function(e){
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


	if ($("#i-regioes").length)
	{
		sel_regioes = JSON.parse($("#i-regioes").val());
		sel_status = JSON.parse($("#i-status").val());
	}

	if ($("#i-estados").length)
	{
		if ($("#i-estados").val().length > 0)
		{
			var a = $("#i-estados").val().split(",");
			for (i=0; i<a.length; i++)
				sel_estados.push(a[i]);
		}
	}

	atualizarDropLb();

	$("#i-da-fr").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#i-da-to").inputmask("d/m/y", {"placeholder":"__/__/____"});

	srch = $("#i-search-type").val();

	if ($("#i-is-search").val() > 0)
		buscar();
	else
		goTab($("#i-tab").val());

	if ($("#i-pp").val() > 0)
	{
		ultimateDialog({
			title:'Adicionar Usuários',
			color:'green',
			content:'Usuários adicionais podem ser incluidos para o uso do sistema.<br><br><span class="bold">Você gostaria de adicionar um novo usuário?</span><br><br><br><br><a class="cb0 fl" href="javascript:void(0);" onclick="ckMostrar(this);" style="position: relative;">Não mostrar novamente</a>',
			buttons:{'Sim':{is_default:1,css_class:'ultimate-btn-red',href:'index.php?p=cli_usuarioeditar'}, 'Não':{onclick:'ultimateClose();',css_class:'ultimate-btn-gray ultimate-btn-right'}}
		});
	}

	dhtmlXCalendarObject.prototype.langData["pt"] = {
		dateformat: '%d/%m/%Y',
		monthesFNames: ["Janeiro","Fereveiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
		monthesSNames: ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
		daysFNames: ["Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado"],
		daysSNames: ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],
		weekstart: 7
	};

	var myCalendar = new dhtmlXCalendarObject(["i-da-fr","i-da-to"]);
	myCalendar.loadUserLanguage("pt");
	myCalendar.hideTime();
	myCalendar.showToday();
	myCalendar.setSkin("dhx_terrace");
});

function ckMostrar(ob)
{
	if ($(ob).hasClass("cb0"))
	{
		$(ob).attr("class", "cb1 fl");
		$.get("c.pp.php", {checked:1});
	}
	else
	{
		$(ob).attr("class", "cb0 fl");
		$.get("c.pp.php", {checked:0});
	}
}

function ckSingle(ob, cl, vl)
{
	if ($(ob).attr("data-mode") == "readonly")
		return false;
	
	$(".cl-"+cl).attr("class","rb0 cl-"+cl+" fl");
	$(ob).attr("class", "rb1 cl-"+cl+" fl");
	$('input[name="f-'+cl+'"]').val(vl);

	listarLicitacoes(false);
}

function ckSelfishONLY(ob)
{
	if ($(ob).hasClass("cb0"))
		$(ob).removeClass("cb0").addClass("cb1");
	else
		$(ob).removeClass("cb1").addClass("cb0");

	listarLicitacoes(false);
}

function goTab(n)
{
	if (ajax)
	{
		start = 0;

		if (n > 0)
		{
			var ob = $("#tab-"+n);
			$(".tb").removeClass("aba0 aba1").addClass("aba0");
			$(".cl").removeClass("cal0 cal1").addClass("cal0");
			$("#i-tab").val(n);

			if ($(ob).attr("data-tipo") == "C")
			{
				$(ob).removeClass("cal0").addClass("cal1");

				$(".lic_search").hide();
				calendario();
				$(".varal-dn").hide();
				$(".no-varal-dn").hide();
			}
			else
			{
				$(ob).addClass("aba1");

				$(".lic_search").show();
				if (n == 8 && !$("#i-regioes").length)
				{
					$(".varal-dn").show();
					$(".no-varal-dn").hide();
				}
				else
				{
					$(".varal-dn").hide();
					$(".no-varal-dn").show();
				}

				if (n == 14)
					$(".apl-sup").show();
				else
					$(".apl-sup").hide();

				listarLicitacoes(false);
			}
		}
	}
}

function listarLicitacoes(mais)
{
	if (mais)
	{
		if (ajax && start > 0 && $("#i-tab").val() != 12 && !$("#res-busca").length)
		{
			ajax = false;

			var reg = "";
			var sta = "";
			var est = "";
			var cki = [];

			if ($("#i-regioes").length)
				reg = sel_regioes.join();

			if ($("#i-status").length)
				sta = sel_status.join();

			if ($("#drop-3").length && $("#drop-3").is(":visible"))
			{
				est = sel_estados.join();
				if ($("#c-municipal").hasClass("cb1")) cki.push(1);
				if ($("#c-estadual").hasClass("cb1")) cki.push(2);
				if ($("#c-federal").hasClass("cb1")) cki.push(3);
			}

			$("#mais").append('<tr id="mais-carregando"><td class="italic" colspan="4" style="background-color:#ffffff;"><img src="img/spinner.gif"></td></tr>');
			$.ajax({
				type: "POST",
				url: "c.licitacao_lista.php",
				data: "aba="+$("#i-tab").val()+"&start="+start+"&mais=1&ordenacao="+$("#i-ordenacao").val()+"&ordem="+$("#i-ordem").val()+"&regioes="+reg+"&estados="+est+"&instancia="+cki.join()+"&status="+sta,
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
			ajax = false;

			if ($("#i-tab").val() == 0)
				$("#i-tab").val(7);

			var ob = $("#tab-"+$("#i-tab").val());
			$(".tb").removeClass("aba0 aba1").addClass("aba0");
			$(ob).removeClass("aba0").addClass("aba1");

			start = 0;
			$("#conteudo").html('<div style="margin: 60px 0; text-align: center; color: #888888; font-style:italic;">Carregando...aguarde...</div>');

			var reg = "";
			var sta = "";
			var est = "";
			var cki = [];

			if ($("#i-regioes").length)
				reg = sel_regioes.join();

			if ($("#i-status").length)
				sta = sel_status.join();

			if ($("#drop-3").length && $("#drop-3").is(":visible"))
			{
				est = sel_estados.join();
				if ($("#c-municipal").hasClass("cb1")) cki.push(1);
				if ($("#c-estadual").hasClass("cb1")) cki.push(2);
				if ($("#c-federal").hasClass("cb1")) cki.push(3);
			}

			if ($('input[name="f-conteudo"]').val() == 1)
			{
				$.ajax({
					type: "POST",
					url: "c.licitacao_lista.php",
					data: "aba="+$("#i-tab").val()+"&start="+start+"&mais=0&ordenacao="+$("#i-ordenacao").val()+"&ordem="+$("#i-ordem").val()+"&regioes="+reg+"&estados="+est+"&instancia="+cki.join()+"&status="+sta,
					dataType: "json",
					success: function(data)
					{
						//ajax = true;
						if (data[0] == 1) //sucesso
						{
							$("#conteudo").html(data[1]);
							start = data[2];
						}
						else
							$("#conteudo").html('<div style="margin: 60px 0; text-align: center; color: #ff0000;">Ocorreu um erro.</div>');

						$("#i-is-search").val(0);
						totalAbas();
					}
				});
			}
			else
			{
				$.ajax({
					type: "POST",
					url: "c.licitacao_mapa.php",
					data: "aba="+$("#i-tab").val()+"&ordenacao="+$("#i-ordenacao").val()+"&ordem="+$("#i-ordem").val()+"&regioes="+reg+"&estados="+est+"&instancia="+cki.join()+"&status="+sta,
					dataType: "json",
					success: function(data)
					{
						//ajax = true;
						if (data[0] == 1) //sucesso
						{
							if (data[2].length > 2)
							{
								$("#conteudo").html(data[3]+data[1]);
								initialize();
								carregarPontos(data[2]);
							}
							else
							{
								$("#conteudo").html(data[3]+'<div style="margin: 60px 0; text-align: center; color: #000000;">Nenhuma Licitação!</div>');
							}
						}
						else
							$("#conteudo").html('<div style="margin: 60px 0; text-align: center; color: #ff0000;">Ocorreu um erro.</div>');

						$("#i-is-search").val(0);
						totalAbas();
					}
				});
			}
		}
	}
}

function totalAbas()
{
	var m = [0,7,8,14,9,10,11,16,17,15];
	$.ajax({
		type: "post",
		url: "c.licitacao_totais.php",
		dataType: "json",
		success: function(data)
		{
			for (i=1; i<m.length; i++)
			{
				if (data[i] == 0)
					$("#tab-"+m[i]).children("span").remove();
				else
				{
					if ($("#tab-"+m[i]).children("span").length)
						$("#tab-"+m[i]).children("span").html(data[i]);
					else
						$("#tab-"+m[i]).append('<span>'+data[i]+'</span>');
				}
			}
			ajax = true;
		}
	});
}

function calendario()
{
	if (ajax)
	{
		$("#conteudo").html('<div style="margin: 60px 0; text-align: center; color: #888888; font-style:italic;">Carregando...aguarde...</div>');
		ajax = false;
		$.ajax({
			type: "POST",
			url: "c.calendario_top.php",
			data: "",
			dataType: "text",
			success: function(data)
			{
				ajax = true;
				$("#conteudo").html(data);
				atualizarCalendario();
			}
		});
	}
}

function atualizarCalendario()
{
	$.ajax({
		type: "GET",
		url: "c.calendario_gen.php",
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

function setView(v,ob)
{
	$(".vi").removeClass("vis1").addClass("vis0");
	$(ob).removeClass("vis0").addClass("vis1");
	$("#v_view").val(v);
	paintSelection();
}

function paintSelection()
{
	$("#calendar_holder").children(".sp,.sps").remove();
	if ($("#v_view").val() == 1)
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
	else if ($("#v_view").val() == 2 || $("#v_view").val() == 3)
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
				if ($("#v_view").val() == 2)
					$("#calendar_holder").prepend('<div class="sps" style="left: 0; width: 188px; top: '+((row*27)-27)+'px;"></div>');
				else
					$("#calendar_holder").prepend('<div class="sps" style="left: '+((col*27)-27)+'px; top: '+((row*27)-27)+'px;"></div>');
		});
	}
	atualizarCalendarioDados();
}

function atualizarCalendarioDados()
{
	$("#cal_data_holder").html('<img src="img/loader_32.gif" style="margin-top: 30px;">');
	$.ajax({
		type: "post",
		url: "c.calendario_dados.php",
		data: "y="+$("#i_year").val()+"&m="+$("#i_month").val()+"&d="+$("#v_day").val()+"&t="+$("#v_view").val()+"&mxy="+$("#v_mxy").val()+"&ord="+$("#i_ordenar").val(),
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

			$("#i-is-search").val(0);
			totalAbas();
		}
	});
}

function doPrint()
{
	var d = new Date();

	pw = window.open("","Imprimir","width=1160,height=600,left=20,top=20");
	pw.document.write('<!DOCTYPE html><html lang="pt"><head><meta charset="utf-8"><title>GELIC - Imprimir</title><link rel="stylesheet" type="text/css" href="css/cli_index.css?'+d.getTime()+'"><style type="text/css">body { background: none; }</style></head><body><div id="cal_data_holder" style="margin: 0 0 20px 0; width: 940px; overflow: hidden;">'+$("#cal_data_holder").html()+'</div></body></html>');

	printer = pw.document.getElementById("printer");
	$(printer).remove();
	
	content = pw.document.getElementById("cal_data_holder");
	$(content).find("a").attr("href", "javascript:void(0);");
}

function buscar()
{
	if (srch == 1)
	{
		$("#i_search").val($.trim($("#i_search").val()));
		if ($("#i_search").val().length == 0)
			return false;

		var dataString = "f-tipo="+srch+"&f-id="+$("#i_search").val();
	}
	else if (srch == 2)
	{
		$("#erro").hide();
		$("#i-orgao").val($.trim($("#i-orgao").val()));

		if ($("#i-da-fr").inputmask("unmaskedvalue").length < 8 &&
			$("#i-da-to").inputmask("unmaskedvalue").length < 8 &&
			$("#i-estado").val().length == 0 &&
			$("#i-cidade").val() == 0 &&
			$("#i-orgao").val().length == 0 &&
			$("#i-modalidade").val() == 0 &&
			$('#i-bx-status').find(":selected").val() == 0)
		{
			$("#erro").fadeIn(260).fadeOut(220).fadeIn(180).fadeOut(140).fadeIn(100);
			return false;
		}

		var dataString = "f-tipo="+srch+"&f-da-fr="+$("#i-da-fr").val()+"&f-da-to="+$("#i-da-to").val()+"&f-estado="+$("#i-estado").val()+"&f-cidade="+$("#i-cidade").val()+"&f-orgao="+encodeURIComponent($("#i-orgao").val())+"&f-modalidade="+$("#i-modalidade").val()+"&f-status="+$('#i-bx-status').find(":selected").val();
		$("#adv-search-box").hide();
	}

	if (ajax)
	{
		start = 0;
		$(".tb").removeClass("aba0 aba1").addClass("aba0");
		$("#conteudo").html('<div style="margin: 60px 0; text-align: center; color: #888888; font-style:italic;">Procurando...aguarde...</div>');
		ajax = false;
		$.ajax({
			type: "POST",
			url: "c.licitacao_buscar.php",
			data: dataString,
			dataType: "json",
			success: function(data)
			{
				//ajax = true;
				srch = 1;
				//$("#i_search").val("");
				$("#i-is-search").val(1);
				$("#conteudo").html(data[1]);
				totalAbas();
			}
		});
	}
}

function listarCidades()
{
	$("#i-cidade").val(0);
	$(".cid").hide();

	$.ajax({
		type: "GET",
		url: "c.cidades.php",
		data: "f-estado="+$("#i-estado").val(),
		dataType: "text",
		success: function(data)
		{
			$("#i-cidade").html(data);
			$(".cid").show();
		}
	});
}

function initialize()
{	
	var latlng = new google.maps.LatLng(-18.8800397, -47.05878999999999);
    var options = {
        zoom: 5,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("mapa"), options);
	idInfoBoxAberto = 0;
	infoBox = [];
	markers = [];
}

function abrirInfoBox(id, marker)
{
	if (typeof(idInfoBoxAberto) == 'number' && typeof(infoBox[idInfoBoxAberto]) == 'object')
	{
		infoBox[idInfoBoxAberto].close();
	}

	infoBox[id].open(map, marker);
	idInfoBoxAberto = id;
}

function carregarPontos(data)
{
	var latlngbounds = new google.maps.LatLngBounds();
	var pontos = JSON.parse(data);
	var latLng;
	$.each(pontos, function(index, ponto)
	{
		var latLng = new google.maps.LatLng(ponto.lat, ponto.lng);
		if (ponto.tipo == "lic")
			var im = 'img/marcador.png';
		else
			var im = 'img/vw.png';

		var marker = new google.maps.Marker({
			position: latLng,
			title: ponto.cidade,
			map: map,
			icon: im,
			tipo: ponto.tipo
		});

		var myOptions = {
			content: "<p>" + ponto.info + "</p>",
			pixelOffset: new google.maps.Size(-150, 0),
			infoBoxClearance: new google.maps.Size(1, 1)
        };

		infoBox[ponto.id] = new InfoBox(myOptions);
		infoBox[ponto.id].marker = marker;
			
		infoBox[ponto.id].listener = google.maps.event.addListener(marker, 'click', function (e) {
			abrirInfoBox(ponto.id, marker);
		});
			
		markers.push(marker);
			
		latlngbounds.extend(marker.position);
	});

	markerCluster = new MarkerClusterer(map, markers, {gridSize: 50});
	map.fitBounds(latlngbounds);
}

function advSearch(ob)
{
	if ($("#adv-search-box").is(":visible"))
	{
		closeAdvSearch();
	}
	else
	{
		srch = 2;
		var rt = ($(window).width() - ($(ob).offset().left + $(ob).outerWidth()));
		$("#adv-search-box").css("right", rt+"px");
		$("#adv-search-box").css("top", $(ob).offset().top+"px");
		$("#adv-search-box").show();
	}
}

function closeAdvSearch()
{
	$("#adv-search-box").hide();
	srch = 1;
}

function dropItens(n)
{
	if (!ajax) { return false; }

	if ($("#drop-"+n).hasClass("drop"))
	{
		closeDropBox();
	}
	else
	{
		if (n == 1)
		{
			$("#drop-2").removeClass("drop");
			$("#drop-2").children("img").attr("src", "img/a-down-g.png");
		}
		else if (n == 2)
		{
			$("#drop-1").removeClass("drop");
			$("#drop-1").children("img").attr("src", "img/a-down-g.png");
		}

		$("#drop-"+n).addClass("drop");
		$("#drop-"+n).children("img").attr("src", "img/a-up-g.png");

		if (!$("#drop-box").length)
			$("body").append('<div id="drop-box" class="dbx"></div>');

		$("#drop-box").css("top", $("#drop-"+n).offset().top + 31);
		$("#drop-box").css("left", $("#drop-"+n).offset().left);
		$("#drop-box").html('<span class="lh-20 italic" style="padding: 0 10px;">Carregando...</span>');
		$("#drop-box").show();

		ajax = false;

		if (n == 2)
			$("#drop-box").load("c.drop-box.php?d=2&v="+sel_regioes.join(), function(){ ajax = true; });
		else if (n == 1)
			$("#drop-box").load("c.drop-box.php?d=1&v="+sel_status.join(), function(){ ajax = true; });
		else if (n == 3)
			$("#drop-box").load("c.drop-box.php?d=3&v="+sel_estados.join(), function(){ ajax = true; });
	}
}

function closeDropBox()
{
	if ($("#drop-box").is(":visible") && (change_regioes || change_status || change_estados))
		listarLicitacoes(false);

	$("#drop-box").hide();
	change_regioes = false;
	change_status = false;
	change_estados = false;

	$("#drop-1").removeClass("drop");
	$("#drop-1").children("img").attr("src", "img/a-down-g.png");

	$("#drop-2").removeClass("drop");
	$("#drop-2").children("img").attr("src", "img/a-down-g.png");

	$("#drop-3").removeClass("drop");
	$("#drop-3").children("img").attr("src", "img/a-down-g.png");
}

function selDbItem(ob, v, n)
{
	if (n == 2) //regioes
	{
		idx = sel_regioes.indexOf(v);
		if (idx > -1)
		{
			sel_regioes.splice(idx, 1);
			$(ob).removeClass("drop-box-item1").addClass("drop-box-item0");
		}
		else
		{
			sel_regioes.push(v);
			$(ob).removeClass("drop-box-item0").addClass("drop-box-item1");
		}
		change_regioes = true;
	}
	else if (n == 1) //status
	{
		idx = sel_status.indexOf(v);
		if (idx > -1)
		{
			sel_status.splice(idx, 1);
			$(ob).removeClass("drop-box-item1").addClass("drop-box-item0");
		}
		else
		{
			sel_status.push(v);
			$(ob).removeClass("drop-box-item0").addClass("drop-box-item1");
		}
		change_status = true;
	}
	else if (n == 3) //estados
	{
		idx = sel_estados.indexOf(v);
		if (idx > -1)
		{
			sel_estados.splice(idx, 1);
			$(ob).removeClass("drop-box-item1").addClass("drop-box-item0");
		}
		else
		{
			sel_estados.push(v);
			$(ob).removeClass("drop-box-item0").addClass("drop-box-item1");
		}
		change_estados = true;
	}

	atualizarDropLb();
}

function atualizarDropLb()
{
	var lb = [];
	if (sel_regioes.indexOf(1) > -1) lb.push('1/2');
	if (sel_regioes.indexOf(3) > -1) lb.push('3');
	if (sel_regioes.indexOf(4) > -1) lb.push('4');
	if (sel_regioes.indexOf(5) > -1) lb.push('5');
	if (sel_regioes.indexOf(6) > -1) lb.push('6');
		
	$("#drop-2").children("span").html(lb.join(', '));
	if (lb.length == 0)
		$("#drop-2").children("span").html("Todas");

	if (sel_status.length > 0)
		$("#drop-1").children("span").html("Status ("+sel_status.length+")");
	else
		$("#drop-1").children("span").html("Todos");

	if (sel_estados.length > 0)
		$("#drop-3").children("span").html("Estado(s): "+sel_estados.length);
	else
		$("#drop-3").children("span").html("Todos");
}