<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(4,3); ?>

			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo">Meus Dados</h4>
			</div>

	<?php

		$sInside_id = $_SESSION[SESSION_ID];
		$sInside_tipo = $_SESSION[SESSION_TYPE];

		$db = new Mysql();		
		$db->query("
SELECT
	cli.tipo,
	cli.nome,
	cli.departamento,
	cli.email,
	cli.celular,
	cli.comercial,
	cli.notificacoes,
	cli.nt_celular,
	cli.nt_email,
	cli.login,
	pfl.nome AS perfil
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_clientes_perfis AS pfl ON pfl.id = cli.id_perfil
WHERE
	cli.id = $sInside_id");
		$db->nextRecord();

		$dTipo = $db->f("tipo");
		$dNome = utf8_encode($db->f("nome"));
		$dDepartamento = utf8_encode($db->f("departamento"));
		$dEmail = $db->f("email");
		$dNotificacoes = $db->f("notificacoes");
		$dComercial = $db->f("comercial");
		$dCelular = $db->f("celular");
		$dLogin = $db->f("login");
		$dPerfil = utf8_encode($db->f("perfil"));

		$nt_eml = json_decode($db->f("nt_email"), true);
		$nt_sms = json_decode($db->f("nt_celular"), true);

		if (strlen($nt_sms["ntf"]) > 0)
			$dNt_celular = str_split($nt_sms["ntf"]);
		else
			$dNt_celular = array();

		if (strlen($nt_eml["ntf"]) > 0)
			$dNt_email = str_split($nt_eml["ntf"]);
		else
			$dNt_email = array();

		$departamento = '';
		if (strlen($dDepartamento) > 0)
			$departamento = '<div class="rw">
				<span class="fl lh-38 w-200">Departamento</span>
				<span class="fl lh-38 gray-88">'.$dDepartamento.'</span>
			</div>';


		if ($sInside_tipo == 1) //BO
		{
			$nome = '<div class="rw">
				<span class="fl lh-38 w-200">Nome <a class="t-red bold inline">*</a></span>
				<input id="i-nome" class="iText fl" type="text" name="f-nome" placeholder="- nome -" maxlength="255" value="'.$dNome.'" style="width:500px;">
			</div>';

			$login = '<div class="rw">
				<span class="fl lh-38 w-200">Login <a class="t-red bold inline">*</a></span>
				<input id="i-login" class="iText fl" type="text" name="f-login" placeholder="- login -" maxlength="50" value="'.$dLogin.'">
			</div>';
		}
		else if ($sInside_tipo == 2) //DN
		{
			$nome = '<div class="rw">
				<span class="fl lh-38 w-200">DN/Nome</span>
				<span class="fl lh-38 gray-88">'.$dNome.'</span>
			</div>';

			$login = '<div class="rw">
				<span class="fl lh-38 w-200">Login</span>
				<span class="fl lh-38 gray-88">'.$dLogin.'</span>
			</div>';
		}
		else if ($sInside_tipo == 3) //DN FILHO
		{
			$nome = '<div class="rw">
				<span class="fl lh-38 w-200">Nome <a class="t-red bold inline">*</a></span>
				<input id="i-nome" class="iText fl" type="text" name="f-nome" placeholder="- nome -" maxlength="255" value="'.$dNome.'" style="width:500px;">
			</div>';

			$login = '';
		}
		else if ($sInside_tipo == 4) //REP
		{
			$sInside_id_acesso = $_SESSION[SESSION_ID_DN];

			$db->query("
SELECT
	pfl.nome
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_clientes_acesso AS clia ON clia.id_cliente = cli.id
	INNER JOIN gelic_clientes_perfis AS pfl ON pfl.id = clia.id_perfil
WHERE
	cli.id = $sInside_id AND
	clia.id_cliente_acesso = $sInside_id_acesso");
			$db->nextRecord();
			$dPerfil = utf8_encode($db->f("nome"));

			$nome = '<div class="rw">
				<span class="fl lh-38 w-200">Nome <a class="t-red bold inline">*</a></span>
				<input id="i-nome" class="iText fl" type="text" name="f-nome" placeholder="- nome -" maxlength="255" value="'.$dNome.'" style="width:500px;">
			</div>';

			$login = '';
		}


		function montarRegioes($vLetter, $vStr)
		{
			$aReturn = array();
			if (strpos($vStr, $vLetter."12") !== false) $aReturn[] = "1/2";
			if (strpos($vStr, $vLetter."3") !== false) $aReturn[] = "3";
			if (strpos($vStr, $vLetter."4") !== false) $aReturn[] = "4";
			if (strpos($vStr, $vLetter."5") !== false) $aReturn[] = "5";
			if (strpos($vStr, $vLetter."6") !== false) $aReturn[] = "6";
			if (count($aReturn) > 0)
				return implode(",",$aReturn);
			else
				return "- - -";
		}


		echo '
			<div class="lic">
				<form id="meus-dados-form">
					<input type="hidden" name="f-notificacoes" value="'.$dNotificacoes.'">
					<input type="hidden" name="f-eml" value="'.$nt_eml["ntf"].'">
					<input type="hidden" name="f-sms" value="'.$nt_sms["ntf"].'">
					<input type="hidden" name="f-eml-reg" value="'.$nt_eml["reg"].'">
					<input type="hidden" name="f-sms-reg" value="'.$nt_sms["reg"].'">


					'.$nome.$departamento.'
					<div class="rw">
						<span class="fl lh-38 w-200">Perfil</span>
						<span class="fl lh-38 gray-88">'.$dPerfil.'</span>
					</div>
					<div class="rw">
						<span class="fl lh-38 w-200">Email <a class="t-red bold inline">*</a></span>
						<input id="i-email" class="iText fl" type="text" name="f-email" placeholder="- email -" maxlength="100" value="'.$dEmail.'" style="width:500px;">
					</div>
					'.$login.'
					<div class="rw">
						<span class="fl lh-38 w-200">Senha</span>
						<input id="i-nova-senha" class="iText fl" type="password" name="f-nova-senha" placeholder="(manter a mesma)" maxlength="15">
					</div>
					<div class="rw">
						<span class="fl lh-38 w-200">Telefone Fixo</span>
						<input id="i-comercial" class="iText fl" type="text" name="f-comercial" placeholder="- telefone fixo -" maxlength="20" value="'.$dComercial.'">
					</div>
					<div class="rw">
						<span class="fl lh-38 w-200">Telefone Celular</span>
						<input id="i-celular" class="iText fl" type="text" name="f-celular" placeholder="- telefone celular -" maxlength="20" value="'.$dCelular.'">
					</div>

					<div class="rw" style="margin-top: 10px;">
						<a class="cb'.$dNotificacoes.' fl" href="javascript:void(0);" onclick="ckSelfish(this,\'notificacoes\');" style="position: relative; margin: 6px 0 0 200px;">Notificações</a>
					</div>';

		if ($sInside_tipo == 1)
			echo '
					<div class="rw" style="margin-top: 20px;">
						<span class="fl bold" style="margin-left:200px;">LICITAÇÕES</span>
					</div>
					<div class="row-100" style="margin-top: 6px;">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Mensagens da Administração</span>
							<a class="cb'.(int)in_array('A', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'A\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regAe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("A", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('A', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'A\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regAs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("A", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Mensagens do DN</span>
							<a class="cb'.(int)in_array('B', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'B\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regBe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("B", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('B', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'B\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regBs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("B", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Licitação Encerrada</span>
							<a class="cb'.(int)in_array('C', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'C\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regCe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("C", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('C', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'C\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regCs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("C", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Encerramento Revertido</span>
							<a class="cb'.(int)in_array('D', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'D\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regDe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("D", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('D', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'D\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regDs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("D", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Aviso de APL Enviada</span>
							<a class="cb'.(int)in_array('E', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'E\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regEe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("E", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('E', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'E\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regEs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("E", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Término do Prazo Limite</span>
							<a class="cb'.(int)in_array('F', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'F\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regFe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("F", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('F', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'F\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regFs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("F", $nt_sms["reg"]).'</span></a>
						</div>
					</div>

					<div class="rw" style="margin-top: 20px;">
						<span class="fl bold" style="margin-left:200px;">COMPRA DIRETA/SRP</span>
					</div>
					<div class="row-100" style="margin-top: 6px;">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Novas Solicitações</span>
							<a class="cb'.(int)in_array('G', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'G\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regGe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("G", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('G', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'G\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regGs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("G", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Mensagens da Administração</span>
							<a class="cb'.(int)in_array('H', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'H\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regHe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("H", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('H', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'H\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regHs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("H", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Mensagens do DN</span>
							<a class="cb'.(int)in_array('I', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'I\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regIe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("I", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('I', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'I\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regIs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("I", $nt_sms["reg"]).'</span></a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 240px;">Aviso de APL Enviada</span>
							<a class="cb'.(int)in_array('J', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'J\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a id="regJe" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("J", $nt_eml["reg"]).'</span></a>
							<a class="cb'.(int)in_array('J', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'J\');" style="position: relative; margin: 5px 0 0 36px;">SMS</a>
							<a id="regJs" class="reg-btn" href="javascript:void(0);" onclick="regioes(this);"><span style="float:left;">Regiões:</span><span class="sel-reg" style="float:right;font-size:12px;">'.montarRegioes("J", $nt_sms["reg"]).'</span></a>
						</div>
					</div>';
		else 
			echo '
					<div class="rw" style="margin-top: 20px;">
						<span class="fl bold" style="margin-left:200px;">LICITAÇÕES</span>
					</div>
					<div class="row-100" style="margin-top: 6px;">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Novas Licitações</span>
							<a class="cb'.(int)in_array('A', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'A\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('A', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'A\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Mensagens da Administração</span>
							<a class="cb'.(int)in_array('B', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'B\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('B', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'B\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Mensagens do Back Office</span>
							<a class="cb'.(int)in_array('C', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'C\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('C', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'C\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Licitação Prorrogada</span>
							<a class="cb'.(int)in_array('D', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'D\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('D', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'D\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Licitação Encerrada</span>
							<a class="cb'.(int)in_array('E', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'E\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('E', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'E\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Encerramento Revertido</span>
							<a class="cb'.(int)in_array('F', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'F\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('F', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'F\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Revertido o Desinteresse na Licitação</span>
							<a class="cb'.(int)in_array('G', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'G\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('G', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'G\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de Solicitação de ATA</span>
							<a class="cb'.(int)in_array('H', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'H\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('H', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'H\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovada</span>
							<a class="cb'.(int)in_array('I', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'I\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('I', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'I\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovada</span>
							<a class="cb'.(int)in_array('J', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'J\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('J', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'J\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovação Revertida</span>
							<a class="cb'.(int)in_array('K', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'K\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('K', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'K\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovação Revertida</span>
							<a class="cb'.(int)in_array('L', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'L\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('L', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'L\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>


					<div class="rw" style="margin-top: 20px;">
						<span class="fl bold" style="margin-left:200px;">COMPRA DIRETA/SRP</span>
					</div>
					<div class="row-100" style="margin-top: 6px;">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Mensagens da Administração</span>
							<a class="cb'.(int)in_array('M', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'M\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('M', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'M\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Mensagens do Back Office</span>
							<a class="cb'.(int)in_array('S', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'S\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('S', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'S\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de Autorização (Envio da APL)</span>
							<a class="cb'.(int)in_array('N', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'N\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('N', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'N\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovada</span>
							<a class="cb'.(int)in_array('O', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'O\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('O', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'O\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovada</span>
							<a class="cb'.(int)in_array('P', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'P\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('P', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'P\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovação Revertida</span>
							<a class="cb'.(int)in_array('Q', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'Q\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('Q', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'Q\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>
					<div class="row-100">
						<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
							<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovação Revertida</span>
							<a class="cb'.(int)in_array('R', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'R\');" style="position: relative; margin: 5px 0 0 20px;">Email</a>
							<a class="cb'.(int)in_array('R', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'R\');" style="position: relative; margin: 5px 0 0 60px;">SMS</a>
						</div>
					</div>';

			echo '	<div class="rw" style="margin-top: 30px;">
						<span class="fl lh-38 w-200 t-red">Senha Atual <a class="t-red bold inline">*</a></span>
						<input id="i-senha-atual" class="iText fl" type="password" name="f-senha-atual" placeholder="- senha atual -" maxlength="15" style="border-color: #cc0000;">
					</div>
				</form>

				<div class="rw" style="margin-top: 20px;">
					<a class="bt-style-2 fl" href="javascript:void(0);" onclick="salvarMeusDados();" style="margin-left: 200px;">Salvar Alterações</a>
				</div>
			</div>';
	?>

			<div style="height: 100px;"><!-- gap --></div>
		</div>
	</section>


	<?php
}
else
{
	?>
	<section>
		<div class="middle">
			<div class="lic" style="height: 340px;">
				<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
				<p style="color: #a6a6a6;">Se você é cliente GELIC utilize o seu login e senha para ter acesso nesta área.</p>
			</div>
		</div>
	</section>
	<?php
}

?>
