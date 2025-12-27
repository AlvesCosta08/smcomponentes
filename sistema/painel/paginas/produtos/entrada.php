<?php
require_once("../../../conexao.php");

if (isset($_POST['id']) && isset($_POST['quantidade_entrada']) && isset($_POST['motivo_entrada'])) {
    $id = intval($_POST['id']);
    $quantidade = intval($_POST['quantidade_entrada']);
    $motivo = trim($_POST['motivo_entrada']);
    $estoque_atual = intval($_POST['estoque']);
    
    if ($quantidade <= 0) {
        echo "Quantidade inválida";
        exit();
    }
    
    if (empty($motivo)) {
        echo "Informe o motivo da entrada";
        exit();
    }
    
    try {
        // Atualizar estoque
        $novo_estoque = $estoque_atual + $quantidade;
        $stmt = $pdo->prepare("UPDATE produtos SET estoque = ? WHERE id = ?");
        $stmt->execute([$novo_estoque, $id]);
        
        // Registrar movimentação (se tiver tabela de movimentações)
        // $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, motivo, data) VALUES (?, 'entrada', ?, ?, NOW())");
        // $stmt->execute([$id, $quantidade, $motivo]);
        
        echo "Salvo com Sucesso";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
} else {
    echo "Dados incompletos";
}
?>