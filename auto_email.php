<?php

set_time_limit(0);
define("THIS_ID", 8);

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
require_once "../Phpmailer-5.2.16/PHPMailerAutoload.php";

function enviaEmail($vDestino, $vAssunto, $vMensagem, $vAnexos)
{
	$aReturn = array("status"=>0, "data_hora_status"=>"0000-00-00 00:00:00", "resultado"=>"");


	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = 'smtp.negociospublicos.com.br';
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = 'aviso@gelicprime.com.br';
	$mail->Password = 'Visgel34';
	$mail->From = 'aviso@gelicprime.com.br';
	$mail->FromName = 'GELIC';
	$mail->addAddress($vDestino);
	$mail->addReplyTo('aviso@gelicprime.com.br', 'GELIC');

/* // QUANDO DA BUG
	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->Host = 'smtp.licitacoes.com.br';
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = 'gelic@licitacoes.com.br';
	$mail->Password = '1qaz@WSX';
	$mail->From = 'gelic@licitacoes.com.br';
	$mail->FromName = 'GELIC';
	$mail->addAddress($vDestino);
	$mail->addReplyTo('gelic@licitacoes.com.br', 'GELIC');
*/
	
	if ($vAnexos <> '')
	{
		$a = json_decode($vAnexos, true);
		for ($i=0; $i<count($a); $i++)
			$mail->addAttachment(UPLOAD_DIR."anexo/".$a[$i]["arquivo"], $a[$i]["nome_arquivo"]);
	}

	$mail->isHTML(true);
	$mail->Subject = $vAssunto;
	$mail->Body = $vMensagem;
	if(!$mail->send())
	{
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = 'smtp.licitacoes.com.br';
		$mail->Port = 587;
		$mail->SMTPAuth = true;
		$mail->Username = 'gelic@licitacoes.com.br';
		$mail->Password = 'Elilic34';
		$mail->From = 'gelic@licitacoes.com.br';
		$mail->FromName = 'GELIC';
		$mail->addAddress($vDestino);
		$mail->addReplyTo('gelic@licitacoes.com.br', 'GELIC');
		if ($vAnexos <> '')
		{
			$a = json_decode($vAnexos, true);
			for ($i=0; $i<count($a); $i++)
				$mail->addAttachment(UPLOAD_DIR."anexo/".$a[$i]["arquivo"], $a[$i]["nome_arquivo"]);
		}

		$mail->isHTML(true);
		$mail->Subject = $vAssunto;
		$mail->Body = $vMensagem;
		if(!$mail->send())
		{
			$aReturn["status"] = 12; //erro
			$aReturn["data_hora_status"] = date("Y-m-d H:i:s");
			$aReturn["resultado"] = $mail->ErrorInfo.' | Saiu pelo licitacoesmais';
		} else {
			$aReturn["status"] = 11; //sucesso
			$aReturn["data_hora_status"] = date("Y-m-d H:i:s");
			$aReturn["resultado"] = "ok";
			return $aReturn;
		}
	}
	else
	{
		$aReturn["status"] = 11; //sucesso
		$aReturn["data_hora_status"] = date("Y-m-d H:i:s");
		$aReturn["resultado"] = "ok";
		return $aReturn;
	}
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
// PROCESSAR TODOS OS NOVOS EMAILS DA FILA
//**************************************************************************
$now = date("Y-m-d H:i:s");
$db->query("SELECT id, id_notificacao, destino, assunto, mensagem, anexos FROM gelic_mensagens WHERE metodo = ".M_EMAIL." AND data_hora_enviar <= '$now' AND status = 0 ORDER BY id");
while ($db->nextRecord())
{
	$aReturn = array("status"=>0, "data_hora_status"=>"0000-00-00 00:00:00", "resultado"=>"");

	// MARCAR COMO PROCESSADO (ISSO IMPEDE O SISTEMA DE ENVIAR A MESMA MENSAGEM NOVAMENTE SE EXISTIR ERRO)
	$now = date("Y-m-d H:i:s");
	$db->query("UPDATE gelic_mensagens SET status = 1, data_hora_status = '$now' WHERE id = ".$db->f("id"),1);


	// Anotar licitacao campo notif_nova como processada
	if ($db->f("id_notificacao") == 27) //Novas Licitações
	{
		$a = explode("(",$db->f("assunto"));
		$id_licitacao = str_replace(array("L","I","C"," ",")"), "", $a[1]);
		$db->query("UPDATE gelic_licitacoes SET notif_nova = 1 WHERE id = $id_licitacao",1);
	}


	$aReturn = enviaEmail($db->f("destino"), $db->f("assunto"), $db->f("mensagem"), $db->f("anexos"));
	if ($aReturn["status"] > 0)
	{
		//se foi enviado com sucesso - mover registro para tabela de log
		if ($aReturn["status"] == 11)
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
		$db->query("UPDATE gelic_mensagens SET status = 13, data_hora_status = '$now' WHERE id = ".$db->f("id"),1);
	}
}
//**************************************************************************

$db->selectDB("gelic_gelic");
$db->query("UPDATE gelic_auto SET script_out = NOW() WHERE id = ".THIS_ID);
echo "Sucesso!";

?>
