<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_obj = $_POST["id-obj"];
	$pMsg = $_POST["msg"];
	$pEml_reg = $_POST["eml-reg"];
	$pSms_reg = $_POST["sms-reg"];

	$pNtf = $pId_obj{3};
	$pTipo = $pId_obj{4};
	$aTipo = array("e"=>"Email", "s"=>"SMS");
	$cTipo = array("e"=>"eml-reg", "s"=>"sms-reg");

	if (in_array($pNtf, array("A","B","C","D","E","F")))
		$pCat = "LICITAÇÕES";
	else
		$pCat = "COMPRA DIRETA/SRP";

	if ($pTipo == "e")
	{
		$r1 = (int)(strpos($pEml_reg, $pNtf."12") !== false);
		$r3 = (int)(strpos($pEml_reg, $pNtf."3") !== false);
		$r4 = (int)(strpos($pEml_reg, $pNtf."4") !== false);
		$r5 = (int)(strpos($pEml_reg, $pNtf."5") !== false);
		$r6 = (int)(strpos($pEml_reg, $pNtf."6") !== false);
	}
	else
	{
		$r1 = (int)(strpos($pSms_reg, $pNtf."12") !== false);
		$r3 = (int)(strpos($pSms_reg, $pNtf."3") !== false);
		$r4 = (int)(strpos($pSms_reg, $pNtf."4") !== false);
		$r5 = (int)(strpos($pSms_reg, $pNtf."5") !== false);
		$r6 = (int)(strpos($pSms_reg, $pNtf."6") !== false);
	}


	$oOutput = '<div class="ultimate-row" style="font-weight:bold;line-height:40px;">'.$pCat.' &gt; '.$pMsg.' &gt; '.$aTipo[$pTipo].'</div>
		<input type="hidden" name="tmp-id-obj" value="'.$pId_obj.'">
		<input type="hidden" name="tmp-eml-reg" value="'.$pEml_reg.'">
		<input type="hidden" name="tmp-sms-reg" value="'.$pSms_reg.'">
		<a class="rsel'.$r1.'" href="javascript:void(0);" onclick="rsel(this,\''.$cTipo[$pTipo].'\',\''.$pNtf.'12\');">Região 1/2</a>
		<a class="rsel'.$r3.'" href="javascript:void(0);" onclick="rsel(this,\''.$cTipo[$pTipo].'\',\''.$pNtf.'3\');">Região 3</a>
		<a class="rsel'.$r4.'" href="javascript:void(0);" onclick="rsel(this,\''.$cTipo[$pTipo].'\',\''.$pNtf.'4\');">Região 4</a>
		<a class="rsel'.$r5.'" href="javascript:void(0);" onclick="rsel(this,\''.$cTipo[$pTipo].'\',\''.$pNtf.'5\');">Região 5</a>
		<a class="rsel'.$r6.'" href="javascript:void(0);" onclick="rsel(this,\''.$cTipo[$pTipo].'\',\''.$pNtf.'6\');">Região 6</a>
		<div class="ultimate-row" style="margin-top: 20px;">
			<a id="ck-global" class="cb0" href="javascript:void(0);" onclick="ckONLY(this);" style="position: relative; float: left;">Aplicar global <span class="red">(alterar todas as outros notificações com estas regiões)</span></a>
		</div>';

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = $oOutput;
}
echo json_encode($aReturn);

?>
