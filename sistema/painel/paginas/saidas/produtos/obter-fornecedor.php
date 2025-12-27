<?php
require_once("../../../conexao.php");

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['nome' => 'Não informado']);
    exit();
}

$id = intval($_POST['id']);

try {
    $query = $pdo->prepare("SELECT nome FROM fornecedores WHERE id = :id");
    $query->bindValue(':id', $id);
    $query->execute();
    
    $fornecedor = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($fornecedor) {
        echo json_encode(['nome' => $fornecedor['nome']]);
    } else {
        echo json_encode(['nome' => 'Não encontrado']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['nome' => 'Erro ao buscar']);
}
?>