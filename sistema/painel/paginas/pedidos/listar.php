<?php
require_once("../../../conexao.php");
$tabela = 'vendas';
$data_hoje = date('Y-m-d');

$status = '%' . @$_POST['status'] . '%';
$ult_ped = @$_POST['ult_pedido'];

$total_vendas = 0;


//TOTAIS DOS PEDIDOS
$query = $pdo->query("SELECT * FROM $tabela where status = 'Iniciado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ini = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Aceito'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ace = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Preparando'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_prep = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status = 'Entrega'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_ent = @count($res);

$query = $pdo->query("SELECT * FROM $tabela where status != 'Finalizado' and status != 'Cancelado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_pedidos = @count($res);


$query = $pdo->query("SELECT * FROM $tabela where status != 'Finalizado' and status != 'Cancelado' order by id desc limit 1");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_ult_pedido = @$res[0]['id'];

if ($ult_ped < $id_ult_pedido and $ult_ped != "") {
	echo '<audio autoplay="true">
<source src="../../img/audio.mp3" type="audio/mpeg" />
</audio>';
}

if ($ult_ped == "" and $id_ult_pedido != "") {
	echo '<audio autoplay="true">
<source src="../../img/audio.mp3" type="audio/mpeg" />
</audio>';
}

$ids = '';
$query = $pdo->query("SELECT * FROM $tabela where status LIKE '$status' and status != 'Finalizado' and status != 'Cancelado' order by hora asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
	<thead> 
	<tr> 
	<th>Cliente</th>	
	<th>Valor</th> 	
	<th>Total Pago</th> 
	<th>Troco</th>	
	<th>Forma PGTO</th> 	
	<th>Status</th>	 	
	<th>Hora</th>		
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		foreach ($res[$i] as $key => $value) {
		}
		$id = $res[$i]['id'];
		$cliente = $res[$i]['cliente'];
		$valor = $res[$i]['valor'];
		$total_pago = $res[$i]['total_pago'];
		$troco = $res[$i]['troco'];
		$data = $res[$i]['data'];
		$hora = $res[$i]['hora'];
		$status = $res[$i]['status'];
		$pago = $res[$i]['pago'];
		$obs = $res[$i]['obs'];
		$taxa_entrega = $res[$i]['taxa_entrega'];
		$tipo_pgto = $res[$i]['tipo_pgto'];
		$usuario_baixa = $res[$i]['usuario_baixa'];
		$entrega = $res[$i]['entrega'];
		$mesa = $res[$i]['mesa'];
		$nome_cliente_ped = $res[$i]['nome_cliente'];
		$pedido = $res[$i]['pedido'];
		$impressao = $res[$i]['impressao'];
		$codigo_pix = $res[$i]['ref_api'];





		//verificar pgto pix
		if ($codigo_pix != "" and $pago != "Sim") {
			require("../../../../js/ajax/verificar_pgto.php");
			if (@$status_api == 'approved') {
				$pdo->query("UPDATE vendas set pago = 'Sim', tipo_pgto = 'Pix' where id = '$id'");
			}
		}


		$valorF = number_format($valor, 2, ',', '.');
		$total_pagoF = number_format($total_pago, 2, ',', '.');
		$trocoF = number_format($troco, 2, ',', '.');
		$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
		$dataF = implode('/', array_reverse(explode('-', $data)));
		//$horaF = date("H:i", strtotime($hora));	

		$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes", strtotime($hora)));

		if ($hora_pedido < date('H:i')) {
			$classe_pago2 = '#ffdddd';
		} else {
			$classe_pago2 = '';
		}

		$query2 = $pdo->query("SELECT * FROM usuarios where id = '$usuario_baixa'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_usuario_pgto = $res2[0]['nome'];
		} else {
			$nome_usuario_pgto = 'Nenhum!';
		}


		$query2 = $pdo->query("SELECT * FROM clientes where id = '$cliente'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$total_reg2 = @count($res2);
		if ($total_reg2 > 0) {
			$nome_cliente = $res2[0]['nome'];
			$telefone_cliente = $res2[0]['telefone'];
			$telefone_clienteF = '55' . preg_replace('/[ ()-]+/', '', $telefone_cliente);
		} else {
			if ($mesa != '0' and $mesa != '') {
				$nome_cliente = 'Mesa: ' . $mesa;
			} else {
				$nome_cliente = $nome_cliente_ped;
			}
			$telefone_cliente = '';
			$telefone_clienteF = '';
		}





		if ($status == 'Iniciado') {
			$classe_alerta = '#eb0000';
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Aceito!';
			$cor_icone_link = '#eb0000';
			$acao_link = 'Aceito';
		} else if ($status == 'Aceito') {
			$classe_alerta = '#0097ff';
			$total_vendas += 0;
			$titulo_link = 'Mudar para Preparando!';
			$cor_icone_link = '#0097ff';
			$acao_link = 'Preparando';
		} else if ($status == 'Preparando') {
			$classe_alerta = '#ff8400';
			$total_vendas += 0;
			$titulo_link = 'Mudar para Entrega!';
			$cor_icone_link = '#ff8400';
			$acao_link = 'Entrega';
		} else {
			$classe_alerta = '#00dc49';
			$total_vendas += $valor;
			$titulo_link = 'Mudar para Finalizado!';
			$cor_icone_link = '#00dc49';
			$acao_link = 'Finalizado';
		}

		if ($pago == 'Sim') {
			$classe_excluir = 'ocultar';
			$visivel = 'ocultar';
			$classe_pago = 'text-verde';
			$texto_pago = '(Pago)';
		} else {
			$classe_excluir = '';
			$visivel = '';
			$classe_pago = '';
			$texto_pago = '';
		}

		if ($obs != "") {
			$classe_info = 'primary';
		} else {
			$classe_info = 'info';
		}

		if ($entrega == 'Delivery') {
			$classe_entrega = 'text-danger';
		} else if ($entrega == 'Retirar') {
			$classe_entrega = 'text-azul';
		} else {
			$classe_entrega = 'text-success';
		}


		//validando impressao automatica
		if ($status == 'Iniciado' and $impressao_automatica == 'Site' and $impressao != "Sim") {
			$ids .= $id . '-';
			//coloco a impressao = sim
			$pdo->query("UPDATE $tabela SET impressao = 'Sim' where id = '$id'");
		}


		echo <<<HTML
<tr style="background: {$classe_pago2}">
<td><sapn style="color:{$classe_alerta}"><i class="fa fa-square"></i></sapn> <b>Pedido ({$pedido})</b> / {$nome_cliente} <span class="{$classe_entrega}"><small>({$entrega}) </small></span>




		<a style="display:inline-block; margin-left:10px" href="#" onclick="darDesconto('{$id}')" title="Dar Desconto"><i class="fa fa-usd text-success"></i></a>

		<form method="POST" action="../../index" target="_blank" style="display:inline-block;">
					<input type="hidden" name="id_edicao" value="{$id}">
					<button style="outline:none; background:transparent; border:none; " type="submit" title="Editar Pedido"><i class="fa fa-edit text-info"></i>
				</form>


</td>
<td>R$ {$valorF}</td>
<td class="{$classe_pago}">R$ {$total_pagoF} <small>{$texto_pago}</small></td>
<td>R$ {$trocoF}</td>
<td>{$tipo_pgto}</td>
<td>
<span id="area_status_{$id}">
<a style="color:#FFF; background:{$cor_icone_link}; padding:8px; width:100% !important; text-align: center; font-size: 12px; border-radius: 6px;" title="{$titulo_link}" href="#" onclick="ativarPedido('{$id}','{$acao_link}','{$telefone_cliente}','{$valorF}','{$tipo_pgto}','{$hora_pedido}','{$entrega}')">
{$status}
<i class="fa fa-arrow-right "></i>
</a>
</span>
<img id="status_link_{$id}" src="../../img/loading.gif" width="80px" style="display:none">

</td>
<td>{$hora}</td>
<td>	

		<a class="btn btn-{$classe_info}-light btn-sm" href="#" onclick="mostrar('{$id}', '{$nome_cliente}', '{$pedido}')" title="Ver Dados"><i class="fa fa-info-circle "></i></a>



		<big><a href="#" class="btn btn-danger-light btn-sm {$classe_excluir}" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>



<a class="btn btn-success-light btn-sm {$classe_excluir}" href="#" onclick="baixar('{$id}', '{$nome_cliente}', '{$tipo_pgto}')" title="Confirmar Pgto">
<i class="fa fa-check-square"></i></a>


<a class="btn btn-success-light btn-sm" target="_blank" href="http://api.whatsapp.com/send?1=pt_BR&phone={$telefone_clienteF}" title="Whatsapp Cliente">
	<i class="fa-brands fa-whatsapp"></i></a>



			<a class="btn btn-dark-light btn-sm" href="#" onclick="gerarComprovante('{$id}')" title="Gerar Comprovante"><i class="fa fa-file-pdf-o"></i></a>		
			
		
	
		</td>
</tr>
HTML;
	}

	$total_vendasF = number_format($total_vendas, 2, ',', '.');

	echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>

<br>	
<div align="right">Total de Pedidos: <span class=""> {$total_reg}</span> </div>

</small>
HTML;
} else {
	echo '<small>Não possui nenhum Pedido Hoje ainda!</small>';
}



$query = $pdo->query("SELECT * FROM vendas where data = CurDate() and status = 'Iniciado'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_dos_itens_pedidos = @count($res);


?>

<script type="text/javascript">
	$(document).ready(function() {

		var ids = "<?= $ids ?>";
		var id_imp = ids.split("-");
		//alert(ids)

		for (i = 0; i < id_imp.length - 1; i++) {
			var id_pedido = id_imp[i];

			let a = document.createElement('a');
			a.target = '_blank';
			a.href = 'rel/comprovante.php?id=' + id_pedido;
			a.click();
		}


		$('#todos_pedidos').text("<?= $total_pedidos ?>");
		$('#ini_pedidos').text("<?= $total_ini ?>");
		$('#prep_pedidos').text("<?= $total_prep ?>");
		$('#ent_pedidos').text("<?= $total_ent ?>");
		$('#id_pedido').val("<?= $id_ult_pedido ?>");
		$('#ace_pedidos').text("<?= $total_ace ?>");

		$('#total-dos-pedidos').text("<?= $total_dos_itens_pedidos ?>");

		$('#tabela').DataTable({
			"ordering": false,
			"stateSave": true
		});
		$('#tabela_filter label input').focus();
	});
</script>


<script type="text/javascript">
	function mostrar(id, cliente, pedido) {
		listarPedido(id);
		$('#nome_dados').text('Pedido ' + pedido + ' - Cliente: ' + cliente);
		$('#modalDados').modal('show');
	}
</script>

<script type="text/javascript">
	function baixar(id, cliente, pgto) {

		$('#nome_baixar').text('Confirmar Pagamento: Pedido ' + id + ' - Cliente: ' + cliente);
		$('#pgto').val(pgto).change();
		$('#id_baixar').val(id);
		$('#modalBaixar').modal('show');
	}
</script>


<script type="text/javascript">
	function ativarPedido(id, acao, telefone, total, pagamento, hora, entrega) {
		var pedido_whatsapp = "<?= $api_whatsapp ?>";

		$('#status_link_' + id).show();
		$('#area_status_' + id).hide();


		if (entrega == 'Delivery') {

			var texto = 'Seu Pedido saiu para entrega';
		} else if (entrega == 'Retirar') {
			var texto = 'Seu Pedido ficou pronto, pode vir retirá-lo';
		} else {
			var texto = 'Seu Pedido já ficou pronto, pode vir!!';
		}

		var imp_aut = '<?= $impressao_automatica ?>';

		if (imp_aut == 'Sim' && acao == 'Preparando') {
			let a = document.createElement('a');
			a.target = '_blank';
			a.href = 'rel/comprovante.php?id=' + id;
			a.click();
		}

		if (acao == 'Entrega' && entrega == 'Delivery') {
			$('#id_entregador').val(id);
			$('#modalEntregador').modal('show');
		}

		setTimeout(function() {

			$.ajax({
				url: 'paginas/' + pag + "/mudar-status.php",
				method: 'POST',
				data: {
					id,
					acao,
					telefone,
					total,
					pagamento,
					texto
				},
				dataType: "text",

				success: function(mensagem) {
					var split = mensagem.split("***");

					var msg = split[0];

					if (msg == "Alterado com Sucesso") {

						if (acao.trim() === 'Entrega') {
							if (pedido_whatsapp != 'Não') {
								//let a= document.createElement('a');
								// a.target= '_blank';
								// a.href= 'http://api.whatsapp.com/send?1=pt_BR&phone='+telefone+'&text= *Atençãoooo*  %0A '+texto+' %0A *Total* R$'+total+' %0A *Tipo Pagamento* '+pagamento;
								// a.click();
							}
						}
						listar();
						$('#status_link_' + id).hide();
						$('#area_status_' + id).show();
					} else {
						$('#mensagem-excluir').addClass('text-danger')
						$('#mensagem-excluir').text(mensagem)

						$('#status_link_' + id).hide();
						$('#area_status_' + id).show();
					}


				},

			});

		}, 1000);
	}
</script>

<script type="text/javascript">
	function gerarComprovante(id) {


		let a = document.createElement('a');
		a.target = '_blank';
		a.href = 'rel/comprovante.php?id=' + id;
		a.click();
	}


	function darDesconto(id){
		$('#id_desconto').val(id);
		$('#modalDesconto').modal('show');
	}
</script>