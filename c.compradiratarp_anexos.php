<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId = 0;
	if (isset($_POST["id"]))
	{
		$pId = intval($_POST["id"]);
		$db = new Mysql();

		$tRow = '<div class="file-box">
			<img src="img/file.png" style="float: left; margin-left: 4px; margin-top: 4px; border: 0;">
			<span class="gray-4c italic t11" style="float: right; line-height: 32px; margin-right: 10px;">{{FS}}</span>
			<a class="fb-a" href="arquivos/atarp/{{MD5}}" target="_blank">{{FN}}</a>
		</div>';
		$dAnexos = "";
		$db->query("SELECT id, nome_arquivo, arquivo FROM gelic_atarp_anexos WHERE id_atarp = $pId ORDER BY id");
		while ($db->nextRecord())
		{
			$tTmp = $tRow;

			$fs = formatSizeUnits(filesize(UPLOAD_DIR."atarp/".$db->f("arquivo")));
			$tTmp = str_replace("{{FS}}", $fs, $tTmp);

			$dShort_file_name = $db->f("nome_arquivo");
			if (strlen($dShort_file_name) > 80)
				$dShort_file_name = substr($dShort_file_name, 0, 69)."...".substr($dShort_file_name, -8);

			$tTmp = str_replace("{{FN}}", utf8_encode($dShort_file_name), $tTmp);
			$tTmp = str_replace("{{MD5}}", $db->f("arquivo"), $tTmp);

			$dAnexos .= $tTmp;
		}

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $dAnexos;
	}
}
echo json_encode($aReturn);

?>
