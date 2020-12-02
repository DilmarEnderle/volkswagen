<?php 

require_once "include/config.php";
require_once "include/essential.php";

$oHtml = "";
if (isInside())
{
	$gDrop = intval($_GET["d"]);
	$gValue = trim($_GET["v"]);

	if ($gDrop == 0) //status
	{
		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';

		$db = new Mysql();
		$db->query("SELECT id, descricao, cor_texto, cor_fundo FROM gelic_status ORDER BY descricao");
		while ($db->nextRecord())
		{
			if (in_array($db->f("id"), $a))
				$oHtml .= '<a class="drop-item1 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\''.utf8_encode($db->f("descricao")).'\',\'\');"><span style="display:inline-block;background-color:#'.$db->f("cor_fundo").';color:#'.$db->f("cor_texto").';border-radius:3px;padding:0 6px;line-height:21px;">'.utf8_encode($db->f("descricao")).'</span></a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\''.utf8_encode($db->f("descricao")).'\',\'\');"><span style="display:inline-block;background-color:#'.$db->f("cor_fundo").';color:#'.$db->f("cor_texto").';border-radius:3px;padding:0 6px;line-height:21px;">'.utf8_encode($db->f("descricao")).'</span></a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 1) //estados
	{
		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';
		$db = new Mysql();
		$db->query("SELECT uf, estado FROM gelic_uf ORDER BY estado");
		while ($db->nextRecord())
		{
			if (in_array($db->f("uf"), $a))
				$oHtml .= '<a class="drop-item1 drp w-300" href="javascript:void(0);" onclick="selItem(this,\''.$db->f("uf").'\','.$gDrop.',\''.utf8_encode($db->f("estado")).'\',\'\');">'.utf8_encode($db->f("estado")).'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-300" href="javascript:void(0);" onclick="selItem(this,\''.$db->f("uf").'\','.$gDrop.',\''.utf8_encode($db->f("estado")).'\',\'\');">'.utf8_encode($db->f("estado")).'</a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 2) //cidades
	{
		$gEstados = trim($_GET["e"]);
		if (strlen($gEstados) == 0)
		{
			echo '<span class="lh-20 red" style="padding: 0 10px;">Nenhum estado selecionado.</span>';
			exit;
		}

		$e = array();
		$e = explode(",", $gEstados);
		function singleQuotes($s) { return "'".$s."'"; }
		$e = array_map("singleQuotes", $e);

		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';
		$db = new Mysql();
		$db->query("SELECT id, nome, uf FROM gelic_cidades WHERE uf IN (".implode(",",$e).") ORDER BY nome");
		while ($db->nextRecord())
		{
			if (in_array($db->f("id"), $a))
				$oHtml .= '<a class="drop-item1 drp w-300" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\''.utf8_encode($db->f("nome")).' - '.$db->f("uf").'\',\''.$db->f("uf").'\');">'.utf8_encode($db->f("nome")).' - '.$db->f("uf").'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-300" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\''.utf8_encode($db->f("nome")).' - '.$db->f("uf").'\',\''.$db->f("uf").'\');">'.utf8_encode($db->f("nome")).' - '.$db->f("uf").'</a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 3) //regioes
	{
		$aReg = array();
		$aReg[1] = "Região 1/2";
		$aReg[3] = "Região 3";
		$aReg[4] = "Região 4";
		$aReg[5] = "Região 5";
		$aReg[6] = "Região 6";

		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';

		foreach ($aReg as $key => $value)
		{
			if (in_array($key, $a))
				$oHtml .= '<a class="drop-item1 drp w-140" href="javascript:void(0);" onclick="selItem(this,'.$key.','.$gDrop.',\''.$value.'\',\'\');">'.$value.'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-140" href="javascript:void(0);" onclick="selItem(this,'.$key.','.$gDrop.',\''.$value.'\',\'\');">'.$value.'</a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 4) //ultimas
	{
		$aUlt = array();
		$aUlt[1] = "Últimas 3";
		$aUlt[2] = "Últimas 10";
		$aUlt[3] = "Últimas 25";
		$aUlt[4] = "Últimas 50";
		$aUlt[5] = "Últimas 100";
		$aUlt[6] = "Últimas 250";
		$aUlt[7] = "Últimas 500";
		$aUlt[8] = "Últimas 1000";

		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';

		foreach ($aUlt as $key => $value)
		{
			if (in_array($key, $a))
				$oHtml .= '<a class="drop-item1 drp w-100 ult" href="javascript:void(0);" onclick="selItem(this,'.$key.','.$gDrop.',\''.$value.'\',\'\');">'.$value.'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-100 ult" href="javascript:void(0);" onclick="selItem(this,'.$key.','.$gDrop.',\''.$value.'\',\'\');">'.$value.'</a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 5) //dns
	{
		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';

		$db = new Mysql();
		$db->query("SELECT id, nome FROM gelic_clientes WHERE tipo = 2 ORDER BY dn");
		while ($db->nextRecord())
		{
			if (in_array($db->f("id"), $a))
				$oHtml .= '<a class="drop-item1 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\''.utf8_encode($db->f("nome")).'\',\'\');">'.utf8_encode($db->f("nome")).'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\''.utf8_encode($db->f("nome")).'\',\'\');">'.utf8_encode($db->f("nome")).'</a>';

		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}

}
echo $oHtml;

?>
