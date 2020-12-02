<?php

require_once "include/config.php";
require_once "include/essential.php";

$aReturn = array(0);
if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$pFormato = trim($_POST["formato"]);

	$db = new Mysql();

	if ($pFormato == "xlsx")
	{
		require_once "../../Phpexcel-1.8.0/PHPExcel.php";
	
		$phpexcel = new PHPExcel();
		$phpexcel->getProperties()->setCreator("GELIC")
			->setLastModifiedBy("GELIC")
			->setTitle("Usuários Cadastrados")
			->setSubject("Acessos")
			->setDescription("Usuários Cadastrados GELIC")
			->setKeywords("office 2007 openxml php gelic")
			->setCategory("Usuarios");
							 
		$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
		$phpexcel->getDefaultStyle()->getFont()->setSize(10);


		$row = 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", 'DN')
			->setCellValue("B$row", 'TIPO')
			->setCellValue("C$row", 'REGIÃO')
			->setCellValue("D$row", 'CIDADE')
			->setCellValue("E$row", 'ESTADO')
			->setCellValue("F$row", 'NOME DO USUÁRIO')
			->setCellValue("G$row", 'EMAIL')
			->setCellValue("H$row", 'DATA DO CADASTRO')
			->setCellValue("I$row", 'ÚLTIMO ACESSO');

		$aTipo = array();
		$aTipo[1] = "BO";
		$aTipo[2] = "DN";
		$aTipo[3] = "USR DN";
		$aTipo[4] = "REP";

		$db->query("
SELECT
	cli.id,
	cli.dn,
	cli.tipo,
	uf.regiao,
	cid.nome AS cidade,
	uf.uf,
	cli.nome,
	cli.email,
	(SELECT data_hora FROM gelic_log_login WHERE tipo = cli.tipo AND id_clienteusuario = cli.id ORDER BY id DESC LIMIT 1) AS ultimo_acesso,
	cli.datahora_cadastro
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	cli.id > 1 AND
	cli.deletado = 0 AND
	cli.tipo IN (1,2)
ORDER BY cli.tipo DESC, dn");
		while ($db->nextRecord())
		{
			$row += 1;
			$phpexcel->setActiveSheetIndex(0)
				->setCellValue("A$row", ($db->f("tipo") == 2) ? $db->f("dn") : '')
				->setCellValue("B$row", $aTipo[$db->f("tipo")])
				->setCellValue("C$row", utf8_encode($db->f("regiao")))
				->setCellValue("D$row", utf8_encode($db->f("cidade")))
				->setCellValue("E$row", $db->f("uf"))
				->setCellValue("F$row", utf8_encode($db->f("nome")))
				->setCellValue("G$row", $db->f("email"))
				->setCellValue("H$row", ($db->f("datahora_cadastro") == '0000-00-00 00:00:00') ? '' : mysqlToBr(substr($db->f("datahora_cadastro"),0,10)).' '.substr($db->f("datahora_cadastro"),11))
				->setCellValue("I$row", ($db->f("ultimo_acesso") == '') ? '' : mysqlToBr(substr($db->f("ultimo_acesso"),0,10)).' '.substr($db->f("ultimo_acesso"),11));



			if ($db->f("tipo") == 2)
			{
				$db->query("
SELECT
	cli.tipo,
	cli.dn,
	uf.regiao,
	cid.nome AS cidade,
	uf.uf,
	cli.nome,
	cli.email,
	(SELECT data_hora FROM gelic_log_login WHERE tipo = cli.tipo AND id_clienteusuario = cli.id ORDER BY id DESC LIMIT 1) AS ultimo_acesso,
	cli.datahora_cadastro
FROM
	gelic_clientes AS cli
	INNER JOIN gelic_cidades AS cid ON cid.id = cli.id_cidade
	INNER JOIN gelic_uf AS uf ON uf.uf = cid.uf
WHERE
	cli.id > 1 AND
	cli.deletado = 0 AND
	(
		(cli.id_parent = ".$db->f("id")." AND tipo = 3) OR
		(cli.id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = ".$db->f("id")."))
	)
ORDER BY cli.nome",1);
				while ($db->nextRecord(1))
				{
					$row += 1;
					$phpexcel->setActiveSheetIndex(0)
						->setCellValue("A$row", '')
						->setCellValue("B$row", $aTipo[$db->f("tipo",1)])
						->setCellValue("C$row", utf8_encode($db->f("regiao",1)))
						->setCellValue("D$row", utf8_encode($db->f("cidade",1)))
						->setCellValue("E$row", $db->f("uf",1))
						->setCellValue("F$row", utf8_encode($db->f("nome",1)))
						->setCellValue("G$row", $db->f("email",1))
						->setCellValue("H$row", ($db->f("datahora_cadastro",1) == '0000-00-00 00:00:00') ? '' : mysqlToBr(substr($db->f("datahora_cadastro",1),0,10)).' '.substr($db->f("datahora_cadastro",1),11))
						->setCellValue("I$row", ($db->f("ultimo_acesso",1) == '') ? '' : mysqlToBr(substr($db->f("ultimo_acesso",1),0,10)).' '.substr($db->f("ultimo_acesso",1),11));
				}
			}
		}


		$phpexcel->getActiveSheet()->setTitle('Usuários Cadastrados');
		$phpexcel->getActiveSheet()->getStyle("A1:I1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ffcecece');
		$phpexcel->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true);
		$phpexcel->getActiveSheet()->getStyle("A1:I$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$phpexcel->getActiveSheet()->getStyle("A1:I$row")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
		$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(23);
		$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
		$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(54);
		$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
		$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(23);
		$phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(23);

		if (file_exists(UPLOAD_DIR."~usuarios_cadastrados_".$sInside_id.".xlsx"))
			unlink(UPLOAD_DIR."~usuarios_cadastrados_".$sInside_id.".xlsx");

		$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
		$obwriter->save(UPLOAD_DIR."~usuarios_cadastrados_".$sInside_id.".xlsx");

		$aReturn[0] = 1; //sucesso
	}
}
echo json_encode($aReturn);

?>
