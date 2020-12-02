<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("msg_visualizar", $xAccess))
	{
		$aReturn[0] = 1;
		$aReturn[1] = '<div style="color:#ff0000;">Acesso restrito.</div>';
		echo json_encode($aReturn);
		exit;
	}


	$pId = intval($_POST["id"]);
	$pTab = intval($_POST["tab"]);

	if ($pTab == 0)
		$tab_color = '0077ee';
	else if ($pTab == 1)
		$tab_color = 'f0b400';
	else if ($pTab == 2)
		$tab_color = 'ef0000';
	else
		$tab_color = '00c400';

	$n = array();
	$n[0] = array("grupo"=>"", "sub"=>"", "lb"=>"n/d", "db"=>"");
	$n[1] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"A", "lb"=>"Mensagens do DN");
	$n[2] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"B", "lb"=>"Mensagens do Back Office");
	$n[3] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"C", "lb"=>"Sem Interesse do DN");
	$n[4] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"D", "lb"=>"Aviso de APL Enviada");
	$n[5] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"E", "lb"=>"Aviso de APL Aprovada");
	$n[6] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"F", "lb"=>"Aviso de APL Reprovada");
	$n[7] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"G", "lb"=>"Aviso de APL Aprovação Revertida");
	$n[8] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"H", "lb"=>"Aviso de APL Reprovação Revertida");
	$n[9] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"I", "lb"=>"Licitação Prorrogada");
	$n[10] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"J", "lb"=>"Novas Solicitações");
	$n[11] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"K", "lb"=>"Mensagens do DN");
	$n[12] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"L", "lb"=>"Aviso de APL Enviada");
	$n[13] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"M", "lb"=>"Aviso de APL Aprovada");
	$n[14] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"N", "lb"=>"Aviso de APL Reprovada");
	$n[15] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"O", "lb"=>"Aviso de APL Aprovação Revertida");
	$n[16] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"P", "lb"=>"Aviso de APL Reprovação Revertida");

	$n[17] = array("grupo"=>"BO", "sub"=>"LICITAÇÕES", "db"=>"A", "lb"=>"Mensagens da Administração");
	$n[18] = array("grupo"=>"BO", "sub"=>"LICITAÇÕES", "db"=>"B", "lb"=>"Mensagens do DN");
	$n[19] = array("grupo"=>"BO", "sub"=>"LICITAÇÕES", "db"=>"C", "lb"=>"Licitação Encerrada");
	$n[20] = array("grupo"=>"BO", "sub"=>"LICITAÇÕES", "db"=>"D", "lb"=>"Encerramento Revertido");
	$n[21] = array("grupo"=>"BO", "sub"=>"LICITAÇÕES", "db"=>"E", "lb"=>"Aviso de APL Enviada");
	$n[22] = array("grupo"=>"BO", "sub"=>"LICITAÇÕES", "db"=>"F", "lb"=>"Término do Prazo Limite");
	$n[23] = array("grupo"=>"BO", "sub"=>"COMPRA DIRETA/SRP", "db"=>"G", "lb"=>"Novas Solicitações");
	$n[24] = array("grupo"=>"BO", "sub"=>"COMPRA DIRETA/SRP", "db"=>"H", "lb"=>"Mensagens da Administração");
	$n[25] = array("grupo"=>"BO", "sub"=>"COMPRA DIRETA/SRP", "db"=>"I", "lb"=>"Mensagens do DN");
	$n[26] = array("grupo"=>"BO", "sub"=>"COMPRA DIRETA/SRP", "db"=>"J", "lb"=>"Aviso de APL Enviada");

	$n[27] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"A", "lb"=>"Novas Licitações");
	$n[28] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"B", "lb"=>"Mensagens da Administração");
	$n[29] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"C", "lb"=>"Mensagens do Back Office");
	$n[30] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"D", "lb"=>"Licitação Prorrogada");
	$n[31] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"E", "lb"=>"Licitação Encerrada");
	$n[32] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"F", "lb"=>"Encerramento Revertido");
	$n[33] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"G", "lb"=>"Revertido o Desinteresse na Licitação");
	$n[34] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"H", "lb"=>"Aviso de Solicitação de ATA");
	$n[35] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"I", "lb"=>"Aviso de APL Aprovada");
	$n[36] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"J", "lb"=>"Aviso de APL Reprovada");
	$n[37] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"K", "lb"=>"Aviso de APL Aprovação Revertida");
	$n[38] = array("grupo"=>"DN", "sub"=>"LICITAÇÕES", "db"=>"L", "lb"=>"Aviso de APL Reprovação Revertida");
	$n[39] = array("grupo"=>"DN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"M", "lb"=>"Mensagens da Administração");
	$n[40] = array("grupo"=>"DN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"N", "lb"=>"Aviso de Autorização (Envio da APL)");
	$n[41] = array("grupo"=>"DN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"O", "lb"=>"Aviso de APL Aprovada");
	$n[42] = array("grupo"=>"DN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"P", "lb"=>"Aviso de APL Reprovada");
	$n[43] = array("grupo"=>"DN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"Q", "lb"=>"Aviso de APL Aprovação Revertida");
	$n[44] = array("grupo"=>"DN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"R", "lb"=>"Aviso de APL Reprovação Revertida");

	$n[45] = array("grupo"=>"ADMIN", "sub"=>"LICITAÇÕES", "db"=>"Q", "lb"=>"Mensagens do Pregoeiro Chama");
	$n[46] = array("grupo"=>"ADMIN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"R", "lb"=>"Mensagens do Back Office");
	$n[48] = array("grupo"=>"DN", "sub"=>"COMPRA DIRETA/SRP", "db"=>"S", "lb"=>"Mensagens do Back Office");



	$db = new Mysql();
	$oMsg = '';

	$mtbl = "gelic_mensagens";
	if ($pTab == 3)
		$mtbl .= "_log";

	$db->query(
"SELECT
	msg.id_notificacao,
	msg.origem,
	msg.metodo,
	msg.tipo_destino,
	msg.destino,
	msg.assunto,
	msg.mensagem,
	msg.anexos,
	msg.data_hora_inserido,
	msg.data_hora_enviar,
	msg.status,
	msg.data_hora_status,
	msg.resultado,
	adm_org.nome AS admin_origem,
    adm_dst.nome AS admin_destino,
    cli_org.nome AS cliente_origem,
	cli_org.tipo AS cliente_origem_tipo,
	cli_dst.nome AS cliente_destino,
	cli_dst.tipo AS cliente_destino_tipo
FROM
	$mtbl AS msg
    LEFT JOIN gelic_admin_usuarios AS adm_org ON adm_org.id = msg.id_origem
    LEFT JOIN gelic_admin_usuarios AS adm_dst ON adm_dst.id = msg.id_destino
	LEFT JOIN gelic_clientes AS cli_org ON cli_org.id = msg.id_origem
	LEFT JOIN gelic_clientes AS cli_dst ON cli_dst.id = msg.id_destino
WHERE
	msg.id = $pId");
	if ($db->nextRecord())
	{
		if ($db->f("metodo") == M_EMAIL)
		{
			$dAnexo = '<div class="t12 row-100" style="border-bottom: 1px solid #888888;height:1px; margin-bottom: 8px;"></div>';

			if ($db->f("anexos") <> '')
			{
				$anexos = '';
				$a = json_decode($db->f("anexos"), true);
				for ($i=0; $i<count($a); $i++)
					$anexos .= '<a class="bt-anexo" href="../arquivos/anexo/'.$a[$i]["arquivo"].'" target="_blank">'.$a[$i]["nome_arquivo"].'</a>';

				$dAnexo = '<div class="t12 row-100" style="border-top: 1px dotted #888888; border-bottom: 1px solid #888888; margin-bottom: 8px;">
					<span class="fl w-120 bold" style="line-height:31px;">Anexo(s):</span>
					<span class="fl lh-21">'.$anexos.'</span>
				</div>';
			}

			$dMetodo = '<span class="m-email fl" style="margin:0;">EMAIL</span>';
			$dAssunto = '<div class="t12 row-100" style="border-top: 1px solid #888888; border-bottom: 1px dotted #888888; margin-top: 8px;">
				<span class="fl w-120 bold lh-21">Assunto:</span>
				<span class="fl lh-21">'.utf8_encode($db->f("assunto")).'</span>
			</div>';
		}
		else
		{
			$dMetodo = '<span class="m-sms fl" style="margin:0;">SMS</span>';
			$dAnexo = '<div class="t12 row-100" style="border-bottom: 1px solid #888888;height:1px; margin-bottom: 8px;"></div>';
			$dAssunto = '<div class="t12 row-100" style="border-top: 1px solid #888888;height:1px; margin-top: 8px;"></div>';
		}


		if (in_array($db->f("tipo_destino"), array(ADM_DLR, ADM_BOF, ADM_ADM)))
			$dRemetente = '(ADMIN) '.utf8_encode($db->f("admin_origem"));
		else if (in_array($db->f("tipo_destino"), array(DLR_ADM, DLR_BOF, DLR_DLR, BOF_DLR, BOF_ADM, BOF_BOF)))
		{
			if ($db->f("cliente_origem_tipo") == 1)
				$dRemetente = '(BO) '.utf8_encode($db->f("cliente_origem"));
			else
				$dRemetente = '(DN) '.utf8_encode($db->f("cliente_origem"));
		}
		else
			$dRemetente = 'Sistema';



		if (in_array($db->f("tipo_destino"), array(ADM_ADM, DLR_ADM, BOF_ADM, SYS_ADM)))
			$dRecipiente = '(ADMIN) '.utf8_encode($db->f("admin_destino"));
		else
		{
			if ($db->f("cliente_destino_tipo") == 1)
				$dRecipiente = '(BO) '.utf8_encode($db->f("cliente_destino"));
			else
				$dRecipiente = '(DN) '.utf8_encode($db->f("cliente_destino"));
		}




		$oMsg = '<div class="t12 row-100">
			'.$dMetodo.'
			<span class="fl clear w-120 bold lh-21">Notificação:</span>
			<span class="fl lh-21">'.$n[$db->f("id_notificacao")]["grupo"].' ('.$n[$db->f("id_notificacao")]["sub"].') - '.$n[$db->f("id_notificacao")]["lb"].' ['.$n[$db->f("id_notificacao")]["db"].']</span>

			<span class="fl clear w-120 bold lh-21">Origem:</span>
			<span class="fl lh-21">'.$db->f("origem").'</span>

			<span class="fl clear w-120 bold lh-21">Remetente:</span>
			<span class="fl lh-21">'.$dRemetente.'</span>

			<span class="fl clear w-120 bold lh-21">Recipiente:</span>
			<span class="fl lh-21">'.$dRecipiente.'</span>

			<span class="fl clear w-120 bold lh-21">Destino:</span>
			<span class="fl lh-21">'.$db->f("destino").'</span>
		</div>
		'.$dAssunto.'
		<div class="t12 row-100">
			'.utf8_encode($db->f("mensagem")).'
		</div>
		'.$dAnexo.'
		<div class="t12 row-100">
			<span class="fl clear w-120 bold lh-21">Inserido:</span>
			<span class="fl lh-21">'.$db->f("data_hora_inserido").'</span>

			<span class="fl clear w-120 bold lh-21">Para Enviar Em:</span>
			<span class="fl lh-21">'.$db->f("data_hora_enviar").'</span>

			<span class="fl clear w-120 bold lh-21">Status:</span>
			<span class="fl lh-21">'.$db->f("status").'</span>

			<span class="fl clear w-120 bold lh-21">Data/Hora Status:</span>
			<span class="fl lh-21">'.$db->f("data_hora_status").'</span>

			<span class="fl clear w-120 bold lh-21">Resultado:</span>
			<span class="fl lh-21" style="color:#'.$tab_color.';">'.utf8_encode(strip_tags(stripslashes($db->f("resultado")))).'</span>
		</div>';

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = $oMsg;
	}
} 
echo json_encode($aReturn);

?>
