<?php
function isInside()
{
    if (!isset($_SESSION))
		session_start();

	if (isset($_SESSION[SESSION_ID]))
		return true;
	else
	{
		//tentar logar com o cookie (vw)
		if (isset($_COOKIE[COOKIE_NAME]))
		{
			$now = date("Y-m-d H:i:s");

			$a = explode("!=|=!", $_COOKIE[COOKIE_NAME]);
			$cLogin = $a[0];
			$cSenha = $a[1];

			$db = new Mysql();
			$db->query("SELECT id, nome FROM gelic_admin_usuarios WHERE ativo = 1 AND MD5(login) = '$cLogin' AND MD5(senha) = '$cSenha'");
			if ($db->nextRecord())
			{
				$dId_admin_usuario = $db->f("id");
				$_SESSION[SESSION_ID] = $dId_admin_usuario;
				$_SESSION[SESSION_NAME] = utf8_encode($db->f("nome"));

				//log
				$db->query("INSERT INTO gelic_log_login VALUES (NULL, '$now', 0, $dId_admin_usuario, '".basename(__FILE__)." (".__LINE__.")', '".$_SERVER['REMOTE_ADDR']."')");

				return true;
			}
		}
		return false;
	}
}

function getSec_top()
{
	$db = new Mysql();
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_nome = $_SESSION[SESSION_NAME];

	$db->query("SELECT p.nome FROM gelic_admin_usuarios AS u INNER JOIN gelic_admin_usuarios_perfis AS p ON p.id = u.id_perfil WHERE u.id = $sInside_id");
	$db->nextRecord();
	$dNome_perfil = $db->f("nome");


	// --- Plataformas ---
	$db->selectDB("gelic_gelic");

	$host = $_SERVER["HTTP_HOST"];
	if ($host == '127.0.0.1')
		$host .= '/GelicPrime.com.br';

	$a = array();
	$a[] = array("n"=>"Gelic Padrão","sid"=>"---","lnk"=>"http://".$host."/admin");
	$a[] = array("n"=>"Volkswagen (vw)","sid"=>"vw","lnk"=>"http://".$host."/vw/admin");

	$db->query("SELECT nome, id_sistema FROM gelic_plataformas WHERE status = 'Ativa' ORDER BY nome");
	while ($db->nextRecord())
		$a[] = array("n"=>utf8_encode($db->f("nome"))." (".$db->f("id_sistema").")","sid"=>$db->f("id_sistema"),"lnk"=>"http://".$host."/".$db->f("id_sistema")."/admin");

	$dAdm_plat = '';
	$dPlat = '';
	for ($i=0; $i<count($a); $i++)
	{
		if ($i == 2)
			$dPlat .= '<div style="height:1px;border-bottom:1px dotted #888888;margin:2px 0 3px 0;"></div>';

		$dPlat .= '<a class="adm-drop-item" href="'.$a[$i]["lnk"].'">'.utf8_decode($a[$i]["n"]).'</a>';

		if (SYSTEM_ID == $a[$i]["sid"])
			$dAdm_plat = utf8_decode($a[$i]["n"]);
	}
	// --- end Plataformas ---


	$tSec_top = new Template("a.top.html");
	$tSec_top->replace("{{IN}}", $sInside_nome);
	$tSec_top->replace("{{PF}}", $dNome_perfil);
	$tSec_top->replace("{{ADMPLAT}}", $dAdm_plat);
	$tSec_top->replace("{{PLAT}}", $dPlat);

	return $tSec_top->body;
}

//input = 'yyyy-mm-dd hh:mm:ss'
//output = 'string'
function timeAgo($aMysql_datetime)
{
	$time = strtotime($aMysql_datetime);
	$tx = "";
	$diff = abs(time() - $time);

	if ($diff > 59)
	{
		if ($diff > 3599)
		{
			if ($diff > 86399)
			{
				if ($diff > 2591999)
				{
					if ($diff > 31103999)
					{
						$diff = round($diff / 31104000);
						$tx = $diff . " ano";
						if ($diff > 1) { $tx .= "s"; }
					}
					else
					{
						$diff = round($diff / 2592000);
						$tx = $diff . " mês";
						if ($diff > 1) { $tx = $diff . " meses"; }
					}
				}
				else
				{
					$diff = round($diff / 86400);
					$tx = $diff . " dia";
					if ($diff > 1) { $tx .= "s"; }
				}
			}
			else
			{
				$diff = round($diff / 3600);
				$tx = $diff . " hr";
				if ($diff > 1) { $tx .= "s"; }
			}
		}
		else
		{
			$diff = round($diff / 60);
			$tx = $diff . " min";
		}
	}
	else
	{	
		$diff = round($diff);
		if ($diff == 0 || $diff == 1)
			$tx = "agora";
		else
			$tx = $diff . " segs";
	}

	return $tx;
}

//input = dd/mm/yyyy
//output = mm/dd/yyyy
function brToUs($aDate)
{
	return substr($aDate,3,2)."/".substr($aDate,0,2)."/".substr($aDate,6,4);
}

//input = dd/mm/yyyy
//output = yyyy-mm-dd
function brToMysql($aDate)
{
	return substr($aDate,6,4)."-".substr($aDate,3,2)."-".substr($aDate,0,2);
}

//input = mm/dd/yyyy
//output = dd/mm/yyyy
function usToBr($aDate)
{
	return substr($aDate,3,2)."/".substr($aDate,0,2)."/".substr($aDate,6,4);
}

//input = mm/dd/yyyy
//output = yyyy-mm-dd
function usToMysql($aDate)
{
	return substr($aDate,6,4)."-".substr($aDate,0,2)."-".substr($aDate,3,2);
}

//input = yyyy-mm-dd
//output = dd/mm/yyyy
function mysqlToBr($aDate)
{
	return substr($aDate,8,2)."/".substr($aDate,5,2)."/".substr($aDate,0,4);
}

//input = yyyy-mm-dd
//output = mm/dd/yyyy
function mysqlToUs($aDate)
{
	return substr($aDate,5,2)."/".substr($aDate,8,2)."/".substr($aDate,0,4);
}

//input = dd/mm/yyyy
//output = true or false
function isValidBrDate($aDate)
{
	$r = false;
	$a = explode("/",$aDate);
	if (count($a) == 3)
	{
		if (intval($a[2]) < 1900)
			$r = false;
		else
			$r = checkdate(intval($a[1]), intval($a[0]), intval($a[2]));
	}

	return $r;	
}

function niceDays($aDays)
{
	if ($aDays == 0) return "hoje";
	if ($aDays == 1) return "amanhã";
	if ($aDays == -1) return "ontem";
	else return $aDays." dias";
}

function clipString($aStr,$aMax)
{
	if (strlen($aStr) > $aMax)
		return substr($aStr,0,$aMax)."...";
	else
		return $aStr;
}

function logThis($aStr, $aNl = true)
{
	$logfile = fopen('../arquivos/log.txt', 'a');
	if (!$aNl)
		fwrite($logfile, $aStr);
	else
		fwrite($logfile, $aStr.PHP_EOL);
	fclose($logfile);
}

/*
Id_notificacao = Notificacao especifica (1 - 45)
Id_pc_mensagem = integer (ID gelic_pc_mensagens)
Origem = nome do arquivo (numero da linha)
Id_origem = integer (ID gelic_clientes, ID gelic_admin_usuarios)
Metodo = integer (M_SMS, M_EMAIL)
Tipo_destino = integer (ADM_DLR, DLR_BOF, SYS_DLR, BOF_ADM, ...)
Id_destino = integer (ID gelic_clientes, ID gelic_admin_usuarios)
Destino = celular ou email
Assunto = texto
Mensagem = texto
Anexos = arquivo(s) anexo(s) em formato [{"nome_arquivo":"nome_arquivo.ext","arquivo":"0c1819507cd542e02d3335f427eb2e1f.ext","custom":"custom",etc...}]
Datahora_enviar = a data/hora que a mensagem deve ser enviada pelo sistema em YYYY-MM-DD HH:MM:SS
*/
function queueMessage($vId_notificacao, $vId_pc_mensagem, $vOrigem, $vId_origem, $vMetodo, $vTipo_destino, $vId_destino, $vDestino, $vAssunto, $vMensagem, $vAnexos, $vDatahora_enviar)
{
	$db = new Mysql();
	$now = date("Y-m-d H:i:s");
	$vAssunto = utf8_decode($vAssunto);
	$vMensagem = utf8_decode($vMensagem);
	if ($vMetodo == M_SMS)
	{
		$vMensagem = preg_replace("/[\r\n]/", "", $vMensagem);
		$vMensagem = preg_replace("/\s+/", " ", $vMensagem);
	}

	if ($vDatahora_enviar == '')
		$vDatahora_enviar = '2000-01-01 01:01:01';

	$db->query("INSERT INTO gelic_mensagens VALUES (NULL, $vId_notificacao, $vId_pc_mensagem, '$vOrigem', $vId_origem, $vMetodo, $vTipo_destino, $vId_destino, '$vDestino', '$vAssunto', '$vMensagem', '$vAnexos', '$now', '$vDatahora_enviar', 0, '$now', '')");
}

function formatSizeUnits($bytes)
{
	if ($bytes >= 1073741824)
		$bytes = number_format($bytes / 1073741824, 2) . ' GB';
	elseif ($bytes >= 1048576)
		$bytes = number_format($bytes / 1048576, 2) . ' MB';
	elseif ($bytes >= 1024)
		$bytes = number_format($bytes / 1024, 2) . ' KB';
	elseif ($bytes > 1)
		$bytes = $bytes . ' bytes';
	elseif ($bytes == 1)
		$bytes = $bytes . ' byte';
	else
		$bytes = '0 bytes';

	return $bytes;
}

function segundosConv($s)
{
	$d = floor($s / 86400);
	$div_h = $s % 86400;
    $h = abs(floor($div_h / (60 * 60)));
    $div_m = $s % (60 * 60);
    $m = abs(floor($div_m / 60));
    $div_s = $div_m % 60;
    $s = abs(ceil($div_s));
	return array("d"=>$d, "h"=>$h, "m"=>$m, "s"=>$s);
}

function getFilename($aId, $aFilename, $aPre = "fn")
{
	$hash = md5($aPre.$aId);
	$ext = ".".pathinfo($aFilename, PATHINFO_EXTENSION);
	if ($ext == ".") $ext = "";
	return $hash.$ext;
}

function emptyZero($aVal)
{
	if ($aVal > 0)
		return $aVal;
	else
		return "&nbsp;";
}

function emptySpace($aStr)
{
	if (strlen($aStr) > 0)
		return $aStr;
	else
		return "&nbsp;";
}

function getAccess()
{
	$sInside_id = $_SESSION[SESSION_ID];
	$a = "";
	$db = new Mysql();
	$db->query("SELECT up.acesso FROM gelic_admin_usuarios AS usr, gelic_admin_usuarios_perfis AS up WHERE usr.id = $sInside_id AND up.id = usr.id_perfil");
	if ($db->nextRecord())
		$a = $db->f("acesso");
	return $a;
}

function in_region($vNtf, $vUf, $vReg)
{
	$aReg = array();
	$aReg["AC"] = 6;
	$aReg["AL"] = 5;
	$aReg["AM"] = 6;
	$aReg["AP"] = 6;
	$aReg["BA"] = 5;
	$aReg["CE"] = 5;
	$aReg["DF"] = 6;
	$aReg["ES"] = 4;
	$aReg["GO"] = 6;
	$aReg["MA"] = 6;
	$aReg["MG"] = 4;
	$aReg["MS"] = 6;
	$aReg["MT"] = 6;
	$aReg["PA"] = 6;
	$aReg["PB"] = 5;
	$aReg["PE"] = 5;
	$aReg["PI"] = 5;
	$aReg["PR"] = 3;
	$aReg["RJ"] = 4;
	$aReg["RN"] = 5;
	$aReg["RO"] = 6;
	$aReg["RR"] = 6;
	$aReg["RS"] = 3;
	$aReg["SC"] = 3;
	$aReg["SE"] = 5;
	$aReg["SP"] = 12;
	$aReg["TO"] = 6;
	
	if (strpos($vReg, $vNtf.$aReg[$vUf]) === false)
		return false;
	else
		return true;
}


function rodapeEmail()
{
	return '<br>
GELIC - Gerenciamento de Licitação e Gestão de Resultados<br>
<a href="https://gelicprime.com.br/" target="_blank">https://gelicprime.com.br</a><br>
<div style="text-align:right;color:#888888;">E-mail automático. Por gentileza não responda.</div>';
}


//Arquivo = Arquivo de origem no sistema
//Key = Nome do arquivo no servidor S3
//Publico = Manter link publico para sempre
function uploadFileBucket($vArquivo, $vKey, $vPublico = true)
{
	require_once "../../Aws-3.0/aws-autoloader.php";

	try
	{
		// CREATE NEW CLIENT
		$s3 = new Aws\S3\S3Client([
			'region' => AWS_S3_REGION,
			'version' => 'latest',
			'endpoint' => 'http://s3.'.AWS_S3_REGION.'.amazonaws.com/',
			's3ForcePathStyle' => true,
			'credentials' => [
				'key' => AWS_S3_KEY,
				'secret' => AWS_S3_SECRET,
				]
			]);

		// UPLOAD FILE
		$content_type = mime_content_type($vArquivo);
		$object_array = [
			'Bucket' => AWS_S3_BUCKET,
			'Key' => $vKey,
			'SourceFile' => $vArquivo,
			'ContentType' => ($content_type === false) ? 'application/octet-stream' : $content_type];

		if ($vPublico)
			$object_array['ACL'] = 'public-read';

		$s3->putObject($object_array);

		return true;
	}
	catch(Exception $e)
	{
		return false;
	}
}

//Key = Nome do arquivo no servidor S3
function sizeFileBucket($vKey)
{
	require_once "../../Aws-3.0/aws-autoloader.php";

	try
	{
		// CREATE NEW CLIENT
		$s3 = new Aws\S3\S3Client([
			'region' => AWS_S3_REGION,
			'version' => 'latest',
			'endpoint' => 'http://s3.'.AWS_S3_REGION.'.amazonaws.com/',
			's3ForcePathStyle' => true,
			'credentials' => [
				'key' => AWS_S3_KEY,
				'secret' => AWS_S3_SECRET,
				]
			]);

		// GET OBJECT INFO
		$result = $s3->headObject(['Bucket' => AWS_S3_BUCKET, 'Key' => $vKey]);
		return intval($result["ContentLength"]);
	}
	catch(Exception $e)
	{
		return 0;
	}
}

//Key = Nome do arquivo no servidor S3
function linkFileBucket($vKey)
{
	return 'http://files.gelicprime.com.br.s3.us-east-1.amazonaws.com/'.$vKey;
}

//Key = Nome do arquivo no servidor S3
function removeFileBucket($vKey)
{
	require_once "../../Aws-3.0/aws-autoloader.php";

	try
	{
		// CREATE NEW CLIENT
		$s3 = new Aws\S3\S3Client([
			'region' => AWS_S3_REGION,
			'version' => 'latest',
			'endpoint' => 'http://s3.'.AWS_S3_REGION.'.amazonaws.com/',
			's3ForcePathStyle' => true,
			'credentials' => [
				'key' => AWS_S3_KEY,
				'secret' => AWS_S3_SECRET,
				]
			]);

		// REMOVE OBJECT
		$s3->deleteObject(['Bucket' => AWS_S3_BUCKET, 'Key' => $vKey]);

		return true;
	}
	catch(Exception $e)
	{
		return false;
	}
}

?>
