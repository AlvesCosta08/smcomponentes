<?php 
require_once("../../../conexao.php");
$tabela = 'produtos';

$id = $_POST['id'];
$acao = $_POST['acao'];

// Converter 'Sim'/'Não' para valores int da tabela
// O JavaScript envia '1' para ativar e '0' para desativar
$status = ($acao == '1') ? 1 : 0;

$pdo->query("UPDATE $tabela SET ativo = '$status' WHERE id = '$id'");
echo 'Alterado com Sucesso';
?>