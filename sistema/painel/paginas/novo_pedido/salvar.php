<?php 
require_once("../../../conexao.php");
$tabela = 'abertura_mesa';


$id = $_POST['id'];
$nome = $_POST['nome'];
$garcon = $_POST['garcon'];
$obs = $_POST['obs'];
$id_ab = $_POST['id_ab'];
$pessoas = $_POST['pessoas'];


if($id_ab == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET mesa = '$id', cliente = :cliente, data = curDate(), horario_abertura = curTime(), garcon = :garcon, status = 'Aberta', obs = :obs, pessoas = :pessoas");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET cliente = :cliente, garcon = :garcon, obs = :obs, pessoas = :pessoas WHERE id = '$id_ab'");
}


$query->bindValue(":cliente", "$nome");
$query->bindValue(":garcon", "$garcon");
$query->bindValue(":obs", "$obs");
$query->bindValue(":pessoas", "$pessoas");
$query->execute();

$pdo->query("UPDATE mesas set status = 'Aberta' where id = '$id'");

echo 'Salvo com Sucesso';

?>