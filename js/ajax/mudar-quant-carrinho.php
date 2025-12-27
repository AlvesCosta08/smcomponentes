<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
@session_start();
require_once('../../sistema/conexao.php');

$id = $_POST['id'];
$acao = $_POST['acao'];
$quantidade = $_POST['quantidade'];

if($acao == 'menos'){
  $quant = $quantidade - 1;
}else{
	$quant = $quantidade + 1;    
}





$query = $pdo->query("SELECT * FROM carrinho where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_item = @$res[0]['total_item'];
$quantidade = @$res[0]['quantidade'];
$produto = @$res[0]['produto'];
if($total_item > 0 and $quantidade > 0){
	$valor_unit = $total_item / $quantidade;
}else{
	$valor_unit = 0;
}


$novo_valor = $quant * $valor_unit;

$query = $pdo->query("SELECT * FROM produtos where id = '$produto'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_categoria = @$res[0]['categoria'];
$valor_produto = @$res[0]['valor_venda'];
$estoque = @$res[0]['estoque'];
$tem_estoque = @$res[0]['tem_estoque'];


//ver se possui a quantidade de produtos comprados
if($acao != 'menos' and $estoque <= 0 and $tem_estoque == 'Sim'){
	echo 'Quantidade em Estoque insuficiente, possui apenas '.$estoque.' Itens';
	exit();
}


//abater produto estoque
if($tem_estoque == 'Sim'){
	if($acao == 'menos'){
  $total_produtos = $estoque + 1;
}else{
	$total_produtos = $estoque - 1;
}


$pdo->query("UPDATE produtos SET estoque = '$total_produtos' where id = '$produto'"); 
}



$pdo->query("UPDATE carrinho set quantidade = '$quant' WHERE id = '$id'"); 

$id_edicao = 0;
if (@$_SESSION['id_edicao'] != "") {
	$id_edicao = $_SESSION['id_edicao'];

	//adicionar o total item na tabela de vendas
	$query = $pdo->query("SELECT * FROM vendas where id = '$id_edicao'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$valor_venda = $res[0]['valor'];

	if($acao == 'menos'){
	  $total_venda = $valor_venda - $total_item;
	}else{
		$total_venda = $valor_venda + $total_item;    
	}
	
	$pdo->query("UPDATE vendas SET valor = '$total_venda' where id = '$id_edicao'");
}

echo 'Alterado com Sucesso';

 ?>