<?php
@session_start();
require_once("cabecalho.php");
require_once("js/ajax/ApiConfig.php");

$id = @$_GET['id'];



//BUSCAR AS INFORMAÇÕES DO PEDIDO
$query = $pdo->query("SELECT * from vendas where id = '$id' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

$cliente = $res[0]['cliente'];
$valor = $res[0]['valor'];
$total_pago = $res[0]['total_pago'];
$troco = $res[0]['troco'];
$data = $res[0]['data'];
$hora = $res[0]['hora'];
$status = $res[0]['status'];
$pago = $res[0]['pago'];
$obs = $res[0]['obs'];
$taxa_entrega = $res[0]['taxa_entrega'];
$tipo_pgto = $res[0]['tipo_pgto'];
$usuario_baixa = $res[0]['usuario_baixa'];
$entrega = $res[0]['entrega'];
$mesa = $res[0]['mesa'];
$nome_cliente_ped = $res[0]['nome_cliente'];
$cupom = $res[0]['cupom'];
$n_pedido = $res[0]['pedido'];

$cupomF = number_format($cupom, 2, ',', '.');
$valorF = number_format($valor, 2, ',', '.');
$total_pagoF = number_format($total_pago, 2, ',', '.');
$trocoF = number_format($troco, 2, ',', '.');
$taxa_entregaF = number_format($taxa_entrega, 2, ',', '.');
$dataF = implode('/', array_reverse(explode('-', $data)));
//$horaF = date("H:i", strtotime($hora));	

$valor_dos_itens = $valor - $taxa_entrega + $cupom;
$valor_dos_itensF = number_format($valor_dos_itens, 2, ',', '.');

$hora_pedido = date('H:i', strtotime("+$previsao_entrega minutes", strtotime($hora)));

if ($pago == 'Sim') {
	$cor_pago = 'green';
} else {
	$cor_pago = 'red';
}


$query = $pdo->query("SELECT * FROM clientes where id = '$cliente' ");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$cartoes = @$res[0]['cartoes'];
   

?>

<body style="background: #ededdf;">
	<div class="container" align="center" style="padding:10px; font-size: 'Verdana';">
		<img src="<?php echo $url_sistema ?>img/check_ok.png" width="80px">
		<br>
		<span style="font-size: 18px; margin-top: 5px"><b>Nº PEDIDO <?php echo $n_pedido ?></b></span>
		<br>
		<span style="font-size: 12px; margin-top: -5px">Ficamos gratos por realizar essa compra!</span><br>
		

		
		<?php if($cartoes == 1) {
			//echo 'Cartão';
		} else { 
			//echo 'Cartões';
		} ?>
		 </b></span><br>



		<hr style="margin:7px">
		<p>
			<span style="font-size: 13px;"><b>Pagamento: </b> <span><?php echo $tipo_pgto ?></span></span> /
			<span style="font-size: 13px;"><b>Pago: </b> <span style="color:<?php echo $cor_pago ?>"><?php echo $pago ?></span></span>
			<br>
			<span style="font-size: 13px;"><b>Opção de Entrega: </b> <span><?php echo $entrega ?></span></span><br>
			<span style="font-size: 13px;"><b>Pagamento: <span style="color:<?php echo $cor_pago ?>; font-size: 15px"></span></b></span><br>
			<?php if ($access_token == "") { ?>
				<span style="font-size: 13px;"><b>Chave Pix:</b> <span style="font-size: 13px"><?php echo $tipo_chave ?> <?php echo $chave_pix ?></span></span><br>
			<?php } ?>
		</p>

		<hr style="margin:7px">
		<p style="background: #757571; padding:2px; color:#FFF; font-size:13px">CONFIRA SEUS ITENS</p>



		<div class="row itens" style="font-size:12px">
			<?php
			$res = $pdo->query("SELECT * from carrinho where pedido = '$id' order by id asc");
			$dados = $res->fetchAll(PDO::FETCH_ASSOC);
			$linhas = count($dados);

			$sub_tot;
			for ($i = 0; $i < count($dados); $i++) {
				foreach ($dados[$i] as $key => $value) {
				}
				$id_carrinho = $dados[$i]['id'];
				$id_produto = $dados[$i]['produto'];
				$quantidade = $dados[$i]['quantidade'];
				$total_item = $dados[$i]['total_item'];
				$obs_item = $dados[$i]['obs'];
				$item = $dados[$i]['item'];
				$variacao = $dados[$i]['variacao'];
				$nome_produto_tab = $dados[$i]['nome_produto'];
				$sabores = $dados[$i]['sabores'];
				$borda = $dados[$i]['borda'];
				$categoria = $dados[$i]['categoria'];


				$query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				if (@count(@$res2) > 0) {
					$sigla_variacao = '(' . $res2[0]['sigla'] . ')';
				} else {
					$sigla_variacao = '';
				}

				$query2 = $pdo->query("SELECT * FROM temp where carrinho = '$id_carrinho' and tabela = 'Variação' order by id asc limit 1");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				$id_do_item = @$res2[0]['id_item'];

				$query2 = $pdo->query("SELECT * FROM itens_grade where id = '$id_do_item'");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				if (@$res2[0]['texto'] != "") {
					$sigla_grade = @$res2[0]['texto'];
				} else {
					$sigla_grade = '';
				}

				$query2 = $pdo->query("SELECT * FROM produtos where id = '$id_produto'");
				$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
				if (@count(@$res2) > 0) {
					$nome_produto = $res2[0]['nome'];
					$foto_produto = $res2[0]['foto'];
				} else {
					$nome_produto = $nome_produto_tab;
					$foto_produto = "";
				}
			?>



				<div align="left" class="col-9"> <?php echo $quantidade ?> - <?php echo $nome_produto ?> <?php echo $sigla_variacao ?> <?php echo $sigla_grade ?>

				</div>



				<hr style="margin:2px">

			<?php } ?>
		</div>



		<div style="font-size: 12px; margin-top: 15px" align="left" id="listar_st">



		</div>


		<br>



<br>
<div class="row" style="display: flex; justify-content: center; flex-wrap: wrap;">
 <?php 
for($i=1; $i<=$total_cartoes_config; $i++){ 
		if($cartoes >= $i){
			$valor = 0;
			$opacity = 1;
		}else{
			$valor = 1;
			$opacity = 0.3;		}
	?>

<div class="col-md-1 col-2" align="center">

</div>
<?php } ?>
</div>
<br>



		<div style="margin-top: 25px">
			<a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whats ?>" target="_blank"><img src="<?php echo $url_sistema ?>img/whats.png" width="80%"></a>
		</div>

</body>


<script type="text/javascript">
	listarStatus();


	function listarStatus() {
		var id = "<?= $id ?>";
		var url_sistema = "<?= $url_sistema ?>";

		$.ajax({
			url: url_sistema + 'js/ajax/listar_status.php',
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(result) {
				$('#listar_st').html(result);
			}
		});
	}

	setInterval(listarStatus, 15000);
</script>