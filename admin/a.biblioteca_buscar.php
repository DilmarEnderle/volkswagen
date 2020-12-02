<?php

require_once "include/config.php";
require_once "include/essential.php";

$r_array = array(0);

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$pTipo_arq = intval($_POST["f-tipo-arq"]);
	$pTipo_lnk = intval($_POST["f-tipo-lnk"]);
	$pBusca = utf8_decode(trim($_POST["f-busca"]));
	$pEstados = explode(",",$_POST["f-estados"]);
	$pFirst = intval($_POST["f-first"]);

	$pEstados_in = "";	

	if (count($pEstados) > 0)
	{
		for ($i=0; $i<count($pEstados); $i++)
		{
			if (strlen($pEstados[$i]) > 0)
				$pEstados_in .= ",'".$pEstados[$i]."'";
		}
	}

	if (strlen($pEstados_in) > 0)
		if ($pEstados_in{0} == ",")
			$pEstados_in = substr($pEstados_in, 1);

	$add_to_where = "";
	
	if ($pTipo_arq == 0 && $pTipo_lnk == 0)
		$add_to_where .= " AND tipo = 5";

	if ($pTipo_arq == 0 && $pTipo_lnk == 1)
		$add_to_where .= " AND tipo = 1";

	if ($pTipo_arq == 1 && $pTipo_lnk == 0)
		$add_to_where .= " AND tipo = 0";

	if ($pTipo_arq == 1 && $pTipo_lnk == 1)
		$add_to_where .= " AND tipo IN (0,1)";

	if (strlen($pBusca) > 0)
		$add_to_where .= " AND (nome LIKE '%$pBusca%' OR observacao LIKE '%$pBusca%')";

	if (strlen($pEstados_in) > 0)
		$add_to_where .= " AND uf IN ($pEstados_in)";
	
	
	$tRow = '<div id="r{{ID}}" class="bib-row">
		<div style="position: absolute; left: 320px; top: 0; width: 1px; height: 100%; background-color: #dedede;"><!-- vline --></div>
		<div style="position: absolute; left: 401px; top: 0; width: 1px; height: 100%; background-color: #dedede;"><!-- vline --></div>
		<div style="position: absolute; left: 472px; top: 0; width: 1px; height: 100%; background-color: #dedede;"><!-- vline --></div>
		<div style="position: absolute; left: 992px; top: 0; width: 1px; height: 100%; background-color: #dedede;"><!-- vline --></div>
		{{TTL}}
		{{TPO}}
		<span class="bib-uf">{{UF}}</span>
		<div class="bib-desc">{{DSC}}</div>
		<a class="btn-edit" href="a.biblioteca_editar.php?id={{ID}}" title="Editar"></a>
		<a class="btn-remv" href="javascript:void(0);" onclick="removerItem({{ID}},false);" title="Remover"></a>
	</div>';


	$db = new Mysql();
	$oRows = '';

	if ($pFirst > 0)
	{
		$db->query("SELECT * FROM gelic_biblioteca ORDER BY id DESC LIMIT 30");
	}
	else
	{
		$db->query("SELECT COUNT(*) AS total FROM gelic_biblioteca WHERE 1$add_to_where");
		$db->nextRecord();
		$dTotal = $db->f("total");

		$db->query("SELECT * FROM gelic_biblioteca WHERE 1$add_to_where ORDER BY id DESC");
	}
	while ($db->nextRecord())
	{
		$dObservacao = strip_tags($db->f("observacao"));

		$tTmp = $tRow;
		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		if ($db->f("tipo") == 0)
		{
			$tTmp = str_replace("{{TTL}}", '<a class="ttl" href="../arquivos/lib/'.$db->f("item").'" target="_blank">'.utf8_encode($db->f("nome")).'</a>', $tTmp);
			$tTmp = str_replace("{{TPO}}", '<a class="tpo" href="../arquivos/lib/'.$db->f("item").'" target="_blank">Arquivo</a><a class="tas">'.formatSizeUnits(intval($db->f("bytes"))).'</a><img src="img/r-file1.png">', $tTmp);
		}
		else
		{
			$tTmp = str_replace("{{TTL}}", '<a class="ttl" href="'.$db->f("item").'" target="_blank">'.utf8_encode($db->f("nome")).'</a>', $tTmp);
			$tTmp = str_replace("{{TPO}}", '<a class="tpo" href="'.$db->f("item").'" target="_blank">Link</a><img src="img/r-link1.png">', $tTmp);
		}

		if ($db->f("uf") == "00")
			$tTmp = str_replace("{{UF}}", "TODOS", $tTmp);
		else
			$tTmp = str_replace("{{UF}}", $db->f("uf"), $tTmp);

		if (strlen($db->f("observacao")) > 220)
			$tTmp = str_replace("{{DSC}}", clipString(utf8_encode($dObservacao),220).' <a class="mais" href="javascript:void(0);" onclick="expandLib('.$db->f("id").',this);">Ver mais...</a>', $tTmp);
		else if (strlen($db->f("observacao")) > 0)
			$tTmp = str_replace("{{DSC}}", utf8_encode($dObservacao), $tTmp);
		else
			$tTmp = str_replace("{{DSC}}", "", $tTmp);

		$oRows .= $tTmp;
	}

	$r_array[0] = 1; //success

	if ($pFirst > 0)
	{
		$r_array[1] = '
		<div class="row_wide_content">
			<span class="t14 gray_88" style="display: block; padding: 20px 0 0 22px;">Últimos itens adicionados</span>
		</div>
		<div class="row_wide_content" style="height: 40px;"></div>
		<div class="row_wide_content">
			<div style="position: relative; width: 1058px; height: 24px; border-bottom: 1px solid #999999; margin-left: 20px;">
				<span class="t13 bold abs" style="left: 10px; top: 0; line-height: 24px;">Título</span>
				<span class="t13 bold abs" style="left: 330px; top: 0; line-height: 24px;">Tipo</span>
				<span class="t13 bold abs" style="left: 410px; top: 0; line-height: 24px;">UF</span>
				<span class="t13 bold abs" style="left: 484px; top: 0; line-height: 24px;">Descrição</span>
			</div>
		</div>
		<div class="row_wide_content">'.$oRows.'</div>
		<div class="row_wide_content" style="height: 60px;"></div>';
	}
	else
	{
		$r_array[1] = '
		<div class="row_wide_content">
			<span class="t14 gray_88" style="display: block; padding: 20px 0 0 22px;">Resultados encontrados: '.$dTotal.'</span>
		</div>
		<div class="row_wide_content" style="height: 40px;"></div>
		<div class="row_wide_content">
			<div style="position: relative; width: 1058px; height: 24px; border-bottom: 1px solid #999999; margin-left: 20px;">
				<span class="t13 bold abs" style="left: 10px; top: 0; line-height: 24px;">Título</span>
				<span class="t13 bold abs" style="left: 330px; top: 0; line-height: 24px;">Tipo</span>
				<span class="t13 bold abs" style="left: 410px; top: 0; line-height: 24px;">UF</span>
				<span class="t13 bold abs" style="left: 484px; top: 0; line-height: 24px;">Descrição</span>
			</div>
		</div>
		<div class="row_wide_content">'.$oRows.'</div>
		<div class="row_wide_content" style="height: 60px;"></div>';
	}

}

echo json_encode($r_array);

?>
