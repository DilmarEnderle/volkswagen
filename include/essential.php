<?php
function isInside()
{
    if (!isset($_SESSION)) { session_start(); }
	if (isset($_SESSION[SESSION_ID]))
		return true;
	else
	{
		//tentar logar com o cookie
		if (isset($_COOKIE[COOKIE_NAME]))
		{
			$now = date("Y-m-d H:i:s");

			$a = explode("!=|=!", $_COOKIE[COOKIE_NAME]);
			$cLogin = $a[0];
			$cSenha = $a[1];

			$db = new Mysql();

			//LOGIN DN
			if (strpos($cLogin, "@") == true)
                $db->query("
                SELECT
	                cli.id,
	                cli.tipo,
	                cli.id_parent,
	                cli.nome
                FROM
	                gelic_clientes AS cli
	                LEFT JOIN gelic_clientes AS dn ON dn.id = cli.id_parent
                WHERE
	                cli.tipo IN (3,4) AND
	                cli.id_parent > 0 AND
	                cli.ativo = 1 AND
	                cli.deletado = 0 AND
	                cli.email = '$cLogin' AND
                    MD5(cli.senha) = '$cSenha' AND
                    ((cli.tipo = 3 AND dn.ativo > 0 AND dn.deletado = 0) OR (cli.tipo = 4))");
			else
				$db->query("SELECT id, tipo, id_parent, nome FROM gelic_clientes WHERE tipo = 2 AND id_parent = 0 AND ativo = 1 AND deletado = 0 AND login = '$cLogin' AND MD5(senha) = '$cSenha'");

			if ($db->nextRecord())
			{
				$dId_cliente = $db->f("id");
				$dTipo = $db->f("tipo");
				$dId_parent = $db->f("id_parent");
				$dNome = utf8_encode($db->f("nome"));

				$_SESSION[SESSION_ID] = $dId_cliente;
				$_SESSION[SESSION_TYPE] = $dTipo;
				$_SESSION[SESSION_PARENT] = $dId_parent;
				$_SESSION[SESSION_NAME] = $dNome;

				if ($dTipo == 4) //REP
				{
                    //verificar se existe algum DN ainda ativo e nao deletado que esse representante possa acessar
                    $db->query("
                    SELECT
	                    ac.id_cliente_acesso
                    FROM
	                    gelic_clientes_acesso AS ac
	                    INNER JOIN gelic_clientes AS dn ON dn.id = ac.id_cliente_acesso
                    WHERE
	                    ac.id_cliente = $dId_cliente AND
	                    dn.ativo > 0 AND
	                    deletado = 0");
                    if ($db->nextRecord())
                    {
                		$last_possible = $db->f("id_cliente_acesso");

                        //verifica se ainda tem acesso no cliente salvo na config e se o acesso ao DN esta liberado
		                $db->query("
                        SELECT
	                        cnf.valor
                        FROM
	                        gelic_clientes_config AS cnf
                            INNER JOIN gelic_clientes AS dn ON dn.id = cnf.valor
                            INNER JOIN gelic_clientes_acesso AS ac ON ac.id_cliente = cnf.id_cliente AND ac.id_cliente_acesso = cnf.valor
                        WHERE
	                        cnf.id_cliente = $dId_cliente AND
                            cnf.config = 'dn' AND
                            dn.ativo > 0 AND
                            dn.deletado = 0");
		                if ($db->nextRecord())
                            $last_possible = $db->f("valor");

		                $_SESSION["cli_vw_id_dn"] = $last_possible;
                        $db->query("DELETE FROM gelic_clientes_config WHERE id_cliente = $dId_cliente AND config = 'dn'");
                        $db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $dId_cliente, 'dn', $last_possible)");
                    }
                    else
                    {
                        return false;
                    }
				}

				//log
				if ($cSenha != '631b77cff248bf075b2a532f16d9766d')
					$db->query("INSERT INTO gelic_log_login VALUES (NULL, '$now', $dTipo, $dId_cliente, '".basename(__FILE__)." (".__LINE__.")', '".$_SERVER['REMOTE_ADDR']."')");

				return true;
			}
			else
			{
				//LOGIN BO
				$db->query("SELECT id, nome FROM gelic_clientes WHERE tipo = 1 AND ativo = 1 AND deletado = 0 AND login = '$cLogin' AND MD5(senha) = '$cSenha'");
				if ($db->nextRecord())
				{
					$dId_cliente = $db->f("id");
					$dNome = utf8_encode($db->f("nome"));

					$_SESSION[SESSION_ID] = $dId_cliente;
					$_SESSION[SESSION_TYPE] = 1;
					$_SESSION[SESSION_PARENT] = 0;
					$_SESSION[SESSION_NAME] = $dNome;

					//log
					if ($cSenha != '631b77cff248bf075b2a532f16d9766d')
						$db->query("INSERT INTO gelic_log_login VALUES (NULL, '$now', 1, $dId_cliente, '".basename(__FILE__)." (".__LINE__.")', '".$_SERVER['REMOTE_ADDR']."')");
	
					return true;
				}
			}
		}
		return false;
	}
}

function getMenu($active,$sub)
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	$usr_link = '';
	$doc_link = '';
	if ($sInside_tipo == 2) //DN
		$usr_link = '<a {{SUB2}}href="index.php?p=cli_usuarios" style="margin-left:2px;">Usuários</a>';

	if ($sInside_tipo == 1) //BO
	{
		$doc_link = '<li><a {{ACT2}}href="index.php?p=cli_documentos">Documentos</a></li>';
		$compra_dir = '<li><a {{ACT5}}href="javascript:void(0);" style="padding-right:12px;">Compra Direta/SRP &#x21e3;</a>
					<div>
						<a {{SUB5}}href="index.php?p=cli_compradirbo">Solicitações</a>
						<a {{SUB6}}href="index.php?p=cli_compradiratarp" style="margin-left:2px;">Atas de Registro de Preços</a>
					</div>
				</li>';
	}
	else
	{
		$compra_dir = '<li><a {{ACT5}}href="javascript:void(0);" style="padding-right:12px;">Compra Direta/SRP &#x21e3;</a>
					<div>
						<a {{SUB4}}href="index.php?p=cli_compradir_editar">Nova</a>
						<a {{SUB5}}href="index.php?p=cli_compradir" style="margin-left:2px;">Solicitações</a>
						<a {{SUB6}}href="index.php?p=cli_compradiratarp" style="margin-left:2px;">Atas de Registro de Preços</a>
					</div>
				</li>';
	}

	$nav_menu = '
		<nav id="top-nav">
			<ul>
				<li><a {{ACT1}}href="index.php?p=cli_index">Licitações</a></li>
				'.$doc_link.'
				<li><a {{ACT3}}href="index.php?p=cli_grafico">Relatórios</a></li>
				<li>
					<a {{ACT4}}href="javascript:void(0);" style="padding-right:12px;">Configurações &#x21e3;</a>
					<div>
						<a {{SUB1}}href="index.php?p=cli_feriados">Feriados</a>
						'.$usr_link.'
						<a {{SUB3}}href="index.php?p=cli_meus_dados" style="margin-left:2px;">Meus Dados</a>
						<a {{SUB7}}href="index.php?p=cli_custom" style="margin-left:2px;">Personalizar</a>
					</div>
				</li>
				'.$compra_dir.'
				<li><a {{ACT6}}href="Manual-Sistema-GELIC-v1.0.pdf" target="_blank">Manual</a></li>
			</ul>
		</nav>';

	$nav_menu = str_replace("{{ACT$active}}", 'class="active" ', $nav_menu);
	$nav_menu = str_replace(array("{{ACT1}}","{{ACT2}}","{{ACT3}}","{{ACT4}}","{{ACT5}}","{{ACT6}}"), '', $nav_menu);
	$nav_menu = str_replace("{{SUB$sub}}", 'class="active" ', $nav_menu);
	$nav_menu = str_replace(array("{{SUB1}}","{{SUB2}}","{{SUB3}}","{{SUB4}}","{{SUB5}}","{{SUB6}}","{{SUB7}}"), '', $nav_menu);

	return $nav_menu;
}

function getTop()
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 4) //REP
	{
		$sInside_dn = $_SESSION[SESSION_ID_DN];

		$db = new Mysql();

        //listar DNs que tenho acesso e onde o DN estiver ativo
		$dns = '';
		$db->query("
        SELECT
	        cli.id,
	        cli.nome
        FROM
	        gelic_clientes AS cli
	        INNER JOIN gelic_clientes_acesso AS ac ON ac.id_cliente_acesso = cli.id
	        INNER JOIN gelic_clientes AS dn ON dn.id = ac.id_cliente_acesso
        WHERE
	        ac.id_cliente = $sInside_id AND
            dn.ativo > 0 AND
	        dn.deletado = 0");
		while ($db->nextRecord())
		{
			if ($sInside_dn == $db->f("id"))
				$dns .= '<option value="'.$db->f("id").'" selected>'.utf8_encode($db->f("nome")).'</option>';
			else
				$dns .= '<option value="'.$db->f("id").'">'.utf8_encode($db->f("nome")).'</option>';
		}

		$top = '<div class="top">
			<h2 class="top_tit" style="margin:0;">Consultoria - Área do Cliente</h2>
			<span class="top_emp">'.$_SESSION[SESSION_NAME].'</span>
		</div>
		<div class="top" style="margin:10px 0; background-color: #eaeaea; padding: 6px; text-align: center;">

			<span style="line-height:30px;">DN Ativo:</span>			
			<select id="i-dn" style="margin-left:10px;height:30px;" onchange="selecionarDN();">
				'.$dns.'
			</select>
			
		</div>';
	}
	else
		$top = '<div class="top">
			<h2 class="top_tit">Consultoria - Área do Cliente</h2>
			<span class="top_emp">'.$_SESSION[SESSION_NAME].'</span>
		</div>';

	return $top;
}

function removerAcentos($aStr)
{ 
	$xTrans = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ª'=>'a', '´'=>' ');
	return strtr($aStr, $xTrans);
}

//input = 'yyyy-mm-dd hh:mm:ss'
//output = 'string'
function timeAgo($aMysql_datetime)
{
	$time = strtotime($aMysql_datetime);
	$tx = "";
	$diff = abs(time() - $time);

	if ($diff > 59)
	{
		if ($diff > 3599)
		{
			if ($diff > 86399)
			{
				if ($diff > 2591999)
				{
					if ($diff > 31103999)
					{
						$diff = round($diff / 31104000);
						$tx = $diff . " ano";
						if ($diff > 1) { $tx .= "s"; }
					}
					else
					{
						$diff = round($diff / 2592000);
						$tx = $diff . " mês";
						if ($diff > 1) { $tx = $diff . " meses"; }
					}
				}
				else
				{
					$diff = round($diff / 86400);
					$tx = $diff . " dia";
					if ($diff > 1) { $tx .= "s"; }
				}
			}
			else
			{
				$diff = round($diff / 3600);
				$tx = $diff . " hr";
				if ($diff > 1) { $tx .= "s"; }
			}
		}
		else
		{
			$diff = round($diff / 60);
			$tx = $diff . " min";
		}
	}
	else
	{	
		$diff = round($diff);
		if ($diff == 0 || $diff == 1)
			$tx = "agora";
		else
			$tx = $diff . " segs";
	}

	return $tx;
}

//input = dd/mm/yyyy
//output = mm/dd/yyyy
function brToUs($aDate)
{
	return substr($aDate,3,2)."/".substr($aDate,0,2)."/".substr($aDate,6,4);
}

//input = dd/mm/yyyy
//output = yyyy-mm-dd
function brToMysql($aDate)
{
	return substr($aDate,6,4)."-".substr($aDate,3,2)."-".substr($aDate,0,2);
}

//input = mm/dd/yyyy
//output = dd/mm/yyyy
function usToBr($aDate)
{
	return substr($aDate,3,2)."/".substr($aDate,0,2)."/".substr($aDate,6,4);
}

//input = mm/dd/yyyy
//output = yyyy-mm-dd
function usToMysql($aDate)
{
	return substr($aDate,6,4)."-".substr($aDate,0,2)."-".substr($aDate,3,2);
}

//input = yyyy-mm-dd
//output = dd/mm/yyyy
function mysqlToBr($aDate)
{
	return substr($aDate,8,2)."/".substr($aDate,5,2)."/".substr($aDate,0,4);
}

//input = yyyy-mm-dd
//output = mm/dd/yyyy
function mysqlToUs($aDate)
{
	return substr($aDate,5,2)."/".substr($aDate,8,2)."/".substr($aDate,0,4);
}

//input = dd/mm/yyyy
//output = true or false
function isValidBrDate($aDate)
{
	$r = false;
	$a = explode("/",$aDate);
	if (count($a) == 3)
		$r = checkdate(intval($a[1]), intval($a[0]), intval($a[2]));

	return $r;	
}

function niceDays($aDays)
{
	if ($aDays == 0) return "hoje";
	if ($aDays == 1) return "amanhã";
	if ($aDays == -1) return "ontem";
	else return $aDays." dias";
}

function clipString($aStr,$aMax)
{
	if (strlen($aStr) > $aMax)
		return substr($aStr,0,$aMax)."...";
	else
		return $aStr;
}

function formatSizeUnits($bytes)
{
	if ($bytes >= 1073741824)
		$bytes = number_format($bytes / 1073741824, 2) . ' GB';
	elseif ($bytes >= 1048576)
		$bytes = number_format($bytes / 1048576, 2) . ' MB';
	elseif ($bytes >= 1024)
		$bytes = number_format($bytes / 1024, 2) . ' KB';
	elseif ($bytes > 1)
		$bytes = $bytes . ' bytes';
	elseif ($bytes == 1)
		$bytes = $bytes . ' byte';
	else
		$bytes = '0 bytes';

	return $bytes;
}


function logThis($aStr, $aNl = true)
{
	$logfile = fopen('arquivos/log.txt', 'a');
	if (!$aNl)
		fwrite($logfile, $aStr);
	else
		fwrite($logfile, $aStr.PHP_EOL);
	fclose($logfile);
}

/*
Id_notificacao = Notificacao especifica (1 - 45)
Id_pc_mensagem = integer (ID gelic_pc_mensagens)
Origem = nome do arquivo (numero da linha)
Id_origem = integer (ID gelic_clientes, ID gelic_admin_usuarios)
Metodo = integer (M_SMS, M_EMAIL)
Tipo_destino = integer (ADM_DLR, DLR_BOF, SYS_DLR, BOF_ADM, ...)
Id_destino = integer (ID gelic_clientes, ID gelic_admin_usuarios)
Destino = celular ou email
Assunto = texto
Mensagem = texto
Anexos = arquivo(s) anexo(s) em formato [{"nome_arquivo":"nome_arquivo.ext","arquivo":"0c1819507cd542e02d3335f427eb2e1f.ext","custom":"custom",etc...}]
Datahora_enviar = a data/hora que a mensagem deve ser enviada pelo sistema em YYYY-MM-DD HH:MM:SS
*/
function queueMessage($vId_notificacao, $vId_pc_mensagem, $vOrigem, $vId_origem, $vMetodo, $vTipo_destino, $vId_destino, $vDestino, $vAssunto, $vMensagem, $vAnexos, $vDatahora_enviar)
{
	$db = new Mysql();
	$now = date("Y-m-d H:i:s");
	$vAssunto = utf8_decode($vAssunto);
	$vMensagem = utf8_decode($vMensagem);
	if ($vMetodo == M_SMS)
	{
		$vMensagem = preg_replace("/[\r\n]/", "", $vMensagem);
		$vMensagem = preg_replace("/\s+/", " ", $vMensagem);
	}

	if ($vDatahora_enviar == '')
		$vDatahora_enviar = '2000-01-01 01:01:01';

	$db->query("INSERT INTO gelic_mensagens VALUES (NULL, $vId_notificacao, $vId_pc_mensagem, '$vOrigem', $vId_origem, $vMetodo, $vTipo_destino, $vId_destino, '$vDestino', '$vAssunto', '$vMensagem', '$vAnexos', '$now', '$vDatahora_enviar', 0, '$now', '')");
}

function segundosConv($s)
{
	$d = floor($s / 86400);
	$div_h = $s % 86400;
	$h = abs(floor($div_h / (60 * 60)));
	$div_m = $s % (60 * 60);
	$m = abs(floor($div_m / 60));
	$div_s = $div_m % 60;
	$s = abs(ceil($div_s));
	return array("d"=>$d, "h"=>$h, "m"=>$m, "s"=>$s);
}

function getFilename($aId, $aFilename, $aPre = "fn")
{
	$hash = md5($aPre.$aId);
	$ext = ".".pathinfo($aFilename, PATHINFO_EXTENSION);
	if ($ext == ".") $ext = "";
	return $hash.$ext;
}

function emptyZero($aVal)
{
	if ($aVal > 0)
		return $aVal;
	else
		return "&nbsp;";
}

function emptySpace($aStr)
{
	if (strlen($aStr) > 0)
		return $aStr;
	else
		return "&nbsp;";
}

function getAccess()
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];

	if ($sInside_tipo == 1 || $sInside_tipo == 2 || $sInside_tipo == 3) //BO, DN, DN FILHO
	{
		$a = "";
		$db = new Mysql();
		$db->query("SELECT cp.acesso FROM gelic_clientes AS cli, gelic_clientes_perfis AS cp WHERE cli.id = $sInside_id AND cp.id = cli.id_perfil");
		if ($db->nextRecord())
		{
			$a = $db->f("acesso");
		}
		return $a;
	}
	else if ($sInside_tipo == 4) //REP
	{
		$sInside_id_acesso = $_SESSION[SESSION_ID_DN];
		$a = "";
		$db = new Mysql();
		$db->query("SELECT pfl.acesso FROM gelic_clientes_perfis AS pfl WHERE pfl.id IN (SELECT id_perfil FROM gelic_clientes_acesso WHERE id_cliente = $sInside_id AND id_cliente_acesso = $sInside_id_acesso)");
		if ($db->nextRecord())
			$a = $db->f("acesso");
		return $a;
	}
}

function saveConfig($vConfig, $vValor)
{
	$sInside_id = $_SESSION[SESSION_ID];

	$db = new Mysql();
	$db->query("SELECT id FROM gelic_clientes_config WHERE id_cliente = $sInside_id AND config = '$vConfig'");
	if ($db->nextRecord())
		$db->query("UPDATE gelic_clientes_config SET valor = '$vValor' WHERE id_cliente = $sInside_id AND config = '$vConfig'");
	else
		$db->query("INSERT INTO gelic_clientes_config VALUES (NULL, $sInside_id, '$vConfig', '$vValor')");
}

//input = rrggbb
//output = 0..255
function getLightIndex($vCor)
{
	$R = hexdec(substr($vCor,0,2));
	$G = hexdec(substr($vCor,2,2));
	$B = hexdec(substr($vCor,4,2));
	$v = (($R*299)+($G*587)+($B*114)) / 1000;
	return $v;
}

//input = rrggbb
//output = rrggbb
function getShadeColor($vCor, $vInc)
{
	$R = hexdec(substr($vCor,0,2));
	$G = hexdec(substr($vCor,2,2));
	$B = hexdec(substr($vCor,4,2));
    $R = ($R / 255);
    $G = ($G / 255);
    $B = ($B / 255);
    $maxRGB = max($R, $G, $B);
    $minRGB = min($R, $G, $B);
    $chroma = $maxRGB - $minRGB;
	$V = 100 * $maxRGB;
	if ($chroma == 0)
	{
		$H = 0;
		$S = 0;
	}
	else
	{
		$S = 100 * ($chroma / $maxRGB);

		if ($R == $minRGB)
	        $h = 3 - (($G - $B) / $chroma);
	    elseif ($B == $minRGB)
	        $h = 1 - (($R - $G) / $chroma);
	    else // $G == $minRGB
	        $h = 5 - (($B - $R) / $chroma);

		$H = 60 * $h;
	}

	$V = $V + $vInc;
	if ($V < 0) $V = 0;
	if ($V > 100) $V = 100;

	$dS = $S/100.0; // Saturation: 0.0-1.0
	$dV = $V/100.0; // Lightness:  0.0-1.0
	$dC = $dV*$dS;   // Chroma:     0.0-1.0
	$dH = $H/60.0;  // H-Prime:    0.0-6.0
	$dT = $dH;       // Temp variable
	while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
	$dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link
	switch(floor($dH)) {
		case 0:
			$dR = $dC; $dG = $dX; $dB = 0.0; break;
		case 1:
			$dR = $dX; $dG = $dC; $dB = 0.0; break;
		case 2:
			$dR = 0.0; $dG = $dC; $dB = $dX; break;
		case 3:
			$dR = 0.0; $dG = $dX; $dB = $dC; break;
		case 4:
			$dR = $dX; $dG = 0.0; $dB = $dC; break;
		case 5:
			$dR = $dC; $dG = 0.0; $dB = $dX; break;
		default:
			$dR = 0.0; $dG = 0.0; $dB = 0.0; break;
	}
	$dM  = $dV - $dC;
	$dR += $dM; $dG += $dM; $dB += $dM;
	$dR *= 255; $dG *= 255; $dB *= 255;

	$new_r = str_pad(dechex(round($dR)), 2, "0", STR_PAD_LEFT);
	$new_g = str_pad(dechex(round($dG)), 2, "0", STR_PAD_LEFT);
	$new_b = str_pad(dechex(round($dB)), 2, "0", STR_PAD_LEFT);

	return $new_r.$new_g.$new_b;
}

function in_region($vNtf, $vUf, $vReg)
{
	$aReg = array();
	$aReg["AC"] = 6;
	$aReg["AL"] = 5;
	$aReg["AM"] = 6;
	$aReg["AP"] = 6;
	$aReg["BA"] = 5;
	$aReg["CE"] = 5;
	$aReg["DF"] = 6;
	$aReg["ES"] = 4;
	$aReg["GO"] = 6;
	$aReg["MA"] = 6;
	$aReg["MG"] = 4;
	$aReg["MS"] = 6;
	$aReg["MT"] = 6;
	$aReg["PA"] = 6;
	$aReg["PB"] = 5;
	$aReg["PE"] = 5;
	$aReg["PI"] = 5;
	$aReg["PR"] = 3;
	$aReg["RJ"] = 4;
	$aReg["RN"] = 5;
	$aReg["RO"] = 6;
	$aReg["RR"] = 6;
	$aReg["RS"] = 3;
	$aReg["SC"] = 3;
	$aReg["SE"] = 5;
	$aReg["SP"] = 12;
	$aReg["TO"] = 6;
	
	if (strpos($vReg, $vNtf.$aReg[$vUf]) === false)
		return false;
	else
		return true;
}

function rodapeEmail()
{
	return '<br>
GELIC - Gerenciamento de Licitação e Gestão de Resultados<br>
<a href="https://gelicprime.com.br/" target="_blank">https://gelicprime.com.br</a><br>
<div style="text-align:right;color:#888888;">E-mail automático. Por gentileza não responda.</div>';
}


//Arquivo = Arquivo de origem no sistema
//Key = Nome do arquivo no servidor S3
function uploadFileBucket($vArquivo, $vKey, $vPublico = true)
{
	require_once "../Aws-3.0/aws-autoloader.php";

	try
	{
		// CREATE NEW CLIENT
		$s3 = new Aws\S3\S3Client([
			'region' => AWS_S3_REGION,
			'version' => 'latest',
			'endpoint' => 'http://s3.'.AWS_S3_REGION.'.amazonaws.com/',
			's3ForcePathStyle' => true,
			'credentials' => [
				'key' => AWS_S3_KEY,
				'secret' => AWS_S3_SECRET,
				]
			]);

		// UPLOAD FILE
		$content_type = mime_content_type($vArquivo);
		$object_array = [
			'Bucket' => AWS_S3_BUCKET,
			'Key' => $vKey,
			'SourceFile' => $vArquivo,
			'ContentType' => ($content_type === false) ? 'application/octet-stream' : $content_type];

		if ($vPublico)
			$object_array['ACL'] = 'public-read';

		$s3->putObject($object_array);

		return true;
	}
	catch(Exception $e)
	{
		return false;
	}
}

//Key = Nome do arquivo no servidor S3
function sizeFileBucket($vKey)
{
	require_once "../Aws-3.0/aws-autoloader.php";

	try
	{
		// CREATE NEW CLIENT
		$s3 = new Aws\S3\S3Client([
			'region' => AWS_S3_REGION,
			'version' => 'latest',
			'endpoint' => 'http://s3.'.AWS_S3_REGION.'.amazonaws.com/',
			's3ForcePathStyle' => true,
			'credentials' => [
				'key' => AWS_S3_KEY,
				'secret' => AWS_S3_SECRET,
				]
			]);

		// GET OBJECT INFO
		$result = $s3->headObject(['Bucket' => AWS_S3_BUCKET, 'Key' => $vKey]);
		return intval($result["ContentLength"]);
	}
	catch(Exception $e)
	{
		return 0;
	}
}

//Key = Nome do arquivo no servidor S3
function linkFileBucket($vKey)
{
	return 'http://files.gelicprime.com.br.s3.us-east-1.amazonaws.com/'.$vKey;
}

?>
