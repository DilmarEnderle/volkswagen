<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cid_visualizar", $xAccess))
	{
		$oRows = '<div class="content-inside" style="padding-top: 20px;">
			<div class="full-row" style="padding: 0 0 2px 0; border-bottom: 1px solid #666666;">
				<span class="t18 bold lh-30 fl red">Acesso Restrito!</span>
			</div>
			<div class="t14" style="position: relative; margin: 40px auto; width: 500px; text-align: center; border: 1px solid #999999; padding: 20px 0;">
				<span class="bold">CIDADES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span><br><br><br>
				<a class="bt-style-1" href="javascript:window.history.back();" style="display: inline-block;">Ok</a>
			</div>
		</div>';
		
		$v2 = 0;
	}
	else
	{
		$pUf = $_POST["uf"];

		$tRow = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
			<a class="alnk t14 abs lh-30 pl-10" href="a.cidade_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{NOME}}</a>
			<a class="alnk t14 abs lh-30 lf-800" href="a.cidade_editar.php?id={{ID}}">{{ADVE}}</a>
			<a href="javascript:void(0);" onclick="removerCidade({{ID}},false);" title="Remover Cidade"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
		</div>';

		$db = new Mysql();
		$oRows = '';
	
		$db->query("SELECT id, nome, adve FROM gelic_cidades WHERE uf = '$pUf' ORDER BY nome");
		while ($db->nextRecord())
		{
			$tTmp = $tRow;
			$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
			$tTmp = str_replace("{{NOME}}", utf8_encode($db->f("nome")), $tTmp);
			$tTmp = str_replace("{{ADVE}}", $db->f("adve"), $tTmp);
			$oRows .= $tTmp;
		}

		$v2 = 1;
	}
	
	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oRows;
	$aReturn[2] = $v2;
}
echo json_encode($aReturn);

?>
