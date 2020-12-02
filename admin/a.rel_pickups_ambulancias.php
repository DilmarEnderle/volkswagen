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
		$db->query("SELECT DATE(datahora_abertura) AS data_abertura FROM gelic_licitacoes WHERE deletado = 0 ORDER BY datahora_abertura LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_fr = mysqlToUs($db->f("data_abertura"));
		else
			$pPeriodo_fr = date("m/d/Y");
	}


	//VALIDAR DATA PERIODO ATE
	if (strlen($pPeriodo_to) == 10 && isValidBrDate($pPeriodo_to))
		$pPeriodo_to = brToUs($pPeriodo_to); // mm/dd/yyyy
	else
	{
		$db->query("SELECT DATE(datahora_abertura) AS data_abertura FROM gelic_licitacoes WHERE deletado = 0 ORDER BY datahora_abertura DESC LIMIT 1");
		if ($db->nextRecord())
			$pPeriodo_to = mysqlToUs($db->f("data_abertura"));
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
			->setTitle("Licitações (PickUps & Ambulâncias)")
			->setSubject("Licitacoes")
			->setDescription("Licitacoes Pickups e Ambulancias")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Licitacoes");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);

		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A1", 'Período escolhido ('.usToBr($pPeriodo_fr).' - '.usToBr($pPeriodo_to).')');

		$row = 2;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'Licitação')
			->setCellValue("B$row", 'Órgão')
			->setCellValue("C$row", 'Número Licitação')
			->setCellValue("D$row", 'Localização')
			->setCellValue("E$row", 'Data Abertura')
			->setCellValue("F$row", 'Modalidade')
			->setCellValue("G$row", 'Instância')
			->setCellValue("H$row", 'SRP')
			->setCellValue("I$row", 'Prazo Entrega')
			->setCellValue("J$row", 'Lote')
			->setCellValue("K$row", 'Item')
			->setCellValue("L$row", 'Modelo')
			->setCellValue("M$row", 'Tipo')
			->setCellValue("N$row", 'Descrição')
			->setCellValue("O$row", 'Qtde.')
			->setCellValue("P$row", 'Valor (R$)')
			->setCellValue("Q$row", 'Transformação');

		$aInstancia = array();
		$aInstancia[0] = "";
		$aInstancia[1] = "Municipal";
		$aInstancia[2] = "Estadual";
		$aInstancia[3] = "Federal";

		$aSimnao = array('','Sim','Não');
		$aPrazoentregaproduto = array('','dias úteis','dias corridos');
		$aSimnao0_1 = array('Não','Sim');

		$aTipo_veiculos = array();
		$aTipo_veiculos[0] = '';
		$aTipo_veiculos[1] = 'Hatch Popular';
		$aTipo_veiculos[2] = 'Hatch Premium';
		$aTipo_veiculos[3] = 'Sedan Popular';
		$aTipo_veiculos[4] = 'Sedan Premium';
		$aTipo_veiculos[5] = 'Pick-up Popular';
		$aTipo_veiculos[6] = 'Pick-up Premium';
		$aTipo_veiculos[7] = 'Station Wagon';
		$aTipo_veiculos[8] = 'Não pertinente';
		$aTipo_veiculos[9] = 'Não disponível';

		$aModelos = array();
		// $aModelos[0] = '&lt;não informado&gt;';
		// $aModelos[1] = 'Amarok 2.0';
		// $aModelos[2] = 'CrossFox 1.6';
		// $aModelos[3] = 'Fox 1.0';
		// $aModelos[4] = 'Fox 1.6';
		// $aModelos[5] = 'Up! 1.0';
		// $aModelos[6] = 'Gol 1.0';
		// $aModelos[7] = 'Gol 1.6';
		// $aModelos[8] = 'Golf 1.0';
		// $aModelos[9] = 'Golf 1.4';
		// $aModelos[10] = 'Golf 1.6';
		// $aModelos[11] = 'Golf 2.0';
		// $aModelos[12] = 'Jetta 1.4';
		// $aModelos[13] = 'Jetta 2.0';
		// $aModelos[14] = 'Polo 1.0';
		// $aModelos[15] = 'Polo 1.6';
		// $aModelos[16] = 'Polo 200 TSI';
		// $aModelos[17] = 'Saveiro 1.6';
		// $aModelos[18] = 'SpaceFox 1.6';
		// $aModelos[19] = 'Voyage 1.0';
		// $aModelos[20] = 'Voyage 1.6';
		// $aModelos[21] = 'Incompatível';
		// $aModelos[22] = 'Não disponível';

		$aModelos[0] = '';
		$aModelos[1] = 'Up! 1.0';
		$aModelos[2] = 'Gol 1.0';
		$aModelos[3] = 'Gol 1.6';
		$aModelos[4] = 'Fox 1.0';
		$aModelos[5] = 'Fox 1.6';
		$aModelos[6] = 'Golf 1.0';
		$aModelos[7] = 'Golf 1.4';
		$aModelos[8] = 'Golf 1.6';
		$aModelos[9] = 'Golf 2.0';
		$aModelos[10] = 'Voyage 1.0';
		$aModelos[11] = 'Voyage 1.6';
		$aModelos[12] = 'Jetta 1.4';
		$aModelos[13] = 'Jetta 2.0';
		$aModelos[14] = 'Saveiro 1.6';
		$aModelos[15] = 'Amarok 2.0';
		$aModelos[16] = 'CrossFox 1.6';
		$aModelos[17] = 'SpaceFox 1.6';
		$aModelos[20] = 'Polo 1.0';
		$aModelos[21] = 'Polo 1.6';
		$aModelos[22] = 'Polo 200';
		$aModelos[18] = 'Incompatível';
		$aModelos[19] = 'Não disponível';

		$db->query("
SELECT
	lic.id,
	lic.orgao,
	lic.numero,
	cid.nome AS nome_cidade,
	cid.uf,
	lic.datahora_abertura,
	mdl.nome AS nome_modalidade,
	lic.instancia,
	lic.srp,
	lic.prazo_entrega_produto,
	lic.prazo_entrega_produto_uteis,
    lot.lote,
    itm.item,
    itm.id_modelo,
    itm.id_tipo_veiculo,
    itm.descricao,
    itm.quantidade,
    itm.valor,
	itm.transformacao
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
	LEFT JOIN gelic_licitacoes_itens AS itm ON itm.id_licitacao = lic.id
    LEFT JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
WHERE
	lic.datahora_abertura >= '".usToMysql($pPeriodo_fr)." 00:00:00' AND
	lic.datahora_abertura <= '".usToMysql($pPeriodo_to)." 23:59:59' AND
	lic.deletado = 0 AND
	(itm.id_modelo IN (14,15) OR itm.modelo = 'Ambulância')
ORDER BY
	lic.id DESC, lot.lote, itm.item");
		while ($db->nextRecord())
		{
			$dId_licitacao = $db->f("id");
			$dOrgao = utf8_encode($db->f("orgao"));
			$dNumero = utf8_encode($db->f("numero"));
			$dLocalizacao = utf8_encode($db->f("nome_cidade").' - '.$db->f("uf"));
			$dData_abertura = mysqlToBr(substr($db->f("datahora_abertura"), 0, 10));
			$dModalidade = utf8_encode($db->f("nome_modalidade"));
			$dInstancia = $aInstancia[$db->f("instancia")];
			$dSrp = $aSimnao[$db->f("srp")];
			$dPrazo_entrega = $db->f("prazo_entrega_produto")." ".$aPrazoentregaproduto[$db->f("prazo_entrega_produto_uteis")];
			$dTransformacao = $aSimnao0_1[$db->f("transformacao")];

			if ($db->f("lote") <> '')
			{
				$dLote = utf8_encode($db->f("lote"));
				$dItem = utf8_encode($db->f("item"));
				$dModelo = $aModelos[$db->f("id_modelo")];
				$dTipo = $aTipo_veiculos[$db->f("id_tipo_veiculo")];
				$dDescricao = utf8_encode($db->f("descricao"));
				$dQuantidade = intval($db->f("quantidade"));
				if ($dQuantidade == 0)
					$dQuantidade = 1;
				if ($db->f("valor") == 0)
					$dValor = '';
				else
					$dValor = number_format($db->f("valor"),2,",",".");
			}
			else
			{
				$dLote = '';
				$dItem = '';
				$dModelo = '';
				$dTipo = '';
				$dDescricao = '';
				$dQuantidade = '';
				$dValor = '';
			}

			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", $dId_licitacao)
				->setCellValue("B$row", $dOrgao)
				->setCellValue("C$row", $dNumero)
				->setCellValue("D$row", $dLocalizacao)
				->setCellValue("E$row", $dData_abertura)
				->setCellValue("F$row", $dModalidade)
				->setCellValue("G$row", $dInstancia)
				->setCellValue("H$row", $dSrp)
				->setCellValue("I$row", $dPrazo_entrega)
				->setCellValue("J$row", $dLote)
				->setCellValue("K$row", $dItem)
				->setCellValue("L$row", $dModelo)
				->setCellValue("M$row", $dTipo)
				->setCellValue("N$row", $dDescricao)
				->setCellValue("O$row", $dQuantidade)
				->setCellValue("P$row", $dValor)
				->setCellValue("Q$row", $dTransformacao);
		}


		$phpexcel->getActiveSheet()->setTitle('PickUps & Ambulâncias');
		$phpexcel->getActiveSheet()->mergeCells("A1:I1");
		$phpexcel->getActiveSheet()->getStyle("A1:A1")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A1:A1")->getFont()->setItalic(true);
		$phpexcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
		$phpexcel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
		$phpexcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("A2:Q2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A2:Q2")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(24);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(11);
		$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
		$phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
		$phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(11);
		$phpexcel->getActiveSheet()->getColumnDimension('K')->setWidth(11);
		$phpexcel->getActiveSheet()->getColumnDimension('L')->setWidth(21);
		$phpexcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
		$phpexcel->getActiveSheet()->getColumnDimension('N')->setWidth(34);
		$phpexcel->getActiveSheet()->getColumnDimension('O')->setWidth(11);
		$phpexcel->getActiveSheet()->getColumnDimension('P')->setWidth(19);
		$phpexcel->getActiveSheet()->getColumnDimension('Q')->setWidth(19);
		$phpexcel->getActiveSheet()->getStyle("A2:A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("B2:B$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("C2:C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("D2:D$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("E2:E$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("F2:F$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("G2:G$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("H2:H$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("I2:I$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("J2:J$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("K2:K$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("L2:L$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("M2:M$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("N2:N$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("O2:O$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("P2:P$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$phpexcel->getActiveSheet()->getStyle("A1:Q$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$phpexcel->getActiveSheet()->getStyle("Q2:Q$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		if (file_exists(UPLOAD_DIR."~pickups_ambulancias_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~pickups_ambulancias_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~pickups_ambulancias_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
