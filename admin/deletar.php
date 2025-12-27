<?php
session_start();

// Verifica se o usuário está logado e se o tipo é admin ou assistente
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Exemplo de lógica para admins terem acesso a determinadas funcionalidades
if ($_SESSION['role'] === 'admin') {
    // Função exclusiva para admins
    echo "Você tem permissões de admin.";
} elseif ($_SESSION['role'] === 'assistente') {
    // Função exclusiva para assistentes
    echo "Você tem permissões de assistente.";
} else {
    // Caso não seja admin nem assistente
    echo "Você não tem permissões para acessar esta página.";
}
require_once '../db.php';
require_once 'base_helper.php';

$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    if (deletarResposta($id)) {
        header("Location: index.php?msg=excluido");
        exit;
    } else {
        echo "Erro ao excluir a resposta.";
    }
} else {
    echo "ID inválido.";
}
