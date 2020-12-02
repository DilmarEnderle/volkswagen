<?php
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="Check List - Requisitos para Transformação.xlsx"'); 
header('Content-Length:'.filesize("Check List - Requisitos para Transformação.xlsx"));
readfile("Check List - Requisitos para Transformação.xlsx");
?>
