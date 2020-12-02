<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("cli_visualizar", $xAccess))
	{
		$tPage = new Template("a.msg.html");
		$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
		$tPage->replace("{{TITLE}}", "Acesso Restrito!");
		$tPage->replace("{{MSG}}", '<span class="bold">CLIENTES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
		$tPage->replace("{{LNK}}", "javascript:window.history.back();");
		$tPage->replace("{{LBL}}", "Ok");
		$tPage->replace("{{VERSION}}", VERSION);
		
		echo $tPage->body;
		exit;
	}

	$tRow1_2 = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<a class="abs ativo{{A}} lf-840 tp-5 lh-20">{{AT}}</a>
		<a class="abs tipo{{T}} lf-500 tp-5 lh-20">{{TIPO}}</a>
		{{ONLINE}}
		{{RM}}
		<a class="alnk t14 abs lh-30 pl-10" href="a.cliente_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{N}}</a>
		<a class="alnk t14 abs lh-30 lf-700" href="a.cliente_editar.php?id={{ID}}">{{P}}</a>
		<a class="ahis" href="a.cliente_historico.php?id={{ID}}" title="Histórico de Registros"></a>
		{{OPENLOGIN}}
		{{RMBTN}}
	</div>';

	$tRow3_4 = '<div class="content-inside hgl" style="height: 30px; border-bottom: 1px solid #dedede;">
		<img class="subarrow" src="img/subarrow.png">
		<a class="abs ativo{{A}} lf-840 tp-5 lh-20">{{AT}}</a>
		<a class="abs tipo{{T}} lf-500 tp-5 lh-20">{{TIPO}}</a>
		{{ONLINE}}
		{{RM}}
		<a class="alnk t14 abs lh-30 pl-100" href="a.cliente_editar.php?id={{ID}}" style="display: inline-block; width: 100%; box-sizing: border-box;">{{N}}</a>
		{{OPENLOGIN}}
		{{RMBTN}}
	</div>';

	$aActive = array("Não","Sim");
	$aTipo = array();
	$aTipo[1] = "BO";
	$aTipo[2] = "DN";
	$aTipo[3] = "USR DN";
	$aTipo[4] = "REP";
	
	$oRows = '';
	$db = new Mysql();
	$time_20_seconds_ago = time() - 20;

	$db->query("SELECT COUNT(*) AS total FROM gelic_clientes");
	$db->nextRecord();
	$dTotal = $db->f("total");

	$db->query("SELECT id, tipo, nome, adve, ativo, online >= $time_20_seconds_ago AS online, deletado FROM gelic_clientes WHERE tipo IN (1,2) ORDER BY tipo DESC, dn");
	while ($db->nextRecord())
	{
		$tTmp = $tRow1_2;
		$tTmp = str_replace("{{N}}", utf8_encode($db->f("nome")), $tTmp);
		$tTmp = str_replace("{{P}}", emptyZero($db->f("adve")), $tTmp);
		$tTmp = str_replace("{{A}}", $db->f("ativo"), $tTmp);
		$tTmp = str_replace("{{AT}}", $aActive[$db->f("ativo")], $tTmp);
		$tTmp = str_replace("{{T}}", $db->f("tipo"), $tTmp);
		$tTmp = str_replace("{{TIPO}}", $aTipo[$db->f("tipo")], $tTmp);

		if ($db->f("online") > 0)
			$tTmp = str_replace("{{ONLINE}}", '<a class="online"></a>', $tTmp);
		else
			$tTmp = str_replace("{{ONLINE}}", '', $tTmp);

		$tTmp = str_replace("{{ID}}", $db->f("id"), $tTmp);

		if ($db->f("deletado") > 0)
		{
			$tTmp = str_replace("{{RM}}", '<span class="del">- REMOVIDO -</span>', $tTmp);
			$tTmp = str_replace("{{OPENLOGIN}}", '', $tTmp);
			$tTmp = str_replace("{{RMBTN}}", '', $tTmp);
		}
		else
		{
			$tTmp = str_replace("{{RM}}", '', $tTmp);

			if (in_array("cli_acessar_ambiente", $xAccess))
				$tTmp = str_replace("{{OPENLOGIN}}", '<a href="../../openlogin.php?k=ea3fa2917fd313de315922c9538c7ff8'.strtolower(md5($db->f("id"))).'" target="_blank" title="Acessar ambiente do cliente"><img src="img/openlogin.png" style="position: absolute; right: 70px; top: 2px; border: none;"></a>', $tTmp);
			else
				$tTmp = str_replace("{{OPENLOGIN}}", '', $tTmp);

			$tTmp = str_replace("{{RMBTN}}", '<a href="javascript:void(0);" onclick="removerCliente('.$db->f("id").',false);" title="Remover Cliente"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>', $tTmp);
		}



		$oRows .= $tTmp;

		if ($db->f("tipo") == 2)
		{
			$db->query("SELECT
							id,
						    tipo,
						    nome,
						    ativo,
							online >= $time_20_seconds_ago AS online,
						    deletado
						FROM
							gelic_clientes
						WHERE
							(id_parent = ".$db->f("id")." AND tipo = 3) OR
						    (id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = ".$db->f("id")."))
						ORDER BY nome",1);

			while ($db->nextRecord(1))
			{
				$tTmp = $tRow3_4;
				$tTmp = str_replace("{{N}}", utf8_encode($db->f("nome",1)), $tTmp);
				$tTmp = str_replace("{{A}}", $db->f("ativo",1), $tTmp);
				$tTmp = str_replace("{{AT}}", $aActive[$db->f("ativo",1)], $tTmp);
				$tTmp = str_replace("{{T}}", $db->f("tipo",1), $tTmp);
				$tTmp = str_replace("{{TIPO}}", $aTipo[$db->f("tipo",1)], $tTmp);

				if ($db->f("online",1) > 0)
					$tTmp = str_replace("{{ONLINE}}", '<a class="online"></a>', $tTmp);
				else
					$tTmp = str_replace("{{ONLINE}}", '', $tTmp);

				$tTmp = str_replace("{{ID}}", $db->f("id",1), $tTmp);

				if ($db->f("deletado",1) > 0)
				{
					$tTmp = str_replace("{{RM}}", '<span class="del">- REMOVIDO -</span>', $tTmp);
					$tTmp = str_replace("{{OPENLOGIN}}", '', $tTmp);
					$tTmp = str_replace("{{RMBTN}}", '', $tTmp);
				}
				else
				{
					$tTmp = str_replace("{{RM}}", '', $tTmp);

					if (in_array("cli_acessar_ambiente", $xAccess))
						$tTmp = str_replace("{{OPENLOGIN}}", '<a href="../../openlogin.php?k=ea3fa2917fd313de315922c9538c7ff8'.strtolower(md5($db->f("id",1))).'" target="_blank" title="Acessar ambiente do cliente"><img src="img/openlogin.png" style="position: absolute; right: 70px; top: 2px; border: none;"></a>', $tTmp);
					else
						$tTmp = str_replace("{{OPENLOGIN}}", '', $tTmp);

					$tTmp = str_replace("{{RMBTN}}", '<a href="javascript:void(0);" onclick="removerCliente('.$db->f("id",1).',false);" title="Remover Cliente"><img src="img/del0.png" style="position: absolute; right: 10px; top: 4px; border: none;"></a>', $tTmp);
				}

				$oRows .= $tTmp;
			}
		}
	}
	
	$tPage = new Template("a.cliente.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TOTAL}}", $dTotal);
	$tPage->replace("{{ROWS}}", $oRows);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
