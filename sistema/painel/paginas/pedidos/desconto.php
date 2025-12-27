<?php 
require_once("../../../conexao.php");
$tabela = 'vendas';
@session_start();
$id_usuario = $_SESSION['id'];


$id = $_POST['id'];
$valor = $_POST['valor'];

$query = $pdo->query("SELECT * FROM vendas where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if ($total_reg > 0) {
  $valor_total = $res[0]['valor'];  
}

$novo_valor = $valor_total - $valor;

$pdo->query("UPDATE $tabela SET cupom = '$valor', valor = '$novo_valor' where id = '$id'");

echo 'Baixado com Sucesso';
