<?php 
require_once("../../../conexao.php");

$tabela = 'itens_grade';

$texto = @$_POST['texto'];
$produto = $_POST['id_item_produto'];
$valor = $_POST['valor'];
$limite = $_POST['limite'];
$grade = $_POST['id'];
$id_item_editar = $_POST['id_item_editar'];

$adicional = @$_POST['adicional'];
$e_adicional = $_POST['e_adicional'];

if($e_adicional == 'Sim' and $adicional == ""){
	echo 'Selecione um adicional!';
	exit();
}

if($e_adicional != 'Sim' and $texto == ""){
	echo 'Digite o Texto do Item!';
	exit();
}


if($adicional == ""){
	$adicional = 0;
}


if($e_adicional == 'Sim'){
	$query = $pdo->query("SELECT * FROM adicionais where id = '$adicional'");
	$res = $query->fetchAll(PDO::FETCH_ASSOC);
	$texto = $res[0]['nome'];

}



$valor = str_replace(',', '.', $valor);

if($id_item_editar == ""){
$query = $pdo->prepare("INSERT INTO $tabela SET produto = '$produto', grade = '$grade', valor = :valor, texto = :texto, limite = :limite, ativo = 'Sim', adicional = '$adicional'");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET valor = :valor, texto = :texto, limite = :limite, adicional = '$adicional' where id = '$id_item_editar'");
}

$query->bindValue(":valor", "$valor");
$query->bindValue(":texto", "$texto");
$query->bindValue(":limite", "$limite");
$query->execute();

echo 'Salvo com Sucesso';
?>