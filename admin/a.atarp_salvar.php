<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$sInside_id = $_SESSION[SESSION_ID];
	$db = new Mysql();

	$pId = intval($_POST["f-id"]);
	$pModelo = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-modelo"])))));
	$pOrgao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-orgao"])))));
	$pLicitacao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-licitacao"])))));
	$pVigencia = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-vigencia"])))));
	$pObservacoes = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-obs"])))));
	$pStatus = intval($_POST["f-adesao"]);
	$now = date("Y-m-d H:i:s");

	if ($pId == 0)
	{
		//************ NOVO REGISTRO ***************

		if (!in_array("atarp_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}
		
		$db->query("INSERT INTO gelic_atarp VALUES (NULL, '$now', '$pModelo', '$pOrgao', '$pLicitacao', '$pVigencia', $pStatus, '$pObservacoes')");
		$dId_atarp = $db->li();

		//processar anexos
		$pUplds = json_decode($_POST["uplds"], true);
		for ($i=0; $i<count($pUplds); $i++)
		{
			if ($pUplds[$i]["action"] == "a" && $pUplds[$i]["id"] == 0 && file_exists(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"])) //adicionar
			{
				//adicionar no banco de dados
				$db->query("INSERT INTO gelic_atarp_anexos VALUES (NULL, $dId_atarp, '$now', '".utf8_decode($pUplds[$i]["filename"])."', '".$pUplds[$i]["filemd5"]."')");
				//copiar arquivo para o bucket S3 Amazon
				uploadFileBucket(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"], "vw/atarp/".$pUplds[$i]["filemd5"]);
				//remover arquivo temporario
				@unlink(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"]);
			}
			else if ($pUplds[$i]["action"] == "r" && $pUplds[$i]["id"] > 0) //remover
			{
				if ($pUplds[$i]["id"] == 0)
				{
					//remover arquivo temporario somente
					@unlink(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"]);
				}
				else
				{
					//remover do banco de dados
					$db->query("DELETE FROM gelic_atarp_anexos WHERE id = ".$pUplds[$i]["id"]);
					//remover do bucket S3 Amazon
					removeFileBucket("vw/atarp/".$pUplds[$i]["filemd5"]);
					//remover do diretorio temporario
					@unlink(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"]);
				}
			}
		}

		$aReturn[0] = 1; //sucesso
	}
	else
	{
		//************ SALVAR ALTERACOES ***************

		if (!in_array("atarp_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_atarp SET modelo = '$pModelo', orgao = '$pOrgao', licitacao = '$pLicitacao', vigencia = '$pVigencia', status = $pStatus, observacoes = '$pObservacoes' WHERE id = $pId");

		//processar anexos
		$pUplds = json_decode($_POST["uplds"], true);
		for ($i=0; $i<count($pUplds); $i++)
		{
			if ($pUplds[$i]["action"] == "a" && $pUplds[$i]["id"] == 0 && file_exists(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"])) //adicionar
			{
				//adicionar no banco de dados
				$db->query("INSERT INTO gelic_atarp_anexos VALUES (NULL, $pId, '$now', '".utf8_decode($pUplds[$i]["filename"])."', '".$pUplds[$i]["filemd5"]."')");
				//copiar arquivo para o bucket S3 Amazon
				uploadFileBucket(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"], "vw/atarp/".$pUplds[$i]["filemd5"]);
				//remover arquivo temporario
				@unlink(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"]);
			}
			else if ($pUplds[$i]["action"] == "r") //remover
			{
				if ($pUplds[$i]["id"] == 0)
				{
					//remover arquivo temporario somente
					@unlink(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"]);
				}
				else
				{
					//remover do banco de dados
					$db->query("DELETE FROM gelic_atarp_anexos WHERE id = ".$pUplds[$i]["id"]);
					//remover do bucket S3 Amazon
					removeFileBucket("vw/atarp/".$pUplds[$i]["filemd5"]);
					//remover do diretorio temporario
					@unlink(UPLOAD_DIR."~upatarp_".$sInside_id."_".$pUplds[$i]["filemd5"]);
				}
			}
		}

		$aReturn[0] = 1; //sucesso
	}
} 
echo json_encode($aReturn);

?>
