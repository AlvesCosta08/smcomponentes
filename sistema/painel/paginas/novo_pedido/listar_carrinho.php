<?php
require_once("../../../conexao.php");
$tabela = 'carrinho';

$id = @$_POST['id'];

$total_carrinho = 0;
$query = $pdo->query("SELECT * FROM $tabela where mesa = '$id' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="">
	<thead> 
	<tr> 
	<th>Foto</th>	
	<th>Produto(s)</th>	
	<th>Total</th>	
	<th>Excluir</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

	for ($i = 0; $i < $total_reg; $i++) {
		$id = $res[$i]['id'];
		$total_item = $res[$i]['total_item'];
		$produto = $res[$i]['produto'];
		$quantidade = $res[$i]['quantidade'];
		$obs = $res[$i]['obs'];
		$item = $res[$i]['item'];
		$variacao = $res[$i]['variacao'];
		$nome_produto_tab = $res[$i]['nome_produto'];
		$sabores = $res[$i]['sabores'];
		$borda = $res[$i]['borda'];
		$categoria = $res[$i]['categoria'];
		$valor_unit = $res[$i]['valor_unitario'];
		$status = $res[$i]['status'];

		if ($status == 'Aguardando') {
			$classe_alerta = 'text-danger';
			$opacidade = '1';
		} else if ($status == 'Preparando') {
			$classe_alerta = 'text-info';
			$opacidade = '1';
		} else if ($status == 'Pronto') {
			$classe_alerta = 'text-verde';
			$opacidade = '1';
		} else {
			$classe_alerta = 'text-secondary';
			$opacidade = '0';
		}


		if ($valor_unit == "") {
			if ($total_item > 0 and $quantidade > 0) {
				$valor_unit = $total_item / $quantidade;
			} else {
				$valor_unit = 0;
			}
		}

		$total_item = $total_item * $quantidade;
		$total_carrinho += $total_item;


		$total_itemF = number_format($total_item, 2, ',', '.');
		$valor_unitF = number_format($valor_unit, 2, ',', '.');
		$total_carrinhoF = number_format($total_carrinho, 2, ',', '.');

		$query2 = $pdo->query("SELECT * FROM variacoes where id = '$variacao'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count(@$res2) > 0) {
			$sigla_variacao = '(' . $res2[0]['sigla'] . ')';
		} else {
			$sigla_variacao = '';
		}


		$query2 = $pdo->query("SELECT * FROM produtos where id = '$produto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		if (@count(@$res2) > 0) {
			$nome_produto = $res2[0]['nome'];
			$foto_produto = $res2[0]['foto'];
		} else {
			$nome_produto = $nome_produto_tab;
			$foto_produto = "";
		}


		if ($obs == '') {
			$classe_obs = 'text-warning';
		} else {
			$classe_obs = 'text-danger';
		}

		if ($sabores > 0) {
			$nome_produto = $nome_produto_tab;
		}



		echo <<<HTML
<tr>
<td><img class="" src="../../sistema/painel/images/produtos/{$foto_produto}" width="25px"></td>
<td><i class="fa fa-square {$classe_alerta}" style="opacity: {$opacidade}"></i> {$quantidade} / {$nome_produto}</td>
<td>{$total_itemF}</td>
<td>




<a class="btn btn-danger btn-sm" href="#" onclick="excluirModal('{$id}', '{$nome_produto}')" title="Excluir Item" class=""><big><i class="fa fa-trash-o "></i></big></a>

	 <a style="" class="btn btn-info btn-sm" href="#" onclick="gerarComprovante('{$id}')" title="Gerar Comprovante"><i class="fa fa-file-pdf-o"></i></a>		


</td>
</tr>
HTML;
	}

	echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir"></div>	</small>
	</table>
	<div align="right"><b>Itens</b> ({$total_reg}) / <b>Total R$ </b><span style="color:green">{$total_carrinhoF}</span></div>
	</small>
HTML;
} else {
	echo 'Não possui itens lançados!';
}


?>


<script type="text/javascript">
	function excluirCarrinho() {

		var id = $('#id_car_excluir').val();
		var senha = $('#senha').val();
		var gerente = $('#gerente').val();

		if (gerente == "") {
			alert('Selecione um Gerente!');
			return;
		}
		
		if (senha == "") {
			alert('Preencha a senha!');
			return;
		}



		$.ajax({
			url: 'paginas/' + pag + "/excluir_carrinho.php",
			method: 'POST',
			data: {
				id,
				gerente,
				senha
			},
			dataType: "text",

			success: function(mensagem) {
				if (mensagem.trim() == "Excluído com Sucesso") {
					listar();
					listarCarrinho();
					$('#mensagem-excluir-modal').text('')
					$('#btn-fechar-excluir').click();
				} else {
					$('#mensagem-excluir-modal').addClass('text-danger')
					$('#mensagem-excluir-modal').text(mensagem)
				}

			},

		});
	}



	function excluirModal(id, nome) {

		$('#nome_modal').text(nome);
		$('#id_car_excluir').val(id);
		$('#senha').val('');

		$('#modalExcluir').modal('show');

	}
</script>


<script type="text/javascript">
  function gerarComprovante(id) {


    let a = document.createElement('a');
    a.target = '_blank';
    a.href = 'rel/comprovante_item_mesa.php?id=' + id;
    a.click();
  }
</script>