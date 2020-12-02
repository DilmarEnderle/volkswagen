<?php
define("VERSION", "20181206");

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

define("LPP", 12);
define("MAX_USR", 10);
define("PATH_HTMTOPDF", "/usr/bin/wkhtmltopdf");
define("UPLOAD_DIR", getcwd()."/arquivos/");
define("HTML_DIR", "html/");

define("SESSION_ID", "cli_vw_id");
define("SESSION_TYPE", "cli_vw_tipo");
define("SESSION_PARENT", "cli_vw_id_parent");
define("SESSION_NAME", "cli_vw_nome");
define("SESSION_ID_DN", "cli_vw_id_dn");
define("COOKIE_NAME", "cli_vw_cookie");

error_reporting(-1);
ini_set("error_log", "arquivos/php_errors_cli.log");
require_once "include/mysql.php";
require_once "include/templates.php";

?>
