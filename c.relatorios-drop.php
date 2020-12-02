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
				$oHtml .= '<a class="drop-item1 drp" href="javascript:void(0);" onclick="selItem(this, '.$db->f("id").');"><span style="display:inline-block;background-color:#'.$db->f("cor_fundo").';color:#'.$db->f("cor_texto").';border-radius:3px;padding:0 6px;line-height:21px;">'.utf8_encode($db->f("descricao")).'</span></a>';
			else
				$oHtml .= '<a class="drop-item0 drp" href="javascript:void(0);" onclick="selItem(this, '.$db->f("id").');"><span style="display:inline-block;background-color:#'.$db->f("cor_fundo").';color:#'.$db->f("cor_texto").';border-radius:3px;padding:0 6px;line-height:21px;">'.utf8_encode($db->f("descricao")).'</span></a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
}
echo $oHtml;

?>
