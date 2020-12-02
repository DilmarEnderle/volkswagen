<?php 

require_once "include/config.php";
require_once "include/essential.php";

$oHtml = "";
if (isInside())
{
	$gDrop = intval($_GET["d"]);
	$gValue = trim($_GET["v"]);

	if ($gDrop == 1) //status
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
				$oHtml .= '<a class="drop-box-item1 dbx" href="javascript:void(0);" onclick="selDbItem(this,'.$db->f("id").','.$gDrop.');"><span style="display:inline-block;background-color:#'.$db->f("cor_fundo").';color:#'.$db->f("cor_texto").';border-radius:3px;padding:0 6px;line-height:21px;">'.utf8_encode($db->f("descricao")).'</span></a>';
			else
				$oHtml .= '<a class="drop-box-item0 dbx" href="javascript:void(0);" onclick="selDbItem(this,'.$db->f("id").','.$gDrop.');"><span style="display:inline-block;background-color:#'.$db->f("cor_fundo").';color:#'.$db->f("cor_texto").';border-radius:3px;padding:0 6px;line-height:21px;">'.utf8_encode($db->f("descricao")).'</span></a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 3) //estados
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
				$oHtml .= '<a class="drop-box-item1 dbx" href="javascript:void(0);" onclick="selDbItem(this,\''.$db->f("uf").'\','.$gDrop.');">'.utf8_encode($db->f("estado")).'</a>';
			else
				$oHtml .= '<a class="drop-box-item0 dbx" href="javascript:void(0);" onclick="selDbItem(this,\''.$db->f("uf").'\','.$gDrop.');">'.utf8_encode($db->f("estado")).'</a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 2) //regioes
	{
		$aReg = array();
		$aReg[1] = "Região 1/2";
		$aReg[3] = "Região 3";
		$aReg[4] = "Região 4";
		$aReg[5] = "Região 5";
		$aReg[6] = "Região 6";

		$a = array();
		if ($gValue != '')
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';

		foreach ($aReg as $key => $value)
		{
			if (in_array($key, $a))
				$oHtml .= '<a class="drop-box-item1 dbx" href="javascript:void(0);" onclick="selDbItem(this,'.$key.','.$gDrop.');">'.$value.'</a>';
			else
				$oHtml .= '<a class="drop-box-item0 dbx" href="javascript:void(0);" onclick="selDbItem(this,'.$key.','.$gDrop.');">'.$value.'</a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
}
echo $oHtml;

?>
