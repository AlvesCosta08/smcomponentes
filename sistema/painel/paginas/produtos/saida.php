<?php
require_once("../../../conexao.php");

if (isset($_POST['id']) && isset($_POST['quantidade_saida']) && isset($_POST['motivo_saida'])) {
    $id = intval($_POST['id']);
    $quantidade = intval($_POST['quantidade_saida']);
    $motivo = trim($_POST['motivo_saida']);
    $estoque_atual = intval($_POST['estoque']);
    
    if ($quantidade <= 0) {
        echo "Quantidade inválida";
        exit();
    }
    
    if (empty($motivo)) {
        echo "Informe o motivo da saída";
        exit();
    }
    
    if ($quantidade > $estoque_atual) {
        echo "Quantidade indisponível em estoque";
        exit();
    }
    
    try {
        // Atualizar estoque
        $novo_estoque = $estoque_atual - $quantidade;
        $stmt = $pdo->prepare("UPDATE produtos SET estoque = ? WHERE id = ?");
        $stmt->execute([$novo_estoque, $id]);
        
        // Registrar movimentação (se tiver tabela de movimentações)
        // $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, motivo, data) VALUES (?, 'saida', ?, ?, NOW())");
        // $stmt->execute([$id, $quantidade, $motivo]);
        
        echo "Salvo com Sucesso";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
} else {
    echo "Dados incompletos";
}
?>