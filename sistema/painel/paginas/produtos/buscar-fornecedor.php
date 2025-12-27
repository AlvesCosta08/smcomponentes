<?php
require_once("../../../conexao.php");

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    
    $query = $pdo->prepare("SELECT nome FROM fornecedores WHERE id = :id");
    $query->bindValue(':id', $id);
    $query->execute();
    
    $res = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($res) {
        echo htmlspecialchars($res['nome']);
    } else {
        echo '---';
    }
} else {
    echo '---';
}
?>