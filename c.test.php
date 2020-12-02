<?php


$output = array();
exec('ls -lha', $output);
echo "<pre>$output</pre>";

//require_once "include/config.php";
//require_once "include/essential.php";

//$output = shell_exec(PATH_HTMTOPDF." https://www.gelicprime.com.br/vw/arquivos/dilmar.html ".UPLOAD_DIR."dilmar.pdf");
//$output = shell_exec(PATH_HTMTOPDF);
//echo "<pre>$output</pre>";

?>
