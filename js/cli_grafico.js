var sta = [];

$(document).ready(function(){
	$("#rel-periodo-fr").inputmask("d/m/y", {"placeholder":"__/__/____"});
	$("#rel-periodo-to").inputmask("d/m/y", {"placeholder":"__/__/____"});

	$(document).click(function(e){
		if (!$(e.target).hasClass("drp") && !$(e.target).parent().hasClass("drp"))
			closeDropBox();
	});

	$(document).keydown(function(e) {
		if (e.keyCode == 27)
			closeDropBox();
	});

	$.getScript("https://www.google.com/jsapi", function(){
		google.load("visualization", "1", {packages:["corechart"]});
	});
});

function drawChart()
{
	if (ajax)
	{
		ajax = false;
		//$("#loader").show();
		//$("#chart_div").hide();
		$.ajax({
			type: 'POST',
			url: "cli_grafico_info.php",
			data: "de="+$("#rel-periodo-fr").val()+"&ate="+$("#rel-periodo-to").val()+"&id_tipo="+$("#i_tipo").val(),
			dataType: "json",
			success: function(data)
			{
				//$("#loader").hide();
				//$("#chart_div").show();
				var total = 0;
				for ($i=1; $i<data.length; $i++)
					total += parseInt(data[$i][1]);

				ajax = true;
				if ($("#i_tipo").val() == 0 && total > 0)
				{
					var data = google.visualization.arrayToDataTable(data);
					var options = {'title':$("#i_tipo option:selected").text()+':','width':940,'height':600,is3D: true,slices: { 0: {color: '#26aa30'}, 1: {color: '#ca1e04'}, 2:{color: '#b0a8bd'} }};
					var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
					chart.draw(data, options);
				}
				else if ($("#i_tipo").val() == 1 && total > 0)
				{
					var data = google.visualization.arrayToDataTable(data);
					var options = {'title':$("#i_tipo option:selected").text()+':','width':940,'height':600,is3D: true};
					var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
					chart.draw(data, options);
				}
				else
				{
					$("#chart_div").html('<span class="t16 bold gray_88 italic" style="display: block; width: 100%; text-align: center; line-height: 144px;">- dados não disponíveis -</span>');
				}
			}
		});
	}
}

function relatorio(formato)
{
	if (!ajax) return false;

	if ($("#rel-selected").val().length == 0)
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Selecione o relatório.',buttons:{'Ok':{is_default:1}}});
		return false;
	}

	//if (formato != 'xlsx')
	//{
	//	ultimateDialog({title:'Erro.',color:'red',content:'Relatório não disponível.',buttons:{'Ok':{is_default:1}}});
	//	return false;
	//}

	if ($("#rel-selected").val() == "r1_1")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_1_1",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_1_1.php",
						data: "formato="+formato,
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r1_2")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_1_2",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_1_2.php",
						data: "formato="+formato,
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r1_3")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_1_3",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_1_3.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r1_4")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_1_4",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_1_4.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r2_1")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_2_1",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_2_1.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",

						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r2_2")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_2_2",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_2_2.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r2_3")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_2_3",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_2_3.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val(),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r2_4")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_2_4",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_2_4.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r3_1")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_3_1",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_3_1.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r4_1")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_4_1",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_4_1.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r4_2")
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Relatório não disponível.',buttons:{'Ok':{is_default:1}}});
		return false;
	}
	else if ($("#rel-selected").val() == "r4_3")
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Relatório não disponível.',buttons:{'Ok':{is_default:1}}});
		return false;
	}
	else if ($("#rel-selected").val() == "r4_4")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_4_4",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_4_4.php",
						data: "formato="+formato+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val(),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r5_1")
	{
		ajax = false;
		ultimateLoader(true, 'Verificando acesso...aguarde...');
		$.ajax({
			type: "post",
			url: "c.rel_acesso.php",
			data: "a=rel_5_1",
			dataType: "json",
			success: function(data)
			{
				ajax = true;
				ultimateLoader(false,'');
				if (data[0] != 1)
				{
					ultimateDialog({title:'<img src="img/padlock.png" style="float:left;margin-top:5px;">',color:'red',content:'Acesso Restrito.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else if (sta.length == 0)
				{
					ultimateDialog({title:'Erro.',color:'red',content:'Selecione 1 ou mais status.',buttons:{'Ok':{is_default:1}}});
					return false;
				}
				else
				{
					ajax = false;
					ultimateLoader(true, 'Gerando relatório...aguarde...');
					$.ajax({
						type: "post",
						url: "c.rel_5_1.php",
						data: "formato="+formato+"&status="+sta.join()+"&periodo-fr="+$("#rel-periodo-fr").val()+"&periodo-to="+$("#rel-periodo-to").val()+"&detalhamento="+($("#rel-detalhamento").hasClass("cb1") << 0),
						dataType: "json",
						success: function(data)
						{
							ultimateLoader(false,'');
							ajax = true;
							if (data[0] == 1)
								window.location.href = "c.rel_download.php?rel="+$("#rel-selected").val()+"&formato="+formato;
							else
								ultimateDialog({title:'Erro.',color:'red',content:'Ocorreu um erro. Entre em contato com o administrador do sistema.',buttons:{'Ok':{is_default:1}}});
						}
					});
				}
			}
		});
	}
	else if ($("#rel-selected").val() == "r6_1")
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Relatório não disponível.',buttons:{'Ok':{is_default:1}}});
		return false;
	}
	else if ($("#rel-selected").val() == "r6_2")
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Relatório não disponível.',buttons:{'Ok':{is_default:1}}});
		return false;
	}
	else if ($("#rel-selected").val() == "r6_3")
	{
		ultimateDialog({title:'Erro.',color:'red',content:'Relatório não disponível.',buttons:{'Ok':{is_default:1}}});
		return false;
	}
	else if ($("#rel-selected").val() == "motd")
	{
		drawChart();
	}
}

function justCheck(ob)
{
	if ($(ob).hasClass("cb0"))
		$(ob).removeClass("cb0").addClass("cb1");
	else
		$(ob).removeClass("cb1").addClass("cb0");
}



function relOpcoes()
{
	var dsc = {
		r1_1:"Total de DNs cadastrados no sistema.",
		r1_2:"DNs que já acessaram o sistema.",
		r1_3:"DNs que aderiram ao sistema no período indicado.",
		r1_4:"DNs que aderiram ao sistema no período indicado e o total cumulativo por mês.",
		r2_1:"Quantidade de DNs que acessaram o sistema no mês.",
		r2_2:"Quantidade de DNs que acessaram o sistema no ano.",
		r2_3:"Quantas vezes o DN acessou o sistema no período.",
		r2_4:"Quantas vezes os DNs da região acessaram o sistema no período.",
		r3_1:"APLs enviadas no período indicado separadas por mês. Inclui o status da APL e a atuação da fábrica.",
		r4_1:"Total de licitações para cada instância no período selecionado.",
		r4_2:'<a style="color:#ff0000;font-weight:bold;">- não disponível -</a>',
		r4_3:'<a style="color:#ff0000;font-weight:bold;">- não disponível -</a>',
		r4_4:"Total de licitações e veículos no período escolhido baseado na data de abertura separado por instância",
		r5_1:"Quantidade de licitações e veículos para cada status.",
		r6_1:'<a style="color:#ff0000;font-weight:bold;">- não disponível -</a>',
		r6_2:'<a style="color:#ff0000;font-weight:bold;">- não disponível -</a>',
		r6_3:'<a style="color:#ff0000;font-weight:bold;">- não disponível -</a>',
		motd:''
	};

	if ($("#rel-selected").val() != '')
	{
		if (dsc[$("#rel-selected").val()].length > 0)
		{
			$("#descricao").html(dsc[$("#rel-selected").val()]);
			$("#descricao").show();
		}
		else
		{
			$("#descricao").html('');
			$("#descricao").hide();
		}
	}
	else
	{
		$("#descricao").html('');
		$("#descricao").hide();
	}

	var status_show = ['r5_1'];
	var date_show = ['r1_3','r1_4','r2_1','r2_2','r2_3','r2_4','r3_1','r4_1','r4_4','r5_1'];
	var detalhamento_show = ['r1_3','r1_4','r2_1','r2_2','r2_4','r3_1','r4_1','r5_1'];
	var pdf_show = [];
	var xls_show = ['r1_1','r1_2','r1_3','r1_4','r2_1','r2_2','r2_3','r2_4','r3_1','r4_1','r4_4','r5_1'];


	if (status_show.indexOf($("#rel-selected").val()) > -1)
		$(".sta").show();
	else
		$(".sta").hide();


	if (date_show.indexOf($("#rel-selected").val()) > -1)
		$(".dt").show();
	else
		$(".dt").hide();


	if (detalhamento_show.indexOf($("#rel-selected").val()) > -1)
		$(".detalhamento").show();
	else
		$(".detalhamento").hide();


	if (pdf_show.indexOf($("#rel-selected").val()) > -1)
		$(".rel-btn-pdf").show();
	else
		$(".rel-btn-pdf").hide();


	if (xls_show.indexOf($("#rel-selected").val()) > -1)
		$(".rel-btn-xlsx").show();
	else
		$(".rel-btn-xlsx").hide();


	if ($("#rel-selected").val() == "motd")
		$(".btg").show();
	else
		$(".btg").hide();
}

function dropBox()
{
	if (!ajax) { return false; }
	if ($("#status").hasClass("dropped"))
	{
		$("#status").removeClass("dropped");
		$("#status").children("img").attr("src", "img/a-down-g.png");
		$("#drop-box").hide();
	}
	else
	{
		$("#status").addClass("dropped");
		$("#status").children("img").attr("src", "img/a-up-g.png");
		
		$("#drop-box").css("top", $("#status").offset().top + 34);
		$("#drop-box").css("left", $("#status").offset().left);
		$("#drop-box").html("Carregando...");
		$("#drop-box").show();

		ajax = false;
		$("#drop-box").load("c.relatorios-drop.php?d=0&v="+sta.join(), function(){
			ajax = true;
		});
	}
}

function closeDropBox()
{
	$("#status").removeClass("dropped");
	$("#status").children("img").attr("src", "img/a-down-g.png");
	$("#drop-box").hide();
}

function selItem(ob, v)
{
	idx = sta.indexOf(v);
	if (idx > -1)
	{
		sta.splice(idx, 1);
		$(ob).removeClass("drop-item1").addClass("drop-item0");
	}
	else
	{
		sta.push(v);
		$(ob).removeClass("drop-item0").addClass("drop-item1");
	}

	$("#status").children("span").html(sta.length);
}


