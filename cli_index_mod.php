<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$gPp = 0;
	if ($sInside_tipo == 2 && isset($_GET["pp"]))
		$gPp = 1;

	$db = new Mysql();

	if ($sInside_tipo == 2 && $gPp == 1)
	{
		$db->query("SELECT id FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'popup'");
		if ($db->nextRecord())
			$gPp = 0;
	}

	$oAbas = '';
	$data_hoje = date("Y-m-d");

	$db->query("SELECT id, nome, tipo FROM gelic_abas WHERE tipo IN ('F','N','C') ORDER BY ordem");
	while ($db->nextRecord())
	{
		if ($db->f("tipo") == "F" || $db->f("tipo") == "N")
			$oAbas .= '<a id="tab-'.$db->f("id").'" class="aba0 tb" data-tipo="'.$db->f("tipo").'" href="javascript:void(0);" onclick="goTab('.$db->f("id").');">'.utf8_encode($db->f("nome")).'</a>';
		else
			$oAbas .= '<a id="tab-'.$db->f("id").'" class="cal0 cl" data-tipo="'.$db->f("tipo").'" href="javascript:void(0);" onclick="goTab('.$db->f("id").');" title="Calendário"></a>';
	}


	$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'tab'");
	$db->nextRecord();
	$dTab = intval($db->f("valor"));

	$dIs_search = 0;
	$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'is_search'");
	if ($db->nextRecord())
		$dIs_search = intval($db->f("valor"));

	// Restaurar busca 1
	$dSearch = "";
	$dSearch_type = 1;
	$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'search'");
	if ($db->nextRecord())
	{
		$a = json_decode($db->f("valor"), true);
		$dSearch = $a["search_1"];
		$dSearch_type = intval($a["search"]);
	}

	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(1,0); ?>

			<div class="lic" style="position: relative;">
				<input id="i-tab" type="hidden" value="<?php echo $dTab; ?>">
				<input id="i-search-type" type="hidden" value="<?php echo $dSearch_type; ?>">
				<input id="i-is-search" type="hidden" value="<?php echo $dIs_search; ?>">
				<input type="hidden" name="f-conteudo" value="1">
				<input id="i-pp" type="hidden" value="<?php echo $gPp; ?>">

				<div style="position:relative;height:60px;">
					<div style="position: absolute; left: 0; bottom: 0; width: 100%; height: 1px; background-color: #bebebe;"><!-- --></div>
					<div><?php echo $oAbas; ?></div>
				</div>

				<div class="lic_search" style="width:100%;background-color:#f5f5f5;padding-top:4px;">
					
					<?php
						$aOrdenacao = array();
						$aOrdenacao[1] = "Cadastro no Sistema";
						$aOrdenacao[2] = "Data da Licitação";
						$aOrdenacao[3] = "Data Última Mensagem";
						$aOrdenacao[4] = "Valor Estimado";
						$aOrdenacao[5] = "Quantidade de Veículos (com APL)";

						$aOrdem = array();
						$aOrdem[1] = "Crescente";
						$aOrdem[2] = "Decrescente";

						$dOrdenacao = 2;
						$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'ordenacao'");
						if ($db->nextRecord())
							$dOrdenacao = $db->f("valor");

						$ordenacao = '';
						foreach ($aOrdenacao as $key => $value)
						{
							if ($key == $dOrdenacao)
						    	$ordenacao .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
							else
								$ordenacao .= '<option value="'.$key.'">'.$value.'</option>';
						}


						$dOrdem = 2;
						$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'ordem'");
						if ($db->nextRecord())
							$dOrdem = $db->f("valor");

						$ordem = '';
						foreach ($aOrdem as $key => $value)
						{
							if ($key == $dOrdem)
						    	$ordem .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
							else
								$ordem .= '<option value="'.$key.'">'.$value.'</option>';
						}

						echo '<div style="float:left;overflow:hidden;margin-left:6px;">
							<span style="float:left;font-weight:bold;font-size:12px;line-height:17px;">Ordenação:</span>
							<select id="i-ordenacao" style="clear:both;float:left;height:30px;width:160px;" onchange="listarLicitacoes(false);">
								'.$ordenacao.'
							</select>
							<select id="i-ordem" style="float:left;margin-left:2px;height:30px;" onchange="listarLicitacoes(false);">
								'.$ordem.'
							</select>
						</div>';


						if ($sInside_tipo == 1) //BO
						{
							$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'reg'");
							if ($db->nextRecord())
								$dReg = '['.$db->f("valor").']';
							else
								$dReg = '[]';


							$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'status'");
							if ($db->nextRecord())
								$dStatus = '['.$db->f("valor").']';
							else
								$dStatus = '[]';

							echo '<input id="i-regioes" type="hidden" value="'.$dReg.'"><input id="i-status" type="hidden" value="'.$dStatus.'">
							<div style="float:left;overflow:hidden;margin-left:10px;">
								<span style="float:left;font-weight:bold;font-size:12px;line-height:17px;">Região:</span>
								<a id="drop-2" class="combo0 clear fl dbx" href="javascript:void(0);" onclick="dropItens(2);"><span>Todas</span><img src="img/a-down-g.png"></a>
							</div>
							<div class="apl-sup" style="display:none;float:left;overflow:hidden;margin-left:10px;">
								<span style="float:left;font-weight:bold;font-size:12px;line-height:17px;">Status:</span>
								<a id="drop-1" class="combo0 clear fl dbx" href="javascript:void(0);" onclick="dropItens(1);" style="width:180px;"><span>Todos</span><img src="img/a-down-g.png"></a>
							</div>';
						}
						else
						{
							$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'reg'");
							if ($db->nextRecord())
								$dReg = $db->f("valor");
							else
								$dReg = '';

							echo '<input id="i-estados" type="hidden" value="'.$dReg.'">
							<div class="varal-dn" style="display:none;float:left;overflow:hidden;margin-left:10px;">
								<span style="float:left;font-weight:bold;font-size:12px;line-height:17px;">Região:</span>
								<a id="drop-3" class="combo0 clear fl dbx" href="javascript:void(0);" onclick="dropItens(3);"><span>Todos</span><img src="img/a-down-g.png"></a>
							</div>';
						}
					?>
					<div style="float:left;overflow:hidden;margin-left:20px;">
						<a class="rb1 cl-conteudo fl" href="javascript:void(0);" onclick="ckSingle(this,'conteudo',1);" style="position:relative;margin-top:24px;">Lista</a>
						<a class="rb0 cl-conteudo fl" href="javascript:void(0);" onclick="ckSingle(this,'conteudo',2);" style="position:relative;margin-left:20px;margin-top:24px;">Mapa</a>
					</div>
					<a class="adv-search-btn drp" href="javascript:void(0);" onclick="advSearch(this);" title="Busca Avançada" style="margin-top:11px;"></a>
					<input id="i_search" type="text" class="lic_search_ip" placeholder="- ID LIC -" maxlength="8" value="<?php echo $dSearch; ?>" style="float:right;margin-top:11px;">
				</div>
			</div>
			<?php

				echo '<div class="no-varal-dn" style="display:none;height: 10px; border-bottom: 1px solid #bebebe;background-color: #f5f5f5;"><!-- --></div>';

				if ($sInside_tipo <> 1)
				{
					$dCm = 1;
					$dCe = 1;
					$dCf = 1;

					$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'instancia'");
					if ($db->nextRecord())
					{
						if (strpos($db->f("valor"), "1") === false) $dCm = 0;
						if (strpos($db->f("valor"), "2") === false) $dCe = 0;
						if (strpos($db->f("valor"), "3") === false) $dCf = 0;
					}

					echo '
					<div class="varal-dn" style="display:none; border-bottom: 1px solid #bebebe; padding: 10px 0; background-color: #f5f5f5; overflow: hidden;">
						<span style="float:left;line-height:17px;margin-left:6px;width:80px;">Instância:</span>
						<a id="c-municipal" class="cb'.$dCm.'" href="javascript:void(0);" onclick="ckSelfishONLY(this);">Municipal</a>
						<a id="c-estadual" class="cb'.$dCe.' ml-20" href="javascript:void(0);" onclick="ckSelfishONLY(this);">Estadual</a>
						<a id="c-federal" class="cb'.$dCf.' ml-20" href="javascript:void(0);" onclick="ckSelfishONLY(this);">Federal</a>
					</div>';
				}
			?>
			<div id="conteudo" style="position: relative; overflow: hidden;"></div>
			<div style="height: 60px;"></div>
			<div style="font-size: 12px; line-height: 21px;">D.E.P. (Data de entrega das propostas)</div>
		</div>
		<div class="bottom">
			<div class="middle">
				<div class="bottom_top">
					<ul>
						<li style="padding-left: 0;"><img src="img/ico_email.png" style="display:inline-block;vertical-align:middle;width:18px;height:18px;margin:0 5px 0 0;">Mensagens</li>
						<li><span class="black"></span>Passou da data de abertura</li>
						<li><span class="dark-red"></span>Prazo limite (passou)</li>
						<li><span class="bright-red"></span>Prazo limite (0 - 2 hr)</li>
						<li><span class="green"></span>Prazo limite (> 2 hr)</li>
						<li><span class="gray"></span>Sem interesse</li>
					</ul>
				</div>
			</div>
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
				<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;float:none;">Acesso Restrito!</h4>
				<p style="color: #a6a6a6;">Se você é cliente GELIC utilize o seu login e senha para ter acesso nesta área.</p>
			</div>
		</div>
	</section>
	<?php
}

?>
