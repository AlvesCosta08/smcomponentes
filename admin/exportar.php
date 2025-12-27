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

$filtro = $_GET['filtro'] ?? '';
$formato = $_GET['formato'] ?? 'csv';

// Recupera as respostas com base no filtro
$respostas = listarRespostasPaginado($filtro, 1000, 0);  // Retorna todas para exportação (limite 1000)

// Função para exportar CSV
function exportarCSV($respostas) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=base_conhecimento.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Palavras-chave', 'Resposta']);  // Cabeçalho
    foreach ($respostas as $linha) {
        fputcsv($output, $linha);
    }
    fclose($output);
}

// Função para exportar JSON
function exportarJSON($respostas) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment;filename=base_conhecimento.json');
    echo json_encode($respostas, JSON_PRETTY_PRINT);
}

// Verifica qual formato de exportação foi solicitado
if ($formato === 'json') {
    exportarJSON($respostas);
} else {
    exportarCSV($respostas);
}
exit;
