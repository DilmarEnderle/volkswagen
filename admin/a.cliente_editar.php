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
		$db->query("
SELECT
	cli.id,
	cli.tipo,
	cli.ativo,
	cli.pessoa,
	cli.cpfcnpj,
	cli.dn,
	cli.adve,
	cli.nome,
	cli.departamento,
	cli.rua,
	cli.numero,
	cli.complemento,
	cli.bairro,
	cli.id_cidade,
	cli.cep,
	cli.comercial,
	cli.celular,
	cli.email,
	cli.login,
	cli.deletado,
	cli.notificacoes,
	cli.nt_celular,
	cli.nt_email,
	cli.observacoes,
	cid.uf
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
WHERE
	cli.id = $gId");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dTipo = $db->f("tipo");
			$dAtivo = $db->f("ativo");
			$dPessoa = $db->f("pessoa");
			$dCpf = "";
			$dCnpj = "";
			if ($dPessoa == 1)
			{
				$dCpf = $db->f("cpfcnpj"); 
				$dDisplay_cpf = "block";
				$dDisplay_cnpj = "none";
			}
			else 
			{
				$dCnpj = $db->f("cpfcnpj");
				$dDisplay_cpf = "none";
				$dDisplay_cnpj = "block";
			}
			$dDn = $db->f("dn");
			$dAdve = $db->f("adve");

			if ($dDn == 0) $dDn = "";
			if ($dAdve == 0) $dAdve = "";

			$dNome = utf8_encode($db->f("nome"));
			$dDepartamento = utf8_encode($db->f("departamento"));
			$dRua = utf8_encode($db->f("rua"));
			$dNumero = $db->f("numero");
			$dComplemento = utf8_encode($db->f("complemento"));
			$dBairro = utf8_encode($db->f("bairro"));
			$dId_cidade = $db->f("id_cidade");
			$dCep = $db->f("cep");
			$dComercial = $db->f("comercial");
			$dCelular = $db->f("celular");
			$dEmail = $db->f("email");
			$dLogin = $db->f("login");
			$dNotificacoes = $db->f("notificacoes");
			$dObservacoes = utf8_encode($db->f("observacoes"));
			$dObservacoes = str_replace("<br /> ", "\r\n", $dObservacoes);
			$dUf = $db->f("uf");

			$vShow_bar = "block";
			$vShow_tipo = "none";
			$vShow_dep = "none";
			$vShow_dn = "none";
			$vShow_adve = "none";
			$vShow_login = "none";
			$vShow_notif = "none";
		
			if ($dTipo == 1) //BO
			{
				$vShow_login = "block";
			}
			else if ($dTipo == 2) //DN
			{
				$vShow_dn = "block";
				$vShow_adve = "block";
				$vShow_login = "block";
				$vShow_notif = "block";
			}
			else if ($dTipo == 3) //DN FILHO
			{
				$vShow_dep = "block";
				$vShow_notif = "block";
			}
			else if ($dTipo == 4) //REP
			{
				$vShow_dep = "block";
				$vShow_notif = "block";
			}

			$dNt_email = json_decode($db->f("nt_email"), true);
			$dNt_ntf_email = $dNt_email["ntf"];
			$eml_reg = $dNt_email["reg"];

			$dNt_celular = json_decode($db->f("nt_celular"), true);
			$dNt_ntf_celular = $dNt_celular["ntf"];
			$sms_reg = $dNt_celular["reg"];


			if ($dTipo == 1) //BO
			{
				$dntf_bo = 'block';
				$dntf_dn = 'none';

				if (strlen($dNt_ntf_celular)>0)
					$dNt_celular_bo = str_split($dNt_ntf_celular);
				else
					$dNt_celular_bo = array();

				if (strlen($dNt_ntf_email)>0)
					$dNt_email_bo = str_split($dNt_ntf_email);
				else
					$dNt_email_bo = array();

				$dNt_email_dn = array();
				$dNt_celular_dn = array();

				$eml_bo = implode("",$dNt_email_bo);
				$sms_bo = implode("",$dNt_celular_bo);
				$eml_dn = "";
				$sms_dn = "";
			}
			else
			{
				$dntf_bo = 'none';
				$dntf_dn = 'block';

				if (strlen($db->f("nt_celular"))>0)
					$dNt_celular_dn = str_split($db->f("nt_celular"));
				else
					$dNt_celular_dn = array();

				if (strlen($dNt_ntf_email)>0)
					$dNt_email_dn = str_split($dNt_ntf_email);
				else
					$dNt_email_dn = array();

				$dNt_email_bo = array();
				$dNt_celular_bo = array();

				$eml_dn = implode("",$dNt_email_dn);
				$sms_dn = implode("",$dNt_celular_dn);
				$eml_bo = "";
				$sms_bo = "";
			}


			$vTitle = 'Editar... (<a class="red">'.$dNome.'</a>)';
			$vSave = "Salvar Alterações";
			$vAbas = '';

			$vShow_btns = '';

			if ($db->f("deletado") == 0)
			{
				if (in_array("cli_enviar_senha", $xAccess))
					$vShow_btns .= '<a class="bt-style-4 fr" href="javascript:void(0);" onclick="clienteSenha(false);">Re-enviar Senha</a>';

				if (in_array("cli_acessar_ambiente", $xAccess))
					$vShow_btns .= '<a href="../../openlogin.php?k=ea3fa2917fd313de315922c9538c7ff8'.strtolower(md5($db->f("id"))).'" target="_blank" title="Acessar ambiente do cliente"><img class="fr mr-10 mb-16" src="img/openlogin.png" style="border: none;"></a>';
			}

			if ($dTipo == 1 || $dTipo == 2)
				$vShow_btns .= '<a class="bt-style-4 clear fr" href="a.cliente_historico.php?id='.$dId.'">Histórico de Registros</a>';
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
		if (!in_array("cli_inserir", $xAccess))
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

		$dId = 0;
		$dTipo = 2; //DN
		$dAtivo = 1;
		$dPessoa = 2;
		$dCpf = "";
		$dCnpj = "";
		$dDisplay_cpf = "none";
		$dDisplay_cnpj = "block";
		$dDn = "";
		$dAdve = "";
		$dNome = "";
		$dDepartamento = "";
		$dRua = "";
		$dNumero = "";
		$dComplemento = "";
		$dBairro = "";
		$dId_cidade = 0;
		$dCep = "";
		$dComercial = "";
		$dCelular = "";
		$dEmail = "";
		$dLogin = "";
		$dNotificacoes = 1;
		$dNt_celular_bo = array();
		$dNt_email_bo = array();
		$dNt_celular_dn = array();
		$dNt_email_dn = array();
		$dObservacoes = "";
		$dUf = "";

		$dntf_bo = 'none';
		$dntf_dn = 'block';
		$eml_bo = "";
		$sms_bo = "";
		$eml_dn = "";
		$sms_dn = "";
		$eml_reg = "";
		$sms_reg = "";

		$vShow_bar = "none";
		$vShow_tipo = "block";
		$vShow_dep = "none";
		$vShow_dn = "block";
		$vShow_adve = "block";
		$vShow_login = "block";
		$vShow_notif = "block";

		$vTitle = "Novo DN/BO";
		$vSave = "Adicionar";
		$vAbas = '';
		$vShow_btns = '';
	}
	else
	{
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
	}

	$dEstados = "";
	$dCidades = '<option value="">- selecione um estado primeiro -</option>';
	foreach ($aEstados as $key => $value)
	{
		if ($key == $dUf)
    		$dEstados .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
		else
			$dEstados .= '<option value="'.$key.'">'.$value.'</option>';
	}
	
	if ($dUf <> "")
	{
		$dCidades = '<option value="">- cidade -</option>';
		$db->query("SELECT id, nome FROM gelic_cidades WHERE uf = '$dUf' ORDER BY nome");
		while ($db->nextRecord())
		{
			if ($dId_cidade == $db->f("id"))
				$dCidades .= '<option value="'.$db->f("id").'" selected="selected">'.utf8_encode($db->f("nome")).'</option>';
			else
				$dCidades .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
		}
	}

	function montarRegioes($vLetter, $vStr)
	{
		$aReturn = array();
		if (strpos($vStr, $vLetter."12") !== false) $aReturn[] = "1/2";
		if (strpos($vStr, $vLetter."3") !== false) $aReturn[] = "3";
		if (strpos($vStr, $vLetter."4") !== false) $aReturn[] = "4";
		if (strpos($vStr, $vLetter."5") !== false) $aReturn[] = "5";
		if (strpos($vStr, $vLetter."6") !== false) $aReturn[] = "6";
		if (count($aReturn) > 0)
			return implode(",",$aReturn);
		else
			return "- - -";
	}


	$aTipo = array();
	$aTipo[1] = "BACK OFFICE";
	$aTipo[2] = "DN (LOGIN PRINCIPAL)";
	$aTipo[3] = "USUARIO DO DN";
	$aTipo[4] = "REPRESENTANTE";

	$tPage = new Template("a.cliente_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{ABAS}}", $vAbas);
	$tPage->replace("{{BT_TEXTO}}", $vSave);

	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{TIPO}}", $dTipo);
	$tPage->replace("{{TIPO_D}}", $aTipo[$dTipo]);
	$tPage->replace("{{TIPO_DN}}", (int)in_array($dTipo,array(2)));
	$tPage->replace("{{TIPO_BO}}", (int)in_array($dTipo,array(1)));
	$tPage->replace("{{NOME}}", $dNome);
	$tPage->replace("{{PESSOA_FIS}}", (int)in_array($dPessoa,array(1)));
	$tPage->replace("{{PESSOA_JUR}}", (int)in_array($dPessoa,array(2)));
	$tPage->replace("{{CPF}}", $dCpf);
	$tPage->replace("{{CNPJ}}", $dCnpj);
	$tPage->replace("{{D_CPF}}", $dDisplay_cpf);
	$tPage->replace("{{D_CNPJ}}", $dDisplay_cnpj);
	$tPage->replace("{{DEP}}", $dDepartamento);
	$tPage->replace("{{DN}}", $dDn);
	$tPage->replace("{{ADVE}}", $dAdve);
	$tPage->replace("{{RUA}}", $dRua);
	$tPage->replace("{{NUMERO}}", $dNumero);
	$tPage->replace("{{COMPLEMENTO}}", $dComplemento);
	$tPage->replace("{{BAIRRO}}", $dBairro);
	$tPage->replace("{{ESTADOS}}", $dEstados);
	$tPage->replace("{{CIDADES}}", $dCidades);
	$tPage->replace("{{CEP}}", $dCep);
	$tPage->replace("{{ATIVO_SIM}}", (int)in_array($dAtivo,array(1)));
	$tPage->replace("{{ATIVO_NAO}}", (int)in_array($dAtivo,array(0)));
	$tPage->replace("{{LOGIN}}", $dLogin);
	$tPage->replace("{{EMAIL}}", $dEmail);
	$tPage->replace("{{COMERCIAL}}", $dComercial);
	$tPage->replace("{{CELULAR}}", $dCelular);
	$tPage->replace("{{NOTIF_SIM}}", (int)in_array($dNotificacoes,array(1)));
	$tPage->replace("{{NOTIF_NAO}}", (int)in_array($dNotificacoes,array(0)));
	$tPage->replace("{{OBSERVACOES}}", $dObservacoes);
	$tPage->replace("{{DNTF_DN}}", $dntf_dn);
	$tPage->replace("{{DNTF_BO}}", $dntf_bo);
	$tPage->replace("{{EML_DN}}", $eml_dn);
	$tPage->replace("{{SMS_DN}}", $sms_dn);
	$tPage->replace("{{EML_BO}}", $eml_bo);
	$tPage->replace("{{SMS_BO}}", $sms_bo);
	$tPage->replace("{{EML_REG}}", $eml_reg);
	$tPage->replace("{{SMS_REG}}", $sms_reg);

	foreach(range('A','S') as $letter)
		$tPage->replace("{{EML_DN_".$letter."}}", (int)in_array($letter, $dNt_email_dn));

	foreach(range('A','S') as $letter)
		$tPage->replace("{{SMS_DN_".$letter."}}", (int)in_array($letter, $dNt_celular_dn));

	foreach(range('A','J') as $letter)
		$tPage->replace("{{EML_BO_".$letter."}}", (int)in_array($letter, $dNt_email_bo));

	foreach(range('A','J') as $letter)
		$tPage->replace("{{SMS_BO_".$letter."}}", (int)in_array($letter, $dNt_celular_bo));

	foreach(range('A','J') as $letter)
		$tPage->replace("{{EML_".$letter."_REG}}", montarRegioes($letter, $eml_reg));

	foreach(range('A','J') as $letter)
		$tPage->replace("{{SMS_".$letter."_REG}}", montarRegioes($letter, $sms_reg));

	$tPage->replace("{{SHOW_BAR}}", $vShow_bar);
	$tPage->replace("{{SHOW_TIPO}}", $vShow_tipo);
	$tPage->replace("{{SHOW_DEP}}", $vShow_dep);
	$tPage->replace("{{SHOW_DN}}", $vShow_dn);
	$tPage->replace("{{SHOW_ADVE}}", $vShow_adve);
	$tPage->replace("{{SHOW_LOGIN}}", $vShow_login);
	$tPage->replace("{{SHOW_NOTIF}}", $vShow_notif);
	$tPage->replace("{{SHOW_BTNS}}", $vShow_btns);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
