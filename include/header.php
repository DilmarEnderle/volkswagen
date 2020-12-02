<?php
	error_reporting(-1);
	ini_set("error_log", "arquivos/php_errors_cli.log");

	$postArray = isset($_REQUEST['p']) ? explode("/",$_REQUEST['p']) : array();
	if (isset($postArray[0]) && file_exists($postArray[0]."_mod.php"))
		$endereco = $postArray[0];
	else
		$endereco = "home";

	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	require_once "config.php";
	require_once "essential.php";

	$dCor_base = '548ec7';  //cor fundo topo pagina/linha rodape/titulo/subtitulo
	$dCor_menu = 'ff6600';  //cor dos botoes de menu topo da pagina
	$dCor_aba = '548ec7';   //cor do texto abas ativas
	$dCor_botao_1 = 'ff6600';
	$dCor_botao_2 = 'bebebe';

	if (isInside())
	{
		$sInside_id = $_SESSION[SESSION_ID];
		$sInside_tipo = $_SESSION[SESSION_TYPE];
		$sInside_parent = $_SESSION[SESSION_PARENT];

		if ($sInside_tipo == 2 || $sInside_tipo == 1)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$db = new Mysql();
		$db->query("SELECT cor_base, cor_menu, cor_aba, cor_botao_1, cor_botao_2 FROM gelic_clientes_personalizacao WHERE id_cliente = $cliente_parent");
		if ($db->nextRecord())
		{
			$dCor_base = $db->f("cor_base");
			$dCor_menu = $db->f("cor_menu");
			$dCor_aba = $db->f("cor_aba");
			$dCor_botao_1 = $db->f("cor_botao_1");
			$dCor_botao_2 = $db->f("cor_botao_2");
		}
	}
	

	//********************************************************************
	//***  calcular logo/botoes sociais/botao sair  ***
	//********************************************************************
	if (getLightIndex($dCor_base) >= 132)
	{
		$dLogo = 'logo-b.png';
		$dSocial = 'black';
		$dSocial_img = 'w';
		$dCor_sair_btn_bg = getShadeColor($dCor_base, -20);
	}
	else
	{
		$dLogo = 'logo-w.png';
		$dSocial = 'white';
		$dSocial_img = '88';
		$dCor_sair_btn_bg = getShadeColor($dCor_base, 20);
	}

	if (getLightIndex($dCor_sair_btn_bg) >= 132)
		$dCor_sair_btn_tx = getShadeColor($dCor_sair_btn_bg, -50);
	else
		$dCor_sair_btn_tx = 'ffffff';//getShadeColor($dCor_sair_btn_bg, 50);
	//********************************************************************
	


	//********************************************************************
	//***  calcular menu fundo/menu border  ***
	//********************************************************************
	if (getLightIndex($dCor_menu) >= 200)
		$dCor_menu_bg = '888888';
	else
		$dCor_menu_bg = 'ffffff';

	$dCor_menu_border = getShadeColor($dCor_menu, -14);
	//********************************************************************



	//********************************************************************
	//***  calcular titulo/subtitulo  ***
	//********************************************************************
	if (getLightIndex($dCor_base) <= 210)
		$dCor_tit = $dCor_base;
	else
		$dCor_tit = getShadeColor($dCor_base, -30);
	//********************************************************************

	

	//********************************************************************
	//***  calcular aba fundo/border/contagem fundo/contagem texto  ***
	//********************************************************************
	if (getLightIndex($dCor_aba) >= 200)
	{
		$dCor_aba_bg = '888888';
		$dCor_aba_border = '888888';
		$dCor_aba_span_bg = $dCor_aba;
		$dCor_aba_span_tx = '000000';
	}
	else
	{
		$dCor_aba_bg = 'f5f5f5';
		$dCor_aba_border = $dCor_aba;
		$dCor_aba_span_bg = $dCor_aba;
		$dCor_aba_span_tx = 'ffffff';	
	}
	//********************************************************************



	//********************************************************************
	//***  calcular btn1 texto/border  ***
	//********************************************************************
	if (getLightIndex($dCor_botao_1) >= 160)
		$dCor_botao_1_tx = getShadeColor($dCor_botao_1, -50);
	else
		$dCor_botao_1_tx = 'ffffff';

	if (getLightIndex($dCor_botao_1) >= 220)
		$dCor_botao_1_border = 'border: 1px solid #dddddd;';
	else
		$dCor_botao_1_border = '';
	//********************************************************************



	//********************************************************************
	//***  calcular btn2 texto/border  ***
	//********************************************************************
	if (getLightIndex($dCor_botao_2) >= 160)
		$dCor_botao_2_tx = getShadeColor($dCor_botao_2, -50);
	else
		$dCor_botao_2_tx = 'ffffff';

	if (getLightIndex($dCor_botao_2) >= 220)
		$dCor_botao_2_border = 'border: 1px solid #dddddd;';
	else
		$dCor_botao_2_border = '';
	//********************************************************************
?>
<!DOCTYPE HTML>
<html lang="pt">
	<head>
		<title>Gelic - Gestão de Licitações</title>
		<meta charset="UTF-8">
		<meta name="format-detection" content="telephone=no">
		<meta name="author" content="Bigmaster Licitações">
		<meta name="description" content="O GELIC é especializado na Gestão das Licitações para empresas das mais diversas áreas e tem seu foco no Monitoramento, Análise e Participação das Licitações Brasileiras com possibilidade de exclusividade no Ramo de Atividade.">
		<meta name="keywords" content="gelic, licitações">
		<meta name="title" content="Gelic - Gestação de Licitações">
		<meta name="robots" content="index,follow">
		<meta property="og:url" content="https://gelicprime.com.br">
		<meta property="og:type" content="website">
		<meta property="og:title" content="Gelic - Gestação de Licitações">
		<meta property="og:image" content="https://gelicprime.com.brimg/logo.png">
		<meta property="og:description" content="O GELIC é especializado na Gestão das Licitações para empresas das mais diversas áreas e tem seu foco no Monitoramento, Análise e Participação das Licitações Brasileiras com possibilidade de exclusividade no Ramo de Atividade.">
		<link rel="stylesheet" type="text/css" href="css/style.css?<?php echo VERSION; ?>">
		<link rel="stylesheet" type="text/css" href="css/<?php echo $endereco ?>.css?<?php echo VERSION; ?>">
		<link rel="stylesheet" type="text/css" href="css/c.ultimate.css?<?php echo VERSION; ?>">
		<?php
			if ($endereco == "cli_index" || $endereco == "cli_open")
				echo '<link rel="stylesheet" type="text/css" href="css/dhtmlxcalendar.css?<?php echo VERSION; ?>">';
		?>
		<link rel="icon" href="img/favicon.png" type="image/png">
	</head>

	<style>
		.htop {
			background-color: #<?php echo $dCor_base; ?>;
			height: 40px;
			}

		footer { border-top: 5px solid #<?php echo $dCor_base; ?>; }

		.top_tit {
			float: left;
			font-size: 30px;
			font-weight: bold;
			line-height: 36px;
			margin: 0 0 25px;
			text-transform: uppercase;
			color: #<?php echo $dCor_tit; ?>;
			}

		.send_tit {
			font-size: 20px;
			font-weight: bold;
			line-height: 24px;
			margin: 0 0 11px;
			text-transform: uppercase;
			color: #<?php echo $dCor_tit; ?>;
			}

		.bt-sair {
			color: #<?php echo $dCor_sair_btn_tx; ?>;
			background-color: #<?php echo $dCor_sair_btn_bg; ?>;
			float: left;
			line-height: 30px;
			padding: 0 24px 0 0;
			text-align: center;
			text-transform: uppercase;
			width: 74px;
			}

		#top-nav {
			overflow: hidden;
			border-bottom: 1px dashed #<?php echo $dCor_tit; ?>;
			padding-bottom: 40px;
			}

		#top-nav ul {
			list-style:none;
			position:relative;
			float:left;
			margin:0;
			padding:0
			}

		#top-nav ul li {
			position: relative;
			float: left;
			margin: 0 6px 0 0;
			padding: 0;
			}

		#top-nav ul li a {
			position: relative;
			display: block;
			border: 1px solid #bebebe;
			color: #bebebe;
			font-size: 16px;
			line-height: 28px;
			padding: 0 16px;
			text-transform: uppercase;
			text-decoration: none;
			white-space: nowrap;
			}

		#top-nav ul li a:hover, #top-nav ul li a.active {
			text-decoration: none;
			border: 1px solid #<?php echo $dCor_menu_border; ?>;
			color: #<?php echo $dCor_menu; ?>;
			background-color: #<?php echo $dCor_menu_bg; ?>;
			}

		#top-nav ul div {
			display: none;
			position:absolute;
			top: 100%;
			width: 600px;
			left: 0;
			background:#fff;
			padding: 3px 0 0 0;
			}

		#top-nav ul div a {
			position: relative;
			display: inline-block;
			border: 1px solid #bebebe;
			color: #bebebe;
			font-size: 14px;
			line-height: 25px;
			padding: 0 16px;
			text-transform: uppercase;
			text-decoration: none;
			white-space: nowrap;
			float: left;
			}

		#top-nav ul li:hover > div { display: block; }

		.aba1 {
			position: relative;
			display: inline-block;
			float: left;
			line-height: 48px;
			text-decoration: none;
			color: #<?php echo $dCor_aba; ?>;
			font-size: 13px;
			text-transform: uppercase;
			padding: 0 9px;
			margin-top: 11px;
			border: 1px solid #<?php echo $dCor_aba_border; ?>;
			border-bottom: none;
			background-color: #<?php echo $dCor_aba_bg; ?>;
			}

		.aba1 span {
			position: absolute;
			right: 5px;
			top: -10px;
			display: inline-block;
			background-color: #<?php echo $dCor_aba_span_bg; ?>;
			color: #<?php echo $dCor_aba_span_tx; ?>;
			line-height: 20px;
			font-size: 11px;
			padding: 0 6px;
			border-radius: 10px;
			min-width: 20px;
			box-sizing: border-box;
			text-align: center;
			}

		.cal1 {
			position: relative;
			display: inline-block;
			float: left;
			height: 49px;
			width: 32px;
			border: 1px solid #<?php echo $dCor_aba_border; ?>;
			border-bottom: none;
			margin-top: 11px;
			background-color: #<?php echo $dCor_aba_bg; ?>;
			box-sizing: border-box;
			background-image: url('img/cal24.png');
			background-position: center center;
			background-repeat: no-repeat;
			}

		.lic_tit {
			float: left;
			font-size: 20px;
			font-weight: bold;
			line-height: 24px;
			color: #<?php echo $dCor_tit; ?>;
			}

		a:link.bt-style-1, a:visited.bt-style-1 {
			display: inline-block;
			height: 30px;
			background-color: #<?php echo $dCor_botao_1; ?>;
			color: #<?php echo $dCor_botao_1_tx; ?>;
			font-size: 16px;
			text-align: center;
			line-height: 30px;
			text-decoration: none;
			padding: 0 20px;
		    text-transform: uppercase;
			box-sizing: border-box;
			<?php echo $dCor_botao_1_border; ?>
			}
		a:hover.bt-style-1 {
			text-decoration: none;
			opacity: 0.8;
			}

		a:link.bt-style-2, a:visited.bt-style-2 {
			display: inline-block;
			height: 30px;
			background-color: #<?php echo $dCor_botao_2; ?>;
			color: #<?php echo $dCor_botao_2_tx; ?>;
			font-size: 16px;
			font-style: normal;
			text-align: center;
			line-height: 30px;
			text-decoration: none;
			padding: 0 20px;
		    text-transform: uppercase;
			box-sizing: border-box;
			<?php echo $dCor_botao_2_border; ?>
			}
		a:hover.bt-style-2 {
			text-decoration: none;
			opacity: 0.8;
			}

		a:link.da, a:visited.da {
			display: inline-block;
			width: 55px;
			height: 35px;
			font-size: 12px;
			background-color: #<?php echo $dCor_botao_1; ?>;
			text-decoration: none;
			padding: 4px 0;
			color: #<?php echo $dCor_botao_1_tx; ?>;
			box-sizing: border-box;
			<?php echo $dCor_botao_1_border; ?>
			}
		a:hover.da {
			text-decoration: none;
			opacity: 0.8;
			}

		.topbts {
			margin: 16px 0;
			overflow: hidden;
			}

		a:link.menu-btn, a:visited.menu-btn {
			position: relative;
			display: inline-block;
			border: 1px solid #bebebe;
			color: #bebebe;
			font-size: 13px;
			line-height: 25px;
			padding: 0 10px;
			text-transform: uppercase;
			text-decoration: none;
			white-space: nowrap;
			box-sizing: border-box;
			}
		a:hover.menu-btn {
			text-decoration: none;
			border: 1px solid #<?php echo $dCor_menu_border; ?>;
			color: #<?php echo $dCor_menu; ?>;
			background-color: #<?php echo $dCor_menu_bg; ?>;
			}

	</style>
<body>
<!--[if lte IE 8]>
   <script>
      document.createElement('header');
      document.createElement('figure');
      document.createElement('hgroup');
      document.createElement('nav');
      document.createElement('section');
      document.createElement('article');
      document.createElement('aside');
      document.createElement('footer');
   </script>
<![endif]-->
<div id="fb-root"></div>
<script>
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
  window.___gcfg = {lang: 'pt-BR'};
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/platform.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
