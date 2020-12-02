<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$xAccess = explode(" ",getAccess());
	if (!in_array("lic_editar", $xAccess))
	{
		$aReturn[0] = 9; //acesso restrito
		echo json_encode($aReturn);
		exit;
	}

	$sInside_id = $_SESSION[SESSION_ID];
	$db = new Mysql();

	$host = $_SERVER["HTTP_HOST"];
	if ($host == '127.0.0.1')
		$host .= '/GelicPrime.com.br';

	if ($_POST["action"] == "read")
	{
		$pId_licitacao = intval($_POST["id-licitacao"]);
		$users = '';
		$aMon_users = array();
		$aButtons = array();
		$aButtons["Ativar Pregoeiro Chama"] = array("onclick"=>"ultimateClose();pregoeiroChamaATIVAR();","is_default"=>1,"css_class"=>"ultimate-btn-green ultimate-btn-left");
		$aButtons["Cancelar"] = array("css_class"=>"ultimate-btn-gray ultimate-btn-right");
		$color = 'gray';
		$title = 'Pregoeiro Chama (<span class="italic">Desativado</span>)';

		// Verificar se a licitacao contem cod_pregao e cod_uasg
		$db->query("SELECT cod_pregao, cod_uasg, pc_ativo FROM gelic_licitacoes WHERE id = $pId_licitacao AND cod_pregao > 0 AND cod_uasg > 0");
		if ($db->nextRecord())
		{
			$dCod_pregao = $db->f("cod_pregao");
			$dCod_uasg = $db->f("cod_uasg");
			$dPc_ativo = $db->f("pc_ativo");

			// Verificar se o monitoramento pode ser abilitado (via API gerenciador)
			$aVars = array('action'=>'verificar', 'cod_pregao'=>$dCod_pregao, 'cod_uasg'=>$dCod_uasg);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "http://".$host."/pcg/?".http_build_query($aVars));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$html = curl_exec($curl);
			curl_close($curl);
			$result = json_decode($html, true);
			if ($result["existe"] == 1 && $result["ativo"] == 2)
			{
				//O robo provavelmente desativou esta monitoracao por questao de dados invalidos
				// Alertar usuario
				$aReturn[0] = 2; //monitoracao nao mais diponivel
				$aReturn[1] = 'A monitoração não está mais disponível para esta licitação (Erro '.__LINE__.').';
				echo json_encode($aReturn);
				exit;
			}


			if ($dPc_ativo == 1)
			{
				$aButtons = array();
				$aButtons["Salvar Alterações"] = array("onclick"=>"ultimateClose();pregoeiroChamaATIVAR();","is_default"=>1,"css_class"=>"ultimate-btn-green ultimate-btn-left");
				$aButtons["Desativar Pregoeiro Chama"] = array("onclick"=>"ultimateClose();pregoeiroChamaDESATIVAR();","css_class"=>"ultimate-btn-red ultimate-btn-left");
				$aButtons["Cancelar"] = array("css_class"=>"ultimate-btn-gray ultimate-btn-right");
				$color = 'green';
				$title = 'Pregoeiro Chama (<span class="italic">Ativo</span>)';
			}
		}
		else
		{
			// Alertar usuario
			$aReturn[0] = 2; //monitoracao nao mais diponivel
			$aReturn[1] = 'A monitoração não está mais disponível para esta licitação (Erro '.__LINE__.').';
			echo json_encode($aReturn);
			exit;
		}


		$aAdmins = array();
		if ($color == 'gray')
		{
			// Buscar admins que postaram mensagens na licitacao
			$db->query("SELECT DISTINCT(id_sender) AS id_admin_usuario FROM gelic_historico WHERE id_licitacao = $pId_licitacao AND tipo = 1");
			while ($db->nextRecord())
				$aAdmins[] = $db->f("id_admin_usuario");
		}


		// Buscar usuarios com status das notificacoes
		$db->query("
SELECT
	usr.id,
	usr.nome,
	usr.login,
	pcusr.email,
	pcusr.sms
FROM
	gelic_admin_usuarios AS usr
	LEFT JOIN gelic_pc_usuarios AS pcusr ON pcusr.id_admin_usuario = usr.id AND id_licitacao = $pId_licitacao
WHERE
	usr.ativo > 0
ORDER BY
	usr.nome");
		while ($db->nextRecord())
		{
			$cb_email = '0';
			$cb_sms = '0';

			if (in_array($db->f("id"), $aAdmins))
			{
				$cb_email = 1;
				$cb_sms = 1;
				$aNtf = array(1,2);
				$aMon_users[] = array("id"=>intval($db->f("id")),"ntf"=>$aNtf);
			}
			else if ($db->f("email") != '')
			{
				$aNtf = array();
				if ($db->f("email") > 0)
				{
					$aNtf[] = 1;
					$cb_email = '1';
				}

				if ($db->f("sms") > 0)
				{
					$aNtf[] = 2;
					$cb_sms = '1';
				}
				
				$aMon_users[] = array("id"=>intval($db->f("id")),"ntf"=>$aNtf);
			}

			$users .= '<div class="mon-user-row">
				<span class="mon-user">'.utf8_encode($db->f("nome")).'</span>
				<span class="mon-user italic gray-88">('.utf8_encode($db->f("login")).')</span>
				<a class="mon-cb'.$cb_sms.'" href="javascript:void(0);" onclick="monUser(this,'.$db->f("id").',2)"></a>
				<div class="mon-divide-0"></div>
				<a class="mon-cb'.$cb_email.'" href="javascript:void(0);" onclick="monUser(this,'.$db->f("id").',1)"></a>
				<div class="mon-divide-0"></div>
			</div>';
		}

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = '
		<div style="padding-bottom:10px;">
			<span class="bold italic">Selecione os usuários que receberão notificações das mensagens do Pregoeiro Chama para<br>esta licitação juntamente com a forma de recebimento:</span>
		</div>
		<div style="padding: 0 0 2px 0; overflow: hidden;">
			<img src="img/mon-icon3.png" style="float: right; margin-right: 27px;" title="Notificar via SMS (celular)">
			<img src="img/mon-icon2.png" style="float: right; margin-right: 25px;" title="Notificar via Email">
		</div>
		<div class="mon-user-holder">
			'.$users.'
		</div>';

		$aReturn[2] = json_encode($aMon_users);
		$aReturn[3] = json_encode($aButtons);
		$aReturn[4] = $color;
		$aReturn[5] = $title;
	}
	else if ($_POST["action"] == "ativar")
	{
		$pId_licitacao = intval($_POST["id-licitacao"]);
		$aUsers = json_decode($_POST["users"], true);
		$now = date("Y-m-d H:i:s");

		$aExisting_users = array();
		$db->query("SELECT id_admin_usuario, email, sms, ultima_msg_email, ultima_msg_sms FROM gelic_pc_usuarios WHERE id_licitacao = $pId_licitacao");
		while ($db->nextRecord())
			$aExisting_users[] = array("id"=>intval($db->f("id_admin_usuario")),"email"=>intval($db->f("email")),"sms"=>intval($db->f("sms")),"ultima_msg_email"=>$db->f("ultima_msg_email"),"ultima_msg_sms"=>$db->f("ultima_msg_sms"));


		$db->query("DELETE FROM gelic_pc_usuarios WHERE id_licitacao = $pId_licitacao");

		// Verificar se a licitacao contem cod_pregao e cod_uasg
		$db->query("SELECT cod_pregao, cod_uasg FROM gelic_licitacoes WHERE id = $pId_licitacao AND cod_pregao > 0 AND cod_uasg > 0");
		if ($db->nextRecord())
		{
			$dCod_pregao = $db->f("cod_pregao");
			$dCod_uasg = $db->f("cod_uasg");

			// Verificar se o monitoramento pode ser abilitado (via API gerenciador)
			$aVars = array('action'=>'ativar', 'cod_pregao'=>$dCod_pregao, 'cod_uasg'=>$dCod_uasg);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "http://".$host."/pcg/?".http_build_query($aVars));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$html = curl_exec($curl);
			curl_close($curl);
			$result = json_decode($html, true);
			if ($result["ativo"] == 2)
			{
				//O robo provavelmente desativou esta monitoracao por questao de dados invalidos
				// Alertar usuario
				$aReturn[0] = 2; //monitoracao nao mais diponivel
				$aReturn[1] = 'A monitoração não está mais disponível para esta licitação (Erro '.__LINE__.').';
				echo json_encode($aReturn);
				exit;
			}
		}
		else
		{
			// Alertar usuario
			$aReturn[0] = 2; //monitoracao nao mais diponivel
			$aReturn[1] = 'A monitoração não está mais disponível para esta licitação (Erro '.__LINE__.').';
			echo json_encode($aReturn);
			exit;
		}

		// Salvar notificacoes dos usuarios aqui
		$insert = "";
		for ($i=0; $i<count($aUsers); $i++)
		{
			if ($insert != "") $insert .= ",";
			$eml = 0;
			$sms = 0;
			if (in_array(1,$aUsers[$i]["ntf"])) $eml = 1;
			if (in_array(2,$aUsers[$i]["ntf"])) $sms = 1;


			// Se notificacao for nova entao atualizar ultima_msg_email ou ultima_msg_sms
			$ult_eml = $now;
			$ult_sms = $now;
			for ($j=0; $j<count($aExisting_users); $j++)
			{
				if ($aUsers[$i]["id"] == $aExisting_users[$j]["id"])
				{
					if (
						($eml == 0 && $aExisting_users[$j]["email"] == 0) ||
						($eml == 0 && $aExisting_users[$j]["email"] == 1) ||
						($eml == 1 && $aExisting_users[$j]["email"] == 1)
						) $ult_eml = $aExisting_users[$j]["ultima_msg_email"];

					if (
						($sms == 0 && $aExisting_users[$j]["sms"] == 0) ||
						($sms == 0 && $aExisting_users[$j]["sms"] == 1) ||
						($sms == 1 && $aExisting_users[$j]["sms"] == 1)
						) $ult_sms = $aExisting_users[$j]["ultima_msg_sms"];

					break;
				}
			}

			$insert .= "(NULL, $pId_licitacao, ".$aUsers[$i]["id"].", $eml, $sms, '$ult_eml', '$ult_sms')";
		}

		if ($insert != "")
			$db->query("INSERT INTO gelic_pc_usuarios VALUES $insert");


		// Ativar local
		$db->query("UPDATE gelic_licitacoes SET pc_ativo = 1 WHERE id = $pId_licitacao");

		// Inserir no historico
		$aHis = array();
		$aHis["users"] = $aUsers;
		$aHis["cod_pregao"] = $dCod_pregao;
		$aHis["cod_uasg"] = $dCod_uasg;
		$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 51, 0, 0, '$now', '".json_encode($aHis)."', '', '')");

		$aReturn[0] = 1; //sucesso
	}
	else if ($_POST["action"] == "desativar")
	{
		$pId_licitacao = intval($_POST["id-licitacao"]);
		$now = date("Y-m-d H:i:s");

		$db->query("DELETE FROM gelic_pc_usuarios WHERE id_licitacao = $pId_licitacao");

		$db->query("SELECT cod_pregao, cod_uasg FROM gelic_licitacoes WHERE id = $pId_licitacao AND cod_pregao > 0 AND cod_uasg > 0");
		if ($db->nextRecord())
		{
			$dCod_pregao = $db->f("cod_pregao");
			$dCod_uasg = $db->f("cod_uasg");

			// Desabilitar local
			$db->query("UPDATE gelic_licitacoes SET pc_ativo = 0 WHERE id = $pId_licitacao");

			// Inserir no historico
			$aHis = array();
			$aHis["cod_pregao"] = $dCod_pregao;
			$aHis["cod_uasg"] = $dCod_uasg;
			$db->query("INSERT INTO gelic_historico VALUES (NULL, $pId_licitacao, 0, 0, $sInside_id, 0, 52, 0, 0, '$now', '".json_encode($aHis)."', '', '')");
		}
		else
		{
			// Alertar usuario
			$aReturn[0] = 2; //monitoracao nao mais diponivel
			$aReturn[1] = 'A monitoração não está mais disponível para esta licitação (Erro '.__LINE__.').';
			echo json_encode($aReturn);
			exit;
		}

		$aReturn[0] = 1; //sucesso
	}
} 
echo json_encode($aReturn);

?>
