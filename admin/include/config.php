<?php
function getMyCWD($vBack)
{
	$cwd = getcwd();
	if ($cwd{0} == "/") //unix
		$a = explode("/",$cwd);
	else
		$a = explode("\\",$cwd);

	for ($i=0; $i<$vBack; $i++)
		array_pop($a);

	if ($cwd{0} == "/") //unix
		return implode("/", $a);
	else
		return implode("\\", $a);
}

define("VERSION", "20181010");

define("AWS_S3_KEY", "AKIAJZN5AQACZQUBJT4A");
define("AWS_S3_SECRET", "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");
define("AWS_S3_REGION", "us-east-1");
define("AWS_S3_BUCKET", "files.gelicprime.com.br");

define("M_EMAIL", 1);
define("M_SMS", 2);

define("ADM_DLR", 111);
define("ADM_BOF", 112);
define("ADM_ADM", 113);

define("DLR_ADM", 121);
define("DLR_BOF", 122);
define("DLR_DLR", 123);

define("BOF_DLR", 131);
define("BOF_ADM", 132);
define("BOF_BOF", 133);

define("SYS_DLR", 141);
define("SYS_ADM", 142);
define("SYS_BOF", 143);

define("SYSTEM_ID", "vw");
define("SESSION_ID", "adm_vw_id");
define("SESSION_NAME", "adm_vw_nome");
define("COOKIE_NAME", "adm_vw_cookie");

define("LPP", 12);
define("PATH_HTMTOPDF", "/usr/local/bin/wkhtmltopdf");
define("UPLOAD_DIR", getMyCWD(1)."/arquivos/");
define("HTML_DIR", "html/");
error_reporting(-1);
ini_set("error_log", "../arquivos/php_errors_adm.log");
require_once "include/mysql.php";
require_once "include/templates.php";

?>
