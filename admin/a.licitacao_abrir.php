<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$xAccess = explode(" ",getAccess());
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

	$gId_licitacao = 0;
	if (isset($_GET["id"])) $gId_licitacao = intval($_GET["id"]);

	$aEstados = array();
	$aEstados["AC"] = "Região 6";
	$aEstados["AL"] = "Região 5";
	$aEstados["AP"] = "Região 6";
	$aEstados["AM"] = "Região 6";
	$aEstados["BA"] = "Região 5";
	$aEstados["CE"] = "Região 5";
	$aEstados["DF"] = "Região 6";
	$aEstados["ES"] = "Região 4";
	$aEstados["GO"] = "Região 6";
	$aEstados["MA"] = "Região 6";
	$aEstados["MT"] = "Região 6";
	$aEstados["MS"] = "Região 6";
	$aEstados["MG"] = "Região 4";
	$aEstados["PA"] = "Região 6";
	$aEstados["PB"] = "Região 5";
	$aEstados["PR"] = "Região 3";
	$aEstados["PE"] = "Região 5";
	$aEstados["PI"] = "Região 5";
	$aEstados["RJ"] = "Região 4";
	$aEstados["RN"] = "Região 5";
	$aEstados["RS"] = "Região 3";
	$aEstados["RO"] = "Região 6";
	$aEstados["RR"] = "Região 6";
	$aEstados["SC"] = "Região 3";
	$aEstados["SP"] = "Região 1/2";
	$aEstados["SE"] = "Região 5";
	$aEstados["TO"] = "Região 6";

	$aInstancia = array();
	$aInstancia[1] = "Municipal";
	$aInstancia[2] = "Estadual";
	$aInstancia[3] = "Federal";


	$now = date("Y-m-d H:i:s"); //data/hora para ser utilizada no banco de dados

	$tLicitacao_info = '<div class="content">
		<div class="content-inside" style="border: 1px solid #bbbbbb;">
			<div class="lic-info-left">
				<span class="lic-info-id">{{LIC_ID}}<a>Nº de Identificação</a></span>
				{{SWITCH}}
			</div>
			<div style="display: block; float: left; margin: 10px 0 40px 140px; line-height: 23px;">
<span class="fl t13 bold" style="width: 220px;">DN(s)</span><span class="fl">{{CLI}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Modalidade</span><span class="fl">{{LIC_MOD}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Nº da Licitação</span><span class="fl">{{LIC_NUMERO}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Código UASG</span><span class="fl">{{LIC_UASG}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Data/Hora de Abertura</span><span class="fl">{{LIC_DHAB}}<span class="gray-88 ml-10">{{LIC_DHAB_D}}</span><a class="ablue ml-10" href="javascript:void(0);" onclick="prorrogarLicitacao(false);">Prorrogar</a></span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Data/Hora de Entrega</span><span class="fl">{{LIC_DHEN}}<span class="gray-88 ml-10">{{LIC_DHEN_D}}</span></span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Prazo Limite</span><span class="fl"><a class="red">{{LIC_DLIM}}</a><span class="prazo ml-10 {{LIC_LCLR}}">{{LIC_DLIM_D}}</span></span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Instância</span><span class="fl">{{LIC_INSTANCIA}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">É SRP?</span><span class="fl">{{LIC_SRP}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Participação ME/EPP?</span><span class="fl">{{LIC_MEEPP}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Prazo de Entrega do Produto</span><span class="fl">{{LIC_PRAZO_ENTREGA_PRODUTO}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Validade da Proposta</span><span class="fl">{{LIC_VALIDADE_PROPOSTA}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Vigência do Contrato</span><span class="fl">{{LIC_VIGENCIA_CONTRATO}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Número de Rastreamento</span><span class="fl">{{LIC_RAST}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Órgão Público</span><span class="fl">{{LIC_ORGAO}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Localização</span><span class="fl">{{CID_NOME}} - {{CID_UF}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Região VW</span><span class="fl">{{LIC_REGIAO}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Link</span><span class="fl">{{LIC_LINK}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Valor Estimado</span><span class="fl">{{LIC_VALOR}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Cadastrado Por</span><span class="fl">{{LIC_ADMIN}}</span>
<span class="fl t13 bold" style="width: 220px; clear:left;">Boletim/Fonte</span><span class="fl">{{LIC_FONTE}}</span>
			</div>

			<a href="javascript:void(0);" onclick="alterarStatus(false);">{{LIC_STATUS}}</a>
			<div style="position: absolute; right: 18px; bottom: 44px;">
				{{LIC_ATA}}
				{{LIC_EDITAL}}
			</div>
			<div style="position:absolute;right:18px;top:18px;text-align:right;overflow:hidden;">
				{{PCRADAR}}
			</div>
		</div>
		<div class="content-inside" style="border: 1px solid #bbbbbb; border-top: 0;">
			<a class="exp-btn0" href="javascript:void(0);" onclick="objetoToggle(this);" style="margin: 0;">Objeto</a>
			<div class="obj-conteudo" style="display: none;">
				<div style="padding: 0 14px; overflow: hidden;">
					<span class="t13 fl lh-30">{{LIC_DATA}}</span>
					<span class="t13 fl lh-30 ml-10 gray-88">{{LIC_HORA}}</span>
					<span class="t13 fr lh-30 gray-88">{{LIC_TEMPO}}</span>
				</div>
				<div class="t13 lh-17" style="padding: 8px 14px 20px 14px; overflow: hidden;">
					{{LIC_OBJETO}}
				</div>
			</div>
			<a class="exp-btn0" href="javascript:void(0);" onclick="importanteToggle(this);">Importante</a>
			<div class="imp-conteudo t13" style="display: none;">
				{{LIC_IMPORTANTE}}
			</div>
			{{PCDROP}}
			<a id="a-itens" class="exp-btn0" href="javascript:void(0);" onclick="itensToggle(this);">Itens (<span class="total-itens">...</span>)</a>
			<div class="itens-conteudo t13" style="display: none;">
				<div id="itens-abas" class="row-100 pb-14"></div>
				<div id="itens-info" class="row-100 pb-40 pt-20" style="display: none;"></div>
			</div>
			<a id="a-si" class="exp-btn0" href="javascript:void(0);" onclick="semInteresseToggle(this);">Sem Interesse (...)</a>
			<div class="si-conteudo t13" style="display: none; padding: 10px 14px;">
			</div>
		</div>
		<div class="content-inside mt-10">
			<div style="float:left;">
				<a class="itm-cb{{ED}}d fl" href="javascript:void(0);">Edital Indisponível</a>
				<a class="itm-cb{{AT}}d fl ml-60" href="javascript:void(0);">Aguardando ATA</a>
				<a class="itm-cb{{DI}} fl ml-60" href="javascript:void(0);" onclick="atualizarCheckLic(this);" data-field="documentacao_irregular"><img src="img/loader20.gif" style="display: none;">Documentação VW - Irregular</a>
				<a class="itm-cb{{SC}} fl ml-60" href="javascript:void(0);" onclick="atualizarCheckLic(this);" data-field="sem_cadastro_no_portal"><img src="img/loader20.gif" style="display: none;">Sem cadastro no portal</a>
			</div>
			{{LIC_ENCERRAR}}
		</div>
	</div>';



	$tMensagem_box = '<div class="content pt-40">
		<div class="content-inside mb-8 w-800">
			<img class="fl" src="img/mail.png">
			<span class="t18 gray-33 fl ml-10 mt-10 lh-26">Enviar Mensagem/Arquivo</span>
		</div>
		<div class="content-inside w-800">
			<textarea id="i-mensagem" name="msg" placeholder="- digite aqui a mensagem -"></textarea>
		</div>
		<div class="content-inside w-800" style="border: 1px solid #cccccc; border-top: 0; height: 37px;">
			<div id="upl-btn" style="display: block;">
				<span class="t11 red" style="position: absolute; right: 14px; top: 0; line-height: 37px;">Máx. 100 MB</span>
				<img src="img/pc-top.png" style="position: absolute; left: 12px; top: 1px;">
				<a class="upl-btn" href="javascript:void(0);" onclick="selectFileMSG();"><img src="img/pc-bot.png">Anexar Arquivo</a>
			</div>
			<div id="upl-loading" style="display: none;">
				<div id="upl-bar"></div>
				<span id="upl-per">Carregando...100%</span>
			</div>
			<div id="upl-ready" style="display: none;">
				<img src="img/file.png" style="position: absolute; left: 6px; top: 6px; border: 0;">
				<span id="upl-filename" class="t13 red italic" style="position: absolute; left: 40px; top: 6px; line-height: 24px;">---</span>
				<span id="upl-filesize" class="gray-4c italic t11" style="position: absolute; right: 40px; top: 6px; line-height: 24px;">0 bytes</span>
				<a class="btn-x24" href="javascript:void(0);" onclick="cancelUploadMSG();" style="right: 6px; top: 6px;" title="Cancelar"></a>
			</div>
		</div>
		<div id="enviar-box" class="content-inside mt-10 w-800">
			<a class="bt-style-1" href="javascript:void(0);" onclick="enviarMensagem();">Enviar</a>
		</div>
		<div id="processando-box" class="content-inside mt-10 w-800" style="display: none;">
			<div class="processando">Enviando...</div>
		</div>
		<div style="position: absolute; left: 0; top: 15px; width: 100%; height: 1px; border-top: 1px solid #cccccc;"></div>
	</div>';


	$aSimnao = array('<a class="gray-aa">- não informado -</a>','Sim','Não');
	$aPrazoentregaproduto = array('<a class="gray-aa">- não informado -</a>','dias úteis','dias corridos');


	$oOutput = "";
	$db = new Mysql();

	$db->query("
SELECT
	lic.id,
	lic.instancia,
	lic.orgao,
	lic.objeto,
	lic.importante,
	lic.datahora_abertura,
	lic.datahora_entrega,
	lic.datahora_limite,
	DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
	DATEDIFF(DATE(lic.datahora_entrega), CURRENT_DATE()) AS data_entrega_dias,
	UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
	lic.fase,
	lic.aprovar_apl,
	lic.valor,
	lic.link,
	lic.numero,
	lic.cod_pregao,
	lic.cod_uasg,
	lic.pc_ativo,
	lic.data_hora,
	lic.srp,
	lic.meepp,
	lic.prazo_entrega_produto,
	lic.prazo_entrega_produto_uteis,
	lic.fonte,
	lic.numero_rastreamento,
	lic.validade_proposta,
	lic.vigencia_contrato,
	lic.documentacao_irregular,
	lic.sem_cadastro_no_portal,
	mdl.nome AS nome_modalidade,
	cid.nome AS nome_cidade,
	cid.uf AS uf,
	if(labas.id_status is not null, labas.id_status, 1) as id_status,
	(SELECT id FROM gelic_licitacoes_apl WHERE id_licitacao = lic.id LIMIT 1) AS tem_apl,
	(SELECT tipo FROM gelic_historico WHERE id_licitacao = lic.id ORDER BY id DESC LIMIT 1) AS ultimo_tipo
FROM
	gelic_licitacoes AS lic,
	gelic_licitacoes_abas AS labas,
	gelic_modalidades AS mdl,
	gelic_cidades AS cid
WHERE
	lic.id = $gId_licitacao AND
	lic.id = labas.id_licitacao AND
	labas.grupo = 1 AND
	lic.deletado = 0 AND
	mdl.id = lic.id_modalidade AND
	cid.id = lic.id_cidade
LIMIT 1");

	if ($db->nextRecord())
	{
		$encerrada = false;
		if ($db->f("ultimo_tipo") == 31) //ENCERRADA
			$encerrada = true;

		$dLimite = segundosConv($db->f("limite"));

		$tTmp = $tLicitacao_info;


		if (in_array($db->f("id_status"), array(8,19))) // APL Aprovada, APL Reprovada
		{
			$db->query("SELECT SUM(enviadas) AS enviadas, SUM(aprovadas) AS aprovadas, SUM(reprovadas) AS reprovadas FROM gelic_licitacoes_apl_ear WHERE id_licitacao = ".$db->f("id"),1);
			$db->nextRecord(1);

			$status = '<span class="lic-info-status" style="background-color:#dedede;font-size:13px;">';

			if ($db->f("fase") == 1)
			{
				if ($db->f("enviadas",1) > 0)
					$status .= '<span style="background-color:#827c7c;color:#ffffff;line-height:30px;display:inline-block;padding:0 12px;">APL em Análise - GELIC ('.$db->f("enviadas",1).')</span>';
			}
			else
			{
				if ($db->f("enviadas",1) > 0)
					$status .= '<span style="color:#050000;background-color:#ffe600;line-height:30px;display:inline-block;padding:0 12px;">APL Aguardando Aprovação ('.$db->f("enviadas",1).')</span>';
			}

			if ($db->f("aprovadas",1) > 0)
				$status .= '<span style="background-color:#00b318;color:#ffffff;line-height:30px;display:inline-block;padding:0 12px;">APL Aprovada ('.$db->f("aprovadas",1).')</span>';

			if ($db->f("reprovadas",1) > 0)
				$status .= '<span style="background-color:#ed0000;color:#ffffff;line-height:30px;display:inline-block;padding:0 12px;">APL Reprovada ('.$db->f("reprovadas",1).')</span>';

			$status .= '</span>';
		}
		else
		{
			$db->query("SELECT descricao, cor_texto, cor_fundo FROM gelic_status WHERE id = ".$db->f("id_status"),1);
			$db->nextRecord(1);
			$status = '<span class="lic-info-status" style="color: #'.$db->f("cor_texto",1).'; background-color: #'.$db->f("cor_fundo",1).';">'.utf8_encode($db->f("descricao",1)).'</span>';
		}

		$dab_h = substr($db->f("datahora_abertura"),11,5);
		$den_h = substr($db->f("datahora_entrega"),11,5);
		if ($dab_h == "00:00") $dab_h = "--:--";
		if ($den_h == "00:00") $den_h = "--:--";

		$tTmp = str_replace("{{LIC_STATUS}}", $status, $tTmp);
		$tTmp = str_replace("{{LIC_ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{LIC_NUMERO}}", $db->f("numero"), $tTmp);
		$tTmp = str_replace("{{LIC_DHAB}}", mysqlToBr(substr($db->f("datahora_abertura"),0,10)).' '.$dab_h, $tTmp);
		$tTmp = str_replace("{{LIC_DHEN}}", mysqlToBr(substr($db->f("datahora_entrega"),0,10)).' '.$den_h, $tTmp);
		$tTmp = str_replace("{{LIC_DLIM}}", mysqlToBr(substr($db->f("datahora_limite"),0,10)).' '.substr($db->f("datahora_limite"),11,5), $tTmp);
		$tTmp = str_replace("{{LIC_DHAB_D}}", niceDays($db->f("data_abertura_dias")), $tTmp);
		$tTmp = str_replace("{{LIC_DHEN_D}}", niceDays($db->f("data_entrega_dias")), $tTmp);
		$tTmp = str_replace("{{LIC_DLIM_D}}", $dLimite["h"].'h '.$dLimite["m"].'m', $tTmp);

		if (!$encerrada)
		{
			if ($db->f("data_abertura_dias") < 0)
				$tTmp = str_replace("{{LIC_LCLR}}", "prazo-black", $tTmp);
			else
			{
				if ($dLimite["h"] >= 2)
					$tTmp = str_replace("{{LIC_LCLR}}", "prazo-green", $tTmp); //green
				else if ($dLimite["h"] > 0 && $dLimite["h"] < 2)
					$tTmp = str_replace("{{LIC_LCLR}}", "prazo-bright-red", $tTmp); //bright red
				else if ($dLimite["h"] <= 0)
					$tTmp = str_replace("{{LIC_LCLR}}", "prazo-dark-red", $tTmp); //red
			}
		}
		else
			$tTmp = str_replace("{{LIC_LCLR}}", "prazo-gray", $tTmp);
		
		$tTmp = str_replace("{{LIC_MOD}}", utf8_encode($db->f("nome_modalidade")), $tTmp);
		$tTmp = str_replace("{{LIC_ORGAO}}", utf8_encode(stripslashes($db->f("orgao"))), $tTmp);
		$tTmp = str_replace("{{CID_NOME}}", utf8_encode($db->f("nome_cidade")), $tTmp);
		$tTmp = str_replace("{{CID_UF}}", $db->f("uf"), $tTmp);
		$tTmp = str_replace("{{LIC_REGIAO}}", $aEstados[$db->f("uf")], $tTmp);

		if ($db->f("cod_uasg") > 0)
			$tTmp = str_replace("{{LIC_UASG}}", $db->f("cod_uasg"), $tTmp);
		else
			$tTmp = str_replace("{{LIC_UASG}}", '<a class="gray-aa">- não informado -</a>', $tTmp);

		if ($db->f("instancia") > 0)
			$tTmp = str_replace("{{LIC_INSTANCIA}}", $aInstancia[$db->f("instancia")], $tTmp);
		else
			$tTmp = str_replace("{{LIC_INSTANCIA}}", '<a class="gray-aa">- não informado -</a>', $tTmp);

		if (strlen($db->f("link")) > 0)
			$tTmp = str_replace("{{LIC_LINK}}", '<a class="ablue" href="'.$db->f("link").'" target="_blank">'.$db->f("link").'</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_LINK}}", '<a class="gray-aa">- não informado -</a>', $tTmp);

		if ($db->f("valor") == 0.00)
			$tTmp = str_replace("{{LIC_VALOR}}", '<a class="gray-aa">- não informado -</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_VALOR}}", "R$ ".number_format($db->f("valor"),2,',','.'), $tTmp);

		$tTmp = str_replace("{{LIC_DATA}}", mysqlToBr(substr($db->f("data_hora"),0,10)), $tTmp);
		$tTmp = str_replace("{{LIC_HORA}}", substr($db->f("data_hora"),11), $tTmp);
		$tTmp = str_replace("{{LIC_TEMPO}}", timeAgo($db->f("data_hora")), $tTmp);
		$tTmp = str_replace("{{LIC_OBJETO}}", utf8_encode(stripslashes($db->f("objeto"))), $tTmp);
		$tTmp = str_replace("{{LIC_IMPORTANTE}}", utf8_encode(stripslashes($db->f("importante"))), $tTmp);
		$tTmp = str_replace("{{LIC_SRP}}", $aSimnao[$db->f("srp")], $tTmp);
		$tTmp = str_replace("{{LIC_MEEPP}}", $aSimnao[$db->f("meepp")], $tTmp);

		$tTmp = str_replace("{{LIC_PRAZO_ENTREGA_PRODUTO}}", $db->f("prazo_entrega_produto")." ".$aPrazoentregaproduto[$db->f("prazo_entrega_produto_uteis")], $tTmp);

		if (strlen($db->f("fonte")) > 0)
			$tTmp = str_replace("{{LIC_FONTE}}", utf8_encode($db->f("fonte")), $tTmp);
		else
			$tTmp = str_replace("{{LIC_FONTE}}", '<a class="gray-aa">- não informado -</a>', $tTmp);

		if (strlen($db->f("numero_rastreamento")) > 0)
			$tTmp = str_replace("{{LIC_RAST}}", '<a class="ablue" href="a.rastreamento.php?n='.$db->f("numero_rastreamento").'" target="_blank">'.$db->f("numero_rastreamento").'</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_RAST}}", '<a class="gray-aa">- não informado -</a>', $tTmp);

		if ($db->f("validade_proposta") == '')
			$tTmp = str_replace("{{LIC_VALIDADE_PROPOSTA}}", '<a class="gray-aa">- não informado -</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_VALIDADE_PROPOSTA}}", utf8_encode($db->f("validade_proposta")), $tTmp);

		if ($db->f("vigencia_contrato") == '')
			$tTmp = str_replace("{{LIC_VIGENCIA_CONTRATO}}", '<a class="gray-aa">- não informado -</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_VIGENCIA_CONTRATO}}", utf8_encode($db->f("vigencia_contrato")), $tTmp);


		//------------
		if ($db->f("cod_pregao") == 0 || $db->f("cod_uasg") == 0)
		{
			$tTmp = str_replace("{{PCRADAR}}", "", $tTmp);
			$tTmp = str_replace("{{PCDROP}}", "", $tTmp);
		}
		else
		{
			// Verificar se o monitoramento esta ativo
			if ($db->f("pc_ativo") == 1)
				$tTmp = str_replace("{{PCRADAR}}", '<a id="radar" class="radar-on" href="javascript:void(0);" onclick="pregoeiroChama();" title="Pregoeiro Chama"><img src="img/radar-on.gif"></a>
<div style="margin-top:10px;overflow:hidden;">
	<span style="width:60px;float:right;line-height:17px;text-align:right;box-sizing:border-box;">'.$db->f("cod_uasg").'</span>
	<span style="float:right;font-weight:bold;line-height:17px;">Cód. UASG:</span>
	<span style="clear:both;width:60px;float:right;line-height:17px;text-align:right;box-sizing:border-box;">'.$db->f("cod_pregao").'</span>
	<span style="float:right;font-weight:bold;line-height:17px;">Nº do Pregão:</span>
</div>', $tTmp);
			else
				$tTmp = str_replace("{{PCRADAR}}", '<a id="radar" class="radar-off" href="javascript:void(0);" onclick="pregoeiroChama();" title="Pregoeiro Chama"><img src="img/radar-off.png"></a>
<div style="margin-top:10px;overflow:hidden;">
	<span style="width:60px;float:right;line-height:17px;text-align:right;box-sizing:border-box;">'.$db->f("cod_uasg").'</span>
	<span style="float:right;font-weight:bold;line-height:17px;">Cód. UASG:</span>
	<span style="clear:both;width:60px;float:right;line-height:17px;text-align:right;box-sizing:border-box;">'.$db->f("cod_pregao").'</span>
	<span style="float:right;font-weight:bold;line-height:17px;">Nº do Pregão:</span>
</div>', $tTmp);

			$tTmp = str_replace("{{PCDROP}}", '<a class="exp-btn0" href="javascript:void(0);" onclick="pcToggle(this);">Pregoeiro Chama (<span id="pc-count">...</span>)</a>
			<div id="pc-conteudo" class="pc-conteudo t13" style="display:none;padding:10px 14px;background-color:#f5f5f5;"></div>', $tTmp);
		}
		//------------



		if ($db->f("tem_apl") == '' || !in_array("lic_check", $xAccess))
		{
			$tTmp = str_replace("{{SWITCH}}", "", $tTmp);
		}
		else
		{
			if ($db->f("aprovar_apl") == 0)
				$tTmp = str_replace("{{SWITCH}}", '<div style="position:absolute;left:0;bottom:0;">
						<span style="display:inline-block;width:120px;text-align:center;line-height:23px;background-color:#ffffff;">Liberar APLs</span>
						<a class="switch s-off" href="javascript:void(0);" onclick="SwitchProcess(this);" style="margin: 6px 0 30px 30px;"><span class="ridge"></span><span class="disc" style="left: 2px;"></span><img class="img" src="img/loader_32.gif"></a>
					</div>', $tTmp);
			else
				$tTmp = str_replace("{{SWITCH}}", '<div style="position:absolute;left:0;bottom:0;">
						<span style="display:inline-block;width:120px;text-align:center;line-height:23px;background-color:#ffffff;">Liberar APLs</span>
						<a class="switch s-on" href="javascript:void(0);" onclick="SwitchProcess(this);" style="margin: 6px 0 30px 30px;"><span class="ridge"></span><span class="disc" style="left: 32px;"></span><img class="img" src="img/loader_32.gif"></a>
					</div>', $tTmp);
		}



		//quem cadastrou
		$db->query("SELECT usr.nome FROM gelic_historico AS his INNER JOIN gelic_admin_usuarios AS usr ON usr.id = his.id_sender WHERE his.id_licitacao = ".$db->f("id")." AND tipo = 11",1);
		$db->nextRecord(1);
		$tTmp = str_replace("{{LIC_ADMIN}}", utf8_encode($db->f("nome",1)), $tTmp);



		if ($db->f("fase") == 1)
		{
			$DNs = '';
			$db->query("SELECT nome FROM gelic_clientes WHERE id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = ".$db->f("id").") ORDER BY dn",1);
			while ($db->nextRecord(1))
				$DNs .= '<br>'.utf8_encode($db->f("nome",1));

			$tTmp = str_replace("{{CLI}}", substr($DNs, 4), $tTmp);
		}
		else if ($db->f("fase") == 2)
		{
			$tTmp = str_replace("{{CLI}}", $aEstados[$db->f("uf")], $tTmp);
		}
		else
		{
			$tTmp = str_replace("{{CLI}}", "Brasil", $tTmp);
		}

		
		//EDITAL
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_edital WHERE id_licitacao = ".$db->f("id"),1);
		$db->nextRecord(1);
		$dEditais = $db->f("total",1);
		if ($dEditais > 0)
		{
			$tTmp = str_replace("{{LIC_EDITAL}}", '<a id="drop-editais" class="deditais drp" href="javascript:void(0);" onclick="dropEditais();">EDITAIS ('.$dEditais.')<img src="img/a-down-g.png"></a>', $tTmp);
			$tTmp = str_replace("{{ED}}", "0", $tTmp);
		}
		else
		{
			$tTmp = str_replace("{{LIC_EDITAL}}", '', $tTmp);
			$tTmp = str_replace("{{ED}}", "1", $tTmp);
		}


		if ($db->f("id_status") == 32)
			$tTmp = str_replace("{{AT}}", "1", $tTmp);
		else
			$tTmp = str_replace("{{AT}}", "0", $tTmp);

		if ($db->f("documentacao_irregular") > 0)
			$tTmp = str_replace("{{DI}}", "1", $tTmp);
		else
			$tTmp = str_replace("{{DI}}", "0", $tTmp);

		if ($db->f("sem_cadastro_no_portal") > 0)
			$tTmp = str_replace("{{SC}}", "1", $tTmp);
		else
			$tTmp = str_replace("{{SC}}", "0", $tTmp);

		//ATA
		$db->query("SELECT nome_arquivo, arquivo FROM gelic_licitacoes_ata WHERE id_licitacao = ".$db->f("id"),1);
		if ($db->nextRecord(1))
			$tTmp = str_replace("{{LIC_ATA}}", '<a class="deditais" href="'.linkFileBucket("vw/licata/".$db->f("arquivo",1)).'" target="_blank" title="'.utf8_encode($db->f("nome_arquivo",1)).'" style="padding:0 10px;margin:0;">ATA</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_ATA}}", '', $tTmp);


		if (!$encerrada && in_array("lic_encerrar", $xAccess))
			$tTmp = str_replace("{{LIC_ENCERRAR}}", '<a class="bt-style-3 clear fl mt-10" href="javascript:void(0);" onclick="encerrarLicitacao(false);">Encerrar Licitação</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_ENCERRAR}}", '', $tTmp);


		$oOutput .= $tTmp;

		$oOutput .= '<div id="historico" class="content pt-20"></div>';

		if (!$encerrada && in_array("lic_mensagem", $xAccess)) //PERMITIR INTERACAO
			$oOutput .= $tMensagem_box;
	}
	else
	{
		header("location: a.licitacao.php");
		exit;
	}

	$dSrch_id = "";
	$dFiltro_dn = '[]';
	$dFiltro_status = '[]';
	$dFiltro_estados = '[]';
	$dFiltro_cidades = '[]';
	$dFiltro_regioes = '[]';
	$dFiltro_ultimas = '[]';
	$dFiltro_orgao = '';
	$dFiltro_orgao_color = '282828';
	$dFiltro_orgao_border = 'cccccc';
	$dFiltro_adve = '';
	$dFiltro_adve_color = '282828';
	$dFiltro_adve_border = 'cccccc';
	$dFiltro_numero = '';
	$dFiltro_numero_color = '282828';
	$dFiltro_numero_border = 'cccccc';
	$dFiltro_data_de = '';
	$dFiltro_data_de_color = '282828';
	$dFiltro_data_de_border = 'cccccc';
	$dFiltro_data_ate = '';
	$dFiltro_data_ate_color = '282828';
	$dFiltro_data_ate_border = 'cccccc';

	$db->query("SELECT valor FROM gelic_admin_usuarios_config WHERE id_admin_usuario = $sInside_id AND config = 'search'");
	if ($db->nextRecord())
	{
		$json_string = $db->f("valor");
		$a = json_decode(utf8_encode($json_string), true);
		$dSrch = $a["search"];
		$dSrch_id = $a["search_1"];
		if ($dSrch_id == 0) $dSrch_id = "";

	
		$dFiltro_orgao = $a["search_2"]["orgao"];
		$dFiltro_orgao = preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $dFiltro_orgao);
		if ($dFiltro_orgao <> '')
		{
			$dFiltro_orgao_color = 'ff0000';
			$dFiltro_orgao_border = 'ee0000';
		}

		if (strlen($a["search_2"]["dn"]) > 0)
		{
			$add_to_status = '';
			$db->query("SELECT id, nome FROM gelic_clientes WHERE id IN (".$a["search_2"]["dn"].")");
			while ($db->nextRecord())
				$add_to_status .= ',{v:'.$db->f("id").',lb:"'.utf8_encode($db->f("nome")).'",uf:""}';

			if (strlen($add_to_status) > 0)
				$dFiltro_dn = '['.substr($add_to_status, 1).']';
		}
		
		if (strlen($a["search_2"]["status"]) > 0)
		{
			$add_to_status = '';
			$db->query("SELECT id, descricao FROM gelic_status WHERE id IN (".$a["search_2"]["status"].")");
			while ($db->nextRecord())
				$add_to_status .= ',{v:'.$db->f("id").',lb:"'.utf8_encode($db->f("descricao")).'",uf:""}';

			if (strlen($add_to_status) > 0)
				$dFiltro_status = '['.substr($add_to_status, 1).']';
		}

		if (strlen($a["search_2"]["estados"]) > 0)
		{
			$add_to_estados = '';
			$e = array();
			$e = explode(",", $a["search_2"]["estados"]);
			function singleQuotes($s) { return "'".$s."'"; }
			$e = array_map("singleQuotes", $e);

			$db->query("SELECT uf, estado FROM gelic_uf WHERE uf IN (".implode(",",$e).")");
			while ($db->nextRecord())
				$add_to_estados .= ',{v:"'.$db->f("uf").'",lb:"'.utf8_encode($db->f("estado")).'",uf:""}';

			if (strlen($add_to_estados) > 0)
				$dFiltro_estados = '['.substr($add_to_estados, 1).']';
		}

		if (strlen($a["search_2"]["cidades"]) > 0)
		{
			$add_to_cidades = '';
			$db->query("SELECT id, nome, uf FROM gelic_cidades WHERE id IN (".$a["search_2"]["cidades"].")");
			while ($db->nextRecord())
				$add_to_cidades .= ',{v:'.$db->f("id").',lb:"'.utf8_encode($db->f("nome")).'",uf:"'.$db->f("uf").'"}';

			if (strlen($add_to_cidades) > 0)
				$dFiltro_cidades = '['.substr($add_to_cidades, 1).']';
		}

		if (strlen($a["search_2"]["regioes"]) > 0)
		{
			$add_to_regioes = '';

			$aReg = array();
			$aReg[1] = "Região 1/2";
			$aReg[3] = "Região 3";
			$aReg[4] = "Região 4";
			$aReg[5] = "Região 5";
			$aReg[6] = "Região 6";

			$r = explode(",", $a["search_2"]["regioes"]);

			foreach ($aReg as $key => $value)
			{
				if (in_array($key, $r))
					$add_to_regioes .= ',{v:'.$key.',lb:"'.$value.'",uf:""}';
			}

			if (strlen($add_to_regioes) > 0)
				$dFiltro_regioes = '['.substr($add_to_regioes, 1).']';
		}

		if ($a["search_2"]["ultimas"] > 0)
		{
			$aUlt = array();
			$aUlt[1] = "Últimas 3";
			$aUlt[2] = "Últimas 10";
			$aUlt[3] = "Últimas 25";
			$aUlt[4] = "Últimas 50";
			$aUlt[5] = "Últimas 100";
			$aUlt[6] = "Últimas 250";
			$aUlt[7] = "Últimas 500";
			$aUlt[8] = "Últimas 1000";

			$dFiltro_ultimas = '[{v:'.$a["search_2"]["ultimas"].',lb:"'.$aUlt[$a["search_2"]["ultimas"]].'",uf:""}]';
		}

		$dFiltro_adve = $a["search_2"]["adve"];
		if ($dFiltro_adve > 0)
		{
			$dFiltro_adve_color = 'ff0000';
			$dFiltro_adve_border = 'ee0000';
		}
		else
			$dFiltro_adve = '';

		$dFiltro_numero = $a["search_2"]["numero"];
		$dFiltro_numero = preg_replace('/u([\da-fA-F]{4})/', '&#x\1;', $dFiltro_numero);
		if ($dFiltro_numero <> '')
		{
			$dFiltro_numero_color = 'ff0000';
			$dFiltro_numero_border = 'ee0000';
		}

		$dFiltro_data_de = $a["search_2"]["data_de"];
		if ($dFiltro_data_de <> '' && isValidBrDate($dFiltro_data_de))
		{
			$dFiltro_data_de_color = 'ff0000';
			$dFiltro_data_de_border = 'ee0000';
		}
		else
			$dFiltro_data_de = '';


		$dFiltro_data_ate = $a["search_2"]["data_ate"];
		if ($dFiltro_data_ate <> '' && isValidBrDate($dFiltro_data_ate))
		{
			$dFiltro_data_ate_color = 'ff0000';
			$dFiltro_data_ate_border = 'ee0000';
		}
		else
			$dFiltro_data_ate = '';
	}

	$tPage = new Template("a.licitacao_abrir.html");
	$tPage->replace("{{SEC_TOP}}", utf8_encode(getSec_top()));
	$tPage->replace("{{ID}}", $gId_licitacao);
	$tPage->replace("{{SRCH_ID}}", $dSrch_id);
	$tPage->replace("{{FILTRO_DN}}", $dFiltro_dn);
	$tPage->replace("{{FILTRO_STATUS}}", $dFiltro_status);
	$tPage->replace("{{FILTRO_ESTADOS}}", $dFiltro_estados);
	$tPage->replace("{{FILTRO_CIDADES}}", $dFiltro_cidades);
	$tPage->replace("{{FILTRO_REGIOES}}", $dFiltro_regioes);
	$tPage->replace("{{FILTRO_ULTIMAS}}", $dFiltro_ultimas);
	$tPage->replace("{{FILTRO_OP}}", $dFiltro_orgao);
	$tPage->replace("{{FILTRO_OP_COLOR}}", $dFiltro_orgao_color);
	$tPage->replace("{{FILTRO_OP_BORDER}}", $dFiltro_orgao_border);
	$tPage->replace("{{FILTRO_ADVE}}", $dFiltro_adve);
	$tPage->replace("{{FILTRO_ADVE_COLOR}}", $dFiltro_adve_color);
	$tPage->replace("{{FILTRO_ADVE_BORDER}}", $dFiltro_adve_border);
	$tPage->replace("{{FILTRO_NUMERO}}", $dFiltro_numero);
	$tPage->replace("{{FILTRO_NUMERO_COLOR}}", $dFiltro_numero_color);
	$tPage->replace("{{FILTRO_NUMERO_BORDER}}", $dFiltro_numero_border);
	$tPage->replace("{{FILTRO_DATA_DE}}", $dFiltro_data_de);
	$tPage->replace("{{FILTRO_DATA_DE_COLOR}}", $dFiltro_data_de_color);
	$tPage->replace("{{FILTRO_DATA_DE_BORDER}}", $dFiltro_data_de_border);
	$tPage->replace("{{FILTRO_DATA_ATE}}", $dFiltro_data_ate);
	$tPage->replace("{{FILTRO_DATA_ATE_COLOR}}", $dFiltro_data_ate_color);
	$tPage->replace("{{FILTRO_DATA_ATE_BORDER}}", $dFiltro_data_ate_border);
	$tPage->replace("{{OUTPUT}}", $oOutput);
	$tPage->replace("{{VERSION}}", VERSION);
	
	echo $tPage->body;
} 
else 
{
	$tPage = new Template("a.login.html");
	echo $tPage->body;
}

?>
