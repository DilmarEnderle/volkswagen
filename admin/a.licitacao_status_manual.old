<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$pId_historico = intval($_POST["f-id-historico"]);

	$status_to_read = array();
	$abas_to_read = array();
	$fixos = array();

	$db = new Mysql();
	$db->query("SELECT texto FROM gelic_historico WHERE id = $pId_historico AND tipo = 36");
	if ($db->nextRecord())
	{
		$a = json_decode($db->f("texto"), true);

		$status_to_read[] = $a["fr"][0]["status"];
		$status_to_read[] = $a["fr"][1]["status"];
		$status_to_read[] = $a["fr"][2]["status"];
		$status_to_read[] = $a["fr"][3]["status"];

		$status_to_read[] = $a["to"][0]["status"];
		$status_to_read[] = $a["to"][1]["status"];
		$status_to_read[] = $a["to"][2]["status"];
		$status_to_read[] = $a["to"][3]["status"];

		$abas_to_read[] = $a["fr"][0]["aba"];
		$abas_to_read[] = $a["fr"][1]["aba"];
		$abas_to_read[] = $a["fr"][2]["aba"];
		$abas_to_read[] = $a["fr"][3]["aba"];

		$abas_to_read[] = $a["to"][0]["aba"];
		$abas_to_read[] = $a["to"][1]["aba"];
		$abas_to_read[] = $a["to"][2]["aba"];
		$abas_to_read[] = $a["to"][3]["aba"];

		for ($i=0; $i<4; $i++)
		{
			if (isset($a["fr"][$i]["fixo"]))
			{
				if ($a["fr"][$i]["fixo"] > 0)
					$fixos["fr"][$i] = "Sim";
				else
					$fixos["fr"][$i] = "Não";
			}
			else
				$fixos["fr"][$i] = '<span style="color:#aaaaaa;">n/d</span>';
		}

		for ($i=0; $i<4; $i++)
		{
			if (isset($a["to"][$i]["fixo"]))
			{
				if ($a["to"][$i]["fixo"] > 0)
					$fixos["to"][$i] = "Sim";
				else
					$fixos["to"][$i] = "Não";
			}
			else
				$fixos["to"][$i] = '<span style="color:#aaaaaa;">n/d</span>';
		}


		$status_to_read = array_unique($status_to_read);
		$abas_to_read = array_unique($abas_to_read);

		$status_ready = array();
		$db->query("SELECT id, descricao, cor_texto, cor_fundo FROM gelic_status WHERE id IN (".implode(",",$status_to_read).")");
		while ($db->nextRecord())
			$status_ready[$db->f("id")] = array("descricao"=>utf8_encode($db->f("descricao")),"cor_texto"=>$db->f("cor_texto"),"cor_fundo"=>$db->f("cor_fundo"));

		$abas_ready = array();
		$db->query("SELECT id, nome FROM gelic_abas WHERE id IN (".implode(",",$abas_to_read).")");
		while ($db->nextRecord())
			$abas_ready[$db->f("id")] = array("nome"=>utf8_encode($db->f("nome")));


		//detectar alteracoes
		$ch_admin = ' <span class="red bold">*</span>';
		$ch_bo = ' <span class="red bold">*</span>';
		$ch_dn_apl = ' <span class="red bold">*</span>';
		$ch_outros = ' <span class="red bold">*</span>';

		if ($a["fr"][0]["status"] == $a["to"][0]["status"] && $a["fr"][0]["aba"] == $a["fr"][0]["aba"] && $fixos["fr"][0] == $fixos["to"][0])
			$ch_admin = '';

		if ($a["fr"][1]["status"] == $a["to"][1]["status"] && $a["fr"][1]["aba"] == $a["fr"][1]["aba"] && $fixos["fr"][1] == $fixos["to"][1])
			$ch_bo = '';

		if ($a["fr"][2]["status"] == $a["to"][2]["status"] && $a["fr"][2]["aba"] == $a["fr"][2]["aba"] && $fixos["fr"][2] == $fixos["to"][2])
			$ch_dn_apl = '';

		if ($a["fr"][3]["status"] == $a["to"][3]["status"] && $a["fr"][3]["aba"] == $a["fr"][3]["aba"] && $fixos["fr"][3] == $fixos["to"][3])
			$ch_outros = '';

		$aReturn[0] = 1; //sucesso
		$aReturn[1] = '<table style="width: 100%; font-size: 13px;" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width:130px;"></td>
			<td style="border-bottom: 1px solid #000000; border-left: 1px solid #666666; font-weight: bold; padding-left: 6px; line-height: 23px;">Status</td>
			<td style="width:50px; border-bottom: 1px solid #000000; border-left: 1px solid #666666; font-weight: bold; text-align: center; line-height: 23px;">Fixo</td>
			<td style="width:170px;border-bottom: 1px solid #000000; border-left: 1px solid #666666; font-weight: bold; padding-left: 6px; line-height: 23px;">Aba</td>
		</tr>
		<tr>
			<td colspan="4" style="height: 10px;"></td>
		</tr>
		<tr>
			<td colspan="4" style="font-style: italic; color: #888888; font-weight: bold;">DE</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">Administração</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["fr"][0]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["fr"][0]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["fr"][0]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["fr"][0].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["fr"][0]["aba"]]["nome"].'</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">Back Office</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["fr"][1]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["fr"][1]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["fr"][1]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["fr"][1].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["fr"][1]["aba"]]["nome"].'</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">DNs com APL</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["fr"][2]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["fr"][2]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["fr"][2]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["fr"][2].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["fr"][2]["aba"]]["nome"].'</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">Outros DNs</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["fr"][3]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["fr"][3]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["fr"][3]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["fr"][3].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["fr"][3]["aba"]]["nome"].'</td>
		</tr>
		<tr>
			<td colspan="4" style="height: 40px;text-align:left;"><img src="img/down-arrow.png" style="border:0;width:24px;"></td>
		</tr>
		<tr>
			<td colspan="4" style="font-style: italic; color: #888888; font-weight: bold;">PARA</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">Administração'.$ch_admin.'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["to"][0]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["to"][0]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["to"][0]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["to"][0].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["to"][0]["aba"]]["nome"].'</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">Back Office'.$ch_bo.'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["to"][1]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["to"][1]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["to"][1]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["to"][1].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["to"][1]["aba"]]["nome"].'</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">DNs com APL'.$ch_dn_apl.'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["to"][2]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["to"][2]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["to"][2]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["to"][2].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["to"][2]["aba"]]["nome"].'</td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid #999999; line-height: 25px;">Outros DNs'.$ch_outros.'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; padding-left: 6px;"><span style="background-color: #'.$status_ready[$a["to"][3]["status"]]["cor_fundo"].'; color: #'.$status_ready[$a["to"][3]["status"]]["cor_texto"].'; line-height: 17px; padding: 0 6px; font-size: 13px; display: inline-block;">'.$status_ready[$a["to"][3]["status"]]["descricao"].'</span></td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; text-align: center;">'.$fixos["to"][3].'</td>
			<td style="border-bottom: 1px solid #999999; border-left: 1px solid #999999; line-height: 25px; padding-left: 6px;">'.$abas_ready[$a["to"][3]["aba"]]["nome"].'</td>
		</tr>
	</table>';
	}
}
echo json_encode($aReturn);

?>
