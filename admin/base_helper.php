<?php
require_once '../db.php';

// Listar todas as respostas
function listarRespostas() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM conhecimento ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Buscar uma resposta pelo ID
function buscarRespostaPorId($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM conhecimento WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Inserir nova resposta
function inserirResposta($pergunta, $resposta) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO conhecimento (pergunta, resposta) VALUES (?, ?)");
    return $stmt->execute([$pergunta, $resposta]);
}

// Atualizar resposta existente
function atualizarResposta($id, $pergunta, $resposta) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE conhecimento SET pergunta = ?, resposta = ? WHERE id = ?");
    return $stmt->execute([$pergunta, $resposta, $id]);
}

// Deletar resposta
function deletarResposta($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM conhecimento WHERE id = ?");
    return $stmt->execute([$id]);
}

// Listar respostas com filtro e paginação
function listarRespostasPaginado($filtro = '', $limite = 10, $offset = 0) {
    global $pdo;
    $sql = "SELECT * FROM conhecimento WHERE pergunta LIKE :filtro ORDER BY id DESC LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $like = "%$filtro%";
    $stmt->bindParam(':filtro', $like, PDO::PARAM_STR);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Contar respostas com filtro
function contarRespostas($filtro = '') {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM conhecimento WHERE pergunta LIKE :filtro";
    $stmt = $pdo->prepare($sql);
    $like = "%$filtro%";
    $stmt->bindParam(':filtro', $like, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
}
?>

