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
		$db->query("SELECT * FROM gelic_atarp WHERE id = $gId");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dModelo = utf8_encode($db->f("modelo"));
			$dOrgao = utf8_encode($db->f("orgao"));
			$dLicitacao = utf8_encode($db->f("licitacao"));
			$dVigencia = utf8_encode($db->f("vigencia"));
			$dAdesao = $db->f("status");
			$dObservacoes = utf8_encode($db->f("observacoes"));

			//anexos
			$tRow = '<div id="upl-{{IDX}}" class="file-box"><img src="img/file.png" style="position: absolute; left: 4px; top: 4px; border: 0;"><span style="position: absolute; left: 34px; top: 0; line-height: 32px; font-size: 13px;">{{FN}}</span><span class="gray-4c italic t11" style="position: absolute; right: 40px; top: 0; line-height: 32px;">{{FS}}</span><a class="btn-x24" href="javascript:void(0);" onclick="removeUpload({{IDX}});" style="right: 4px; top: 4px;" title="Remover"></a></div>';
			$dAnexos = "";
			$dUplds = "";
			$db->query("SELECT id, nome_arquivo, arquivo FROM gelic_atarp_anexos WHERE id_atarp = $dId ORDER BY id");
			while ($db->nextRecord())
			{
				$tTmp = $tRow;
				$tTmp = str_replace("{{IDX}}", ($db->Row[0]-1), $tTmp);

				$fs = formatSizeUnits(sizeFileBucket("vw/atarp/".$db->f("arquivo")));
				$tTmp = str_replace("{{FS}}", $fs, $tTmp);

				$dShort_file_name = $db->f("nome_arquivo");
				if (strlen($dShort_file_name) > 74)
					$dShort_file_name = substr($dShort_file_name, 0, 63)."...".substr($dShort_file_name, -8);

				$tTmp = str_replace("{{FN}}", utf8_encode($dShort_file_name), $tTmp);

				$dUplds .= ",{filename:'".utf8_encode($db->f("nome_arquivo"))."', shortfilename:'".utf8_encode($dShort_file_name)."', filesize:'".$fs."', filemd5:'".$db->f("arquivo")."', id:".$db->f("id").", action:'n'}";
				$dAnexos .= $tTmp;
			}
			if (strlen($dUplds) > 0)
				$dUplds = substr($dUplds, 1);

			$vTitle = '<span class="italic normal">Editar...</span>';
			$vSave = "Salvar Alterações";
		}
		else
		{
			$gId = 0;
		}
	}
	
	if ($gId == 0)
	{
		if (!in_array("atarp_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">ATAS DE REGISTRO DE PREÇOS VIGENTES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dModelo = "";
		$dOrgao = "";
		$dLicitacao = "";
		$dVigencia = "";
		$dAdesao = 1;
		$dAnexos = "";
		$dUplds = "";
		$dObservacoes = "";

		$vTitle = '<span class="italic normal">Inserir Nova!</span>';
		$vSave = "Salvar Nova";
	}
	else
	{
		if (!in_array("atarp_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">ATAS DE REGISTRO DE PREÇOS VIGENTES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}
	}

	$tPage = new Template("a.atarp_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);

	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{MODELO}}", $dModelo);
	$tPage->replace("{{ORGAO}}", $dOrgao);
	$tPage->replace("{{LICITACAO}}", $dLicitacao);
	$tPage->replace("{{VIGENCIA}}", $dVigencia);
	$tPage->replace("{{OBS}}", $dObservacoes);
	$tPage->replace("{{ADESAO_SIM}}", (int)in_array($dAdesao,array(1)));
	$tPage->replace("{{ADESAO_NAO}}", (int)in_array($dAdesao,array(0)));
	$tPage->replace("{{ANX}}", $dAnexos);
	$tPage->replace("{{UPLDS}}", $dUplds);
	$tPage->replace("{{VERSION}}", VERSION);

	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
