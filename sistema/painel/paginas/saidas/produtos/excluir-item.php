<?php 
require_once("../../../conexao.php");
$tabela = 'produtos';

$id = $_POST['id'];

// Antes de excluir, verificar se existe foto para excluir
$query = $pdo->query("SELECT foto FROM $tabela WHERE id = '$id'");
$res = $query->fetch(PDO::FETCH_ASSOC);

if ($res && $res['foto'] != 'sem-foto.jpg') {
    $foto = $res['foto'];
    if (file_exists("../../images/produtos/$foto")) {
        unlink("../../images/produtos/$foto");
    }
}

$pdo->query("DELETE FROM $tabela WHERE id = '$id'");
echo 'Excluído com Sucesso';
?>