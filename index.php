<?php 

$p = "";
if (isset($_GET["p"]))
	$p = $_GET["p"];

if (in_array($p, array("cli_index","cli_documentos","cli_grafico","cli_feriados","cli_meus_dados","cli_open","cli_usuarios","cli_usuarioeditar","cli_compradir","cli_compradir_editar","cli_compradir_abrir","cli_compradiratarp","cli_compradirbo","cli_custom")))
{
	require_once "include/config.php";
	include "include/header.php";
	require_once "include/essential.php";

	?>

	<header style="height: 41px;">
		<div class="htop">
			<div class="middle" style="height:40px;">
				<span style="display: inline-block; float: left; padding: 0 20px 0 0; margin: 0 10px 0 0;height: 40px;"><img src="img/<?php echo $dLogo; ?>" style="width:130px;height: 36px;display: inline-block;margin-top: 1px;"></span>
				<ul class="soc_top">
					<li class="soc_li_<?php echo $dSocial; ?>"><a href="https://www.facebook.com/npgelic" target="_blank" title="Facebook"><img src="img/fb-<?php echo $dSocial_img; ?>.png"></a></li>
					<li class="soc_li_<?php echo $dSocial; ?>"><a href="https://twitter.com/NpGelic" target="_blank" title="Twitter"><img src="img/tw-<?php echo $dSocial_img; ?>.png"></a></li>
					<li class="soc_pho_li_<?php echo $dSocial; ?>"><img src="img/ph-<?php echo $dSocial_img; ?>.png">0800-942-1700</li>
				</ul>
				<div class="htop_login">
					<a href="../cli_logout.php" class="bt-sair" style="background-image: none; text-align: center; padding: 0;">Sair</a>
				</div>
			</div>
		</div>
	</header>

	<?php include($p.'_mod.php'); ?>

	<div style="height: 1px; background-color: #dddddd; width: 100%;"></div>
	<footer>
		<div class="middle">
			<h1 class="flogo" style="margin: 17px 0 0 0;"></h1>
			<ul class="soc_bottom">
				<li class="soc_li_gray"><a href="https://www.facebook.com/npgelic" target="_blank" title="Facebook"><img src="img/fb-w.png"></a></li>
				<li class="soc_li_gray"><a href="https://twitter.com/NpGelic" target="_blank" title="Twitter"><img src="img/tw-w.png"></a></li>
				<li class="soc_pho_li_gray"><img src="img/ph-w.png">0800-942-1700</li>
			</ul>
		</div>
		<div class="middle">
			<div class="fend"><span class="bold">São Paulo: </span>Avenida Paulista, nº 726, 17º Andar Cj. 1707, Bela Vista – SP, CEP: 01310-910<br><span class="bold">Curitiba: </span>Rua Mal. Floriano Peixoto, nº 306, 22º Andar, Curitiba – PR, Centro, CEP: 80010-130</div>
		</div>
	</footer>
	
	<?php

	include "js/scripts.php";

	if ($p == "cli_index" || $p == "cli_open")
	{
		$db = new Mysql();

		//=============================
		// Restaurar opcoes de busca
		//=============================
		if (isInside())
			$sInside_id = $_SESSION[SESSION_ID];
		else
			$sInside_id = 0;

		$dFiltro_data_de = '';
		$dFiltro_data_ate = '';
		$dFiltro_estado = '';
		$dFiltro_cidade = 0;
		$dFiltro_orgao = '';
		$dFiltro_modalidade = 0;

		$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'search'");
		if ($db->nextRecord())
		{
			$json_string = $db->f("valor");
			$a = json_decode(utf8_encode($json_string), true);

			if ($a["search_2"]["data_de"] <> '' && isValidBrDate($a["search_2"]["data_de"]))
				$dFiltro_data_de = $a["search_2"]["data_de"];

			if ($a["search_2"]["data_ate"] <> '' && isValidBrDate($a["search_2"]["data_ate"]))
				$dFiltro_data_ate = $a["search_2"]["data_ate"];

			$dFiltro_estado = $a["search_2"]["estado"];
			$dFiltro_cidade = intval($a["search_2"]["cidade"]);
			$dFiltro_orgao = $a["search_2"]["orgao"];
			$dFiltro_orgao = preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $dFiltro_orgao);
			$dFiltro_modalidade = intval($a["search_2"]["modalidade"]);
		}
		//=============================


		$aEstados = array();
		$aEstados["AC"] = "Acre";
		$aEstados["AL"] = "Alagoas";
		$aEstados["AP"] = "Amapá";
		$aEstados["AM"] = "Amazonas";
		$aEstados["BA"] = "Bahia";
		$aEstados["CE"] = "Ceará";
		$aEstados["DF"] = "Distrito Federal";
		$aEstados["ES"] = "Espírito Santo";
		$aEstados["GO"] = "Goiás";
		$aEstados["MA"] = "Maranhão";
		$aEstados["MT"] = "Mato Grosso";
		$aEstados["MS"] = "Mato Grosso do Sul";
		$aEstados["MG"] = "Minas Gerais";
		$aEstados["PA"] = "Pará";
		$aEstados["PB"] = "Paraíba";
		$aEstados["PR"] = "Paraná";
		$aEstados["PE"] = "Pernambuco";
		$aEstados["PI"] = "Piauí";
		$aEstados["RJ"] = "Rio de Janeiro";
		$aEstados["RN"] = "Rio Grande do Norte";
		$aEstados["RS"] = "Rio Grande do Sul";
		$aEstados["RO"] = "Rondônia";
		$aEstados["RR"] = "Roraima";
		$aEstados["SC"] = "Santa Catarina";
		$aEstados["SP"] = "São Paulo";
		$aEstados["SE"] = "Sergipe";
		$aEstados["TO"] = "Tocantins";

		$tEstados = '<option value="">- estado -</option>';
		foreach ($aEstados as $key => $value)
		{
			if ($key == $dFiltro_estado)
				$tEstados .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
			else
				$tEstados .= '<option value="'.$key.'">'.$value.'</option>';
		}


		$tModalidades = '';
		$db->query("
SELECT 
	lic.id_modalidade,
    mdl.nome
FROM 
	gelic_licitacoes AS lic
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
WHERE
	mdl.antigo = 0
GROUP BY
	lic.id_modalidade
ORDER BY
	mdl.nome");
		while ($db->nextRecord())
		{
			if ($db->f("id_modalidade") == $dFiltro_modalidade)
				$tModalidades .= '<option value="'.$db->f("id_modalidade").'" selected="selected">'.utf8_encode($db->f("nome")).'</option>';
			else
				$tModalidades .= '<option value="'.$db->f("id_modalidade").'">'.utf8_encode($db->f("nome")).'</option>';
		}


		$tCidades = '<option value="0">- selecione um estado primeiro -</option>';
		if ($dFiltro_estado <> '')
		{
			$db->query("SELECT id, nome FROM gelic_cidades WHERE uf = '$dFiltro_estado' AND id > 0 ORDER BY nome");
			if ($db->nf() > 0)
			{
				$tCidades = '<option value="0">- cidade -</option>';
				while ($db->nextRecord())
				{
					if ($db->f("id") == $dFiltro_cidade)
						$tCidades .= '<option value="'.$db->f("id").'" selected="selected">'.utf8_encode($db->f("nome")).'</option>';
					else
						$tCidades .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
				}
			}
		}

		$dStatus = '';
		if ($sInside_tipo == 1) //BO
		{
			$db->query("SELECT id, descricao FROM gelic_status");
			if ($db->nextRecord()) {
				while ($db->nextRecord()) {
					$dStatus .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("descricao")).'</option>';
				}
			} else {
				$dStatus .= '[]';
			}
		} else {
			$dStatus = '[]';
		}

		echo '<div id="adv-search-box" class="drp">
			<span class="fl bold t16">BUSCA AVANÇADA</span>

			<div class="clear" style="height:24px;"><!-- --></div>

			<span class="clear fl lh-30 w-160">Data de Abertura: <span class="fr lh-30 italic mr-10 drp">de</span></span>
			<input id="i-da-fr" class="iText fl" type="text" placeholder="dd/mm/aaaa" maxlength="10" value="'.$dFiltro_data_de.'" style="width: 120px;height:30px;line-height:28px;">
			<span class="fl lh-30 italic ml-10">até</span>
			<input id="i-da-to" class="iText fl ml-10" type="text" placeholder="dd/mm/aaaa" maxlength="10" value="'.$dFiltro_data_ate.'" style="width: 120px;height:30px;line-height:28px;">
			
			<div class="clear" style="height:8px;"><!-- --></div>

			<span class="clear fl lh-30 w-160">Estado</span>
			<select id="i-estado" class="fl" style="height:30px;padding:0 10px;width:400px;" onchange="listarCidades();">
				'.$tEstados.'
			</select>

			<div class="cid clear" style="height:8px;"><!-- --></div>

			<span class="cid clear fl lh-30 w-160">Cidade</span>
			<select id="i-cidade" class="cid fl" style="height:30px;padding:0 10px;width:400px;">
				'.$tCidades.'
			</select>

			<div class="clear" style="height:8px;"><!-- --></div>

			<span class="clear fl lh-30 w-160">Órgão</span>
			<input id="i-orgao" class="iText fl" type="text" placeholder="- órgão -" maxlength="255" value="'.$dFiltro_orgao.'" style="width:400px;height:30px;line-height:28px;">

			<div class="clear" style="height:8px;"><!-- --></div>

			<span class="clear fl lh-30 w-160">Modalidade</span>
			<select id="i-modalidade" class="fl" style="height:30px;padding:0 10px;width:400px;">
				<option value="0"></option>
				'.$tModalidades.'
			</select>

			<div class="clear" style="height:8px;"><!-- --></div>

			<span class="clear fl lh-30 w-160">Status</span>
			<select id="i-bx-status" class="fl" style="height:30px;padding:0 10px;width:400px;">
				<option value="0"></option>
				'.$dStatus.'
			</select>

			<span id="erro" class="clear fl t-red italic t12" style="display:none;margin-left:160px;line-height:40px;">Preencha pelo menos 1(um) campo antes de buscar.</span>

			<div class="clear" style="height:24px;"><!-- --></div>

			<a class="bt-style-1 fl" href="javascript:void(0);" onclick="buscar();" style="margin-left:160px;">Buscar</a>
			<span><a class="adv-x" href="javascript:void(0);" onclick="closeAdvSearch();"></a></span>
		</div>';
	}

	if ($p == "cli_open")
		echo '<div id="drop-box" class="drp"></div>';
}
else
{
	require_once "include/config.php";
	require_once "include/essential.php";

	if (isInside())
		header("location: ./?p=cli_index");
	else
		header("location: ../");
}

?>

</body>
</html>
