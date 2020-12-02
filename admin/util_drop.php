<?php 

require_once "include/config.php";
require_once "include/essential.php";

$r_html = "";
if (isInside())
{
	$gDrop = intval($_GET["d"]);
	$gValue = trim($_GET["v"]);
	if ($gDrop == 1)
	{
		if (strlen($gValue) > 0)
			$r_html = '<a id="todos_1" class="drop_item drp" href="javascript:void(0);" onclick="cf1(this,0);">Todos</a>';
		else
			$r_html = '<a id="todos_1" class="drop_item1 drp" href="javascript:void(0);" onclick="cf1(this,0);">Todos</a>';
		
		$r_html .= '<div style="width: 712px; height: 4px; border-bottom: 1px solid #cccccc;"><!-- --></div>';
		$r_html .= '<div style="width: 712px; height: 4px;"><!-- --></div>';
			
		$db = new Mysql();
		$db->query("SELECT id,nome FROM gelic_clientes WHERE tipo = 2 AND id_parent = 0 AND ((dn BETWEEN 19 AND 4458) OR (dn IN (10101,232323,242424,252525,262626,272727))) ORDER BY nome");
		$a = explode(",", $gValue);
		while ($db->nextRecord())
		{
			if (in_array($db->f("id"), $a))
				$r_html .= '<a class="drop_item1 drp clitem" href="javascript:void(0);" onclick="cf1(this,'.$db->f("id").');">'.utf8_encode(clipString($db->f("nome"),74)).'</a>';
			else
				$r_html .= '<a class="drop_item drp clitem" href="javascript:void(0);" onclick="cf1(this,'.$db->f("id").');">'.utf8_encode(clipString($db->f("nome"),74)).'</a>';
		}
		$r_html .= '<div style="width: 712px; height: 8px;"><!-- --></div>';
	}
	else if ($gDrop == 3) //estados
	{
		$aEstado = array();
		$aEstado[] = array("UF" => "AC", "NOME" => "Acre");
		$aEstado[] = array("UF" => "AL", "NOME" => "Alagoas");
		$aEstado[] = array("UF" => "AP", "NOME" => "Amapá");
		$aEstado[] = array("UF" => "AM", "NOME" => "Amazonas");
		$aEstado[] = array("UF" => "BA", "NOME" => "Bahia");
		$aEstado[] = array("UF" => "CE", "NOME" => "Ceará");
		$aEstado[] = array("UF" => "DF", "NOME" => "Distrito Federal");
		$aEstado[] = array("UF" => "ES", "NOME" => "Espírito Santo");
		$aEstado[] = array("UF" => "GO", "NOME" => "Goiás");
		$aEstado[] = array("UF" => "MA", "NOME" => "Maranhão");
		$aEstado[] = array("UF" => "MT", "NOME" => "Mato Grosso");
		$aEstado[] = array("UF" => "MS", "NOME" => "Mato Grosso do Sul");
		$aEstado[] = array("UF" => "MG", "NOME" => "Minas Gerais");
		$aEstado[] = array("UF" => "PA", "NOME" => "Pará");
		$aEstado[] = array("UF" => "PB", "NOME" => "Paraíba");
		$aEstado[] = array("UF" => "PR", "NOME" => "Paraná");
		$aEstado[] = array("UF" => "PE", "NOME" => "Pernambuco");
		$aEstado[] = array("UF" => "PI", "NOME" => "Piauí");
		$aEstado[] = array("UF" => "RJ", "NOME" => "Rio de Janeiro");
		$aEstado[] = array("UF" => "RN", "NOME" => "Rio Grande do Norte");
		$aEstado[] = array("UF" => "RS", "NOME" => "Rio Grande do Sul");
		$aEstado[] = array("UF" => "RO", "NOME" => "Rondônia");
		$aEstado[] = array("UF" => "RR", "NOME" => "Roraima");
		$aEstado[] = array("UF" => "SC", "NOME" => "Santa Catarina");
		$aEstado[] = array("UF" => "SP", "NOME" => "São Paulo");
		$aEstado[] = array("UF" => "SE", "NOME" => "Sergipe");
		$aEstado[] = array("UF" => "TO", "NOME" => "Tocantins");

		if (strlen($gValue) > 0)
			$r_html = '<a id="todos" class="drop_item drp" href="javascript:void(0);" onclick="cf(this,\'\');">Todos</a>';
		else
			$r_html = '<a id="todos" class="drop_item1 drp" href="javascript:void(0);" onclick="cf(this,\'\');">Todos</a>';

		$a = explode(",", $gValue);
		for ($i=0; $i<count($aEstado); $i++)
		{
			if (in_array($aEstado[$i]["UF"],$a))
				$r_html .= '<a class="drop_item1 drp esta" href="javascript:void(0);" onclick="cf(this,\''.$aEstado[$i]["UF"].'\');">'.$aEstado[$i]["NOME"].'</a>';
			else
				$r_html .= '<a class="drop_item drp esta" href="javascript:void(0);" onclick="cf(this,\''.$aEstado[$i]["UF"].'\');">'.$aEstado[$i]["NOME"].'</a>';
		}
	}
}
echo $r_html;

?>
