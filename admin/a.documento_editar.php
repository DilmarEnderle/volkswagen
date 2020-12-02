<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$db = new Mysql();
	$gId = 0;
	if (isset($_GET["id"]))
	{
		$gId = intval($_GET["id"]);
		$db->query("
SELECT 
	doc.nome, 
	doc.descricao, 
	doc.validade,
	doc.notificar,
	doc.prazo_notificacao,
	(SELECT nome_arquivo FROM gelic_documentos_historico WHERE id_documento = doc.id ORDER BY id DESC LIMIT 1) AS nome_arquivo,
	(SELECT arquivo FROM gelic_documentos_historico WHERE id_documento = doc.id ORDER BY id DESC LIMIT 1) AS arquivo
FROM 
	gelic_documentos AS doc
WHERE 
	doc.id = $gId");
		if ($db->nextRecord())
		{
			$dId_documento = $gId;
			$dNome = utf8_encode($db->f("nome"));
			$dDescricao = utf8_encode($db->f("descricao"));
			$dValidade = mysqlToBr($db->f("validade"));
			if ($dValidade == "00/00/0000" || $dValidade == "11/11/1111") $dValidade = ""; 
			$dNotificar = $db->f("notificar");
			$dPrazo_notificacao = $db->f("prazo_notificacao");

			$dReturn_upload = '{}';
			$dUpl_btn = "block";
			$dUpl_loading = "none";
			$dUpl_ready = "none";
			$dDoc_arquivo = "";
			$dDoc_tamanho = "0 bytes";

			if (strlen($db->f("nome_arquivo")) > 0)
			{
				$dUpl_btn = "none";
				$dUpl_loading = "none";
				$dUpl_ready = "block";
				$dDoc_tamanho = formatSizeUnits(sizeFileBucket("vw/doc/".$db->f("arquivo")));

				$short_file_name = $db->f("nome_arquivo");
				if (strlen($short_file_name) > 56)
					$short_file_name = substr($short_file_name, 0, 45)."...".substr($short_file_name, -8);

				$dDoc_arquivo = utf8_encode($short_file_name);

				$dReturn_upload = '{0: true, long_filename: "'.utf8_encode($db->f("nome_arquivo")).'", short_filename: "'.utf8_encode($short_file_name).'", file_size: "'.$dDoc_tamanho.'", is_new: 0, status: 1}';
			}

			$vTitle = 'Editar... (<a class="red">'.$dNome.'</a>)';
			$vSave = "Salvar Alterações";
		}
		else
		{
			$gId = 0;
		}
	}
	
	if ($gId == 0)
	{
		if (!in_array("doc_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">DOCUMENTOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}

		$vTitle = "Novo Documento";
		$vSave = "Salvar Novo Documento";
		$dId_documento = 0;
		$dNome = "";
		$dDescricao = "";
		$dValidade = "";
		$dNotificar = 0;
		$dPrazo_notificacao = "";

		$dReturn_upload = '{}';
		$dUpl_btn = "block";
		$dUpl_loading = "none";
		$dUpl_ready = "none";
		$dDoc_arquivo = "";
		$dDoc_tamanho = "0 bytes";
	}
	else
	{
		if (!in_array("doc_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">DOCUMENTOS</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}
	}

	$tPage = new Template("a.documento_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);
	$tPage->replace("{{ID}}", $dId_documento);
	$tPage->replace("{{NOTIFICAR}}", $dNotificar);
	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{DESC}}", $dDescricao);
	$tPage->replace("{{DATA_VALIDADE}}", $dValidade);
	$tPage->replace("{{NOTIF_SIM}}", (int)in_array($dNotificar,array(1)));
	$tPage->replace("{{NOTIF_NAO}}", (int)in_array($dNotificar,array(0)));
	$tPage->replace("{{PRAZO}}", $dPrazo_notificacao);
	//--- doc upload ---
	$tPage->replace("{{RETURN_UPLOAD}}", $dReturn_upload);
	$tPage->replace("{{UPL_BTN}}", $dUpl_btn);
	$tPage->replace("{{UPL_LOADING}}", $dUpl_loading);
	$tPage->replace("{{UPL_READY}}", $dUpl_ready);
	$tPage->replace("{{DOC_ARQUIVO}}", $dDoc_arquivo);
	$tPage->replace("{{DOC_TAMANHO}}", $dDoc_tamanho);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
