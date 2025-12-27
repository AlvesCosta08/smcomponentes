<?php
require_once("../../../conexao.php");
$tabela = 'valor_adiantamento';

$id = @$_POST['id'];


$query = $pdo->query("SELECT * FROM $tabela where abertura = '$id' order by id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {

	echo <<<HTML
	<small>
	<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="">
	<thead> 
	<tr> 
	<th width="40%">Nome</th>
	<th>Forma de pagamanto</th>
	<th>Valor</th>		
	<th>Excluir</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;
	$total_valor = 0;
	$total_valorF = 0;
	for ($i = 0; $i < $total_reg; $i++) {
		$id = $res[$i]['id'];
		$nome = $res[$i]['nome'];
		$valor = $res[$i]['valor'];
		$forma_pgto = $res[$i]['forma_pgto'];


		$query2 = $pdo->query("SELECT * FROM formas_pgto where id = '$forma_pgto'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_pgto = @$res2[0]['nome'];


		$valorF = number_format($valor, 2, ',', '.');

		$total_valor += $valor;
		$total_valorF = number_format($total_valor, 2, ',', '.');

		echo <<<HTML
<tr>
<td>{$nome}</td>
<td>{$nome_pgto}</td>
<td>{$valorF}</td>
<td>



<div class="dropdown" style="display: inline-block;">                      
<a href="#" aria-expanded="false" aria-haspopup="true" data-bs-toggle="dropdown" class="dropdown"><i class="fe fe-trash-2 text-danger"></i> </a>
<div  class="dropdown-menu tx-13">
<div style="width: 180px; padding:10px;" class="dropdown-item-text">
<p>Confirmar Exclusão? <a href="#" onclick="excluirValor('{$id}')"><span class="botao-excluir" style="background: red; border-radius: 3px; color: #ffffff; padding: 3px;"> Sim</span></a></p>
</div>
</div>
</div>



</td>
</tr>
HTML;
	}

	echo <<<HTML
	</tbody>
	<small><div align="center" id="mensagem-excluir"></div>	</small>
	</table>
	<div align="right"><b>Total R$ </b><span style="color:green">{$total_valorF}</span></div>
	</small>
HTML;
} else {
	echo 'Não possui valores lançados!';
}


?>


<script type="text/javascript">
	function excluirValor(id) {
		$.ajax({
			url: 'paginas/' + pag + "/excluir_valor.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "text",

			success: function(mensagem) {
				if (mensagem.trim() == "Excluído com Sucesso") {

					listarValores();
				} else {
					$('#mensagem-valor').addClass('text-danger')
					$('#mensagem-valor').text(mensagem)
				}

			},

		});
	}
</script>