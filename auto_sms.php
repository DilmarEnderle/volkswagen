<?php

set_time_limit(0);
define("THIS_ID", 10);

$gKey = "";
if (isset($_GET["k"]))
	$gKey = $_GET["k"];

if ($gKey <> "a32cbc5af5914dc4624e2d28cd6a139e")
{
	echo "Acesso Negado.";
	exit;
}

require_once "include/config.php";
require_once "include/essential.php";

function utf8_ansi($vStr)
{
    $aAnsi = array(
"u00c0"=>"À",
"u00c1"=>"Á",
"u00c2"=>"Â",
"u00c3"=>"Ã",
"u00c4"=>"Ä",
"u00c5"=>"Å",
"u00c6"=>"Æ",
"u00c7"=>"Ç",
"u00c8"=>"È",
"u00c9"=>"É",
"u00ca"=>"Ê",
"u00cb"=>"Ë",
"u00cc"=>"Ì",
"u00cd"=>"Í",
"u00ce"=>"Î",
"u00cf"=>"Ï",
"u00d1"=>"Ñ",
"u00d2"=>"Ò",
"u00d3"=>"Ó",
"u00d4"=>"Ô",
"u00d5"=>"Õ",
"u00d6"=>"Ö",
"u00d8"=>"Ø",
"u00d9"=>"Ù",
"u00da"=>"Ú",
"u00db"=>"Û",
"u00dc"=>"Ü",
"u00dd"=>"Ý",
"u00df"=>"ß",
"u00e0"=>"à",
"u00e1"=>"á",
"u00e2"=>"â",
"u00e3"=>"ã",
"u00e4"=>"ä",
"u00e5"=>"å",
"u00e6"=>"æ",
"u00e7"=>"ç",
"u00e8"=>"è",
"u00e9"=>"é",
"u00ea"=>"ê",
"u00eb"=>"ë",
"u00ec"=>"ì",
"u00ed"=>"í",
"u00ee"=>"î",
"u00ef"=>"ï",
"u00f0"=>"ð",
"u00f1"=>"ñ",
"u00f2"=>"ò",
"u00f3"=>"ó",
"u00f4"=>"ô",
"u00f5"=>"õ",
"u00f6"=>"ö",
"u00f8"=>"ø",
"u00f9"=>"ù",
"u00fa"=>"ú",
"u00fb"=>"û",
"u00fc"=>"ü",
"u00fd"=>"ý",
"u00ff"=>"ÿ");
   	return strtr($vStr, $aAnsi);
}

function enviaSMS($vDestino, $vMensagem)
{
	$aReturn = array("status"=>0, "data_hora_status"=>"0000-00-00 00:00:00", "resultado"=>"");

	//remover acentos
	$vMensagem = removerAcentos(utf8_encode($vMensagem));
	$vDestino = preg_replace("/[^0-9]/","",$vDestino);
	if (strlen($vDestino) < 10)
	{
		$aReturn["status"] = 24;
		$aReturn["data_hora_status"] = date("Y-m-d H:i:s");
		$aReturn["resultado"] = "Destino inválido.";
		return $aReturn;
	}

	$aVars = array('user'=>'rodrigo@gelicprime.com.br', 'pass'=>'xpto3224', 'numbers'=>$vDestino, 'message'=>$vMensagem, 'return_format'=>'json');

	if (date("G") < 8) //0-23 se a hora for de meia noite ate 8 da manha entao agendar o envio para 8:00am
		$aVars["date"] = date("Y-m-d").' 08:00';

	$html = file_get_contents("https://www.paposms.com/webservice/1.0/send/?".http_build_query($aVars));
	$result = json_decode($html, true);

	if (is_null($result))
		$aReturn["status"] = 25;
	else if ($result["result"] == 1)
		$aReturn["status"] = 21;
	else
		$aReturn["status"] = 22;

	$aReturn["data_hora_status"] = date("Y-m-d H:i:s");
	$aReturn["resultado"] = utf8_ansi($html);
	return $aReturn;
}

$db = new Mysql();


$db->selectDB("gelic_gelic");
$db->query("SELECT id FROM gelic_auto WHERE id = ".THIS_ID." AND ativo > 0");
if (!$db->nextRecord())
{
	echo "Script desativado. (gelic_auto)";
	exit;
}
$db->query("UPDATE gelic_auto SET script_in = NOW() WHERE id = ".THIS_ID);
$db->selectDB("gelic_vw");


//**************************************************************************
// PROCESSAR TODOS OS NOVOS SMSs DA FILA
//**************************************************************************
$now = date("Y-m-d H:i:s");
$db->query("SELECT id, id_notificacao, destino, mensagem FROM gelic_mensagens WHERE metodo = ".M_SMS." AND data_hora_enviar <= '$now' AND status = 0 ORDER BY id");
while ($db->nextRecord())
{
	$aReturn = array("status"=>0, "data_hora_status"=>"0000-00-00 00:00:00", "resultado"=>"");

	// MARCAR COMO PROCESSADO (ISSO IMPEDE O SISTEMA DE ENVIAR A MESMA MENSAGEM NOVAMENTE SE EXISTIR ERRO)
	$now = date("Y-m-d H:i:s");
	$db->query("UPDATE gelic_mensagens SET status = 1, data_hora_status = '$now' WHERE id = ".$db->f("id"),1);


	// Anotar licitacao campo notif_nova como processada
	if ($db->f("id_notificacao") == 27) //Novas Licitações
	{
		$a = explode("(",$db->f("mensagem"));
		$id_licitacao = substr($a[1],0,strpos($a[1],")"));
		$id_licitacao = str_replace(array("L","I","C"," "), "", $id_licitacao);
		$db->query("UPDATE gelic_licitacoes SET notif_nova = 1 WHERE id = $id_licitacao",1);
	}

	$aReturn = enviaSMS($db->f("destino"), $db->f("mensagem"));
	if ($aReturn["status"] > 0)
	{
		//se foi enviado com sucesso - mover registro para tabela de log
		if ($aReturn["status"] == 21)
		{
			$db->query("INSERT INTO gelic_mensagens_log SELECT NULL, id_notificacao, id_pc_mensagem, origem, id_origem, metodo, tipo_destino, id_destino, destino, assunto, mensagem, anexos, data_hora_inserido, data_hora_enviar, ".$aReturn["status"].", '".$aReturn["data_hora_status"]."', '".utf8_decode($aReturn["resultado"])."' FROM gelic_mensagens WHERE id = ".$db->f("id"),1);
			$db->query("DELETE FROM gelic_mensagens WHERE id = ".$db->f("id"),1);
		}
		else
		{
			$db->query("UPDATE gelic_mensagens SET status = ".$aReturn["status"].", data_hora_status = '".$aReturn["data_hora_status"]."', resultado = '".utf8_decode($db->escapeString($aReturn["resultado"]))."' WHERE id = ".$db->f("id"),1);
		}
	}
	else
	{
		$db->query("UPDATE gelic_mensagens SET status = 25, data_hora_status = '$now' WHERE id = ".$db->f("id"),1);
	}
}
//**************************************************************************

$db->selectDB("gelic_gelic");
$db->query("UPDATE gelic_auto SET script_out = NOW() WHERE id = ".THIS_ID);
echo "Sucesso!";

?>
