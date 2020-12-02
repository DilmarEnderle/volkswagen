<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$xAccess = explode(" ",getAccess());

	$aImgTags_1 = array();
	$dVi_1 = "";
	$aImgTags_2 = array();
	$dVi_2 = "";
	
	$db = new Mysql();
	$gId = 0;

	//close button to:
	$vBack_to = "a.licitacao.php";
	$vBack_to_id = 0;
	if (isset($_GET["fr"]))
	{
		$vBack_to_id = intval($_GET["fr"]);
		if ($vBack_to_id == 1) $vBack_to = "a.licitacao_abrir.php?id=".intval($_GET["id"]);
	}
	
	if (isset($_GET["id"]))
	{
		$gId = intval($_GET["id"]);
		$db->query("SELECT 
			lic.*,
			cid.uf AS uf
		FROM 
			gelic_licitacoes AS lic, 
			gelic_cidades AS cid
		WHERE 
			lic.id = $gId AND
			lic.id_cidade = cid.id AND
			lic.deletado = 0");
		if ($db->nextRecord())
		{
			$dId = $db->f("id");
			$dId_modalidade = $db->f("id_modalidade");
			$dInstancia = $db->f("instancia");
			$dId_cidade = $db->f("id_cidade");

			$dOrgao = utf8_encode(stripslashes($db->f("orgao")));
			$dObjeto = utf8_encode(stripslashes($db->f("objeto")));
			preg_match_all('/<img[^>]+>/i',$dObjeto,$aImgTags_1);
			for ($i=0; $i<count($aImgTags_1[0]); $i++)
				if (strpos($aImgTags_1[0][$i],"../arquivos/im/") !== false)
					$dVi_1 .= ",".substr($aImgTags_1[0][$i],25,36);
			
			$dImportante = utf8_encode(stripslashes($db->f("importante")));
			preg_match_all('/<img[^>]+>/i',$dImportante,$aImgTags_2);
			for ($i=0; $i<count($aImgTags_2[0]); $i++)
				if (strpos($aImgTags_2[0][$i],"../arquivos/im/") !== false)
					$dVi_2 .= ",".substr($aImgTags_2[0][$i],25,36);
			
			$dData_abertura = mysqlToBr(substr($db->f("datahora_abertura"),0,10));
			$dHora_abertura = substr($db->f("datahora_abertura"),11,5);
			if ($dHora_abertura == "00:00") $dHora_abertura = "";
			$dData_entrega = mysqlToBr(substr($db->f("datahora_entrega"),0,10));
			$dHora_entrega = substr($db->f("datahora_entrega"),11,5);
			if ($dHora_entrega == "00:00") $dHora_entrega = "";

			$dValor = $db->f("valor");
			$dValor = "R$ ".number_format($db->f("valor"), 2, ",", ".");
			if ($dValor == "R$ 0,00") $dValor = "";
			$dLink = $db->f("link");
			$dNumero = $db->f("numero");

			$dSRP = $db->f("srp");
			$dSRP_sim = "";
			$dSRP_nao = "";
			if ($dSRP == 1)
				$dSRP_sim = " selected";
			else if ($dSRP == 2)
				$dSRP_nao = " selected";
			
			$dMeepp = $db->f("meepp");
			$dMeepp_sim = "";
			$dMeepp_nao = "";
			if ($dMeepp == 1)
				$dMeepp_sim = " selected";
			else if ($dMeepp == 2)
				$dMeepp_nao = " selected";

			$dPrazo_entrega_produto = $db->f("prazo_entrega_produto");
			$dPrazo_entrega_produto_uteis = "";
			$dPrazo_entrega_produto_corri = "";
			if ($db->f("prazo_entrega_produto_uteis") == 1)
				$dPrazo_entrega_produto_uteis = " selected";
			else
				$dPrazo_entrega_produto_corri = " selected";
			
			$dFonte = utf8_encode($db->f("fonte"));
			if ($db->f("cod_uasg") == 0)
				$dCodigo_uasg = "";
			else
				$dCodigo_uasg = $db->f("cod_uasg");

			$dNumero_rastreamento = $db->f("numero_rastreamento");
			$dValidade_proposta = utf8_encode($db->f("validade_proposta"));
			$dVigencia_contrato = utf8_encode($db->f("vigencia_contrato"));
			$dUf = $db->f("uf");

			$dUploads = "";
			$dEditais = '';

			$db->query("SELECT id, data_publicacao, nome_arquivo, arquivo FROM gelic_licitacoes_edital WHERE id_licitacao = $dId");
			while ($db->nextRecord())
			{
				$short_file_name = $db->f("nome_arquivo");
				if (strlen($short_file_name) > 56)
					$short_file_name = substr($short_file_name, 0, 45)."...".substr($short_file_name, -8);

				$publish_date = mysqlToBr($db->f("data_publicacao"));
				if ($publish_date == "00/00/0000")
					$publish_date = "";

				$file_size = formatSizeUnits(sizeFileBucket("vw/edital/".$db->f("arquivo")));

				$dUploads .= ",{id:".$db->f("id").",status:3,long_filename:'".utf8_encode($db->f("nome_arquivo"))."',short_filename:'".utf8_encode($short_file_name)."',file_size:'".$file_size."',file_md5:'".$db->f("arquivo")."',publish_date:'".$publish_date."'}";

				$dEditais .= '<div id="edital-'.($db->Row[0]-1).'" class="upload-ready" style="display:block;">
					<div class="file">
						<img src="img/file.png" style="float:left;margin-left:6px;margin-top:4px;border:0;">
						<span class="filename t13 red italic fl" style="margin-left:4px;line-height:32px;">'.utf8_encode($short_file_name).'</span>
						<span class="filesize gray-4c italic t11 fr" style="line-height:32px;margin-right:6px;">'.$file_size.'</span>
					</div>
					<input class="data-publicacao iText fl" type="text" placeholder="dd/mm/aaaa" value="'.$publish_date.'" style="width:150px;margin-left:4px;">
					<div style="float:left;width:36px;height:34px;">
						<a class="btn-x24" href="javascript:void(0);" onclick="removeUpload('.($db->Row[0]-1).');" style="right: 4px; top: 4px;" title="Remover"></a>
					</div>
				</div>';
			}
	
			$dUploads = trim($dUploads, ',');

			if ($dUploads != "")
				$dEditais = '<div id="upload-ready-header" style="display:block;">
					<span>Arquivo</span>
					<span>Data de Publicação</span>
				</div>'.$dEditais;
			else
				$dEditais = '<div id="upload-ready-header">
					<span>Arquivo</span>
					<span>Data de Publicação</span>
				</div>';

			$vTitle = "Editar...";
			$vSave = "Salvar Alterações";
		}
		else
		{
			$gId = 0;
		}
	}
	
	if ($gId == 0)
	{
		if (!in_array("lic_inserir", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">LICITAÇÕES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
		}

		$dId = 0;
		$dOrgao = "";
		$dObjeto = "";
		$dImportante = "";
		$dId_modalidade = 0;
		$dInstancia = 0;
		$dUf = "";
		$dId_cidade = 0;
		$dValor = "";
		$dData_abertura = "";
		$dHora_abertura = "";
		$dData_entrega = "";
		$dHora_entrega = "";
		$dLink = "";
		$dNumero = "";
		$dSRP_sim = "";
		$dSRP_nao = "";
		$dMeepp_sim = "";
		$dMeepp_nao = "";
		$dPrazo_entrega_produto = "";
		$dPrazo_entrega_produto_uteis = "";
		$dPrazo_entrega_produto_corri = "";
		$dFonte = "";
		$dCodigo_uasg = "";
		$dNumero_rastreamento = "";
		$dValidade_proposta = "";
		$dVigencia_contrato = "";
		$dUploads = "";
		$dEditais = '<div id="upload-ready-header">
			<span>Arquivo</span>
			<span>Data de Publicação</span>
		</div>';

		$vTitle = "Nova Licitação";
		$vSave = "Salvar Nova Licitação";
	}
	else
	{
		if (!in_array("lic_visualizar", $xAccess))
		{
			$tPage = new Template("a.msg.html");
			$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
			$tPage->replace("{{TITLE}}", "Acesso Restrito!");
			$tPage->replace("{{MSG}}", '<span class="bold">LICITAÇÕES</span><br><br><span class="gray-88">Você não tem permissão neste módulo.</span>');
			$tPage->replace("{{LNK}}", "javascript:window.history.back();");
			$tPage->replace("{{LBL}}", "Ok");
			$tPage->replace("{{VERSION}}", VERSION);
			
			echo $tPage->body;
			exit;
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

	$aInstancia = array();
	$aInstancia[1] = "Municipal";
	$aInstancia[2] = "Estadual";
	$aInstancia[3] = "Federal";

	$aPrazos = array();

	$dEstados = "";
	$dCidades = '<option value="0">- selecione um estado primeiro -</option>';
	foreach ($aEstados as $key => $value)
	{
		if ($key == $dUf)
    		$dEstados .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
		else
			$dEstados .= '<option value="'.$key.'">'.$value.'</option>';
	}
	
	if ($dUf <> "")
	{
		$dCidades = '<option value="0">- cidade -</option>';
		$db->query("SELECT id, nome FROM gelic_cidades WHERE uf = '$dUf' ORDER BY nome");
		while ($db->nextRecord())
		{
			if ($dId_cidade == $db->f("id"))
				$dCidades .= '<option value="'.$db->f("id").'" selected="selected">'.utf8_encode($db->f("nome")).'</option>';
			else
				$dCidades .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
		}
	}


	$dMods = "";
	$db->query("SELECT id, nome FROM gelic_modalidades WHERE antigo = 0 ORDER BY nome");
	while ($db->nextRecord())
	{
		if ($db->f("id") == $dId_modalidade)
			$dMods .= '<option value="'.$db->f("id").'" selected="selected">'.utf8_encode($db->f("nome")).'</option>';
		else
			$dMods .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
	}

	$dInstancias = '';
	foreach ($aInstancia as $key => $value)
	{
		if ($key == $dInstancia)
    		$dInstancias .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
		else
			$dInstancias .= '<option value="'.$key.'">'.$value.'</option>';
	}
	
	
	$tPage = new Template("a.licitacao_editar.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));

	$tPage->replace("{{ID}}", $dId);
	$tPage->replace("{{ORGAO}}", $dOrgao);
	$tPage->replace("{{OBJETO}}", $dObjeto);
	$tPage->replace("{{IMPORTANTE}}", $dImportante);
	$tPage->replace("{{VI}}", $dVi_1.$dVi_2);
	$tPage->replace("{{MODALIDADES}}", $dMods);
	$tPage->replace("{{INSTANCIAS}}", $dInstancias);
	$tPage->replace("{{ESTADOS}}", $dEstados);
	$tPage->replace("{{CIDADES}}", $dCidades);
	$tPage->replace("{{VALOR}}", $dValor);
	$tPage->replace("{{DATA_ABERTURA}}", $dData_abertura);
	$tPage->replace("{{HORA_ABERTURA}}", $dHora_abertura);
	$tPage->replace("{{DATA_ENTREGA}}", $dData_entrega);
	$tPage->replace("{{HORA_ENTREGA}}", $dHora_entrega);
	$tPage->replace("{{LINK}}", $dLink);
	$tPage->replace("{{NUMERO}}", $dNumero);
	$tPage->replace("{{SRP_SIM}}", $dSRP_sim);
	$tPage->replace("{{SRP_NAO}}", $dSRP_nao);
	$tPage->replace("{{MEEPP_SIM}}", $dMeepp_sim);
	$tPage->replace("{{MEEPP_NAO}}", $dMeepp_nao);
	$tPage->replace("{{PRAZO_ENTREGA_PRODUTO}}", $dPrazo_entrega_produto);
	$tPage->replace("{{PEP_UTEIS}}", $dPrazo_entrega_produto_uteis);
	$tPage->replace("{{PEP_CORRI}}", $dPrazo_entrega_produto_corri);
	$tPage->replace("{{FONTE}}", $dFonte);
	$tPage->replace("{{UASG}}", $dCodigo_uasg);
	$tPage->replace("{{NUMERO_RASTREAMENTO}}", $dNumero_rastreamento);
	$tPage->replace("{{VALIDADE_PROPOSTA}}", $dValidade_proposta);
	$tPage->replace("{{VIGENCIA_CONTRATO}}", $dVigencia_contrato);
	//--- editais ---
	$tPage->replace("{{UPLOADS}}", $dUploads);
	$tPage->replace("{{EDITAIS}}", $dEditais);
	//--- titulo/botao ---
	$tPage->replace("{{BACK_TO}}", $vBack_to);
	$tPage->replace("{{BACK_TO_ID}}", $vBack_to_id);
	$tPage->replace("{{TITLE}}", $vTitle);
	$tPage->replace("{{BT_TEXTO}}", $vSave);
	if ($dId > 0)
		$tPage->replace("{{ABRIR}}", '<a class="bt-style-2 fr mr-10" href="a.licitacao_abrir.php?id='.$dId.'">Abrir</a>');
	else
		$tPage->replace("{{ABRIR}}", "");

	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	header("location: index.php");
}

?>
