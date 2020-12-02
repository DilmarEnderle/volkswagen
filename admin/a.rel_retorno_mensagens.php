<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$pFormato = trim($_POST["formato"]);
	$pPeriodo_fr = $_POST["periodo-fr"];
	$pPeriodo_to = $_POST["periodo-to"];

	$db = new Mysql();

	//VALIDAR DATA PERIODO DE
	if (strlen($pPeriodo_fr) == 10 && isValidBrDate($pPeriodo_fr))
		$pPeriodo_fr = brToUs($pPeriodo_fr); // mm/dd/yyyy
	else
	{
		$db->query("SELECT DATE(data_hora) AS data_mensagem FROM gelic_historico WHERE tipo IN (1,2) ORDER BY data_hora LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_fr = mysqlToUs($db->f("data_mensagem"));
		else
			$pPeriodo_fr = date("m/d/Y");
	}


	//VALIDAR DATA PERIODO ATE
	if (strlen($pPeriodo_to) == 10 && isValidBrDate($pPeriodo_to))
		$pPeriodo_to = brToUs($pPeriodo_to); // mm/dd/yyyy
	else
	{
		$db->query("SELECT DATE(data_hora) AS data_mensagem FROM gelic_historico WHERE tipo IN (1,2) ORDER BY data_hora DESC LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_to = mysqlToUs($db->f("data_mensagem"));
		else
			$pPeriodo_to = date("m/d/Y");
	}


	//SE O PERIODO DE FOR MAIOR DO QUE O PERIODO ATE (INVERTER)
	if (intval(str_replace("-","",usToMysql($pPeriodo_fr))) > intval(str_replace("-","",usToMysql($pPeriodo_to))))
	{
		$t = $pPeriodo_to;
		$pPeriodo_to = $pPeriodo_fr;
		$pPeriodo_fr = $t;
	}



	if ($pFormato == "xlsx")
	{
		require_once "../../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Retorno de Mensagens")
			->setSubject("Mensagens")
			->setDescription("Retorno de Mensagens GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Mensagens");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A1", 'Período escolhido ('.usToBr($pPeriodo_fr).' - '.usToBr($pPeriodo_to).')');

		$row = 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Licitação')
			->setCellValue("B$row", 'Remetente')
			->setCellValue("C$row", 'Usuário')
			->setCellValue("D$row", 'Data da Mensagem')
			->setCellValue("E$row", 'Hora da Mensagem')
			->setCellValue("F$row", 'Tempo de Retorno ADMIN')
			->setCellValue("G$row", 'Sem Retorno')
			->setCellValue("H$row", 'Data/Hora do Retorno');

		$db->query("
SELECT
	his.id,
	his.id_licitacao,
	his.data_hora,
	his.tipo,
	usr.nome AS usuario_admin,
	cli.tipo AS tipo_cliente,
	cli.id_parent,
	cli.nome AS nome_cliente,
	clidn.nome AS nome_dn,
	(SELECT data_hora FROM gelic_historico WHERE id_licitacao = his.id_licitacao AND tipo <> his.tipo AND tipo IN (1,2) AND id > his.id LIMIT 1) AS resposta
FROM
	gelic_historico AS his
	INNER JOIN gelic_licitacoes AS lic ON lic.id = his.id_licitacao
	LEFT JOIN gelic_admin_usuarios AS usr ON usr.id = his.id_sender
	LEFT JOIN gelic_clientes AS cli ON cli.id = his.id_sender
	LEFT JOIN gelic_clientes AS clidn ON clidn.id = cli.id_parent
WHERE
	his.tipo IN (1,2) AND
	his.data_hora >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	his.data_hora <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	lic.deletado = 0
ORDER BY
	his.id_licitacao, his.id");
		while ($db->nextRecord())
		{
			$intervalo = '-';
			$tempo = array("h"=>0, "m"=>0, "s"=>0);
			if ($db->f("resposta") <> '')
			{
				$data_hora_mensagem = strtotime($db->f("data_hora"));
				$data_hora_resposta = strtotime($db->f("resposta"));
				$intervalo = $data_hora_resposta - $data_hora_mensagem;
				$tempo = segundosConv($intervalo);
			}

			$tempo["h"] = str_pad($tempo["h"], 2, "0", STR_PAD_LEFT);
			$tempo["m"] = str_pad($tempo["m"], 2, "0", STR_PAD_LEFT);
			$tempo["s"] = str_pad($tempo["s"], 2, "0", STR_PAD_LEFT);

			$row += 1;

			if ($db->f("tipo") == 1)
			{
				//ADMIN
				$phpexcel->setActiveSheetIndex(0)
					->setCellValue("A$row", $db->f("id_licitacao"))
					->setCellValue("B$row", "ADMIN")
					->setCellValue("C$row", utf8_encode($db->f("usuario_admin")))
					->setCellValue("D$row", mysqlToBr(substr($db->f("data_hora"), 0, 10)))
					->setCellValue("E$row", substr($db->f("data_hora"), 11))
					->setCellValue("F$row", "-")
					->setCellValue("G$row", "")
					->setCellValue("H$row", "");
			}
			else
			{
				$ret = '';
				$tmp = $tempo["h"].':'.$tempo["m"].':'.$tempo["s"];
				$dhr = mysqlToBr(substr($db->f("resposta"), 0, 10)).' '.substr($db->f("resposta"), 11);
				if ($intervalo == '-')
				{
					$ret = 'Sem Retorno';
					$tmp = '-';
					$dhr = '';
				}

				if ($db->f("tipo_cliente") == 1)
				{
					//BO
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", $db->f("id_licitacao"))
						->setCellValue("B$row", "BO")
						->setCellValue("C$row", utf8_encode($db->f("nome_cliente")))
						->setCellValue("D$row", mysqlToBr(substr($db->f("data_hora"), 0, 10)))
						->setCellValue("E$row", substr($db->f("data_hora"), 11))
						->setCellValue("F$row", $tmp)
						->setCellValue("G$row", $ret)
						->setCellValue("H$row", $dhr);
				}
				else
				{
					//DN
					$n = $db->f("nome_cliente");
					if ($db->f("id_parent") > 0)
						$n = $db->f("nome_dn");

					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", $db->f("id_licitacao"))
						->setCellValue("B$row", "DN")
						->setCellValue("C$row", utf8_encode($n))
						->setCellValue("D$row", mysqlToBr(substr($db->f("data_hora"), 0, 10)))
						->setCellValue("E$row", substr($db->f("data_hora"), 11))
						->setCellValue("F$row", $tmp)
						->setCellValue("G$row", $ret)
						->setCellValue("H$row", $dhr);
				}				
			}
		}


		$phpexcel->getActiveSheet()->setTitle('Retorno de Mensagens');
		$phpexcel->getActiveSheet()->mergeCells("A1:H1");
		$phpexcel->getActiveSheet()->getStyle("A1:A1")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A1:A1")->getFont()->setItalic(true);
		$phpexcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
		$phpexcel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
		$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A2:H2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:H2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(14);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(54);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(26);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(16);
		$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(22);
		$phpexcel->getActiveSheet()->getStyle("A1:H$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A2:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

		if (file_exists(UPLOAD_DIR."~retorno_mensagens_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~retorno_mensagens_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~retorno_mensagens_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
