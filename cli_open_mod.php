<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside() || isset($_GET['dev']) )
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	//--- Ajustar inside_id se for Representante ---
	if ($sInside_tipo == 4) //REP
		$sInside_id = $_SESSION[SESSION_ID_DN];
	//----------------------------------------------

	$xAccess = explode(" ",getAccess());

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

	$db = new Mysql();

	$tLicitacao_info = '<div class="onecont">
		<ul class="onecont_left">
			<li><div>DN(s)</div><div>{{CLI}}</div></li>
			<li><div>Nº da Licitação</div><div>{{LIC_NUMERO}}</div></li>
			<li><div>Data/Hora de Abertura</div><div>{{LIC_DHAB}} ({{LIC_DHAB_D}})</div></li>
			<li><div>Data/Hora de Entrega</div><div>{{LIC_DHEN}} ({{LIC_DHEN_D}})</div></li>
			<li><div>Modalidade</div><div>{{LIC_MOD}}</div></li>
			<li><div>Instância</div><div>{{LIC_INSTANCIA}}</div></li>
			<li><div>É SRP?</div><div>{{LIC_SRP}}</div></li>
			<li><div>Participação ME/EPP?</div><div>{{LIC_MEEPP}}</div></li>
			<li><div>Número de Rastreamento</div><div>{{LIC_RAST}}</div></li>
			<li><div>Validade da Proposta</div><div>{{LIC_VALP}}</div></li>
			<li><div>Vigência do Contrato</div><div>{{LIC_VIGC}}</div></li>
			<li><div>Prazo de Entrega do Veículo</div><div>{{LIC_PRAZO_ENTREGA_PRODUTO}}</div></li>
			<li><div>Órgão Público</div><div>{{LIC_ORGAO}}</div></li>
			<li><div>Localização</div><div>{{CID_NOME}} - {{CID_UF}}</div></li>
			<li><div>Região VW</div><div>{{LIC_REG}}</div></li>
			<li><div>Link</div><div class="none">{{LIC_LINK}}</div></li>
			<li><div>Valor Estimado</div><div>{{LIC_VALOR}}</div></li>
			<li><div>Status</div><div id="d-status">...</div></li>
		</ul>
		<div class="numid">
			<span class="one">Número de Identificação</span>
			<span class="two">{{LIC_ID}}</span>
		</div>
		<div style="position: absolute; right: 10px; bottom: 10px;">
			{{LIC_ATA}}
			{{LIC_EDITAL}}
		</div>
	</div>
	<div class="twocont" style="border-top: 0;">
		<a class="exp-btn0" href="javascript:void(0);" onclick="objetoToggle(this);" style="margin: 0;">Objeto</a>
		<div class="obj-conteudo" style="display: none; padding: 10px;">
			<div style="overflow: hidden;">
				<span class="t12 fl">{{LIC_DATA}}</span>
				<span class="t12 fl ml-10 gray-88">{{LIC_HORA}}</span>
				<span class="t12 fr gray-88">{{LIC_TEMPO}}</span>
			</div>
			<div class="t13 lh-17" style="padding: 10px 0 0 0; overflow: hidden;">
				{{LIC_OBJETO}}
			</div>
		</div>
		<a class="exp-btnY0" href="javascript:void(0);" onclick="importanteToggle(this);">Importante</a>
		<div class="imp-conteudo" style="display: none; background-color: #fae5e9; padding: 10px;">
			{{LIC_IMPORTANTE}}
		</div>
		<a id="a-itens" class="exp-btn0" href="javascript:void(0);" onclick="itensToggle(this);">Itens (<span class="total-itens">...</span>)</a>
		<div class="itens-conteudo" style="display: none; background-color: #ffffff;">
			<div id="itens-abas" class="row-100 pb-14"></div>
			<div id="itens-info" class="row-100 pb-40 pt-20" style="display: none;"></div>
		</div>
	</div>
	{{LIC_PARTICIPAR}}
	<div id="historico" style="margin-top: 20px; overflow: hidden;"></div>';


	$tMessage_box = '<div class="send">
				<form id="upload-form" enctype="multipart/form-data"></form>
				<h4 class="send_tit">Enviar Mensagem/Arquivo</h4>
				<div class="send_box">
					<textarea id="i-mensagem" placeholder="Digite aqui a mensagem..." style="resize: none;"></textarea>
					<div id="upl-btn" class="send_box_bottom">
						<a class="bt-style-2 fl" href="javascript:void(0);" onclick="selectFileMSG();" style="height:25px;line-height:25px;margin:5px;">Anexar Arquivo</a>
						<span>Tamanho máximo (100 MB)</span>
					</div>
					<div id="upl-loading" class="send_box_bottom" style="display: none; position: relative; height: 35px;">
						<div id="upl-bar" style="position: absolute; left: 10px; top: 6px; height: 25px; background-color: #deddcc; width: 918px;"></div>
						<span id="upl-per" style="left: 20px;position: absolute;">Carregando... Aguarde...</span>					
  					</div>
					<div id="upl-ready" class="send_box_bottom" style="display: none;">
						<span style="margin-left: 10px;">Arquivo Anexo:</span> <span id="upl-filename" style="display: inline; color: #ff0000; margin-left:8px;"></span> <span id="upl-filesize" style="margin-left:8px;">(0 bytes)</span>
						<a class="bt-style-2 fr" href="javascript:void(0);" onclick="cancelUploadMSG();" style="height:25px;line-height:25px;margin:5px;">Cancelar</a>
					</div>
				</div>
				<div id="enviar-box" style="overflow:hidden;">
					<a class="bt-style-1 fl" href="javascript:void(0);" onclick="enviarMensagem();" style="height:40px;line-height:40px;">Enviar</a>
				</div>
				<div id="processando-box" style="display: none;">
					<div class="processando">Enviando...</div>
				</div>
			</div>';

	$aSimnao = array('<span class="gray-aa">- não informado -</span>','Sim','Não');
	$aPrazoentregaproduto = array('<span class="gray-aa">- não informado -</span>','dias úteis','dias corridos');
	$data_hoje = date("Y-m-d");

	if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
	{
		if ($sInside_tipo == 2)
			$cliente_parent = $sInside_id;
		else if ($sInside_tipo == 3)
			$cliente_parent = $sInside_parent;
		else if ($sInside_tipo == 4)
			$cliente_parent = $_SESSION[SESSION_ID_DN];

		$select = "lic.id,
			lic.instancia,
			lic.orgao,
			lic.objeto,
			lic.importante,
			lic.datahora_abertura,
			lic.datahora_entrega,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			DATEDIFF(DATE(lic.datahora_entrega), CURRENT_DATE()) AS data_entrega_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			lic.link,
			lic.numero,
			lic.data_hora,
			lic.srp,
			lic.meepp,
			lic.prazo_entrega_produto,
			lic.prazo_entrega_produto_uteis,
			lic.numero_rastreamento,
			lic.validade_proposta,
			lic.vigencia_contrato,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf AS uf,
			labas.id_status,
			hdec.id AS declinou";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id
			INNER JOIN gelic_licitacoes_clientes AS licc ON licc.id_licitacao = lic.id AND licc.id_cliente = $cliente_parent
			INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
			INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
			LEFT JOIN gelic_licitacoes_apl AS apl ON apl.id_licitacao = lic.id AND (apl.id_cliente = $cliente_parent OR apl.id_cliente IN (SELECT id FROM gelic_clientes WHERE id_parent = $cliente_parent))
			LEFT JOIN gelic_historico AS hdec ON hdec.id_licitacao = lic.id AND hdec.tipo = 22
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$where = "lic.id = $gId_licitacao AND
			lic.deletado = 0 AND
			IF (apl.id IS NULL, labas.grupo = 4, labas.grupo = 3) AND
			IF (hdec.id IS NULL, lic.id > 0, DATE(lic.datahora_abertura) >= '$data_hoje')";
	}
	else if ($sInside_tipo == 1) //BO
	{
		$select = "lic.id,
			lic.instancia,
			lic.orgao,
			lic.objeto,
			lic.importante,
			lic.datahora_abertura,
			lic.datahora_entrega,
			DATEDIFF(DATE(lic.datahora_abertura), CURRENT_DATE()) AS data_abertura_dias,
			DATEDIFF(DATE(lic.datahora_entrega), CURRENT_DATE()) AS data_entrega_dias,
			UNIX_TIMESTAMP(lic.datahora_limite) - UNIX_TIMESTAMP() AS limite,
			lic.fase,
			lic.valor,
			lic.link,
			lic.numero,
			lic.data_hora,
			lic.srp,
			lic.meepp,
			lic.prazo_entrega_produto,
			lic.prazo_entrega_produto_uteis,
			lic.numero_rastreamento,
			lic.validade_proposta,
			lic.vigencia_contrato,
			mdl.nome AS nome_modalidade,
			cid.nome AS nome_cidade,
			cid.uf AS uf,
			labas.id_status";

		$from = "gelic_licitacoes AS lic
			INNER JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id AND itm.id_tipo_veiculo > 0 AND itm.acompanhamento = 0
			INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 2
			INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
			INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
			LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31";

		$where = "lic.id = $gId_licitacao AND
			lic.deletado = 0 AND
			his.id IS NULL";
	}


	//carregar dados da licitacao
	$oOutput = '';
	$db->query("SELECT $select FROM $from WHERE $where GROUP BY lic.id");
	if ($db->nextRecord())
	{
		$declinou = false;
		if (($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) && $db->f("declinou") > 0)
			$declinou = true;

		$tTmp = $tLicitacao_info;

		$dLimite = segundosConv($db->f("limite"));

		$dab_h = substr($db->f("datahora_abertura"),11,5);
		$den_h = substr($db->f("datahora_entrega"),11,5);
		if ($dab_h == "00:00") $dab_h = "--:--";
		if ($den_h == "00:00") $den_h = "--:--";

		$tTmp = str_replace("{{LIC_ID}}", $db->f("id"), $tTmp);
		$tTmp = str_replace("{{LIC_NUMERO}}", $db->f("numero"), $tTmp);
		$tTmp = str_replace("{{LIC_DHAB}}", mysqlToBr(substr($db->f("datahora_abertura"),0,10)).' '.$dab_h, $tTmp);
		$tTmp = str_replace("{{LIC_DHEN}}", mysqlToBr(substr($db->f("datahora_entrega"),0,10)).' '.$den_h, $tTmp);
		$tTmp = str_replace("{{LIC_DHAB_D}}", niceDays($db->f("data_abertura_dias")), $tTmp);
		$tTmp = str_replace("{{LIC_DHEN_D}}", niceDays($db->f("data_entrega_dias")), $tTmp);
		$tTmp = str_replace("{{LIC_MOD}}", utf8_encode($db->f("nome_modalidade")), $tTmp);
		$tTmp = str_replace("{{LIC_ORGAO}}", utf8_encode(stripslashes($db->f("orgao"))), $tTmp);
		$tTmp = str_replace("{{CID_NOME}}", utf8_encode($db->f("nome_cidade")), $tTmp);
		$tTmp = str_replace("{{CID_UF}}", $db->f("uf"), $tTmp);

		if ($db->f("instancia") > 0)
			$tTmp = str_replace("{{LIC_INSTANCIA}}", $aInstancia[$db->f("instancia")], $tTmp);
		else
			$tTmp = str_replace("{{LIC_INSTANCIA}}", '<a class="gray-aa">- não informado -</a>', $tTmp);

		if (strlen($db->f("link")) > 0)
			$tTmp = str_replace("{{LIC_LINK}}", '<a class="ablue" href="'.$db->f("link").'" target="_blank">'.$db->f("link").'</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_LINK}}", '<span class="gray-aa">- não informado -</span>', $tTmp);

		if ($db->f("valor") == 0.00)
			$tTmp = str_replace("{{LIC_VALOR}}", '<span class="gray-aa">- não informado -</span>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_VALOR}}", "R$ ".number_format($db->f("valor"),2,',','.'), $tTmp);


		$tTmp = str_replace("{{LIC_SRP}}", $aSimnao[$db->f("srp")], $tTmp);
		$tTmp = str_replace("{{LIC_MEEPP}}", $aSimnao[$db->f("meepp")], $tTmp);

		if (strlen($db->f("numero_rastreamento")) > 0)
			$tTmp = str_replace("{{LIC_RAST}}", '<a class="ablue" href="c.rastreamento.php?n='.$db->f("numero_rastreamento").'" target="_blank">'.$db->f("numero_rastreamento").'</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_RAST}}", '<span class="gray-aa">- não informado -</span>', $tTmp);

		$tTmp = str_replace("{{LIC_PRAZO_ENTREGA_PRODUTO}}", $db->f("prazo_entrega_produto")." ".$aPrazoentregaproduto[$db->f("prazo_entrega_produto_uteis")], $tTmp);

		if ($db->f("validade_proposta") == '')
			$tTmp = str_replace("{{LIC_VALP}}", '<span class="gray-aa">- não informado -</span>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_VALP}}", utf8_encode($db->f("validade_proposta")), $tTmp);

		if ($db->f("vigencia_contrato") == '')
			$tTmp = str_replace("{{LIC_VIGC}}", '<span class="gray-aa">- não informado -</span>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_VIGC}}", utf8_encode($db->f("vigencia_contrato")), $tTmp);

		$tTmp = str_replace("{{LIC_REG}}", $aEstados[$db->f("uf")], $tTmp);


		if ($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) //DN, DN FILHO, REP
		{
			$db->query("SELECT nome FROM gelic_clientes WHERE id = $cliente_parent",1);
			$db->nextRecord(1);
			$tTmp = str_replace("{{CLI}}", utf8_encode($db->f("nome",1)), $tTmp);
		}
		else
		{
			if ($db->f("fase") == 1)
			{
				$DNs = '';
				$db->query("SELECT nome FROM gelic_clientes WHERE id IN (SELECT id_cliente FROM gelic_licitacoes_clientes WHERE id_licitacao = ".$db->f("id").")",1);
				while ($db->nextRecord(1))
					$DNs .= '<br>'.utf8_encode($db->f("nome",1));

				$tTmp = str_replace("{{CLI}}", substr($DNs,4), $tTmp);
			}
			else if ($db->f("fase") == 2)
			{
				$tTmp = str_replace("{{CLI}}", $aEstados[$db->f("uf")], $tTmp);
			}
			else
			{
				$tTmp = str_replace("{{CLI}}", "Brasil", $tTmp);
			}
		}


		//EDITAL
		//$db->query("SELECT nome_arquivo, arquivo FROM gelic_licitacoes_edital WHERE id_licitacao = ".$db->f("id"),1);
		//if ($db->nextRecord(1))
		//	$tTmp = str_replace("{{LIC_EDITAL}}", '<a class="bt-style-2 fr ml-8" href="arquivos/edital/'.$db->f("arquivo",1).'" target="_blank" title="'.utf8_encode($db->f("nome_arquivo",1)).'" style="height: 26px; line-height: 26px;">EDITAL</a>', $tTmp);
		//else
		//	$tTmp = str_replace("{{LIC_EDITAL}}", '', $tTmp);


		//EDITAL
		$db->query("SELECT COUNT(*) AS total FROM gelic_licitacoes_edital WHERE id_licitacao = ".$db->f("id"),1);
		$db->nextRecord(1);
		$dEditais = $db->f("total",1);
		if ($dEditais > 0)
			$tTmp = str_replace("{{LIC_EDITAL}}", '<a id="drop-editais" class="deditais drp" href="javascript:void(0);" onclick="dropEditais();">EDITAIS (<span id="total-editais">'.$dEditais.'</span>)<img src="img/a-down-g.png"></a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_EDITAL}}", '', $tTmp);




		//ATA
		//$db->query("SELECT nome_arquivo, arquivo FROM gelic_licitacoes_ata WHERE id_licitacao = ".$db->f("id"),1);
		//if ($db->nextRecord(1))
		//	$tTmp = str_replace("{{LIC_ATA}}", '<a class="bt-style-2 fr ml-8" href="arquivos/ata/'.$db->f("arquivo",1).'" target="_blank" title="'.utf8_encode($db->f("nome_arquivo",1)).'" style="height: 26px; line-height: 26px;">ATA</a>', $tTmp);
		//else
		//	$tTmp = str_replace("{{LIC_ATA}}", '', $tTmp);

		//ATA
		$db->query("SELECT nome_arquivo, arquivo FROM gelic_licitacoes_ata WHERE id_licitacao = ".$db->f("id"),1);
		if ($db->nextRecord(1))
			$tTmp = str_replace("{{LIC_ATA}}", '<a class="deditais" href="'.linkFileBucket("vw/licata/".$db->f("arquivo",1)).'" target="_blank" title="'.utf8_encode($db->f("nome_arquivo",1)).'" style="padding:0 10px;margin:0;">ATA</a>', $tTmp);
		else
			$tTmp = str_replace("{{LIC_ATA}}", '', $tTmp);

	
		$tTmp = str_replace("{{LIC_DATA}}", mysqlToBr(substr($db->f("data_hora"),0,10)), $tTmp);
		$tTmp = str_replace("{{LIC_HORA}}", substr($db->f("data_hora"),11), $tTmp);
		$tTmp = str_replace("{{LIC_TEMPO}}", timeAgo($db->f("data_hora")), $tTmp);

		$objeto = utf8_encode(stripslashes($db->f("objeto")));
		$objeto = str_replace('../arquivos/im/','arquivos/im/',$objeto);
		$tTmp = str_replace("{{LIC_OBJETO}}", $objeto, $tTmp);

		$importante = utf8_encode(stripslashes($db->f("importante")));
		$importante = str_replace('../arquivos/im/','arquivos/im/',$importante);
		$tTmp = str_replace("{{LIC_IMPORTANTE}}", $importante, $tTmp);


		$tParticipar = '';
		if (($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) && $db->f("fase") == 1 && $db->f("id_status") != 26 && $db->f("id_status") != 27 && $db->f("id_status") != 28 && !$declinou && in_array("lic_interesse", $xAccess))
			$tParticipar = '<div id="d-participar" style="overflow: hidden; margin-top: 20px; margin-bottom: 20px;"><a id="a-declinar" class="bt-style-2 fl" href="javascript:void(0);" onclick="declinarParticipacao(false);" style="height: 26px; line-height: 26px;">Sem interesse</a></div>';

		$tTmp = str_replace("{{LIC_PARTICIPAR}}", $tParticipar, $tTmp);

		$oOutput .= $tTmp;

		if (($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) && $db->f("fase") == 1 && $db->f("id_status") != 26 && $db->f("id_status") != 27 && $db->f("id_status") != 28 && !$declinou && in_array("lic_mensagem", $xAccess))
			$oOutput .= $tMessage_box;
		else if (($sInside_tipo == 2 || $sInside_tipo == 3 || $sInside_tipo == 4) && ($db->f("fase") == 2 || $db->f("fase") == 3) && !$declinou && in_array("lic_mensagem", $xAccess))
			$oOutput .= $tMessage_box;
		else if ($sInside_tipo == 1)
			$oOutput .= $tMessage_box;
	}
	else
	{
		echo '<script>
		window.location = "index.php?p=cli_index";
		</script>';
		exit;
	}

	// Restaurar busca 1
	$dSearch = "";
	$db->query("SELECT valor FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = 'search'");
	if ($db->nextRecord())
	{
		$a = json_decode($db->f("valor"), true);
		$dSearch = $a["search_1"];
	}

	?>
	<section>
		<form id="upload-form-apr" enctype="multipart/form-data"></form>
		<div class="middle">
			<?php echo getTop(); ?>
			<?php echo getMenu(1,0); ?>

			<div class="topbts" style="position: relative;">
				<a class="menu-btn fl" href="index.php?p=cli_index" style="height:40px;line-height:38px;padding:0 14px;">&#x2190; Voltar</a>
				<a class="menu-btn fr" href="javascript:void(0);" onclick="exportarPDF();" style="height:40px;line-height:38px;padding: 0 16px;"><img src="img/pdf.png" style="width:16px;height:16px;border:0;margin: 0 5px 2px 0;vertical-align:middle;">Exportar em PDF</a>
				<div id="search-options">
					<a class="adv-search-btn drp" href="javascript:void(0);" onclick="advSearch(this);" title="Busca Avançada"></a>
					<input id="i_search" type="text" class="lic_search_ip fr" placeholder="Nº de Identificação" maxlength="8" value="<?php echo $dSearch; ?>" style="float: right;">
				</div>
				<span id="search-processing" style="display:none;float:right;margin-right:40px;font-style:italic;line-height:40px;font-weight:bold;">Procurando...</span>
			</div>

			<input id="id-licitacao" type="hidden" value="<?php echo $gId_licitacao; ?>">
			<?php echo $oOutput; ?>
		</div>
		<div class="middle" style="height: 40px;">
		</div>
	</section>

	<?php
}
else
{
	?>
	<section>
		<div class="middle">
			<div class="lic" style="height: 340px;">
				<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;float:none;">Acesso Restrito!</h4>
				<p style="color: #a6a6a6;">Se você é cliente GELIC utilize o seu login e senha para ter acesso nesta área.</p>
			</div>
		</div>
	</section>
	<?php
}

?>
