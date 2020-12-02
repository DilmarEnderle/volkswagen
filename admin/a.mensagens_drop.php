<?php 

require_once "include/config.php";
require_once "include/essential.php";

$oHtml = "";
if (isInside())
{
	$gDrop = intval($_GET["d"]);
	$gValue = trim($_GET["v"]);

	if ($gDrop == 0) //recipientes
	{
		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$oHtml = '<div style="height: 4px;"><!-- --></div>';
		$db = new Mysql();

		$db->query("SELECT id, tipo, nome, deletado FROM gelic_clientes WHERE tipo IN (1,2) ORDER BY tipo DESC, dn");
		while ($db->nextRecord())
		{
			if ($db->f("tipo") == 1)
			{
				$s = '<span class="drop-bo">BO</span> ';
				$n = 'BO';
			}
			else
			{
				$s = '<span class="drop-dn">DN</span> ';
				$n = 'DN';
			}

			if (in_array($db->f("id"), $a))
				$oHtml .= '<a class="drop-item1 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\'('.$n.') '.utf8_encode($db->f("nome")).'\',\'\');">'.$s.utf8_encode($db->f("nome")).'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\'('.$n.') '.utf8_encode($db->f("nome")).'\',\'\');">'.$s.utf8_encode($db->f("nome")).'</a>';

			if ($db->f("tipo") == 2)
			{
				$db->query("SELECT id, tipo, nome, deletado FROM gelic_clientes WHERE tipo = 3 AND id_parent = ".$db->f("id")." ORDER BY nome",1);
				while ($db->nextRecord(1))
				{
					if (in_array($db->f("id",1), $a))
						$oHtml .= '<a class="drop-item1 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id",1).','.$gDrop.',\'(USER DN) '.utf8_encode($db->f("nome",1)).'\',\'\');"><span class="drop-user-dn">USER DN</span> '.utf8_encode($db->f("nome",1)).'</a>';
					else
						$oHtml .= '<a class="drop-item0 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id",1).','.$gDrop.',\'(USER DN) '.utf8_encode($db->f("nome",1)).'\',\'\');"><span class="drop-user-dn">USER DN</span> '.utf8_encode($db->f("nome",1)).'</a>';
				}
			}
		}

		$db->query("SELECT id, tipo, nome, deletado FROM gelic_clientes WHERE tipo = 4 ORDER BY nome");
		while ($db->nextRecord())
		{
			if (in_array($db->f("id"), $a))
				$oHtml .= '<a class="drop-item1 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\'(REP) '.utf8_encode($db->f("nome")).'\',\'\');"><span class="drop-rep">REP</span> '.utf8_encode($db->f("nome")).'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-400" href="javascript:void(0);" onclick="selItem(this,'.$db->f("id").','.$gDrop.',\'(REP) '.utf8_encode($db->f("nome")).'\',\'\');"><span class="drop-rep">REP</span> '.utf8_encode($db->f("nome")).'</a>';
		}

		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 1) //notificacoes
	{
		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$n = array();
		$n[] = array("t"=>"0", "id"=>0, "lb"=>"ADMIN");
		$n[] = array("t"=>"1", "id"=>0, "lb"=>"LICITAÇÕES");
		$n[] = array("t"=>"2", "id"=>1, "lb"=>"Mensagens do DN");
		$n[] = array("t"=>"2", "id"=>2, "lb"=>"Mensagens do Back Office");
		$n[] = array("t"=>"2", "id"=>3, "lb"=>"Sem Interesse do DN");
		$n[] = array("t"=>"2", "id"=>4, "lb"=>"Aviso de APL Enviada");
		$n[] = array("t"=>"2", "id"=>5, "lb"=>"Aviso de APL Aprovada");
		$n[] = array("t"=>"2", "id"=>6, "lb"=>"Aviso de APL Reprovada");
		$n[] = array("t"=>"2", "id"=>7, "lb"=>"Aviso de APL Aprovação Revertida");
		$n[] = array("t"=>"2", "id"=>8, "lb"=>"Aviso de APL Reprovação Revertida");
		$n[] = array("t"=>"2", "id"=>9, "lb"=>"Licitação Prorrogada");
		$n[] = array("t"=>"2", "id"=>45, "lb"=>"Mensagens do Pregoeiro Chama");
		$n[] = array("t"=>"1", "id"=>0, "lb"=>"COMPRA DIRETA/SRP");
		$n[] = array("t"=>"2", "id"=>10, "lb"=>"Novas Solicitações");
		$n[] = array("t"=>"2", "id"=>11, "lb"=>"Mensagens do DN");
		$n[] = array("t"=>"2", "id"=>46, "lb"=>"Mensagens do Back Office");
		$n[] = array("t"=>"2", "id"=>12, "lb"=>"Aviso de APL Enviada");
		$n[] = array("t"=>"2", "id"=>13, "lb"=>"Aviso de APL Aprovada");
		$n[] = array("t"=>"2", "id"=>14, "lb"=>"Aviso de APL Reprovada");
		$n[] = array("t"=>"2", "id"=>15, "lb"=>"Aviso de APL Aprovação Revertida");
		$n[] = array("t"=>"2", "id"=>16, "lb"=>"Aviso de APL Reprovação Revertida");
		$n[] = array("t"=>"0", "id"=>0, "lb"=>"BO");
		$n[] = array("t"=>"1", "id"=>0, "lb"=>"LICITAÇÕES");
		$n[] = array("t"=>"2", "id"=>17, "lb"=>"Mensagens da Administração");
		$n[] = array("t"=>"2", "id"=>18, "lb"=>"Mensagens do DN");
		$n[] = array("t"=>"2", "id"=>19, "lb"=>"Licitação Encerrada");
		$n[] = array("t"=>"2", "id"=>20, "lb"=>"Encerramento Revertido");
		$n[] = array("t"=>"2", "id"=>21, "lb"=>"Aviso de APL Enviada");
		$n[] = array("t"=>"2", "id"=>22, "lb"=>"Término do Prazo Limite");
		$n[] = array("t"=>"1", "id"=>0, "lb"=>"COMPRA DIRETA/SRP");
		$n[] = array("t"=>"2", "id"=>23, "lb"=>"Novas Solicitações");
		$n[] = array("t"=>"2", "id"=>24, "lb"=>"Mensagens da Administração");
		$n[] = array("t"=>"2", "id"=>25, "lb"=>"Mensagens do DN");
		$n[] = array("t"=>"2", "id"=>26, "lb"=>"Aviso de APL Enviada");
		$n[] = array("t"=>"0", "id"=>0, "lb"=>"DN");
		$n[] = array("t"=>"1", "id"=>0, "lb"=>"LICITAÇÕES");
		$n[] = array("t"=>"2", "id"=>27, "lb"=>"Novas Licitações");
		$n[] = array("t"=>"2", "id"=>28, "lb"=>"Mensagens da Administração");
		$n[] = array("t"=>"2", "id"=>29, "lb"=>"Mensagens do Back Office");
		$n[] = array("t"=>"2", "id"=>30, "lb"=>"Licitação Prorrogada");
		$n[] = array("t"=>"2", "id"=>31, "lb"=>"Licitação Encerrada");
		$n[] = array("t"=>"2", "id"=>32, "lb"=>"Encerramento Revertido");
		$n[] = array("t"=>"2", "id"=>33, "lb"=>"Revertido o Desinteresse na Licitação");
		$n[] = array("t"=>"2", "id"=>34, "lb"=>"Aviso de Solicitação de ATA");
		$n[] = array("t"=>"2", "id"=>35, "lb"=>"Aviso de APL Aprovada");
		$n[] = array("t"=>"2", "id"=>36, "lb"=>"Aviso de APL Reprovada");
		$n[] = array("t"=>"2", "id"=>37, "lb"=>"Aviso de APL Aprovação Revertida");
		$n[] = array("t"=>"2", "id"=>38, "lb"=>"Aviso de APL Reprovação Revertida");
		$n[] = array("t"=>"1", "id"=>0, "lb"=>"COMPRA DIRETA/SRP");
		$n[] = array("t"=>"2", "id"=>39, "lb"=>"Mensagens da Administração");
		$n[] = array("t"=>"2", "id"=>48, "lb"=>"Mensagens do Back Office");
		$n[] = array("t"=>"2", "id"=>40, "lb"=>"Aviso de Autorização (Envio da APL)");
		$n[] = array("t"=>"2", "id"=>41, "lb"=>"Aviso de APL Aprovada");
		$n[] = array("t"=>"2", "id"=>42, "lb"=>"Aviso de APL Reprovada");
		$n[] = array("t"=>"2", "id"=>43, "lb"=>"Aviso de APL Aprovação Revertida");
		$n[] = array("t"=>"2", "id"=>44, "lb"=>"Aviso de APL Reprovação Revertida");



		$oHtml = '<div style="height: 4px;"><!-- --></div>';

		for ($i=0; $i<count($n); $i++)
		{
			if ($n[$i]["t"] == 0)
				$oHtml .= '<div style="background-color:#666666;color:#ffffff;padding-left:12px;line-height:28px;">'.$n[$i]["lb"].'</div>';
			else if ($n[$i]["t"] == 1)
				$oHtml .= '<div class="italic" style="color:#ff0000;padding-left:36px;line-height:21px;border-bottom:1px dotted #ff0000;">'.$n[$i]["lb"].'</div>';
			else
			{
				if (in_array($n[$i]["id"], $a))
					$oHtml .= '<a class="drop-item1 drp w-300" href="javascript:void(0);" onclick="selItem(this,\''.$n[$i]["id"].'\','.$gDrop.',\''.$n[$i]["lb"].'\',\'\');">'.$n[$i]["lb"].'</a>';
				else
					$oHtml .= '<a class="drop-item0 drp w-300" href="javascript:void(0);" onclick="selItem(this,\''.$n[$i]["id"].'\','.$gDrop.',\''.$n[$i]["lb"].'\',\'\');">'.$n[$i]["lb"].'</a>';
			}
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}
	else if ($gDrop == 2) //metodo
	{
		$a = array();
		if (strlen($gValue) > 0)
			$a = explode(",", $gValue);

		$m = array();
		$m[] = array("id"=>1, "lb"=>"EMAIL");
		$m[] = array("id"=>2, "lb"=>"SMS");


		$oHtml = '<div style="height: 4px;"><!-- --></div>';

		for ($i=0; $i<count($m); $i++)
		{
			if (in_array($m[$i]["id"], $a))
				$oHtml .= '<a class="drop-item1 drp w-300" href="javascript:void(0);" onclick="selItem(this,'.$m[$i]["id"].','.$gDrop.',\''.$m[$i]["lb"].'\',\'\');">'.$m[$i]["lb"].'</a>';
			else
				$oHtml .= '<a class="drop-item0 drp w-300" href="javascript:void(0);" onclick="selItem(this,'.$m[$i]["id"].','.$gDrop.',\''.$m[$i]["lb"].'\',\'\');">'.$m[$i]["lb"].'</a>';
		}
		$oHtml .= '<div style="height: 4px;"><!-- --></div>';
	}

}
echo $oHtml;

?>
