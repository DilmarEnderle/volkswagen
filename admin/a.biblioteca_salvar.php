<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$sInside_id = $_SESSION[SESSION_ID];
	
	$pId = intval($_POST["f-id"]);
	$pTipo = intval($_POST["f-tipo"]);
	$pArquivo = $_POST["f-arquivo"];
	$pLink = utf8_decode(trim($_POST["f-link"]));
	$pNome = utf8_decode(trim($_POST["f-nome"]));
	$pEstado = $_POST["f-estado"];

	$pObservacao = utf8_decode(trim($_POST["f-obs"]));
	$pObservacao = str_replace('<p>', '', $pObservacao);
	$pObservacao = str_replace('</p>', '<br>', $pObservacao);
	$pObservacao = str_replace("'", '"', $pObservacao);

	$pBytes = 0;
	
	$db = new Mysql();
	if ($pId == 0)
	{
		//************ ADD NEW ***************
		if (!in_array("bib_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		if ($pTipo == 0)
		{
			$item = $pArquivo;
			//move file into place
			if (file_exists(UPLOAD_DIR."/~up_".$sInside_id.".tmp"))
			{
				$pBytes = filesize(UPLOAD_DIR."/~up_".$sInside_id.".tmp");
				copy(UPLOAD_DIR."/~up_".$sInside_id.".tmp", UPLOAD_DIR."/lib/".$pArquivo);			
				unlink(UPLOAD_DIR."/~up_".$sInside_id.".tmp");
			}
		}
		else
			$item = $pLink;

		$now = date("Y-m-d H:i:s");
		$db->query("INSERT INTO gelic_biblioteca VALUES (NULL, '$now', $pTipo, '$item', $pBytes, '$pNome', '$pEstado', '$pObservacao')");

		$aReturn[0] = 1; //sucesso
	}
	else
	{
		//************ SAVE ***************

		if (!in_array("bib_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}
		
		//restaurar dados originais
		$db->query("SELECT tipo, item, bytes FROM gelic_biblioteca WHERE id = $pId");
		$db->nextRecord();
		$dTipo_original = intval($db->f("tipo"));
		$dItem_original = $db->f("item");

		$pBytes = intval($db->f("bytes"));
		$item = $dItem_original;		

		if ($pTipo == 1)
		{
			$item = $pLink;
			$pBytes = 0;
			//remover arquivo se o tipo for alterado de arquivo para link
			if ($dTipo_original == 0)
				@unlink(UPLOAD_DIR."/lib/".$dItem_original);
		}

		if ($pTipo == 0 && $pArquivo != $dItem_original)
		{
			//remover arquivo antigo
			@unlink(UPLOAD_DIR."/lib/".$dItem_original);

			//salvar novo
			$item = $pArquivo;
			if (file_exists(UPLOAD_DIR."/~up_".$sInside_id.".tmp"))
			{
				$pBytes = filesize(UPLOAD_DIR."/~up_".$sInside_id.".tmp");
				@copy(UPLOAD_DIR."/~up_".$sInside_id.".tmp", UPLOAD_DIR."/lib/".$pArquivo);			
				@unlink(UPLOAD_DIR."/~up_".$sInside_id.".tmp");
			}
		}

		//salvar alteracoes
		$db->query("UPDATE gelic_biblioteca SET tipo = $pTipo, item = '$item', bytes = $pBytes, nome = '$pNome', uf = '$pEstado', observacao = '$pObservacao' WHERE id = $pId");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
