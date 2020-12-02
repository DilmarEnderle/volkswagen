<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("his_inserir", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();

	$pId_cliente = intval($_POST["id-cliente"]);
	$pTexto = preg_replace("/\s+/", " ", $db->escapeString(trim(utf8_decode($_POST["texto"]))));
	$pAnexo = utf8_decode(trim($_POST["anexo"]));
	$now = date("Y-m-d H:i:s");

	// Verificar se o cliente existe
	$db->query("SELECT id FROM gelic_clientes WHERE id = $pId_cliente AND tipo IN (1,2)");
	if (!$db->nextRecord())
	{
		echo json_encode($aReturn);
		exit;
	}


	//processar anexo
	if ($pAnexo <> '' && file_exists(UPLOAD_DIR."~upchis_".$sInside_id.".tmp"))
	{
		$arquivo_md5 = strtolower(getFilename(mt_rand(9,999999), $pAnexo, 'chis'.time().$sInside_id));

		//adicionar arquivo no S3 em vw/chis/...
		uploadFileBucket(UPLOAD_DIR."~upchis_".$sInside_id.".tmp", "vw/chis/".$arquivo_md5);

		//remover arquivo temporario
		@unlink(UPLOAD_DIR."~upchis_".$sInside_id.".tmp");
	}
	else
	{
		$pAnexo = "";
		$arquivo_md5 = "";
	}

	$db->query("INSERT INTO gelic_clientes_historico VALUES (NULL, $pId_cliente, $sInside_id, '$now', '$pTexto', '$pAnexo', '$arquivo_md5')");
	$aReturn[0] = 1; //sucesso
}
echo json_encode($aReturn);

?>
