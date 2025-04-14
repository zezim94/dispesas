<?php
session_start();

require "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $usuario_id = $usuario['id'];
        $token = bin2hex(random_bytes(32));
        $expira_em = date('Y-m-d H:i:s', time() + 3600); // 1 hora

        $stmt2 = $pdo->prepare("INSERT INTO tokens_reset (usuario_id, token, expira_em) VALUES (?, ?, ?)");
        $stmt2->execute([$usuario_id, $token, $expira_em]);

        // Enviar email com link de reset
        $apiUrl = "https://php-py-xwtc.onrender.com/api/enviar_email";
        $link = "localhost/novo_dispesas/redefinir_senha.php?token=$token";

        $postData = [
            'destinatarios' => $email,
            'assunto' => "Recuperação de Senha",
            'mensagem' => "Clique no link para resetar sua senha: <a href='$link'>$link</a>"
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $resposta = curl_exec($ch);
        curl_close($ch);

        $mensagem = "E-mail de recuperação enviado!";
    } else {
        $mensagem = "Email não encontrado.";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #f8f9fa, #dbe9f4);
            font-family: 'Segoe UI', sans-serif;
        }

        .container-recuperar {
            max-width: 500px;
            margin: 80px auto;
            padding: 40px 30px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }

        .btn-recuperar {
            width: 100%;
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .btn-recuperar:hover {
            background-color: #0056b3;
        }

        .msg-alert {
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="container-recuperar">
        <h2>Recuperar Senha</h2>

        <?php if (isset($mensagem)): ?>
            <div class="alert alert-info msg-alert">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Digite seu e-mail:</label>
                <input type="email" class="form-control" name="email" id="email" required placeholder="exemplo@dominio.com">
            </div>
            <button type="submit" class="btn btn-recuperar">Enviar link de recuperação</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">Voltar para o login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>