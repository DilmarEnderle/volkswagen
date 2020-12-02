<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$gId = 0;
	if (isset($_GET["id"]))
	{
		$gId = intval($_GET["id"]);
		$db = new Mysql();
		$db->query("SELECT id, nome, uf, adve FROM gelic_cidades WHERE id = $gId");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dNome = utf8_encode($db->f("nome"));
			$dUf = $db->f("uf");
			$dAdve = $db->f("adve");

			$vTitle = 'Editar... (<a class="red">'.$dNome.'</a>)';
			$vSave = "Salvar Alterações";
			$vAbas = '';
		}
		else
		{
			$gId = 0;
		}
	}

	$aEstados = array();
	$aEstados["AC"] = "Acre";
	$aEstados["AL"] = "Alagoas";
	$aEstados["AP"] = "Amapá";
	$aEstados["AM"] = "Amazonas";
	$aEstados["BA"] = "Bahia";
	$aEstados["CE"] = "Ceará";
	$aEstados["DF"] = "Distrito Federal";
	$aEstados["ES"] = "Espírito Santo";
	$aEstados["GO"] = "Goiás";
	$aEstados["MA"] = "Maranhão";
	$aEstados["MT"] = "Mato Grosso";
	$aEstados["MS"] = "Mato Grosso do Sul";
	$aEstados["MG"] = "Minas Gerais";
	$aEstados["PA"] = "Pará";
	$aEstados["PB"] = "Paraíba";
	$aEstados["PR"] = "Paraná";
	$aEstados["PE"] = "Pernambuco";
	$aEstados["PI"] = "Piauí";
	$aEstados["RJ"] = "Rio de Janeiro";
	$aEstados["RN"] = "Rio Grande do Norte";
	$aEstados["RS"] = "Rio Grande do Sul";
	$aEstados["RO"] = "Rondônia";
	$aEstados["RR"] = "Roraima";
	$aEstados["SC"] = "Santa Catarina";
	$aEstados["SP"] = "São Paulo";
	$aEstados["SE"] = "Sergipe";
	$aEstados["TO"] = "Tocantins";
	
	if ($gId == 0)
	{
		if (!in_array("cid_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">CIDADES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dNome = "";
		$dUf = "";
		$dAdve = 0;

		$vTitle = "Nova Cidade";
		$vSave = "Salvar Nova Cidade";
		$vAbas = '';
	}
	else
	{
		if (!in_array("cid_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">CIDADES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);

			echo $tPage->body;
			exit;
		}
	}

	$dEstados = "";
	$dCidades = '<option value="">- selecione a estado -</option>';
	foreach ($aEstados as $key => $value)
	{
		if ($key == $dUf)
    		$dEstados .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
		else
			$dEstados .= '<option value="'.$key.'">'.$value.'</option>';
	}
	
	$tPage = new Template("a.cidade_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{ABAS}}", $vAbas);
	$tPage->replace("{{BT_TEXTO}}", $vSave);
	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{ESTADOS}}", $dEstados);
	$tPage->replace("{{UF}}", $dUf);
	$tPage->replace("{{ADVE}}", $dAdve);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
