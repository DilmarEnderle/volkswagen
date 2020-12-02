<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$db = new Mysql();

	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	if ($sInside_tipo == 2 || $sInside_tipo == 1)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];

	$dCor_base = '548ec7';
	$dCor_menu = 'ff6600';
	$dCor_aba = '548ec7';
	$dCor_botao_1 = 'ff6600';
	$dCor_botao_2 = 'bebebe';

	$db->query("SELECT cor_base, cor_menu, cor_aba, cor_botao_1, cor_botao_2 FROM gelic_clientes_personalizacao WHERE id_cliente = $cliente_parent");
	if ($db->nextRecord())
	{
		$dCor_base = $db->f("cor_base");
		$dCor_menu = $db->f("cor_menu");
		$dCor_aba = $db->f("cor_aba");
		$dCor_botao_1 = $db->f("cor_botao_1");
		$dCor_botao_2 = $db->f("cor_botao_2");
	}

	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(4,7); ?>

			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo">Personalização</h4>
			</div>


			<div class="lic">
				<div class="rw">
					<span class="fl lh-38 w-200">Cor Base</span>
					<input id="i-cor-base" class="iText fl jscolor {width:243, height:150, mode:'HVS',position:'right',uppercase:false}" type="text" style="width: 140px;" value="<?php echo $dCor_base; ?>" autocomplete="off">
				</div>
				<div class="rw">
					<span class="fl lh-38 w-200">Cor do Menu</span>
					<input id="i-cor-menu" class="iText fl jscolor {width:243, height:150, mode:'HVS',position:'right',uppercase:false}" type="text" style="width: 140px;" value="<?php echo $dCor_menu; ?>" autocomplete="off">
				</div>
				<div class="rw">
					<span class="fl lh-38 w-200">Cor das Abas</span>
					<input id="i-cor-aba" class="iText fl jscolor {width:243, height:150, mode:'HVS',position:'right',uppercase:false}" type="text" style="width: 140px;" value="<?php echo $dCor_aba; ?>" autocomplete="off">
				</div>
				<div class="rw">
					<span class="fl lh-38 w-200">Cor Botão Primário</span>
					<input id="i-cor-btn1" class="iText fl jscolor {width:243, height:150, mode:'HVS',position:'right',uppercase:false}" type="text" style="width: 140px;" value="<?php echo $dCor_botao_1; ?>" autocomplete="off">
				</div>
				<div class="rw">
					<span class="fl lh-38 w-200">Cor Botão Secundário</span>
					<input id="i-cor-btn2" class="iText fl jscolor {width:243, height:150, mode:'HVS',position:'right',uppercase:false}" type="text" style="width: 140px;" value="<?php echo $dCor_botao_2; ?>" autocomplete="off">
				</div>
				<div class="rw" style="margin-top: 40px;">
					<a class="bt-style-2 fl" href="javascript:void(0);" onclick="salvarCores();" style="margin-left: 200px;">Salvar alterações</a>
					<a class="menu-btn fl" href="javascript:void(0);" onclick="padraoCores();" style="margin-left: 60px;height:30px;line-height:30px;">Voltar à configuração padrão</a>
				</div>
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
