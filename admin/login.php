<?php
session_start();
require_once '../db.php'; // Conexão com o banco de dados (config.php)

// Verifique se o usuário já está logado
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}


// Verificação do login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Gera o hash SHA256 da senha fornecida para comparar com o do banco
    $hashedPassword = hash('sha256', $password);

    // Consulta o usuário
    $sql = "SELECT id, nome_usuario, senha, role FROM usuarios WHERE nome_usuario = :username AND senha = :senha";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':senha', $hashedPassword, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $user['nome_usuario'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuário ou senha inválidos!";
    }
}

?>

<!-- Formulário de login -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        <h2 class="text-center">Login de Usuário</h2>
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="username" class="form-label">Nome de Usuário</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


