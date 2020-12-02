<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0,'<div class="content-inside" style="text-align: center; padding: 40px 0;">
			<span class="t14 red italic">Erro.</span>
		</div>');

if (isInside())
{
	$tRow = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<a class="alnk t14 abs lh-30 pl-10" href="a.documento_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{NOME}}</a>
		{{VALIDADE}}
		{{STATUS}}
		{{ANEXO}}
		<a href="javascript:void(0);" onclick="removerDocumento({{ID}},false);" title="Remover Documento"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
		<div style="position:absolute;left:650px;top:0;height:100%;width:1px;background-color:#cccccc;"></div>
	</div>';

	$oRows = '';
	$db = new Mysql();
	$db->query("
SELECT 
	doc.id,
	doc.nome, 
	doc.validade,
	doc.validade < CURRENT_DATE() AS expired,
	dh.nome_arquivo,
	dh.arquivo
FROM 
	gelic_documentos AS doc
	LEFT JOIN gelic_documentos_historico AS dh ON dh.id_documento = doc.id AND dh.id = (SELECT MAX(id) FROM gelic_documentos_historico WHERE id_documento = doc.id)
ORDER BY doc.nome");
	while ($db->nextRecord())
	{
		$tTmp = $tRow;
		if ($db->f("expired") > 0)
		{
			if ($db->f("validade") == "1111-11-11")
			{
				$tTmp = str_replace("{{VALIDADE}}", '<a class="t12 abs lh-30" style="right: 420px;">Sem</a>', $tTmp);
				$tTmp = str_replace("{{STATUS}}", '<a class="t12 abs lh-30 gray-88" style="left: 660px;">OK</a>', $tTmp);
			}
			else if ($db->f("validade") == "0000-00-00")
			{
				$tTmp = str_replace("{{VALIDADE}}", '<a class="t12 abs lh-30" style="right: 420px;">---</a>', $tTmp);
				$tTmp = str_replace("{{STATUS}}", '<a class="t12 abs lh-30 gray-88" style="left: 660px;">OK</a>', $tTmp);
			}
			else
			{
				$tTmp = str_replace("{{VALIDADE}}", '<a class="t12 abs lh-30 red" style="right: 420px;">'.mysqlToBr($db->f("validade")).'</a>', $tTmp);
				$tTmp = str_replace("{{STATUS}}", '<a class="t12 abs lh-30 black" style="left: 660px;">vencido</a>', $tTmp);
			}
		}
		else
		{
			$tTmp = str_replace("{{VALIDADE}}", '<a class="t12 abs lh-30 green" style="right: 420px;">'.mysqlToBr($db->f("validade")).'</a>', $tTmp);
			$tTmp = str_replace("{{STATUS}}", '<a class="t12 abs lh-30 gray-88" style="left: 660px;">OK</a>', $tTmp);
		}

		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{NOME}}", utf8_encode($db->f("nome")), $tTmp);

		if (strlen($db->f("arquivo")) > 0)
			$tTmp = str_replace("{{ANEXO}}", '<a href="'.linkFileBucket("vw/doc/".$db->f("arquivo")).'" target="_blank" title="Visualizar"><img class="icon-22" src="img/eye.png" style="position:absolute;right:40px;top:4px;border:none;"></a>', $tTmp);
		else
			$tTmp = str_replace("{{ANEXO}}", "", $tTmp);

		$oRows .= $tTmp;
	}

	$aReturn[0] = 1; //sucesso
	$aReturn[1] = '<div class="content-inside" style="height: 30px; background-color: #cccccc;">
		<span class="t14 abs bold lh-30 lf-10">Documento</span>
		<span class="t14 abs bold lh-30" style="right:420px;">Validade</span>
		<span class="t14 abs bold lh-30" style="left:660px;">Status</span>
	</div>'.$oRows;
}
echo json_encode($aReturn);

?>
