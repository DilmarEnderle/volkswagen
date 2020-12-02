<?php

require_once "include/config.php";

$db = new Mysql();
$db->query("SELECT texto FROM gelic_texto WHERE id = 1");
$db->nextRecord();
$dTermos = nl2br(utf8_encode($db->f("texto")));

$oOutput = '<div class="ultimate-row" style="border: 1px solid #bebebe; padding: 10px; box-sizing: border-box; color: #666666; font-size: 13px;">'.$dTermos.'</div>
<div class="ultimate-row" style="margin-top: 20px;"><a id="i-aceitar" class="cb0" href="javascript:void(0);" onclick="ckSelfishONLY(this);" style="position: relative;">Li e concordo com todos os termos e condições acima.</a></div>
<div id="ultimate-error"></div>';
echo $oOutput;

?>
