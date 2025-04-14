<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    if ($stmt->execute([$nome, $email, $senha])) {
        header('Location: login.php');
        exit;
    } else {
        $erro = "Erro ao registrar usuário.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #f0f8ff, #d0e6f7);
            font-family: 'Segoe UI', sans-serif;
        }

        .register-container {
            max-width: 500px;
            margin: 60px auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .register-container h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }

        .form-control {
            padding: 10px;
        }

        .btn-custom {
            width: 100%;
            background-color: #007bff;
            color: white;
            font-weight: bold;
            padding: 10px;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Criar Conta</h2>

        <?php if (isset($erro)) echo "<div class='alert alert-danger'>$erro</div>"; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome completo</label>
                <input type="text" class="form-control" name="nome" id="nome" required placeholder="Seu nome">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" name="email" id="email" required placeholder="seu@email.com">
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" name="senha" id="senha" required placeholder="Crie uma senha segura">
            </div>
            <button type="submit" class="btn btn-custom">Registrar</button>
        </form>

        <div class="login-link">
            <a href="login.php">Já tem uma conta? Faça login</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>