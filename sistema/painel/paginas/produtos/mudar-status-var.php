<?php
require_once("../../../conexao.php");

if (isset($_POST['id']) && isset($_POST['acao'])) {
    $id = intval($_POST['id']);
    $acao = ($_POST['acao'] == '1') ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE produtos SET ativo = ? WHERE id = ?");
        $stmt->execute([$acao, $id]);
        
        if ($stmt->rowCount() > 0) {
            echo "Alterado com Sucesso";
        } else {
            echo "Produto não encontrado";
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
} else {
    echo "Dados incompletos";
}
?>