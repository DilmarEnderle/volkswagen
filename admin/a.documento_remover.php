<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("doc_excluir", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$pId_documento = 0;
	if (isset($_POST["f-id-documento"]))
	{
		$pId_documento = intval($_POST["f-id-documento"]);
		$db = new Mysql();

		//remover arquivos (historico)
		$db->query("SELECT arquivo FROM gelic_documentos_historico WHERE id_documento = $pId_documento");
		while ($db->nextRecord())
		{
			if (file_exists(UPLOAD_DIR."doc/".$db->f("arquivo")))
				unlink(UPLOAD_DIR."doc/".$db->f("arquivo"));
		}

		//remover do documento historico
		$db->query("DELETE FROM gelic_documentos_historico WHERE id_documento = $pId_documento");
			
		//remover do historico
		$db->query("DELETE FROM gelic_historico WHERE id_documento = $pId_documento");
			
		//remover documento
		$db->query("DELETE FROM gelic_documentos WHERE id = $pId_documento");

		$aReturn[0] = 1; //sucesso			
	}
} 
echo json_encode($aReturn);

?>
