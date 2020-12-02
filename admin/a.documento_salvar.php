<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();

	$pId_documento = intval($_POST["f-id"]);
	$pNome = utf8_decode(trim($_POST["f-nome"]));
	$pDescricao = utf8_decode(trim($_POST["f-descricao"]));
	$pValidade = trim($_POST["f-data-validade"]);  // 'dd/mm/yyyy'
	$pNotificar = intval($_POST["f-notificar"]);
	$pPrazo_notificacao = trim($_POST["f-prazo-notificacao"]);
	$pArquivo = utf8_decode(trim($_POST["f-arquivo"]));
	$pNovo = intval($_POST["f-novo"]);
	

	if (isValidBrDate($pValidade))
		$pValidade = brToMysql($pValidade);
	else
		$pValidade = "1111-11-11";

	//**************  prepare and cleanup prazo  **************
	$pPrazo = preg_replace('/[^0-9,+]/', '', $pPrazo_notificacao);
	if (strpos($pPrazo,",") !== false)
	{
		$pPrazo_aux = explode(",", $pPrazo);
		$pPrazo = "";
		for ($i=0; $i<count($pPrazo_aux); $i++)
		{
			if (strlen($pPrazo_aux[$i]) > 0 && $pPrazo_aux[$i]{0} == "+")
			{
				$pPrazo_aux[$i] = "+" . preg_replace('/[^0-9]/','',substr($pPrazo_aux[$i],1));
				if (strlen($pPrazo_aux[$i]) > 3)
					$pPrazo_aux[$i] = substr($pPrazo_aux[$i],0,3);
				else if (strlen($pPrazo_aux[$i]) == 1)
					$pPrazo_aux[$i] = "";
				else if ($pPrazo_aux[$i] == "+0")
					$pPrazo_aux[$i] = "0";
			}
			else
			{
				$pPrazo_aux[$i] = preg_replace('/[^0-9]/','',$pPrazo_aux[$i]);
				if (strlen($pPrazo_aux[$i]) > 2)
					$pPrazo_aux[$i] = substr($pPrazo_aux[$i],0,2);
			}

			if (strlen($pPrazo_aux[$i]) > 0 && $pPrazo_aux[$i]{0} == "+")
				$pPrazo .= ",+".intval(substr($pPrazo_aux[$i],1));
			else if (strlen($pPrazo_aux[$i]) > 0)
				$pPrazo .= ",".intval($pPrazo_aux[$i]);
		}
		if (strlen($pPrazo)>0 && $pPrazo{0} == ",")
			$pPrazo = substr($pPrazo,1);
	}
	else
	{
		if (strlen($pPrazo) > 0)
		{
			if ($pPrazo{0} == "+")
			{
				$pPrazo = "+" . preg_replace('/[^0-9]/','',substr($pPrazo,1));
				if (strlen($pPrazo) > 3)
					$pPrazo = substr($pPrazo,0,3);
				else if (strlen($pPrazo) == 1)
					$pPrazo = "";
				else if ($pPrazo == "+0")
					$pPrazo = "0";
			}
			else
			{
				$pPrazo = preg_replace('/[^0-9]/','',$pPrazo);
				if (strlen($pPrazo) > 2)
					$pPrazo = substr($pPrazo,0,2);
			}
		}
	}

	if (strlen($pPrazo) > 0)
	{
		$pPrazo = str_replace("+","p",$pPrazo);
		$new_array_n = array();
		$new_array_p = array();
	
		//remove duplicates	
		$pPrazo_aux1 = explode(",",$pPrazo);
		for ($i=0; $i<count($pPrazo_aux1); $i++)
		{
			if ($pPrazo_aux1[$i]{0} == "p")
			{
				if (strlen($pPrazo_aux1[$i]) > 0 && !in_array($pPrazo_aux1[$i], $new_array_p))
					$new_array_p[] = $pPrazo_aux1[$i];
			}
			else
			{
				if (strlen($pPrazo_aux1[$i]) > 0 && !in_array($pPrazo_aux1[$i], $new_array_n))
					$new_array_n[] = $pPrazo_aux1[$i];
			}
		}

		rsort($new_array_n); //sort array in reverse
		for ($i=0; $i<count($new_array_p); $i++) $new_array_p[$i] = str_replace("p", "", $new_array_p[$i]);
		sort($new_array_p); //sort array regular
		for ($i=0; $i<count($new_array_p); $i++) $new_array_p[$i] = "p" . $new_array_p[$i];
		$combined = array_merge($new_array_n,$new_array_p);
		$pPrazo = "";	
		for ($i=0; $i<count($combined); $i++)
		{
			if ($i == 0)
				$pPrazo .= str_replace("p","+",$combined[$i]);
			else
				$pPrazo .= "," . str_replace("p","+",$combined[$i]);
		}
	}
	//************** end of prazo ****************

	$now = date("Y-m-d H:i:s");

	//ready
	if ($pId_documento == 0)
	{
		//************ NOVO REGISTRO ***************
		if (!in_array("doc_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		if (file_exists(UPLOAD_DIR."~updoc_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($pId_documento, $pArquivo, 'doc'.time().$sInside_id));
			
			//adicionar arquivo no S3 em vw/doc/...
			uploadFileBucket(UPLOAD_DIR."~updoc_".$sInside_id.".tmp", "vw/doc/".$arquivo_md5);

			//inserir documento
			$db->query("INSERT INTO gelic_documentos VALUES (NULL, '$pNome', '$pDescricao', '$pValidade', $pNotificar, '$pPrazo', 0)");
			$dId_documento = $db->li();

			//inserir historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, $dId_documento, 0, $sInside_id, 0, 12, 0, 0, '$now', '', '', '')");
			$dId_historico = $db->li();

			//inserir documento historico
			$db->query("INSERT INTO gelic_documentos_historico VALUES (NULL, $dId_documento, $dId_historico, '$pArquivo', '$arquivo_md5')");
			$dId_documento_historico = $db->li();

			//remover arquivo temporario
			@unlink(UPLOAD_DIR."~updoc_".$sInside_id.".tmp");

			$aReturn[0] = 1; //sucesso
		}
	}
	else
	{
		//************ SALVAR ALTERACOES ***************
		if (!in_array("doc_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$db->query("UPDATE gelic_documentos SET nome = '$pNome', descricao = '$pDescricao', validade = '$pValidade', notificar = $pNotificar, prazo_notificacao = '$pPrazo' WHERE id = $pId_documento");

		//processar arquivo
		if ($pNovo > 0 && file_exists(UPLOAD_DIR."~updoc_".$sInside_id.".tmp"))
		{
			$arquivo_md5 = strtolower(getFilename($pId_documento, $pArquivo, 'doc'.time().$sInside_id));

			//adicionar arquivo no S3 em vw/doc/...
			uploadFileBucket(UPLOAD_DIR."~updoc_".$sInside_id.".tmp", "vw/doc/".$arquivo_md5);

			//inserir historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, 0, $pId_documento, 0, $sInside_id, 0, 12, 0, 0, '$now', '', '', '')");
			$dId_historico = $db->li();

			//inserir documento historico
			$db->query("INSERT INTO gelic_documentos_historico VALUES (NULL, $pId_documento, $dId_historico, '$pArquivo', '$arquivo_md5')");
			$dId_documento_historico = $db->li();

			//remover arquivo temporario
			@unlink(UPLOAD_DIR."~updoc_".$sInside_id.".tmp");
		}

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
