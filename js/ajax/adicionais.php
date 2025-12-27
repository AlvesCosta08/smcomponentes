<?php 
@session_start();
require_once('../../sistema/conexao.php');

$id = $_POST['id'];
$quantidade = $_POST['quantidade'];
$sessao = @$_SESSION['sessao_usuario'];




if($quantidade == 0){
	$pdo->query("DELETE FROM temp WHERE id_item = '$id' and sessao = '$sessao' and tabela = 'adicionais' and carrinho = '0' and categoria = 'Sim'"); 
	echo 'Alterado com Sucesso';
	exit();
}

$query = $pdo->query("SELECT * FROM adicionais_cat where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$valor_adc = $res[0]['valor'];

$query = $pdo->query("SELECT * FROM temp where id_item = '$id' and sessao = '$sessao' and tabela = 'adicionais' and carrinho = '0'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){
	$pdo->query("UPDATE temp SET quantidade = '$quantidade' WHERE id_item = '$id' and sessao = '$sessao' and tabela = 'adicionais' and carrinho = '0' and categoria = 'Sim'"); 
}else{
	$pdo->query("INSERT INTO temp SET sessao = '$sessao', tabela = 'adicionais', id_item = '$id', carrinho = '0', data = curDate(), categoria = 'Sim', quantidade = '1', valor_item = '$valor_adc'"); 
}



echo 'Alterado com Sucesso';
