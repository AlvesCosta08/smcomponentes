<?php
include('../../conexao.php');
include('data_formatada.php');

$dataInicial = $_GET['dataInicial'];
$dataFinal = $_GET['dataFinal'];
$status = $_GET['status'];
$forma_pgto = $_GET['forma_pgto'];
$tipo = $_GET['tipo'];

$horaInicial = $_GET['horaInicial'];
$horaFinal = $_GET['horaFinal'];

if($tipo == ""){
	$sql_tipo = ' ';
}else{
	if($tipo == "Delivery"){
		$tipo = '';
	}
	$sql_tipo = " and tipo_pedido = '$tipo' ";
}

if($horaInicial == "" || $horaFinal == ""){
	$sql_hora = ' ';
}else{	
	if($dataInicial == $dataFinal){
		$sql_hora = " AND hora >= '$horaInicial' AND hora <= '$horaFinal' ";
	}else{
		$sql_hora = " AND ( (data = '$dataInicial' AND hora >= '$horaInicial')  OR (data = '$dataFinal' AND hora <= '$horaFinal')) ";
	}
	
}


$dataInicialF = implode('/', array_reverse(explode('-', $dataInicial)));
$dataFinalF = implode('/', array_reverse(explode('-', $dataFinal)));

if ($dataInicial == $dataFinal) {
	$texto_apuracao = 'APURADO EM ' . $dataInicialF;
} else if ($dataInicial == '1980-01-01') {
	$texto_apuracao = 'APURADO EM TODO O PERÍODO';
} else {
	$texto_apuracao = 'APURAÇÃO DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}


if ($status == '') {
	$acao_rel = '';
} else {
	if ($status == 'Finalizado') {
		$acao_rel = ' Finalizadas ';
	} else {
		$acao_rel = ' Canceladas ';
	}
}

if ($forma_pgto == '') {
	$texto_tabela = '';
} else {
	$texto_tabela = ' ' . $forma_pgto;
}


$status = '%' . $status . '%';
$forma_pgto = '%' . $forma_pgto . '%';
?>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<?php if (@$impressao_automatica != 'Não') { ?>
	<script type="text/javascript">
		$(document).ready(function() {
			window.print();
			window.close();
		});
	</script>
<?php } ?>

<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
	integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">

<style type="text/css">
	* {
		margin: 0px;

		/*Espaçamento da margem da esquerda e da Direita*/
		padding: 0px;
		background-color: #ffffff;


	}

	.text {
		&-center {
			text-align: center;
		}
	}

	.printer-ticket {
		display: table !important;
		width: 100%;

		/*largura do Campos que vai os textos*/
		max-width: 400px;
		font-weight: light;
		line-height: 1.3em;

		/*Espaçamento da margem da esquerda e da Direita*/
		padding: 0px;
		font-family: TimesNewRoman, Geneva, sans-serif;

		/*tamanho da Fonte do Texto*/
		font-size:
			<?php echo $fonte_comprovante ?>px;



	}

	.th {
		font-weight: inherit;
		/*Espaçamento entre as uma linha para outra*/
		padding: 5px;
		text-align: center;
		/*largura dos tracinhos entre as linhas*/
		border-bottom: 1px dashed #000000;
	}

	.itens {
		font-weight: inherit;
		/*Espaçamento entre as uma linha para outra*/
		padding: 5px;

	}

	.valores {
		font-weight: inherit;
		/*Espaçamento entre as uma linha para outra*/
		padding: 2px 5px;

	}


	.cor {
		color: #000000;
	}


	.title {
		font-size: 12px;
		text-transform: uppercase;
		font-weight: bold;
	}

	/*margem Superior entre as Linhas*/
	.margem-superior {
		padding-top: 5px;
	}


	}
</style>



<div class="printer-ticket">
	<div align="center">
		<td>
			<img style="margin: 12px; margin-left: 50px;" id="imag" src="<?php echo $url_sistema ?>sistema/img/logo.jpg"
				width="110px">
		</td>
	</div>

	<div class="th">
		<?php echo $endereco_sistema ?> <br />
		<small>Contato: <?php echo $telefone_sistema ?>
			<?php if ($cnpj_sistema != "") {
				echo ' / CNPJ ' . @$cnpj_sistema;
			} ?>
		</small>
	</div>



	
	<br>
	<div class="th title">Detalhamento das Vendas</div>

	<br>

	<div class="row itens">

			<div align="left" class="col-5"><b>CLIENTE</b></div>

			<div align="right" class="col-4"><b>VALOR</b></div>

			<div align="right" class="col-3"><b>HORA</b></div>

	
		</div>


		<?php 
		$total_vendas = 0;
		$total_vendasF = 0;
		$total_canceladas = 0;
		$total_canceladasF = 0;
		$total_delivery = 0;
		$total_deliveryF = 0;
		$total_mesas = 0;
		$total_mesasF = 0;
		$total_balcao = 0;
		$total_balcaoF = 0;
		$query = $pdo->query("SELECT * from vendas where (data >= '$dataInicial' and data <= '$dataFinal') and pago = 'Sim' and status LIKE '$status' and tipo_pgto LIKE '$forma_pgto' $sql_tipo $sql_hora order by data asc, hora asc ");
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		$total_reg = count($res);
		if ($total_reg > 0) {


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
					$mesa = $res[$i]['mesa'];
					$nome_do_cliente = $res[$i]['nome_cliente'];
					$tipo_pedido = $res[$i]['tipo_pedido'];

					$valorF = number_format($valor, 2, ',', '.');
					$total_pagoF = number_format($total_pago, 2, ',', '.');
					$trocoF = number_format($troco, 2, ',', '.');
					$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
					$dataF = implode('/', array_reverse(explode('-', $data)));
					//$horaF = date("H:i", strtotime($hora));	



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
					} else {
						$nome_cliente = 'Nenhum!';
					}

					if ($mesa != '0' and $mesa != '') {
						$nome_cliente = 'Mesa: ' . $mesa;
					}

					if ($nome_do_cliente != "") {
						$nome_cliente = $nome_do_cliente;
					}


					if ($status == 'Finalizado') {
						$classe_alerta = 'text-verde';
						$total_vendas += $valor;
						$classe_linha = '';
						$classe_square = 'verde';
						$imagem = 'verde.jpg';
					} else if ($status == 'Cancelado') {
						$classe_alerta = 'text-danger';
						$total_canceladas += $valor;
						$classe_linha = 'text-muted';
						$classe_square = 'text-danger';

						$imagem = 'vermelho.jpg';
					} else {
						$classe_alerta = 'text-primary';
						$total_vendas += $valor;
						$classe_linha = '';
						$classe_square = 'verde';
						$imagem = 'verde.jpg';
					}



					if($tipo_pedido == ""){
						$total_delivery += $total_pago;
					}

					if($tipo_pedido == "Balcão"){
						$total_balcao += $total_pago;
					}


					if($tipo_pedido == "Mesa"){
						$total_mesas += $total_pago;
					}

					$total_deliveryF = number_format($total_delivery, 2, ',', '.');
					$total_balcaoF = number_format($total_balcao, 2, ',', '.');
					$total_mesasF = number_format($total_mesas, 2, ',', '.');





					$total_vendasF = number_format($total_vendas, 2, ',', '.');
					$total_canceladasF = number_format($total_canceladas, 2, ',', '.');

					$nome_do_clienteF = mb_strimwidth($nome_do_cliente, 0, 17, "...");

		 ?>

		<div class="row itens">

			<div align="left" class="col-6"><?php echo $nome_do_clienteF ?></div>

			<div align="right" class="col-3"><?php echo $total_pagoF ?></div>

			<div align="right" class="col-3"><?php echo $hora ?></div>

	
		</div>

		<?php } } ?>



		<hr>

		<div class="row itens">

			<div align="left" class="col-4"><b> DELIVERY</b></div>

			<div align="right" class="col-4"><b> BALCÃO</b></div>

			<div align="right" class="col-4"><b> MESAS</b></div>

	
		</div>

		<div class="row itens">

			<div align="left" class="col-4">R$ <?php echo $total_deliveryF ?></div>

			<div align="right" class="col-4">R$ <?php echo $total_balcaoF ?></div>

			<div align="right" class="col-4">R$ <?php echo $total_mesasF ?></div>

	
		</div>