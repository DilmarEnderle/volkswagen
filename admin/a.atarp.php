<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("atarp_visualizar", $xAccess))
	{
		$oRows = utf8_decode('<div class="content-inside" style="padding-top: 20px;">
			<div class="full-row" style="padding: 0 0 2px 0; border-bottom: 1px solid #666666;">
				<span class="t18 bold lh-30 fl red">Acesso Restrito!</span>
			</div>
			<div class="t14" style="position: relative; margin: 40px auto; width: 500px; text-align: center; border: 1px solid #999999; padding: 20px 0;">
				<span class="bold">ATAS DE REGISTRO DE PREÇOS VIGENTES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span><br><br><br>
				<a class="bt-style-1" href="javascript:window.history.back();" style="display: inline-block;">Ok</a>
			</div>
		</div>');

		$dh = "none";
	}
	else
	{
		$db = new Mysql();

		$oRows = '';
		$aAdesao = array();
		$aAdesao[0] = utf8_decode("Não");
		$aAdesao[1] = "Sim";

		$db->query("SELECT atarp.*, (SELECT id FROM gelic_atarp_anexos WHERE id_atarp = atarp.id LIMIT 1) AS anexo FROM gelic_atarp AS atarp ORDER BY atarp.id DESC");
		while ($db->nextRecord())
		{
			$anx = '';
			if (strlen($db->f("anexo")) > 0)
				$anx = '<a href="javascript:void(0);" onclick="verAnexos('.$db->f("id").');" title="Anexo(s)"><img src="img/attach.png" style="position: absolute; right: 76px; top: 5px; border: 0;"></a>';

			$obs = '';
			if ($db->f("observacoes") <> '')
				$obs = utf8_decode('<a href="javascript:void(0);" onclick="verObservacoes('.$db->f("id").');" title="Observações"><img src="img/notes.png" style="position: absolute; right: 50px; top: 5px; border: 0;"></a>');

			$oRows .= '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
				<a class="alnk t14 abs lh-30 pl-10" href="a.atarp_editar.php?id='.$db->f("id").'" style="display: inline-block; width: 100%; box-sizing: border-box;">'.$db->f("modelo").'</a>
				<a class="alnk t14 abs lh-30 lf-160" href="a.atarp_editar.php?id='.$db->f("id").'">'.$db->f("orgao").'</a>
				<a class="alnk t14 abs lh-30 lf-560" href="a.atarp_editar.php?id='.$db->f("id").'">'.$db->f("licitacao").'</a>
				<a class="alnk t14 abs lh-30 lf-730" href="a.atarp_editar.php?id='.$db->f("id").'">'.$db->f("vigencia").'</a>
				<a class="abs ativo'.(int)($db->f("status") > 0).' tp-5 lh-20" style="left:890px;">'.$aAdesao[$db->f("status")].'</a>
				<a href="javascript:void(0);" onclick="removerAtarp('.$db->f("id").',false);" title="Remover"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>
				'.$anx.$obs.'
			</div>';
		}

		$dh = "block";
	}

	$tPage = new Template("a.atarp.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{DH}}", $dh);
	$tPage->replace("{{ROWS}}", utf8_encode($oRows));
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
