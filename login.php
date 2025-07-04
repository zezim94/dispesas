<?php
session_start();
require 'config.php'; // Aqui você já deve ter o código de conexão com PostgreSQL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Consulta para pegar o id, senha e nível do usuário
    $stmt = $pdo->prepare("SELECT id, senha, nivel FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Armazena os dados do usuário na sessão
        $_SESSION['user_id'] = $usuario['id'];  // Armazena o ID do usuário
        $_SESSION['nivel'] = $usuario['nivel'];  // Armazena o nível do usuário na sessão

        // Redireciona para a página do administrador ou para a página normal
        if ($_SESSION['nivel'] === 'adm') {
            header('Location: adm.php');  // Redireciona para a página do admin
        } else {
            header('Location: index.php');  // Redireciona para a página do usuário normal
        }
        exit;
    } else {
        $erro = "E-mail ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Relatório Mensal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #e0eafc, #cfdef3);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .login-container {
            max-width: 450px;
            margin: 80px auto;
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            color: #007bff;
        }

        .form-control {
            margin-bottom: 20px;
        }

        .btn-custom {
            width: 100%;
            background-color: #007bff;
            color: white;
            font-weight: 500;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .bottom-links {
            text-align: center;
            margin-top: 20px;
        }

        .bottom-links a {
            display: block;
            color: #007bff;
            text-decoration: none;
            margin-bottom: 5px;
        }

        .bottom-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger text-center">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label for="email">E-mail</label>
            <input type="email" class="form-control" name="email" id="email" required placeholder="Digite seu e-mail">

            <label for="senha">Senha</label>
            <input type="password" class="form-control" name="senha" id="senha" required placeholder="Digite sua senha">

            <button type="submit" class="btn btn-custom">Entrar</button>
        </form>

        <div class="bottom-links">
            <a href="esqueci_senha.php">Esqueci minha senha</a>
            <a href="register.php">Criar conta</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
