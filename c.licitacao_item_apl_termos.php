<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	if ($sInside_tipo == 2)
		$cliente_parent = $sInside_id;
	else if ($sInside_tipo == 3)
		$cliente_parent = $sInside_parent;
	else if ($sInside_tipo == 4)
		$cliente_parent = $_SESSION[SESSION_ID_DN];

	$pId_licitacao = intval($_POST["f-id-licitacao"]);
	$pId_item = intval($_POST["f-id-item"]);

	$db = new Mysql();

	//pegar fase e final
	$db->query("SELECT fase, final FROM gelic_licitacoes WHERE id = $pId_licitacao");
	$db->nextRecord();
	$dFase = $db->f("fase");
	$dFinal = $db->f("final");

	$pergunta = '';
	if ($dFinal == 0)
	{
		//se este DN ja enviou uma APL para este item entao ignorar a pergunta
		$db->query("SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = $pId_licitacao AND id_item = $pId_item AND (id_cliente = $cliente_parent OR id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))");
		if (!$db->nextRecord() && $dFase > 1)
		{

			// Se este DN ja tiver uma APL aprovada entao ignorar a pergunta
			$db->query("SELECT 
    (SELECT tipo FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id ORDER BY id DESC LIMIT 1) AS tipo
FROM 
	gelic_licitacoes_apl AS apl
WHERE
	apl.id_licitacao = $pId_licitacao AND
	(apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
HAVING
	tipo = 2");
			if (!$db->nextRecord())
			{
				$db->query("
SELECT COUNT(*) AS total FROM 
(
SELECT
    if (cli.id_parent > 0, cli.id_parent, cli.id) AS global_parent
FROM
	gelic_licitacoes_apl AS apl
    INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
WHERE
	apl.id_licitacao = $pId_licitacao AND 
	apl.id_cliente <> $cliente_parent AND
    apl.id_cliente NOT IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent)
GROUP BY
	global_parent
) AS t");
				$db->nextRecord();
				if ($db->f("total") > 0)
					$pergunta = '<div class="ultimate-row" style="margin-top: 20px; background-color: #ffde66; padding: 10px; box-sizing: border-box; line-height:21px; border-radius: 5px;"><span style="font-weight:bold;background-color:#ff0000;color:#ffffff;padding:2px 6px;">ALERTA!</span><br>Já existe(m) <span class="bold t-red">'.$db->f("total").'</span> DN(s) com APL enviada(s). <span class="italic">Gostaria de enviar a sua mesmo assim ?</span></div>';
			}
		}
	}


	$db->query("SELECT texto FROM gelic_texto WHERE id = 1");
	$db->nextRecord();
	$dTermos = nl2br(utf8_encode($db->f("texto")));

	$oOutput = '<div class="ultimate-row" style="border: 1px solid #bebebe; padding: 10px; box-sizing: border-box; color: #666666; font-size: 13px;">'.$dTermos.'</div>
	'.$pergunta.'
	<div class="ultimate-row" style="margin-top: 20px;"><a id="i-aceitar" class="cb0" href="javascript:void(0);" onclick="ckSelfishONLY(this);" style="position: relative;">Aceitar as condições de envio.</a></div>
	<div id="ultimate-error"></div>';
	echo $oOutput;
}

?>
