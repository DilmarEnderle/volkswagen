<?php 

require_once "include/config.php";
require_once "include/essential.php";

$gDrop = intval($_GET["d"]);
$gValue = trim($_GET["v"]);

$aReturn = array(0,'',0);
if (in_array($gDrop, array(1,2,3))) //estados
{
	$aEstados = array();
	$aEstados[] = array('AC', 'Acre');
	$aEstados[] = array('AL', 'Alagoas');
	$aEstados[] = array('AP', 'Amapá');
	$aEstados[] = array('AM', 'Amazonas');
	$aEstados[] = array('BA', 'Bahia');
	$aEstados[] = array('CE', 'Ceará');
	$aEstados[] = array('DF', 'Distrito Federal');
	$aEstados[] = array('ES', 'Espírito Santo');
	$aEstados[] = array('GO', 'Goiás');
	$aEstados[] = array('MA', 'Maranhão');
	$aEstados[] = array('MT', 'Mato Grosso');
	$aEstados[] = array('MS', 'Mato Grosso do Sul');
	$aEstados[] = array('MG', 'Minas Gerais');
	$aEstados[] = array('PA', 'Pará');
	$aEstados[] = array('PB', 'Paraíba');
	$aEstados[] = array('PR', 'Paraná');
	$aEstados[] = array('PE', 'Pernambuco');
	$aEstados[] = array('PI', 'Piauí');
	$aEstados[] = array('RJ', 'Rio de Janeiro');
	$aEstados[] = array('RN', 'Rio Grande do Norte');
	$aEstados[] = array('RS', 'Rio Grande do Sul');
	$aEstados[] = array('RO', 'Rondônia');
	$aEstados[] = array('RR', 'Roraima');
	$aEstados[] = array('SC', 'Santa Catarina');
	$aEstados[] = array('SP', 'São Paulo');
	$aEstados[] = array('SE', 'Sergipe');
	$aEstados[] = array('TO', 'Tocantins');

	for ($i=0; $i<count($aEstados); $i++)
	{
		if ($aEstados[$i][0] == $gValue)
		{
			$aReturn[1] .= '<a class="drop-box-item1 dbx" href="javascript:void(0);" onclick="selDbItem(this,\''.$aEstados[$i][0].'\','.$gDrop.');">'.$aEstados[$i][1].'</a>';
			$aReturn[2] = $i + 1;
		}
		else
		{
			$aReturn[1] .= '<a class="drop-box-item0 dbx" href="javascript:void(0);" onclick="selDbItem(this,\''.$aEstados[$i][0].'\','.$gDrop.');">'.$aEstados[$i][1].'</a>';
		}
	}
}
else if ($gDrop == 4) //prazo garantia
{
	$aPrazo = array();
	$aPrazo[] = array('1', '12 meses');
	$aPrazo[] = array('2', '24 meses');
	$aPrazo[] = array('3', '36 meses');
	$aPrazo[] = array('4', 'Outro');

	for ($i=0; $i<count($aPrazo); $i++)
	{
		if ($aPrazo[$i][0] == $gValue)
		{
			$aReturn[1] .= '<a class="drop-box-item1 dbx" href="javascript:void(0);" onclick="selDbItem(this,\''.$aPrazo[$i][1].'\','.$gDrop.');" data-value="'.$aPrazo[$i][0].'" style="width:296px;">'.$aPrazo[$i][1].'</a>';
			$aReturn[2] = $i + 1;
		}
		else
		{
			$aReturn[1] .= '<a class="drop-box-item0 dbx" href="javascript:void(0);" onclick="selDbItem(this,\''.$aPrazo[$i][1].'\','.$gDrop.');" data-value="'.$aPrazo[$i][0].'" style="width:296px;">'.$aPrazo[$i][1].'</a>';
		}
	}
}
echo json_encode($aReturn);

?>
