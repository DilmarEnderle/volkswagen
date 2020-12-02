<?php

//dialog defaults
$width = 400;
$height = 0; //auto
$title = '';
$content = '';
$x_btn = 1;
$color = 'gray';
$buttons = '';
$popover = '';

//override dialog defaults
if (isset($_POST["options"]["width"])) $width = $_POST["options"]["width"];
if (isset($_POST["options"]["height"])) $height = $_POST["options"]["height"];
if (isset($_POST["options"]["title"])) $title = $_POST["options"]["title"];
if (isset($_POST["options"]["content"])) $content = $_POST["options"]["content"];
if (isset($_POST["options"]["popover"])) $popover = $_POST["options"]["popover"];
if (isset($_POST["options"]["x_btn"])) $x_btn = $_POST["options"]["x_btn"];
if (isset($_POST["options"]["color"])) $color = $_POST["options"]["color"];
if (isset($_POST["options"]["width"])) $width = $_POST["options"]["width"];
if (isset($_POST["options"]["buttons"])) $buttons = $_POST["options"]["buttons"];

$tButtons = '';
while ($b = current($buttons))
{
	//button defaults
	$b_class = 'ultimate-btn-gray ultimate-btn-left';
	$b_href = 'javascript:void(0);';
	$b_onclick = '';
	$b_default = 0;

	//override button defaults
	if (isset($buttons[key($buttons)]["css_class"])) $b_class = $buttons[key($buttons)]["css_class"];
	if (isset($buttons[key($buttons)]["href"])) $b_href = $buttons[key($buttons)]["href"];
	if (isset($buttons[key($buttons)]["onclick"])) $b_onclick = $buttons[key($buttons)]["onclick"];
	if (isset($buttons[key($buttons)]["is_default"])) $b_default = $buttons[key($buttons)]["is_default"];

	//create button
	$tBtn = '<a {{DEF}}class="'.$b_class.'" href="'.$b_href.'"{{ONCLICK}}>'.key($buttons).'</a>';

	if (strlen($b_onclick) > 0)
		$tBtn = str_replace("{{ONCLICK}}", ' onclick="'.$b_onclick.'"', $tBtn);
	else if ($b_href == 'javascript:void(0);')
		$tBtn = str_replace("{{ONCLICK}}", ' onclick="ultimateClose();"', $tBtn);
	else
		$tBtn = str_replace("{{ONCLICK}}", '', $tBtn);

	if ($b_default > 0)
		$tBtn = str_replace("{{DEF}}", 'id="ultimate-default-btn"', $tBtn);
	else
		$tBtn = str_replace("{{DEF}}", '', $tBtn);

	$tButtons .= $tBtn;	

    next($buttons);
}

if (strlen($tButtons) > 0)
	$tButtons = '<div class="ultimate-buttons">'.$tButtons.'</div>';

if (strlen($popover) > 0)
    $popover = '<div id="ultimate-popover">'.$popover.'</div>';

$tUltimate = '
<div id="ultimate-box" style="width: '.$width.'px;">
	<div class="ultimate-title-{{COLOR}}">{{TITLE}}{{X_BTN}}</div>
	<div class="ultimate-content"{{HEIGHT}}>{{CONTENT}}</div>
	{{BUTTONS}}'.$popover.'
</div>';

$tUltimate = str_replace("{{COLOR}}", $color, $tUltimate);
$tUltimate = str_replace("{{TITLE}}", $title, $tUltimate);
if ($x_btn > 0)
	$tUltimate = str_replace("{{X_BTN}}", '<a class="ultimate-x" href="javascript:void(0);" onclick="ultimateClose();"></a>', $tUltimate);
else
	$tUltimate = str_replace("{{X_BTN}}", '', $tUltimate);

if ($height > 0)
	$tUltimate = str_replace("{{HEIGHT}}", ' style="height: '.$height.'px;"', $tUltimate);
else
	$tUltimate = str_replace("{{HEIGHT}}", '', $tUltimate);

$tUltimate = str_replace("{{CONTENT}}", $content, $tUltimate);
$tUltimate = str_replace("{{BUTTONS}}", $tButtons, $tUltimate);

echo $tUltimate;

?>
