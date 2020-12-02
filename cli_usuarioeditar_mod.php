<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$db = new Mysql();

	if ($sInside_tipo == 1 || $sInside_tipo == 3 || $sInside_tipo == 4) //BO, DN FILHO, REP
	{
		echo '<section>
			<div class="middle">
				<div class="lic" style="height: 340px;">
					<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
				</div>
			</div>
		</section>';
		exit;
	}


	$gId = 0;
	if (isset($_GET["id"]))
	{
		$gId = intval($_GET["id"]);
		$db->query("
SELECT
	id,
	tipo,
	id_perfil,
	nome,
	departamento,
	email,
	senha,
	celular,
	comercial,
	notificacoes,
	nt_celular,
	nt_email
FROM
	gelic_clientes
WHERE
	id = $gId AND
	deletado = 0 AND
	((id_parent = $sInside_id AND tipo = 3) OR id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = $sInside_id))");

		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dTipo = $db->f("tipo");
			$dId_perfil = $db->f("id_perfil");
			$dNome = utf8_encode($db->f("nome"));
			$dDepartamento = utf8_encode($db->f("departamento"));
			$dEmail = $db->f("email");
			$dNotificacoes = $db->f("notificacoes");
			$dComercial = $db->f("comercial");
			$dCelular = $db->f("celular");

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

			if ($dTipo == 4)
			{
				//buscar perfil de acesso referente ao DN logado
				$db->query("SELECT id_perfil FROM gelic_clientes_acesso WHERE id_cliente_acesso = $sInside_id AND id_cliente = $dId");
				$db->nextRecord();
				$dId_perfil = $db->f("id_perfil");
			}

			$vTitle = 'Editar... (<a class="gray-88 t14" style="display:inline;">'.$dNome.'</a>)';
			$vSave = "Salvar Alterações";
		}
		else
		{
			$gId = 0;
		}
	}


	if ($gId == 0)
	{
		//verificar limitacao quantidade de usuarios
		$db->query("SELECT COUNT(*) AS total FROM gelic_clientes WHERE id_parent = $sInside_id AND deletado = 0");
		$db->nextRecord();
		if ($db->f("total") >= MAX_USR)
		{
			echo '<section>
				<div class="middle">
					<div class="lic" style="height: 340px;">
						<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
					</div>
				</div>
			</section>';
			exit;
		}

		$dId = 0;
		$dTipo = 3;
		$dId_perfil = 2;
		$dNome = "";
		$dDepartamento = "";
		$dEmail = "";
		$dComercial = "";
		$dCelular = "";
		$dNotificacoes = 1;
		$nt_eml = array("ntf"=>"");
		$nt_sms = array("ntf"=>"");
		$dNt_celular = array();
		$dNt_email = array();

		$vTitle = "Novo Usuário";
		$vSave = "Salvar Novo Usuário";
	}


	$dPerfis = '';
	$db->query("SELECT id, nome FROM gelic_clientes_perfis WHERE id < 4 ORDER BY nome");
	while ($db->nextRecord())
	{
		if ($dId_perfil == $db->f("id"))
			$dPerfis .= '<option value="'.$db->f("id").'" selected="selected">'.utf8_encode($db->f("nome")).'</option>';
		else
			$dPerfis .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
	}

	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(4,2); ?>

			<div class="lic">
				<?php 
					if ($sInside_tipo <> 4)
						echo '<a class="bt-style-2 fr" href="index.php?p=cli_usuarios">x</a>';
				?>
				<h4 class="lic_tit" id="lic_titulo"><?php echo $vTitle; ?></h4>
			</div>


			<div class="lic">
				<form id="usuario-form">
					<?php
						echo '
							<input type="hidden" name="f-id" value="'.$dId.'">
							<input type="hidden" name="f-notificacoes" value="'.$dNotificacoes.'">
							<input type="hidden" name="f-eml" value="'.$nt_eml["ntf"].'">
							<input type="hidden" name="f-sms" value="'.$nt_sms["ntf"].'">';


						if ($dTipo == 3)
						{
							echo '
							<div class="rw">
								<span class="fl lh-38 w-200">Nome do Usuário</span>
								<input id="i-nome" class="iText fl" type="text" name="f-nome" placeholder="- nome do usuário -" maxlength="255" value="'.$dNome.'" style="width:500px;">
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Departamento</span>
								<input id="i-departamento" class="iText fl" type="text" name="f-departamento" placeholder="- departamento -" maxlength="100" value="'.$dDepartamento.'" style="width:500px;">
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Perfil de Acesso</span>
								<select id="i-perfil" class="iText fl" name="f-perfil" style="width:500px; background-color: #ffffff;">
									'.$dPerfis.'
								</select>
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Email (login)</span>
								<input id="i-email" class="iText fl" type="text" name="f-email" placeholder="- email -" maxlength="100" value="'.$dEmail.'" style="width:500px;">
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Telefone Fixo</span>
								<input id="i-comercial" class="iText fl" type="text" name="f-comercial" placeholder="- telefone fixo -" maxlength="20" value="'.$dComercial.'">
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Telefone Celular</span>
								<input id="i-celular" class="iText fl" type="text" name="f-celular" placeholder="- telefone celular -" maxlength="20" value="'.$dCelular.'">
							</div>

							</form>

							<div class="rw" style="margin-top: 10px;">
								<a class="cb'.$dNotificacoes.' fl" href="javascript:void(0);" onclick="ckSelfish(this,\'notificacoes\');" style="position: relative; margin: 6px 0 0 200px;">Notificações</a>
							</div>

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
							</div>

							<div class="rw" style="margin-top: 30px;">
								<a class="bt-style-2 fl" href="javascript:void(0);" onclick="salvarUsuario(false);" style="margin-left: 200px;">'.$vSave.'</a>
							</div>';
						}
						else
						{
							echo '
							<div class="rw">
								<span class="fl lh-38 w-200">Nome do Usuário</span>
								<input id="i-nome" class="iText fl ro" type="text" name="f-nome" placeholder="- nome do usuário -" maxlength="255" value="'.$dNome.'" style="width:500px;" readonly>
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Departamento</span>
								<input id="i-departamento" class="iText fl ro" type="text" name="f-departamento" placeholder="- departamento -" maxlength="100" value="'.$dDepartamento.'" style="width:500px;" readonly>
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Perfil de Acesso</span>
								<select id="i-perfil" class="iText fl" name="f-perfil" style="width:500px; background-color: #ffffff;">
									'.$dPerfis.'
								</select>
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Email (login)</span>
								<input id="i-email" class="iText fl ro" type="text" name="f-email" placeholder="- email -" maxlength="100" value="'.$dEmail.'" style="width:500px;" readonly>
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Telefone Fixo</span>
								<input id="i-comercial" class="iText fl ro" type="text" name="f-comercial" placeholder="- telefone fixo -" maxlength="20" value="'.$dComercial.'" readonly>
							</div>
							<div class="rw">
								<span class="fl lh-38 w-200">Telefone Celular</span>
								<input id="i-celular" class="iText fl ro" type="text" name="f-celular" placeholder="- telefone celular -" maxlength="20" value="'.$dCelular.'" readonly>
							</div>

							</form>

							<div class="rw" style="margin-top: 10px;">
								<a class="cb'.$dNotificacoes.' fl" href="javascript:void(0);" onclick="ckSelfish(this,\'notificacoes\');" style="position: relative; margin: 6px 0 0 200px; opacity: 0.4;" data-mode="readonly">Notificações</a>
							</div>

							<div class="rw" style="margin-top: 20px;">
								<span class="fl bold" style="margin-left:200px;">LICITAÇÕES</span>
							</div>
							<div class="row-100" style="margin-top: 6px;">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Novas Licitações</span>
									<a class="cb'.(int)in_array('A', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'A\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('A', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'A\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Mensagens da Administração</span>
									<a class="cb'.(int)in_array('B', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'B\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('B', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'B\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Mensagens do Back Office</span>
									<a class="cb'.(int)in_array('C', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'C\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('C', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'C\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Licitação Prorrogada</span>
									<a class="cb'.(int)in_array('D', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'D\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('D', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'D\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Licitação Encerrada</span>
									<a class="cb'.(int)in_array('E', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'E\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('E', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'E\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Encerramento Revertido</span>
									<a class="cb'.(int)in_array('F', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'F\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('F', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'F\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Revertido o Desinteresse na Licitação</span>
									<a class="cb'.(int)in_array('G', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'G\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('G', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'G\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de Solicitação de ATA</span>
									<a class="cb'.(int)in_array('H', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'H\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('H', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'H\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovada</span>
									<a class="cb'.(int)in_array('I', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'I\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('I', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'I\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovada</span>
									<a class="cb'.(int)in_array('J', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'J\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('J', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'J\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovação Revertida</span>
									<a class="cb'.(int)in_array('K', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'K\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('K', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'K\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovação Revertida</span>
									<a class="cb'.(int)in_array('L', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'L\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('L', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'L\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>


							<div class="rw" style="margin-top: 20px;">
								<span class="fl bold" style="margin-left:200px;">COMPRA DIRETA/SRP</span>
							</div>
							<div class="row-100" style="margin-top: 6px;">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Mensagens da Administração</span>
									<a class="cb'.(int)in_array('M', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'M\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('M', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'M\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Mensagens do Back Office</span>
									<a class="cb'.(int)in_array('S', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'S\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('S', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'S\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de Autorização (Envio da APL)</span>
									<a class="cb'.(int)in_array('N', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'N\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('N', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'N\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovada</span>
									<a class="cb'.(int)in_array('O', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'O\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('O', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'O\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovada</span>
									<a class="cb'.(int)in_array('P', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'P\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('P', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'P\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Aprovação Revertida</span>
									<a class="cb'.(int)in_array('Q', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'Q\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('Q', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'Q\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>
							<div class="row-100">
								<div style="display:inline-block; float: left; border-bottom: 1px dotted #d0d0d0; margin-left:240px;">
									<span class="fl lh-26" style="width: 280px;">Aviso de APL Reprovação Revertida</span>
									<a class="cb'.(int)in_array('R', $dNt_email).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'eml\',\'R\');" style="position: relative; margin: 5px 0 0 20px; opacity: 0.4;" data-mode="readonly">Email</a>
									<a class="cb'.(int)in_array('R', $dNt_celular).' fl" href="javascript:void(0);" onclick="ckSelf(this,\'sms\',\'R\');" style="position: relative; margin: 5px 0 0 60px; opacity: 0.4;" data-mode="readonly">SMS</a>
								</div>
							</div>

							<div class="rw" style="margin-top: 30px;">
								<a class="bt-style-2 fl" href="javascript:void(0);" onclick="salvarUsuario(false);" style="margin-left: 200px;">'.$vSave.'</a>
							</div>';
						}

				?>

			</div>
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
