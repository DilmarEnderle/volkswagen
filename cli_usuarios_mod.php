<?php

require_once "include/config.php";
require_once "include/essential.php";

if (isInside())
{
	$sInside_id = $_SESSION[SESSION_ID];
	$sInside_tipo = $_SESSION[SESSION_TYPE];
	$sInside_parent = $_SESSION[SESSION_PARENT];

	$db = new Mysql();

	if ($sInside_tipo == 1 || $sInside_tipo == 3 || $sInside_tipo == 4) //BO, DN FILHO, REP
	{
		echo '<section>
			<div class="middle">
				<div class="lic" style="height: 340px;">
					<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
				</div>
			</div>
		</section>';
		exit;
	}


	$oRows = '';
	//carregar usuarios
	$db->query("
SELECT 
	id, 
	nome, 
	departamento, 
    email 
FROM 
	gelic_clientes 
WHERE 
	deletado = 0 AND 
	((id_parent = $sInside_id AND tipo = 3) OR id IN (SELECT id_cliente FROM gelic_clientes_acesso WHERE id_cliente_acesso = $sInside_id))
ORDER BY 
	nome");
	$dTotal_usuarios = $db->nf();
	while ($db->nextRecord())
	{
		$oRows .= '<tr>
			<td class="usr_nome"><span class="t14 bold gray-48">'.utf8_encode($db->f("nome")).'</span></td>
			<td class="usr_dep">'.utf8_encode($db->f("departamento")).'</td>
			<td class="usr_email">'.$db->f("email").'</td>
			<td class="usr_acao">
				<a href="index.php?p=cli_usuarioeditar&id='.$db->f("id").'" title="Editar"><img class="btn-editar" src="img/btn-editar.png"></a>
				<a href="javascript:void(0);" onclick="removerUsuario('.$db->f("id").',false);" title="Excluir"><img class="btn-remover" src="img/btn-remover.png"></a>
			</td>
		</tr>';
	}


	if (strlen($oRows) == 0)
	{
		$oRows = '<tr><td colspan="4" style="border: 1px solid #bebebe; border-top: 0; text-align: center; padding: 40px 0;">Nenhum usuário!<br><br>[<a href="index.php?p=cli_usuarioeditar" style="color: #0000ff;display:inline;">Criar Novo</a>]</td></tr>';
	}
	else
	{
		if ($dTotal_usuarios < MAX_USR)
			$oRows .= '<tr><td colspan="4" style="padding:10px 0;">[<a href="index.php?p=cli_usuarioeditar" style="color: #0000ff;display:inline;">Criar Novo</a>]</td></tr>';
	}

	?>

	<section>
		<div class="middle">

			<?php echo getTop(); ?>
			<?php echo getMenu(4,2); ?>

			<div class="lic">
				<h4 class="lic_tit" id="lic_titulo">Usuários</h4>
				<table cellpading="0" cellspacing="0" class="lic_list">
					<thead>
						<tr>
							<th style="text-align: left; width: 380px; border-bottom: 1px solid #bebebe;">Nome</th>
							<th style="text-align: left; width: 180px; border-bottom: 1px solid #bebebe;">Departamento</th>
							<th style="text-align: left; border-bottom: 1px solid #bebebe;">Email</th>
							<th style="border-bottom: 1px solid #bebebe; width: 60px;"></th>
						</tr>
					</thead>
					<tbody id="row-container">
						<?php echo $oRows; ?>
					</tbody>
				</table>
			</div>

			<div style="height: 100px;"><!-- gap --></div>
		</div>
	</section>


	<?php
}
else
{
	?>
	<section>
		<div class="middle">
			<div class="lic" style="height: 340px;">
				<h4 class="lic_tit" id="lic_titulo" style="color: #aa0000;">Acesso Restrito!</h4><br><br>
				<p style="color: #a6a6a6;">Se você é cliente GELIC utilize o seu login e senha para ter acesso nesta área.</p>
			</div>
		</div>
	</section>
	<?php
}

?>
