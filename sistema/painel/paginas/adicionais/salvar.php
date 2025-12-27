<?php 
require_once("../../../conexao.php");
$tabela = 'adicionais';

$id = $_POST['id'];
$nome = $_POST['nome'];
$valor = $_POST['valor'];


//validar nome
$query = $pdo->query("SELECT * from $tabela where nome = '$nome'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Adicional já Cadastrado, escolha outro!!';
	exit();
}


if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, valor = :valor, ativo = 'Sim'");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, valor = :valor WHERE id = '$id'");
	$pdo->query("UPDATE itens_grade SET valor = '$valor', texto = '$nome' where adicional = '$id'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":valor", "$valor");
$query->execute();

echo 'Salvo com Sucesso';

?>