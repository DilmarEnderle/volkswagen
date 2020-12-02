<?php
require_once "include/config.php";
require_once "include/essential.php";
require_once "a.licitacao_ntfnova.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	$sInside_id = $_SESSION[SESSION_ID];
	$db = new Mysql();

	$host = $_SERVER["HTTP_HOST"];
	if ($host == '127.0.0.1')
		$full_host = $_SERVER['REQUEST_SCHEME'].'://'.$host.'/Gelic';
	else
		$full_host = $_SERVER['REQUEST_SCHEME'].'://'.$host;

	$aEstados = array();
	$aEstados["AC"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["AL"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["AP"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["AM"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["BA"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["CE"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["DF"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["ES"] = "'ES','MG','RJ'";
	$aEstados["GO"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["MA"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["MT"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["MS"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["MG"] = "'ES','MG','RJ'";
	$aEstados["PA"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["PB"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["PR"] = "'PR','RS','SC'";
	$aEstados["PE"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["PI"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["RJ"] = "'ES','MG','RJ'";
	$aEstados["RN"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["RS"] = "'PR','RS','SC'";
	$aEstados["RO"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["RR"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";
	$aEstados["SC"] = "'PR','RS','SC'";
	$aEstados["SP"] = "'SP'";
	$aEstados["SE"] = "'AL','BA','CE','PB','PE','PI','RN','SE'";
	$aEstados["TO"] = "'AC','AP','AM','DF','GO','MA','MT','MS','PA','RO','RR','TO'";

	//----- para processar imagens ------
	$aImgTags_1 = array();
	$aImgTags_2 = array();
	$aCompare = array(); //posted images
	$pVi = explode(",",$_POST["vi"]); //posted images + failed tries

	//----- receber valores postados ------
	$pId_licitacao = intval($_POST["f-id"]);
	$pOrgao = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-orgao"])))));
	$pObjeto = utf8_decode($_POST["f-objeto"]);
	$pObjeto = str_replace($full_host."/vw/arquivos", "http://files.gelicprime.com.br.s3.us-east-1.amazonaws.com/vw/im", $pObjeto);
	$pImportante = utf8_decode($_POST["f-importante"]);
	$pImportante = str_replace($full_host."/vw/arquivos", "http://files.gelicprime.com.br.s3.us-east-1.amazonaws.com/vw/im", $pImportante);
	$pId_modalidade = intval($_POST["f-modalidade"]);
	$pInstancia = intval($_POST["f-instancia"]);
	$pId_cidade = intval($_POST["f-cidade"]);
	$pUf = $_POST["f-estado"];
	$pValor = trim($_POST["f-valor"]);
	$pValor = str_replace(".","",$pValor);
	$pValor = str_replace("R","",$pValor);
	$pValor = str_replace("$","",$pValor);
	$pValor = str_replace(" ","",$pValor);
	$pValor = str_replace(",",".",$pValor);
	if (strlen($pValor) == 0)
		$pValor = '0.00';
	$pData_abertura = trim($_POST["f-data_abertura"]);  // 'dd/mm/yyyy'
	$pHora_abertura = trim($_POST["f-hora_abertura"]);  // '' or 'hh:mm'
	$pData_entrega = trim($_POST["f-data_entrega"]);    // 'dd/mm/yyyy'
	$pHora_entrega = trim($_POST["f-hora_entrega"]);    // '' or 'hh:mm'
	$pLink = trim($_POST["f-link"]);
	$pNumero = utf8_decode(trim($_POST["f-numero"]));
	$pSrp = intval($_POST["f-srp"]);
	$pMeepp = intval($_POST["f-meepp"]);
	$pPrazo_entrega_produto = intval($_POST["f-prazo_entrega"]);
	$pPrazo_entrega_produto_uteis = intval($_POST["f-prazo_entrega_uteis"]);
	$pFonte = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-fonte"])))));
	$pCodigo_pregao = intval(preg_replace('/[^0-9]/','',$pNumero));
	$pCodigo_uasg = intval($_POST["f-uasg"]);
	$pNumero_rastreamento = trim($_POST["f-numero_rastreamento"]);
	$pValidade_proposta = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-validade_proposta"])))));
	$pVigencia_contrato = preg_replace("/\s+/", " ", $db->escapeString(strip_tags(trim(utf8_decode($_POST["f-vigencia_contrato"])))));

	$pVerif_dup = intval($_POST["verif_dup"]);

	//------- limpar arquivos desnecessarios -------	
	preg_match_all('/<img[^>]+>/i',$pObjeto,$aImgTags_1);
	for ($i=0; $i<count($aImgTags_1[0]); $i++)
		if (strpos($aImgTags_1[0][$i],"http://files.gelicprime.com.br.s3.us-east-1.amazonaws.com/vw/im/") !== false)
			$aCompare[] = substr($aImgTags_1[0][$i],73,37);


	preg_match_all('/<img[^>]+>/i',$pImportante,$aImgTags_2);
	for ($i=0; $i<count($aImgTags_2[0]); $i++)
		if (strpos($aImgTags_2[0][$i],"http://files.gelicprime.com.br.s3.us-east-1.amazonaws.com/vw/im") !== false)
			$aCompare[] = substr($aImgTags_2[0][$i],73,37);


	$pObjeto = addslashes($pObjeto);
	$pImportante = addslashes($pImportante);	
	$aEditais = json_decode($_POST["editais"], true);


	//**** DATA DE ABERTURA ****
	if (isValidBrDate($pData_abertura))
		$pData_abertura = brToMysql($pData_abertura);
	else
		$pData_abertura = "0000-00-00";

	$pData_abertura_dup = $pData_abertura; //para verificar duplicidade de licitacao

	//**** HORA DE ABERTURA ****
	if (strlen($pHora_abertura) > 0)
	{
		$pHora_abertura_aux = explode(":", $pHora_abertura);
		if (intval($pHora_abertura_aux[0]) > 23 || intval($pHora_abertura_aux[1]) > 59)
		{
			$aReturn[0] = 2; //hora abertura invalida
			echo json_encode($aReturn);
			exit;
		}
		$pHora_abertura = " ".$pHora_abertura.":00";
	}
	else
		$pHora_abertura .= " 00:00:00";


	//**** DATA DE ENTREGA ****
	if (isValidBrDate($pData_entrega))
		$pData_entrega = brToMysql($pData_entrega);
	else
		$pData_entrega = "0000-00-00";

	//**** HORA DE ENTREGA ****
	if (strlen($pHora_entrega) > 0)
	{
		$pHora_entrega_aux = explode(":", $pHora_entrega);
		if (intval($pHora_entrega_aux[0]) > 23 || intval($pHora_entrega_aux[1]) > 59)
		{
			$aReturn[0] = 8; //hora entrega invalida
			echo json_encode($aReturn);
			exit;
		}
		$pHora_entrega = " ".$pHora_entrega.":00";
	}
	else
		$pHora_entrega .= " 00:00:00";


	//$dVerificar_datas = true;
	//if ($dVerificar_datas && $pId_licitacao == 0)
	//{
		$pData_hoje_int = intval(date("Ymd")); //para calculo de datas

		//------- verificar data de abertura -----------
		$pData_abertura_int = intval(str_replace("-","",$pData_abertura));
		//if ($pData_abertura_int < $pData_hoje_int) //verifica se a data de abertura eh menor do que hoje
		//{
		//	$aReturn[0] = 3; //data de abertura invalida
		//	echo json_encode($aReturn);
		//	exit;
		//}

		//--------- verificar data de entrega ----------
		$pData_entrega_int = intval(str_replace("-","",$pData_entrega));
		//if ($pData_entrega_int < $pData_hoje_int || $pData_entrega_int > $pData_abertura_int)
		if ($pData_entrega_int > $pData_abertura_int)
		{
			$aReturn[0] = 4; //data de entrega inválida
			echo json_encode($aReturn);
			exit;
		}
	//}

	$pDatahora_abertura = $pData_abertura.$pHora_abertura;
	$pDatahora_entrega = $pData_entrega.$pHora_entrega;

	
	//-------- DNs ------------------------------------------
	if ($pId_cidade < 1)
	{
		$aReturn[0] = 7; //dados incorretos
		echo json_encode($aReturn);
		exit;
	}

	$aClientes = array();
	if ($pId_cidade == 3314) //curitiba
	{
		//pegar pool
		$db->query("SELECT id FROM gelic_clientes WHERE dn = 232323");
		$db->nextRecord();
		$aClientes[] = $db->f("id");
	}
	else if ($pId_cidade == 4266) //porto alegre
	{
		//pegar pool
		$db->query("SELECT id FROM gelic_clientes WHERE dn = 242424");
		$db->nextRecord();
		$aClientes[] = $db->f("id");
	}
	else if ($pId_cidade == 805) //brasilia
	{
		//pegar pool
		$db->query("SELECT id FROM gelic_clientes WHERE dn = 252525");
		$db->nextRecord();
		$aClientes[] = $db->f("id");
	}
	else if ($pId_cidade == 1440) //belo horizonte
	{
		//pegar pool
		$db->query("SELECT id FROM gelic_clientes WHERE dn = 262626");
		$db->nextRecord();
		$aClientes[] = $db->f("id");
	}
	else if ($pId_cidade == 5370) //sao paulo
	{
		//pegar pool
		$db->query("SELECT id FROM gelic_clientes WHERE dn = 272727");
		$db->nextRecord();
		$aClientes[] = $db->f("id");
	}
	else
	{
		//pegar todos os DNs da cidade selecionada
		$db->query("SELECT adve FROM gelic_cidades WHERE id = $pId_cidade AND adve > 0");
		if ($db->nextRecord())
		{
			$dAdve = $db->f("adve");
			$db->query("SELECT id FROM gelic_clientes WHERE tipo = 2 AND adve = $dAdve");
			while ($db->nextRecord())
				$aClientes[] = $db->f("id");
		}
	}

	$now = time();

	$t_24horas = $now;
	$aFeriados = array();
	$db->query("SELECT CONCAT(LPAD(mes,2,'0'),LPAD(dia,2,'0')) AS feriado FROM gelic_feriados");
	while ($db->nextRecord()) { $aFeriados[] = $db->f("feriado"); } //array com valores em (mmdd)
	$pPointer = date("Ymdw", $t_24horas);
	$pDia_util = 0;
	while ($pDia_util == 0)
	{
		$t_24horas += 86400;
		$pPointer = date("Ymdw", $t_24horas);
		if (substr($pPointer, -1) <> "6" && substr($pPointer, -1) <> "0" && !in_array(substr($pPointer,4,4), $aFeriados)) //se nao for sabado, domingo e feriado
			$pDia_util = 1;
	}
	$pDatahora_limite = date("Y-m-d H:i:s", $t_24horas);


	if ($pId_licitacao == 0)
	{
		//************ NOVO REGISTRO ***************
		if (!in_array("lic_inserir", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}


		//verificar se a licitacao ja pode constar no sistema
		if ($pVerif_dup == 1)
		{
			$db->query("SELECT lic.id, lic.orgao, lic.objeto, lic.datahora_abertura, lic.valor, lic.numero, CONCAT(cid.nome,' - ',cid.uf) AS localizacao, mdl.nome AS modalidade FROM gelic_licitacoes AS lic INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade WHERE DATE(lic.datahora_abertura) = '$pData_abertura_dup' AND lic.id_cidade = $pId_cidade AND lic.numero = '$pNumero' ORDER BY lic.id DESC LIMIT 1");
			if ($db->nextRecord())
			{
				if ($db->f("valor") == 0.00)
					$v = '<span class="fl ml-10 gray-aa w-600">- não informado -</span>';
				else
					$v = '<span class="fl ml-10 w-600">R$ '.number_format($db->f("valor"),2,',','.').'</span>';

				$o = strip_tags(stripslashes($db->f("objeto")));
				if (strlen($o) > 500)
					$o = substr($o, 0, 500) . '...';

				$dab_h = substr($db->f("datahora_abertura"),11,5);
				if ($dab_h == "00:00") $dab_h = "--:--";


				//retornar com pergunta
				$aReturn[0] = 6; //duplicada
				$aReturn[1] = '<span class="bold">ATENÇÃO: Esta licitação pode já constar no sistema.</span><br><br>- Clique em <span class="italic red">Ignorar</span> para inserir mesmo assim.';

				$aReturn[1] .= '<div class="ultimate-row mt-20 bold" style="background-color: #666666; line-height:28px; padding-left: 10px; box-sizing: border-box; color: #ffffff;">Licitação com possível conflito:</div>
				
					<div class="ultimate-row" style="padding: 4px 0; background-color: #efefef;">
						<span class="bold fl w-140 ml-10">ID Licitação:</span>
						<span class="fl ml-10 w-600">'.$db->f("id").'</span>
					</div>
					<div class="ultimate-row" style="border-top: 1px solid #cccccc;padding: 4px 0;background-color: #efefef;">
						<span class="bold fl w-140 ml-10">Órgão:</span>
						<span class="fl ml-10 w-600">'.utf8_encode($db->f("orgao")).'</span>
					</div>
					<div class="ultimate-row" style="border-top: 1px solid #cccccc;padding: 4px 0;background-color: #efefef;">
						<span class="bold fl w-140 ml-10">Objeto:</span>
						<span class="fl ml-10 w-600">'.utf8_encode($o).'</span>
					</div>
					<div class="ultimate-row" style="border-top: 1px solid #cccccc;padding: 4px 0;background-color: #efefef;">
						<span class="bold fl w-140 ml-10">Data/Hora Abertura:</span>
						<span class="fl ml-10 w-600">'.mysqlToBr(substr($db->f("datahora_abertura"),0,10)).' '.$dab_h.'</span>
					</div>
					<div class="ultimate-row" style="border-top: 1px solid #cccccc;padding: 4px 0;background-color: #efefef;">
						<span class="bold fl w-140 ml-10">Valor:</span>'.$v.'
					</div>
					<div class="ultimate-row" style="border-top: 1px solid #cccccc;padding: 4px 0;background-color: #efefef;">
						<span class="bold fl w-140 ml-10">Nº da Licitação:</span>
						<span class="fl ml-10 w-600">'.$db->f("numero").'</span>
					</div>
					<div class="ultimate-row" style="border-top: 1px solid #cccccc;padding: 4px 0;background-color: #efefef;">
						<span class="bold fl w-140 ml-10">Localização:</span>
						<span class="fl ml-10 w-600">'.utf8_encode($db->f("localizacao")).'</span>
					</div>
					<div class="ultimate-row" style="border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc;padding: 4px 0;background-color: #efefef;">
						<span class="bold fl w-140 ml-10">Modalidade:</span>
						<span class="fl ml-10 w-600">'.utf8_encode($db->f("modalidade")).'</span>
					</div>';

				echo json_encode($aReturn);
				exit;
			}
		}

		$now = date("Y-m-d H:i:s", $now);

		$fase = 1;
		$aprovar_apl = 1;
		if (count($aClientes) == 0)
		{
			$fase = 2;
			$aprovar_apl = 0;
		}

		$db->query("INSERT INTO gelic_licitacoes VALUES (NULL, $pId_cidade, $pId_modalidade, $pInstancia, '$pOrgao', '$pObjeto', '$pImportante', '$now', '$pDatahora_abertura', '$pDatahora_entrega', '$pDatahora_limite', $fase, $aprovar_apl, 0, $pValor, '$pLink', '$pNumero', 0, $pCodigo_pregao, $pCodigo_uasg, 0, $pSrp, $pMeepp, $pPrazo_entrega_produto, $pPrazo_entrega_produto_uteis, '$pFonte', '$pNumero_rastreamento', 0, 0, '$pValidade_proposta', '$pVigencia_contrato', 0, 0)");
		if ($db->Errno[0] == 0)
		{
			$dId_licitacao = $db->li();

			if (count($aClientes) == 0)
			{
				// --- SEGUNDA CHAMADA ---

				//inserir no historico
				$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, $sInside_id, 0, 11, 0, 0, '$now', '', '', '')");

				//inserir DNs regiao
				$db->query("SELECT cli.id FROM gelic_clientes AS cli, gelic_cidades AS cid WHERE cli.tipo = 2 AND cid.uf IN (".$aEstados[$pUf].") AND cli.id_cidade = cid.id");
				while ($db->nextRecord())
					$db->query("INSERT INTO gelic_licitacoes_clientes VALUES (NULL, $dId_licitacao, ".$db->f("id").")",1);

				//inserir para grupos
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 8, 4, 0)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 8, 4, 0)"); //BO
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 3, 8, 4, 0)"); //DN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 4, 8, 4, 0)"); //OUTRO DN
			}
			else
			{
				// --- PRIMEIRA CHAMADA ---

				//inserir no historico
				$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, $sInside_id, 0, 11, 0, 0, '$now', '', '', '')");

				//inserir para clientes
				for ($i=0; $i<count($aClientes); $i++)
					$db->query("INSERT INTO gelic_licitacoes_clientes VALUES (NULL, $dId_licitacao, ".$aClientes[$i].")");

				//inserir para grupos
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 1, 7, 2, 0)"); //ADMIN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 2, 7, 2, 0)"); //BO
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 3, 7, 2, 0)"); //DN
				$db->query("INSERT INTO gelic_licitacoes_abas VALUES (NULL, $dId_licitacao, 4, 7, 2, 0)"); //OUTRO DN
			}

			$aLic_aba_status = array("fr"=>array(),"to"=>array());
			$aLic_aba_status["fr"][] = array("grupo"=>1, "aba"=>0, "status"=>0, "fixo"=>0);
			$aLic_aba_status["fr"][] = array("grupo"=>2, "aba"=>0, "status"=>0, "fixo"=>0);
			$aLic_aba_status["fr"][] = array("grupo"=>3, "aba"=>0, "status"=>0, "fixo"=>0);
			$aLic_aba_status["fr"][] = array("grupo"=>4, "aba"=>0, "status"=>0, "fixo"=>0);

			//STATUS E ABAS DEPOIS DA ALTERACAO
			$db->query("SELECT grupo, id_aba, id_status, status_fixo FROM gelic_licitacoes_abas WHERE id_licitacao = $dId_licitacao ORDER BY grupo");
			while ($db->nextRecord())
				$aLic_aba_status["to"][] = array("grupo"=>$db->f("grupo"), "aba"=>$db->f("id_aba"), "status"=>$db->f("id_status"), "fixo"=>$db->f("status_fixo"));

			//anotar fase inicial no historico
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $dId_licitacao, 0, 0, $sInside_id, 0, 13, 0, $fase, '$now', '".json_encode($aLic_aba_status)."', '', '')");


			$aReturn[0] = 1; //sucesso
			$aReturn[1] = $dId_licitacao;


			//processar imagens coladas
			for ($i=0; $i<count($pVi); $i++)
			{
				if (strlen($pVi[$i]) > 0 && file_exists(UPLOAD_DIR.$pVi[$i]))
				{
					if (in_array($pVi[$i], $aCompare))
					{
						//adicionar arquivo no S3 em vw/im/...
						uploadFileBucket(UPLOAD_DIR.$pVi[$i], "vw/im/".substr($pVi[$i],1), true);
					}
					
					//remover arquivo temporario
					unlink(UPLOAD_DIR.$pVi[$i]);
				}
			}


			//processar editais
			for ($i=0; $i<count($aEditais); $i++)
			{
				if ($aEditais[$i]["status"] == 1) //adicionar
				{
					//adicionar no banco de dados
					if (isValidBrDate($aEditais[$i]["publish_date"]))
						$data_publicacao = brToMysql($aEditais[$i]["publish_date"]);
					else
						$data_publicacao = '0000-00-00';

					$db->query("INSERT INTO gelic_licitacoes_edital VALUES (NULL, $dId_licitacao, '$now', '".$data_publicacao."', '".utf8_decode($aEditais[$i]["long_filename"])."', '".$aEditais[$i]["file_md5"]."')");

					//adicionar arquivo no S3 em vw/edital/...
					if (uploadFileBucket(UPLOAD_DIR."~upedital_".$sInside_id."_".$aEditais[$i]["file_md5"], "vw/edital/".$aEditais[$i]["file_md5"]))
						//remover arquivo temporario
						unlink(UPLOAD_DIR."~upedital_".$sInside_id."_".$aEditais[$i]["file_md5"]);
				}
				else if ($aEditais[$i]["status"] == 2)
				{
					//remover arquivo temporario
					unlink(UPLOAD_DIR."~upedital_".$sInside_id."_".$aEditais[$i]["file_md5"]);
				}
			}
		}
	}
	else
	{
		//************ SALVAR ALTERACOES ***************
		if (!in_array("lic_editar", $xAccess))
		{
			$aReturn[0] = 9; //acesso restrito
			echo json_encode($aReturn);
			exit;
		}

		$now = date("Y-m-d H:i:s", $now);

		//processar editais
		for ($i=0; $i<count($aEditais); $i++)
		{
			if ($aEditais[$i]["status"] == 1 && !empty($aEditais[$i]["file_md5"])) //adicionar
			{
				//adicionar no banco de dados
				if (isValidBrDate($aEditais[$i]["publish_date"]))
					$data_publicacao = brToMysql($aEditais[$i]["publish_date"]);
				else
					$data_publicacao = '0000-00-00';

				$db->query("INSERT INTO gelic_licitacoes_edital VALUES (NULL, $pId_licitacao, '$now', '".$data_publicacao."', '".utf8_decode($aEditais[$i]["long_filename"])."', '".$aEditais[$i]["file_md5"]."')");

				//adicionar arquivo no S3 em vw/edital/...
				if (uploadFileBucket(UPLOAD_DIR."~upedital_".$sInside_id."_".$aEditais[$i]["file_md5"], "vw/edital/".$aEditais[$i]["file_md5"]))
					//remover arquivo temporario
					unlink(UPLOAD_DIR."~upedital_".$sInside_id."_".$aEditais[$i]["file_md5"]);
			}
			else if ($aEditais[$i]["status"] == 2)
			{
				//remover arquivo temporario
				unlink(UPLOAD_DIR."~upedital_".$sInside_id."_".$aEditais[$i]["file_md5"]);
			}
			else if ($aEditais[$i]["status"] == 3)
			{
				//update data de publicacao
				if (isValidBrDate($aEditais[$i]["publish_date"]))
					$data_publicacao = brToMysql($aEditais[$i]["publish_date"]);
				else
					$data_publicacao = '0000-00-00';

				$db->query("UPDATE gelic_licitacoes_edital SET data_publicacao = '".$data_publicacao."' WHERE id = ".$aEditais[$i]["id"]." AND id_licitacao = $pId_licitacao");
			}
			else if ($aEditais[$i]["status"] == 4)
			{
				//remover do banco de dados
				$db->query("DELETE FROM gelic_licitacoes_edital WHERE id = ".$aEditais[$i]["id"]);

				//remover arquivo do S3
				removeFileBucket("vw/edital/".$aEditais[$i]["file_md5"]);
			}
		}


		//====================================================================================
		$db->query("SELECT cod_pregao, cod_uasg, pc_ativo FROM gelic_licitacoes WHERE id = $pId_licitacao");
		$db->nextRecord();
		$dOld_cod_pregao = intval($db->f("cod_pregao"));
		$dOld_cod_uasg = intval($db->f("cod_uasg"));
		$dOld_pc_ativo = intval($db->f("pc_ativo"));

		if ($dOld_cod_pregao > 0 && $dOld_cod_uasg > 0 && ($pCodigo_pregao == 0 || $pCodigo_uasg == 0))
		{
			// VALIDA > INVALIDA

			// Remover registros da tabela "gelic_pc_usuarios"
			$db->query("DELETE FROM gelic_pc_usuarios WHERE id_licitacao = $pId_licitacao");

			// Desabilitar pc local
			$db->query("UPDATE gelic_licitacoes SET pc_ativo = 0 WHERE id = $pId_licitacao");

			// Inserir no historico
			$aHis = array();
			$aHis["alteracao"] = "VALIDA_INVALIDA";
			$aHis["cod_pregao_antigo"] = $dOld_cod_pregao;
			$aHis["cod_uasg_antigo"] = $dOld_cod_uasg;
			$aHis["cod_pregao_atual"] = $pCodigo_pregao;
			$aHis["cod_uasg_atual"] = $pCodigo_uasg;
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 52, 0, 0, '$now', '".json_encode($aHis)."', '', '')");
		}
		else if ($dOld_cod_pregao > 0 && $dOld_cod_uasg > 0 && $pCodigo_pregao > 0 && $pCodigo_uasg > 0 && ($dOld_cod_pregao != $pCodigo_pregao || $dOld_cod_uasg != $pCodigo_uasg))
		{
			// VALIDA > VALIDA DIFERENTE

			if ($dOld_pc_ativo == 1)
			{
				// Tentar ativar monitoramento (via API gerenciador)
				$aVars = array('action'=>'ativar', 'cod_pregao'=>$dCod_pregao, 'cod_uasg'=>$dCod_uasg);
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, "http://".$host."/pcg/?".http_build_query($aVars));
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$html = curl_exec($curl);
				curl_close($curl);
				$result = json_decode($html, true);
				if ($result["ativo"] == 1)
					// Ativar local
					$db->query("UPDATE gelic_licitacoes SET pc_ativo = 1 WHERE id = $pId_licitacao");

				// Inserir no historico como nova ativacao
				$aHis = array();
				$aHis["alteracao"] = "VALIDA_VALIDA_DIFF";
				$aHis["cod_pregao_antigo"] = $dOld_cod_pregao;
				$aHis["cod_uasg_antigo"] = $dOld_cod_uasg;
				$aHis["cod_pregao_atual"] = $pCodigo_pregao;
				$aHis["cod_uasg_atual"] = $pCodigo_uasg;
				$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 51, 0, 0, '$now', '".json_encode($aHis)."', '', '')");
			}
		}
		//====================================================================================


		$db->query("UPDATE gelic_licitacoes SET id_modalidade = $pId_modalidade, instancia = $pInstancia, id_cidade = $pId_cidade, orgao = '$pOrgao', objeto = '$pObjeto', importante = '$pImportante', datahora_abertura = '$pDatahora_abertura', datahora_entrega = '$pDatahora_entrega', valor = '$pValor', link = '$pLink', numero = '$pNumero', srp = $pSrp, meepp = $pMeepp, prazo_entrega_produto = $pPrazo_entrega_produto, prazo_entrega_produto_uteis = $pPrazo_entrega_produto_uteis, fonte = '$pFonte', cod_pregao = $pCodigo_pregao, cod_uasg = $pCodigo_uasg, numero_rastreamento = '$pNumero_rastreamento', validade_proposta = '$pValidade_proposta', vigencia_contrato = '$pVigencia_contrato' WHERE id = $pId_licitacao");


		if ($pData_abertura_int < $pData_hoje_int)
		{
			//Remover notificacoes (remove da fila de envio)
			$db->query("DELETE FROM gelic_mensagens WHERE id_notificacao = 27 AND ((metodo = 1 AND assunto LIKE '%(LIC $pId_licitacao)%') OR (metodo = 2 AND mensagem LIKE '%(LIC $pId_licitacao)%'))");
		}
		else
		{
			ntfNovaLicitacao($pId_licitacao);
		}


		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $pId_licitacao;
	}
} 
echo json_encode($aReturn);

?>
