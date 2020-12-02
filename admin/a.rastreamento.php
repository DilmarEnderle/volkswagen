<?php

$gNumero = '';
if (isset($_GET["n"]))
	$gNumero = trim($_GET["n"]);

$url = 'https://www2.correios.com.br/sistemas/rastreamento/resultado.cfm';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_REFERER, "https://www2.correios.com.br/sistemas/rastreamento/");
$post_data = array();
$post_data["objetos"] = $gNumero;
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$output = curl_exec($ch);
curl_close($ch);
// echo $output;


// header('Content-type: text/plain; charset=utf-8');
echo $output;
// echo mb_convert_encoding($output, 'ISO-8859-1', 'auto');

?>