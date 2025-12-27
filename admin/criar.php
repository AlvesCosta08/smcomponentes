<?php
session_start();
require_once '../db.php';
require_once 'base_helper.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Permissão: somente admin ou assistente pode acessar
$role = $_SESSION['role'] ?? '';
if ($role !== 'admin' && $role !== 'assistente') {
    echo "Você não tem permissão para acessar esta página.";
    exit;
}

// Processa envio do formulário
$mensagem = '';
$status = ''; // success ou error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $palavras = trim($_POST['palavras_chave'] ?? '');
    $resposta = trim($_POST['resposta'] ?? '');

    if ($palavras && $resposta) {
        if (inserirResposta($palavras, $resposta)) {
            header("Location: adicionar_resposta.php?status=success&msg=Resposta cadastrada com sucesso!");
            exit;
        } else {
            header("Location: adicionar_resposta.php?status=error&msg=Erro ao salvar a resposta.");
            exit;
        }
    } else {
        header("Location: adicionar_resposta.php?status=error&msg=Preencha todos os campos.");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Adicionar Resposta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow p-4">
      <h2 class="mb-4">Nova Resposta para o Chatbot</h2>
      <form method="POST">
        <div class="mb-3">
          <label for="palavras_chave" class="form-label">Palavras-chave (separadas por vírgula)</label>
          <input type="text" name="palavras_chave" id="palavras_chave" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="resposta" class="form-label">Resposta do Chatbot</label>
          <textarea name="resposta" id="resposta" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>

  <script>
    // Exibir alerta com base no GET
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const msg = urlParams.get('msg');

    if (status && msg) {
      Swal.fire({
        icon: status === 'success' ? 'success' : 'error',
        title: status === 'success' ? 'Sucesso' : 'Erro',
        text: msg,
        confirmButtonText: 'OK'
      }).then(() => {
        // Limpa a URL após exibir a mensagem
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    }
  </script>
</body>
</html>


