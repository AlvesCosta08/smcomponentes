<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
@session_start();
require_once('../../sistema/conexao.php');

$id = @$_POST['id'];
$grade = @$_POST['grade'];
$valor = @$_POST['valor'];
$tipo = @$_POST['tipo'];
$sessao = @$_SESSION['sessao_usuario'];
$marcado = @$_POST['marcado'];
$quantidade = @$_POST['quantidade'];
$tipagem = @$_POST['tipagem'];

//verificar se o item já existe na tabela para remove-lo
if($tipo == '1 de Cada'){
     $query = $pdo->query("SELECT * FROM temp where grade = '$grade' and carrinho = '0' and sessao = '$sessao' and id_item = '$id'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$total_reg = @count($res);
	if($total_reg > 0){
		 $pdo->query("DELETE FROM temp where grade = '$grade' and carrinho = '0' and sessao = '$sessao' and id_item = '$id'");

		 echo 'Alterado com Sucesso';
		exit();   
	}



}

if($quantidade == ""){
	$quantidade = 1;
}





if($quantidade == 0){
	$pdo->query("DELETE FROM temp where id_item = '$id' and carrinho = '0' and sessao = '$sessao'");
	echo 'Alterado com Sucesso';
	exit();
}

if($tipo == 'Múltiplo'){
	$pdo->query("DELETE FROM temp where id_item = '$id' and carrinho = '0' and sessao = '$sessao'");
}

if($tipo == 'Único' || $tipo == 'Variação'){
	$query = $pdo->query("SELECT * FROM temp where grade = '$grade' and carrinho = '0' and sessao = '$sessao'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$total_reg = @count($res);
	if($total_reg > 0){
		 $pdo->query("DELETE FROM temp where grade = '$grade' and carrinho = '0' and sessao = '$sessao'");
	}
}


$pdo->query("INSERT INTO temp SET sessao = '$sessao', tabela = '$tipo', id_item = '$id', carrinho = '0', data = curDate(), grade = '$grade', valor_item = '$valor', quantidade = '$quantidade', tipagem = '$tipagem'"); 


echo 'Alterado com Sucesso';
