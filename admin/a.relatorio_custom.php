<?php

require_once "include/config.php";
require_once "include/essential.php";
require_once "../../Phpexcel-1.8.0/PHPExcel.php";

$db = new Mysql();


$phpexcel = new PHPExcel();
$phpexcel->getProperties()->setCreator("GELIC")
	->setLastModifiedBy("GELIC")
	->setTitle("Licitações perdidas com APL")
	->setSubject("GELIC")
	->setDescription("Licitações perdidas com APL")
	->setKeywords("office 2007 openxml php gelic")
	->setCategory("Relatorio");
							 
$phpexcel->getDefaultStyle()->getFont()->setName('Arial');
$phpexcel->getDefaultStyle()->getFont()->setSize(10);

$row = 1;
$phpexcel->setActiveSheetIndex(0)
	->setCellValue("A$row", 'Licitação')
	->setCellValue("B$row", 'Órgão')
	->setCellValue("C$row", 'Cidade')
	->setCellValue("D$row", 'Estado')
	->setCellValue("E$row", 'Lote')
	->setCellValue("F$row", 'Item')
	->setCellValue("G$row", 'DN que enviou')
	->setCellValue("H$row", 'APL enviada em')
	->setCellValue("I$row", 'AVE');

$phpexcel->getActiveSheet()->getStyle("A$row:I$row")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ff888888');
$phpexcel->getActiveSheet()->getStyle("A$row:I$row")->getFont()->setBold(true);
$phpexcel->getActiveSheet()->getStyle("A$row:I$row")->getFont()->getColor()->setRGB('ffffff');


$db->query("
SELECT
	lic.id, 
	lic.orgao, 
	lic.datahora_abertura,
	cid.nome AS cidade,
	cid.uf AS estado
FROM
	gelic_licitacoes AS lic
	INNER JOIN gelic_licitacoes_abas AS labas ON labas.id_licitacao = lic.id AND labas.grupo = 1
	INNER JOIN gelic_cidades AS cid ON cid.id = lic.id_cidade
	INNER JOIN gelic_modalidades AS mdl ON mdl.id = lic.id_modalidade
    LEFT JOIN gelic_historico AS his ON his.id_licitacao = lic.id AND his.tipo = 31
WHERE
	lic.deletado = 0 AND
    labas.id_aba = 10 AND
    his.id IS NULL
GROUP BY
	lic.id
ORDER BY
	lic.datahora_abertura");
while ($db->nextRecord())
{
	$db->query("
SELECT
	apl.id_licitacao,
	apl.ave,
	ahis.tipo AS apl_status,
	ahis.data_hora AS apl_status_data_hora,
	ahis.texto,
	lot.lote,
	itm.item,
	IF (cli.id_parent > 0, clip.nome, cli.nome) AS nome_dn,
	CONCAT(apl.id_licitacao,IF (cli.id_parent > 0, cli.id_parent, cli.id),apl.id_item) AS grp,
	(SELECT data_hora FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id AND tipo = 1 ORDER BY id DESC LIMIT 1) AS datah
FROM
	gelic_licitacoes_apl AS apl
	INNER JOIN gelic_clientes AS cli ON cli.id = apl.id_cliente
	LEFT JOIN gelic_clientes AS clip ON clip.id = cli.id_parent
	INNER JOIN gelic_licitacoes_itens AS itm ON itm.id = apl.id_item
	INNER JOIN gelic_licitacoes_lotes AS lot ON lot.id = itm.id_lote
	INNER JOIN gelic_licitacoes_apl_historico AS ahis ON ahis.id_apl = apl.id AND ahis.id = (SELECT MAX(id) FROM gelic_licitacoes_apl_historico WHERE id_apl = apl.id)
WHERE
	apl.id_licitacao = ".$db->f("id")." AND
	apl.id = (
		SELECT
			MAX(id)
		FROM
			gelic_licitacoes_apl
		WHERE
			id_licitacao = apl.id_licitacao AND
			id_item = apl.id_item AND
			id_cliente = apl.id_cliente
		)
ORDER BY
	datah DESC",1);
	while ($db->nextRecord(1))
	{
		$row += 1;
		$phpexcel->setActiveSheetIndex(0)
			->setCellValue("A$row", $db->f("id"))
			->setCellValue("B$row", utf8_encode($db->f("orgao")))
			->setCellValue("C$row", utf8_encode($db->f("cidade")))
			->setCellValue("D$row", $db->f("estado"))
			->setCellValue("E$row", utf8_encode($db->f("lote",1)))
			->setCellValue("F$row", utf8_encode($db->f("item",1)))
			->setCellValue("G$row", utf8_encode($db->f("nome_dn",1)))
			->setCellValue("H$row", mysqlToBr(substr($db->f("datah",1),0,10))." ".substr($db->f("datah",1),11))
			->setCellValue("I$row", utf8_encode($db->f("ave",1)));
	}
}


$phpexcel->getActiveSheet()->setTitle('Licitações perdidas com APL');
$phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
$phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
$phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
$phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
$phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
$phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
$phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);
$phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
$phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
$phpexcel->getActiveSheet()->getStyle("A1:I$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

if (file_exists(UPLOAD_DIR."~rel_custom_.xlsx"))
	unlink(UPLOAD_DIR."~rel_custom.xlsx");

$obwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
$obwriter->save(UPLOAD_DIR."~rel_custom.xlsx");

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="Licitações perdidas com APL.xlsx"'); 
header('Content-Length:'.filesize(UPLOAD_DIR."~rel_custom.xlsx"));
readfile(UPLOAD_DIR."~rel_custom.xlsx");

?>
