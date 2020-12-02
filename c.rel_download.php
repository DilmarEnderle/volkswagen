<?php
	
require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];

	$gRel = "";
	$gFormato = "";
	if (isset($_GET["rel"])) $gRel = trim($_GET["rel"]);
	if (isset($_GET["formato"])) $gFormato = trim($_GET["formato"]);

	$now = date("Ymd_His");
	$f = array("xlsx"=>".xlsx", "pdf"=>".pdf");
	$a = array();

	$a["r1_1"] = array("tmp"=>UPLOAD_DIR."~bo_rel_1_1_$sInside_id", "filename"=>"Total_de_DNs_$now");
	$a["r1_2"] = array("tmp"=>UPLOAD_DIR."~bo_rel_1_2_$sInside_id", "filename"=>"Quantos_já_acessaram_$now");
	$a["r1_3"] = array("tmp"=>UPLOAD_DIR."~bo_rel_1_3_$sInside_id", "filename"=>"Novos_acessos_$now");
	$a["r1_4"] = array("tmp"=>UPLOAD_DIR."~bo_rel_1_4_$sInside_id", "filename"=>"Por_mês_e_acumulado_do_ano_$now");
	$a["r2_1"] = array("tmp"=>UPLOAD_DIR."~bo_rel_2_1_$sInside_id", "filename"=>"Quantidade_de_acessos_por_mês_$now");
	$a["r2_2"] = array("tmp"=>UPLOAD_DIR."~bo_rel_2_2_$sInside_id", "filename"=>"Quantidade_de_acessos_por_ano_$now");
	$a["r2_3"] = array("tmp"=>UPLOAD_DIR."~bo_rel_2_3_$sInside_id", "filename"=>"Volume_de_acessos_por_DN_$now");
	$a["r2_4"] = array("tmp"=>UPLOAD_DIR."~bo_rel_2_4_$sInside_id", "filename"=>"Volume_de_acessos_por_região_$now");
	$a["r3_1"] = array("tmp"=>UPLOAD_DIR."~bo_rel_3_1_$sInside_id", "filename"=>"APLs_enviadas_por_mês_$now");
	$a["r4_1"] = array("tmp"=>UPLOAD_DIR."~bo_rel_4_1_$sInside_id", "filename"=>"Quantitativo_total_de_processos_estaduais_federais_e_municipais_$now");
	$a["r4_4"] = array("tmp"=>UPLOAD_DIR."~bo_rel_4_4_$sInside_id", "filename"=>"Licitações_e_veículos_totais_$now");
	$a["r5_1"] = array("tmp"=>UPLOAD_DIR."~bo_rel_5_1_$sInside_id", "filename"=>"Relatório_por_status_licitações_e_veículos_$now");

	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$a[$gRel]["filename"].$f[$gFormato].'"'); 
	header('Content-Length:'.filesize($a[$gRel]["tmp"].$f[$gFormato]));
	readfile($a[$gRel]["tmp"].$f[$gFormato]);
}

?>
