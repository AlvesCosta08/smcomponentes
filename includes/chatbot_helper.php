<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php'; // Ajuste o caminho se necessário

$mensagem = strtolower(trim($_POST['mensagem'] ?? ''));
$resposta = "Desculpe, não entendi sua pergunta.";

// Verificar se a mensagem não está vazia
if ($mensagem !== '') {
  // Preparar a consulta para buscar perguntas e respostas
  $stmt = $pdo->query("SELECT pergunta, resposta FROM conhecimento");
  
  // Iterar sobre as perguntas e respostas
  while ($row = $stmt->fetch()) {
    // Verificar se a mensagem contém a pergunta (ou parte dela)
    if (strpos(strtolower($row['pergunta']), $mensagem) !== false) {
      $resposta = $row['resposta'];  // Definir a resposta encontrada
      break;  // Interromper o loop assim que encontrar a resposta
    }
  }
}

// Exibir a resposta
echo $resposta;
?>
