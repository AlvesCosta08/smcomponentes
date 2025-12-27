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
if (!$id) {
    header("Location: index.php");
    exit;
}

$resposta = buscarRespostaPorId($id);
if (!$resposta) {
    echo "Resposta não encontrada.";
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $palavras = trim($_POST['palavras_chave'] ?? '');
    $respostaTexto = trim($_POST['resposta'] ?? '');

    if ($palavras && $respostaTexto) {
        if (atualizarResposta($id, $palavras, $respostaTexto)) {
            header("Location: index.php");
            exit;
        } else {
            $mensagem = "Erro ao atualizar resposta.";
        }
    } else {
        $mensagem = "Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Resposta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4">Editar Resposta</h2>

    <?php if ($mensagem): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="palavras_chave" class="form-label">Palavras-chave</label>
        <input type="text" name="palavras_chave" id="palavras_chave" class="form-control" required value="<?= htmlspecialchars($resposta['palavras_chave']) ?>">
      </div>
      <div class="mb-3">
        <label for="resposta" class="form-label">Resposta</label>
        <textarea name="resposta" id="resposta" class="form-control" rows="4" required><?= htmlspecialchars($resposta['resposta']) ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Atualizar</button>
      <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</body>
</html>
