<?php 
require_once("../../../conexao.php");
$tabela = 'cupons';

$id = $_POST['id'];
$codigo = $_POST['codigo'];
$valor = $_POST['valor'];
$valor = str_replace(',', '.', $valor);
$data = $_POST['data'];
$quantidade = $_POST['quantidade'];
$valor_minimo = $_POST['valor_minimo'];
$valor_minimo = str_replace(',', '.', $valor_minimo);
$tipo = $_POST['tipo'];

//validar email
$query = $pdo->query("SELECT * from $tabela where codigo = '$codigo'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Código já Cadastrado, escolha outro!!';
	exit();
}


if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET codigo = :codigo, valor = :valor, data = '$data', quantidade = '$quantidade', valor_minimo = :valor_minimo, tipo = '$tipo'");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET codigo = :codigo, valor = :valor, data = '$data', quantidade = '$quantidade', valor_minimo = :valor_minimo, tipo = '$tipo' WHERE id = '$id'");
}

$query->bindValue(":codigo", "$codigo");
$query->bindValue(":valor", "$valor");
$query->bindValue(":valor_minimo", "$valor_minimo");
$query->execute();

echo 'Salvo com Sucesso';

?>